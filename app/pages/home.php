<?php
declare(strict_types=1);

$counts   = site_counts();
$wotd     = word_of_day();
$posts    = all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.status='published' AND p.published_at<=NOW() ORDER BY p.featured DESC, p.published_at DESC LIMIT 3");
$events   = all("SELECT * FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT 3");
$programs = all("SELECT * FROM programs WHERE status='published' ORDER BY featured DESC, sort, id LIMIT 3");
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
    <div>
      <div class="kicker"><?= e(setting('org_subtitle', 'Won Nyaci me Lango')) ?></div>
      <h1><?php _e('Our Culture.'); ?><br><?php _e('Our People.'); ?><br><em><?php _e('Our Future.'); ?></em></h1>
      <p class="lead"><?php _e('The Lango Cultural Institution safeguards our language, clans and traditions, and brings Lango to the world through a living, community-built digital archive.'); ?></p>
      <div class="hero__row">
        <a class="btn btn--primary" href="<?= e(url('contribute')) ?>"><?= icon('camera') ?> <?php _e('Become a Contributor'); ?></a>
        <?php if ($next): ?>
          <a class="playcard" href="<?= e(url('events/' . $next['slug'])) ?>">
            <span class="playcard__img"><img src="<?= e(media_url($next['image'], 'assets/img/photos/hero-site.jpg')) ?>" alt=""><i><span><?= icon('play') ?></span></i></span>
            <span><b><?php _e('Join our upcoming event'); ?></b><small><?= e(t($next, 'title')) ?> · <?= e(fmt_date($next['starts_at'], 'j M')) ?></small></span>
          </a>
        <?php else: ?>
          <a class="playcard" href="<?= e(url('about')) ?>">
            <span class="playcard__img"><img src="<?= e(site_img('hero_image_1', 'assets/img/photos/hero-site.jpg')) ?>" alt=""><i><span><?= icon('play') ?></span></i></span>
            <span><b><?php _e('Discover LCI'); ?></b><small><?php _e('Who we are and what we do'); ?></small></span>
          </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="hv" aria-hidden="true">
      <span class="hv__dot hv__d2"></span><span class="hv__dot hv__d1"></span>
      <div class="hv__c hv__c1"><img src="<?= e(site_img('hero_image_2', 'assets/img/photos/hero-hill.jpg')) ?>" alt=""></div>
      <div class="hv__c hv__c2"><img src="<?= e(site_img('hero_image_1', 'assets/img/photos/hero-site.jpg')) ?>" alt=""></div>
      <span class="hv__dot hv__d3"></span>
      <div class="hv__card"><span class="ic"><?= icon('users') ?></span><span><b><?= (int)$counts['clans'] ?></b><small><?php _e('Registered clans united'); ?></small></span></div>
    </div>
  </div>
</section>

<div class="quick"><div class="container"><div class="quick__grid">
  <a class="qcard q-blue" href="<?= e(url('contribute')) ?>"><span class="ico"><?= icon('camera') ?></span><span><b><?php _e('Become a contributor'); ?></b><span class="t"><?php _e('Share photos, stories and sightings.'); ?></span></span></a>
  <a class="qcard q-orange" href="<?= e(url('donate')) ?>"><span class="ico"><?= icon('heart') ?></span><span><b><?php _e('Donate'); ?></b><span class="t"><?php _e('Support Lango heritage programs.'); ?></span></span></a>
  <a class="qcard q-green" href="<?= e(url('library')) ?>"><span class="ico"><?= icon('book') ?></span><span><b><?php _e('Explore the Library'); ?></b><span class="t"><?php _e('Search the Data Center.'); ?></span></span></a>
  <a class="qcard q-pink" href="#app"><span class="ico"><?= icon('phone-app') ?></span><span><b><?php _e('Get the Lango App'); ?></b><span class="t"><?php _e('Offline, in your pocket.'); ?></span></span></a>
</div></div></div>

