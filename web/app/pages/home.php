<?php
declare(strict_types=1);

$counts   = site_counts();
$wotd     = word_of_day();
$posts    = all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.status='published' AND p.published_at<=NOW() ORDER BY p.featured DESC, p.published_at DESC LIMIT 3");
$events   = all("SELECT * FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT 3");
$notables = all("SELECT * FROM notables WHERE status='published' ORDER BY featured DESC, sort LIMIT 4");
$districtList = all("SELECT name, slug, kind FROM districts WHERE status='published' ORDER BY kind DESC, name");
$photos   = all("SELECT file, title FROM media WHERE type='photo' AND status='published' AND file IS NOT NULL ORDER BY id DESC LIMIT 6");
$defaults = ['hero-road', 'hero-site', 'hero-hill', 'hero-aerial', 'hero-field', 'hero-ridge'];
$strip    = [];
foreach ($defaults as $i => $d) $strip[] = isset($photos[$i]) ? [media_url($photos[$i]['file']), $photos[$i]['title']] : [asset('assets/img/photos/' . $d . '.jpg'), ''];
$next     = $events[0] ?? null;
$progImgs = ['hero-aerial', 'hero-field', 'hero-ridge', 'hero-hill', 'hero-site', 'hero-road'];

$moments = [
    ['dancers-group', __('Dancers in traditional dress')],
    ['harvest', __('Gathering the harvest')],
    ['pot-dance', __('A dancer balancing a pot')],
    ['warrior-rock', __('Spear, shield and feathered headdress')],
    ['dance-story', __('Performers with a handmade prop')],
];
$moment = fn(array $m) => '<figure class="mh-photo"><img loading="lazy" src="' . e(asset('assets/img/culture/' . $m[0] . '.jpg')) . '" alt="' . e($m[1]) . '"><figcaption>' . e($m[1]) . '</figcaption></figure>';
$explore = [['book', __('Dictionary'), 'language/dictionary'], ['users', __('Clans'), 'clans'], ['globe', __('Heritage'), 'heritage'], ['clock', __('History'), 'history'], ['award', __('Notable Langos'), 'people'], ['pin', __('Districts'), 'districts']];

page_start('', ['class' => 'home']);
?>
<!-- ============ phones: the same look as the Lango App ============ -->
<div class="mob mh">
  <div class="container">
    <div class="mh-hero">
      <img src="<?= e(asset('assets/img/photos/hero-ridge.jpg')) ?>" alt="" fetchpriority="high">
      <div class="mh-hero__in">
        <span class="mh-eyebrow"><?php _e('Everything about Lango, in one place'); ?></span>
        <h1><?php _e('Welcome to Lango'); ?></h1>
        <p><?php _e('Apwoyo!'); ?></p>
      </div>
    </div>
    <form class="mh-search" action="<?= e(url('language/dictionary')) ?>" role="search"><input type="search" name="q" placeholder="<?= e(__('Search a word')) ?>" aria-label="<?= e(__('Search the dictionary')) ?>"><button aria-label="<?= e(__('Search')) ?>"><?= icon('arrow') ?></button></form>

    <?php if ($wotd): ?>
    <a class="mh-word" href="<?= e(url('language/dictionary', ['q' => $wotd['lango']])) ?>"><small><?php _e('Word of the day'); ?></small><b><?= e($wotd['lango']) ?></b><span><?= e(excerpt(str_replace(';', ' ·', $wotd['english']), 60)) ?></span><i><?= icon('arrow') ?></i></a>
    <?php endif; ?>

    <div class="mh-head"><h2><?php _e('Explore'); ?></h2></div>
    <div class="mh-tiles"><?php foreach ($explore as [$ic, $lb, $pt]): ?><a href="<?= e(url($pt)) ?>"><span><?= icon($ic) ?></span><b><?= e($lb) ?></b></a><?php endforeach; ?></div>

    <div class="mh-head"><h2><?php _e('Moments from Lango'); ?></h2><a href="<?= e(url('media')) ?>"><?php _e('All'); ?> <?= icon('arrow') ?></a></div>
    <div class="mh-scroll"><?php foreach ($moments as $m) echo $moment($m); ?></div>

    <?php if ($notables): ?>
    <div class="mh-head"><h2><?php _e('Notable Langos'); ?></h2><a href="<?= e(url('people')) ?>"><?php _e('All'); ?> <?= icon('arrow') ?></a></div>
    <div class="mh-scroll mh-scroll--cards"><?php foreach ($notables as $n) echo person_card($n); ?></div>
    <?php endif; ?>

    <a class="mh-ask" href="<?= e(url('assistant')) ?>"><span><?= icon('chat') ?></span><div><b><?php _e('Ask the Cultural Assistant'); ?></b><small><?php _e('Answers from reviewed Lango sources'); ?></small></div><i><?= icon('arrow') ?></i></a>

    <div class="mh-cta">
      <h2><?php _e('Know something about Lango?'); ?></h2>
      <p><?php _e('Add a word, a photograph, a story or your clan. We only ask you to create a free account when you send it, and our reviewers check everything before it is published.'); ?></p>
      <a class="btn btn--gold btn--block" href="<?= e(url('contribute')) ?>"><?= icon('camera') ?> <?php _e('Share what you know'); ?></a>
    </div>
  </div>
