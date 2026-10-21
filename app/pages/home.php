<?php
declare(strict_types=1);

$counts   = site_counts();
$wotd     = word_of_day();
$posts    = all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.status='published' AND p.published_at<=NOW() ORDER BY p.featured DESC, p.published_at DESC LIMIT 3");
$events   = all("SELECT * FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT 3");
$programs = all("SELECT * FROM programs WHERE status='published' ORDER BY featured DESC, sort, id LIMIT 4");
$notables = all("SELECT * FROM notables WHERE status='published' ORDER BY featured DESC, sort LIMIT 4");
$districtList = all("SELECT name, slug, kind FROM districts WHERE status='published' ORDER BY kind DESC, name");
$partners = all("SELECT * FROM partners WHERE status='published' ORDER BY sort, id");
$photos   = all("SELECT file, title FROM media WHERE type='photo' AND status='published' AND file IS NOT NULL ORDER BY id DESC LIMIT 6");
$defaults = ['hero-road', 'hero-site', 'hero-hill', 'hero-aerial', 'hero-field', 'hero-ridge'];
$strip    = [];
foreach ($defaults as $i => $d) $strip[] = isset($photos[$i]) ? [media_url($photos[$i]['file']), $photos[$i]['title']] : [asset('assets/img/photos/' . $d . '.jpg'), ''];
$next     = $events[0] ?? null;
$progImgs = ['hero-aerial', 'hero-field', 'hero-ridge', 'hero-hill', 'hero-site', 'hero-road'];

page_start('', ['class' => 'home']);
?>
<section class="hero">
  <div class="container">
    <div class="hero__panel">
      <div class="hero__txt">
        <span class="eyebrow"><?= e(setting('org_subtitle', 'Won Nyaci me Lango')) ?></span>
        <h1><?php _e('Our Culture. Our People.'); ?> <em><?php _e('Our Future.'); ?></em></h1>
        <p class="lead"><?php _e('The Lango Cultural Institution safeguards our language, clans and traditions, and brings Lango to the world through a living, community-built digital archive.'); ?></p>
        <div class="hero__cta">
          <a class="btn btn--primary" href="<?= e(url('contribute')) ?>"><?= icon('camera') ?> <?php _e('Become a Contributor'); ?></a>
          <a class="btn btn--outline" href="<?= e(url('about')) ?>"><?php _e('Discover LCI'); ?></a>
        </div>
        <ul class="hero__facts">
          <li><b><?= (int)$counts['clans'] ?></b><span><?php _e('Registered clans'); ?></span></li>
          <li><b><?= (int)$counts['districts'] ?></b><span><?php _e('Districts'); ?></span></li>
          <li><b><?= number_format($counts['words']) ?></b><span><?php _e('Words documented'); ?></span></li>
        </ul>
      </div>
      <div class="hm">
        <div class="hm__main"><img src="<?= e(site_img('hero_image_1', 'assets/img/photos/hero-site.jpg')) ?>" alt="<?= e(__('A Lango heritage site')) ?>"></div>
        <div class="hm__inset"><img src="<?= e(site_img('hero_image_2', 'assets/img/photos/hero-hill.jpg')) ?>" alt=""></div>
        <?php if ($next): $nts = strtotime($next['starts_at']); ?>
          <a class="hm__event" href="<?= e(url('events/' . $next['slug'])) ?>">
            <span class="d"><b><?= e(date('j', $nts)) ?></b><small><?= e(date('M', $nts)) ?></small></span>
            <span class="t"><?= e(t($next, 'title')) ?><small><?php _e('Upcoming event'); ?> · <?= e((string)$next['location']) ?></small></span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="qstrip"><div class="container"><div class="qstrip__in">
  <a class="qi" href="<?= e(url('contribute')) ?>"><span class="ico"><?= icon('camera') ?></span><span><b><?php _e('Submit a contribution'); ?></b><small><?php _e('Photos, stories, sightings'); ?></small></span></a>
  <a class="qi" href="<?= e(url('donate')) ?>"><span class="ico"><?= icon('heart') ?></span><span><b><?php _e('Donate'); ?></b><small><?php _e('Support Lango heritage'); ?></small></span></a>
  <a class="qi" href="<?= e(url('library')) ?>"><span class="ico"><?= icon('book') ?></span><span><b><?php _e('Explore the Library'); ?></b><small><?php _e('Search the Data Center'); ?></small></span></a>
  <a class="qi" href="<?= e(url('language/dictionary')) ?>"><span class="ico"><?= icon('globe') ?></span><span><b><?php _e('Dictionary & AI Translator'); ?></b><small><?php _e('Search and translate Leb Lango'); ?></small></span></a>
