<?php
declare(strict_types=1);

/* ---------- Clan requests: clans that members typed because theirs was not in the register ---------- */

/** The tables that hold a typed clan name, with the column that holds the name and the column that holds the register id. */
function clan_request_sources(): array
{
    return [['users', 'clan_other', 'clan_id'], ['contributions', 'contributor_clan_other', 'contributor_clan_id'], ['applications', 'clan_other', 'clan_id'],
        ['event_registrations', 'clan_other', 'clan_id'], ['professionals', 'clan_other', 'clan_id']];
}

/** Typed clan names that are not linked to the register yet, grouped without regard to case: [key => [name, people, where]]. */
function clan_request_groups(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $groups = [];
    foreach (clan_request_sources() as [$t, $other, $cid]) {
        try {
            $rows = all("SELECT TRIM(`$other`) AS n, COUNT(*) AS c FROM `$t` WHERE `$other` IS NOT NULL AND TRIM(`$other`) <> '' AND (`$cid` IS NULL OR `$cid` = 0) GROUP BY TRIM(`$other`)");
        } catch (Throwable $e) {
            continue;
        }
        foreach ($rows as $r) {
            $key = mb_strtolower((string)preg_replace('/\s+/u', ' ', (string)$r['n']));
            if (!isset($groups[$key])) $groups[$key] = ['name' => (string)$r['n'], 'best' => 0, 'people' => 0, 'where' => []];
            if ((int)$r['c'] > $groups[$key]['best']) { $groups[$key]['best'] = (int)$r['c']; $groups[$key]['name'] = (string)$r['n']; }
            $groups[$key]['people'] += (int)$r['c'];
            $groups[$key]['where'][$t] = true;
        }
    }
    uasort($groups, fn($a, $b) => $b['people'] <=> $a['people'] ?: strcmp($a['name'], $b['name']));
    return $cache = $groups;
}

/** Points every record that typed this name at the register entry, and forgets the typed text. */
function clan_request_link(string $key, int $clanId): int
{
    $n = 0;
    foreach (clan_request_sources() as [$t, $other, $cid]) {
        try {
            $n += q("UPDATE `$t` SET `$cid` = ?, `$other` = NULL WHERE LOWER(REGEXP_REPLACE(TRIM(`$other`), '[[:space:]]+', ' ')) = ? AND (`$cid` IS NULL OR `$cid` = 0)", [$clanId, $key])->rowCount();
        } catch (Throwable $e) {
            continue;
        }
    }
    return $n;
}