</div>

<section class="fd-hero dsk">
  <div class="container fd-hero__in">
    <div class="fd-hero__txt">
      <span class="fd-pill"><i></i> <?php _e('Everything about Lango'); ?></span>
      <h1><?php _e('Our Language.'); ?><br><?php _e('Our Clans.'); ?><br><span><?php _e('Our Culture.'); ?></span></h1>
      <p class="fd-sub"><?php _e('One home for the Lango language, clans and heritage. Search the dictionary, find your clan and explore your culture.'); ?></p>
      <div class="fd-cta">
        <a class="btn btn--dark btn--lg" href="<?= e(url('language/dictionary')) ?>"><?= icon('book') ?> <?php _e('Search the dictionary'); ?></a>
        <a class="btn btn--outline btn--lg" href="<?= e(url('clans')) ?>"><?= icon('users') ?> <?php _e('Find your clan'); ?></a>
      </div>
      <ul class="fd-trust">
        <li><?= icon('check') ?> <?php _e('Free to use'); ?></li>
        <li><?= icon('shield') ?> <?php _e('Checked before it is published'); ?></li>
        <li><?= icon('phone-app') ?> <?php _e('On the web and your phone'); ?></li>
      </ul>
    </div>
    <div class="fd-art">
      <div class="fd-art__photo"><img src="<?= e(site_img('hero_image_1', 'assets/img/photos/hero-site.jpg')) ?>" alt="<?= e(__('A Lango heritage site')) ?>" fetchpriority="high"></div>
      <div class="fd-phone"><img src="<?= e(asset('assets/img/app/app-home.jpg')) ?>" alt="<?= e(__('The Lango App')) ?>" width="390" height="844"></div>
      <div class="fd-chip"><span class="ico"><?= icon('users') ?></span><span><b><?= (int)$counts['clans'] ?></b> <?php _e('clans listed'); ?></span></div>
    </div>
  </div>
</section>

<section class="fd-facts dsk"><div class="container"><ul class="fd-facts__in">
  <li><b><?= (int)$counts['clans'] ?></b><span><?php _e('Clans listed'); ?></span></li>
  <li><b><?= (int)$counts['districts'] ?></b><span><?php _e('Districts'); ?></span></li>
  <li><b><?= number_format($counts['words']) ?></b><span><?php _e('Words documented'); ?></span></li>
</ul></div></section>

