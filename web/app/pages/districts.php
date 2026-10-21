<?php
declare(strict_types=1);

$all = all("SELECT d.*, (SELECT COUNT(*) FROM clans c WHERE c.district_id=d.id AND c.status='published') clans
            FROM districts d WHERE d.status='published' ORDER BY d.sort, d.name");
$fallbackImgs = ['hero-aerial', 'hero-field', 'hero-road', 'hero-hill', 'hero-site', 'hero-ridge'];
$img = fn(array $d, int $i) => media_url($d['image'], 'assets/img/photos/' . $fallbackImgs[$i % 6] . '.jpg');
$knownList = fn(array $d) => array_values(array_filter(array_map('trim', explode("\n", (string)$d['known_for']))));
$density = fn(array $d) => ($d['population'] && $d['area_km2'] > 0) ? (int)round($d['population'] / $d['area_km2']) : null;

/* ============================== /districts/{slug} ============================== */
if (!empty($seg[1])) {
    $idx = null;
    foreach ($all as $i => $row) if ($row['slug'] === $seg[1]) { $idx = $i; break; }
    if ($idx === null) not_found();
    $d = $all[$idx];
    $prev = $all[($idx - 1 + count($all)) % count($all)]; $next = $all[($idx + 1) % count($all)];
    $known = $knownList($d);
    $isCity = $d['kind'] === 'city';
    $items = all("SELECT title, title_lg, slug, category, summary, image FROM heritage_items WHERE district_id=? AND status='published' ORDER BY title", [$d['id']]);
    $people = all("SELECT * FROM notables WHERE district_id=? AND status='published' ORDER BY sort", [$d['id']]);
    $clans = all("SELECT name, slug FROM clans WHERE district_id=? AND status='published' ORDER BY name", [$d['id']]);
    $byName = []; foreach ($all as $row) $byName[mb_strtolower(preg_replace('/\s+(city|district)$/i', '', $row['name']))] = $row;
    $nb = array_values(array_filter(array_map('trim', explode(',', (string)$d['neighbours']))));
    $dens = $density($d);
    $sections = array_filter([
        'overview' => [__('Overview'), $d['description']], 'known' => [__('Best known for'), $known ? 'x' : ''], 'people' => [__('People & demography'), $d['demography']],
        'economy' => [__('Economy & livelihoods'), $d['economy']], 'places' => [__('Places to visit'), ($d['attractions'] || $items) ? 'x' : ''], 'history' => [__('History'), $d['history']],
    ], fn($s) => trim((string)$s[1]) !== '');
    $title = $d['name'] . ($isCity ? '' : ' ' . __('District'));
    page_start($title, ['image' => $d['image'], 'desc' => excerpt($d['description'], 160), 'leaflet' => (bool)$d['lat'], 'hero' => ['eyebrow' => $isCity ? __('City of Lango') : __('District of Lango'), 'title' => $title, 'sub' => excerpt($d['description'], 170), 'crumbs' => [[__('About'), 'about'], [__('Districts'), 'districts'], [$d['name'], null]], 'image' => $d['image'] ?: 'assets/img/photos/' . $fallbackImgs[$idx % 6] . '.jpg']]);
    ?>
    <section class="section section--tight"><div class="container">
      <dl class="facts">
        <?php if ($d['population']): ?><div><dt><?= number_format((int)$d['population']) ?></dt><dd><?= sprintf(__('People (%s census)'), (int)$d['pop_year']) ?></dd></div><?php endif; ?>
        <?php if ($d['area_km2']): ?><div><dt><?= number_format((float)$d['area_km2']) ?> km²</dt><dd><?php _e('Area'); ?></dd></div><?php endif; ?>
        <?php if ($dens): ?><div><dt><?= number_format($dens) ?></dt><dd><?php _e('People per km²'); ?></dd></div><?php endif; ?>
        <?php if ($d['headquarters']): ?><div><dt><?= e($d['headquarters']) ?></dt><dd><?= $isCity ? e(__('City centre')) : e(__('Headquarters')) ?></dd></div><?php endif; ?>
        <?php if ($d['created_year']): ?><div><dt><?= (int)$d['created_year'] ?></dt><dd><?= $isCity ? e(__('Became a city')) : e(__('Created')) ?></dd></div><?php endif; ?>
      </dl>
    </div></section>
    <section class="section section--tight" style="padding-top:8px"><div class="container article">
      <div class="dist">
        <nav class="subnav" aria-label="<?= e(__('On this page')) ?>"><?php foreach ($sections as $k => [$l]): ?><a href="#<?= e($k) ?>"><?= e($l) ?></a><?php endforeach; ?></nav>

        <?php if (isset($sections['overview'])): ?><div class="dist__sec prose" id="overview"><h2><?php _e('Overview'); ?></h2><?= rich($d['description']) ?></div><?php endif; ?>

        <?php if ($known): ?><div class="dist__sec" id="known"><h2><?php _e('Best known for'); ?></h2>
          <ul class="known"><?php foreach ($known as $i => $k): ?><li><span><?= $i + 1 ?></span><?= e($k) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <?php if (isset($sections['people'])): ?><div class="dist__sec prose" id="people"><h2><?php _e('People & demography'); ?></h2><?= rich($d['demography']) ?></div><?php endif; ?>
        <?php if (isset($sections['economy'])): ?><div class="dist__sec prose" id="economy"><h2><?php _e('Economy & livelihoods'); ?></h2><?= rich($d['economy']) ?></div><?php endif; ?>

        <?php if (isset($sections['places'])): ?><div class="dist__sec prose" id="places"><h2><?php _e('Places to visit'); ?></h2><?= rich($d['attractions']) ?>
          <?php if ($items): ?><div class="grid g2" style="margin-top:18px;gap:14px"><?php foreach ($items as $h): ?>
            <a class="evrow" href="<?= e(url('heritage/item/' . $h['slug'])) ?>"><span class="tag"><?= e(HERITAGE_CATS[$h['category']][0] ?? '') ?></span><div><h4><?= e(t($h, 'title')) ?></h4><p><?= e(excerpt((string)$h['summary'], 90)) ?></p></div></a>
          <?php endforeach; ?></div><?php endif; ?></div><?php endif; ?>

        <?php if (isset($sections['history'])): ?><div class="dist__sec prose" id="history"><h2><?php _e('History'); ?></h2><?= rich($d['history']) ?></div><?php endif; ?>

        <p class="dist__src"><?php _e('Population figures are from the 2024 National Population and Housing Census (Uganda Bureau of Statistics). Descriptions are compiled from published sources and are reviewed by LCI; corrections are welcome.'); ?> <a href="<?= e(url('contact')) ?>"><?php _e('Send a correction'); ?></a></p>

        <div class="pn"><a href="<?= e(url('districts/' . $prev['slug'])) ?>"><small>← <?php _e('Previous'); ?></small><b><?= e($prev['name']) ?></b></a><a class="pn__r" href="<?= e(url('districts/' . $next['slug'])) ?>"><small><?php _e('Next'); ?> →</small><b><?= e($next['name']) ?></b></a></div>
      </div>

      <aside class="aside">
        <?php if ($d['lat']): ?><div class="box" style="padding:10px"><div id="map" class="mapbox" style="height:230px" aria-label="<?= e(__('Map')) ?>"></div></div><?php endif; ?>
        <div class="box"><h4><?php _e('Quick facts'); ?></h4>
          <dl class="kv" style="grid-template-columns:96px 1fr">
            <dt><?php _e('Type'); ?></dt><dd><?= $isCity ? e(__('City')) : e(__('District')) ?></dd>
            <?php if ($d['parent']): ?><dt><?php _e('Carved from'); ?></dt><dd><?= e($d['parent']) ?></dd><?php endif; ?>
            <?php if ($nb): ?><dt><?php _e('Neighbours'); ?></dt><dd><?php foreach ($nb as $n => $nm): $key = mb_strtolower(preg_replace('/\s+(city|district)$/i', '', $nm)); $lk = $byName[$key] ?? null; ?><?= $n ? ', ' : '' ?><?= ($lk && $lk['slug'] !== $d['slug']) ? '<a href="' . e(url('districts/' . $lk['slug'])) . '">' . e($nm) . '</a>' : e($nm) ?><?php endforeach; ?></dd><?php endif; ?>
            <?php if ($d['subdivisions']): ?><dt><?php _e('Divisions'); ?></dt><dd><?= e($d['subdivisions']) ?></dd><?php endif; ?>
            <?php if ($d['distance']): ?><dt><?php _e('Distances'); ?></dt><dd><?= e($d['distance']) ?></dd><?php endif; ?>
          </dl></div>
        <?php if ($people): ?><div class="box"><h4><?php _e('Notable people'); ?></h4><?php foreach ($people as $pp): ?><p style="margin:6px 0"><a href="<?= e(url('people/' . $pp['slug'])) ?>"><b><?= e($pp['short_name'] ?: $pp['name']) ?></b></a><br><small class="dim"><?= e((string)$pp['title']) ?></small></p><?php endforeach; ?></div><?php endif; ?>
        <?php if ($clans): ?><div class="box"><h4><?php _e('Clans in this area'); ?></h4><div class="chipcloud"><?php foreach ($clans as $c): ?><a href="<?= e(url('clans/' . $c['slug'])) ?>"><?= e($c['name']) ?></a><?php endforeach; ?></div></div><?php endif; ?>
        <div class="box"><h4><?php _e('All districts'); ?></h4><div class="chipcloud"><?php foreach ($all as $o): ?><a class="<?= $o['slug'] === $d['slug'] ? 'on' : '' ?>" href="<?= e(url('districts/' . $o['slug'])) ?>"><?= e($o['name']) ?></a><?php endforeach; ?></div></div>
      </aside>
    </div></section>
    <?php if ($d['lat']): ?><script>window.LCI_PINS=<?= json_encode([['lat' => (float)$d['lat'], 'lng' => (float)$d['lng'], 'title' => $title, 'sub' => $d['headquarters'] ?: '', 'color' => '#ee1a26']], JSON_UNESCAPED_UNICODE) ?>;window.LCI_CENTER=[<?= (float)$d['lat'] ?>,<?= (float)$d['lng'] ?>];window.LCI_ZOOM=10;</script><?php endif;
    page_end(['scripts' => $d['lat'] ? ['https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js'] : []]); return;
}

/* ================================= /districts ================================= */
$pins = [];
foreach ($all as $d) if ($d['lat']) $pins[] = ['lat' => (float)$d['lat'], 'lng' => (float)$d['lng'], 'title' => $d['name'] . ($d['kind'] === 'city' ? '' : ' District'), 'sub' => $d['population'] ? number_format((int)$d['population']) . ' people' : '', 'url' => url('districts/' . $d['slug']), 'color' => $d['kind'] === 'city' ? '#ee1a26' : '#0786b8'];
$totalPop = array_sum(array_column($all, 'population'));
$totalArea = array_sum(array_column($all, 'area_km2'));
$by = $all; usort($by, fn($a, $b) => ($b['population'] ?? 0) <=> ($a['population'] ?? 0));
$biggest = $by[0] ?? null;

page_start(__('Districts & Lira City'), ['leaflet' => true, 'hero' => ['eyebrow' => __('Tourism & geography'), 'title' => __('Districts and Lira City'), 'sub' => __('Nine districts and one city make up the Lango sub-region. Open any of them for its people, its economy, what it is best known for and where to go.'), 'crumbs' => [[__('About'), 'about'], [__('Districts'), null]], 'image' => 'assets/img/photos/hero-aerial.jpg']]);
?>
<section class="section section--tight"><div class="container">
  <dl class="facts">
    <div><dt><?= number_format($totalPop) ?></dt><dd><?php _e('People in the sub-region (2024 census)'); ?></dd></div>
    <div><dt><?= count($all) ?></dt><dd><?php _e('Districts and cities'); ?></dd></div>
    <div><dt><?= number_format($totalArea) ?> km²</dt><dd><?php _e('Combined area'); ?></dd></div>
    <?php if ($biggest): ?><div><dt><?= e($biggest['name']) ?></dt><dd><?php _e('Most populous'); ?></dd></div><?php endif; ?>
  </dl>
</div></section>

<section class="section section--tight" style="padding-top:8px"><div class="container">
  <div class="sec-row"><div><span class="eyebrow"><?php _e('Explore'); ?></span><h2><?php _e('Choose a district'); ?></h2></div></div>
  <div class="atlas">
    <?php foreach ($all as $i => $d): $known = array_slice($knownList($d), 0, 3); ?>
      <a class="arow reveal" href="<?= e(url('districts/' . $d['slug'])) ?>">
        <span class="arow__img"><img loading="lazy" src="<?= e($img($d, $i)) ?>" alt=""></span>
        <span class="arow__main"><span class="arow__name"><b><?= e($d['name']) ?></b><?= $d['kind'] === 'city' ? '<span class="tag tag--red">' . e(__('City')) . '</span>' : '' ?></span>
          <span class="arow__sub"><?= e($d['headquarters'] ? sprintf(__('Headquarters: %s'), $d['headquarters']) : '') ?></span>
          <?php if ($known): ?><span class="arow__known"><?php foreach ($known as $k): ?><i><?= e(mb_strimwidth($k, 0, 54, '…')) ?></i><?php endforeach; ?></span><?php endif; ?></span>
        <span class="arow__facts"><span><b><?= $d['population'] ? number_format((int)$d['population']) : '—' ?></b><small><?php _e('people'); ?></small></span><span><b><?= $d['area_km2'] ? number_format((float)$d['area_km2']) : '—' ?></b><small>km²</small></span><span><b><?= $d['created_year'] ?: '—' ?></b><small><?= $d['kind'] === 'city' ? e(__('city since')) : e(__('created')) ?></small></span></span>
        <span class="arow__go"><?= icon('arrow') ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="section section--cream"><div class="container">
  <div class="sec-head"><span class="eyebrow"><?php _e('On the map'); ?></span><h2><?php _e('Where they are'); ?></h2></div>
  <div id="map" class="mapbox" aria-label="<?= e(__('Map of the Lango sub-region')) ?>"></div>
  <p class="dim" style="font-size:.85rem;margin-top:8px"><?php _e('Pins show approximate district and city centres. Click a pin to open the page.'); ?></p>
</div></section>

<section class="section"><div class="container">
  <div class="sec-head"><span class="eyebrow"><?php _e('Side by side'); ?></span><h2><?php _e('Compare the districts'); ?></h2></div>
  <div class="table-wrap"><table class="table table--cmp">
    <thead><tr><th><?php _e('District'); ?></th><th><?php _e('Headquarters'); ?></th><th class="num"><?php _e('Population'); ?></th><th class="num"><?php _e('Area (km²)'); ?></th><th class="num"><?php _e('People per km²'); ?></th><th class="num"><?php _e('Created'); ?></th><th><?php _e('Carved from'); ?></th></tr></thead>
    <tbody>
      <?php foreach ($all as $d): ?><tr><td><a href="<?= e(url('districts/' . $d['slug'])) ?>"><b><?= e($d['name']) ?></b></a></td><td><?= e((string)$d['headquarters']) ?></td>
        <td class="num"><?= $d['population'] ? number_format((int)$d['population']) : '—' ?></td><td class="num"><?= $d['area_km2'] ? number_format((float)$d['area_km2']) : '—' ?></td><td class="num"><?= ($x = $density($d)) ? number_format($x) : '—' ?></td>
        <td class="num"><?= $d['created_year'] ?: '—' ?></td><td><?= e((string)$d['parent']) ?></td></tr><?php endforeach; ?>
      <tr class="total"><td colspan="2"><b><?php _e('Lango sub-region'); ?></b></td><td class="num"><b><?= number_format($totalPop) ?></b></td><td class="num"><b><?= number_format($totalArea) ?></b></td><td class="num"><b><?= $totalArea > 0 ? number_format((int)round($totalPop / $totalArea)) : '—' ?></b></td><td colspan="2"></td></tr>
    </tbody></table></div>
  <p class="dim" style="font-size:.85rem;margin-top:10px"><?php _e('Source: Uganda Bureau of Statistics, National Population and Housing Census 2024. Areas and creation dates are compiled from published sources; LCI will review them.'); ?></p>
</div></section>
<script>window.LCI_PINS=<?= json_encode($pins, JSON_UNESCAPED_UNICODE) ?>;window.LCI_CENTER=[<?= e(setting('map_lat', '2.2499')) ?>,<?= e(setting('map_lng', '32.8998')) ?>];</script>
<?php page_end(['scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js']]); ?>