function screen_clan_requests(): void
{
    $groups = clan_request_groups();
    if (is_post()) {
        csrf_check();
        $key = mb_strtolower(trim((string)post('key')));
        if (!isset($groups[$key])) { flash('That request is no longer waiting.', 'error'); redirect(aurl('clan_requests')); }
        if (post('do') === 'merge') {
            $cid = (int)post('clan_id');
            $name = $cid ? (string)val('SELECT name FROM clans WHERE id=?', [$cid]) : '';
            if ($name === '') { flash('Choose the clan it belongs to.', 'error'); redirect(aurl('clan_requests')); }
            $n = clan_request_link($key, $cid);
            audit('update', 'clans', $cid, "clan request merged ($n)");
            flash(sprintf('“%s” is now linked to %s (%d record%s).', $groups[$key]['name'], $name, $n, $n === 1 ? '' : 's'));
        } elseif (post('do') === 'add') {
            $name = trim((string)preg_replace('/\s+/u', ' ', $groups[$key]['name']));
            $cid = (int)val('SELECT id FROM clans WHERE LOWER(name)=?', [mb_strtolower($name)]);
            if (!$cid) {
                $cid = insert('clans', ['name' => $name, 'slug' => unique_slug('clans', slugify($name)), 'leader_title' => 'Awitong', 'verified' => 0, 'status' => 'published',
                    'source_note' => 'Added from ' . $groups[$key]['people'] . ' member registration' . ($groups[$key]['people'] === 1 ? '' : 's') . ' (not yet verified by LCI)']);
            }
            $n = clan_request_link($key, $cid);
            audit('create', 'clans', $cid, "clan request added ($n)");
            flash(sprintf('“%s” is now in the clan register, awaiting LCI verification (%d record%s linked).', $name, $n, $n === 1 ? '' : 's'));
        } else {
            flash('Nothing to do.', 'error');
        }
        redirect(aurl('clan_requests'));
    }

    $clans = all('SELECT id, name FROM clans ORDER BY name');
    admin_start('Clan requests', 'clan_requests', '<a class="btn btn--line" href="' . e(aurl('clan_quick')) . '">Add clans in bulk</a>');
    ?>
    <div class="panel">
      <h3>Clans members typed because theirs is not in the register</h3>
      <p class="dim">When someone signs up or contributes and picks “My clan is not listed”, the name they type waits here. <b>Add to register</b> creates the clan (shown as awaiting verification, so nothing is claimed as confirmed) and links everyone who typed it. If it is a spelling of a clan you already have, link it to that clan instead.</p>
      <?php if (!$groups): ?>
        <p class="dim">No clan names are waiting. Members who pick a clan from the list are already linked to it.</p>
      <?php else: ?>
        <div class="tablewrap"><table class="table"><thead><tr><th>Typed by members</th><th>People</th><th>Where</th><th style="width:44%">Action</th></tr></thead><tbody>
        <?php foreach ($groups as $key => $g): ?>
          <tr>
            <td><b><?= e($g['name']) ?></b></td>
            <td><?= (int)$g['people'] ?></td>
            <td class="dim"><?= e(implode(', ', array_map(fn($t) => str_replace('_', ' ', (string)$t), array_keys($g['where'])))) ?></td>
            <td>
              <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="key" value="<?= e((string)$key) ?>"><input type="hidden" name="do" value="add"><button class="btn btn--primary btn--sm">Add to register</button></form>
              <form method="post" style="display:inline-flex;gap:6px;margin-left:8px"><?= csrf_field() ?><input type="hidden" name="key" value="<?= e((string)$key) ?>"><input type="hidden" name="do" value="merge">
                <select name="clan_id" aria-label="Same as"><option value="">Same as…</option><?php foreach ($clans as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
                <button class="btn btn--line btn--sm">Link</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </div>
    <?php admin_end();
}

/* ---------- Clan messages: what members write in their clan groups, with reports first ---------- */
function screen_clan_messages(): void
{
    if (is_post()) {
        csrf_check();
        $id = (int)post('id');
        $do = post('do');
        $m = $id ? one('SELECT m.*, c.name clan FROM clan_messages m LEFT JOIN clans c ON c.id=m.clan_id WHERE m.id=?', [$id]) : null;
        if ($m && $do === 'hide') { q("UPDATE clan_messages SET status='hidden', hidden_by=? WHERE id=?", [user()['id'], $id]); audit('update', 'clan_messages', $id, 'hidden'); flash('Message hidden from the group.'); }
        elseif ($m && $do === 'show') { q("UPDATE clan_messages SET status='visible', hidden_by=NULL WHERE id=?", [$id]); q('DELETE FROM clan_message_reports WHERE message_id=?', [$id]); audit('update', 'clan_messages', $id, 'restored'); flash('Message restored and its reports cleared.'); }
        elseif ($m && $do === 'delete') { q('DELETE FROM clan_message_reports WHERE message_id=?', [$id]); q('DELETE FROM clan_messages WHERE id=?', [$id]); audit('delete', 'clan_messages', $id); flash('Message deleted.'); }
        elseif ($m && $do === 'mute' && $m['user_id']) { q('UPDATE users SET clan_joined_at=NULL, clan_seen_id=0 WHERE id=?', [$m['user_id']]); audit('update', 'users', (int)$m['user_id'], 'removed from the clan group'); flash('The member was removed from the clan group (they can join again).'); }
        redirect(aurl('clan_messages', '', array_filter(['filter' => get('filter')])));
    }
    $reportedOnly = get('filter') !== 'all';
    $where = $reportedOnly ? 'WHERE (SELECT COUNT(*) FROM clan_message_reports r WHERE r.message_id=m.id) > 0' : '';
    $rows = all("SELECT m.*, c.name clan, u.name author, u.email, (SELECT COUNT(*) FROM clan_message_reports r WHERE r.message_id=m.id) reports
                 FROM clan_messages m LEFT JOIN clans c ON c.id=m.clan_id LEFT JOIN users u ON u.id=m.user_id $where ORDER BY m.id DESC LIMIT 150");
    $tabs = '<a class="btn btn--line' . ($reportedOnly ? ' on' : '') . '" href="' . e(aurl('clan_messages')) . '">Reported</a> <a class="btn btn--line' . ($reportedOnly ? '' : ' on') . '" href="' . e(aurl('clan_messages', '', ['filter' => 'all'])) . '">All recent</a>';
    admin_start('Clan messages', 'clan_messages', $tabs);
    ?>
    <div class="panel">
      <h3>Messages in the clan groups</h3>
      <p class="dim">Members write these in their own clan's group in the app. A message reported by three different members is hidden automatically until you look at it. Clan leaders can also delete messages in their own group from the app.</p>
      <?php if (!$rows): ?>
        <div class="empty"><?= $reportedOnly ? 'No reported messages. Nothing needs your attention.' : 'No messages have been written yet.' ?></div>
      <?php else: ?>
        <div class="tablewrap"><table class="table"><thead><tr><th>Message</th><th>Clan</th><th>Reports</th><th style="width:34%">Action</th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><div style="white-space:pre-wrap"><?= e($r['body']) ?></div><div class="dim"><?= e((string)($r['author'] ?: 'Former member')) ?><?= $r['email'] ? ' · ' . e($r['email']) : '' ?> · <?= e((string)$r['created_at']) ?> · <span class="chip <?= $r['status'] === 'visible' ? 'ok' : 'warn' ?>"><?= e($r['status']) ?></span></div></td>
            <td><?= e((string)$r['clan']) ?></td>
            <td><?= (int)$r['reports'] ?></td>
            <td>
              <?php foreach ([['hide', 'Hide', 'btn--line'], ['show', 'Restore', 'btn--line'], ['mute', 'Remove member', 'btn--line'], ['delete', 'Delete', 'btn--line danger']] as [$do, $label, $cls]):
                  if (($do === 'hide' && $r['status'] !== 'visible') || ($do === 'show' && $r['status'] === 'visible') || ($do === 'mute' && !$r['user_id'])) continue; ?>
                <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="<?= $do ?>"><button class="btn <?= $cls ?> btn--sm"><?= $label ?></button></form>
              <?php endforeach; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </div>
    <?php admin_end();
}
