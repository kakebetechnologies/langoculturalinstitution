<?php
declare(strict_types=1);

if (!empty($seg[1])) {
    $s = one("SELECT * FROM species WHERE slug=? AND status='published'", [$seg[1]]);
    if (!$s) not_found();
    page_start($s['name'], ['image' => $s['image'], 'hero' => ['eyebrow' => __('Biodiversity'), 'title' => $s['name'] . ($s['name_lg'] ? ' · ' . $s['name_lg'] : ''), 'sub' => $s['scientific_name'], 'crumbs' => [[__('Biodiversity'), 'species'], [$s['name'], null]], 'image' => $s['image'] ?: 'assets/img/photos/hero-ridge.jpg']]);
    ?>
    <section class="section"><div class="container article">
      <div class="prose"><?= rich($s['body']) ?>
        <?php if ($s['uses']): ?><h2><?php _e('Traditional uses'); ?></h2><?= rich($s['uses']) ?><?php endif; ?>
        <?php if ($s['habitat']): ?><h2><?php _e('Habitat'); ?></h2><?= rich($s['habitat']) ?><?php endif; ?>
      </div>
      <aside class="aside">
        <div class="box"><h4><?php _e('Profile'); ?></h4><dl class="kv">
          <dt><?php _e('Type'); ?></dt><dd><?= e(ucfirst($s['kind'])) ?></dd>
          <dt><?php _e('Status'); ?></dt><dd><span class="tag <?= in_array($s['conservation'], ['endangered', 'critical', 'locally_extinct'], true) ? 'tag--red' : 'tag--green' ?>"><?= e(__(CONSERVATION[$s['conservation']])) ?></span></dd>
          <?php if ($s['medicinal']): ?><dt><?php _e('Medicinal'); ?></dt><dd>✓ <?php _e('Used in traditional medicine'); ?></dd><?php endif; ?>
        </dl></div>
        <div class="box box--dark"><h4><?php _e('Seen this species?'); ?></h4><p><?php _e('Report a sighting with a photo and GPS location. Experts will review it.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('contribute?type=natural')) ?>"><?php _e('Report a sighting'); ?></a></div>
      </aside>
    </div></section>
    <?php page_end(); return;
}

$kind = get('kind'); $cons = get('status'); $qs = get('q');
$where = ["status='published'"]; $par = [];
if (isset(['plant' => 1, 'animal' => 1, 'bird' => 1, 'insect' => 1][$kind])) { $where[] = 'kind=?'; $par[] = $kind; }
if (isset(CONSERVATION[$cons])) { $where[] = 'conservation=?'; $par[] = $cons; }
if ($qs !== '') { $where[] = '(name LIKE ? OR name_lg LIKE ? OR scientific_name LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
$rows = all('SELECT * FROM species WHERE ' . implode(' AND ', $where) . ' ORDER BY name', $par);

$sightings = all("SELECT title, lat, lng, code FROM contributions WHERE type='natural' AND status='published' AND lat IS NOT NULL AND lng IS NOT NULL LIMIT 200");
$pins = array_map(fn($s) => ['lat' => (float)$s['lat'], 'lng' => (float)$s['lng'], 'title' => $s['title'], 'sub' => __('Community sighting'), 'color' => '#2f5b3a'], $sightings);

page_start(__('Biodiversity'), ['leaflet' => (bool)$pins, 'hero' => ['eyebrow' => __('Biodiversity & conservation'), 'title' => __('Plants and animals of Lango'), 'sub' => __('A living register of species, traditional medicinal plants and conservation hotspots — built with community sightings reviewed by experts.'), 'crumbs' => [[__('Heritage'), 'heritage'], [__('Biodiversity'), null]], 'image' => 'assets/img/photos/hero-ridge.jpg']]);
?>
<section class="section"><div class="container">
  <div class="filters">
    <a class="pill <?= !$kind ? 'on' : '' ?>" href="<?= e(url('species')) ?>"><?php _e('All'); ?></a>
    <?php foreach (['plant' => 'Plants', 'animal' => 'Animals', 'bird' => 'Birds', 'insect' => 'Insects'] as $k => $l): ?><a class="pill <?= $kind === $k ? 'on' : '' ?>" href="<?= e(url('species', ['kind' => $k])) ?>"><?= e(__($l)) ?></a><?php endforeach; ?>
    <span style="flex:1"></span><a class="btn btn--primary btn--sm" href="<?= e(url('contribute?type=natural')) ?>"><?= icon('camera') ?> <?php _e('Report a sighting'); ?></a>
  </div>
  <?php if ($rows): ?><div class="grid g3"><?php foreach ($rows as $s): echo card(url('species/' . $s['slug']), $s['image'], $s['name'] . ($s['name_lg'] ? ' · ' . $s['name_lg'] : ''), excerpt($s['body'], 130), __(CONSERVATION[$s['conservation']]), '<span><em>' . e((string)$s['scientific_name']) . '</em></span>'); endforeach; ?></div>
  <?php else: echo empty_state(__('No species match'), __('Try another filter, or help us document the wildlife and plants of your area.'), 'leaf'); endif; ?>
  <?php if ($pins): ?><h2 style="margin:56px 0 18px"><?php _e('Community sightings map'); ?></h2><div id="map" class="mapbox"></div><?php endif; ?>
</div></section>
<?php if ($pins): ?><script>window.LCI_PINS=<?= json_encode($pins, JSON_UNESCAPED_UNICODE) ?>;window.LCI_CENTER=[<?= e(setting('map_lat', '2.2499')) ?>,<?= e(setting('map_lng', '32.8998')) ?>];</script><?php endif; ?>
<?php page_end($pins ? ['scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js']] : []); ?>