</div></div></div>

<section class="section">
  <div class="container split">
    <div class="collage reveal">
      <div class="collage__a"><img loading="lazy" src="<?= e(site_img('about_image_1', 'assets/img/photos/hero-aerial.jpg')) ?>" alt=""></div>
      <div class="collage__b"><img loading="lazy" src="<?= e(site_img('about_image_2', 'assets/img/photos/hero-field.jpg')) ?>" alt=""></div>
      <div class="collage__badge"><b><?= (int)$counts['districts'] ?></b><small><?php _e('Districts of Lango'); ?></small></div>
    </div>
    <div class="reveal">
      <span class="eyebrow"><?php _e('Welcome to LCI'); ?></span>
      <h2><?php _e('A people with one heart and a shared future'); ?></h2>
      <p><?php _e('The Lango Cultural Institution (LCI) is the traditional authority of the Lango people of mid-northern Uganda. Under the leadership of Won Nyaci Eng. Dr. Moses Michael Odongo Okune, we serve communities across Lira, Apac, Alebtong, Dokolo, Amolatar, Oyam, Kole, Otuke and Kwania.'); ?></p>
      <p><?php _e('We believe that culture is identity. Together with 149 registered clans, we preserve what makes us Lango and build a future our children can be proud of.'); ?></p>
      <div class="mv">
        <div><b><?php _e('Our Mission'); ?></b><p><?= e(excerpt(setting('mission'), 170)) ?></p></div>
        <div><b><?php _e('Our Vision'); ?></b><p><?= e(excerpt(setting('vision'), 170)) ?></p></div>
      </div>
      <a class="link" href="<?= e(url('about')) ?>"><?php _e('More about us'); ?> <?= icon('arrow') ?></a>
    </div>
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('What we do'); ?></span><h2><?php _e('Four ways we serve the Lango people'); ?></h2></div></div>
    <div class="grid g4">
      <a class="pillar reveal" href="<?= e(url('heritage')) ?>"><span class="pillar__n">01</span><h3><?php _e('Cultural preservation'); ?></h3><p><?php _e('Safeguarding dances like Okeme and Abuda, sacred sites and oral histories.'); ?></p><span class="link"><?php _e('Explore heritage'); ?> <?= icon('arrow') ?></span></a>
      <a class="pillar reveal" href="<?= e(url('programs', ['category' => 'education'])) ?>"><span class="pillar__n">02</span><h3><?php _e('Education & youth'); ?></h3><p><?php _e('Scholarships, mentorship and skills rooted in Lango identity.'); ?></p><span class="link"><?php _e('See programs'); ?> <?= icon('arrow') ?></span></a>
      <a class="pillar reveal" href="<?= e(url('clans')) ?>"><span class="pillar__n">03</span><h3><?php _e('Community unity'); ?></h3><p><?php _e('Bringing 149 clans across nine districts together in peace and development.'); ?></p><span class="link"><?php _e('Clan explorer'); ?> <?= icon('arrow') ?></span></a>
      <a class="pillar reveal" href="<?= e(url('library')) ?>"><span class="pillar__n">04</span><h3><?php _e('Digital heritage'); ?></h3><p><?php _e('A secure archive of clans, language, media and documents for everyone.'); ?></p><span class="link"><?php _e('Open the Data Center'); ?> <?= icon('arrow') ?></span></a>
    </div>
  </div>
</section>

