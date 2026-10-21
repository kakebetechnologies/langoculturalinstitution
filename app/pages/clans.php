<?php
declare(strict_types=1);

/* /clans/{slug}  → clan detail */
if (!empty($seg[1])) {
    $c = one("SELECT c.*, d.name district FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE c.slug=? AND c.status='published'", [$seg[1]]);
    if (!$c) not_found();
    $events = all("SELECT * FROM heritage_items WHERE clan_id=? AND status='published' ORDER BY title", [$c['id']]);
    $words = (int)0;
    page_start($c['name'], ['image' => $c['image'], 'desc' => $c['summary'] ?: excerpt($c['history']), 'hero' => ['eyebrow' => __('Clan'), 'title' => $c['name'] . ' ' . __('Clan'), 'sub' => $c['summary'], 'crumbs' => [[__('Clan Explorer'), 'clans'], [$c['name'], null]], 'image' => $c['image'] ?: 'assets/img/photos/hero-site.jpg']]);
    ?>
    <section class="section"><div class="container article">
      <div class="prose">
        <?php if ($c['history']): ?><h2><?php _e('Clan history'); ?></h2><?= rich(t($c, 'history')) ?><?php endif; ?>
        <?php if ($c['lineage']): ?><h2><?php _e('Lineage'); ?></h2><?= rich($c['lineage']) ?><?php endif; ?>
        <?php if ($c['notable_members']): ?><h2><?php _e('Notable members'); ?></h2><?= rich($c['notable_members']) ?><?php endif; ?>
        <?php if (!$c['history'] && !$c['lineage']): echo empty_state(__('History being documented'), __('Clan elders can help complete this page.'), 'book', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute?type=oral_history')) . '">' . e(__('Contribute this clan’s history')) . '</a></p>'); endif; ?>
        <?php if ($events): ?><h2><?php _e('Clan heritage'); ?></h2><ul><?php foreach ($events as $h): ?><li><a href="<?= e(url('heritage/item/' . $h['slug'])) ?>"><?= e(t($h, 'title')) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
      </div>
      <aside class="aside">
        <div class="box"><h4><?php _e('Clan profile'); ?></h4>
          <dl class="kv">
            <?php if ($c['totem']): ?><dt><?php _e('Totem'); ?></dt><dd><?= e($c['totem']) ?><?= $c['totem_type'] ? ' (' . e($c['totem_type']) . ')' : '' ?></dd><?php endif; ?>
            <?php if ($c['district']): ?><dt><?php _e('District'); ?></dt><dd><?= e($c['district']) ?></dd><?php endif; ?>
            <?php if ($c['leader_name']): ?><dt><?= e($c['leader_title'] ?: 'Awitong') ?></dt><dd><?= e($c['leader_name']) ?></dd><?php endif; ?>
            <dt><?php _e('Status'); ?></dt><dd><?= $c['verified'] ? '<span class="tag tag--green">' . e(__('Verified by LCI')) . '</span>' : '<span class="tag tag--gold">' . e(__('Awaiting verification')) . '</span>' ?></dd>
          </dl>
        </div>
        <div class="box box--dark"><h4><?php _e('Know this clan?'); ?></h4><p><?php _e('Clan leaders can verify and update this page. Everyone can contribute stories and photographs.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('contribute')) ?>"><?php _e('Contribute'); ?></a></div>
      </aside>
    </div></section>
    <?php page_end(); return;
}

/* /clans → explorer */
$qs = get('q'); $dist = get('district'); $letter = strtoupper(substr(get('letter'), 0, 1));
$where = ["c.status='published'"]; $par = [];
if ($qs !== '') { $where[] = '(c.name LIKE ? OR c.totem LIKE ? OR c.leader_name LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
if ($dist !== '') { $where[] = 'd.slug=?'; $par[] = $dist; }
if ($letter !== '' && ctype_alpha($letter)) { $where[] = 'c.name LIKE ?'; $par[] = $letter . '%'; }
$w = implode(' AND ', $where);
$total = (int)val("SELECT COUNT(*) FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE $w", $par);
$pg = paginate($total, 24);
$rows = all("SELECT c.*, d.name district FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE $w ORDER BY c.name LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
$districts = all("SELECT name, slug FROM districts WHERE status='published' ORDER BY name");
$registered = (int)val("SELECT COUNT(*) FROM clans WHERE status='published'");

page_start(__('Clan Explorer'), ['hero' => ['eyebrow' => __('Heritage'), 'title' => __('Clan Explorer'), 'sub' => __('Histories, totems, lineages and leaders of the 149 Lango clans.'), 'crumbs' => [[__('Heritage'), 'heritage'], [__('Clans'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
?>
<section class="section">
  <div class="container">
    <form class="searchbar" method="get" action="<?= e(url('clans')) ?>" style="margin-bottom:26px">
      <input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search clans by name, totem or leader…')) ?>" aria-label="Search clans">
      <?php if ($dist): ?><input type="hidden" name="district" value="<?= e($dist) ?>"><?php endif; ?>
      <button class="btn btn--primary"><?= icon('search') ?> <?php _e('Search'); ?></button>
    </form>
    <div class="filters" style="justify-content:center">
      <a class="pill <?= $dist === '' ? 'on' : '' ?>" href="<?= e(url('clans')) ?>"><?php _e('All districts'); ?></a>
      <?php foreach ($districts as $d): ?><a class="pill <?= $dist === $d['slug'] ? 'on' : '' ?>" href="<?= e(url('clans', ['district' => $d['slug']])) ?>"><?= e($d['name']) ?></a><?php endforeach; ?>
    </div>
    <?php if ($rows): ?>
      <p style="color:var(--muted)"><?= $registered ?> / <?= (int)setting('clan_total', '149') ?> <?php _e('clans published so far'); ?> · <?= $total ?> <?php _e('shown'); ?></p>
      <div class="grid g4">
        <?php foreach ($rows as $c): ?>
          <a class="tile reveal" href="<?= e(url('clans/' . $c['slug'])) ?>">
            <span class="ico"><?= icon('users') ?></span>
            <h3><?= e($c['name']) ?></h3>
            <p><?= e($c['summary'] ?: ($c['totem'] ? __('Totem') . ': ' . $c['totem'] : __('Clan profile'))) ?></p>
            <span class="meta"><?= e((string)$c['district']) ?><?= $c['verified'] ? ' · ✓ ' . e(__('Verified')) : '' ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <?= pager($pg) ?>
    <?php else: ?>
      <?= empty_state(__('The clan register is being digitised'), __('LCI is documenting all 149 clans. Each clan page will carry its history, totem, lineage and Awitong once verified by its leaders. Clan leaders and members can help right now.'), 'users',
          '<p style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap"><a class="btn btn--primary btn--sm" href="' . e(url('apply/clan')) . '">' . e(__('Register your clan')) . '</a><a class="btn btn--outline btn--sm" href="' . e(url('contribute?type=oral_history')) . '">' . e(__('Share clan history')) . '</a></p>') ?>
    <?php endif; ?>
  </div>
</section>
<?php page_end(); ?>