<section class="section">
  <div class="container split">
    <div class="collage reveal">
      <span class="collage__o"></span><span class="collage__t"></span>
      <div class="collage__a"><img loading="lazy" src="<?= e(site_img('about_image_1', 'assets/img/photos/hero-aerial.jpg')) ?>" alt=""></div>
      <div class="collage__b"><img loading="lazy" src="<?= e(site_img('about_image_2', 'assets/img/photos/hero-field.jpg')) ?>" alt=""></div>
      <div class="collage__badge"><b><?= (int)$counts['districts'] ?></b><small><?php _e('Districts'); ?></small></div>
    </div>
    <div class="reveal">
      <div class="eyebrow"><?php _e('Welcome to LCI'); ?></div>
      <h2><?php _e('A people with one heart and a shared future'); ?></h2>
      <p><?php _e('The Lango Cultural Institution (LCI) is the traditional authority of the Lango people of mid-northern Uganda. Under the leadership of Won Nyaci Eng. Dr. Moses Michael Odongo Okune, we serve communities across Lira, Apac, Alebtong, Dokolo, Amolatar, Oyam, Kole, Otuke and Kwania.'); ?></p>
      <p><?php _e('We believe that culture is identity. Together with 149 registered clans, we preserve what makes us Lango and build a future our children can be proud of.'); ?></p>
      <div class="mv">
        <div><span class="ic"><?= icon('flag') ?></span><span><b><?php _e('Our Mission'); ?></b><p><?= e(excerpt(setting('mission'), 190)) ?></p></span></div>
        <div><span class="ic o"><?= icon('eye') ?></span><span><b><?php _e('Our Vision'); ?></b><p><?= e(excerpt(setting('vision'), 190)) ?></p></span></div>
      </div>
      <a class="btn btn--primary" href="<?= e(url('about')) ?>"><?php _e('More About Us'); ?> <?= icon('arrow') ?></a>
    </div>
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('What we do'); ?></div><h2><?php _e('Your support means a brighter future for Lango'); ?></h2></div>
    <div class="grid g4">
      <a class="tile reveal" href="<?= e(url('heritage')) ?>"><span class="ico"><?= icon('star') ?></span><h3><?php _e('Cultural Preservation'); ?></h3><p><?php _e('Safeguarding dances like Okeme and Abuda, sacred sites and oral histories.'); ?></p></a>
      <a class="tile reveal" href="<?= e(url('programs', ['category' => 'education'])) ?>"><span class="ico"><?= icon('book') ?></span><h3><?php _e('Education & Youth'); ?></h3><p><?php _e('Scholarships, mentorship and skills rooted in Lango identity.'); ?></p></a>
      <a class="tile reveal" href="<?= e(url('clans')) ?>"><span class="ico"><?= icon('users') ?></span><h3><?php _e('Community Unity'); ?></h3><p><?php _e('Bringing 149 clans across nine districts together in peace and development.'); ?></p></a>
      <a class="tile reveal" href="<?= e(url('library')) ?>"><span class="ico"><?= icon('db') ?></span><h3><?php _e('Digital Heritage'); ?></h3><p><?php _e('A secure archive of clans, language, media and documents for all.'); ?></p></a>
    </div>
  </div>
</section>

<section class="section" style="padding-top:70px;padding-bottom:30px">
  <div class="container"><div class="band-stats">
    <div class="bs"><b data-count="<?= $counts['clans'] ?>">0</b><span><?php _e('Registered Clans'); ?></span></div>
    <div class="bs"><b data-count="<?= $counts['districts'] ?>">0</b><span><?php _e('Districts'); ?></span></div>
    <div class="bs"><b data-count="<?= $counts['words'] ?>">0</b><span><?php _e('Words Documented'); ?></span></div>
    <div class="bs"><b data-count="<?= max($counts['archived'], 1) ?>">0</b><span><?php _e('Heritage Items'); ?></span></div>
  </div></div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('Featured programs'); ?></div><h2><?php _e('Find the programs you want to support'); ?></h2><p><?php _e('Preserving heritage, empowering youth and embracing digital innovation to serve Lango today and for generations to come.'); ?></p></div>
    <div class="grid g3">
      <?php foreach ($programs as $p): $goal = (float)$p['goal_amount']; $raised = (float)$p['raised_amount']; $pct = $goal > 0 ? min(100, (int)round($raised / $goal * 100)) : (int)$p['progress']; ?>
        <article class="card cause reveal">
          <a class="card__img" href="<?= e(url('programs/' . $p['slug'])) ?>"><span class="tag"><?= e(__(PROGRAM_CATS[$p['category']] ?? '')) ?></span><img loading="lazy" src="<?= e(media_url($p['image'], 'assets/img/photos/' . $progImgs[$p['id'] % 6] . '.jpg')) ?>" alt=""></a>
          <div class="card__body">
            <h3><a href="<?= e(url('programs/' . $p['slug'])) ?>"><?= e(t($p, 'title')) ?></a></h3>
            <p><?= e(excerpt($p['summary'], 110)) ?></p>
            <div style="margin-top:12px"><div class="progress"><i style="width:<?= $pct ?>%"></i></div>
              <div class="pmeta"><span><?= $goal > 0 ? e(money($raised)) . ' ' . e(__('raised')) : e(__('Progress')) ?></span><b><?= $pct ?>%</b></div></div>
            <div class="cause__foot"><a class="card__more" style="padding:0" href="<?= e(url('programs/' . $p['slug'])) ?>"><?php _e('Learn more'); ?> <?= icon('arrow') ?></a>
              <?php if ($p['accepts_donations']): ?><a class="btn btn--primary btn--sm" href="<?= e(url('donate', ['program' => $p['id']])) ?>"><?php _e('Donate'); ?></a><?php endif; ?></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:40px"><a class="btn btn--outline" href="<?= e(url('programs')) ?>"><?php _e('See all programs'); ?></a></p>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container"><div class="cta-photo" style="background-image:url('<?= e(site_img('cta_image', 'assets/img/photos/hero-road.jpg')) ?>')">
    <div class="kicker"><?php _e('Be part of the story'); ?></div>
    <h2><?php _e('Your support means a lot to Lango. Give what you can.'); ?></h2>
    <p><?= e(setting('donation_note')) ?></p>
    <a class="btn btn--primary" href="<?= e(url('donate')) ?>"><?= icon('heart') ?> <?php _e('Donate Now'); ?></a>
  </div></div>
