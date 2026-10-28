<?php
declare(strict_types=1);

/* ==========================================================================
   Mobile-app API (/api/v1/...): token sign-in, the member's profile, contributions with media,
   notifications, and the content the app reads (clans, districts, history, dictionary, events, programs…).
   Everything answers JSON as { data: … } (lists add meta: { page, limit, total, has_more }); errors are
   { message, errors? } with the usual HTTP status codes, so the Expo app can treat them like any REST API.
   ========================================================================== */

require_once __DIR__ . '/clan_group_api.php';
require_once __DIR__ . '/google_auth.php';
require_once __DIR__ . '/daily_api.php';

/* ---------- plumbing ---------- */
function api_abs(?string $p): ?string
{
    if (!$p) return null;
    if (preg_match('#^https?://#', $p)) return $p;
    $host = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $host . url($p);
}

/** JSON or form body as an array; also fills $_POST so post() and read_clan_input() work for both. */
function api_in(): array
{
    static $in = null;
    if ($in !== null) return $in;
    $ct = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($ct, 'application/json')) {
        $j = json_decode((string)file_get_contents('php://input'), true);
        $in = is_array($j) ? $j : [];
        $_POST = array_map(fn($v) => is_scalar($v) || $v === null ? $v : '', $in);
    } else $in = $_POST;
    return $in;
}

function api_err(string $message, int $code = 400, array $errors = []): never
{
    json_out(['message' => $message] + ($errors ? ['errors' => array_map(fn($e) => (array)$e, $errors)] : []), $code);
}

function api_header_token(): ?string
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($h === '' && function_exists('getallheaders')) foreach (getallheaders() as $k => $v) if (strcasecmp((string)$k, 'Authorization') === 0) $h = (string)$v;
    if (preg_match('/^Bearer\s+(\S+)$/i', $h, $m)) return $m[1];
    $x = trim((string)($_SERVER['HTTP_X_AUTH_TOKEN'] ?? ''));
    return $x !== '' ? $x : null;
}

function api_user(): ?array
{
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if ($tok = api_header_token()) {
        $row = one('SELECT u.*, t.id token_id FROM api_tokens t JOIN users u ON u.id=t.user_id WHERE t.token_hash=? AND u.is_active=1', [hash('sha256', $tok)]);
        if ($row) { $u = $row; q('UPDATE api_tokens SET last_used=NOW() WHERE id=?', [$row['token_id']]); }
    }
    return $u;
}

function api_need_user(): array
{
    return api_user() ?? api_err('Please sign in again.', 401);
}

/** Per-IP (or per-key) limit that survives without cookies. */
function api_throttle(string $key, int $max, int $window): void
{
    $k = substr($key . ':' . client_ip(), 0, 120);
    $now = time();
    $r = one('SELECT hits, reset_at FROM rate_limits WHERE k=?', [$k]);
    if (!$r || (int)$r['reset_at'] < $now) { q('REPLACE INTO rate_limits (k,hits,reset_at) VALUES (?,1,?)', [$k, $now + $window]); return; }
    if ((int)$r['hits'] >= $max) api_err('Too many attempts. Please wait a moment and try again.', 429);
    q('UPDATE rate_limits SET hits=hits+1 WHERE k=?', [$k]);
}

function api_issue_token(int $userId, string $device): string
{
    $tok = 'lci_' . bin2hex(random_bytes(32));
    q('INSERT INTO api_tokens (user_id, token_hash, device_name) VALUES (?,?,?)', [$userId, hash('sha256', $tok), mb_substr($device, 0, 120)]);
    return $tok;
}

function api_meta(int $page, int $limit, int $total): array
{
    return ['page' => $page, 'limit' => $limit, 'total' => $total, 'has_more' => $page * $limit < $total];
}

function api_paging(int $defLimit = 20): array
{
    $limit = max(1, min(100, (int)get('limit', (string)$defLimit)));
    $page = max(1, (int)get('page', '1'));
    return [$page, $limit, ($page - 1) * $limit];
}

function short_person_name(string $name): string
{
    $p = preg_split('/\s+/', trim($name)) ?: [];
    return count($p) > 1 ? $p[0] . ' ' . mb_substr((string)end($p), 0, 1) . '.' : ($p[0] ?? '');
}

/* ---------- levels, badges, member JSON ---------- */
function contributor_level(int $pts): array
{
    $tiers = [['Bronze', 0, 100], ['Silver', 101, 500], ['Gold', 501, 1000], ['Platinum', 1001, null]];
    foreach ($tiers as $i => [$name, $min, $max]) {
        if ($max === null || $pts <= $max) {
            $next = $tiers[$i + 1] ?? null;
            return ['name' => $name, 'min' => $min, 'next_name' => $next[0] ?? null, 'next_at' => $next[1] ?? null,
                'progress' => $next ? round(min(1, ($pts - $min) / max(1, $next[1] - $min)), 2) : 1];
        }
    }
    return ['name' => 'Bronze', 'min' => 0, 'next_name' => 'Silver', 'next_at' => 101, 'progress' => 0];
}

function user_badges(int $uid): array
{
    $c = fn(string $w) => (int)val("SELECT COUNT(*) FROM contributions WHERE user_id=? AND status IN ('approved','published') $w", [$uid]);
    $total = $c('');
    $best = max((int)val('SELECT best_streak FROM users WHERE id=?', [$uid]), streak_info($uid, 'open')['current']);
    $defs = [
        ['first', 'First contribution', 'Your first approved contribution', $total >= 1],
        ['ten', '10 approved', 'Ten approved contributions', $total >= 10],
        ['language', 'Language champion', 'Five approved words or proverbs', $c("AND type='language'") >= 5],
        ['nature', 'Conservation hero', 'Three approved nature records', $c("AND type='natural'") >= 3],
        ['historian', 'Clan historian', 'Three approved oral histories or traditions', $c("AND type IN ('oral_history','cultural')") >= 3],
        ['streak7', 'Seven-day streak', 'Open the app seven days in a row', $best >= 7],
        ['streak30', 'Thirty-day streak', 'Open the app thirty days in a row', $best >= 30],
        ['words', 'Word collector', 'Learn the word of the day on ten different days', (int)val("SELECT COUNT(*) FROM activity_days WHERE user_id=? AND kind='word'", [$uid]) >= 10],
        ['challenge', 'Challenger', 'Take a weekly Language Challenge', (bool)val("SELECT COUNT(*) FROM quiz_scores WHERE user_id=? AND kind='weekly'", [$uid])],
    ];
    return array_map(fn($d) => ['key' => $d[0], 'name' => $d[1], 'description' => $d[2], 'earned' => $d[3]], $defs);
}

