<?php
declare(strict_types=1);

/* Public, read-only JSON API (/api/v1/...) + cultural assistant endpoints (/api/assistant). */
require_once ROOT . '/app/lib/app_api.php';

/* CORS preflight, for the installable web version (PWA) of the Lango app */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, X-Auth-Token, X-App-Lang');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 86400');
    http_response_code(204);
    exit;
}

$a = $seg[1] ?? '';
$res = $seg[2] ?? '';

/* ----- assistant ----- */
if ($a === 'assistant') {
    if (!is_post()) json_out(['error' => 'POST required'], 405);
    $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
    $tok = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sess = (string)($_SESSION['csrf'] ?? '');
    if ($sess === '' || !hash_equals($sess, (string)$tok)) json_out(['error' => 'Invalid session'], 403);
    if (($res ?? '') === 'flag') {
        q('UPDATE chat_log SET flagged=1 WHERE id=?', [(int)($body['id'] ?? 0)]);
        json_out(['ok' => true]);
    }
    if (!throttle('assistant', 30, 600)) json_out(['answer' => __('You are asking very quickly — please wait a moment.'), 'sources' => []], 429);
    $qn = mb_substr(trim((string)($body['q'] ?? '')), 0, 300);
    if ($qn === '') json_out(['answer' => '', 'sources' => []]);
    $r = assistant_answer($qn);
    $id = insert('chat_log', ['question' => $qn, 'answer' => strip_tags($r['answer_html'])]);
    json_out(['id' => $id, 'answer_html' => $r['answer_html'], 'sources' => $r['sources']]);
}

/* ----- translator (used by the translate page) ----- */
if ($a === 'translate') {
    if (!is_post()) json_out(['error' => 'POST required'], 405);
    $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
    $sess = (string)($_SESSION['csrf'] ?? '');
    if ($sess === '' || !hash_equals($sess, (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) json_out(['error' => 'Invalid session'], 403);
    if (!throttle('translate', 60, 600)) json_out(['error' => __('You are translating very quickly — please wait a moment.')], 429);
    $text = mb_substr(trim((string)($body['text'] ?? '')), 0, 600);
    if ($text === '') json_out(['error' => 'No text provided.'], 400);
    json_out(lango_translate($text, ($body['direction'] ?? '') === 'lango-en' ? 'lango-en' : 'en-lango'));
}

if ($a !== 'v1') json_out(['name' => 'LCI Open Data API', 'version' => 'v1', 'docs' => url('developers')], $a === '' ? 200 : 404);

api_app($res, $seg);   // the mobile app's endpoints (token auth, profile, contributions, content); exits when it handles the request

$limit = max(1, min(100, (int)get('limit', '25')));
$page = max(1, (int)get('page', '1'));
$off = ($page - 1) * $limit;
$like = '%' . like_escape(get('q')) . '%';
$absolute = fn(?string $p) => $p ? (preg_match('#^https?://#', $p) ? $p : ((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url($p))) : null;

switch ($res) {
    case 'stats':
        json_out(['data' => site_counts()]);
    case 'news':
        $rows = all("SELECT title, title_lg, slug, excerpt, excerpt_lg, image, author_name, published_at FROM posts WHERE status='published' AND published_at<=NOW() ORDER BY published_at DESC LIMIT $limit OFFSET $off");
        foreach ($rows as &$r) { $r['image'] = $absolute($r['image']); $r['url'] = $absolute('news/' . $r['slug']); }
        json_out(['data' => $rows, 'page' => $page]);
    case 'events':
        $rows = all("SELECT title, title_lg, slug, summary, location, starts_at, ends_at, image FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT $limit OFFSET $off");
        foreach ($rows as &$r) { $r['image'] = $absolute($r['image']); $r['url'] = $absolute('events/' . $r['slug']); }
        json_out(['data' => $rows, 'page' => $page]);
    case 'clans':
        $rows = all("SELECT c.name, c.slug, c.totem, c.totem_type, d.name district, c.summary, c.leader_name, c.leader_title, c.verified FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE c.status='published' AND (c.name LIKE ? OR c.totem LIKE ?) ORDER BY c.name LIMIT $limit OFFSET $off", [$like, $like]);
        json_out(['data' => $rows, 'page' => $page]);
    case 'dictionary':
        $rows = all("SELECT lango, english, pos, example_lg, example_en, verified FROM dictionary WHERE status='published' AND (lango LIKE ? OR english LIKE ?) ORDER BY (lango=?) DESC, CHAR_LENGTH(lango) LIMIT $limit OFFSET $off", [$like, $like, get('q')]);
        json_out(['data' => $rows, 'page' => $page]);
    case 'translate':
        $text = mb_substr(trim(get('text')), 0, 600);
        if ($text === '') json_out(['error' => 'Provide ?text=… and optionally &direction=en-lango|lango-en'], 400);
        json_out(['data' => lango_translate($text, get('direction') === 'lango-en' ? 'lango-en' : 'en-lango'), 'note' => 'Dictionary-based translation; not a full machine-translation model.']);
    case 'districts':
        $rows = all("SELECT name, slug, kind, headquarters, population, pop_year, area_km2, created_year, known_for, lat, lng FROM districts WHERE status='published' ORDER BY sort");
        foreach ($rows as &$r) { $r['known_for'] = array_values(array_filter(array_map('trim', explode("\n", (string)$r['known_for'])))); $r['url'] = $absolute('districts/' . $r['slug']); }
        json_out(['data' => $rows]);
    case 'people':
        $rows = all("SELECT name, slug, category, title, born, died, summary, photo FROM notables WHERE status='published' ORDER BY featured DESC, sort LIMIT $limit OFFSET $off");
        foreach ($rows as &$r) { $r['photo'] = $absolute($r['photo']); $r['url'] = $absolute('people/' . $r['slug']); }
        json_out(['data' => $rows, 'page' => $page]);
    case 'word-of-the-day':
        $w = word_of_day();
        json_out(['data' => $w ? ['lango' => $w['lango'], 'english' => $w['english'], 'pos' => $w['pos']] : null]);
    case 'proverbs':
        json_out(['data' => all("SELECT lango, english, meaning, verified FROM proverbs WHERE status='published' ORDER BY id DESC LIMIT $limit OFFSET $off")]);
    case 'heritage':
        $cat = get('category');
        $rows = all("SELECT category, title, title_lg, slug, summary, image, lat, lng, verified FROM heritage_items WHERE status='published'" . (isset(HERITAGE_CATS[$cat]) ? ' AND category=?' : '') . " ORDER BY title LIMIT $limit OFFSET $off", isset(HERITAGE_CATS[$cat]) ? [$cat] : []);
        foreach ($rows as &$r) { $r['image'] = $absolute($r['image']); $r['url'] = $absolute('heritage/item/' . $r['slug']); }
        json_out(['data' => $rows, 'page' => $page]);
    case 'species':
        $rows = all("SELECT name, name_lg, slug, scientific_name, kind, conservation, medicinal, image FROM species WHERE status='published' ORDER BY name LIMIT $limit OFFSET $off");
        foreach ($rows as &$r) $r['image'] = $absolute($r['image']);
        json_out(['data' => $rows, 'page' => $page]);
    case 'programs':
        $rows = all("SELECT title, title_lg, slug, category, summary, progress, image FROM programs WHERE status='published' ORDER BY sort LIMIT $limit OFFSET $off");
        foreach ($rows as &$r) $r['image'] = $absolute($r['image']);
        json_out(['data' => $rows, 'page' => $page]);
    default:
        json_out(['error' => 'Unknown resource', 'docs' => url('developers')], 404);
}
