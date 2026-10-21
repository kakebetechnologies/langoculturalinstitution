<?php
declare(strict_types=1);
$base = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('api/v1');
$eps = [
    ['/stats', 'Headline numbers (clans, words, archived items…)'], ['/news?limit=10', 'Latest published news'], ['/events', 'Upcoming events'],
    ['/clans?q=', 'Published clans (search by name or totem)'], ['/dictionary?q=yoo', 'Lango–English dictionary search'], ['/word-of-the-day', 'Today’s word'],
    ['/proverbs', 'Published proverbs'], ['/heritage?category=dance', 'Heritage items'], ['/species', 'Species register'], ['/programs', 'Foundation programs'],
];
page_start(__('Open Data & API'), ['hero' => ['eyebrow' => __('For researchers & developers'), 'title' => __('Open data & API'), 'sub' => __('Approved Lango cultural datasets, free to use with attribution.'), 'crumbs' => [[__('Library'), 'library'], [__('Open Data'), null]], 'image' => 'assets/img/photos/hero-ridge.jpg']]);
?>
<section class="section"><div class="container article">
  <div class="prose">
    <h2><?php _e('Public REST API'); ?></h2>
    <p><?php _e('All endpoints return JSON, are read-only, and only expose content that has passed expert verification and is published. The same API will power the Lango mobile app.'); ?></p>
    <p><code><?= e($base) ?></code></p>
    <div class="table-wrap"><table class="table"><thead><tr><th><?php _e('Endpoint'); ?></th><th><?php _e('Returns'); ?></th></tr></thead><tbody>
      <?php foreach ($eps as [$p, $d]): ?><tr><td><a href="<?= e(url('api/v1' . $p)) ?>"><code>GET /api/v1<?= e($p) ?></code></a></td><td><?= e(__($d)) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <h2><?php _e('Data licence'); ?></h2>
    <p><?php _e('Unless an item states otherwise, published data is made available under Creative Commons Attribution (CC BY 4.0): you may share and adapt it, including commercially, provided you credit “Lango Cultural Institution”. Contributor-owned media remains the property of its contributor and may carry different terms. Sacred or restricted knowledge is never published.'); ?></p>
    <p><?php _e('For bulk exports (CSV/JSON), higher rate limits or research partnerships, please contact the Data Center team.'); ?></p>
  </div>
  <aside class="aside"><div class="box box--dark"><h4><?php _e('Need something else?'); ?></h4><p><?php _e('We welcome research collaborations and app integrations.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('contact')) ?>"><?php _e('Contact the Data Center'); ?></a></div></aside>
</div></section>
<?php page_end(); ?>
