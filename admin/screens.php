<?php
declare(strict_types=1);

/* ============================== DASHBOARD ============================== */
function screen_dashboard(): void
{
    $c = site_counts();
    $stat = [
        ['Contributions to review', badge_count('contributions'), 'contributions', 'camera', 'clay'],
        ['New messages', badge_count('messages'), 'messages', 'inbox', 'leaf'],
        ['Comments to moderate', badge_count('comments'), 'comments', 'chat', 'ochre'],
        ['New applications', badge_count('applications'), 'applications', 'file', 'ink'],
        ['Pledges awaiting payment', badge_count('donations'), 'donations', 'dollar', 'clay'],
        ['Professionals to verify', badge_count('professionals'), 'professionals', 'users', 'leaf'],
    ];
    $months = [];
    for ($i = 5; $i >= 0; $i--) { $k = date('Y-m', strtotime("-$i month")); $months[$k] = 0; }
    foreach (all("SELECT DATE_FORMAT(created_at,'%Y-%m') ym, COUNT(*) n FROM contributions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym") as $r) if (isset($months[$r['ym']])) $months[$r['ym']] = (int)$r['n'];
    $max = max(1, max($months));
    $unverifiedWords = (int)val("SELECT COUNT(*) FROM dictionary WHERE verified=0");
    $drafts = (int)val("SELECT (SELECT COUNT(*) FROM posts WHERE status='draft') + (SELECT COUNT(*) FROM heritage_items WHERE status='draft') + (SELECT COUNT(*) FROM clans WHERE status='draft')");
    $log = all('SELECT a.*, u.name uname FROM audit_log a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 8');
    $recent = all("SELECT * FROM contributions ORDER BY created_at DESC LIMIT 5");
    $subs = (int)val('SELECT COUNT(*) FROM subscribers');
    $don = donation_totals();
    admin_start('Dashboard', 'dashboard');
    ?>
    <div class="welcome"><div><h2>Welcome back, <?= e(explode(' ', user()['name'])[0]) ?></h2><p>Here is what needs your attention across the Lango digital archive.</p></div><span class="welcome__dot"></span></div>
    <div class="cards">
      <?php foreach ($stat as [$l, $n, $m, $ic, $col]): if (!can_module($m)) continue; ?>
        <a class="card c-<?= $col ?>" href="<?= e(aurl($m)) ?>"><span class="ico"><?= icon($ic) ?></span><b><?= $n ?></b><span><?= e($l) ?></span></a>
      <?php endforeach; ?>
    </div>
    <div class="cols">
      <div class="panel"><h3>Contributions per month</h3>
        <div class="bars"><?php foreach ($months as $k => $n): ?><div><i style="height:<?= round($n / $max * 100) ?>%"><b><?= $n ?></b></i><span><?= e(date('M', strtotime($k . '-01'))) ?></span></div><?php endforeach; ?></div></div>
      <div class="panel"><h3>Archive at a glance</h3>
        <dl class="kv"><dt>Registered clans</dt><dd><?= (int)val("SELECT COUNT(*) FROM clans WHERE status='published'") ?> published of <?= $c['clans'] ?></dd>
          <dt>Dictionary entries</dt><dd><?= number_format($c['words']) ?> <span class="dim">(<?= number_format($unverifiedWords) ?> unverified)</span></dd>
          <dt>Heritage items</dt><dd><?= (int)val("SELECT COUNT(*) FROM heritage_items WHERE status='published'") ?> published</dd>
          <dt>Items in draft</dt><dd><?= $drafts ?></dd><dt>Subscribers</dt><dd><?= number_format($subs) ?></dd>
          <dt>Confirmed donations</dt><dd><?= number_format((int)$don['n']) ?> · UGX <?= number_format((float)$don['ugx']) ?> · USD <?= number_format((float)$don['usd']) ?></dd></dl></div>
    </div>
    <div class="cols">
      <div class="panel"><h3>Latest contributions</h3>
        <?php foreach ($recent as $r): ?><div class="line"><div><b><a href="<?= e(aurl('contributions', 'review', ['id' => $r['id']])) ?>"><?= e($r['title']) ?></a></b><br><span class="dim"><?= e(CONTRIB_TYPES[$r['type']][0]) ?> · <?= e($r['name']) ?> · <?= e(fmt_date($r['created_at'])) ?></span></div><span class="chip <?= in_array($r['status'], ['approved', 'published'], true) ? 'ok' : 'warn' ?>"><?= e(CONTRIB_STATUS[$r['status']]) ?></span></div><?php endforeach; if (!$recent) echo '<p class="dim">No contributions yet.</p>'; ?></div>
      <div class="panel"><h3>Recent activity</h3>
        <?php foreach ($log as $l): ?><div class="line"><div><b><?= e($l['uname'] ?? 'Visitor') ?></b> <?= e($l['action']) ?> <?= e((string)$l['entity']) ?> <span class="dim"><?= e(mb_strimwidth((string)$l['detail'], 0, 50, '…')) ?></span></div><span class="dim"><?= e(fmt_date($l['created_at'], 'j M H:i')) ?></span></div><?php endforeach; if (!$log) echo '<p class="dim">No activity yet.</p>'; ?></div>
    </div>
    <?php if (in_array(user()['role'], ['super_admin', 'admin', 'editor'], true)): ?>
    <div class="panel"><h3>Quick actions</h3><div class="quick">
      <a class="btn btn--line" href="<?= e(aurl('posts', 'new')) ?>"><?= icon('plus') ?> Write news</a><a class="btn btn--line" href="<?= e(aurl('events', 'new')) ?>"><?= icon('plus') ?> Add event</a>
      <a class="btn btn--line" href="<?= e(aurl('clans', 'import')) ?>"><?= icon('upload') ?> Import clans (CSV)</a><a class="btn btn--line" href="<?= e(aurl('heritage_items', 'new')) ?>"><?= icon('plus') ?> Add heritage item</a>
      <a class="btn btn--line" href="<?= e(aurl('proverbs', 'new')) ?>"><?= icon('plus') ?> Add proverb</a><a class="btn btn--line" href="<?= e(aurl('translations')) ?>"><?= icon('globe') ?> Translate interface</a></div></div>
    <?php endif;
    admin_end();
}

