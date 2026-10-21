<?php
declare(strict_types=1);

const HERITAGE_GROUPS = [
    'ceremonies'   => ['Ceremonies & Traditions', ['ceremony'], 'Rituals, rites of passage and customs that mark Lango life.'],
    'attire'       => ['Attire & Crafts', ['attire', 'craft'], 'Traditional clothing, jewellery and the handmade crafts of Lango.'],
    'crafts'       => ['Crafts', ['craft'], 'Handmade crafts of Lango.'],
    'music'        => ['Music, Dance & Storytelling', ['music', 'dance', 'storytelling'], 'Songs, dances such as Okeme and Abuda, and the stories told by elders.'],
    'dance'        => ['Dance', ['dance'], 'Traditional Lango dances.'],
    'storytelling' => ['Storytelling', ['storytelling'], 'Oral storytelling traditions.'],
    'symbols'      => ['Cultural Symbols & Heritage Sites', ['symbol', 'site'], 'The symbols of Lango identity and the places that hold our history.'],
    'sites'        => ['Heritage Sites', ['site'], 'Landmarks and sacred places across the Lango sub-region.'],
];

/* /heritage/item/{slug} */
if (($seg[1] ?? '') === 'item' && !empty($seg[2])) {
    $h = one("SELECT h.*, c.name clan, c.slug clan_slug, d.name district FROM heritage_items h LEFT JOIN clans c ON c.id=h.clan_id LEFT JOIN districts d ON d.id=h.district_id WHERE h.slug=? AND h.status='published'", [$seg[2]]);
    if (!$h) not_found();
    $related = all("SELECT * FROM heritage_items WHERE status='published' AND category=? AND id<>? ORDER BY RAND() LIMIT 3", [$h['category'], $h['id']]);
    $embed = embed_url($h['video_url']);
    page_start(t($h, 'title'), ['image' => $h['image'], 'desc' => $h['summary'] ?: excerpt($h['body']), 'leaflet' => (bool)$h['lat'],
        'hero' => ['eyebrow' => HERITAGE_CATS[$h['category']][0], 'title' => t($h, 'title'), 'sub' => $h['summary'], 'crumbs' => [[__('Heritage'), 'heritage'], [HERITAGE_CATS[$h['category']][0], 'heritage/' . HERITAGE_CATS[$h['category']][1]], [t($h, 'title'), null]], 'image' => $h['image'] ?: 'assets/img/photos/hero-site.jpg']]);
    ?>
    <section class="section"><div class="container article">
      <div class="prose">
        <?= rich(t($h, 'body')) ?>
        <?php if ($embed): ?><div class="video"><iframe src="<?= e($embed) ?>" title="Video" allowfullscreen loading="lazy"></iframe></div><?php endif; ?>
        <?php if ($h['audio']): ?><audio controls style="width:100%" src="<?= e(media_url($h['audio'])) ?>"></audio><?php endif; ?>
        <?php if ($h['lat']): ?><div id="map" class="mapbox" style="height:340px"></div><?php endif; ?>
      </div>
      <aside class="aside">
        <div class="box"><h4><?php _e('Details'); ?></h4><dl class="kv">
          <dt><?php _e('Category'); ?></dt><dd><?= e(HERITAGE_CATS[$h['category']][0]) ?></dd>
          <?php if ($h['clan']): ?><dt><?php _e('Clan'); ?></dt><dd><a href="<?= e(url('clans/' . $h['clan_slug'])) ?>"><?= e($h['clan']) ?></a></dd><?php endif; ?>
          <?php if ($h['district']): ?><dt><?php _e('District'); ?></dt><dd><?= e($h['district']) ?></dd><?php endif; ?>
          <dt><?php _e('Status'); ?></dt><dd><?= $h['verified'] ? '<span class="tag tag--green">' . e(__('Verified by LCI')) . '</span>' : '<span class="tag tag--gold">' . e(__('Awaiting expert review')) . '</span>' ?></dd>
        </dl></div>
        <div class="box box--dark"><h4><?php _e('Add to this story'); ?></h4><p><?php _e('Know more about this tradition? Share photos, recordings or memories for our experts to review.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('contribute?type=cultural')) ?>"><?php _e('Contribute'); ?></a></div>
      </aside>
    </div></section>
    <?php if ($related): ?><section class="section section--sand"><div class="container"><div class="sec-head"><h2><?php _e('Related heritage'); ?></h2></div><div class="grid g3">
      <?php foreach ($related as $r) echo card(url('heritage/item/' . $r['slug']), $r['image'], t($r, 'title'), (string)$r['summary'], HERITAGE_CATS[$r['category']][0]); ?></div></div></section><?php endif; ?>
    <?php if ($h['lat']): ?><script>window.LCI_PINS=[{lat:<?= (float)$h['lat'] ?>,lng:<?= (float)$h['lng'] ?>,title:<?= json_encode(t($h, 'title')) ?>,color:'#2f5b3a'}];window.LCI_CENTER=[<?= (float)$h['lat'] ?>,<?= (float)$h['lng'] ?>];</script><?php endif;
    page_end($h['lat'] ? ['scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js']] : []);
    return;
}