<section class="section section--tight">
  <div class="container"><div class="langband">
    <div class="langband__txt">
      <span class="eyebrow"><?php _e('Leb Lango'); ?></span>
      <h2><?php _e('Search the dictionary. Translate with AI.'); ?></h2>
      <p><?= sprintf(__('%s words with meanings and examples — and a translator that turns English into Leb Lango and back. Know a word we are missing? Add it and our language team will review it.'), number_format($counts['words'])) ?></p>
      <form class="lhub__search lhub__search--lg" action="<?= e(url('language/dictionary')) ?>" role="search"><input type="search" name="q" placeholder="<?= e(__('Search a word in Leb Lango or English…')) ?>" aria-label="<?= e(__('Search the dictionary')) ?>"><button class="btn btn--gold"><?= icon('search') ?> <?php _e('Search'); ?></button></form>
      <div class="langband__btns">
        <a class="btn btn--primary" href="<?= e(url('language/dictionary')) ?>"><?= icon('book') ?> <?php _e('Open the dictionary'); ?></a>
        <a class="btn btn--ghost" href="<?= e(url('language/translate')) ?>"><?= icon('globe') ?> <?php _e('AI Lango translator'); ?></a>
        <a class="btn btn--ghost" href="<?= e(url('contribute', ['type' => 'language'])) ?>"><?= icon('plus') ?> <?php _e('Suggest a word'); ?></a>
      </div>
    </div>
    <?php if ($wotd): ?><div class="langband__card"><small><?php _e('Word of the day'); ?></small><h3><?= e($wotd['lango']) ?></h3><p><?= e(str_replace(';', ' ·', $wotd['english'])) ?></p>
      <div class="langband__try"><span><?php _e('Try the translator'); ?></span>
        <a href="<?= e(url('language/translate', ['text' => 'thank you', 'dir' => 'en-lango'])) ?>">thank you</a><a href="<?= e(url('language/translate', ['text' => 'water', 'dir' => 'en-lango'])) ?>">water</a><a href="<?= e(url('language/translate', ['text' => 'how are you', 'dir' => 'en-lango'])) ?>">how are you</a></div></div><?php endif; ?>
  </div></div>
</section>

<section class="section section--tight" style="padding-top:0">
  <div class="container"><div class="statband">
    <div><b data-count="<?= $counts['clans'] ?>">0</b><span><?php _e('Registered clans'); ?></span></div>
    <div><b data-count="<?= $counts['districts'] ?>">0</b><span><?php _e('Districts'); ?></span></div>
    <div><b data-count="<?= $counts['words'] ?>">0</b><span><?php _e('Words documented'); ?></span></div>
    <div><b data-count="<?= max($counts['archived'], 1) ?>">0</b><span><?php _e('Heritage items'); ?></span></div>
  </div></div>
</section>

<section class="section" style="padding-top:34px">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Featured programs'); ?></span><h2><?php _e('Programs you can support'); ?></h2></div><a class="link" href="<?= e(url('programs')) ?>"><?php _e('All programs'); ?> <?= icon('arrow') ?></a></div>
    <div class="pgrid"><?php foreach ($programs as $p) echo program_card($p); ?></div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container"><div class="support" style="background-image:url('<?= e(site_img('cta_image', 'assets/img/photos/hero-road.jpg')) ?>')">
    <div><span class="eyebrow"><?php _e('Be part of the story'); ?></span><h2><?php _e('Your support means a lot to Lango'); ?></h2><p><?= e(setting('donation_note')) ?></p></div>
    <a class="btn btn--gold" href="<?= e(url('donate')) ?>"><?= icon('heart') ?> <?php _e('Donate now'); ?></a>
  </div></div>
</section>

