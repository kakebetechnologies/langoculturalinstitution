<?php
declare(strict_types=1);

/* ==========================================================================
   Admin helpers: URLs, permissions, layout and the generic CRUD engine
   ========================================================================== */

function aurl(string $m = '', string $a = '', array $q = []): string
{
    $p = array_filter(['m' => $m, 'a' => $a] + $q, fn($v) => $v !== '' && $v !== null);
    return url('admin/') . ($p ? '?' . http_build_query($p) : '');
}

function modules(): array { static $m = null; return $m ??= require __DIR__ . '/modules.php'; }

const ADM_ALL = ['super_admin', 'admin'];
const ADM_CONTENT = ['super_admin', 'admin', 'editor'];
const ADM_MOD = ['super_admin', 'admin', 'moderator'];
const ADM_STAFF = ['super_admin', 'admin', 'editor', 'moderator'];

function special_modules(): array
{
    return [
        'dashboard'     => ['Dashboard', 'home', ['super_admin', 'admin', 'editor', 'moderator', 'clan_leader'], 'Overview'],
        'notifications' => ['Notifications', 'bell', ADM_STAFF, 'Overview'],
        'contributions' => ['Contributions', 'camera', array_merge(ADM_MOD, ['clan_leader']), 'Moderation'],
        'words'         => ['Word suggestions', 'book', ADM_STAFF, 'Moderation'],
        'clan_quick'    => ['Add clans in bulk', 'plus', ADM_CONTENT, 'Heritage'],
        'settings'      => ['Site settings', 'settings', ADM_ALL, 'System'],
        'translations'  => ['Translations', 'globe', ['super_admin', 'admin', 'editor'], 'System'],
        'audit'         => ['Audit log', 'list', ADM_ALL, 'System'],
        'backup'        => ['Backup & export', 'db', ADM_ALL, 'System'],
        'profile'       => ['My profile', 'user', ['super_admin', 'admin', 'editor', 'moderator', 'clan_leader'], null],
    ];
}

function can_module(string $key): bool
{
    $u = user();
    if (!$u) return false;
    $mods = modules();
    $roles = $mods[$key]['roles'] ?? (special_modules()[$key][2] ?? []);
    return in_array($u['role'], $roles, true);
}

function badge_count(string $key): int
{
    static $c = null;
    if ($c === null) {
        $c = [
            'contributions' => (int)val("SELECT COUNT(*) FROM contributions WHERE status IN ('pending','under_review')"),
            'comments' => (int)val("SELECT COUNT(*) FROM comments WHERE status='pending'"),
            'messages' => (int)val("SELECT COUNT(*) FROM messages WHERE status='new'"),
            'applications' => (int)val("SELECT COUNT(*) FROM applications WHERE status='new'"),
            'professionals' => (int)val("SELECT COUNT(*) FROM professionals WHERE status='pending'"),
            'donations' => (int)val("SELECT COUNT(*) FROM donations WHERE status='pledged'"),
            'words' => (int)val("SELECT COUNT(*) FROM contributions WHERE type='language' AND status IN ('pending','under_review','needs_info')"),
            'notifications' => (int)val('SELECT COUNT(*) FROM notifications WHERE is_read=0'),
        ];
    }
    return $c[$key] ?? 0;
}

