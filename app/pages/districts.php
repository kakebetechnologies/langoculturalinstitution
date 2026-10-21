<?php
declare(strict_types=1);

$districts = all("SELECT d.*, (SELECT COUNT(*) FROM clans c WHERE c.district_id=d.id AND c.status='published') clans,
                  (SELECT COUNT(*) FROM heritage_items h WHERE h.district_id=d.id AND h.status='published') items
                  FROM districts d WHERE d.status='published' ORDER BY d.sort, d.name");
$sites = all("SELECT title, title_lg, slug, lat, lng, category FROM heritage_items WHERE status='published' AND lat IS NOT NULL AND lng IS NOT NULL");
$pins = [];
foreach ($districts as $d) if ($d['lat']) $pins[] = ['lat' => (float)$d['lat'], 'lng' => (float)$d['lng'], 'title' => $d['name'] . ' District', 'sub' => $d['clans'] . ' clans', 'color' => '#a8431f'];
foreach ($sites as $s) $pins[] = ['lat' => (float)$s['lat'], 'lng' => (float)$s['lng'], 'title' => t($s, 'title'), 'sub' => HERITAGE_CATS[$s['category']][0], 'url' => url('heritage/item/' . $s['slug']), 'color' => '#2f5b3a'];

page_start(__('Our 9 Districts'), ['leaflet' => true, 'hero' => ['eyebrow' => __('Tourism & geography'), 'title' => __('Our nine districts'), 'sub' => __('Lira, Alebtong, Amolatar, Apac, Dokolo, Kole, Kwania, Oyam and Otuke — the homeland of the Lango.'), 'crumbs' => [[__('About'), 'about'], [__('Districts'), null]], 'image' => 'assets/img/photos/hero-aerial.jpg']]);
?>
<section class="section">
  <div class="container">
    <div id="map" class="mapbox" aria-label="<?= e(__('Map of the Lango sub-region')) ?>"></div>
    <p style="color:var(--muted);font-size:.85rem;margin-top:8px"><?php _e('Pins show approximate district centres (terracotta) and mapped heritage sites (green).'); ?></p>
  </div>
</section>
<section class="section section--sand" style="padding-top:60px">
  <div class="container">
    <div class="grid g3">
      <?php foreach ($districts as $i => $d): ?>
        <article class="card reveal"><a class="card__img" href="<?= e(url('clans', ['district' => $d['slug']])) ?>"><img loading="lazy" src="<?= e(media_url($d['image'], 'assets/img/photos/' . ['hero-aerial', 'hero-field', 'hero-road', 'hero-hill', 'hero-site', 'hero-ridge'][$i % 6] . '.jpg')) ?>" alt=""></a>
          <div class="card__body"><h3><?= e($d['name']) ?></h3>
            <p><?= e(t($d, 'description') ?: __('A district of the Lango sub-region.')) ?></p>
            <div class="meta"><span><?= icon('users') ?> <?= (int)$d['clans'] ?> <?php _e('clans listed'); ?></span><span><?= icon('star') ?> <?= (int)$d['items'] ?> <?php _e('heritage items'); ?></span></div>
            <a class="card__more" href="<?= e(url('clans', ['district' => $d['slug']])) ?>"><?php _e('Explore clans'); ?> <?= icon('arrow') ?></a></div></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script>window.LCI_PINS=<?= json_encode($pins, JSON_UNESCAPED_UNICODE) ?>;window.LCI_CENTER=[<?= e(setting('map_lat', '2.2499')) ?>,<?= e(setting('map_lng', '32.8998')) ?>];</script>
<?php page_end(['scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js']]); ?>