/* /heritage/{group} */
$g = $seg[1] ?? '';
if ($g !== '' && isset(HERITAGE_GROUPS[$g])) {
    [$label, $cats, $blurb] = HERITAGE_GROUPS[$g];
    $in = implode(',', array_fill(0, count($cats), '?'));
    $total = (int)val("SELECT COUNT(*) FROM heritage_items WHERE status='published' AND category IN ($in)", $cats);
    $pg = paginate($total, 12);
    $rows = all("SELECT * FROM heritage_items WHERE status='published' AND category IN ($in) ORDER BY featured DESC, title LIMIT {$pg['per']} OFFSET {$pg['offset']}", $cats);
    $sites = in_array('site', $cats, true) ? all("SELECT * FROM heritage_items WHERE status='published' AND category='site' AND lat IS NOT NULL") : [];
    $pins = array_map(fn($s) => ['lat' => (float)$s['lat'], 'lng' => (float)$s['lng'], 'title' => t($s, 'title'), 'url' => url('heritage/item/' . $s['slug']), 'color' => '#2f5b3a'], $sites);
    page_start(__($label), ['leaflet' => (bool)$pins, 'hero' => ['eyebrow' => __('Heritage'), 'title' => __($label), 'sub' => __($blurb), 'crumbs' => [[__('Heritage'), 'heritage'], [__($label), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
    ?>
    <section class="section"><div class="container">
      <?php if ($pins): ?><div id="map" class="mapbox" style="margin-bottom:40px"></div><?php endif; ?>
      <?php if ($rows): ?><div class="grid g3"><?php foreach ($rows as $r) echo card(url('heritage/item/' . $r['slug']), $r['image'], t($r, 'title'), (string)$r['summary'], HERITAGE_CATS[$r['category']][0]); ?></div><?= pager($pg) ?>
      <?php else: echo empty_state(__('Nothing here yet'), __('This part of the archive is waiting for its first entries. Elders, cultural practitioners and community members can help fill it.'), 'star', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute?type=cultural')) . '">' . e(__('Contribute to this collection')) . '</a></p>'); endif; ?>
    </div></section>
    <?php if ($pins): ?><script>window.LCI_PINS=<?= json_encode($pins, JSON_UNESCAPED_UNICODE) ?>;window.LCI_CENTER=[<?= e(setting('map_lat', '2.2499')) ?>,<?= e(setting('map_lng', '32.8998')) ?>];</script><?php endif;
    page_end($pins ? ['scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js']] : []);
    return;
}
if ($g !== '') not_found();

/* /heritage hub */
$counts = [];
foreach (all("SELECT category, COUNT(*) n FROM heritage_items WHERE status='published' GROUP BY category") as $r) $counts[$r['category']] = (int)$r['n'];
$clanN = (int)val("SELECT COUNT(*) FROM clans WHERE status='published'");
$featured = all("SELECT * FROM heritage_items WHERE status='published' ORDER BY featured DESC, id DESC LIMIT 3");
page_start(__('Heritage'), ['hero' => ['eyebrow' => __('Cultural Heritage Module'), 'title' => __('The living heritage of Lango'), 'sub' => __('Clans, ceremonies, attire, music, dance, symbols and sacred places.'), 'crumbs' => [[__('Heritage'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
?>
<section class="section"><div class="container">
  <div class="grid g4">
    <a class="tile reveal" href="<?= e(url('clans')) ?>"><span class="cnt"><?= $clanN ?></span><span class="ico"><?= icon('users') ?></span><h3><?php _e('Clan Explorer'); ?></h3><p><?php _e('Histories, totems and leaders of every clan.'); ?></p></a>
    <?php foreach (['ceremonies' => 'star', 'attire' => 'image', 'music' => 'music', 'symbols' => 'pin'] as $k => $ic): [$label, $cats, $blurb] = HERITAGE_GROUPS[$k]; $n = array_sum(array_map(fn($c) => $counts[$c] ?? 0, $cats)); ?>
      <a class="tile reveal" href="<?= e(url('heritage/' . $k)) ?>"><span class="cnt"><?= $n ?></span><span class="ico"><?= icon($ic) ?></span><h3><?= e(__($label)) ?></h3><p><?= e(__($blurb)) ?></p></a>
    <?php endforeach; ?>
    <a class="tile reveal" href="<?= e(url('species')) ?>"><span class="ico"><?= icon('leaf') ?></span><h3><?php _e('Biodiversity'); ?></h3><p><?php _e('Plants and animals of Lango.'); ?></p></a>
    <a class="tile reveal" href="<?= e(url('language/dictionary')) ?>"><span class="ico"><?= icon('book') ?></span><h3><?php _e('Language'); ?></h3><p><?php _e('Dictionary, proverbs and lessons.'); ?></p></a>
    <a class="tile reveal" href="<?= e(url('tours')) ?>"><span class="ico"><?= icon('eye') ?></span><h3><?php _e('360° Tours'); ?></h3><p><?php _e('Visit Lango from anywhere.'); ?></p></a>
  </div>
</div></section>
<?php if ($featured): ?><section class="section section--sand"><div class="container"><div class="sec-head"><div class="eyebrow"><?php _e('From the archive'); ?></div><h2><?php _e('Featured heritage'); ?></h2></div><div class="grid g3">
  <?php foreach ($featured as $r) echo card(url('heritage/item/' . $r['slug']), $r['image'], t($r, 'title'), (string)$r['summary'], HERITAGE_CATS[$r['category']][0]); ?></div></div></section><?php endif; ?>
<?php page_end(); ?>