function admin_start(string $title, string $active, string $actions = ''): void
{
    $u = user();
    $groups = [];
    foreach (special_modules() as $k => [$label, $ic, $roles, $grp]) {
        if ($grp && can_module($k)) $groups[$grp][] = [$k, $label, $ic];
    }
    foreach (modules() as $k => $m) {
        if (can_module($k)) $groups[$m['group']][] = [$k, $m['label'], $m['icon']];
    }
    $order = ['Overview', 'Moderation', 'Content', 'Heritage', 'Language', 'Community', 'System'];
    $bellOn = in_array($u['role'], ADM_STAFF, true);
    $bellItems = $bellOn ? notif_items(7) : [];
    $bellN = $bellOn ? badge_count('notifications') : 0;
    ?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · LCI Admin</title><meta name="robots" content="noindex">
<link rel="icon" href="<?= e(logo_url() ?: asset('assets/img/favicon.svg')) ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head><body>
<aside class="side" id="side">
  <a class="side__brand" href="<?= e(aurl()) ?>"><?= logo_url() ? '<img class="mark-img" src="' . e(logo_url()) . '" alt="">' : '<span class="mark">LCI</span>' ?><span><b>LCI Admin</b><small><?= e(setting('site_name', 'Lango Cultural Institution')) ?></small></span></a>
  <nav>
    <?php foreach ($order as $g): if (empty($groups[$g])) continue; ?>
      <div class="side__group"><?= e($g) ?></div>
      <?php foreach ($groups[$g] as [$k, $label, $ic]): $n = $k === 'contributions' ? badge_count('contributions') : badge_count($k); ?>
        <a href="<?= e(aurl($k)) ?>" class="<?= $active === $k ? 'on' : '' ?>"><?= icon($ic) ?> <span><?= e($label) ?></span><?php if ($n): ?><em><?= $n ?></em><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
</aside>
<div class="main">
  <header class="top">
    <button class="top__burger" id="sideBtn" aria-label="Menu"><?= icon('menu') ?></button>
    <h1><?= e($title) ?></h1>
    <div class="top__actions"><?= $actions ?></div>
    <?php if ($bellOn): ?>
    <div class="bell" id="bell" data-poll="<?= e(aurl('notifications', 'poll')) ?>">
      <button type="button" class="bell__btn" id="bellBtn" aria-label="Notifications" aria-expanded="false" aria-controls="bellMenu"><?= icon('bell') ?><em id="bellN"<?= $bellN ? '' : ' hidden' ?>><?= $bellN > 99 ? '99+' : $bellN ?></em></button>
      <div class="bell__menu" id="bellMenu" hidden>
        <div class="bell__head"><b>Notifications</b><form method="post" action="<?= e(aurl('notifications', 'read_all')) ?>"><?= csrf_field() ?><button class="linkbtn">Mark all read</button></form></div>
        <div class="bell__list" id="bellList"><?= notif_list_html($bellItems) ?></div>
        <a class="bell__all" href="<?= e(aurl('notifications')) ?>">See all notifications</a>
      </div>
    </div>
    <?php endif; ?>
    <div class="top__user">
      <a href="<?= e(url()) ?>" target="_blank" class="btn btn--sm btn--line"><?= icon('external') ?> View site</a>
      <a href="<?= e(aurl('profile')) ?>" class="who"><span class="av"><?= e(mb_strtoupper(mb_substr($u['name'], 0, 1))) ?></span><span><?= e($u['name']) ?><small><?= e(str_replace('_', ' ', $u['role'])) ?></small></span></a>
      <form method="post" action="<?= e(aurl('', 'logout')) ?>"><?= csrf_field() ?><button class="btn btn--sm btn--line" title="Sign out"><?= icon('logout') ?></button></form>
    </div>
  </header>
  <main class="content">
    <?= flash_html() ?>
<?php
}

/* ---------- notifications (bell + page) ---------- */
const NOTIF_ICONS = ['contribution' => 'camera', 'word' => 'book', 'message' => 'inbox', 'application' => 'file', 'donation' => 'dollar', 'comment' => 'chat',
    'professional' => 'users', 'event' => 'calendar', 'user' => 'user', 'clan' => 'flag'];

function time_ago(string $dt): string
{
    $s = max(0, time() - strtotime($dt));
    if ($s < 60) return 'just now';
    if ($s < 3600) return floor($s / 60) . ' min ago';
    if ($s < 86400) return floor($s / 3600) . ' h ago';
    if ($s < 172800) return 'yesterday';
    return fmt_date($dt, 'j M');
}

function notif_items(int $limit = 7): array
{
    return all('SELECT * FROM notifications ORDER BY is_read ASC, id DESC LIMIT ' . (int)$limit);
}

function notif_list_html(array $items): string
{
    if (!$items) return '<p class="bell__empty">You are all caught up.</p>';
    $h = '';
    foreach ($items as $n) {
        $h .= '<a class="bell__item' . ($n['is_read'] ? '' : ' new') . '" href="' . e(aurl('notifications', 'go', ['id' => $n['id']])) . '"><span class="bell__ic">' . icon(NOTIF_ICONS[$n['type']] ?? 'bell') . '</span>'
            . '<span><b>' . e($n['title']) . '</b>' . ($n['body'] ? '<small>' . e($n['body']) . '</small>' : '') . '<small class="when">' . e(time_ago($n['created_at'])) . '</small></span></a>';
    }
    return $h;
}

function admin_end(array $scripts = []): void
{
    ?>
  </main>
</div>
<script>window.LCI={base:<?= json_encode(BASE) ?>,csrf:<?= json_encode(csrf_token()) ?>};</script>
<script src="<?= e(asset('assets/js/admin.js')) ?>"></script>
</body></html>
<?php
}

function deny(): never
{
    http_response_code(403);
    admin_start('Access denied', '');
    echo '<div class="panel"><h2>You do not have access to this section.</h2><p>Ask a super administrator to change your role if you need it.</p><a class="btn btn--primary" href="' . e(aurl()) . '">Back to dashboard</a></div>';
    admin_end();
    exit;
}

