<?php
declare(strict_types=1);

$sub = $seg[1] ?? '';

/* ---------- documents ---------- */
if ($sub === 'documents') {
    $cat = get('category'); $qs = get('q');
    $where = ["status='published'"]; $par = [];
    if (in_array($cat, ['policy', 'governance', 'historical', 'research', 'report', 'other'], true)) { $where[] = 'category=?'; $par[] = $cat; }
    if ($qs !== '') { $where[] = '(title LIKE ? OR description LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l); }
    $rows = all('SELECT * FROM documents WHERE ' . implode(' AND ', $where) . ' ORDER BY year DESC, id DESC', $par);
    page_start(__('Official Documents'), ['hero' => ['eyebrow' => __('Data Center'), 'title' => __('Official documents'), 'sub' => __('Policies, governance records, historical documents and research papers.'), 'crumbs' => [[__('Library'), 'library'], [__('Documents'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
    ?>
    <section class="section"><div class="container">
      <div class="filters"><a class="pill <?= $cat === '' ? 'on' : '' ?>" href="<?= e(url('library/documents')) ?>"><?php _e('All'); ?></a>
        <?php foreach (['policy' => 'Policies', 'governance' => 'Governance', 'historical' => 'Historical', 'research' => 'Research', 'report' => 'Reports'] as $k => $l): ?><a class="pill <?= $cat === $k ? 'on' : '' ?>" href="<?= e(url('library/documents', ['category' => $k])) ?>"><?= e(__($l)) ?></a><?php endforeach; ?>
        <form method="get" style="margin-left:auto"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search documents…')) ?>" style="padding:.55em 1em;border-radius:999px" aria-label="Search"></form></div>
      <?php if ($rows): ?><div class="grid g3"><?php foreach ($rows as $d): ?>
        <a class="box reveal" style="display:block;color:inherit" href="<?= e(url('download/' . $d['id'])) ?>"><div class="meta"><span class="tag"><?= e(__(ucfirst($d['category']))) ?></span><?php if ($d['year']): ?><span><?= (int)$d['year'] ?></span><?php endif; ?></div>
          <h4 style="margin-top:10px"><?= e($d['title']) ?></h4><p style="color:var(--muted);font-size:.92rem"><?= e(excerpt($d['description'], 120)) ?></p><b><?= icon('download') ?> <?php _e('Download'); ?></b></a>
      <?php endforeach; ?></div>
      <?php else: echo empty_state(__('No documents published yet'), __('Official policies and historical records will be added by the institution.'), 'file'); endif; ?>
    </div></section>
    <?php page_end(); return;
}
if ($sub !== '') not_found();

/* ---------- Data Center: search with facets ---------- */
$qs = get('q'); $only = get('type');
$results = []; $facets = [];
if (mb_strlen($qs) >= 2) {
    $results = search_all($qs, 200);
    foreach ($results as $r) $facets[$r['type']] = ($facets[$r['type']] ?? 0) + 1;
    if ($only && isset(SEARCH_TYPE_LABELS[$only])) $results = array_values(array_filter($results, fn($r) => $r['type'] === $only));
}
$pg = paginate(count($results), 20);
$show = array_slice($results, $pg['offset'], $pg['per']);

$c = site_counts();
$cat = [
    ['clans', 'users', 'Clan data', (int)val("SELECT COUNT(*) FROM clans WHERE status='published'") . ' / ' . $c['clans'], 'Clan names, totems, lineages, leaders.'],
    ['heritage', 'star', 'Cultural heritage', (int)val("SELECT COUNT(*) FROM heritage_items WHERE status='published'"), 'Ceremonies, attire, crafts, music, dance, symbols.'],
    ['species', 'leaf', 'Biodiversity', $c['species'], 'Native plants, animals, medicinal plants.'],
    ['language/dictionary', 'book', 'Language', number_format($c['words']), 'Dictionary entries, proverbs, lessons.'],
    ['media', 'image', 'Media archive', (int)val("SELECT COUNT(*) FROM media WHERE status='published'"), 'Photos, videos, audio and podcasts.'],
    ['library/documents', 'file', 'Official documents', (int)val("SELECT COUNT(*) FROM documents WHERE status='published'"), 'Policies, records, research papers.'],
    ['professionals', 'users', 'Professionals directory', (int)val("SELECT COUNT(*) FROM professionals WHERE status='published'"), 'Verified Lango professionals.'],
    ['events', 'calendar', 'Events & programs', (int)val("SELECT COUNT(*) FROM events WHERE status='published'") + (int)val("SELECT COUNT(*) FROM programs WHERE status='published'"), 'Calendar and foundation programs.'],
];
page_start(__('Data Center'), ['hero' => ['eyebrow' => __('The LCI digital nerve center'), 'title' => __('Data Center'), 'sub' => __('One secure, searchable home for Lango clans, language, heritage, biodiversity, media and documents.'), 'crumbs' => [[__('Library'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section"><div class="container">
  <form class="searchbar" method="get" action="<?= e(url('library')) ?>"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search clans, words, ceremonies, species, news…')) ?>" aria-label="Search" autofocus><button class="btn btn--primary"><?= icon('search') ?> <?php _e('Search'); ?></button></form>

  <?php if (mb_strlen($qs) >= 2): ?>
    <div class="filters" style="margin-top:30px;justify-content:center">
      <a class="pill <?= $only === '' ? 'on' : '' ?>" href="<?= e(url('library', ['q' => $qs])) ?>"><?php _e('All'); ?> (<?= array_sum($facets) ?>)</a>
      <?php foreach ($facets as $t => $n): ?><a class="pill <?= $only === $t ? 'on' : '' ?>" href="<?= e(url('library', ['q' => $qs, 'type' => $t])) ?>"><?= e(__(SEARCH_TYPE_LABELS[$t])) ?> (<?= $n ?>)</a><?php endforeach; ?>
    </div>
    <?php if ($show): ?>
      <div class="grid" style="max-width:900px;margin:0 auto;gap:12px">
        <?php foreach ($show as $r): ?>
          <a class="evrow" href="<?= e($r['url']) ?>"><span class="tag tag--dark"><?= e(__(SEARCH_TYPE_LABELS[$r['type']])) ?></span><div><h4><?= e($r['title']) ?></h4><p><?= e(excerpt((string)$r['text'], 150)) ?></p></div></a>
        <?php endforeach; ?>
      </div><?= pager($pg) ?>
    <?php else: echo '<div style="max-width:700px;margin:30px auto 0">' . empty_state(__('Nothing matched your search'), __('Try a different spelling, or contribute what you know.'), 'search', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute')) . '">' . e(__('Contribute')) . '</a></p>') . '</div>'; endif; ?>
  <?php else: ?>
    <div class="grid g4" style="margin-top:56px">
      <?php foreach ($cat as [$u, $ic, $t, $n, $d]): ?><a class="tile reveal" href="<?= e(url($u)) ?>"><span class="cnt"><?= e((string)$n) ?></span><span class="ico"><?= icon($ic) ?></span><h3><?= e(__($t)) ?></h3><p><?= e(__($d)) ?></p></a><?php endforeach; ?>
    </div>
    <div class="split" style="margin-top:60px">
      <div class="box"><h4><?= icon('shield') ?> <?php _e('Verified, versioned, backed up'); ?></h4><p><?php _e('Every entry carries standard metadata, passes expert verification before publication, keeps a full edit history, and is backed up to protect irreplaceable heritage.'); ?></p></div>
      <div class="box box--dark"><h4><?= icon('code') ?> <?php _e('Open data for researchers'); ?></h4><p><?php _e('Approved datasets are available as CSV and JSON through a documented, attribution-based API.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('developers')) ?>"><?php _e('API & data licence'); ?></a></div>
    </div>
  <?php endif; ?>
</div></section>
<?php page_end(); ?>
