<?php
declare(strict_types=1);
$timeline = all('SELECT * FROM timeline ORDER BY sort, year');
page_start(__('Our History'), ['hero' => ['eyebrow' => __('Origins, migration & cultural evolution'), 'title' => __('History of the Lango people'), 'crumbs' => [[__('About'), 'about'], [__('History'), null]], 'image' => 'assets/img/photos/hero-ridge.jpg']]);
?>
<section class="section">
  <div class="container article">
    <div class="prose reveal">
      <p style="font-size:1.25rem;font-family:var(--fd)"><?php _e('The Lango people are a Nilotic ethnic group from mid-northern Uganda. Oral traditions trace their migration from the Bahr el Ghazal region of present-day South Sudan between the 15th and 17th centuries.'); ?></p>
      <p><?php _e('Settling in Northern Uganda, the Lango organised themselves in a decentralised clan system. Each clan was led by a clan chief — the Awitong — with elders and spiritual leaders guiding decision-making, conflict resolution and cultural rituals.'); ?></p>
      <p><?php _e('Unlike centralised kingdoms, Lango society emphasised collective leadership, strengthening unity among clans while maintaining each clan’s identity. Agriculture, particularly subsistence farming and cotton production, became central to the economy during the colonial period.'); ?></p>
      <p><?php _e('Missionary education introduced schools that produced many of the post-independence leaders of Uganda. Today, the Lango Cultural Institution (LCI), led by Won Nyaci Eng. Dr. Moses Michael Odongo Okune, unites 149 clans across nine districts to preserve cultural heritage, foster unity, and support sustainable community development.'); ?></p>
      <blockquote><?php _e('Culture is our identity.'); ?></blockquote>
      <?php if ($timeline): ?>
        <h2><?php _e('Timeline'); ?></h2>
        <ul class="tl"><?php foreach ($timeline as $t): ?><li><b><?= e($t['year']) ?></b><h3 style="margin:2px 0 6px"><?= e($t['title']) ?></h3><p><?= e($t['body']) ?></p></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </div>
    <aside class="aside">
      <div class="box box--dark"><h4><?php _e('Help us record our history'); ?></h4><p><?php _e('Do you hold an old photograph, a letter, or the recorded memory of an elder? Add it to the archive so it is never lost.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('contribute?type=oral_history')) ?>"><?php _e('Share an oral history'); ?></a></div>
      <div class="box"><h4><?php _e('Explore next'); ?></h4><p><a href="<?= e(url('clans')) ?>"><?php _e('Clan Explorer'); ?></a><br><a href="<?= e(url('districts')) ?>"><?php _e('Our nine districts'); ?></a><br><a href="<?= e(url('library/documents')) ?>"><?php _e('Historical documents'); ?></a></p></div>
    </aside>
  </div>
</section>
<?php page_end(); ?>