/* ============================ CONTRIBUTIONS ============================ */
const CONTRIB_POINTS = ['cultural' => 10, 'natural' => 15, 'language' => 5, 'oral_history' => 20, 'document' => 15, 'heritage_site' => 15];

function contribution_scope(): array
{
    $u = user();
    return $u['role'] === 'clan_leader' ? [' AND clan_id=?', [(int)$u['clan_id']]] : ['', []];
}

function contribution_to_archive(array $c): array
{
    $img = $c['media_kind'] === 'image' ? $c['media_file'] : null;
    $slug = fn(string $t, string $table) => unique_slug($table, slugify($t));
    switch ($c['type']) {
        case 'natural':
            $id = insert('species', ['name' => $c['title'], 'slug' => $slug($c['title'], 'species'), 'kind' => 'animal', 'body' => $c['description'], 'habitat' => $c['location_text'], 'image' => $img, 'status' => 'draft']);
            return ['species', $id];
        case 'language':
            $id = insert('dictionary', ['lango' => mb_substr($c['title'], 0, 190), 'english' => mb_substr((string)$c['description'], 0, 500), 'source' => 'Contribution ' . $c['code'], 'audio' => $c['media_kind'] === 'audio' ? $c['media_file'] : null, 'verified' => 0, 'status' => 'draft']);
            return ['dictionary', $id];
        case 'oral_history':
            $id = insert('media', ['type' => $c['media_kind'] === 'audio' ? 'audio' : ($c['media_kind'] === 'video' ? 'video' : 'photo'), 'title' => $c['title'], 'description' => $c['description'], 'file' => $c['media_file'], 'credit' => $c['name'], 'album' => 'Oral histories', 'source' => 'community', 'status' => 'draft']);
            return ['media', $id];
        case 'document':
            if ($c['media_kind'] === 'document') { $id = insert('documents', ['title' => $c['title'], 'category' => 'historical', 'description' => $c['description'], 'file' => $c['media_file'], 'status' => 'draft']); return ['documents', $id]; }
            $id = insert('media', ['type' => 'photo', 'title' => $c['title'], 'description' => $c['description'], 'file' => $c['media_file'], 'credit' => $c['name'], 'album' => 'Historical documents', 'source' => 'community', 'status' => 'draft']);
            return ['media', $id];
        case 'heritage_site':
            $id = insert('heritage_items', ['category' => 'site', 'title' => $c['title'], 'slug' => $slug($c['title'], 'heritage_items'), 'summary' => excerpt($c['description'], 200), 'body' => $c['description'], 'image' => $img, 'clan_id' => $c['clan_id'], 'district_id' => $c['district_id'], 'lat' => $c['lat'], 'lng' => $c['lng'], 'status' => 'draft']);
            return ['heritage_items', $id];
        default:
            $id = insert('heritage_items', ['category' => 'ceremony', 'title' => $c['title'], 'slug' => $slug($c['title'], 'heritage_items'), 'summary' => excerpt($c['description'], 200), 'body' => $c['description'], 'image' => $img, 'clan_id' => $c['clan_id'], 'district_id' => $c['district_id'], 'status' => 'draft']);
            return ['heritage_items', $id];
    }
}