function api_user_json(array $u): array
{
    $clan = $u['clan_id'] ? one('SELECT id, name, slug FROM clans WHERE id=?', [$u['clan_id']]) : null;
    $n = fn(string $w) => (int)val("SELECT COUNT(*) FROM contributions WHERE user_id=? $w", [$u['id']]);
    return [
        'id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email'], 'phone' => $u['phone'], 'country' => $u['country'],
        'role' => $u['role'], 'clan' => $clan ? ['id' => (int)$clan['id'], 'name' => $clan['name'], 'slug' => $clan['slug']] : null, 'clan_other' => $u['clan_other'],
        'points' => (int)$u['points'], 'level' => contributor_level((int)$u['points']), 'badges' => user_badges((int)$u['id']),
        'counts' => ['total' => $n(''), 'approved' => $n("AND status IN ('approved','published')"), 'pending' => $n("AND status IN ('pending','under_review','needs_info')")],
        'joined' => $u['created_at'], 'clan_group' => clan_group_summary($u), 'streaks' => streaks_json($u),
    ];
}

/* ---------- shapers ---------- */
function api_person(array $n, bool $full = false): array
{
    $r = ['id' => (int)$n['id'], 'slug' => $n['slug'], 'name' => $n['name'], 'short_name' => $n['short_name'] ?: $n['name'], 'category' => $n['category'], 'category_label' => PEOPLE_CATS[$n['category']] ?? $n['category'],
        'title' => $n['title'], 'born' => $n['born'], 'died' => $n['died'], 'place' => $n['place'], 'summary' => $n['summary'], 'photo' => api_abs($n['photo']), 'featured' => (bool)$n['featured']];
    if ($full) $r['body_html'] = rich($n['body']);
    return $r;
}

function api_district(array $d, bool $full = false): array
{
    $known = array_values(array_filter(array_map('trim', explode("\n", (string)$d['known_for']))));
    $r = ['id' => (int)$d['id'], 'slug' => $d['slug'], 'name' => $d['name'], 'kind' => $d['kind'], 'headquarters' => $d['headquarters'], 'population' => $d['population'] !== null ? (int)$d['population'] : null,
        'pop_year' => $d['pop_year'] !== null ? (int)$d['pop_year'] : null, 'area_km2' => $d['area_km2'] !== null ? (float)$d['area_km2'] : null, 'created_year' => $d['created_year'] !== null ? (int)$d['created_year'] : null,
        'summary' => excerpt($d['description'], 180), 'known_for' => $known, 'lat' => $d['lat'] !== null ? (float)$d['lat'] : null, 'lng' => $d['lng'] !== null ? (float)$d['lng'] : null, 'image' => api_abs($d['image'])];
    if ($full) $r += ['parent' => $d['parent'], 'neighbours' => $d['neighbours'], 'distance' => $d['distance'], 'subdivisions' => $d['subdivisions'], 'overview_html' => rich($d['description']),
        'demography_html' => rich($d['demography']), 'economy_html' => rich($d['economy']), 'attractions_html' => rich($d['attractions']), 'history_html' => rich($d['history'])];
    return $r;
}

function api_program(array $p, bool $full = false): array
{
    $r = ['id' => (int)$p['id'], 'slug' => $p['slug'], 'title' => t($p, 'title'), 'category' => $p['category'], 'category_label' => PROGRAM_CATS[$p['category']] ?? $p['category'], 'summary' => $p['summary'],
        'image' => api_abs($p['image']), 'progress' => (int)$p['progress'], 'goal_amount' => (float)$p['goal_amount'], 'raised_amount' => (float)$p['raised_amount'], 'accepts_donations' => (bool)$p['accepts_donations'], 'featured' => (bool)$p['featured']];
    if ($full) $r['body_html'] = rich(t($p, 'body'));
    return $r;
}

function api_event(array $e, bool $full = false): array
{
    $taken = (int)val('SELECT COALESCE(SUM(tickets),0) FROM event_registrations WHERE event_id=?', [$e['id']]);
    $left = $e['capacity'] ? max(0, (int)$e['capacity'] - $taken) : null;
    $past = strtotime($e['starts_at']) < strtotime('today');
    $r = ['id' => (int)$e['id'], 'slug' => $e['slug'], 'title' => t($e, 'title'), 'summary' => $e['summary'], 'image' => api_abs($e['image']), 'location' => $e['location'], 'starts_at' => $e['starts_at'], 'ends_at' => $e['ends_at'],
        'capacity' => $e['capacity'] ? (int)$e['capacity'] : null, 'places_left' => $left, 'past' => $past, 'registration_open' => (bool)$e['registration_open'] && !$past && ($left === null || $left > 0)];
    if ($full) $r['body_html'] = rich(t($e, 'body'));
    return $r;
}

function api_post(array $p, bool $full = false): array
{
    $r = ['id' => (int)$p['id'], 'slug' => $p['slug'], 'title' => t($p, 'title'), 'excerpt' => t($p, 'excerpt') ?: excerpt($p['body'], 160), 'image' => api_abs($p['image']), 'author' => $p['author_name'], 'published_at' => $p['published_at'], 'category' => $p['cat'] ?? null];
    if ($full) $r += ['body_html' => rich(t($p, 'body')), 'video_url' => $p['video_url'], 'audio' => api_abs($p['audio'])];
    return $r;
}

function api_word(array $w): array
{
    return ['id' => (int)$w['id'], 'lango' => $w['lango'], 'english' => $w['english'], 'pos' => $w['pos'], 'category' => $w['category'], 'example_lg' => $w['example_lg'], 'example_en' => $w['example_en'],
        'audio' => api_abs($w['audio']), 'verified' => (bool)$w['verified'], 'community' => str_starts_with((string)$w['source'], 'Community'), 'contributor' => $w['contributor']];
}

function api_contribution(array $c): array
{
    $word = $c['type'] === 'language';
    return ['id' => (int)$c['id'], 'code' => $c['code'], 'type' => $c['type'], 'type_label' => CONTRIB_TYPES[$c['type']][0] ?? $c['type'], 'title' => $c['title'], 'description' => $c['description'],
        'status' => $c['status'], 'status_label' => $word && $c['status'] === 'published' ? 'Added to the dictionary' : (CONTRIB_STATUS[$c['status']] ?? $c['status']),
        'review_note' => $c['review_note'], 'points_awarded' => (int)$c['points_awarded'], 'media_kind' => $c['media_kind'], 'media_url' => api_abs($c['media_file']), 'created_at' => $c['created_at']];
}

