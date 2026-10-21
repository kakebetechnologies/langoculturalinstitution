<?php
declare(strict_types=1);

/* ---------- output & URLs ---------- */
function e($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function url(string $path = '', array $query = []): string
{
    $u = BASE . '/' . ltrim($path, '/');
    return $query ? $u . '?' . http_build_query($query) : $u;
}

function asset(string $path): string
{
    $f = ROOT . '/' . ltrim($path, '/');
    return url($path) . (is_file($f) ? '?v=' . filemtime($f) : '');
}

/** URL for a stored media path (uploads/..., images/...) or absolute URL. */
function media_url(?string $p, string $fallback = 'assets/img/photos/hero-road.jpg'): string
{
    if (!$p) return url($fallback);
    if (preg_match('#^https?://#', $p)) return $p;
    return url($p);
}

function redirect(string $to): never
{
    if (!preg_match('#^https?://#', $to) && !str_starts_with($to, BASE . '/') && $to !== BASE) $to = url($to);
    header('Location: ' . $to);
    exit;
}

function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function post(string $k, $d = ''): string { return trim((string)($_POST[$k] ?? $d)); }
function get(string $k, $d = ''): string { return trim((string)($_GET[$k] ?? $d)); }
function client_ip(): string { return (string)($_SERVER['REMOTE_ADDR'] ?? ''); }

function json_out($data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}

/* ---------- CSRF, flash, old input ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void
{
    $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $s = (string)($_SESSION['csrf'] ?? '');
    if ($s === '' || !is_string($t) || !hash_equals($s, $t)) {
        http_response_code(403);
        exit('Your session expired. Please go back, refresh the page and try again.');
    }
}

function flash(?string $msg = null, string $type = 'success')
{
    if ($msg !== null) { $_SESSION['flash'][] = [$type, $msg]; return null; }
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
function flash_html(): string
{
    $out = '';
    foreach (flash() as [$t, $m]) $out .= '<div class="alert alert-' . e($t) . '" role="status">' . e($m) . '</div>';
    return $out;
}
function keep_old(array $data): void { $_SESSION['old'] = $data; }
function old(string $k, $d = '')
{
    return $_SESSION['old'][$k] ?? $d;
}
function clear_old(): void { unset($_SESSION['old']); }

/* ---------- settings ---------- */
function setting(string $k, string $default = ''): string
{
    static $all = null;
    if ($all === null) {
        try { $all = array_column(all('SELECT k,v FROM settings'), 'v', 'k'); } catch (Throwable $e) { $all = []; }
    }
    $v = $all[$k] ?? '';
    return $v !== '' ? (string)$v : $default;
}

/* ---------- text & dates ---------- */
function slugify(string $s): string
{
    $s = mb_strtolower($s);
    $s = preg_replace('~[^\p{L}\p{N}]+~u', '-', $s);
    $s = trim((string)$s, '-');
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($t !== false && $t !== '') $s = preg_replace('~[^a-z0-9-]+~', '', strtolower($t));
    return $s !== '' ? $s : 'item-' . substr(bin2hex(random_bytes(3)), 0, 6);
}

function unique_slug(string $table, string $slug, int $ignoreId = 0): string
{
    $base = $slug; $i = 2;
    while (val("SELECT COUNT(*) FROM `$table` WHERE slug=? AND id<>?", [$slug, $ignoreId])) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

function excerpt(?string $html, int $len = 160): string
{
    $t = trim(preg_replace('/\s+/', ' ', strip_tags((string)$html)));
    if (mb_strlen($t) <= $len) return $t;
    $cut = mb_substr($t, 0, $len - 1);
    $sp = mb_strrpos($cut, ' ');
    if ($sp !== false && $sp > $len * 0.6) $cut = mb_substr($cut, 0, $sp);
    return rtrim($cut, " ,;:-–—") . '…';
}

function fmt_date(?string $d, string $fmt = 'j M Y'): string
{
    if (!$d || str_starts_with($d, '0000')) return '';
    return date($fmt, strtotime($d));
}

function money(float $n, string $cur = 'UGX'): string
{
    return $cur . ' ' . number_format($n, $cur === 'UGX' ? 0 : 2);
}

/** Allow-list HTML sanitiser for admin-authored rich text. */
function clean_html(?string $html): string
{
    $html = (string)$html;
    if (trim($html) === '') return '';
    $html = strip_tags($html, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><blockquote><a><img><figure><figcaption><hr>');
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?><div id="r">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $dom->getElementsByTagName('div')->item(0);
    $xp = new DOMXPath($dom);
    foreach ($xp->query('//*[@*]') as $n) {
        if ($n === $root) continue;
        foreach (iterator_to_array($n->attributes) as $a) {
            $name = strtolower($a->name); $v = trim($a->value);
            $ok = in_array($name, ['href', 'src', 'alt', 'title', 'target', 'rel'], true);
            if ($ok && in_array($name, ['href', 'src'], true) && !preg_match('#^(https?:|mailto:|/|\#|uploads/|images/|assets/)#i', $v)) $ok = false;
            if (!$ok) $n->removeAttribute($a->name);
        }
        if ($n->nodeName === 'a' && $n->getAttribute('target') === '_blank') $n->setAttribute('rel', 'noopener');
    }
    $out = '';
    if ($root) foreach ($root->childNodes as $c) $out .= $dom->saveHTML($c);
    return $out;
}

/** Render a stored body: HTML if it contains tags, otherwise paragraphs. */
function rich(?string $s): string
{
    $s = (string)$s;
    if (trim($s) === '') return '';
    if ($s !== strip_tags($s)) return clean_html($s);
    $paras = preg_split('/\n{2,}/', trim($s));
    return implode('', array_map(fn($p) => '<p>' . nl2br(e($p)) . '</p>', $paras));
}

function embed_url(?string $u): ?string
{
    if (!$u) return null;
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{6,})~', $u, $m)) return 'https://www.youtube.com/embed/' . $m[1];
    if (preg_match('~vimeo\.com/(\d+)~', $u, $m)) return 'https://player.vimeo.com/video/' . $m[1];
    return null;
}

/* ---------- pagination ---------- */
function paginate(int $total, int $per, ?int $page = null): array
{
    $pages = max(1, (int)ceil($total / $per));
    $page = max(1, min($pages, $page ?? (int)get('page', '1')));
    return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $per, 'per' => $per, 'total' => $total];
}

function pager(array $p, ?string $base = null): string
{
    if ($p['pages'] <= 1) return '';
    $q = $_GET; unset($q['page']);
    $base = $base ?? strtok($_SERVER['REQUEST_URI'], '?');
    $link = fn(int $n) => e($base . '?' . http_build_query($q + ['page' => $n]));
    $h = '<nav class="pager" aria-label="Pagination">';
    if ($p['page'] > 1) $h .= '<a href="' . $link($p['page'] - 1) . '" rel="prev">‹</a>';
    $from = max(1, $p['page'] - 2); $to = min($p['pages'], $p['page'] + 2);
    if ($from > 1) $h .= '<a href="' . $link(1) . '">1</a>' . ($from > 2 ? '<span>…</span>' : '');
    for ($i = $from; $i <= $to; $i++) $h .= $i === $p['page'] ? '<span class="on">' . $i . '</span>' : '<a href="' . $link($i) . '">' . $i . '</a>';
    if ($to < $p['pages']) $h .= ($to < $p['pages'] - 1 ? '<span>…</span>' : '') . '<a href="' . $link($p['pages']) . '">' . $p['pages'] . '</a>';
    if ($p['page'] < $p['pages']) $h .= '<a href="' . $link($p['page'] + 1) . '" rel="next">›</a>';
    return $h . '</nav>';
}

/* ---------- shared vocabularies ---------- */
const HERITAGE_CATS = [
    'ceremony'     => ['Ceremonies & Traditions', 'ceremonies'],
    'attire'       => ['Traditional Attire', 'attire'],
    'craft'        => ['Crafts', 'crafts'],
    'music'        => ['Music', 'music'],
    'dance'        => ['Dance', 'dance'],
    'storytelling' => ['Storytelling', 'storytelling'],
    'symbol'       => ['Cultural Symbols', 'symbols'],
    'site'         => ['Heritage Sites', 'sites'],
];
const CONTRIB_TYPES = [
    'cultural'       => ['Cultural observation', 'A ceremony, tradition, craft, dance or custom'],
    'natural'        => ['Natural heritage', 'A plant, animal, bird or habitat'],
    'language'       => ['Language', 'A word, proverb, pronunciation or translation'],
    'oral_history'   => ['Oral history', 'An elder telling a story, history or legend'],
    'document'       => ['Historical document', 'An old letter, photograph or artefact'],
    'heritage_site'  => ['Heritage site', 'A sacred grove, monument, palace site or landmark'],
];
const CONTRIB_STATUS = [
    'pending' => 'Pending', 'under_review' => 'Under review', 'needs_info' => 'More information needed',
    'approved' => 'Approved', 'published' => 'Published', 'rejected' => 'Not accepted',
];
const PROGRAM_CATS = [
    'heritage' => 'Cultural Heritage', 'education' => 'Education', 'youth' => 'Youth Empowerment',
    'conservation' => 'Conservation', 'development' => 'Community Development', 'digital' => 'Digital Innovation',
];
const CONSERVATION = [
    'unknown' => 'Not assessed', 'least_concern' => 'Least concern', 'vulnerable' => 'Vulnerable',
    'endangered' => 'Endangered', 'critical' => 'Critically endangered', 'locally_extinct' => 'Locally extinct',
    'reintroduced' => 'Reintroduced',
];

function badge_for_points(int $pts): array
{
    $tiers = [[500, 'Heritage Guardian'], [200, 'Conservation Hero'], [100, 'Language Champion'], [50, 'Community Voice'], [10, 'First Contributor']];
    foreach ($tiers as [$min, $name]) if ($pts >= $min) return [$name, $min];
    return ['New member', 0];
}

function new_code(int $len = 8): string
{
    $a = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $s = '';
    for ($i = 0; $i < $len; $i++) $s .= $a[random_int(0, strlen($a) - 1)];
    return $s;
}

/** Simple per-session rate limit for public forms. */
function throttle(string $key, int $max = 6, int $window = 600): bool
{
    $now = time();
    $hits = array_filter($_SESSION['thr'][$key] ?? [], fn($t) => $t > $now - $window);
    if (count($hits) >= $max) return false;
    $hits[] = $now;
    $_SESSION['thr'][$key] = $hits;
    return true;
}

function site_counts(): array
{
    static $c = null;
    if ($c) return $c;
    $words = (int)val("SELECT COUNT(*) FROM dictionary WHERE status='published'");
    $items = (int)val("SELECT COUNT(*) FROM heritage_items WHERE status='published'")
           + (int)val("SELECT COUNT(*) FROM media WHERE status='published'")
           + (int)val("SELECT COUNT(*) FROM documents WHERE status='published'");
    return $c = [
        'clans'    => (int)setting('clan_total', '149'),
        'districts'=> (int)val("SELECT COUNT(*) FROM districts WHERE status='published'") ?: 9,
        'words'    => $words,
        'archived' => $items,
        'species'  => (int)val("SELECT COUNT(*) FROM species WHERE status='published'")
                    + (int)val("SELECT COUNT(*) FROM contributions WHERE type='natural' AND status='published'"),
        'contrib'  => (int)val("SELECT COUNT(*) FROM contributions WHERE status='published'"),
    ];
}


/* ---------- branding ---------- */
/** URL of the uploaded logo (Admin → Site settings) or a file dropped at assets/img/logo.{png,webp,jpg,svg}; null if none yet. */
function logo_url(): ?string
{
    $l = setting('logo');
    if ($l && is_file(ROOT . '/' . $l)) return url($l);
    foreach (['png', 'webp', 'jpg', 'jpeg', 'svg'] as $ext) if (is_file(ROOT . '/assets/img/logo.' . $ext)) return asset('assets/img/logo.' . $ext);
    return null;
}

/** Configurable site photo (hero, about, banner) with a bundled default. */
function site_img(string $key, string $default): string
{
    $v = setting($key);
    return ($v && is_file(ROOT . '/' . $v)) ? url($v) : asset($default);
}

function brand_html(bool $footer = false): string
{
    $name = setting('site_name', 'Lango Cultural Institution');
    $logo = logo_url();
    $cls = 'brand' . ($footer ? ' brand--footer' : '');
    $text = '<span class="brand__text"><b>' . e($name) . '</b><small>' . e(setting('org_subtitle', 'Won Nyaci me Lango')) . '</small></span>';
    if ($logo) return '<a class="' . $cls . ' brand--logo" href="' . e(url()) . '" aria-label="' . e($name) . '"><img class="brand__logo" src="' . e($logo) . '" alt="" width="57" height="80">' . $text . '</a>';
    return '<a class="' . $cls . '" href="' . e(url()) . '" aria-label="' . e($name) . '"><span class="brand__mark" aria-hidden="true">LCI</span>' . $text . '</a>';
}