/* ---------- option lookups ---------- */
function field_options(array $f): array
{
    $o = $f['options'] ?? [];
    if (is_array($o)) return $o;
    static $cache = [];
    if (isset($cache[$o])) return $cache[$o];
    [$table, $idc, $lab] = explode(':', $o);
    $rows = all("SELECT `$idc` id, `$lab` label FROM `$table` ORDER BY `$lab`" . ($table === 'dictionary' ? ' LIMIT 500' : ''));
    return $cache[$o] = array_column($rows, 'label', 'id');
}

function local_dt(?string $v): string { return $v && !str_starts_with($v, '0000') ? date('Y-m-d\TH:i', strtotime($v)) : ''; }

/* ---------- field renderers ---------- */
function render_field(string $k, array $f, $val, array $row = []): string
{
    $type = $f['type'] ?? 'text';
    $label = $f['label'] ?? ucfirst(str_replace('_', ' ', $k));
    $req = !empty($f['required']) ? ' required' : '';
    $help = !empty($f['help']) ? '<small>' . e($f['help']) . '</small>' : '';
    $cls = 'field' . (in_array($type, ['textarea', 'rich', 'image', 'file']) || !empty($f['full']) ? ' full' : '');
    $id = 'f_' . $k;
    $lab = '<label for="' . $id . '">' . e($label) . (!empty($f['required']) ? ' <b>*</b>' : '') . '</label>';
    $val = $val ?? ($f['default'] ?? '');
    if (!empty($f['readonly'])) {
        $show = $type === 'select' ? (field_options($f)[$val] ?? $val) : $val;
        return '<div class="' . $cls . '">' . $lab . '<div class="ro">' . (($f['type'] ?? '') === 'textarea' ? nl2br(e($show)) : e((string)$show)) . '</div></div>';
    }
    $in = '';
    switch ($type) {
        case 'textarea':
            $in = '<textarea id="' . $id . '" name="' . $k . '"' . $req . ' rows="' . (int)($f['rows'] ?? 4) . '">' . e($val) . '</textarea>'; break;
        case 'rich':
            $in = '<div class="rte"><div class="rte__bar"><button type="button" data-cmd="bold" title="Bold"><b>B</b></button><button type="button" data-cmd="italic" title="Italic"><i>I</i></button><button type="button" data-cmd="formatBlock" data-val="h2">H2</button><button type="button" data-cmd="formatBlock" data-val="h3">H3</button><button type="button" data-cmd="formatBlock" data-val="p">¶</button><button type="button" data-cmd="insertUnorderedList">• List</button><button type="button" data-cmd="insertOrderedList">1. List</button><button type="button" data-cmd="formatBlock" data-val="blockquote">❝</button><button type="button" data-cmd="createLink">Link</button><button type="button" data-cmd="removeFormat">Clear</button></div><div class="rte__body" contenteditable="true">' . rich((string)$val) . '</div><textarea hidden name="' . $k . '" id="' . $id . '">' . e((string)$val) . '</textarea></div>'; break;
        case 'select':
            $opts = field_options($f);
            $in = '<select id="' . $id . '" name="' . $k . '"' . $req . '>' . (empty($f['required']) ? '<option value="">—</option>' : '');
            foreach ($opts as $ov => $ol) $in .= '<option value="' . e((string)$ov) . '"' . ((string)$ov === (string)$val ? ' selected' : '') . '>' . e((string)$ol) . '</option>';
            $in .= '</select>'; break;
        case 'bool':
            return '<div class="field check"><label><input type="checkbox" name="' . $k . '" value="1"' . ($val ? ' checked' : '') . '> ' . e($label) . '</label>' . $help . '</div>';
        case 'image':
        case 'file':
            $cur = (string)($row[$k] ?? '');
            $prev = '';
            if ($cur) $prev = ($type === 'image' ? '<img class="thumb" src="' . e(media_url($cur)) . '" alt="">' : '<a href="' . e(media_url($cur)) . '" target="_blank">' . e(basename($cur)) . '</a>') . ' <label class="inl"><input type="checkbox" name="remove_' . $k . '" value="1"> remove</label>';
            $in = '<div class="upl">' . $prev . '<input type="file" id="' . $id . '" name="' . $k . '"' . ($type === 'image' ? ' accept="image/*"' : '') . '></div>'; break;
        case 'datetime':
            $in = '<input type="datetime-local" id="' . $id . '" name="' . $k . '" value="' . e(local_dt((string)$val)) . '"' . $req . '>'; break;
        case 'date':
            $in = '<input type="date" id="' . $id . '" name="' . $k . '" value="' . e((string)$val) . '"' . $req . '>'; break;
        case 'number':
            $in = '<input type="number" id="' . $id . '" name="' . $k . '" value="' . e((string)$val) . '" step="' . e((string)($f['step'] ?? '1')) . '"' . (isset($f['min']) ? ' min="' . $f['min'] . '"' : '') . (isset($f['max']) ? ' max="' . $f['max'] . '"' : '') . $req . '>'; break;
        case 'password':
            $in = '<input type="password" id="' . $id . '" name="' . $k . '" autocomplete="new-password" minlength="8"' . (empty($row['id']) ? ' required' : '') . ' placeholder="' . (empty($row['id']) ? '' : 'leave blank to keep current') . '">'; break;
        case 'slug':
            $in = '<input type="text" id="' . $id . '" name="' . $k . '" value="' . e((string)$val) . '" data-slug-from="' . e((string)($f['from'] ?? 'title')) . '" placeholder="auto-generated from ' . e((string)($f['from'] ?? 'title')) . '">'; break;
        default:
            $in = '<input type="' . ($type === 'url' ? 'url' : ($type === 'email' ? 'email' : 'text')) . '" id="' . $id . '" name="' . $k . '" value="' . e((string)$val) . '"' . $req . (isset($f['max']) ? ' maxlength="' . (int)$f['max'] . '"' : '') . '>';
    }
    return '<div class="' . $cls . '">' . $lab . $in . $help . '</div>';
}