function award_points(array $c, string $newStatus): void
{
    if (!in_array($newStatus, ['approved', 'published'], true) || $c['points_awarded'] || !$c['user_id']) return;
    $pts = CONTRIB_POINTS[$c['type']] ?? 10;
    q('UPDATE users SET points=points+? WHERE id=?', [$pts, $c['user_id']]);
    q('UPDATE contributions SET points_awarded=? WHERE id=?', [$pts, $c['id']]);
}

function screen_contributions(): void
{
    $a = get('a'); $id = (int)get('id');
    [$sw, $sp] = contribution_scope();

    if ($a === 'review_save' && is_post()) {
        csrf_check();
        $c = one("SELECT * FROM contributions WHERE id=? $sw", array_merge([(int)post('id')], $sp));
        if (!$c || !isset(CONTRIB_STATUS[post('status')])) deny();
        $lat = is_numeric(post('lat')) ? (float)post('lat') : null; $lng = is_numeric(post('lng')) ? (float)post('lng') : null;
        update('contributions', ['status' => post('status'), 'review_note' => post('review_note') ?: null, 'reviewer_id' => user()['id'], 'lat' => $lat, 'lng' => $lng, 'updated_at' => date('Y-m-d H:i:s')], (int)$c['id']);
        award_points($c, post('status'));
        audit('review', 'contributions', (int)$c['id'], post('status'));
        flash('Review saved.');
        redirect(aurl('contributions', 'review', ['id' => $c['id']]));
    }
    if ($a === 'archive' && is_post()) {
        csrf_check();
        $c = one("SELECT * FROM contributions WHERE id=? $sw", array_merge([(int)post('id')], $sp));
        if (!$c || !can_module('posts') && !in_array(user()['role'], ADM_MOD, true)) deny();
        [$mod, $newId] = contribution_to_archive($c);
        update('contributions', ['status' => 'published', 'reviewer_id' => user()['id'], 'updated_at' => date('Y-m-d H:i:s')], (int)$c['id']);
        award_points($c, 'published');
        audit('archive', 'contributions', (int)$c['id'], "→ $mod #$newId");
        flash('Added to the archive as a draft. Complete and publish it here.');
        redirect(can_module($mod) ? aurl($mod, 'edit', ['id' => $newId]) : aurl('contributions'));
    }

    if ($a === 'review' && $id) {
        $c = one("SELECT c.*, cl.name clan, d.name district FROM contributions c LEFT JOIN clans cl ON cl.id=c.clan_id LEFT JOIN districts d ON d.id=c.district_id WHERE c.id=? " . str_replace('clan_id', 'c.clan_id', $sw), array_merge([$id], $sp));
        if (!$c) deny();
        admin_start('Review: ' . $c['title'], 'contributions', '<a class="btn btn--line" href="' . e(aurl('contributions')) . '">← All contributions</a>');
        ?>
        <div class="cols two">
          <div class="panel">
            <p><span class="chip warn"><?= e(CONTRIB_TYPES[$c['type']][0]) ?></span> <span class="dim">Code <b><?= e($c['code']) ?></b> · received <?= e(fmt_date($c['created_at'], 'j M Y, H:i')) ?></span></p>
            <h2 style="margin-top:6px"><?= e($c['title']) ?></h2>
            <p><?= nl2br(e((string)$c['description'])) ?></p>
            <?php if ($c['media_file']): ?><h4>Attached <?= e($c['media_kind']) ?></h4>
              <?php if ($c['media_kind'] === 'image'): ?><a href="<?= e(media_url($c['media_file'])) ?>" target="_blank"><img class="prev" src="<?= e(media_url($c['media_file'])) ?>" alt=""></a>
              <?php elseif ($c['media_kind'] === 'audio'): ?><audio controls src="<?= e(media_url($c['media_file'])) ?>" style="width:100%"></audio>
              <?php elseif ($c['media_kind'] === 'video'): ?><video controls src="<?= e(media_url($c['media_file'])) ?>" style="width:100%;border-radius:10px"></video>
              <?php else: ?><a class="btn btn--line" href="<?= e(media_url($c['media_file'])) ?>" target="_blank">Open document</a><?php endif; endif; ?>
            <dl class="kv" style="margin-top:20px"><dt>Contributor</dt><dd><?= e($c['name']) ?><?= $c['user_id'] ? ' <span class="chip ok">account</span>' : '' ?></dd><dt>Email</dt><dd><?= e((string)$c['email']) ?: '—' ?></dd><dt>Phone</dt><dd><?= e((string)$c['phone']) ?: '—' ?></dd>
              <dt>Clan</dt><dd><?= e((string)$c['clan']) ?: '—' ?></dd><dt>District</dt><dd><?= e((string)$c['district']) ?: '—' ?></dd><dt>Place</dt><dd><?= e((string)$c['location_text']) ?: '—' ?></dd>
              <dt>Observed on</dt><dd><?= e(fmt_date($c['observed_on'])) ?: '—' ?></dd><dt>Rights</dt><dd><?= e(['retain' => 'Contributor keeps all rights', 'cc_by' => 'Open with credit (CC BY)', 'lci' => 'LCI full rights'][$c['rights']]) ?></dd>
              <?php if ($c['lat']): ?><dt>GPS</dt><dd><a target="_blank" href="https://www.openstreetmap.org/?mlat=<?= e((string)$c['lat']) ?>&mlon=<?= e((string)$c['lng']) ?>#map=14/<?= e((string)$c['lat']) ?>/<?= e((string)$c['lng']) ?>"><?= e((string)$c['lat']) ?>, <?= e((string)$c['lng']) ?></a></dd><?php endif; ?></dl>
          </div>
          <div>
            <form class="panel" method="post" action="<?= e(aurl('contributions', 'review_save')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <h3>Review</h3>
              <div class="field"><label>Status</label><select name="status"><?php foreach (CONTRIB_STATUS as $k => $l): ?><option value="<?= e($k) ?>" <?= $c['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
              <div class="field"><label>Note to the contributor</label><textarea name="review_note" rows="4" placeholder="Shown when they track their contribution — ask for more detail, explain a decision…"><?= e((string)$c['review_note']) ?></textarea></div>
              <div class="fgrid2"><div class="field"><label>Latitude</label><input type="text" name="lat" value="<?= e((string)$c['lat']) ?>"></div><div class="field"><label>Longitude</label><input type="text" name="lng" value="<?= e((string)$c['lng']) ?>"></div></div>
              <p class="dim" style="margin-top:-4px">For endangered species or sacred sites, round the coordinates (e.g. 2 decimals) or clear them before publishing.</p>
              <p class="dim">Points on approval: <b><?= (int)(CONTRIB_POINTS[$c['type']] ?? 10) ?></b><?= $c['points_awarded'] ? ' (already awarded: ' . (int)$c['points_awarded'] . ')' : '' ?><?= $c['user_id'] ? '' : ' — no account, no points' ?></p>
              <button class="btn btn--primary">Save review</button>
            </form>
            <form class="panel" method="post" action="<?= e(aurl('contributions', 'archive')) ?>" data-confirm="Create a draft archive entry from this contribution?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <h3>Add to the archive</h3><p class="dim">Creates a draft <b><?= e(['cultural' => 'heritage item', 'natural' => 'species entry', 'language' => 'dictionary entry', 'oral_history' => 'media item', 'document' => 'document / media item', 'heritage_site' => 'heritage site'][$c['type']]) ?></b> from this submission, marks it Published here and awards points. You then edit and publish the draft.</p>
              <button class="btn btn--dark">Create archive draft</button></form>
          </div>
        </div>
        <?php admin_end(); return;
    }

    /* list */
    $where = ['1=1']; $par = [];
    $st = get('f_status'); $ty = get('f_type'); $qs = get('q');
    if (isset(CONTRIB_STATUS[$st])) { $where[] = 'status=?'; $par[] = $st; }
    if (isset(CONTRIB_TYPES[$ty])) { $where[] = 'type=?'; $par[] = $ty; }
    if ($qs !== '') { $where[] = '(title LIKE ? OR name LIKE ? OR code LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
    $w = implode(' AND ', $where) . $sw; $par = array_merge($par, $sp);
    $total = (int)val("SELECT COUNT(*) FROM contributions WHERE $w", $par);
    $pg = paginate($total, 25);
    $rows = all("SELECT * FROM contributions WHERE $w ORDER BY FIELD(status,'pending','under_review','needs_info','approved','published','rejected'), created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
    admin_start('Contributions', 'contributions');
    ?>
    <form class="toolbar" method="get"><input type="hidden" name="m" value="contributions"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="Search title, name or code…">
      <select name="f_status" onchange="this.form.submit()"><option value="">Status: all</option><?php foreach (CONTRIB_STATUS as $k => $l): ?><option value="<?= e($k) ?>" <?= $st === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
      <select name="f_type" onchange="this.form.submit()"><option value="">Type: all</option><?php foreach (CONTRIB_TYPES as $k => [$l]): ?><option value="<?= e($k) ?>" <?= $ty === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
      <button class="btn btn--dark btn--sm"><?= icon('search') ?></button><span class="grow"></span><span class="dim"><?= number_format($total) ?> contributions</span></form>
    <div class="panel flush"><div class="tablewrap"><table class="table"><thead><tr><th>Contribution</th><th>Type</th><th>From</th><th>Media</th><th>Status</th><th>Received</th></tr></thead><tbody>
      <?php foreach ($rows as $r): ?><tr><td><a href="<?= e(aurl('contributions', 'review', ['id' => $r['id']])) ?>"><b><?= e(mb_strimwidth($r['title'], 0, 60, '…')) ?></b></a><br><span class="dim"><?= e($r['code']) ?></span></td><td><?= e(CONTRIB_TYPES[$r['type']][0]) ?></td><td><?= e($r['name']) ?></td>
        <td><?= $r['media_kind'] === 'image' ? '<img class="thumb s" src="' . e(media_url($r['media_file'])) . '" alt="">' : ($r['media_kind'] !== 'none' ? e($r['media_kind']) : '<span class="dim">—</span>') ?></td>
        <td><span class="chip <?= in_array($r['status'], ['approved', 'published'], true) ? 'ok' : ($r['status'] === 'rejected' ? 'bad' : 'warn') ?>"><?= e(CONTRIB_STATUS[$r['status']]) ?></span></td><td><?= e(fmt_date($r['created_at'])) ?></td></tr><?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">No contributions match.</td></tr><?php endif; ?></tbody></table></div></div>
    <?= pager($pg) ?>
    <?php admin_end();
}

/* ============================== SETTINGS ============================== */
function screen_settings(): void
{
    $groups = [
        'Identity' => ['site_name' => 'Site name', 'org_subtitle' => 'Subtitle (shown under the name)', 'site_tagline' => 'Tagline'],
        'About' => ['mission' => 'Mission statement', 'vision' => 'Vision statement'],
        'Contact' => ['email' => 'Public email', 'phone' => 'Public phone', 'address' => 'Address', 'whatsapp' => 'WhatsApp link (https://wa.me/…)'],
        'Social media' => ['facebook' => 'Facebook URL', 'x' => 'X (Twitter) URL', 'youtube' => 'YouTube URL', 'instagram' => 'Instagram URL'],
        'Numbers & map' => ['clan_total' => 'Total registered clans', 'map_lat' => 'Map centre latitude', 'map_lng' => 'Map centre longitude'],
        'Giving' => ['donation_note' => 'Donation appeal text'],
    ];
    $images = [
        'logo' => ['Replace logo', 'PNG, WebP or JPG, ideally with a transparent background. Shown in the header, footer, admin and browser tab.'],
        'hero_image_1' => ['Homepage hero — large circle photo', 'Landscape or people photo, at least 900×900.'],
        'hero_image_2' => ['Homepage hero — small circle photo', 'At least 600×600.'],
        'about_image_1' => ['About section — main photo', 'Portrait orientation works best (about 700×850).'],
        'about_image_2' => ['About section — second photo', 'About 600×500.'],
        'cta_image' => ['Support banner background', 'Wide photo, at least 1600×700.'],
    ];
    if (is_post()) {
        csrf_check();
        $err = [];
        foreach ($groups as $g) foreach ($g as $k => $_) q('INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)', [$k, post($k)]);
        foreach ($images as $k => $_) {
            try {
                $up = save_upload($k, 'image');
                if ($up) q('INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)', [$k, $up]);
                elseif (!empty($_POST['remove_' . $k])) q('INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)', [$k, '']);
            } catch (RuntimeException $e) { $err[] = ($images[$k][0] ?? $k) . ': ' . $e->getMessage(); }
        }
        audit('update', 'settings');
        if ($err) flash(implode(' ', $err), 'error'); else flash('Settings saved.');
        redirect(aurl('settings'));
    }
    admin_start('Site settings', 'settings');
    ?>
    <form method="post" enctype="multipart/form-data" class="panel"><?= csrf_field() ?>
      <h3>Branding & photos</h3>
      <p class="dim"><?= setting('logo') ? 'Your uploaded logo is in use.' : 'The official LCI logo (<code>assets/img/logo.png</code>) is in use — upload a file below only if you want to replace it.' ?> Photos that are not set use the bundled drone photography.</p>
      <div class="formgrid">
        <?php foreach ($images as $k => [$label, $help]): $cur = setting($k); ?>
          <div class="field">
            <label><?= e($label) ?></label>
            <div class="upl"><?php if ($cur): ?><img class="thumb" src="<?= e(media_url($cur)) ?>" alt=""> <label class="inl"><input type="checkbox" name="remove_<?= e($k) ?>" value="1"> remove</label><?php endif; ?>
              <input type="file" name="<?= e($k) ?>" accept="image/*"></div>
            <small><?= e($help) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
      <?php foreach ($groups as $title => $fields): ?><h3 style="margin-top:26px"><?= e($title) ?></h3><div class="formgrid"><?php foreach ($fields as $k => $l): $long = in_array($k, ['address', 'donation_note', 'site_tagline', 'mission', 'vision']); ?><div class="field <?= $long ? 'full' : '' ?>"><label><?= e($l) ?></label><?php if (in_array($k, ['mission', 'vision'])): ?><textarea name="<?= e($k) ?>" rows="3"><?= e(setting($k)) ?></textarea><?php else: ?><input type="text" name="<?= e($k) ?>" value="<?= e(setting($k)) ?>"><?php endif; ?></div><?php endforeach; ?></div><?php endforeach; ?>
      <div class="formfoot" style="margin-top:22px"><button class="btn btn--primary">Save settings</button></div></form>
    <?php admin_end();
}

/* ============================ TRANSLATIONS ============================ */
function screen_translations(): void
{
    if (is_post()) {
        csrf_check();
        foreach (($_POST['lg'] ?? []) as $h => $v) {
            if (!preg_match('/^[a-f0-9]{32}$/', (string)$h)) continue;
            q('UPDATE translations SET lg=?, updated_at=NOW() WHERE h=?', [trim((string)$v) === '' ? null : trim((string)$v), $h]);
        }
        audit('update', 'translations');
        flash('Translations saved.');
        redirect(aurl('translations', '', array_filter(['q' => get('q'), 'only' => get('only'), 'page' => get('page')])));
    }
    $qs = get('q'); $only = get('only');
    $where = ['1=1']; $par = [];
    if ($qs !== '') { $where[] = '(k LIKE ? OR lg LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l); }
    if ($only === 'missing') $where[] = "(lg IS NULL OR lg='')";
    $w = implode(' AND ', $where);
    $total = (int)val("SELECT COUNT(*) FROM translations WHERE $w", $par);
    $pg = paginate($total, 40);
    $rows = all("SELECT * FROM translations WHERE $w ORDER BY (lg IS NULL OR lg='') DESC, k LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
    $all = (int)val('SELECT COUNT(*) FROM translations'); $done = (int)val("SELECT COUNT(*) FROM translations WHERE lg IS NOT NULL AND lg<>''");
    admin_start('Interface translations (Leb Lango)', 'translations');
    ?>
    <div class="panel"><p>Every phrase the website displays is listed here once a visitor has seen it. Enter the Leb Lango text — ideally reviewed by a language expert. Anything left blank shows in English. <b><?= $done ?></b> of <b><?= $all ?></b> translated.</p>
      <div class="progressbar"><i style="width:<?= $all ? round($done / $all * 100) : 0 ?>%"></i></div></div>
    <form class="toolbar" method="get"><input type="hidden" name="m" value="translations"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="Search phrases…"><select name="only" onchange="this.form.submit()"><option value="">All phrases</option><option value="missing" <?= $only === 'missing' ? 'selected' : '' ?>>Not yet translated</option></select><button class="btn btn--dark btn--sm"><?= icon('search') ?></button></form>
    <form method="post" class="panel flush"><?= csrf_field() ?><div class="tablewrap"><table class="table"><thead><tr><th style="width:42%">English</th><th>Leb Lango</th></tr></thead><tbody>
      <?php foreach ($rows as $r): ?><tr><td><?= e($r['k']) ?></td><td><textarea name="lg[<?= e($r['h']) ?>]" rows="<?= mb_strlen($r['k']) > 90 ? 3 : 1 ?>" style="width:100%"><?= e((string)$r['lg']) ?></textarea></td></tr><?php endforeach; if (!$rows): ?><tr><td colspan="2" class="empty">No phrases yet — browse the website in Leb Lango to collect them.</td></tr><?php endif; ?></tbody></table></div>
      <div class="formfoot" style="padding:16px"><button class="btn btn--primary">Save translations</button></div></form>
    <?= pager($pg) ?>
    <?php admin_end();
}

/* ================================ AUDIT ================================ */
function screen_audit(): void
{
    $total = (int)val('SELECT COUNT(*) FROM audit_log');
    $pg = paginate($total, 40);
    $rows = all("SELECT a.*, u.name uname FROM audit_log a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
    admin_start('Audit log', 'audit');
    ?>
    <div class="panel flush"><div class="tablewrap"><table class="table"><thead><tr><th>When</th><th>Who</th><th>Action</th><th>What</th><th>Detail</th><th>IP</th></tr></thead><tbody>
      <?php foreach ($rows as $r): ?><tr><td><?= e(fmt_date($r['created_at'], 'j M Y H:i')) ?></td><td><?= e($r['uname'] ?? 'visitor') ?></td><td><span class="chip warn"><?= e($r['action']) ?></span></td><td><?= e((string)$r['entity']) ?><?= $r['entity_id'] ? ' #' . (int)$r['entity_id'] : '' ?></td><td><?= e((string)$r['detail']) ?></td><td class="dim"><?= e((string)$r['ip']) ?></td></tr><?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">Nothing logged yet.</td></tr><?php endif; ?></tbody></table></div></div>
    <?= pager($pg) ?>
    <?php admin_end();
}

/* ================================ BACKUP ================================ */
function screen_backup(): void
{
    if (get('a') === 'download' && is_post()) {
        csrf_check();
        audit('backup', 'database');
        set_time_limit(120);
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="lci-backup-' . date('Ymd-His') . '.sql"');
        $pdo = db();
        echo "-- LCI database backup " . date('c') . "\nSET NAMES utf8mb4; SET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) {
            $create = $pdo->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM)[1];
            echo "DROP TABLE IF EXISTS `$t`;\n$create;\n\n";
            $st = $pdo->query("SELECT * FROM `$t`");
            $batch = [];
            while ($r = $st->fetch(PDO::FETCH_NUM)) {
                $batch[] = '(' . implode(',', array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), $r)) . ')';
                if (count($batch) >= 200) { echo "INSERT INTO `$t` VALUES " . implode(",\n", $batch) . ";\n"; $batch = []; }
            }
            if ($batch) echo "INSERT INTO `$t` VALUES " . implode(",\n", $batch) . ";\n";
            echo "\n";
        }
        echo "SET FOREIGN_KEY_CHECKS=1;\n";
        exit;
    }
    $size = (float)val("SELECT SUM(data_length+index_length)/1048576 FROM information_schema.tables WHERE table_schema=DATABASE()");
    $uploads = 0; $files = 0;
    if (is_dir(ROOT . '/uploads')) foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT . '/uploads', FilesystemIterator::SKIP_DOTS)) as $f) { $uploads += $f->getSize(); $files++; }
    admin_start('Backup & export', 'backup');
    ?>
    <div class="cols two"><div class="panel"><h3>Database backup</h3><p>Download a complete SQL backup of every table (content, users, contributions, donations, translations, history). Database size: <b><?= number_format($size, 1) ?> MB</b>.</p>
      <form method="post" action="<?= e(aurl('backup', 'download')) ?>"><?= csrf_field() ?><button class="btn btn--primary"><?= icon('download') ?> Download SQL backup</button></form></div>
      <div class="panel"><h3>Uploaded media</h3><p><b><?= $files ?></b> files, <b><?= number_format($uploads / 1048576, 1) ?> MB</b> in the <code>uploads/</code> folder. Copy this folder together with the SQL backup to keep a full copy.</p></div></div>
    <div class="panel"><h3>Recommended routine</h3><ul><li>Download the SQL backup weekly (or schedule <code>mysqldump</code> daily on the server) and store a copy off-site.</li><li>Back up the <code>uploads/</code> folder at the same time.</li><li>Every edit in the admin keeps its previous version — use <b>History</b> on any record to restore it.</li><li>CSV and JSON exports are available from the list page of every content type.</li></ul></div>
    <?php admin_end();
}