</section>

<?php if ($events): ?>
<section class="section section--cream">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('Events'); ?></div><h2><?php _e('Join our upcoming events'); ?></h2></div>
    <div class="grid g3">
      <?php foreach ($events as $i => $ev): $ts = strtotime($ev['starts_at']); ?>
        <a class="evcard reveal" href="<?= e(url('events/' . $ev['slug'])) ?>">
          <img loading="lazy" src="<?= e(media_url($ev['image'], 'assets/img/photos/' . $progImgs[$i % 6] . '.jpg')) ?>" alt="">
          <span class="evcard__date"><b><?= e(date('j', $ts)) ?></b><small><?= e(date('M', $ts)) ?></small></span>
          <span class="evcard__in"><h3><?= e(t($ev, 'title')) ?></h3><span class="meta"><span><?= icon('pin') ?> <?= e((string)$ev['location']) ?></span><span><?= icon('clock') ?> <?= e(date('g:i A', $ts)) ?></span></span></span>
        </a>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:40px"><a class="btn btn--primary" href="<?= e(url('events')) ?>"><?php _e('All events'); ?></a></p>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('Heritage'); ?></div><h2><?php _e('Explore Lango heritage'); ?></h2><p><?php _e('From clans and totems to ceremonies, music and the Leb Lango language — the living culture of the Lango people.'); ?></p></div>
    <div class="split" style="align-items:stretch">
      <div class="grid g2">
        <a class="tile reveal" href="<?= e(url('clans')) ?>"><span class="ico"><?= icon('users') ?></span><h3><?php _e('Clan Explorer'); ?></h3><p><?php _e('Histories, totems and leaders.'); ?></p></a>
        <a class="tile reveal" href="<?= e(url('heritage/ceremonies')) ?>"><span class="ico"><?= icon('star') ?></span><h3><?php _e('Ceremonies'); ?></h3><p><?php _e('Rituals and customs.'); ?></p></a>
        <a class="tile reveal" href="<?= e(url('heritage/music')) ?>"><span class="ico"><?= icon('music') ?></span><h3><?php _e('Music & Dance'); ?></h3><p><?php _e('Okeme, Abuda and more.'); ?></p></a>
        <a class="tile reveal" href="<?= e(url('species')) ?>"><span class="ico"><?= icon('leaf') ?></span><h3><?php _e('Biodiversity'); ?></h3><p><?php _e('Plants and animals of Lango.'); ?></p></a>
      </div>
      <div class="reveal" style="display:flex;flex-direction:column;gap:22px">
        <?php if ($wotd): ?>
        <div class="wotd">
          <small><?php _e('Word of the day'); ?></small>
          <h3><?= e($wotd['lango']) ?></h3>
          <p style="font-size:1.15rem;margin:0 0 4px;color:#fff"><?= e($wotd['english']) ?></p>
          <?php if ($wotd['pos']): ?><p style="opacity:.7;font-style:italic;margin:0 0 16px"><?= e($wotd['pos']) ?></p><?php endif; ?>
          <a class="btn btn--primary btn--sm" href="<?= e(url('language/dictionary')) ?>"><?php _e('Open the dictionary'); ?></a>
        </div>
        <?php endif; ?>
        <div class="box" style="flex:1">
          <h4><?= icon('chat') ?> <?php _e('Ask the Cultural Assistant'); ?></h4>
          <p style="margin-bottom:14px"><?php _e('Questions about Lango history, clans or language? Get answers drawn from our reviewed archive, with sources.'); ?></p>
          <a class="btn btn--dark btn--sm" href="<?= e(url('assistant')) ?>"><?php _e('Start chatting'); ?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section--dark" id="contribute-cta">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('Community Contribution Engine'); ?></div><h2><?php _e('Every Lango voice can help build the archive'); ?></h2><p><?php _e('Photograph a rare bird. Record an elder. Share a proverb. Our experts verify every contribution before it joins the archive — and you keep your rights.'); ?></p></div>
    <div class="steps">
      <div class="step"><h4><?php _e('Choose'); ?></h4><p><?php _e('Culture, nature, language, oral history, documents or heritage sites.'); ?></p></div>
      <div class="step"><h4><?php _e('Describe'); ?></h4><p><?php _e('A few guided questions: what, where, when and which clan.'); ?></p></div>
      <div class="step"><h4><?php _e('Upload'); ?></h4><p><?php _e('Add a photo, audio or video, and pin the location with GPS.'); ?></p></div>
      <div class="step"><h4><?php _e('Get verified'); ?></h4><p><?php _e('Clan leaders and experts review it. Track the status with your code.'); ?></p></div>
    </div>
    <p style="text-align:center;margin-top:44px"><a class="btn btn--primary" href="<?= e(url('contribute')) ?>"><?= icon('camera') ?> <?php _e('Submit a contribution'); ?></a></p>
  </div>