function render_cell(string $k, array $f, array $row): string
{
    $v = $row[$k] ?? null;
    $type = $f['type'] ?? 'text';
    if ($v === null || $v === '') return '<span class="dim">—</span>';
    switch ($type) {
        case 'select':
            $lab = field_options($f)[$v] ?? $v;
            if (in_array($k, ['status', 'role', 'currency'], true) || !empty($f['badge'])) {
                $good = ['published', 'approved', 'confirmed', 'accepted', 'replied', 'read'];
                $bad = ['rejected', 'failed', 'declined'];
                $c = in_array($v, $good, true) ? 'ok' : (in_array($v, $bad, true) ? 'bad' : 'warn');
                return '<span class="chip ' . $c . '">' . e((string)$lab) . '</span>';
            }
            return e((string)$lab);
        case 'bool': return $v ? '<span class="chip ok">✓</span>' : '<span class="dim">—</span>';
        case 'image': return '<img class="thumb s" src="' . e(media_url((string)$v)) . '" alt="">';
        case 'file': return '<a href="' . e(media_url((string)$v)) . '" target="_blank">file</a>';
        case 'datetime': return e(fmt_date((string)$v, 'j M Y, H:i'));
        case 'date': return e(fmt_date((string)$v));
        case 'rich': case 'textarea': return e(excerpt((string)$v, 80));
        case 'password': return '••••';
        default: return e(mb_strimwidth((string)$v, 0, 70, '…'));
    }
}

/* ---------- CRUD: save ---------- */
function crud_collect(array $mod, ?array $existing): array
{
    $data = []; $errors = [];
    foreach ($mod['fields'] as $k => $f) {
        $type = $f['type'] ?? 'text';
        if (!empty($f['readonly'])) continue;
        $raw = $_POST[$k] ?? null;
        switch ($type) {
            case 'bool': $data[$k] = isset($_POST[$k]) ? 1 : 0; break;
            case 'image': case 'file':
                try {
                    $kind = $f['kind'] ?? 'image';
                    if (isset($f['kind_from'])) $kind = ($f['kind_map'] ?? [])[$_POST[$f['kind_from']] ?? ''] ?? 'image';
                    $up = save_upload($k, $kind);
                    if ($up) $data[$k] = $up;
                    elseif (!empty($_POST['remove_' . $k])) $data[$k] = null;
                } catch (RuntimeException $e) { $errors[] = ($f['label'] ?? $k) . ': ' . $e->getMessage(); }
                break;
            case 'rich': $data[$k] = clean_html((string)$raw) ?: null; break;
            case 'datetime':
                $v = trim((string)$raw);
                $data[$k] = $v === '' ? null : date('Y-m-d H:i:s', strtotime($v)); break;
            case 'date': $v = trim((string)$raw); $data[$k] = $v === '' ? null : $v; break;
            case 'number':
                $v = trim((string)$raw);
                $data[$k] = $v === '' ? (isset($f['default']) ? $f['default'] : null) : (str_contains($v, '.') ? (float)$v : (int)$v); break;
            case 'select':
                $v = trim((string)$raw);
                if ($v !== '' && !array_key_exists($v, field_options($f))) { $errors[] = ($f['label'] ?? $k) . ': invalid choice.'; break; }
                $data[$k] = $v === '' ? null : $v; break;
            case 'password':
                $v = (string)$raw;
                if ($v !== '') { if (strlen($v) < 8) $errors[] = 'Password must be at least 8 characters.'; else $data['password_hash'] = password_hash($v, PASSWORD_DEFAULT); }
                break;
            case 'slug': $data[$k] = trim((string)$raw); break;
            default:
                $v = trim((string)$raw);
                $data[$k] = $v === '' && empty($f['required']) ? ($f['empty'] ?? null) : $v;
        }
        if (!empty($f['required']) && !in_array($type, ['image', 'file', 'password', 'bool'], true) && ($data[$k] ?? '') === '' ) $errors[] = ($f['label'] ?? $k) . ' is required.';
        if ($type === 'password' && !$existing && empty($data['password_hash'])) $errors[] = 'A password is required for new users.';
    }
    return [$data, $errors];
}

