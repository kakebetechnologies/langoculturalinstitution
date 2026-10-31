<?php
declare(strict_types=1);

// The Lango Bible. The text belongs to the Bible Society of Uganda and is not copied here: this page lists the books and chapters, and each chapter opens in the
// reader on Bible.com straight at the text, so a visitor never lands on the page that offers to download the app.
require_once ROOT . '/app/lib/app_api.php';   // bible_links(), bible_reader_url(), BIBLE_CHAPTERS

$links = bible_links();
$byKey = [];
foreach ($links as $l) if ($l['version']) $byKey[$l['key']] = $l;
$books = all('SELECT code, testament, name_en, name_lg FROM bible_books ORDER BY id');
$bookBy = [];
foreach ($books as $b) $bookBy[$b['code']] = $b;
$bname = fn(array $b) => $b['name_lg'] ?: $b['name_en'];

/* ------------------------------ /bible/read/{edition}/{CODE}/{chapter}: old links go straight to the reader ------------------------------ */
if (($seg[1] ?? '') === 'read') {
    $ver = (int)($seg[2] ?? 0); $code = strtoupper((string)($seg[3] ?? '')); $ch = (int)($seg[4] ?? 1);
    $ok = in_array($ver, array_column($links, 'version'), true) && isset($bookBy[$code], BIBLE_CHAPTERS[$code]) && $ch >= 1 && $ch <= BIBLE_CHAPTERS[$code];
    if (!$ok) not_found();
    header('Location: ' . bible_reader_url($ver, $code, $ch), true, 302);
    exit;
}

/* ------------------------------------------------ /bible ------------------------------------------------ */
$names = ['full' => [__('Full Bible'), __('Old and New Testament')], 'nt' => [__('New Testament'), __('The New Testament in Leb Lango')]];
page_start(__('Lango Bible'), ['hero' => ['eyebrow' => __('Library'), 'title' => __('Lango Bible'), 'sub' => __('Read and listen to the Bible in Leb Lango.'), 'crumbs' => [[__('Library'), 'library'], [__('Lango Bible'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section"><div class="container" style="max-width:900px">
  <?php if ($byKey): $first = $byKey['full'] ?? reset($byKey); ?>
    <p style="font-size:1.08rem"><?php _e('Choose a book and a chapter. The chapter opens straight at the text, in Leb Lango.'); ?></p>
    <div id="books">
    <?php foreach ($byKey as $key => $l):
      $ver = $l['version'];
      $groups = $key === 'nt' ? ['new' => __('New Testament')] : ['old' => __('Old Testament'), 'new' => __('New Testament')];
      if ($key === 'nt' && isset($byKey['full'])) continue;   // the full Bible already has the New Testament
      foreach ($groups as $tm => $label): ?>
        <h2 style="font-size:1.3rem;margin:30px 0 12px"><?= e($label) ?><?= $key === 'nt' ? ' · La79' : '' ?></h2>
        <div class="bookgrid">
          <?php foreach ($books as $b): if ($b['testament'] !== $tm) continue; $tot = BIBLE_CHAPTERS[$b['code']] ?? 0; if (!$tot) continue; ?>
            <details class="bookitem"><summary><?= e($bname($b)) ?> <small><?= $tot ?></small></summary>
              <div class="chgrid"><?php for ($c = 1; $c <= $tot; $c++): ?><a href="<?= e(bible_reader_url($ver, $b['code'], $c)) ?>" target="_blank" rel="noopener"><?= $c ?></a><?php endfor; ?></div>
            </details>
          <?php endforeach; ?>
        </div>
      <?php endforeach; endforeach; ?>
    </div>
    <h2 style="font-size:1.15rem;margin:34px 0 10px"><?php _e('Also on Bible.com'); ?></h2>
    <div class="biblecards">
      <?php foreach ($links as $l): [$title, $sub] = $names[$l['key']]; ?>
        <a class="biblecard" href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><span class="ico"><?= icon('book') ?></span><b><?= e($title) ?></b><small><?= e($sub) ?></small><span class="go"><?php _e('Open on Bible.com'); ?> <?= icon('external') ?></span></a>
      <?php endforeach; ?>
    </div>
    <p class="dim" style="font-size:.88rem;margin-top:22px"><?php _e('The text is shown from the Bible Society of Uganda’s own pages. This site does not copy it, because it belongs to its publisher.'); ?></p>
  <?php elseif ($links): ?>
    <p style="font-size:1.08rem"><?= e(bible_link_note()) ?></p>
    <div class="biblecards"><?php foreach ($links as $l): [$title, $sub] = $names[$l['key']]; ?>
      <a class="biblecard" href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><span class="ico"><?= icon('book') ?></span><b><?= e($title) ?></b><small><?= e($sub) ?></small><span class="go"><?php _e('Open on Bible.com'); ?> <?= icon('external') ?></span></a>
    <?php endforeach; ?></div>
  <?php else: echo empty_state(__('The Lango Bible is being prepared'), __('The links will appear here as soon as they are added.'), 'book'); endif; ?>
</div></section>
<?php page_end(); ?>