/* ---------- creating things ---------- */
function api_create_contribution(array $u): array
{
    $in = api_in();
    $type = (string)($in['type'] ?? '');
    if (!isset(CONTRIB_TYPES[$type])) api_err('Please choose a contribution type.', 422, ['type' => 'Please choose a contribution type.']);
    $ref = mb_substr(trim((string)($in['client_ref'] ?? '')), 0, 40);
    if ($ref !== '' && ($dup = one('SELECT code, status FROM contributions WHERE user_id=? AND client_ref=?', [$u['id'], $ref]))) return ['code' => $dup['code'], 'status' => $dup['status'], 'duplicate' => true];

    $isWord = $type === 'language';
    $prov = $isWord && ($in['kind'] ?? '') === 'proverb';
    $title = trim((string)($isWord ? ($in['word'] ?? $in['title'] ?? '') : ($in['title'] ?? '')));
    $desc = trim((string)($isWord ? ($in['meaning'] ?? $in['description'] ?? '') : ($in['description'] ?? '')));
    $errors = [];
    if ($isWord) {
        if (mb_strlen($title) < 2) $errors['word'] = $prov ? 'Please write the proverb.' : 'Please write the Leb Lango word.';
        if ($desc === '') $errors['meaning'] = 'Please add the English meaning.';
    } else {
        if ($title === '') $errors['title'] = 'Please add a title.';
        if (mb_strlen($desc) < 10) $errors['description'] = 'Please describe it in at least 10 characters.';
    }
    if (($in['clan_id'] ?? '') === '' || ($in['clan_id'] ?? null) === null) $errors['clan_id'] = 'Please choose your clan.';
    [$clanId, $clanOther] = read_clan_input();
    if (($in['clan_id'] ?? '') === 'other' && !$clanOther) $errors['clan_other'] = 'Please type the name of your clan.';
    if ($errors) api_err(reset($errors), 422, $errors);

    $file = null; $mkind = 'none';
    try {
        foreach (['image', 'audio', 'video', 'document'] as $k) if (!empty($_FILES['media_' . $k]['name'])) { $file = save_upload('media_' . $k, $k); $mkind = $k; break; }
    } catch (RuntimeException $e) { api_err($e->getMessage(), 422, ['media' => $e->getMessage()]); }

    $lat = is_numeric($in['lat'] ?? null) ? round((float)$in['lat'], 6) : null; $lng = is_numeric($in['lng'] ?? null) ? round((float)$in['lng'], 6) : null;
    if ($lat !== null && ($lat < -90 || $lat > 90 || $lng === null || $lng < -180 || $lng > 180)) $lat = $lng = null;
    $code = new_code(8);
    $row = ['code' => $code, 'user_id' => $u['id'], 'name' => mb_substr($u['name'], 0, 150), 'email' => $u['email'], 'phone' => $u['phone'], 'type' => $type, 'title' => mb_substr($title, 0, 220), 'description' => mb_substr($desc, 0, 8000),
        'clan_id' => $clanId, 'contributor_clan_id' => $clanId, 'contributor_clan_other' => $clanOther, 'district_id' => (int)($in['district_id'] ?? 0) ?: null, 'location_text' => mb_substr((string)($in['location_text'] ?? ''), 0, 190) ?: null,
        'lat' => $lat, 'lng' => $lng, 'observed_on' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($in['observed_on'] ?? '')) ? $in['observed_on'] : null,
        'media_file' => $file, 'media_kind' => $mkind, 'rights' => in_array($in['rights'] ?? '', ['retain', 'cc_by', 'lci'], true) ? $in['rights'] : 'retain', 'client_ref' => $ref ?: null];
    if ($isWord) $row += ['word_cat' => $prov ? 'proverb' : (isset(WORD_CATS[$in['word_cat'] ?? '']) ? $in['word_cat'] : null), 'word_pos' => $prov ? 'phrase' : (isset(WORD_POS[$in['word_pos'] ?? '']) ? $in['word_pos'] : null),
        'example_lg' => $prov ? null : (mb_substr((string)($in['example_lg'] ?? ''), 0, 500) ?: null), 'word_note' => $prov ? (mb_substr((string)($in['note'] ?? ''), 0, 500) ?: null) : null, 'credit' => !empty($in['credit']) ? 1 : 0];
    insert('contributions', $row);
    return ['code' => $code, 'status' => 'pending'];
}