function crud_save(string $key, array $mod, ?int $id): void
{
    csrf_check();
    $existing = $id ? one("SELECT * FROM `{$mod['table']}` WHERE id=?", [$id]) : null;
    if ($id && !$existing) { flash('Record not found.', 'error'); redirect(aurl($key)); }
    [$data, $errors] = crud_collect($mod, $existing);
    foreach ($mod['fields'] as $k => $f) {
        if (($f['type'] ?? '') === 'slug') {
            $src = $data[$f['from'] ?? 'title'] ?? ($existing[$f['from'] ?? 'title'] ?? '');
            $s = $data[$k] !== '' && $data[$k] !== null ? slugify($data[$k]) : slugify((string)$src);
            $data[$k] = unique_slug($mod['table'], $s, (int)$id);
        }
    }
    if (isset($mod['before'])) $mod['before']($data, !$existing, $existing);
    if ($errors) { flash(implode(' ', $errors), 'error'); keep_old($_POST); redirect(aurl($key, $id ? 'edit' : 'new', $id ? ['id' => $id] : [])); }

    if ($existing) {
        q('INSERT INTO revisions (entity,entity_id,data,user_id) VALUES (?,?,?,?)', [$key, $id, json_encode($existing, JSON_UNESCAPED_UNICODE), user()['id']]);
        $cols = array_keys($mod['fields']);
        if (in_array('updated_at', array_column(all("SHOW COLUMNS FROM `{$mod['table']}`"), 'Field'), true)) $data['updated_at'] = date('Y-m-d H:i:s');
        update($mod['table'], $data, $id);
        audit('update', $key, $id, $data[$mod['title']] ?? ($existing[$mod['title']] ?? ''));
        flash('Saved.');
    } else {
        $id = insert($mod['table'], $data);
        audit('create', $key, $id, (string)($data[$mod['title']] ?? ''));
        flash('Created.');
    }
    clear_old();
    redirect(isset($_POST['_stay']) ? aurl($key, 'edit', ['id' => $id]) : aurl($key));
}

/* ---------- CRUD: list ---------- */
function crud_scope(array $mod): array
{
    $u = user();
    if ($u['role'] === 'clan_leader' && isset($mod['clan_scope'])) return [' AND ' . $mod['clan_scope'], [(int)$u['clan_id']]];
    return ['', []];
}