<?php if ($events): ?>
<section class="section section--cream">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Events'); ?></span><h2><?php _e('Upcoming events'); ?></h2></div><a class="link" href="<?= e(url('events')) ?>"><?php _e('All events'); ?> <?= icon('arrow') ?></a></div>
    <div class="grid g3">
      <?php foreach ($events as $i => $ev): $ts = strtotime($ev['starts_at']); ?>
        <a class="evcard reveal" href="<?= e(url('events/' . $ev['slug'])) ?>">
          <img loading="lazy" src="<?= e(media_url($ev['image'], 'assets/img/photos/' . $progImgs[$i % 6] . '.jpg')) ?>" alt="">
          <span class="evcard__date"><b><?= e(date('j', $ts)) ?></b><small><?= e(date('M', $ts)) ?></small></span>
          <span class="evcard__in"><h3><?= e(t($ev, 'title')) ?></h3><span class="meta"><span><?= icon('pin') ?> <?= e((string)$ev['location']) ?></span><span><?= icon('clock') ?> <?= e(date('g:i A', $ts)) ?></span></span></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Heritage'); ?></span><h2><?php _e('Explore Lango heritage'); ?></h2></div><a class="link" href="<?= e(url('heritage')) ?>"><?php _e('All heritage'); ?> <?= icon('arrow') ?></a></div>
    <div class="split" style="align-items:stretch;gap:28px">
      <div class="grid g2" style="gap:18px">
        <a class="tile reveal" href="<?= e(url('clans')) ?>"><span class="ico"><?= icon('users') ?></span><h3><?php _e('Clan Explorer'); ?></h3><p><?php _e('Histories, totems and leaders.'); ?></p></a>
        <a class="tile reveal" href="<?= e(url('heritage/ceremonies')) ?>"><span class="ico"><?= icon('star') ?></span><h3><?php _e('Ceremonies'); ?></h3><p><?php _e('Rituals and customs.'); ?></p></a>
        <a class="tile reveal" href="<?= e(url('heritage/music')) ?>"><span class="ico"><?= icon('music') ?></span><h3><?php _e('Music & Dance'); ?></h3><p><?php _e('Okeme, Abuda and more.'); ?></p></a>
        <a class="tile reveal" href="<?= e(url('species')) ?>"><span class="ico"><?= icon('leaf') ?></span><h3><?php _e('Biodiversity'); ?></h3><p><?php _e('Plants and animals of Lango.'); ?></p></a>
      </div>
      <div class="reveal" style="display:flex;flex-direction:column;gap:18px">
        <div class="box box--dark" style="flex:1">
          <h4><?= icon('chat') ?> <?php _e('Ask the Cultural Assistant'); ?></h4>
          <p style="margin-bottom:14px;font-size:.92rem"><?php _e('Who leads a clan? What is a district known for? Who was Gen. Oyite-Ojok? Get answers drawn from our reviewed archive, with sources.'); ?></p>
          <a class="btn btn--gold btn--sm" href="<?= e(url('assistant')) ?>"><?php _e('Start chatting'); ?></a>
        </div>
        <div class="box" style="flex:1">
          <h4><?= icon('pin') ?> <?php _e('Explore the districts'); ?></h4>
          <div class="chipcloud"><?php foreach ($districtList as $d): ?><a href="<?= e(url('districts/' . $d['slug'])) ?>"><?= e($d['name']) ?></a><?php endforeach; ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($notables): ?>
<section class="section section--cream">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Notable Langos'); ?></span><h2><?php _e('Sons and daughters of Lango every young person should know'); ?></h2></div><a class="link" href="<?= e(url('people')) ?>"><?php _e('Meet them all'); ?> <?= icon('arrow') ?></a></div>
    <div class="grid g4"><?php foreach ($notables as $n) echo person_card($n); ?></div>
  </div>
</section>
<?php endif; ?>

<section class="section" style="padding-top:0" id="contribute-cta">
  <div class="container"><div class="darkpanel">
    <div class="sec-head"><span class="eyebrow"><?php _e('Community Contribution Engine'); ?></span><h2><?php _e('Every Lango voice can help build the archive'); ?></h2><p><?php _e('Photograph a rare bird. Record an elder. Share a proverb. Our experts verify every contribution before it joins the archive — and you keep your rights.'); ?></p></div>
    <div class="steps">
      <div class="step"><h4><?php _e('Choose'); ?></h4><p><?php _e('Culture, nature, language, oral history, documents or heritage sites.'); ?></p></div>
      <div class="step"><h4><?php _e('Describe'); ?></h4><p><?php _e('A few guided questions: what, where, when and which clan.'); ?></p></div>
      <div class="step"><h4><?php _e('Upload'); ?></h4><p><?php _e('Add a photo, audio or video, and pin the location with GPS.'); ?></p></div>
      <div class="step"><h4><?php _e('Get verified'); ?></h4><p><?php _e('Clan leaders and experts review it. Track the status with your code.'); ?></p></div>
    </div>
    <p style="margin:30px 0 0"><a class="btn btn--gold" href="<?= e(url('contribute')) ?>"><?= icon('camera') ?> <?php _e('Submit a contribution'); ?></a></p>
  </div></div>
