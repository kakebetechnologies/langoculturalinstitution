<?php
declare(strict_types=1);
$counts = site_counts();
page_start(__('About Lango'), ['hero' => ['eyebrow' => __('About'), 'title' => __('Everything about Lango, in one place'), 'sub' => __('A free community website for the Lango people of mid-northern Uganda, and for everyone who wants to learn about them.'), 'crumbs' => [[__('About'), null]], 'image' => site_img('hero_image_1', 'assets/img/photos/hero-site.jpg')]]);
?>
<section class="section">
  <div class="container split">
    <div class="collage reveal">
      <div class="collage__a"><img loading="lazy" src="<?= e(site_img('about_image_1', 'assets/img/lango/lango-house.jpg')) ?>" alt=""></div>
      <div class="collage__b"><img loading="lazy" src="<?= e(site_img('about_image_2', 'assets/img/lango/abuda-dancers.jpg')) ?>" alt=""></div>
      <div class="collage__badge"><b><?= (int)$counts['districts'] ?></b><small><?php _e('Districts'); ?></small></div>
    </div>
    <div class="reveal">
      <div class="eyebrow"><?php _e('What this site is'); ?></div>
      <h2><?php _e('A home for Lango on the web'); ?></h2>
      <p><?php _e('Lango is a community website about the Lango people of mid-northern Uganda: their language, clans, districts, history and culture. It is free to use. It is an independent website that anyone can add to.'); ?></p>
      <p><?php _e('Look words up in the Lango dictionary, find your clan, read about the districts and the history of the Lango, discover heritage places, and add what you know. Everything people add is checked by our reviewers before it is published.'); ?></p>
      <p style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px"><a class="btn btn--primary" href="<?= e(url(user() ? 'account' : 'contribute')) ?>"><?= user() ? e(__('Open My culture')) : e(__('Share what you know')) ?></a><a class="btn btn--outline" href="<?= e(url('history')) ?>"><?php _e('Read the history'); ?></a></p>
    </div>
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('What you can do here'); ?></div><h2><?php _e('Learn it. Share it. Keep it alive.'); ?></h2></div>
    <div class="grid g3">
      <a class="tile reveal" href="<?= e(url('language/dictionary')) ?>"><span class="ico"><?= icon('book') ?></span><h3><?php _e('Learn the language'); ?></h3><p><?php _e('Search the dictionary in Leb Lango and English, and translate with AI.'); ?></p></a>
      <a class="tile reveal" href="<?= e(url('clans')) ?>"><span class="ico"><?= icon('users') ?></span><h3><?php _e('Find your clan'); ?></h3><p><?php _e('Read about each clan and register under yours.'); ?></p></a>
      <a class="tile reveal" href="<?= e(url('contribute')) ?>"><span class="ico"><?= icon('camera') ?></span><h3><?php _e('Share what you know'); ?></h3><p><?php _e('Add a word, a photo, a story or a place to the Lango archive.'); ?></p></a>
    </div>
  </div>
</section>

<section class="section section--dark">
  <div class="container">
    <div class="stats">
      <div class="stat"><b data-count="<?= $counts['clans'] ?>">0</b><span><?php _e('Clans listed'); ?></span></div>
      <div class="stat"><b data-count="<?= $counts['districts'] ?>">0</b><span><?php _e('Districts'); ?></span></div>
      <div class="stat"><b data-count="<?= $counts['words'] ?>">0</b><span><?php _e('Words documented'); ?></span></div>
      <div class="stat"><b data-count="<?= $counts['contrib'] ?>">0</b><span><?php _e('Verified contributions'); ?></span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container"><div class="cta-band">
    <div><h3><?php _e('Add what you know'); ?></h3><p><?php _e('A word, a photograph, a story or your clan: share it, and our reviewers will check it before it is published.'); ?></p></div>
    <div style="display:flex;gap:12px;flex-wrap:wrap"><a class="btn btn--gold" href="<?= e(url('contribute')) ?>"><?php _e('Contribute'); ?></a></div>
  </div></div>
</section>
<?php page_end(); ?>
