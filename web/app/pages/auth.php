<?php
declare(strict_types=1);

/* /auth/google/start (app), /auth/google/web (website) and /auth/google/callback (both): the browser leg of "Continue with Google" (see lib/google_auth.php). */
require_once ROOT . '/app/lib/app_api.php';

$step = ($seg[1] ?? '') === 'google' ? ($seg[2] ?? '') : '';
$plain = function (string $msg, int $code): never {
    http_response_code($code);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lango App</title>'
        . '<body style="font:16px/1.5 system-ui,sans-serif;max-width:420px;margin:15vh auto;padding:0 20px;text-align:center"><h1 style="font-size:1.3rem">Lango App</h1><p>' . e($msg) . '</p></body>';
    exit;
};
$back = function (string $to, array $params): never {
    header('Location: ' . $to . (str_contains($to, '?') ? '&' : '?') . http_build_query($params), true, 302);
    exit;
};

if ($step === 'start') {
    $redirect = (string)get('redirect'); $challenge = (string)get('challenge');
    if (!google_allowed_return($redirect)) $plain('That return address is not allowed.', 422);
    if (!preg_match('/^[A-Za-z0-9_-]{43}$/', $challenge)) $plain('That sign-in request is not valid.', 422);
    if (!google_enabled()) $plain('Google sign-in is not available right now.', 503);
    $nonce = bin2hex(random_bytes(16));
    $state = google_state_make(['r' => $redirect, 'c' => $challenge, 'n' => $nonce, 'e' => time() + 600]);
    header('Location: ' . google_auth_url() . '?' . http_build_query(['client_id' => google_cfg()['client_id'], 'redirect_uri' => google_callback_url(), 'response_type' => 'code',
        'scope' => 'openid email profile', 'state' => $state, 'nonce' => $nonce, 'prompt' => 'select_account']), true, 302);
    exit;
}

/* The website: the same trip, tied to this browser's session so nobody can be signed in by a link someone else started. */
if ($step === 'web') {
    if (!google_enabled()) { flash(__('Google sign-in is not available right now.'), 'error'); redirect('account/login'); }
    $nonce = bin2hex(random_bytes(16));
    $_SESSION['gw'] = ['n' => $nonce, 'e' => time() + 600];
    $state = google_state_make(['w' => 1, 'n' => $nonce, 'e' => time() + 600, 'x' => safe_next((string)get('next'))]);
    header('Location: ' . google_auth_url() . '?' . http_build_query(['client_id' => google_cfg()['client_id'], 'redirect_uri' => google_callback_url(), 'response_type' => 'code',
        'scope' => 'openid email profile', 'state' => $state, 'nonce' => $nonce, 'prompt' => 'select_account']), true, 302);
    exit;
}

if ($step === 'callback') {
    $state = google_state_read((string)get('state'));
    if ($state && !empty($state['w'])) {   // the website's own sign-in
        $fail = function (string $msg): never { flash($msg, 'error'); redirect('account/login'); };
        $mine = $_SESSION['gw'] ?? null;
        unset($_SESSION['gw']);
        if (!is_array($mine) || (int)$mine['e'] < time() || !hash_equals((string)$mine['n'], (string)$state['n'])) $fail(__('This Google sign-in link is not valid or has expired. Please try again.'));
        if (get('error') !== '' || get('code') === '' || !google_enabled()) $fail(__('Google sign-in was cancelled.'));
        $claims = google_claims_from_code((string)get('code'));
        if (!$claims || !hash_equals((string)$state['n'], (string)($claims['nonce'] ?? ''))) $fail(__('We could not verify your Google sign-in. Please try again.'));
        [$user, $why] = google_find_member($claims);
        if ($why) $fail(__($why));
        $next = (string)($state['x'] ?? '');
        if ($user) { login_member($user); audit('login'); redirect($next ?: 'account'); }
        $_SESSION['gw_new'] = ['sub' => (string)$claims['sub'], 'email' => strtolower((string)$claims['email']), 'name' => (string)($claims['name'] ?? ''), 'e' => time() + 900, 'x' => $next];
        redirect('account/google');   // a new person chooses a clan first, as in the app
    }
    if (!$state) $plain('This sign-in link is invalid or has expired. Please go back to the app and try again.', 400);
    if (get('error') !== '' || get('code') === '' || !google_enabled()) $back($state['r'], ['error' => 'cancelled']);
    $claims = google_claims_from_code((string)get('code'));
    if (!$claims || !hash_equals((string)$state['n'], (string)($claims['nonce'] ?? ''))) $back($state['r'], ['error' => 'failed']);
    [$user, $why] = google_find_member($claims);
    if ($why) $back($state['r'], ['error' => 'denied', 'message' => $why]);
    $code = google_code_issue((string)$state['c'], $user ? ['user_id' => (int)$user['id']] : ['sub' => (string)$claims['sub'], 'email' => strtolower((string)$claims['email']), 'name' => (string)($claims['name'] ?? '')]);
    $back($state['r'], ['code' => $code]);
}

not_found();
