<?php
declare(strict_types=1);

// The Lango Bible: links to the Bible Society of Uganda's own pages on Bible.com (the text is theirs and is not copied here).
require_once ROOT . '/app/lib/app_api.php';   // bible_links()

$links = bible_links();
$names = ['full' => [__('Full Bible'), __('Old and New Testament')], 'nt' => [__('New Testament'), __('The New Testament in Leb Lango')]];
page_start(__('Lango Bible'), ['hero' => ['eyebrow' => __('Library'), 'title' => __('Lango Bible'), 'sub' => __('Read and listen to the Bible in Leb Lango.'), 'crumbs' => [[__('Library'), 'library'], [__('Lango Bible'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section"><div class="container" style="max-width:780px">
  <?php if ($links): ?>
    <p style="font-size:1.08rem"><?= e(bible_link_note()) ?></p>
    <div class="biblecards">
      <?php foreach ($links as $l): [$title, $sub] = $names[$l['key']]; ?>
        <a class="biblecard" href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><span class="ico"><?= icon('book') ?></span><b><?= e($title) ?></b><small><?= e($sub) ?></small><span class="go"><?php _e('Open on Bible.com'); ?> <?= icon('external') ?></span></a>
      <?php endforeach; ?>
    </div>
    <p class="dim" style="font-size:.88rem;margin-top:22px"><?php _e('These links open the Bible Society’s own pages. This site does not copy the text, which belongs to its publisher.'); ?></p>
  <?php else: echo empty_state(__('The Lango Bible is being prepared'), __('The links will appear here as soon as they are added.'), 'book'); endif; ?>
</div></section>
<?php page_end(); ?>
