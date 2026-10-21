<?php
declare(strict_types=1);

$sections = all("SELECT * FROM history_sections WHERE status='published' ORDER BY sort, id");
$timeline = all('SELECT * FROM timeline ORDER BY sort, id');
$glossary = all("SELECT * FROM glossary WHERE status='published' ORDER BY sort, term");
$pop = (int)val("SELECT SUM(population) FROM districts WHERE status='published'");
$nDist = (int)val("SELECT COUNT(*) FROM districts WHERE status='published' AND kind='district'");

$sources = [
    ['J. H. Driberg', 'The Lango: A Nilotic Tribe of Uganda (1923)', 'The earliest detailed ethnography of the Lango, written by a colonial officer.'],
    ['Owiny', 'The Lango of Uganda: identity, origin, migration and settlements', 'A study of Lango origins and movements, published through Boston University.'],
    ['Ingham, K.', 'Writing on Lango District administration', 'On the colonial chiefs and the Lango District council.'],
    ['Wikipedia', 'Articles on the Lango people, Lango sub-region, Lira, Milton Obote, David Oyite-Ojok and John Akii-Bua', 'Used for dates and for the outline of events; each fact should be checked against the books above.'],
    ['Daily Monitor', 'Features on Lango history and personalities', 'Used for Owiny Akullu and for stories of the district creations.'],
    ['Uganda Bureau of Statistics', 'National Population and Housing Census 2024', 'The population figures quoted on the districts pages.'],
    ['Practical Lango Grammar', 'Worked examples and vocabulary', 'The source of the glossary spellings and of the dictionary and translator examples.'],
];
page_start(__('Our History'), ['hero' => ['eyebrow' => __('Origins, migration & cultural evolution'), 'title' => __('History of the Lango people'), 'sub' => __('From the oral traditions of migration to the districts and cities of today.'), 'crumbs' => [[__('About'), 'about'], [__('History'), null]], 'image' => 'assets/img/photos/hero-ridge.jpg']]);
?>
<section class="section section--tight"><div class="container">
  <div class="hlead">
    <div class="hlead__txt">
      <span class="eyebrow"><?php _e('The story in brief'); ?></span>
      <p class="hlead__p"><?php _e('The Lango are a Nilotic people of north-central Uganda. Oral tradition remembers a long migration from the north, a society built on clans rather than kings, a colonial era that created chiefs where there had been none, and a post-independence history in which Lango sons and daughters stood at the centre of the nation.'); ?></p>
      <p class="hlead__s"><?php _e('This history is compiled from the published sources listed at the end of the page. It will be reviewed and extended by LCI’s historians and the Council of Elders — and you can help.'); ?> <a href="<?= e(url('contribute', ['type' => 'oral_history'])) ?>"><?php _e('Share what you know.'); ?></a></p>
    </div>
    <dl class="hlead__facts">
      <div><dt><?= $pop ? number_format($pop) : '—' ?></dt><dd><?php _e('people in the sub-region (2024 census)'); ?></dd></div>
      <div><dt>149</dt><dd><?php _e('registered clans'); ?></dd></div>
      <div><dt><?= $nDist ?> + 1</dt><dd><?php _e('districts, and Lira City'); ?></dd></div>
      <div><dt>Leb Lango</dt><dd><?php _e('a Western Nilotic language'); ?></dd></div>
    </dl>
  </div>
</div></section>

<section class="section section--tight" style="padding-top:0"><div class="container hist">
  <nav class="hist__toc" id="toc" aria-label="<?= e(__('On this page')) ?>">
    <b><?php _e('On this page'); ?></b>
    <ol>
      <?php foreach ($sections as $i => $s): ?><li><a href="#<?= e($s['slug']) ?>" data-open="<?= e($s['slug']) ?>"><span><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($s['title']) ?></a></li><?php endforeach; ?>
      <?php if ($timeline): ?><li class="sep"><a href="#timeline"><span>—</span><?php _e('Timeline'); ?></a></li><?php endif; ?>
      <?php if ($glossary): ?><li><a href="#glossary"><span>—</span><?php _e('Lango words in this history'); ?></a></li><?php endif; ?>
      <li><a href="#sources"><span>—</span><?php _e('Sources'); ?></a></li>
    </ol>
  </nav>

  <div class="hist__main">
    <div class="hist__tools"><h2><?php _e('Read the story'); ?></h2><span><button type="button" class="linkbtn" data-expand="all"><?php _e('Expand all'); ?></button> · <button type="button" class="linkbtn" data-expand="none"><?php _e('Collapse all'); ?></button></span></div>

    <?php foreach ($sections as $i => $s): ?>
      <details class="chap" id="<?= e($s['slug']) ?>"<?= $i === 0 ? ' open' : '' ?>>
        <summary>
          <span class="chap__n"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="chap__t"><span class="chap__era"><?= icon($s['icon'] ?: 'history') ?> <?= e((string)$s['era']) ?></span><b><?= e($s['title']) ?></b><small><?= e((string)$s['summary']) ?></small></span>
          <?= icon('chevron', 'chap__chev') ?>
        </summary>
        <div class="chap__body prose"><?= rich($s['body']) ?></div>
      </details>
    <?php endforeach; ?>

    <?php if ($timeline): ?>
      <div class="hist__block" id="timeline">
        <div class="eyebrow"><?php _e('At a glance'); ?></div><h2><?php _e('Timeline'); ?></h2>
        <ol class="tline">
          <?php foreach ($timeline as $t): ?><li class="reveal"><span class="tline__y"><?= e($t['year']) ?></span><div><h3><?= e($t['title']) ?></h3><p><?= e((string)$t['body']) ?></p></div></li><?php endforeach; ?>
        </ol>
      </div>
    <?php endif; ?>

    <?php if ($glossary): ?>
      <div class="hist__block" id="glossary">
        <div class="eyebrow"><?php _e('Leb Lango'); ?></div><h2><?php _e('Lango words in this history'); ?></h2>
        <p style="color:var(--muted);max-width:640px"><?php _e('Words that come up again and again when Lango history is told, with their meanings from the dictionary.'); ?></p>
        <div class="gloss">
          <?php foreach ($glossary as $g): ?><a class="gloss__i" href="<?= e(url('language/dictionary', ['q' => $g['term']])) ?>"><b><?= e($g['term']) ?></b><span><?= e(str_replace(';', ' ·', $g['meaning'])) ?></span><?php if ($g['note']): ?><small><?= e($g['note']) ?></small><?php endif; ?></a><?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="hist__block" id="sources">
      <div class="eyebrow"><?php _e('Where this comes from'); ?></div><h2><?php _e('Sources & further reading'); ?></h2>
      <ul class="srcs"><?php foreach ($sources as [$who, $what, $why]): ?><li><b><?= e($who) ?></b> — <i><?= e($what) ?></i><small><?= e($why) ?></small></li><?php endforeach; ?></ul>
    </div>

    <div class="cta-band">
      <div><h3><?php _e('Do you hold a piece of Lango history?'); ?></h3><p><?php _e('An old photograph, a letter, a story told by your grandparents — add it to the archive so it is never lost.'); ?></p></div>
      <div style="display:flex;gap:12px;flex-wrap:wrap"><a class="btn btn--gold" href="<?= e(url('contribute', ['type' => 'oral_history'])) ?>"><?php _e('Share an oral history'); ?></a><a class="btn btn--ghost" href="<?= e(url('people')) ?>"><?php _e('Notable Langos'); ?></a></div>
    </div>
  </div>
</div></section>
<?php page_end(); ?>
