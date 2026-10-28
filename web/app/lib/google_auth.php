<?php
declare(strict_types=1);

/* ==========================================================================
   "Continue with Google" for the mobile app, through the phone's browser (no Google library inside the app, so it works in Expo Go
   and in store builds). The same design as the FaithFellows app:

     app ── opens ──▶ /auth/google/start?redirect=<app link>&challenge=<PKCE hash>
        start ──▶ Google ──▶ /auth/google/callback?code&state
        callback: trades Google's code for the ID token (server to server, with the client secret), checks it, finds the member
                  by Google id or by verified email (or notes a new person), and sends the browser back to the app with a ONE-TIME code.
        app ── POST /api/v1/auth/google/exchange {code, verifier} ──▶ API token  (or "needs a clan" for a new person)
        app ── POST /api/v1/auth/google/complete {code, verifier, clan_id…} ──▶ account created, API token

   Nothing secret travels in the app link: the code works once, for fifteen minutes at most, and only with the verifier the app kept.
   A new person must still choose a clan (every member belongs to one), so the account is created at "complete", not at the callback.
   Set up: Google Cloud Console → Credentials → OAuth client ID → "Web application"; authorised redirect URI = the callback address
   shown in Admin → Site settings → Google sign-in. Put the client ID and secret there (or in config/config.php under 'google').
   ========================================================================== */

const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';

function google_cfg(): array
{
    global $CFG;
    $c = is_array($CFG['google'] ?? null) ? $CFG['google'] : [];
    return ['client_id' => trim((string)($c['client_id'] ?? setting('google_client_id'))), 'client_secret' => trim((string)($c['client_secret'] ?? setting('google_client_secret')))];
}

function google_enabled(): bool
{
    $c = google_cfg();
    return $c['client_id'] !== '' && $c['client_secret'] !== '';
}

/** The address to register in Google Cloud as an authorised redirect URI. */
function google_callback_url(): string
{
    return (string)api_abs('auth/google/callback');
}

/** A random secret that stays the same for this site, used to sign the sign-in state. */
function google_secret(): string
{
    $k = setting('app_secret');
    if ($k === '') {
        $k = bin2hex(random_bytes(32));
        q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v=IF(v IS NULL OR v=\'\', VALUES(v), v)', ['app_secret', $k]);
        $k = (string)val('SELECT v FROM settings WHERE k=?', ['app_secret']);
    }
    return $k;
}

/** Only the app's own link may receive the browser back (never a web address, so this is not an open redirect). */
function google_allowed_return(string $url): bool
{
    global $CFG;
    if ($url === '' || strlen($url) > 300 || preg_match('/[\s<>"\\\\]/', $url) || str_contains($url, '#')) return false;
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    return $scheme === 'lango' || (in_array($scheme, ['exp', 'exps'], true) && !empty($CFG['debug']));   // Expo Go links are for development only
}

function b64url(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function b64url_decode(string $s): string { return (string)base64_decode(strtr($s, '-_', '+/')); }

function google_state_make(array $payload): string
{
    $body = b64url((string)json_encode($payload));
    return $body . '.' . b64url(hash_hmac('sha256', $body, google_secret(), true));
}

function google_state_read(string $state): ?array
{
    [$body, $sig] = array_pad(explode('.', $state, 2), 2, '');
    if ($body === '' || !hash_equals(b64url(hash_hmac('sha256', $body, google_secret(), true)), $sig)) return null;
    $p = json_decode(b64url_decode($body), true);
    return is_array($p) && (int)($p['e'] ?? 0) > time() && google_allowed_return((string)($p['r'] ?? '')) ? $p : null;
}

/** Trades Google's authorisation code for the ID token. Returns the token's claims, or null. */
function google_claims_from_code(string $code): ?array
{
    $cfg = google_cfg();
    $post = http_build_query(['code' => $code, 'client_id' => $cfg['client_id'], 'client_secret' => $cfg['client_secret'], 'redirect_uri' => google_callback_url(), 'grant_type' => 'authorization_code']);
    if (function_exists('curl_init')) {
        $ch = curl_init(GOOGLE_TOKEN_URL);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']]);
        $res = curl_exec($ch);
        curl_close($ch);
    } else {
        $res = @file_get_contents(GOOGLE_TOKEN_URL, false, stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded', 'content' => $post, 'timeout' => 10]]));
    }
    $j = is_string($res) ? json_decode($res, true) : null;
    return is_array($j) && !empty($j['id_token']) ? google_id_token_claims((string)$j['id_token']) : null;
}

/**
 * The claims of an ID token that came straight from Google's token endpoint over TLS (so its signature need not be checked again),
 * after checking who it is for, who issued it, that it has not expired, and that the email is verified.
 */
function google_id_token_claims(string $jwt, ?string $audience = null): ?array
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return null;
    $c = json_decode(b64url_decode($parts[1]), true);
    if (!is_array($c)) return null;
    $aud = $audience ?? google_cfg()['client_id'];
    if (($c['aud'] ?? null) !== $aud || !in_array($c['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true) || (int)($c['exp'] ?? 0) < time()) return null;
    if (empty($c['sub']) || empty($c['email']) || !filter_var($c['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) return null;
    return $c;
}

/** Stores a one-time code for the app and returns it (only a hash is kept). */
function google_code_issue(string $challenge, array $who): string
{
    $code = bin2hex(random_bytes(24));
    q('DELETE FROM google_codes WHERE expires_at < ?', [time()]);
    q('INSERT INTO google_codes (code_hash, challenge, user_id, google_sub, email, name, expires_at) VALUES (?,?,?,?,?,?,?)',
        [hash('sha256', $code), $challenge, $who['user_id'] ?? null, $who['sub'] ?? null, $who['email'] ?? null, $who['name'] ?? null, time() + 120]);
    return $code;
}

/** Finds the code row for an app's {code, verifier} pair, or stops with an error. */
function google_code_check(string $code, string $verifier): array
{
    $row = $code !== '' ? one('SELECT * FROM google_codes WHERE code_hash=?', [hash('sha256', $code)]) : null;
    if (!$row || (int)$row['expires_at'] < time() || $verifier === '' || !hash_equals((string)$row['challenge'], b64url(hash('sha256', $verifier, true)))) {
        api_err('This Google sign-in has expired. Please try again.', 422);
    }
    return $row;
}

/**
 * Who the Google account is: the member that already uses this Google id, else the member with the same verified email
 * (the accounts are linked), else null (a new person). Staff accounts never sign in through Google.
 * Returns [user row or null, message or null].
 */
function google_find_member(array $claims): array
{
    $user = one('SELECT * FROM users WHERE google_sub=?', [(string)$claims['sub']]) ?? one('SELECT * FROM users WHERE email=?', [strtolower((string)$claims['email'])]);
    if (!$user) return [null, null];
    if (!(int)$user['is_active']) return [null, 'This account has been switched off. Please contact LCI.'];
    if (!in_array($user['role'], ['contributor', 'clan_leader'], true)) return [null, 'This is a staff account. Please sign in with your email and password.'];
    if ($user['google_sub'] === null) q('UPDATE users SET google_sub=? WHERE id=?', [(string)$claims['sub'], $user['id']]);
    elseif ($user['google_sub'] !== (string)$claims['sub']) return [null, 'This email belongs to a different Google account.'];
    return [$user, null];
}
