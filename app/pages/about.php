<?php
declare(strict_types=1);
$counts = site_counts();
page_start(__('About LCI'), ['hero' => ['eyebrow' => __('About us'), 'title' => __('Preserving Lango culture, uniting our people'), 'sub' => __('The traditional authority of the Lango people of mid-northern Uganda.'), 'crumbs' => [[__('About'), null]], 'image' => site_img('hero_image_1', 'assets/img/photos/hero-site.jpg')]]);
?>
<section class="section">
  <div class="container split">
    <div class="collage reveal">
      <span class="collage__o"></span><span class="collage__t"></span>
      <div class="collage__a"><img loading="lazy" src="<?= e(site_img('about_image_1', 'assets/img/photos/hero-aerial.jpg')) ?>" alt=""></div>
      <div class="collage__b"><img loading="lazy" src="<?= e(site_img('about_image_2', 'assets/img/photos/hero-field.jpg')) ?>" alt=""></div>
      <div class="collage__badge"><b><?= (int)$counts['clans'] ?></b><small><?php _e('Clans'); ?></small></div>
    </div>
    <div class="reveal">
      <div class="eyebrow"><?php _e('Who we are'); ?></div>
      <h2><?php _e('Won Nyaci me Lango'); ?></h2>
      <p><?php _e('The Lango Cultural Institution (LCI) is the traditional authority representing the Lango people of mid-northern Uganda. Under the leadership of Won Nyaci Eng. Dr. Moses Michael Odongo Okune, we serve millions of people across Lira, Apac, Alebtong, Dokolo, Amolatar, Oyam, Kole, Otuke and Kwania.'); ?></p>
      <p><?php _e('With 149 registered clans, our mandate is to preserve Lango cultural heritage, promote unity among our people, and support sustainable community development through education, cultural programs, and strategic partnerships with government and development organisations.'); ?></p>
      <p style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px"><a class="btn btn--primary" href="<?= e(url('leadership')) ?>"><?php _e('Meet our leadership'); ?></a><a class="btn btn--outline" href="<?= e(url('history')) ?>"><?php _e('Read our history'); ?></a></p>
    </div>
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('Our purpose'); ?></div><h2><?php _e('Mission & vision'); ?></h2></div>
    <div class="grid g2">
      <div class="box reveal" style="padding:38px"><div class="mv" style="grid-template-columns:1fr;margin:0"><div><span class="ic"><?= icon('flag') ?></span><span><b style="font-size:1.35rem"><?php _e('Our Mission'); ?></b><p style="font-size:1rem"><?= e(setting('mission')) ?></p></span></div></div></div>
      <div class="box reveal" style="padding:38px"><div class="mv" style="grid-template-columns:1fr;margin:0"><div><span class="ic o"><?= icon('eye') ?></span><span><b style="font-size:1.35rem"><?php _e('Our Vision'); ?></b><p style="font-size:1rem"><?= e(setting('vision')) ?></p></span></div></div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('Our mandate'); ?></div><h2><?php _e('Three pillars of our work'); ?></h2></div>
    <div class="grid g3">
      <div class="tile reveal"><span class="ico"><?= icon('star') ?></span><h3><?php _e('Cultural preservation'); ?></h3><p><?php _e('Safeguarding traditional dances like Okeme and Abuda, protecting sacred sites and documenting our rich cultural heritage.'); ?></p></div>
      <div class="tile reveal"><span class="ico"><?= icon('book') ?></span><h3><?php _e('Education programs'); ?></h3><p><?php _e('Supporting our youth through scholarships and educational partnerships for community advancement.'); ?></p></div>
      <div class="tile reveal"><span class="ico"><?= icon('users') ?></span><h3><?php _e('Community unity'); ?></h3><p><?php _e('Bringing together 149 clans across nine districts to promote peace, development and Lango cultural identity.'); ?></p></div>
    </div>
  </div>
</section>

<section class="section section--dark">
  <div class="container">
    <div class="stats">
      <div class="stat"><b data-count="<?= $counts['clans'] ?>">0</b><span><?php _e('Registered clans'); ?></span></div>
      <div class="stat"><b data-count="<?= $counts['districts'] ?>">0</b><span><?php _e('Districts'); ?></span></div>
      <div class="stat"><b data-count="<?= $counts['words'] ?>">0</b><span><?php _e('Words documented'); ?></span></div>
      <div class="stat"><b data-count="<?= $counts['contrib'] ?>">0</b><span><?php _e('Verified contributions'); ?></span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container"><div class="cta-band">
    <div><h3><?php _e('Join our mission'); ?></h3><p><?php _e('Help us preserve our traditions, support education and strengthen community bonds.'); ?></p></div>
    <div style="display:flex;gap:12px;flex-wrap:wrap"><a class="btn btn--gold" href="<?= e(url('donate')) ?>"><?php _e('Donate'); ?></a><a class="btn btn--ghost" href="<?= e(url('contribute')) ?>"><?php _e('Contribute'); ?></a></div>
  </div></div>
</section>
<?php page_end(); ?>
