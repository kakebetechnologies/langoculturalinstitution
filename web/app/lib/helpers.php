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

/** Articles, profiles, documentaries and records about a notable person on other websites (we only link to them). */
function person_links(int $id): array
{
    return all("SELECT kind, title, source, url FROM notable_links WHERE notable_id=? AND status='published' ORDER BY sort, id", [$id]);
}

/**
 * Dates worth remembering in Lango and Ugandan history, each in the year and on the day it happened (from the notable people pages and the history chapters),
 * with the next time the date comes round. This fills the Events page when no event has been announced. [month, day, year, title, text]
 */
function lango_dates(int $limit = 0): array
{
    if (community() !== 'lango') return [];   // these dates are Lango's; another community has none until its own are written
    $all = [
        [2, 21, 0, 'International Mother Language Day', 'UNESCO marks the day each year for the world’s languages: a good day to learn, speak and record Leb Lango.'],
        [4, 15, 1940, 'Birth of Gen. David Oyite-Ojok', 'The Lango army officer who helped lead the 1979 war that removed Idi Amin was born in Lira District.'],
        [6, 20, 1997, 'Death of John Akii-Bua', 'Uganda’s first Olympic champion died in Kampala, aged 47, and was given a state funeral.'],
        [7, 1, 2020, 'Lira becomes a city', 'Lira Municipality became Lira City on 1 July 2020. Dokolo (2006), Otuke (2009), Alebtong and Kole (2010) and Kwania (2018) also began on 1 July.'],
        [9, 2, 1972, 'Akii-Bua wins Olympic gold', 'John Akii-Bua won the 400 metres hurdles at the Munich Games in a world record 47.82 seconds, the first Olympic gold for Uganda.'],
        [10, 9, 1962, 'Uganda’s independence', 'Uganda became independent in 1962 with Milton Obote, born in Akokoro, Apac, as its first Prime Minister.'],
        [10, 10, 2005, 'Death of Milton Obote', 'The former Prime Minister and President of Uganda died in Johannesburg, aged 79.'],
        [12, 2, 1983, 'Death of Gen. Oyite-Ojok', 'The army Chief of Staff died when his helicopter crashed in Nakasongola District.'],
        [12, 3, 1949, 'Birth of John Akii-Bua', 'The future Olympic champion was born in Lira.'],
        [12, 28, 1925, 'Birth of Milton Obote', 'Apollo Milton Obote was born at Akokoro village in Apac.'],
    ];
    $today = strtotime('today');
    $out = [];
    foreach ($all as [$m, $d, $y, $title, $text]) {
        $next = mktime(0, 0, 0, $m, $d, (int)date('Y'));
        if ($next < $today) $next = mktime(0, 0, 0, $m, $d, (int)date('Y') + 1);
        $out[] = ['month' => $m, 'day' => $d, 'year' => $y ?: null, 'title' => $title, 'text' => $text, 'next' => date('Y-m-d', $next), 'in_days' => (int)round(($next - $today) / 86400),
            'years_ago' => $y ? (int)date('Y', $next) - $y : null];
    }
    usort($out, fn($a, $b) => $a['in_days'] <=> $b['in_days']);
    return $limit > 0 ? array_slice($out, 0, $limit) : $out;
}