function crud_list(string $key, array $mod): void
{
    $qs = get('q');
    $where = ['1=1']; $par = [];
    if ($qs !== '' && !empty($mod['search'])) {
        $parts = [];
        foreach ($mod['search'] as $s) { $parts[] = "`$s` LIKE ?"; $par[] = '%' . like_escape($qs) . '%'; }
        $where[] = '(' . implode(' OR ', $parts) . ')';
    }
    foreach (($mod['filters'] ?? []) as $fk) {
        $v = get('f_' . $fk);
        if ($v !== '') { $where[] = "`$fk`=?"; $par[] = $v; }
    }
    [$sw, $sp] = crud_scope($mod);
    $w = implode(' AND ', $where) . $sw; $par = array_merge($par, $sp);

    if (get('export') === 'csv' || get('export') === 'json') {
        $rows = all("SELECT * FROM `{$mod['table']}` WHERE $w ORDER BY {$mod['order']}", $par);
        foreach ($rows as &$r) unset($r['password_hash']);
        audit('export', $key, null, count($rows) . ' rows');
        if (get('export') === 'json') { header('Content-Disposition: attachment; filename="' . $key . '.json"'); json_out($rows); }
        header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="' . $key . '-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
        if ($rows) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $r) fputcsv($out, $r); }
        exit;
    }

    $total = (int)val("SELECT COUNT(*) FROM `{$mod['table']}` WHERE $w", $par);
    $pg = paginate($total, 25);
    $rows = all("SELECT * FROM `{$mod['table']}` WHERE $w ORDER BY {$mod['order']} LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);

    $actions = '';
    if (($mod['create'] ?? true) && in_array(user()['role'], $mod['create_roles'] ?? $mod['roles'], true) && !(user()['role'] === 'clan_leader' && isset($mod['clan_scope']))) $actions .= '<a class="btn btn--primary" href="' . e(aurl($key, 'new')) . '">' . icon('plus') . ' New</a>';
    if (!empty($mod['import'])) $actions .= ' <a class="btn btn--line" href="' . e(aurl($key, 'import')) . '">' . icon('upload') . ' Import CSV</a>';
    foreach (($mod['links'] ?? []) as [$ll, $lm, $li]) if (can_module($lm)) $actions .= ' <a class="btn btn--line" href="' . e(aurl($lm)) . '">' . icon($li) . ' ' . e($ll) . '</a>';
    admin_start($mod['label'], $key, $actions);
    ?>
    <form class="toolbar" method="get"><input type="hidden" name="m" value="<?= e($key) ?>">
      <?php if (!empty($mod['search'])): ?><input type="search" name="q" value="<?= e($qs) ?>" placeholder="Search <?= e(strtolower($mod['label'])) ?>…"><?php endif; ?>
      <?php foreach (($mod['filters'] ?? []) as $fk): $f = $mod['fields'][$fk]; ?>
        <select name="f_<?= e($fk) ?>" onchange="this.form.submit()"><option value=""><?= e($f['label'] ?? ucfirst(str_replace('_', ' ', $fk))) ?>: all</option><?php foreach (field_options($f) as $ov => $ol): ?><option value="<?= e((string)$ov) ?>" <?= get('f_' . $fk) === (string)$ov ? 'selected' : '' ?>><?= e((string)$ol) ?></option><?php endforeach; ?></select>
      <?php endforeach; ?>
      <button class="btn btn--dark btn--sm"><?= icon('search') ?></button>
      <span class="grow"></span><span class="dim"><?= number_format($total) ?> records</span>
      <a class="btn btn--line btn--sm" href="<?= e(aurl($key, '', array_merge($_GET, ['export' => 'csv']))) ?>">CSV</a>
      <a class="btn btn--line btn--sm" href="<?= e(aurl($key, '', array_merge($_GET, ['export' => 'json']))) ?>">JSON</a>
    </form>
    <div class="panel flush"><div class="tablewrap"><table class="table">
      <thead><tr><?php foreach ($mod['list'] as $c): ?><th><?= e($mod['fields'][$c]['label'] ?? ucfirst(str_replace('_', ' ', $c))) ?></th><?php endforeach; ?><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <?php foreach ($mod['list'] as $i => $c): ?><td><?php if ($i === 0): ?><a href="<?= e(aurl($key, 'edit', ['id' => $r['id']])) ?>"><b><?= render_cell($c, $mod['fields'][$c], $r) ?></b></a><?php else: echo render_cell($c, $mod['fields'][$c], $r); endif; ?></td><?php endforeach; ?>
          <td class="rowact">
            <?php foreach (($mod['actions'] ?? []) as $an => [$al, $af, $av]): if (($r[$af] ?? null) === $av) continue; ?>
              <form method="post" action="<?= e(aurl($key, 'set')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="act" value="<?= e($an) ?>"><button class="mini"><?= e($al) ?></button></form>
            <?php endforeach; ?>
            <a class="mini" href="<?= e(aurl($key, 'edit', ['id' => $r['id']])) ?>"><?= icon('edit') ?></a>
            <?php if (in_array(user()['role'], $mod['delete_roles'] ?? ADM_ALL, true) && ($mod['delete'] ?? true)): ?>
            <form method="post" action="<?= e(aurl($key, 'delete')) ?>" data-confirm="Delete this record permanently?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="mini danger" title="Delete"><?= icon('trash') ?></button></form><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?><tr><td colspan="<?= count($mod['list']) + 1 ?>" class="empty">Nothing here yet.</td></tr><?php endif; ?>
      </tbody></table></div></div>
    <?= pager($pg) ?>
    <?php admin_end();
}