/* ---------- the dispatcher ---------- */
/** Handles the app's endpoints; returns normally only when $res is not one of them (the legacy handlers then run). */
function api_app(string $res, array $seg): void
{
    $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $id = $seg[3] ?? ''; $sub = $seg[4] ?? '';
    $user = api_user();
    daily_api($res, $id, $sub, $m, $user);   // streaks, daily word, weekly challenge, leaderboards (exits when it handles the request)

    switch ($res) {
        case 'ping':
            json_out(['status' => 'ok', 'name' => setting('site_name', 'Lango Cultural Institution'), 'version' => 'v1', 'google' => google_enabled(), 'time' => date('c')]);

        /* ---------- accounts ---------- */
        case 'auth':
            if ($m !== 'POST' && !($m === 'DELETE' && $id === 'token')) api_err('Method not allowed.', 405);
            if ($id === 'register') {
                api_throttle('register', 8, 3600);
                $in = api_in();
                $name = trim((string)($in['name'] ?? '')); $email = strtolower(trim((string)($in['email'] ?? ''))); $pw = (string)($in['password'] ?? '');
                $e = [];
                if ($name === '') $e['name'] = 'Please enter your name.';
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $e['email'] = 'Please enter a valid email address.';
                elseif (val('SELECT COUNT(*) FROM users WHERE email=?', [$email])) $e['email'] = 'An account with this email already exists. Try signing in.';
                if (strlen($pw) < 8) $e['password'] = 'Choose a password of at least 8 characters.';
                if (($in['clan_id'] ?? '') === '') $e['clan_id'] = 'Please choose your clan.';
                [$clanId, $clanOther] = read_clan_input();
                if (($in['clan_id'] ?? '') === 'other' && !$clanOther) $e['clan_other'] = 'Please type the name of your clan.';
                if ($e) api_err(reset($e), 422, $e);
                $uid = insert('users', ['name' => mb_substr($name, 0, 150), 'email' => $email, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'role' => 'contributor', 'clan_id' => $clanId, 'clan_other' => $clanOther,
                    'country' => mb_substr((string)($in['country'] ?? ''), 0, 80) ?: null, 'phone' => mb_substr((string)($in['phone'] ?? ''), 0, 40) ?: null]);
                $tok = api_issue_token($uid, (string)($in['device_name'] ?? 'Lango app'));
                json_out(['token' => $tok, 'user' => api_user_json(one('SELECT * FROM users WHERE id=?', [$uid]))], 201);
            }
            if ($id === 'login') {
                api_throttle('login', 10, 900);
                $in = api_in();
                $login = trim((string)($in['login'] ?? $in['email'] ?? '')); $pw = (string)($in['password'] ?? '');
                $row = null;
                if ($login !== '') {
                    $row = str_contains($login, '@') ? one('SELECT * FROM users WHERE email=? AND is_active=1', [strtolower($login)]) : null;
                    if (!$row && !str_contains($login, '@')) {
                        $digits = preg_replace('/\D+/', '', $login);
                        $cands = $digits !== '' ? all("SELECT * FROM users WHERE is_active=1 AND REPLACE(REPLACE(REPLACE(phone,' ',''),'+',''),'-','') LIKE ?", ['%' . substr($digits, -9)]) : [];
                        $row = count($cands) === 1 ? $cands[0] : null;
                    }
                }
                if (!$row || !password_verify($pw, $row['password_hash'])) api_err('Incorrect email, phone or password.', 422, ['login' => 'Incorrect email, phone or password.']);
                q('UPDATE users SET last_login=NOW() WHERE id=?', [$row['id']]);
                json_out(['token' => api_issue_token((int)$row['id'], (string)($in['device_name'] ?? 'Lango app')), 'user' => api_user_json($row)]);
            }
            if ($id === 'token' || $id === 'logout') {
                if ($tok = api_header_token()) q('DELETE FROM api_tokens WHERE token_hash=?', [hash('sha256', $tok)]);
                json_out(['ok' => true]);
            }
            if ($id === 'google' && in_array($sub, ['exchange', 'complete'], true) && $m === 'POST') {
                api_throttle('google', 30, 900);
                $in = api_in();
                $row = google_code_check((string)($in['code'] ?? ''), (string)($in['verifier'] ?? ''));
                $device = (string)($in['device_name'] ?? 'Lango app');
                if ($row['user_id']) {   // already a member: the code is used up
                    q('DELETE FROM google_codes WHERE code_hash=?', [$row['code_hash']]);
                    $member = one('SELECT * FROM users WHERE id=? AND is_active=1', [$row['user_id']]) ?? api_err('This account is not available.', 403);
                    q('UPDATE users SET last_login=NOW() WHERE id=?', [$member['id']]);
                    json_out(['token' => api_issue_token((int)$member['id'], $device), 'user' => api_user_json($member), 'is_new_user' => false]);
                }
                if ($sub === 'exchange') {   // a new person: the code stays valid a little longer while they choose their clan
                    q('UPDATE google_codes SET expires_at=? WHERE code_hash=?', [time() + 900, $row['code_hash']]);
                    json_out(['needs_clan' => true, 'profile' => ['name' => $row['name'], 'email' => $row['email']]]);
                }
                $e = [];
                if (($in['clan_id'] ?? '') === '') $e['clan_id'] = 'Please choose your clan.';
                [$clanId, $clanOther] = read_clan_input();
                if (($in['clan_id'] ?? '') === 'other' && !$clanOther) $e['clan_other'] = 'Please type the name of your clan.';
                if ($e) api_err(reset($e), 422, $e);
                if (val('SELECT COUNT(*) FROM users WHERE email=? OR google_sub=?', [$row['email'], $row['google_sub']])) api_err('There is already an account for this Google address. Please continue with Google again.', 409);
                $name = trim((string)($in['name'] ?? '')) ?: (string)$row['name'] ?: (string)strstr((string)$row['email'], '@', true);
                $uid = insert('users', ['name' => mb_substr($name, 0, 150), 'email' => $row['email'], 'password_hash' => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT), 'role' => 'contributor',
                    'clan_id' => $clanId, 'clan_other' => $clanOther, 'google_sub' => $row['google_sub'], 'country' => mb_substr((string)($in['country'] ?? ''), 0, 80) ?: null]);
                q('DELETE FROM google_codes WHERE code_hash=?', [$row['code_hash']]);
                json_out(['token' => api_issue_token($uid, $device), 'user' => api_user_json(one('SELECT * FROM users WHERE id=?', [$uid])), 'is_new_user' => true], 201);
            }
            api_err('Unknown request.', 404);

        case 'me':
            $u = api_need_user();
            if ($id === '') {
                if ($m === 'GET') json_out(['data' => api_user_json($u)]);
                if ($m === 'PATCH' || $m === 'PUT' || $m === 'POST') {
                    $in = api_in(); $data = [];
                    if (isset($in['name'])) { $nm = trim((string)$in['name']); if ($nm === '') api_err('Please enter your name.', 422, ['name' => 'Please enter your name.']); $data['name'] = mb_substr($nm, 0, 150); }
                    if (array_key_exists('phone', $in)) $data['phone'] = mb_substr(trim((string)$in['phone']), 0, 40) ?: null;
                    if (array_key_exists('country', $in)) $data['country'] = mb_substr(trim((string)$in['country']), 0, 80) ?: null;
                    if (array_key_exists('clan_id', $in)) {
                        [$cid, $cother] = read_clan_input(); $data['clan_id'] = $cid; $data['clan_other'] = $cother;
                        if ((int)$cid !== (int)$u['clan_id']) { $data['clan_joined_at'] = null; $data['clan_hidden'] = 0; $data['clan_seen_id'] = 0; }   // a new clan means joining its group again
                    }
                    if ($data) update('users', $data, (int)$u['id']);
                    json_out(['data' => api_user_json(one('SELECT * FROM users WHERE id=?', [$u['id']]))]);
                }
                if ($m === 'DELETE') {   // delete the account (stores require it); contributions stay, without the person's link
                    $in = api_in();
                    if (!password_verify((string)($in['password'] ?? ''), $u['password_hash'])) api_err('That password is not right.', 422, ['password' => 'That password is not right.']);
                    q('UPDATE contributions SET user_id=NULL WHERE user_id=?', [$u['id']]);
                    q('DELETE FROM clan_message_reports WHERE user_id=? OR message_id IN (SELECT id FROM (SELECT id FROM clan_messages WHERE user_id=?) x)', [$u['id'], $u['id']]);
                    q('DELETE FROM clan_messages WHERE user_id=?', [$u['id']]);
                    foreach (['activity_days', 'quiz_scores'] as $t) q("DELETE FROM `$t` WHERE user_id=?", [$u['id']]);   // streaks and quiz results go with the account
                    foreach (['api_tokens', 'device_tokens', 'user_notifications'] as $t) q("DELETE FROM `$t` WHERE user_id=?", [$u['id']]);
                    q('DELETE FROM users WHERE id=? AND role=?', [$u['id'], 'contributor']);
                    json_out(['ok' => true]);
                }
                api_err('Method not allowed.', 405);
            }
            if ($id === 'password' && $m === 'POST') {
                $in = api_in();
                if (!password_verify((string)($in['current_password'] ?? ''), $u['password_hash'])) api_err('Your current password is not right.', 422, ['current_password' => 'Your current password is not right.']);
                if (strlen((string)($in['password'] ?? '')) < 8) api_err('Choose a new password of at least 8 characters.', 422, ['password' => 'Choose a new password of at least 8 characters.']);
                update('users', ['password_hash' => password_hash((string)$in['password'], PASSWORD_DEFAULT)], (int)$u['id']);
                json_out(['ok' => true]);
            }
            if ($id === 'contributions') {
                [$page, $limit, $off] = api_paging(30);
                $total = (int)val('SELECT COUNT(*) FROM contributions WHERE user_id=?', [$u['id']]);
                $rows = all("SELECT * FROM contributions WHERE user_id=? ORDER BY id DESC LIMIT $limit OFFSET $off", [$u['id']]);
                json_out(['data' => array_map('api_contribution', $rows), 'meta' => api_meta($page, $limit, $total)]);
            }
            if ($id === 'notifications') {
                if ($sub === 'read' && $m === 'POST') {
                    $in = api_in();
                    if (!empty($in['all'])) q('UPDATE user_notifications SET is_read=1 WHERE user_id=?', [$u['id']]);
                    else q('UPDATE user_notifications SET is_read=1 WHERE user_id=? AND id=?', [$u['id'], (int)($in['id'] ?? 0)]);
                    json_out(['ok' => true]);
                }
                $rows = all('SELECT id, type, title, body, data, is_read, created_at FROM user_notifications WHERE user_id=? ORDER BY id DESC LIMIT 60', [$u['id']]);
                foreach ($rows as &$r) { $r['is_read'] = (bool)$r['is_read']; $r['data'] = $r['data'] ? json_decode($r['data'], true) : null; }
                json_out(['data' => $rows, 'unread' => (int)val('SELECT COUNT(*) FROM user_notifications WHERE user_id=? AND is_read=0', [$u['id']])]);
            }
            if ($id === 'device') {
                if ($m === 'DELETE') { q('DELETE FROM device_tokens WHERE user_id=?', [$u['id']]); json_out(['ok' => true]); }
                $in = api_in(); $tok = trim((string)($in['token'] ?? ''));
                if ($tok === '' || strlen($tok) > 190) api_err('Missing push token.', 422);
                q('INSERT INTO device_tokens (user_id, token, platform, updated_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), platform=VALUES(platform), updated_at=NOW()', [$u['id'], $tok, mb_substr((string)($in['platform'] ?? ''), 0, 20)]);
                json_out(['ok' => true]);
            }
            api_err('Unknown request.', 404);

        /* ---------- contributions ---------- */
        case 'contributions':
            if ($id === 'track') {
                $c = one('SELECT * FROM contributions WHERE code=?', [strtoupper(trim(get('code')))]);
                if (!$c) api_err('No contribution found with that code.', 404);
                json_out(['data' => api_contribution($c)]);
            }
            if ($m !== 'POST') api_err('Method not allowed.', 405);
            api_throttle('contribute', 40, 3600);
            json_out(['data' => api_create_contribution(api_need_user())], 201);

        /* ---------- home ---------- */
        case 'home':
            $featured = all("SELECT * FROM notables WHERE status='published' ORDER BY featured DESC, sort");
            $pool = array_values(array_filter($featured, fn($n) => $n['featured'])) ?: $featured;   // today's person comes from the featured ones when there are any
            $wotd = word_of_day();
            json_out(['data' => [
                'word_of_the_day' => $wotd ? api_word($wotd) : null,
                'featured_person' => $pool ? api_person($pool[(int)date('z') % count($pool)]) : null,
                'news' => array_map('api_post', all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.status='published' AND p.published_at<=NOW() ORDER BY p.featured DESC, p.published_at DESC LIMIT 3")),
                'events' => array_map(fn($e) => api_event($e) + ['days_left' => max(0, (int)ceil((strtotime(substr((string)$e['starts_at'], 0, 10)) - strtotime('today')) / 86400))], all("SELECT * FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT 3")),
                'programs' => array_map('api_program', all("SELECT * FROM programs WHERE status='published' ORDER BY featured DESC, sort, id LIMIT 4")),
                'stats' => site_counts(),
                'unread' => $user ? (int)val('SELECT COUNT(*) FROM user_notifications WHERE user_id=? AND is_read=0', [$user['id']]) : 0,
            ] + daily_home($user)]);

        /* ---------- people, places, history ---------- */
        case 'clans':
            if ($sub !== '' && ctype_digit($id)) clan_group_api((int)$id, $sub, $seg, $m);
            if ($id === 'options') json_out(['data' => array_map(fn($c) => ['id' => (int)$c['id'], 'name' => $c['name']], all("SELECT id, name FROM clans WHERE status='published' ORDER BY name"))]);
            if ($id !== '') {
                $c = one("SELECT c.*, d.name district, d.slug district_slug FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE c.slug=? AND c.status='published'", [$id]);
                if (!$c) api_err('We could not find that clan.', 404);
                $ld = clan_leader_of($c);
                json_out(['data' => ['members' => clan_group_members((int)$c['id']), 'id' => (int)$c['id'], 'slug' => $c['slug'], 'name' => $c['name'], 'totem' => $c['totem'], 'district' => $c['district'], 'district_slug' => $c['district_slug'], 'summary' => $c['summary'],
                    'history_html' => rich(t($c, 'history')), 'lineage_html' => rich($c['lineage']), 'notable_members_html' => rich($c['notable_members']), 'verified' => (bool)$c['verified'], 'image' => api_abs($c['image']),
                    'leader' => $ld ? ['name' => $ld['name'], 'title' => $ld['title'], 'photo' => api_abs($ld['photo']), 'bio' => $ld['bio']] : null]]);
            }
            [$page, $limit, $off] = api_paging(30);
            $where = ["c.status='published'"]; $par = [];
            if (get('q') !== '') { $where[] = '(c.name LIKE ? OR c.totem LIKE ?)'; $l = '%' . like_escape(get('q')) . '%'; array_push($par, $l, $l); }
            if (get('district') !== '') { $where[] = 'd.slug=?'; $par[] = get('district'); }
            $w = implode(' AND ', $where);
            $total = (int)val("SELECT COUNT(*) FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE $w", $par);
            $rows = all("SELECT c.id, c.name, c.slug, c.totem, c.summary, c.verified, d.name district FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE $w ORDER BY c.name LIMIT $limit OFFSET $off", $par);
            $sizes = clan_group_counts();
            foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['verified'] = (bool)$r['verified']; $r['members'] = $sizes[$r['id']] ?? 0; }
            json_out(['data' => $rows, 'page' => $page, 'meta' => api_meta($page, $limit, $total), 'registered_total' => (int)setting('clan_total', '149')]);

        case 'districts':
            $rows = all("SELECT * FROM districts WHERE status='published' ORDER BY sort, name");
            if ($id !== '') {
                foreach ($rows as $i => $d) if ($d['slug'] === $id) {
                    $out = api_district($d, true);
                    $out['places'] = array_map(fn($h) => ['slug' => $h['slug'], 'title' => t($h, 'title'), 'category' => $h['category'], 'summary' => $h['summary']], all("SELECT slug, title, title_lg, category, summary FROM heritage_items WHERE district_id=? AND status='published' ORDER BY title", [$d['id']]));
                    $out['people'] = array_map(fn($p) => api_person($p), all("SELECT * FROM notables WHERE district_id=? AND status='published' ORDER BY sort", [$d['id']]));
                    json_out(['data' => $out]);
                }
                api_err('We could not find that district.', 404);
            }
            json_out(['data' => array_map('api_district', $rows), 'totals' => ['population' => (int)array_sum(array_column($rows, 'population')), 'area_km2' => round((float)array_sum(array_column($rows, 'area_km2')), 1)]]);

        case 'people':
            if ($id !== '') {
                $n = one("SELECT * FROM notables WHERE slug=? AND status='published'", [$id]);
                if (!$n) api_err('We could not find that person.', 404);
                json_out(['data' => api_person($n, true)]);
            }
            $cat = get('category');
            $rows = all("SELECT * FROM notables WHERE status='published'" . (isset(PEOPLE_CATS[$cat]) ? ' AND category=?' : '') . ' ORDER BY featured DESC, sort, name', isset(PEOPLE_CATS[$cat]) ? [$cat] : []);
            json_out(['data' => array_map('api_person', $rows), 'categories' => PEOPLE_CATS]);

        case 'history':
            json_out(['data' => [
                'sections' => array_map(fn($s) => ['slug' => $s['slug'], 'title' => $s['title'], 'era' => $s['era'], 'icon' => $s['icon'], 'summary' => $s['summary'], 'body_html' => rich($s['body'])], all("SELECT * FROM history_sections WHERE status='published' ORDER BY sort, id")),
                'timeline' => all('SELECT year, title, body FROM timeline ORDER BY sort, id'),
                'glossary' => all("SELECT term, meaning, note FROM glossary WHERE status='published' ORDER BY sort, term"),
            ]]);

        case 'heritage':
            if ($id !== '') {
                $h = one("SELECT h.*, d.name district FROM heritage_items h LEFT JOIN districts d ON d.id=h.district_id WHERE h.slug=? AND h.status='published'", [$id]);
                if (!$h) api_err('We could not find that item.', 404);
                json_out(['data' => ['id' => (int)$h['id'], 'slug' => $h['slug'], 'title' => t($h, 'title'), 'category' => $h['category'], 'category_label' => HERITAGE_CATS[$h['category']][0] ?? $h['category'], 'summary' => $h['summary'], 'body_html' => rich(t($h, 'body')),
                    'image' => api_abs($h['image']), 'video_url' => $h['video_url'], 'audio' => api_abs($h['audio']), 'district' => $h['district'], 'lat' => $h['lat'] !== null ? (float)$h['lat'] : null, 'lng' => $h['lng'] !== null ? (float)$h['lng'] : null, 'verified' => (bool)$h['verified']]]);
            }
            [$page, $limit, $off] = api_paging(30);
            $where = ["status='published'"]; $par = [];
            if (isset(HERITAGE_CATS[get('category')])) { $where[] = 'category=?'; $par[] = get('category'); }
            if (get('q') !== '') { $where[] = '(title LIKE ? OR summary LIKE ?)'; $l = '%' . like_escape(get('q')) . '%'; array_push($par, $l, $l); }
            $w = implode(' AND ', $where);
            $total = (int)val("SELECT COUNT(*) FROM heritage_items WHERE $w", $par);
            $rows = all("SELECT id, category, title, title_lg, slug, summary, image, lat, lng FROM heritage_items WHERE $w ORDER BY featured DESC, title LIMIT $limit OFFSET $off", $par);
            json_out(['data' => array_map(fn($h) => ['id' => (int)$h['id'], 'slug' => $h['slug'], 'title' => t($h, 'title'), 'category' => $h['category'], 'category_label' => HERITAGE_CATS[$h['category']][0] ?? $h['category'], 'summary' => $h['summary'], 'image' => api_abs($h['image'])], $rows),
                'page' => $page, 'meta' => api_meta($page, $limit, $total), 'categories' => array_map(fn($c) => $c[0], HERITAGE_CATS)]);

        case 'species':
            if ($id !== '') {
                $s = one("SELECT * FROM species WHERE slug=? AND status='published'", [$id]);
                if (!$s) api_err('We could not find that species.', 404);
                json_out(['data' => ['id' => (int)$s['id'], 'slug' => $s['slug'], 'name' => $s['name'], 'name_lg' => $s['name_lg'], 'scientific_name' => $s['scientific_name'], 'kind' => $s['kind'], 'conservation' => $s['conservation'], 'conservation_label' => CONSERVATION[$s['conservation']] ?? $s['conservation'],
                    'medicinal' => (bool)$s['medicinal'], 'uses' => $s['uses'], 'habitat' => $s['habitat'], 'body_html' => rich($s['body']), 'image' => api_abs($s['image'])]]);
            }
            $rows = all("SELECT id, name, name_lg, slug, scientific_name, kind, conservation, medicinal, image FROM species WHERE status='published' ORDER BY name");
            json_out(['data' => array_map(fn($s) => ['id' => (int)$s['id'], 'slug' => $s['slug'], 'name' => $s['name'], 'name_lg' => $s['name_lg'], 'scientific_name' => $s['scientific_name'], 'kind' => $s['kind'], 'conservation_label' => CONSERVATION[$s['conservation']] ?? $s['conservation'], 'medicinal' => (bool)$s['medicinal'], 'image' => api_abs($s['image'])], $rows)]);

        case 'library':
            $rows = all("SELECT id, title, category, year, description, downloads FROM documents WHERE status='published' ORDER BY year DESC, id DESC");
            foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['download_url'] = api_abs('download/' . $r['id']); }
            json_out(['data' => $rows]);

        case 'news':
            if ($id !== '') {
                $p = one("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.slug=? AND p.status='published' AND p.published_at<=NOW()", [$id]);
                if (!$p) api_err('We could not find that article.', 404);
                q('UPDATE posts SET views=views+1 WHERE id=?', [$p['id']]);
                json_out(['data' => api_post($p, true)]);
            }
            [$page, $limit, $off] = api_paging(15);
            $total = (int)val("SELECT COUNT(*) FROM posts WHERE status='published' AND published_at<=NOW()");
            $rows = all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.status='published' AND p.published_at<=NOW() ORDER BY p.featured DESC, p.published_at DESC LIMIT $limit OFFSET $off");
            json_out(['data' => array_map('api_post', $rows), 'page' => $page, 'meta' => api_meta($page, $limit, $total)]);

        /* ---------- language ---------- */
        case 'dictionary':
            if ($id === 'export') {
                $rows = all("SELECT lango, english, pos, category, verified, source FROM dictionary WHERE status='published' ORDER BY fold, lango");
                $ver = md5(count($rows) . '|' . (string)val("SELECT MAX(id) FROM dictionary") . '|' . (string)val("SELECT SUM(verified) FROM dictionary"));
                if (get('version') === $ver) json_out(['version' => $ver, 'unchanged' => true]);
                json_out(['version' => $ver, 'count' => count($rows), 'entries' => array_map(fn($r) => [$r['lango'], $r['english'], $r['pos'] ?: '', $r['category'] ?: '', (int)$r['verified'], str_starts_with((string)$r['source'], 'Community') ? 1 : 0], $rows)]);
            }
            [$page, $limit, $off] = api_paging(30);
            $qs = mb_substr(get('q'), 0, 80); $dir = in_array(get('dir'), ['lg', 'en'], true) ? get('dir') : 'all';
            $cat = isset(WORD_CATS[get('cat')]) ? get('cat') : '';
            $letter = preg_match('/^[a-z]$/i', get('letter')) ? strtolower(get('letter')) : '';
            $where = ["status='published'"]; $par = [];
            if ($qs !== '') {
                $l = '%' . like_escape($qs) . '%'; $f = '%' . like_escape(lango_fold($qs)) . '%';
                if ($dir === 'lg') { $where[] = '(lango LIKE ? OR fold LIKE ?)'; array_push($par, $l, $f); }
                elseif ($dir === 'en') { $where[] = 'english LIKE ?'; $par[] = $l; }
                else { $where[] = '(lango LIKE ? OR fold LIKE ? OR english LIKE ?)'; array_push($par, $l, $f, $l); }
            } elseif ($letter !== '') { $where[] = 'fold LIKE ?'; $par[] = $letter . '%'; }
            if ($cat !== '') { $where[] = 'category=?'; $par[] = $cat; }
            $w = implode(' AND ', $where);
            $order = $qs !== '' ? 'ORDER BY (lango=?) DESC, (fold=?) DESC, (english=?) DESC, (fold LIKE ?) DESC, CHAR_LENGTH(lango), lango' : 'ORDER BY fold, lango';
            $ordPar = $qs !== '' ? [$qs, lango_fold($qs), $qs, like_escape(lango_fold($qs)) . '%'] : [];
            $total = (int)val("SELECT COUNT(*) FROM dictionary WHERE $w", $par);
            $rows = all("SELECT * FROM dictionary WHERE $w $order LIMIT $limit OFFSET $off", array_merge($par, $ordPar));
            json_out(['data' => array_map('api_word', $rows), 'page' => $page, 'meta' => api_meta($page, $limit, $total), 'categories' => WORD_CATS]);

        case 'proverbs':
            $rows = all("SELECT p.*, c.name clan FROM proverbs p LEFT JOIN clans c ON c.id=p.clan_id WHERE p.status='published' ORDER BY p.id DESC LIMIT 200");
            json_out(['data' => array_map(fn($r) => ['id' => (int)$r['id'], 'lango' => $r['lango'], 'english' => $r['english'], 'meaning' => $r['meaning'], 'verified' => (bool)$r['verified'], 'credit' => $r['contributor'] ?: ($r['clan'] ? $r['clan'] . ' clan' : null)], $rows)]);

        case 'greetings':
            json_out(['data' => array_map(fn($g) => ['lango' => $g[0], 'english' => $g[1]], require ROOT . '/app/data/greetings.php'), 'source' => 'Practical Lango Grammar']);

        case 'translate':
            api_throttle('translate', 120, 600);
            $in = $m === 'POST' ? api_in() : $_GET;
            $text = mb_substr(trim((string)($in['text'] ?? '')), 0, 600);
            if ($text === '') api_err('Type something to translate.', 422, ['text' => 'Type something to translate.']);
            json_out(['data' => lango_translate($text, ($in['direction'] ?? '') === 'lango-en' ? 'lango-en' : 'en-lango'), 'note' => 'Dictionary-based translation; not a full machine-translation model.']);

        case 'assistant':
            if ($m !== 'POST') api_err('Method not allowed.', 405);
            api_throttle('assistant', 60, 600);
            $qn = mb_substr(trim((string)(api_in()['q'] ?? '')), 0, 300);
            if ($qn === '') api_err('Ask a question first.', 422, ['q' => 'Ask a question first.']);
            $r = assistant_answer($qn);
            $cid = insert('chat_log', ['question' => $qn, 'answer' => strip_tags($r['answer_html'])]);
            json_out(['data' => ['id' => $cid, 'answer_html' => $r['answer_html'], 'sources' => $r['sources']]]);

        case 'i18n':
            if ($id === 'register' && $m === 'POST') {   // the app lists its English phrases so language experts can translate them in the admin panel
                $keys = array_slice(array_filter((array)(api_in()['keys'] ?? []), fn($k) => is_string($k) && $k !== '' && mb_strlen($k) <= 400), 0, 600);
                foreach ($keys as $k) q('INSERT IGNORE INTO translations (h,k) VALUES (?,?)', [md5($k), $k]);
                json_out(['ok' => true, 'registered' => count($keys)]);
            }
            $rows = all("SELECT k, lg FROM translations WHERE lg IS NOT NULL AND lg<>''");
            json_out(['data' => array_column($rows, 'lg', 'k'), 'count' => count($rows)]);

        /* ---------- connect & support ---------- */
        case 'events':
            if ($id !== '') {
                $e = one("SELECT * FROM events WHERE slug=? AND status='published'", [$id]);
                if (!$e) api_err('We could not find that event.', 404);
                if ($sub === 'register') {
                    if ($m !== 'POST') api_err('Method not allowed.', 405);
                    api_throttle('evreg', 20, 3600);
                    $ev = api_event($e); $in = api_in();
                    if (!$ev['registration_open']) api_err('Registration is closed for this event.', 422);
                    $name = trim((string)($in['name'] ?? ($user['name'] ?? ''))); $email = strtolower(trim((string)($in['email'] ?? ($user['email'] ?? ''))));
                    $tix = max(1, min(10, (int)($in['tickets'] ?? 1)));
                    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) api_err('Please enter your name and a valid email.', 422);
                    if ($ev['places_left'] !== null && $tix > $ev['places_left']) api_err('Sorry, not enough places are left.', 422);
                    $cid = null; $cother = null;
                    if (isset($in['clan_id'])) [$cid, $cother] = read_clan_input(); elseif ($user) { $cid = $user['clan_id'] ?: null; $cother = $user['clan_other'] ?: null; }
                    $code = new_code(8);
                    insert('event_registrations', ['event_id' => $e['id'], 'name' => mb_substr($name, 0, 150), 'email' => $email, 'phone' => ($in['phone'] ?? $user['phone'] ?? null) ?: null, 'tickets' => $tix, 'clan_id' => $cid, 'clan_other' => $cother, 'code' => $code]);
                    json_out(['data' => ['code' => $code, 'tickets' => $tix, 'event' => api_event($e)]], 201);
                }
                json_out(['data' => api_event($e, true)]);
            }
            $upcoming = get('past') !== '1';
            $rows = all("SELECT * FROM events WHERE status='published' AND starts_at" . ($upcoming ? '>=CURDATE() ORDER BY starts_at' : '<CURDATE() ORDER BY starts_at DESC') . ' LIMIT 50');
            json_out(['data' => array_map('api_event', $rows)]);

        case 'professionals':
            if ($m === 'POST') {
                $u = api_need_user(); $in = api_in();
                api_throttle('prof', 6, 3600);
                $ind = trim((string)($in['industry'] ?? ''));
                if ($ind === '') api_err('Please enter your industry or profession.', 422, ['industry' => 'Please enter your industry or profession.']);
                [$cid, $cother] = isset($in['clan_id']) ? read_clan_input() : [$u['clan_id'] ?: null, $u['clan_other'] ?: null];
                insert('professionals', ['name' => $u['name'], 'email' => $u['email'], 'phone' => $u['phone'], 'clan_id' => $cid, 'clan_other' => $cother, 'industry' => mb_substr($ind, 0, 120), 'expertise' => mb_substr((string)($in['expertise'] ?? ''), 0, 190) ?: null,
                    'location' => mb_substr((string)($in['location'] ?? ''), 0, 190) ?: null, 'bio' => mb_substr((string)($in['bio'] ?? ''), 0, 2000) ?: null]);
                json_out(['ok' => true, 'message' => 'Thank you! Your profile will appear in the directory after verification.'], 201);
            }
            $where = ["p.status='published'"]; $par = [];
            if (get('q') !== '') { $where[] = '(p.name LIKE ? OR p.expertise LIKE ? OR p.industry LIKE ?)'; $l = '%' . like_escape(get('q')) . '%'; array_push($par, $l, $l, $l); }
            if (get('industry') !== '') { $where[] = 'p.industry=?'; $par[] = get('industry'); }
            $rows = all('SELECT p.id, p.name, p.industry, p.expertise, p.location, p.bio, p.photo, c.name clan FROM professionals p LEFT JOIN clans c ON c.id=p.clan_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.name LIMIT 100', $par);
            foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['photo'] = api_abs($r['photo']); }
            json_out(['data' => $rows, 'industries' => array_column(all("SELECT DISTINCT industry FROM professionals WHERE status='published' ORDER BY industry"), 'industry')]);

        case 'programs':
            if ($id !== '') {
                $p = one("SELECT * FROM programs WHERE slug=? AND status='published'", [$id]);
                if (!$p) api_err('We could not find that program.', 404);
                json_out(['data' => api_program($p, true)]);
            }
            json_out(['data' => array_map('api_program', all("SELECT * FROM programs WHERE status='published' ORDER BY featured DESC, sort, id")), 'categories' => PROGRAM_CATS]);

        case 'donations':
            if ($m !== 'POST') api_err('Method not allowed.', 405);
            api_throttle('donate', 15, 3600);
            $in = api_in();
            $methods = ['mtn_momo' => 'MTN Mobile Money', 'airtel_money' => 'Airtel Money', 'visa' => 'Visa', 'mastercard' => 'Mastercard', 'paypal' => 'PayPal', 'bank' => 'Bank transfer'];
            $amount = (float)str_replace(',', '', (string)($in['amount'] ?? '0')); $cur = ($in['currency'] ?? '') === 'USD' ? 'USD' : 'UGX';
            $min = $cur === 'UGX' ? 1000 : 1;
            $name = trim((string)($in['donor_name'] ?? ($user['name'] ?? ''))); $email = trim((string)($in['email'] ?? ($user['email'] ?? ''))); $phone = trim((string)($in['phone'] ?? ($user['phone'] ?? '')));
            $anon = !empty($in['anonymous']);
            $e = [];
            if ($amount < $min) $e['amount'] = 'Please enter an amount of at least ' . money($min, $cur) . '.';
            if ($name === '' && !$anon) $e['donor_name'] = 'Please enter your name, or choose to give anonymously.';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $e['email'] = 'That email address does not look right.';
            if ($email === '' && $phone === '') $e['email'] = 'Please give an email or phone number so we can send payment instructions.';
            if ($e) api_err(reset($e), 422, $e);
            $ref = 'LCI' . new_code(7);
            insert('donations', ['reference' => $ref, 'donor_name' => $name !== '' ? mb_substr($name, 0, 150) : 'Anonymous', 'email' => $email ?: null, 'phone' => $phone ?: null, 'country' => ($in['country'] ?? $user['country'] ?? null) ?: null,
                'amount' => $amount, 'currency' => $cur, 'method' => isset($methods[$in['method'] ?? '']) ? $in['method'] : 'mtn_momo', 'program_id' => (int)($in['program_id'] ?? 0) ?: null,
                'recurring' => in_array($in['recurring'] ?? '', ['monthly', 'yearly'], true) ? $in['recurring'] : 'once', 'anonymous' => $anon ? 1 : 0, 'message' => mb_substr((string)($in['message'] ?? ''), 0, 500) ?: null]);
            json_out(['data' => ['reference' => $ref, 'amount' => $amount, 'currency' => $cur, 'method' => $methods[$in['method'] ?? ''] ?? 'MTN Mobile Money',
                'message' => 'Your pledge has been recorded. Online payment is being connected; until then, the LCI finance office will contact you with payment instructions. Keep your reference and quote it if you pay by mobile money or bank transfer.']], 201);

        case 'transparency':
            $d = donation_totals();
            json_out(['data' => ['confirmed_donations' => (int)$d['n'], 'ugx' => (float)$d['ugx'], 'usd' => (float)$d['usd']]]);

        case 'about':
            json_out(['data' => ['name' => setting('site_name', 'Lango Cultural Institution'), 'subtitle' => setting('org_subtitle', 'Won Nyaci me Lango'), 'tagline' => setting('site_tagline'), 'mission' => setting('mission'), 'vision' => setting('vision'),
                'email' => setting('email'), 'phone' => setting('phone'), 'address' => setting('address'), 'website' => api_abs('')]]);

        case 'leaderboard':
            $rows = all("SELECT name, points FROM users WHERE points>0 AND role='contributor' ORDER BY points DESC LIMIT 10");
            json_out(['data' => array_map(fn($r, $i) => ['rank' => $i + 1, 'name' => short_person_name($r['name']), 'points' => (int)$r['points'], 'level' => contributor_level((int)$r['points'])['name']], $rows, array_keys($rows))]);
    }
}