<section class="container fd-block dsk">
  <span class="eyebrow" style="text-align:center"><?php _e('Everything in one place'); ?></span>
  <h2 class="fd-h2"><?php _e('Learn it. Share it. Keep it alive.'); ?></h2>
  <p class="fd-lead"><?php _e('Everything about Lango in one place, whether you are in Lira or on the other side of the world.'); ?></p>
  <div class="fd-grid">
    <a class="fd-card" href="<?= e(url('language/dictionary')) ?>"><span class="ico"><?= icon('book') ?></span><h3><?php _e('Dictionary & AI Translator'); ?></h3><p><?php _e('Search words in Leb Lango and English, and translate with AI.'); ?></p></a>
    <a class="fd-card" href="<?= e(url('clans')) ?>"><span class="ico"><?= icon('users') ?></span><h3><?php _e('Clan Explorer'); ?></h3><p><?php _e('Read about each clan and register under yours.'); ?></p></a>
    <a class="fd-card" href="<?= e(url('heritage')) ?>"><span class="ico"><?= icon('globe') ?></span><h3><?php _e('Heritage & History'); ?></h3><p><?php _e('Ceremonies, music, dance and the story of the Lango people.'); ?></p></a>
    <a class="fd-card" href="<?= e(url('contribute')) ?>"><span class="ico"><?= icon('camera') ?></span><h3><?php _e('Share what you know'); ?></h3><p><?php _e('Add a photo, a story or a word. Experts check it before it joins the archive.'); ?></p></a>
    <a class="fd-card" href="<?= e(url('events')) ?>"><span class="ico"><?= icon('calendar') ?></span><h3><?php _e('Events & Programs'); ?></h3><p><?php _e('Follow what is happening and support the work.'); ?></p></a>
    <a class="fd-card" href="<?= e(url('assistant')) ?>"><span class="ico"><?= icon('chat') ?></span><h3><?php _e('Cultural Assistant'); ?></h3><p><?php _e('Ask a question and get an answer from reviewed Lango sources.'); ?></p></a>
  </div>
</section>

<section class="container fd-block dsk">
  <div class="sec-row"><div><span class="eyebrow"><?php _e('Gallery'); ?></span><h2><?php _e('Moments from Lango'); ?></h2></div><a class="link" href="<?= e(url('media')) ?>"><?php _e('Media center'); ?> <?= icon('arrow') ?></a></div>
  <div class="mh-scroll mh-scroll--wide"><?php foreach ($moments as $m) echo $moment($m); ?></div>
</section>

<section class="section dsk">
  <div class="container split">
    <div class="collage reveal">
      <div class="collage__a"><img loading="lazy" src="<?= e(site_img('about_image_1', 'assets/img/photos/hero-aerial.jpg')) ?>" alt=""></div>
      <div class="collage__b"><img loading="lazy" src="<?= e(site_img('about_image_2', 'assets/img/photos/hero-field.jpg')) ?>" alt=""></div>
      <div class="collage__badge"><b><?= (int)$counts['districts'] ?></b><small><?php _e('Districts of Lango'); ?></small></div>
    </div>
    <div class="reveal">
      <span class="eyebrow"><?php _e('Welcome'); ?></span>
      <h2><?php _e('A home for Lango on the web'); ?></h2>
      <p><?php _e('Lango is a community website about the Lango people of mid-northern Uganda: their language, clans, districts, history and culture. It is free to use, and anyone can add to it.'); ?></p>
      <p><?php _e('Culture is identity. Add your clan, your words and your stories, and help keep what makes us Lango for the next generation.'); ?></p>
      <a class="link" href="<?= e(url('about')) ?>"><?php _e('More about Lango'); ?> <?= icon('arrow') ?></a>
    </div>
  </div>
</section>

<section class="section section--tight dsk">
  <div class="container"><div class="langband">
    <div class="langband__txt">
      <span class="eyebrow"><?php _e('Leb Lango'); ?></span>
      <h2><?php _e('Search the dictionary. Translate with AI.'); ?></h2>
      <p><?= sprintf(__('%s words with meanings and examples — and a word translator (whole sentences are coming soon). Know a word we are missing? Add it and our language team will review it.'), number_format($counts['words'])) ?></p>
      <form class="lhub__search lhub__search--lg" action="<?= e(url('language/dictionary')) ?>" role="search"><input type="search" name="q" placeholder="<?= e(__('Search a word in Leb Lango or English…')) ?>" aria-label="<?= e(__('Search the dictionary')) ?>"><button class="btn btn--gold"><?= icon('search') ?> <?php _e('Search'); ?></button></form>
      <div class="langband__btns">
        <a class="btn btn--primary" href="<?= e(url('language/dictionary')) ?>"><?= icon('book') ?> <?php _e('Open the dictionary'); ?></a>
        <a class="btn btn--ghost" href="<?= e(url('language/translate')) ?>"><?= icon('globe') ?> <?php _e('AI Lango translator'); ?></a>
        <a class="btn btn--ghost" href="<?= e(url('contribute', ['type' => 'language'])) ?>"><?= icon('plus') ?> <?php _e('Suggest a word'); ?></a>
      </div>
    </div>
    <?php if ($wotd): ?><div class="langband__card"><small><?php _e('Word of the day'); ?></small><h3><?= e($wotd['lango']) ?></h3><p><?= e(str_replace(';', ' ·', $wotd['english'])) ?></p>
      <div class="langband__try"><span><?php _e('Try the translator'); ?></span>
        <a href="<?= e(url('language/translate', ['text' => 'water', 'dir' => 'en-lango'])) ?>">water</a><a href="<?= e(url('language/translate', ['text' => 'mother', 'dir' => 'en-lango'])) ?>">mother</a><a href="<?= e(url('language/translate', ['text' => 'love', 'dir' => 'en-lango'])) ?>">love</a></div></div><?php endif; ?>
  </div></div>