</section>

<section class="section" id="app">
  <div class="container split">
    <div class="reveal">
      <div class="eyebrow"><?php _e('The Lango App'); ?></div>
      <h2><?php _e('All of Lango, in your pocket'); ?></h2>
      <p><?php _e('The Lango App brings the dictionary, clan pages, proverbs and the contribution tool to Android, iOS and the web — with offline access, low-data mode and SMS fallback for areas without internet.'); ?></p>
      <ul style="padding:0;list-style:none;display:grid;gap:10px;margin:0 0 26px;color:var(--ink);font-weight:700">
        <?php foreach (['Offline dictionary and proverbs', 'Contribute from the field, sync later', 'Push alerts for events and festivals', 'English and Leb Lango interface'] as $f): ?>
          <li><span style="color:var(--teal)"><?= icon('check') ?></span> <?= e(__($f)) ?></li><?php endforeach; ?>
      </ul>
      <span class="tag tag--gold"><?php _e('Coming soon'); ?></span> &nbsp;<a class="btn btn--outline btn--sm" href="<?= e(url('contribute')) ?>"><?php _e('Contribute on the web today'); ?></a>
    </div>
    <div class="reveal" aria-hidden="true"><div class="phone"><div class="phone__s">
      <div class="phone__t">Lango</div>
      <div class="phone__c">
        <div><?= icon('camera') ?>Contribute</div><div><?= icon('book') ?>Dictionary</div><div><?= icon('users') ?>Clans</div><div><?= icon('leaf') ?>Nature</div><div><?= icon('calendar') ?>Events</div><div><?= icon('chat') ?>Assistant</div>
      </div>
    </div></div></div>
  </div>
</section>

<section aria-label="<?= e(__('Gallery')) ?>"><div class="strip">
  <?php foreach ($strip as [$src, $ttl]): ?><a href="<?= e(url('media')) ?>"><img loading="lazy" src="<?= e($src) ?>" alt="<?= e($ttl) ?>"></a><?php endforeach; ?>
</div></section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-head"><div class="eyebrow"><?php _e('News & articles'); ?></div><h2><?php _e('Our latest news and articles'); ?></h2></div>
    <div class="grid g3">
      <?php foreach ($posts as $p) echo card(url('news/' . $p['slug']), $p['image'], t($p, 'title'), excerpt(t($p, 'excerpt'), 120), (string)$p['cat'], '<span>' . icon('calendar') . ' ' . e(fmt_date($p['published_at'])) . '</span>' . ($p['author_name'] ? '<span>' . icon('user') . ' ' . e($p['author_name']) . '</span>' : '')); ?>
    </div>
    <p style="text-align:center;margin-top:40px"><a class="btn btn--outline" href="<?= e(url('news')) ?>"><?php _e('All news'); ?></a></p>
  </div>
</section>

<?php if ($partners): ?>
<section class="section" style="padding:60px 0">
  <div class="container"><h3 style="text-align:center;margin-bottom:28px"><?php _e('Our partners'); ?></h3>
    <div class="partners"><?php foreach ($partners as $pt): ?>
      <a href="<?= e($pt['url'] ?: '#') ?>" <?= $pt['url'] ? 'target="_blank" rel="noopener"' : '' ?>><?= $pt['logo'] ? '<img src="' . e(media_url($pt['logo'])) . '" alt="' . e($pt['name']) . '">' : '<span>' . e($pt['name']) . '</span>' ?></a>
    <?php endforeach; ?></div></div>
</section>
<?php endif; ?>
<?php page_end(); ?>