/* ---------- CRUD: form ---------- */
function crud_form(string $key, array $mod, ?int $id): void
{
    $row = $id ? one("SELECT * FROM `{$mod['table']}` WHERE id=?", [$id]) : null;
    if ($id && !$row) { flash('Record not found.', 'error'); redirect(aurl($key)); }
    if ($id) { [$sw, $sp] = crud_scope($mod); if ($sw && !one("SELECT id FROM `{$mod['table']}` WHERE id=? $sw", array_merge([$id], $sp))) deny(); }
    $old = $_SESSION['old'] ?? [];
    $vals = $row ?: [];
    foreach ($mod['fields'] as $k => $f) if (!isset($vals[$k])) $vals[$k] = $old[$k] ?? ($f['default'] ?? null);
    if ($row && $old) $vals = $old + $vals;
    if (!$row && isset($mod['defaults'])) foreach ($mod['defaults']() as $dk => $dv) if (empty($old[$dk])) $vals[$dk] = $dv;
    clear_old();

    $actions = $id ? '<a class="btn btn--line" href="' . e(aurl($key, 'history', ['id' => $id])) . '">' . icon('history') . ' History</a>' : '';
    admin_start(($id ? 'Edit ' : 'New ') . strtolower(rtrim($mod['label'], 's')), $key, $actions);
    ?>
    <form method="post" enctype="multipart/form-data" class="panel formgrid" action="<?= e(aurl($key, 'save', $id ? ['id' => $id] : [])) ?>">
      <?= csrf_field() ?>
      <?php foreach ($mod['fields'] as $k => $f) echo render_field($k, $f, $vals[$k] ?? null, ($row ?? []) + ['id' => $id]); ?>
      <div class="formfoot">
        <button class="btn btn--primary" name="_save" value="1">Save</button>
        <button class="btn btn--line" name="_stay" value="1">Save & keep editing</button>
        <a class="btn btn--line" href="<?= e(aurl($key)) ?>">Cancel</a>
        <?php if ($id && !empty($mod['view_url'])): ?><a class="btn btn--line" target="_blank" href="<?= e($mod['view_url']($row)) ?>"><?= icon('external') ?> View on site</a><?php endif; ?>
      </div>
    </form>
    <?php admin_end();
}

/* ---------- CRUD: history ---------- */
function crud_history(string $key, array $mod, int $id): void
{
    $row = one("SELECT * FROM `{$mod['table']}` WHERE id=?", [$id]);
    if (!$row) redirect(aurl($key));
    if (is_post()) {
        csrf_check();
        $rev = one('SELECT * FROM revisions WHERE id=? AND entity=? AND entity_id=?', [(int)post('rev'), $key, $id]);
        if ($rev) {
            q('INSERT INTO revisions (entity,entity_id,data,user_id) VALUES (?,?,?,?)', [$key, $id, json_encode($row, JSON_UNESCAPED_UNICODE), user()['id']]);
            $data = json_decode($rev['data'], true) ?: [];
            unset($data['id']);
            $data = array_intersect_key($data, $row);
            update($mod['table'], $data, $id);
            audit('restore', $key, $id, 'revision ' . $rev['id']);
            flash('Version restored.');
        }
        redirect(aurl($key, 'edit', ['id' => $id]));
    }
    $revs = all('SELECT r.*, u.name uname FROM revisions r LEFT JOIN users u ON u.id=r.user_id WHERE r.entity=? AND r.entity_id=? ORDER BY r.id DESC LIMIT 50', [$key, $id]);
    admin_start('History: ' . ($row[$mod['title']] ?? '#' . $id), $key, '<a class="btn btn--line" href="' . e(aurl($key, 'edit', ['id' => $id])) . '">Back to editing</a>');
    ?>
    <div class="panel"><p class="dim">Every save keeps the previous version. Restoring a version also keeps the current one, so nothing is lost.</p>
    <?php if (!$revs): ?><p>No earlier versions yet.</p><?php endif; ?>
    <?php foreach ($revs as $r): $d = json_decode($r['data'], true) ?: []; $changed = [];
        foreach ($mod['fields'] as $k => $f) if (isset($row[$k], $d[$k]) && (string)$row[$k] !== (string)$d[$k] && ($f['type'] ?? '') !== 'password') $changed[] = $f['label'] ?? $k; ?>
      <div class="rev"><div><b><?= e(fmt_date($r['created_at'], 'j M Y, H:i')) ?></b> <span class="dim">by <?= e($r['uname'] ?? 'system') ?></span><br>
        <span class="dim">Differs from current in: <?= $changed ? e(implode(', ', $changed)) : 'nothing' ?></span><br><span><?= e(mb_strimwidth((string)($d[$mod['title']] ?? ''), 0, 90, '…')) ?></span></div>
        <form method="post" data-confirm="Restore this version?"><?= csrf_field() ?><input type="hidden" name="rev" value="<?= (int)$r['id'] ?>"><button class="btn btn--line btn--sm">Restore</button></form></div>
    <?php endforeach; ?></div>
    <?php admin_end();
}