</section>

<section class="section section--cream" id="app">
  <div class="container split">
    <div class="reveal">
      <span class="eyebrow"><?php _e('The Lango App'); ?></span>
      <h2><?php _e('All of Lango, in your pocket'); ?></h2>
      <p><?php _e('The Lango App brings the dictionary, clan pages, proverbs and the contribution tool to Android, iOS and the web — with offline access, low-data mode and SMS fallback for areas without internet.'); ?></p>
      <ul style="padding:0;list-style:none;display:grid;gap:9px;margin:0 0 24px;color:var(--ink);font-weight:500">
        <?php foreach (['Offline dictionary and proverbs', 'Contribute from the field, sync later', 'Push alerts for events and festivals', 'English and Leb Lango interface'] as $f): ?>
          <li style="display:flex;gap:10px;align-items:center"><span style="color:var(--sky-d)"><?= icon('check') ?></span> <?= e(__($f)) ?></li><?php endforeach; ?>
      </ul>
      <span class="tag tag--gold"><?php _e('Coming soon'); ?></span> &nbsp;<a class="btn btn--outline btn--sm" href="<?= e(url('contribute')) ?>"><?php _e('Contribute on the web today'); ?></a>
    </div>
    <div class="reveal" aria-hidden="true"><div class="phone"><div class="phone__s">
      <div class="phone__t">Lango</div>
      <div class="phone__c">
        <div><?= icon('camera') ?>Contribute</div><div><?= icon('book') ?>Dictionary</div><div><?= icon('users') ?>Clans</div><div><?= icon('leaf') ?>Nature</div><div><?= icon('calendar') ?>Events</div><div><?= icon('chat') ?>Assistant</div>
      </div>
      <?php if ($wotd): ?><div class="phone__w"><small><?php _e('Word of the day'); ?></small><b><?= e($wotd['lango']) ?></b><span><?= e(excerpt($wotd['english'], 40)) ?></span></div><?php endif; ?>
    </div></div></div>
  </div>
</section>

<section class="section section--tight">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Gallery'); ?></span><h2><?php _e('Moments from Lango'); ?></h2></div><a class="link" href="<?= e(url('media')) ?>"><?php _e('Media center'); ?> <?= icon('arrow') ?></a></div>
    <div class="strip">
      <?php foreach ($strip as [$src, $ttl]): ?><a href="<?= e(url('media')) ?>"><img loading="lazy" src="<?= e($src) ?>" alt="<?= e($ttl) ?>"></a><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('News & articles'); ?></span><h2><?php _e('Latest news'); ?></h2></div><a class="link" href="<?= e(url('news')) ?>"><?php _e('All news'); ?> <?= icon('arrow') ?></a></div>
    <div class="grid g3">
      <?php foreach ($posts as $p) echo card(url('news/' . $p['slug']), $p['image'], t($p, 'title'), excerpt(t($p, 'excerpt'), 120), (string)$p['cat'], '<span>' . icon('calendar') . ' ' . e(fmt_date($p['published_at'])) . '</span>' . ($p['author_name'] ? '<span>' . icon('user') . ' ' . e($p['author_name']) . '</span>' : '')); ?>
    </div>
  </div>
</section>

<?php if ($partners): ?>
<section class="section section--tight">
  <div class="container"><div class="sec-head"><span class="eyebrow"><?php _e('Our partners'); ?></span></div>
    <div class="partners"><?php foreach ($partners as $pt): ?>
      <a href="<?= e($pt['url'] ?: '#') ?>" <?= $pt['url'] ? 'target="_blank" rel="noopener"' : '' ?>><?= $pt['logo'] ? '<img src="' . e(media_url($pt['logo'])) . '" alt="' . e($pt['name']) . '">' : '<span>' . e($pt['name']) . '</span>' ?></a>
    <?php endforeach; ?></div></div>
</section>
<?php endif; ?>
<?php page_end(); ?>