</section>

<section class="section section--tight dsk" style="padding-top:0">
  <div class="container"><div class="statband">
    <div><b data-count="<?= $counts['clans'] ?>">0</b><span><?php _e('Clans listed'); ?></span></div>
    <div><b data-count="<?= $counts['districts'] ?>">0</b><span><?php _e('Districts'); ?></span></div>
    <div><b data-count="<?= $counts['words'] ?>">0</b><span><?php _e('Words documented'); ?></span></div>
    <div><b data-count="<?= max($counts['archived'], 1) ?>">0</b><span><?php _e('Heritage items'); ?></span></div>
  </div></div>
</section>

<?php if ($events): ?>
<section class="section section--cream mh-events">
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

<section class="section dsk">
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
<section class="section section--cream dsk">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Notable Langos'); ?></span><h2><?php _e('Sons and daughters of Lango every young person should know'); ?></h2></div><a class="link" href="<?= e(url('people')) ?>"><?php _e('Meet them all'); ?> <?= icon('arrow') ?></a></div>
    <div class="grid g4"><?php foreach ($notables as $n) echo person_card($n); ?></div>
  </div>
</section>
<?php endif; ?>

<section class="section dsk" style="padding-top:0" id="contribute-cta">
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

<section class="section section--cream dsk" id="app">
  <div class="container split">
    <div class="reveal">
      <span class="eyebrow"><?php _e('The Lango App'); ?></span>
      <h2><?php _e('All of Lango, in your pocket'); ?></h2>
      <p><?php _e('The Lango App brings the dictionary, clan pages, proverbs and the contribution tool to Android, iOS and the web — with offline access and a low-data mode.'); ?></p>
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

<?php if ($posts): ?>
<section class="section section--cream">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('News & articles'); ?></span><h2><?php _e('Latest news'); ?></h2></div><a class="link" href="<?= e(url('news')) ?>"><?php _e('All news'); ?> <?= icon('arrow') ?></a></div>
    <div class="grid g3">
      <?php foreach ($posts as $p) echo card(url('news/' . $p['slug']), $p['image'], t($p, 'title'), excerpt(t($p, 'excerpt'), 120), (string)$p['cat'], '<span>' . icon('calendar') . ' ' . e(fmt_date($p['published_at'])) . '</span>' . ($p['author_name'] ? '<span>' . icon('user') . ' ' . e($p['author_name']) . '</span>' : '')); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="container fd-block dsk" style="padding-bottom:48px">
  <div class="fd-join">
    <div class="fd-join__photo"><img loading="lazy" src="<?= e(asset('assets/img/culture/dancers-group.jpg')) ?>" alt="<?= e(__('Dancers in traditional dress')) ?>"></div>
    <div class="fd-join__body">
      <h2><?php _e('Know something about Lango?'); ?></h2>
      <p><?php _e('Add a word, a photograph, a story or your clan. We only ask you to create a free account when you send it, and our reviewers check everything before it is published.'); ?></p>
      <div class="fd-cta"><a class="btn btn--gold btn--lg" href="<?= e(url('contribute')) ?>"><?= icon('camera') ?> <?php _e('Share what you know'); ?></a></div>
    </div>
  </div>
</section>

<?php page_end(); ?>