/* ---------- CRUD: CSV import ---------- */
function crud_import(string $key, array $mod): void
{
    $cfg = $mod['import'];
    if (is_post()) {
        csrf_check();
        $f = $_FILES['csv'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) { flash('Please choose a CSV file.', 'error'); redirect(aurl($key, 'import')); }
        $h = fopen($f['tmp_name'], 'r');
        $bom = fread($h, 3); if ($bom !== "\xEF\xBB\xBF") rewind($h);
        $head = array_map(fn($x) => strtolower(trim((string)$x)), fgetcsv($h) ?: []);
        $n = 0; $skipped = 0;
        $dist = array_column(all('SELECT id, name FROM districts'), 'id', 'name');
        $distL = array_change_key_case($dist, CASE_LOWER);
        while (($line = fgetcsv($h)) !== false) {
            $r = array_combine($head, array_pad($line, count($head), '')) ?: [];
            $row = [];
            foreach ($cfg['columns'] as $col) if (isset($r[$col]) && trim($r[$col]) !== '') $row[$col] = trim($r[$col]);
            if (empty($row[$cfg['required']])) { $skipped++; continue; }
            if (isset($row['district'])) { $row['district_id'] = $distL[strtolower($row['district'])] ?? null; unset($row['district']); }
            foreach ($mod['fields'] as $k => $fd) if (($fd['type'] ?? '') === 'slug') $row[$k] = unique_slug($mod['table'], slugify($row[$fd['from']] ?? $row[$cfg['required']]));
            if (isset($cfg['status'])) $row['status'] = $cfg['status'];
            insert($mod['table'], $row); $n++;
        }
        fclose($h);
        audit('import', $key, null, "$n rows");
        flash("Imported $n rows" . ($skipped ? " ($skipped skipped)" : '') . '. Review them in the list.');
        redirect(aurl($key));
    }
    admin_start('Import ' . $mod['label'], $key);
    ?>
    <div class="panel"><p>Upload a CSV file with a header row. Recognised columns: <code><?= e(implode(', ', $cfg['columns'])) ?></code>. Required: <code><?= e($cfg['required']) ?></code>.</p>
      <?php if (isset($cfg['status'])): ?><p class="dim">Imported rows are created as <b><?= e($cfg['status']) ?></b> so you can review them before publishing.</p><?php endif; ?>
      <p><a href="data:text/csv;charset=utf-8,<?= e(rawurlencode(implode(',', $cfg['columns']) . "\n")) ?>" download="<?= e($key) ?>-template.csv">Download an empty template</a></p>
      <form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="file" name="csv" accept=".csv,text/csv" required> <button class="btn btn--primary">Import</button></form></div>
    <?php admin_end();
}

/* ---------- CRUD controller ---------- */
function crud_run(string $key, array $mod): void
{
    $a = get('a'); $id = (int)get('id') ?: null;
    switch ($a) {
        case 'new': crud_form($key, $mod, null); return;
        case 'edit': crud_form($key, $mod, $id); return;
        case 'save': if (!is_post()) redirect(aurl($key)); crud_save($key, $mod, $id); return;
        case 'history': crud_history($key, $mod, (int)$id); return;
        case 'import': if (empty($mod['import'])) deny(); crud_import($key, $mod); return;
        case 'delete':
            if (!is_post() || !in_array(user()['role'], $mod['delete_roles'] ?? ADM_ALL, true) || !($mod['delete'] ?? true)) deny();
            csrf_check();
            $did = (int)post('id');
            $row = one("SELECT * FROM `{$mod['table']}` WHERE id=?", [$did]);
            if ($row) {
                q('INSERT INTO revisions (entity,entity_id,data,user_id) VALUES (?,?,?,?)', [$key . ':deleted', $did, json_encode($row, JSON_UNESCAPED_UNICODE), user()['id']]);
                q("DELETE FROM `{$mod['table']}` WHERE id=?", [$did]);
                audit('delete', $key, $did, (string)($row[$mod['title']] ?? ''));
                flash('Deleted.');
            }
            redirect(aurl($key)); return;
        case 'set':
            if (!is_post()) redirect(aurl($key));
            csrf_check();
            $act = $mod['actions'][post('act')] ?? null;
            if ($act) { [, $fld, $val] = $act; update($mod['table'], [$fld => $val], (int)post('id')); audit('set', $key, (int)post('id'), "$fld=$val"); flash('Updated.'); }
            redirect($_SERVER['HTTP_REFERER'] ?? aurl($key)); return;
        default: crud_list($key, $mod);
    }
}
