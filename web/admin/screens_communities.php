<?php
declare(strict_types=1);

/* ================================ COMMUNITIES ================================
   The tribes that use the app. Each community has its own content (clans or chiefdoms, districts, dictionary, history ...); switching one on makes it
   a choice on the app's welcome and sign-in screens and on the website; switching it off hides it again (its content is kept). Adding a community
   creates its content tables, which the admin then fills (use the "Editing" switch in the sidebar to choose which community's content is edited). */

function screen_communities(): void
{
    require_once ROOT . '/database/communities.php';
    $rows = communities(true, true);

    if (is_post()) {
        csrf_check();
        $a = get('a');
        $id = (int)post('id');
        $target = $id ? one('SELECT * FROM communities WHERE id=?', [$id]) : null;
        if ($a === 'toggle' && $target) {
            $on = (int)$target['enabled'] === 1;
            $others = (int)val('SELECT COUNT(*) FROM communities WHERE enabled=1 AND id<>?', [$id]);
            if ($on && !$others) flash('At least one community must stay switched on.', 'error');
            else {
                q('UPDATE communities SET enabled=? WHERE id=?', [$on ? 0 : 1, $id]);
                audit($on ? 'disable' : 'enable', 'communities', $id, $target['name']);
                flash($target['name'] . ($on ? ' is switched off: it no longer appears in the app or on the website.' : ' is switched on: people can now choose it in the app and on the website.'));
            }
        } elseif ($a === 'save' && $target) {
            $f = ['name' => post('name'), 'language' => post('language'), 'people' => post('people'), 'register_one' => post('register_one'), 'register_many' => post('register_many'),
                'leader' => post('leader'), 'region' => post('region') ?: null, 'capital' => post('capital') ?: null];
            if ($f['name'] === '' || $f['language'] === '' || $f['people'] === '' || $f['register_one'] === '' || $f['register_many'] === '' || $f['leader'] === '') flash('Please fill in the name, the language, the people and the clan words.', 'error');
            else { update('communities', array_map(fn($v) => is_string($v) ? mb_substr($v, 0, 120) : $v, $f), $id); audit('update', 'communities', $id, $f['name']); flash('Saved.'); }
        } elseif ($a === 'add') {
            $name = mb_substr(post('name'), 0, 80);
            $code = substr(preg_replace('/[^a-z]/', '', strtolower($name)), 0, 12);
            if (strlen($code) < 3 || in_array($code, ['en', 'all', 'admin', 'api', 'new'], true)) flash('Please give the community\'s name in letters (at least three).', 'error');
            elseif (val('SELECT COUNT(*) FROM communities WHERE code=?', [$code])) flash('There is already a community with that name.', 'error');
            elseif (post('language') === '' || post('people') === '') flash('Please fill in the language and the people.', 'error');
            else {
                $nid = insert('communities', ['code' => $code, 'name' => $name, 'language' => mb_substr(post('language'), 0, 80), 'people' => mb_substr(post('people'), 0, 80),
                    'register_one' => mb_substr(post('register_one') ?: 'Clan', 0, 40), 'register_many' => mb_substr(post('register_many') ?: 'Clans', 0, 40), 'leader' => mb_substr(post('leader') ?: 'Leader', 0, 40),
                    'region' => mb_substr(post('region'), 0, 120) ?: null, 'capital' => mb_substr(post('capital'), 0, 120) ?: null, 'prefix' => '', 'enabled' => 0,
                    'sort' => 1 + (int)val('SELECT COALESCE(MAX(sort), 0) FROM communities')]);
                $prefix = 'x' . $nid . '_';
                update('communities', ['prefix' => $prefix], $nid);
                lci_community_tables(db(), $prefix);
                audit('create', 'communities', $nid, $name);
                flash($name . ' is added, switched off. Fill in its content (choose it with the "Editing" switch in the sidebar), then switch it on here.');
            }
        }
        redirect(aurl('communities'));
    }

    admin_start('Communities', 'communities');
    ?>
    <div class="panel"><h3>One app for every tribe</h3>
      <p class="dim">A community that is <b>on</b> appears as a choice when someone signs in or creates an account, in the app and on the website. People then see that community only: its clans or chiefdoms, districts, people, history, heritage, dictionary and the rest. Only the translator and the word comparison work across communities. Switching a community off hides it again and keeps its content. To edit a community's content, choose it with the <b>Editing</b> switch in the sidebar.</p>
    </div>
    <div class="panel flush"><div class="tablewrap"><table class="table"><thead><tr><th>Community</th><th>Language</th><th>Words</th><th>Members</th><th>Status</th><th></th></tr></thead><tbody>
      <?php foreach ($rows as $c): $words = (int)with_community($c['code'], fn() => val("SELECT COUNT(*) FROM dictionary WHERE status='published'")); $members = (int)val('SELECT COUNT(*) FROM users WHERE culture=? AND role=?', [$c['code'], 'contributor']); ?>
        <tr>
          <td><b><?= e($c['name']) ?></b><br><span class="dim">Clans are called <?= e($c['register']) ?> / <?= e($c['registers']) ?> · a leader is a <?= e($c['leader']) ?></span></td>
          <td><?= e($c['language']) ?></td>
          <td><?= number_format($words) ?></td>
          <td><?= number_format($members) ?></td>
          <td><span class="chip <?= $c['enabled'] ? 'ok' : 'warn' ?>"><?= $c['enabled'] ? 'On' : 'Off' ?></span></td>
          <td style="white-space:nowrap">
            <form method="post" action="<?= e(aurl('communities', 'toggle')) ?>" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)(val('SELECT id FROM communities WHERE code=?', [$c['code']])) ?>"><button class="btn btn--line"><?= $c['enabled'] ? 'Switch off' : 'Switch on' ?></button></form>
            <a class="btn btn--line" href="<?= e(aurl('communities', '', ['edit' => $c['code']])) ?>">Edit</a>
            <a class="btn btn--line" href="<?= e(url('admin/', ['community' => $c['code']])) ?>">Edit its content</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody></table></div></div>
    <?php
    $edit = get('edit') !== '' ? one('SELECT * FROM communities WHERE code=?', [get('edit')]) : null;
    $form = function (string $action, array $v, string $button): void { ?>
      <form method="post" action="<?= e(aurl('communities', $action)) ?>" class="panel"><?= csrf_field() ?>
        <?php if (!empty($v['id'])): ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><?php endif; ?>
        <div class="formgrid">
          <?php if ($action === 'add' || !empty($v['id'])): foreach ([
              ['name', 'Name of the community', 'Acholi'], ['language', 'Name of its language', 'Leb Acholi'], ['people', 'What its people are called', 'Acholi'],
              ['register_one', 'One clan, in their words', 'Chiefdom'], ['register_many', 'Many clans, in their words', 'Chiefdoms'], ['leader', 'Title of a clan\'s leader', 'Rwot'],
              ['region', 'Where they live (used in page text)', 'northern Uganda'], ['capital', 'Their main city (used in page text)', 'Gulu City']] as [$k, $l, $ph]): ?>
            <div class="field"><label><?= e($l) ?></label><input name="<?= e($k) ?>" value="<?= e((string)($v[$k] ?? '')) ?>" placeholder="<?= e($ph) ?>" maxlength="120"></div>
          <?php endforeach; endif; ?>
        </div>
        <div class="formfoot" style="margin-top:14px"><button class="btn btn--primary"><?= e($button) ?></button></div>
      </form>
    <?php };
    if ($edit) { echo '<h3 style="margin:26px 0 8px">Edit ' . e($edit['name']) . '</h3>'; $form('save', $edit, 'Save'); }
    echo '<h3 style="margin:26px 0 8px">Add a community</h3>';
    $form('add', [], 'Add the community');
    admin_end();
}
