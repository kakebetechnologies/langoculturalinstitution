<?php
declare(strict_types=1);

/* ==========================================================================
   Admin screens for community input: notifications, word suggestions, bulk clan entry
   ========================================================================== */

/* ============================== NOTIFICATIONS ============================== */
function screen_notifications(): void
{
    $a = get('a');

    if ($a === 'poll') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['count' => (int)val('SELECT COUNT(*) FROM notifications WHERE is_read=0'), 'html' => notif_list_html(notif_items(7))], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($a === 'go') {
        $n = one('SELECT * FROM notifications WHERE id=?', [(int)get('id')]);
        if ($n) { q('UPDATE notifications SET is_read=1 WHERE id=?', [$n['id']]); redirect($n['url'] ?: aurl('notifications')); }
        redirect(aurl('notifications'));
    }
    if (is_post() && in_array($a, ['read_all', 'clear_read', 'read'], true)) {
        csrf_check();
        if ($a === 'read_all') q('UPDATE notifications SET is_read=1 WHERE is_read=0');
        elseif ($a === 'clear_read') q('DELETE FROM notifications WHERE is_read=1');
        else q('UPDATE notifications SET is_read=1 WHERE id=?', [(int)post('id')]);
        redirect($_SERVER['HTTP_REFERER'] ?? aurl('notifications'));
    }

    $f = get('f'); $type = get('type');
    $where = ['1=1']; $par = [];
    if ($f === 'unread') $where[] = 'is_read=0';
    if (isset(NOTIF_ICONS[$type])) { $where[] = 'type=?'; $par[] = $type; }
    $w = implode(' AND ', $where);
    $total = (int)val("SELECT COUNT(*) FROM notifications WHERE $w", $par);
    $pg = paginate($total, 30);
    $rows = all("SELECT * FROM notifications WHERE $w ORDER BY id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
    $unread = (int)val('SELECT COUNT(*) FROM notifications WHERE is_read=0');
    $labels = ['contribution' => 'Contributions', 'word' => 'Words', 'message' => 'Messages', 'application' => 'Applications', 'donation' => 'Donations', 'comment' => 'Comments', 'professional' => 'Professionals', 'event' => 'Events', 'user' => 'Members', 'clan' => 'Missing clans'];
    admin_start('Notifications', 'notifications', '<form method="post" action="' . e(aurl('notifications', 'read_all')) . '" style="display:inline">' . csrf_field() . '<button class="btn btn--line">Mark all read</button></form> <form method="post" action="' . e(aurl('notifications', 'clear_read')) . '" style="display:inline" data-confirm="Remove all notifications you have already read?">' . csrf_field() . '<button class="btn btn--line">Clear read</button></form>');
    ?>
    <div class="panel"><p>Every contribution, word suggestion, message, application, donation pledge, comment, directory profile, event registration and new member appears here the moment it is submitted. <b><?= $unread ?></b> unread.</p>
      <div class="chips">
        <a class="chipbtn <?= $f === '' && $type === '' ? 'on' : '' ?>" href="<?= e(aurl('notifications')) ?>">All</a>
        <a class="chipbtn <?= $f === 'unread' ? 'on' : '' ?>" href="<?= e(aurl('notifications', '', ['f' => 'unread'])) ?>">Unread</a>
        <?php foreach ($labels as $k => $l): ?><a class="chipbtn <?= $type === $k ? 'on' : '' ?>" href="<?= e(aurl('notifications', '', ['type' => $k])) ?>"><?= e($l) ?></a><?php endforeach; ?>
      </div></div>
    <div class="panel flush"><div class="nlist">
      <?php foreach ($rows as $n): ?>
        <div class="nrow<?= $n['is_read'] ? '' : ' new' ?>">
          <span class="bell__ic"><?= icon(NOTIF_ICONS[$n['type']] ?? 'bell') ?></span>
          <div class="grow"><a href="<?= e(aurl('notifications', 'go', ['id' => $n['id']])) ?>"><b><?= e($n['title']) ?></b></a><?= $n['body'] ? '<br><span class="dim">' . e($n['body']) . '</span>' : '' ?></div>
          <span class="dim"><?= e(fmt_date($n['created_at'], 'j M Y, H:i')) ?></span>
          <?php if (!$n['is_read']): ?><form method="post" action="<?= e(aurl('notifications', 'read')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$n['id'] ?>"><button class="mini">Mark read</button></form><?php endif; ?>
        </div>
      <?php endforeach; if (!$rows): ?><p class="empty" style="padding:30px">Nothing here.</p><?php endif; ?>
    </div></div>
    <?= pager($pg) ?>
    <?php admin_end();
}

/* ============================ WORD SUGGESTIONS ============================ */
const WORD_POS_ABBR = ['noun' => 'n.', 'verb' => 'v.', 'adjective' => 'adj.', 'adverb' => 'adv.', 'phrase' => 'phrase', 'other' => ''];

function contributor_label(string $name): string
{
    $p = preg_split('/\s+/', trim($name));
    return count($p) > 1 ? $p[0] . ' ' . mb_substr(end($p), 0, 1) . '.' : ($p[0] ?? '');
}

/** Publish a suggested word to the dictionary (idempotent per suggestion code). Returns the dictionary id. */
function word_publish(array $c, array $f): int
{
    $src = 'Community suggestion ' . $c['code'];
    $clanLabel = trim((string)preg_replace('/\s+clan$/i', '', (string)$f['clan']));
    $credit = ($c['credit'] ?? 1) ? contributor_label($c['name']) . ($clanLabel !== '' ? ', ' . $clanLabel . ' clan' : '') : null;
    if (($c['word_cat'] ?? '') === 'proverb') {
        $prow = ['lango' => $f['lango'], 'english' => $f['english'] ?: null, 'meaning' => ($f['note'] ?? '') ?: null, 'clan_id' => $c['contributor_clan_id'] ?: null,
            'verified' => 1, 'status' => 'published', 'source' => $src, 'contributor' => $credit];
        $pid = val('SELECT id FROM proverbs WHERE source=?', [$src]);
        if ($pid) { update('proverbs', $prow, (int)$pid); return (int)$pid; }
        return insert('proverbs', $prow);
    }
    $row = ['lango' => mb_substr($f['lango'], 0, 190), 'english' => mb_substr($f['english'], 0, 500), 'pos' => $f['pos'] ?: null, 'category' => $f['cat'] ?: null,
        'example_lg' => $f['example_lg'] ?: null, 'example_en' => $f['example_en'] ?: null, 'source' => $src, 'verified' => 1, 'status' => 'published',
        'contributor' => $credit, 'fold' => mb_substr(lango_fold($f['lango']), 0, 200),
        'audio' => $c['media_kind'] === 'audio' ? $c['media_file'] : null];
    $exists = val('SELECT id FROM dictionary WHERE source=?', [$src]);
    if ($exists) { update('dictionary', $row, (int)$exists); return (int)$exists; }
    return insert('dictionary', $row);
}

function word_clan(array $c): string
{
    if ($c['contributor_clan_id']) return (string)val('SELECT name FROM clans WHERE id=?', [$c['contributor_clan_id']]);
    return (string)($c['contributor_clan_other'] ?? '');
}

function screen_words(): void
{
    $a = get('a'); $id = (int)get('id');

    if (is_post() && in_array($a, ['decide', 'quick'], true)) {
        csrf_check();
        $c = one("SELECT * FROM contributions WHERE id=? AND type='language'", [(int)post('id')]);
        if (!$c) deny();
        $back = aurl('words', 'review', ['id' => $c['id']]);
        $decision = $a === 'quick' ? 'approve' : post('decision');
        $isProv = ($c['word_cat'] ?? '') === 'proverb';
        $f = $a === 'quick'
            ? ['lango' => $c['title'], 'english' => (string)$c['description'], 'pos' => WORD_POS_ABBR[$c['word_pos']] ?? '', 'cat' => $c['word_cat'], 'example_lg' => (string)$c['example_lg'], 'example_en' => '', 'note' => (string)$c['word_note'], 'clan' => word_clan($c)]
            : ['lango' => post('lango'), 'english' => post('english'), 'pos' => WORD_POS_ABBR[post('pos')] ?? '', 'cat' => $isProv ? 'proverb' : (isset(WORD_CATS[post('word_cat')]) ? post('word_cat') : null),
               'example_lg' => post('example_lg'), 'example_en' => post('example_en'), 'note' => post('word_note'), 'clan' => word_clan($c)];
        $note = $a === 'quick' ? null : (post('review_note') ?: null);

        if ($decision === 'approve') {
            if ($f['lango'] === '' || $f['english'] === '') { flash('Both the Leb Lango word and its English meaning are needed before it can be added.', 'error'); redirect($back); }
            $did = word_publish($c, $f);
            update('contributions', ['status' => 'published', 'title' => mb_substr($f['lango'], 0, 220), 'description' => $f['english'], 'word_pos' => $c['word_pos'], 'word_cat' => $f['cat'], 'example_lg' => $f['example_lg'] ?: null, 'word_note' => $f['note'] ?: null,
                'review_note' => $note, 'reviewer_id' => user()['id'], 'updated_at' => date('Y-m-d H:i:s')], (int)$c['id']);
            award_points($c, 'published');
            audit('approve', 'dictionary', $did, $f['lango']);
            flash($isProv ? 'The proverb was added to the public proverbs page.' : '“' . $f['lango'] . '” was added to the dictionary and the translator.');
            $next = val("SELECT id FROM contributions WHERE type='language' AND status IN ('pending','under_review','needs_info') AND id<>? ORDER BY id LIMIT 1", [$c['id']]);
            redirect($next ? aurl('words', 'review', ['id' => $next]) : aurl('words'));
        }
        $status = ['needs_info' => 'needs_info', 'reject' => 'rejected', 'pending' => 'pending'][$decision] ?? null;
        if (!$status) deny();
        update('contributions', ['status' => $status, 'review_note' => $note, 'reviewer_id' => user()['id'], 'updated_at' => date('Y-m-d H:i:s')], (int)$c['id']);
        audit('review', 'contributions', (int)$c['id'], $status);
        flash($status === 'rejected' ? 'Suggestion marked as not accepted.' : 'Suggestion updated.');
        redirect(aurl('words'));
    }

    /* ----- review one suggestion ----- */
    if ($a === 'review' && $id) {
        $c = one("SELECT * FROM contributions WHERE id=? AND type='language'", [$id]);
        if (!$c) deny();
        $clan = word_clan($c);
        $dups = all('SELECT id, lango, english, verified, source FROM dictionary WHERE lango=? OR lango=? LIMIT 6', [$c['title'], lango_fold($c['title'])]);
        $dups = array_values(array_filter($dups, fn($d) => $d['source'] !== 'Community suggestion ' . $c['code']));
        $done = $c['status'] === 'published';
        $isProv = $c['word_cat'] === 'proverb';
        $added = $done ? one('SELECT id FROM ' . ($isProv ? 'proverbs' : 'dictionary') . ' WHERE source=?', ['Community suggestion ' . $c['code']]) : null;
        if ($isProv) $dups = all('SELECT id, lango, english FROM proverbs WHERE lango=? LIMIT 4', [$c['title']]);
        $dupMod = $isProv ? 'proverbs' : 'dictionary';
        admin_start(($isProv ? 'Proverb suggestion: ' : 'Word suggestion: ') . mb_strimwidth($c['title'], 0, 50, '…'), 'words', '<a class="btn btn--line" href="' . e(aurl('words')) . '">← All suggestions</a>');
        ?>
        <div class="cols two">
          <div class="panel">
            <p><span class="chip <?= $done ? 'ok' : 'warn' ?>"><?= e(CONTRIB_STATUS[$c['status']]) ?></span> <span class="dim">Code <b><?= e($c['code']) ?></b> · received <?= e(fmt_date($c['created_at'], 'j M Y, H:i')) ?></span></p>
            <h2 style="margin:8px 0 2px;font-size:1.9rem"><?= e($c['title']) ?></h2>
            <p style="font-size:1.1rem;margin-top:0"><?= e((string)$c['description']) ?></p>
            <?php if ($c['example_lg']): ?><p><b>Example:</b> <em><?= e($c['example_lg']) ?></em></p><?php endif; ?>
            <?php if ($c['word_note']): ?><p><b>Meaning / when it is used:</b> <?= e($c['word_note']) ?></p><?php endif; ?>
            <dl class="kv"><dt>Suggested by</dt><dd><?= e($c['name']) ?><?= $c['user_id'] ? ' <span class="chip ok">account</span>' : '' ?></dd>
              <dt>Clan</dt><dd><?= $clan !== '' ? e($clan) . ($c['contributor_clan_other'] ? ' <span class="chip warn">not in register</span>' : '') : '—' ?></dd>
              <dt>Email</dt><dd><?= e((string)$c['email']) ?: '—' ?></dd><dt>Phone</dt><dd><?= e((string)$c['phone']) ?: '—' ?></dd>
              <dt>Type</dt><dd><?= e(WORD_POS[$c['word_pos']] ?? '—') ?> · <?= e(WORD_CATS[$c['word_cat']] ?? 'no category') ?></dd>
              <dt>District / place</dt><dd><?= e((string)$c['location_text']) ?: '—' ?></dd><dt>Credit</dt><dd><?= ($c['credit'] ?? 1) ? 'Show contributor\'s name beside the word' : 'Keep anonymous' ?></dd></dl>
            <?php if ($c['media_kind'] === 'audio' && $c['media_file']): ?><h4>Pronunciation</h4><audio controls src="<?= e(media_url($c['media_file'])) ?>" style="width:100%"></audio><?php endif; ?>
            <?php if ($dups): ?><div class="alert alert-info" style="margin-top:16px"><b>Already in the dictionary:</b><br><?php foreach ($dups as $d): ?><a href="<?= e(aurl($dupMod, 'edit', ['id' => $d['id']])) ?>"><?= e($d['lango']) ?></a> — <?= e((string)$d['english']) ?><br><?php endforeach; ?><span class="dim">Check this is genuinely new before approving.</span></div><?php endif; ?>
          </div>
          <div>
            <?php if ($done): ?>
              <div class="panel"><h3>Added to the dictionary</h3><p>This word is live in the dictionary and the AI translator.<?= $added ? ' <a href="' . e(aurl('dictionary', 'edit', ['id' => $added['id']])) . '">Edit the dictionary entry</a>.' : '' ?></p></div>
            <?php else: ?>
            <form class="panel" method="post" action="<?= e(aurl('words', 'decide')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <h3>Check and publish</h3>
              <p class="dim">Correct spelling or the translation here if needed — what you save is what the public sees.</p>
              <?php if ($isProv): ?>
              <div class="field"><label>Proverb (Leb Lango) *</label><textarea name="lango" rows="2" required><?= e($c['title']) ?></textarea></div>
              <div class="field"><label>English translation *</label><textarea name="english" rows="2" required><?= e((string)$c['description']) ?></textarea></div>
              <div class="field"><label>Meaning / when it is used</label><textarea name="word_note" rows="3"><?= e((string)$c['word_note']) ?></textarea></div>
              <?php else: ?>
              <div class="fgrid2"><div class="field"><label>Leb Lango word *</label><input type="text" name="lango" value="<?= e($c['title']) ?>" required></div>
                <div class="field"><label>English meaning *</label><input type="text" name="english" value="<?= e((string)$c['description']) ?>" required></div></div>
              <div class="fgrid2"><div class="field"><label>Part of speech</label><select name="pos"><option value="">—</option><?php foreach (WORD_POS as $k => $l): ?><option value="<?= e($k) ?>" <?= $c['word_pos'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
                <div class="field"><label>Category</label><select name="word_cat"><option value="">—</option><?php foreach (WORD_CATS as $k => $l): ?><option value="<?= e($k) ?>" <?= $c['word_cat'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div></div>
              <div class="fgrid2"><div class="field"><label>Example (Leb Lango)</label><input type="text" name="example_lg" value="<?= e((string)$c['example_lg']) ?>"></div>
                <div class="field"><label>Example (English)</label><input type="text" name="example_en"></div></div>
              <?php endif; ?>
              <div class="field"><label>Note to the contributor</label><textarea name="review_note" rows="3" placeholder="Shown when they track their suggestion — thank them, or explain what is needed."><?= e((string)$c['review_note']) ?></textarea></div>
              <p class="dim">Approving adds it to the public <?= $isProv ? 'proverbs page' : 'dictionary and the AI translator' ?><?= $c['user_id'] ? ' and awards ' . (int)CONTRIB_POINTS['language'] . ' points' : '' ?>.</p>
              <div class="btnrow"><button class="btn btn--primary" name="decision" value="approve"><?= $isProv ? 'Approve &amp; add to proverbs' : 'Approve &amp; add to dictionary' ?></button>
                <button class="btn btn--line" name="decision" value="needs_info">Ask for more detail</button>
                <button class="btn btn--line danger" name="decision" value="reject">Not accepted</button></div>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php admin_end(); return;
    }

    /* ----- list ----- */
    $st = get('f', 'open'); $qs = get('q');
    $where = ["type='language'"]; $par = [];
    if ($st === 'open') $where[] = "status IN ('pending','under_review','needs_info')";
    elseif ($st === 'added') $where[] = "status='published'";
    elseif ($st === 'rejected') $where[] = "status='rejected'";
    if ($qs !== '') { $where[] = '(title LIKE ? OR description LIKE ? OR name LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
    $w = implode(' AND ', $where);
    $total = (int)val("SELECT COUNT(*) FROM contributions WHERE $w", $par);
    $pg = paginate($total, 25);
    $rows = all("SELECT * FROM contributions WHERE $w ORDER BY FIELD(status,'pending','under_review','needs_info','published','rejected'), id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
    $n = fn(string $cond) => (int)val("SELECT COUNT(*) FROM contributions WHERE type='language' AND $cond");
    admin_start('Word suggestions', 'words');
    ?>
    <div class="panel"><p>Words, names of animals, places and everyday terms suggested by Lango speakers. Review each one, correct it if needed, then approve it — approved words go straight into the public dictionary and the AI translator.</p>
      <div class="chips">
        <a class="chipbtn <?= $st === 'open' ? 'on' : '' ?>" href="<?= e(aurl('words')) ?>">Waiting for review <em><?= $n("status IN ('pending','under_review','needs_info')") ?></em></a>
        <a class="chipbtn <?= $st === 'added' ? 'on' : '' ?>" href="<?= e(aurl('words', '', ['f' => 'added'])) ?>">Added to dictionary <em><?= $n("status='published'") ?></em></a>
        <a class="chipbtn <?= $st === 'rejected' ? 'on' : '' ?>" href="<?= e(aurl('words', '', ['f' => 'rejected'])) ?>">Not accepted <em><?= $n("status='rejected'") ?></em></a>
        <a class="chipbtn <?= $st === 'all' ? 'on' : '' ?>" href="<?= e(aurl('words', '', ['f' => 'all'])) ?>">All</a>
      </div></div>
    <form class="toolbar" method="get"><input type="hidden" name="m" value="words"><input type="hidden" name="f" value="<?= e($st) ?>"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="Search word, meaning or name…"><button class="btn btn--dark btn--sm"><?= icon('search') ?></button><span class="grow"></span><span class="dim"><?= number_format($total) ?> suggestions</span></form>
    <div class="panel flush"><div class="tablewrap"><table class="table"><thead><tr><th>Leb Lango</th><th>English</th><th>Category</th><th>From</th><th>Received</th><th>Status</th><th></th></tr></thead><tbody>
      <?php foreach ($rows as $r): $open = in_array($r['status'], ['pending', 'under_review', 'needs_info'], true); ?>
        <tr><td><a href="<?= e(aurl('words', 'review', ['id' => $r['id']])) ?>"><b><?= e($r['title']) ?></b></a></td><td><?= e(mb_strimwidth((string)$r['description'], 0, 60, '…')) ?></td>
          <td><?= e($r['word_cat'] === 'proverb' ? 'Proverb' : (WORD_CATS[$r['word_cat']] ?? '—')) ?></td><td><?= e($r['name']) ?><?php $cl = word_clan($r); if ($cl !== ''): ?><br><span class="dim"><?= e($cl) ?></span><?php endif; ?></td>
          <td><?= e(fmt_date($r['created_at'])) ?></td>
          <td><span class="chip <?= $r['status'] === 'published' ? 'ok' : ($r['status'] === 'rejected' ? 'bad' : 'warn') ?>"><?= e($r['status'] === 'published' ? 'Added' : CONTRIB_STATUS[$r['status']]) ?></span></td>
          <td class="rowact"><?php if ($open): ?><form method="post" action="<?= e(aurl('words', 'quick')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="mini" title="Add to the dictionary exactly as submitted">Quick add</button></form><?php endif; ?>
            <a class="mini" href="<?= e(aurl('words', 'review', ['id' => $r['id']])) ?>">Review</a></td></tr>
      <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="empty">No word suggestions here.</td></tr><?php endif; ?></tbody></table></div></div>
    <?= pager($pg) ?>
    <?php admin_end();
}

/* ============================== BULK CLAN ENTRY ============================== */
function screen_clan_quick(): void
{
    if (is_post()) {
        csrf_check();
        $names = preg_split('/[\r\n,;]+/', (string)($_POST['names'] ?? '')) ?: [];
        $status = post('status') === 'draft' ? 'draft' : 'published';
        $did = (int)post('district_id') ?: null;
        $have = array_flip(array_map('mb_strtolower', array_column(all('SELECT name FROM clans'), 'name')));
        $added = 0; $dup = 0;
        foreach ($names as $nm) {
            $nm = trim(preg_replace('/\s+/u', ' ', (string)$nm));
            if (mb_strlen($nm) < 2 || mb_strlen($nm) > 150) continue;
            if (isset($have[mb_strtolower($nm)])) { $dup++; continue; }
            insert('clans', ['name' => $nm, 'slug' => unique_slug('clans', slugify($nm)), 'district_id' => $did, 'leader_title' => 'Awitong', 'verified' => post('verified') ? 1 : 0, 'status' => $status, 'source_note' => 'Added in bulk by ' . user()['name']]);
            $have[mb_strtolower($nm)] = 1; $added++;
        }
        audit('create', 'clans', null, "quick add $added");
        flash("Added $added clan" . ($added === 1 ? '' : 's') . ($dup ? " ($dup already in the register)" : '') . '. Add each clan\'s leader and photo from Clans.');
        redirect(aurl('clans'));
    }
    $total = (int)val('SELECT COUNT(*) FROM clans');
    $recent = all('SELECT name, status, created_at FROM clans ORDER BY id DESC LIMIT 8');
    admin_start('Add clans in bulk', 'clan_quick', '<a class="btn btn--line" href="' . e(aurl('clans')) . '">← All clans</a>');
    ?>
    <div class="cols two">
      <form class="panel" method="post"><?= csrf_field() ?>
        <h3>Paste clan names</h3>
        <p class="dim">One clan per line (or separated by commas). Names already in the register are skipped. LCI has <?= (int)setting('clan_total', '149') ?> registered clans; <b><?= $total ?></b> are loaded so far.</p>
        <div class="field"><label>Clan names</label><textarea name="names" rows="14" placeholder="Otengoro Olang&#10;Atek&#10;Okarowok…" required><?= e(str_replace(['|', ';'], "\n", get('names'))) ?></textarea></div>
        <div class="fgrid2"><div class="field"><label>Show on the website</label><select name="status"><option value="published">Yes, publish now</option><option value="draft">No, keep as draft</option></select></div>
          <div class="field"><label>District (optional)</label><select name="district_id"><option value="">—</option><?php foreach (field_options(['options' => 'districts:id:name']) as $dk => $dn): ?><option value="<?= (int)$dk ?>"><?= e($dn) ?></option><?php endforeach; ?></select></div></div>
        <label class="check"><input type="checkbox" name="verified" value="1"> Mark these as verified by LCI</label>
        <div class="btnrow"><button class="btn btn--primary">Add clans</button></div>
      </form>
      <div class="panel"><h3>What happens next</h3>
        <ul><li>Each name becomes a clan page and appears in the clan drop-down on every registration form.</li>
          <li>Open a clan in <b>Clans</b> to add its leader (Awitong), the leader's photograph, totem, district and history.</li>
          <li>Once a leader is recorded, the AI assistant answers “Who is the clan leader of …?” and the clan page shows the leader.</li></ul>
        <h4 style="margin-top:20px">Most recently added</h4>
        <?php foreach ($recent as $r): ?><div class="line"><b><?= e($r['name']) ?></b><span class="chip <?= $r['status'] === 'published' ? 'ok' : 'warn' ?>"><?= e($r['status']) ?></span></div><?php endforeach; ?></div>
    </div>
    <?php admin_end();
}