/** Articles, profiles, documentaries and papers about Lango people on other websites (we only link to them); shown while no news has been written. */
function web_reads(int $limit = 12): array
{
    return all("SELECT l.title, l.source, l.url, l.kind, n.name person, n.slug person_slug FROM notable_links l JOIN notables n ON n.id=l.notable_id
                WHERE l.status='published' AND n.status='published' AND l.kind IN ('article','profile','documentary','research') ORDER BY l.id DESC LIMIT " . (int)$limit);
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
    $c = community();
    if ($c !== 'lango') {   // another community: its own setting (named <code>.<key>), else what comes from the community's own record, else the site-wide one
        $own = trim((string)($all[$c . '.' . $k] ?? ''));
        if ($own !== '') return $own;
        $i = community_info();
        if ($k === 'site_name') return $i['name'];
        if ($k === 'site_tagline') return 'Everything about ' . $i['name'] . ', in one place.';
        if ($k === 'org_subtitle' || $k === 'clan_total') return $default;
        if (strncmp($k, 'bible_', 6) === 0) return $default;   // each community has its own Bible: never the Lango links, title or copyright line
    }
    $v = $all[$k] ?? '';
    return trim((string)$v) !== '' ? (string)$v : $default;
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
    $s = str_replace('{base}', BASE, (string)$s);
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
    'unknown' => 'Not assessed', 'least_concern' => 'Least concern', 'near_threatened' => 'Near threatened', 'vulnerable' => 'Vulnerable',
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
        'clans'    => (int)val("SELECT COUNT(*) FROM clans WHERE status='published'"),
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
    if (community() !== 'lango') return null;   // the emblem is Lango's
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
    $name = setting('site_name', 'Lango');
    $logo = logo_url();
    $cls = 'brand' . ($footer ? ' brand--footer' : '');
    $text = '<span class="brand__text"><b>' . e($name) . '</b>' . (setting('org_subtitle') ? '<small>' . e(setting('org_subtitle')) . '</small>' : '') . '</span>';
    if ($logo) return '<a class="' . $cls . ' brand--logo" href="' . e(url()) . '" aria-label="' . e($name) . '"><img class="brand__logo" src="' . e($logo) . '" alt="" width="42" height="60">' . $text . '</a>';
    return '<a class="' . $cls . '" href="' . e(url()) . '" aria-label="' . e($name) . '"><span class="brand__mark" aria-hidden="true">' . e(mb_strtoupper(mb_substr($name, 0, 1))) . '</span>' . $text . '</a>';
}


/* ---------- admin notifications ---------- */
/** Queue a bell notification for the admin panel. Never throws: a notification must not break a visitor's submission. */
function notify(string $type, string $title, ?string $body = null, ?string $path = null): void
{
    try {
        q('INSERT INTO notifications (type,title,body,url) VALUES (?,?,?,?)', [$type, mb_substr($title, 0, 190), $body !== null ? mb_substr($body, 0, 400) : null, $path]);
    } catch (Throwable $e) {}
}

/**
 * Called from insert(): every public submission raises a notification for the admins.
 * Rows created inside the admin panel (LCI_ADMIN) are not announced back to the admins.
 */
function notify_on_insert(string $table, array $d, int $id): void
{
    if (defined('LCI_ADMIN')) return;
    $who = (string)($d['name'] ?? $d['donor_name'] ?? 'A visitor');
    $clanOther = trim((string)($d['clan_other'] ?? $d['contributor_clan_other'] ?? ''));
    switch ($table) {
        case 'contributions':
            $word = ($d['type'] ?? '') === 'language';
            notify($word ? 'word' : 'contribution', ($word ? (($d['word_cat'] ?? '') === 'proverb' ? 'New proverb suggestion: ' : 'New word suggestion: ') : 'New contribution: ') . ($d['title'] ?? ''),
                $who . ' · ' . ($word ? 'Language' : (CONTRIB_TYPES[$d['type'] ?? 'cultural'][0] ?? 'Contribution')), $word ? "admin/?m=words&a=review&id=$id" : "admin/?m=contributions&a=review&id=$id");
            break;
        case 'messages':
            notify('message', 'New message: ' . ($d['subject'] ?? 'Contact form'), $who . (isset($d['email']) ? ' · ' . $d['email'] : ''), "admin/?m=messages&a=edit&id=$id"); break;
        case 'applications':
            $kind = ['scholarship' => 'Scholarship application', 'grant' => 'Research grant application', 'volunteer' => 'Volunteer application', 'partner' => 'Partnership request', 'clan' => 'Clan registration', 'diaspora' => 'Diaspora association'][$d['type'] ?? ''] ?? 'New application';
            notify('application', $kind . ' from ' . $who, (string)($d['organization'] ?? ''), "admin/?m=applications&a=edit&id=$id"); break;
        case 'donations':
            notify('donation', 'New donation pledge: ' . ($d['currency'] ?? 'UGX') . ' ' . number_format((float)($d['amount'] ?? 0)), $who . ' · ' . ($d['method'] ?? ''), "admin/?m=donations&a=edit&id=$id"); break;
        case 'comments':
            notify('comment', 'New comment to moderate', $who, "admin/?m=comments&a=edit&id=$id"); break;
        case 'professionals':
            notify('professional', 'Directory profile to verify: ' . $who, (string)($d['industry'] ?? ''), "admin/?m=professionals&a=edit&id=$id"); break;
        case 'event_registrations':
            notify('event', 'New event registration: ' . $who, ((int)($d['tickets'] ?? 1)) . ' ticket(s)', "admin/?m=event_registrations&a=edit&id=$id"); break;
        case 'users':
            if (($d['role'] ?? 'contributor') === 'contributor') notify('user', 'New member account: ' . $who, (string)($d['email'] ?? ''), "admin/?m=users&a=edit&id=$id");
            break;
    }
    if ($clanOther !== '' && in_array($table, ['contributions', 'applications', 'users', 'event_registrations', 'professionals'], true)
        && !val("SELECT COUNT(*) FROM notifications WHERE type='clan' AND is_read=0 AND title=?", ['A clan is missing from the register: ' . $clanOther])) {
        notify('clan', 'A clan is missing from the register: ' . $clanOther, 'Entered by ' . $who . '. Add it so others can pick it.', 'admin/?m=clan_requests&names=' . rawurlencode($clanOther));
    }
}

/* ---------- clan selection (used by every registration form) ---------- */
function clan_choices(): array
{
    static $c = null;
    return $c ??= array_column(all("SELECT id, name FROM clans WHERE status='published' ORDER BY name"), 'name', 'id');
}

/** A clan <select> with a "my clan is not listed" escape hatch that reveals a free-text field. */
function clan_select(string $id = 'clan', $selected = null, bool $required = false, string $other = ''): string
{
    $sel = (string)($selected ?? '');
    $isOther = $sel === 'other' || ($other !== '' && $sel === '');
    $h = '<div class="clanpick' . ($isOther ? ' other' : '') . '" data-clanpick><select name="clan_id" id="' . e($id) . '"' . ($required ? ' required' : '') . '>';
    $h .= '<option value="">' . e(__('Select your clan…')) . '</option>';
    foreach (clan_choices() as $cid => $name) $h .= '<option value="' . (int)$cid . '"' . ((string)$cid === $sel ? ' selected' : '') . '>' . e($name) . '</option>';
    $h .= '<option value="other"' . ($isOther ? ' selected' : '') . '>' . e(__('My clan is not listed')) . '</option>';
    $h .= '<option value="unknown"' . ($sel === 'unknown' ? ' selected' : '') . '>' . e(__('I am not sure of my clan')) . '</option></select>';
    $h .= '<input type="text" name="clan_other" maxlength="120" value="' . e($other) . '" placeholder="' . e(__('Type your clan name')) . '" aria-label="' . e(__('Your clan name')) . '"></div>';
    return $h;
}

/** Reads clan_id/clan_other from the posted form → [clan id or null, free-text clan or null]. */
function read_clan_input(): array
{
    $v = post('clan_id');
    if ($v === 'other') { $o = mb_substr(post('clan_other'), 0, 120); return [null, $o !== '' ? $o : null]; }
    $id = (int)$v;
    return [$id && val("SELECT id FROM clans WHERE id=? AND status='published'", [$id]) ? $id : null, null];
}

/* ---------- language & people vocabularies ---------- */
const WORD_CATS = [
    'animal' => 'Animals', 'bird' => 'Birds', 'plant' => 'Plants & trees', 'place' => 'Places', 'name' => 'Personal names',
    'food' => 'Food & drink', 'body' => 'Body & health', 'family' => 'Family & kinship', 'nature' => 'Nature & weather',
    'object' => 'Tools & household', 'culture' => 'Culture & ceremony', 'verb' => 'Actions', 'other' => 'Other',
];
const WORD_POS = ['noun' => 'Noun', 'verb' => 'Verb', 'adjective' => 'Adjective', 'adverb' => 'Adverb', 'phrase' => 'Phrase / saying', 'other' => 'Other'];
const PEOPLE_CATS = [
    'statesman' => 'Statesmen', 'military' => 'Soldiers', 'sports' => 'Sport', 'chief' => 'Chiefs & leaders', 'warrior' => 'Warriors',
    'public-service' => 'Public service', 'education' => 'Education', 'arts' => 'Arts & culture', 'faith' => 'Faith', 'business' => 'Business',
];

/** Lowercase and fold the special Lango letters so words typed on an ordinary keyboard still match. */
function lango_fold(string $s, bool $lower = true): string
{
    return strtr($lower ? mb_strtolower($s) : $s, ['ε' => 'e', 'ɛ' => 'e', 'ɔ' => 'o', 'ŋ' => 'ng', 'ë' => 'e', 'ö' => 'o', 'ï' => 'i', 'é' => 'e', 'è' => 'e', 'ó' => 'o', 'ò' => 'o', 'á' => 'a', 'à' => 'a', 'í' => 'i', 'ì' => 'i', 'ú' => 'u', 'ù' => 'u']);
}

/* ---------- member notifications (the mobile app) ---------- */
/** Sends Expo push messages [{to,title,body,data}]. Never throws and never waits long: a push must not slow the admin down. */
function expo_push(array $messages): void
{
    if (!$messages || !function_exists('curl_init')) return;
    try {
        $ch = curl_init('https://exp.host/--/api/v2/push/send');
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($messages, JSON_UNESCAPED_UNICODE), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json']]);
        curl_exec($ch);
        curl_close($ch);
    } catch (Throwable $e) {}
}

/** Adds a notification to a member's in-app list and pushes it to their phones. */
function user_notify(int $uid, string $title, string $body = '', array $data = [], string $type = 'info'): void
{
    try {
        q('INSERT INTO user_notifications (user_id, type, title, body, data) VALUES (?,?,?,?,?)', [$uid, $type, mb_substr($title, 0, 190), $body !== '' ? mb_substr($body, 0, 400) : null, $data ? mb_substr(json_encode($data), 0, 500) : null]);
        $tokens = array_column(all('SELECT token FROM device_tokens WHERE user_id=?', [$uid]), 'token');
        expo_push(array_map(fn($t) => ['to' => $t, 'title' => $title, 'body' => $body, 'sound' => 'default', 'channelId' => 'default', 'data' => $data], $tokens));
    } catch (Throwable $e) {}
}

/** Tells the contributor when a reviewer changes the status of their contribution. */
function contribution_status_notify(array $c, string $newStatus): void
{
    if (empty($c['user_id']) || $c['status'] === $newStatus) return;
    $word = $c['type'] === 'language';
    $notice = match ($newStatus) {
        'approved', 'published' => [$word ? 'Added to the dictionary' : 'Your contribution was approved', '“' . mb_strimwidth((string)$c['title'], 0, 60, '…') . '” ' . ($word ? 'is now in the Lango dictionary.' : 'was approved. Thank you!')],
        'rejected' => ['Your contribution was not accepted', 'Open it to read the reviewer’s note.'],
        'needs_info' => ['More information needed', 'A reviewer has a question about “' . mb_strimwidth((string)$c['title'], 0, 50, '…') . '”.'],
        'under_review' => ['Your contribution is being reviewed', '“' . mb_strimwidth((string)$c['title'], 0, 60, '…') . '” is with our reviewers.'],
        default => null,
    };
    if ($notice) user_notify((int)$c['user_id'], $notice[0], $notice[1], ['code' => $c['code']], 'contribution');
}

/** Safe local path for a "next" redirect after sign-in/registration (never an external URL). */
function safe_next(string $n): string
{
    return ($n !== '' && preg_match('#^[a-z0-9/_\-?=&%.]+$#i', $n) && !str_contains($n, '//') && !str_starts_with($n, 'http')) ? ltrim($n, '/') : '';
}