/* ================================ PROFILE ================================ */
function screen_profile(): void
{
    $u = user();
    if (is_post()) {
        csrf_check();
        $name = post('name'); $new = (string)($_POST['new_password'] ?? '');
        if ($name === '') { flash('Name is required.', 'error'); redirect(aurl('profile')); }
        $data = ['name' => $name, 'phone' => post('phone') ?: null];
        if ($new !== '') {
            if (!password_verify((string)($_POST['current_password'] ?? ''), $u['password_hash'])) { flash('Your current password is incorrect.', 'error'); redirect(aurl('profile')); }
            if (strlen($new) < 10) { flash('Choose a new password of at least 10 characters.', 'error'); redirect(aurl('profile')); }
            $data['password_hash'] = password_hash($new, PASSWORD_DEFAULT); $data['must_change_password'] = 0;
            audit('password', 'users', (int)$u['id']);
        }
        update('users', $data, (int)$u['id']);
        flash('Profile updated.');
        redirect(aurl('profile'));
    }
    admin_start('My profile', 'profile');
    ?>
    <form method="post" class="panel formgrid"><?= csrf_field() ?>
      <?php if ($u['must_change_password']): ?><div class="alert alert-info full">For security, please choose a new password now (at least 10 characters).</div><?php endif; ?>
      <div class="field"><label>Name</label><input type="text" name="name" value="<?= e($u['name']) ?>" required></div><div class="field"><label>Email</label><div class="ro"><?= e($u['email']) ?></div></div>
      <div class="field"><label>Phone</label><input type="text" name="phone" value="<?= e((string)$u['phone']) ?>"></div><div class="field"><label>Role</label><div class="ro"><?= e(str_replace('_', ' ', $u['role'])) ?></div></div>
      <div class="field"><label>Current password</label><input type="password" name="current_password" autocomplete="current-password"></div><div class="field"><label>New password</label><input type="password" name="new_password" autocomplete="new-password" minlength="10"></div>
      <div class="formfoot"><button class="btn btn--primary">Save profile</button></div></form>
    <?php admin_end();
}
