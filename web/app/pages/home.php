<?php
declare(strict_types=1);

$counts   = site_counts();
$wotd     = word_of_day();
$posts    = all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.status='published' AND p.published_at<=NOW() ORDER BY p.featured DESC, p.published_at DESC LIMIT 3");
$events   = all("SELECT * FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT 3");
$notables = all("SELECT * FROM notables WHERE status='published' ORDER BY (photo IS NULL OR photo=''), featured DESC, sort LIMIT 4");   // those with a portrait first
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

    <?php if (community() === 'lango'): ?>
    <div class="mh-head"><h2><?php _e('Moments from Lango'); ?></h2><a href="<?= e(url('media')) ?>"><?php _e('All'); ?> <?= icon('arrow') ?></a></div>
    <div class="mh-scroll"><?php foreach ($moments as $m) echo $moment($m); ?></div>
    <?php endif; ?>

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

<!-- ============ large screens ============ -->
<section class="hx dsk">
  <img class="hx__bg" src="<?= e(asset('assets/img/lango/sunset-lira.jpg')) ?>" alt="<?= e(__('Sunset over Lira')) ?>" fetchpriority="high">
  <div class="container hx__in">
    <div class="hx__txt">
      <h1><?php _e('Our Language.'); ?><br><?php _e('Our Clans.'); ?><br><em><?php _e('Our Culture.'); ?></em></h1>
      <p><?php _e('One home for the Lango language, clans and heritage. Search the dictionary, find your clan and explore the culture of Lango, wherever you are.'); ?></p>
      <div class="hx__cta">
        <a class="hx__btn" href="<?= e(url('heritage')) ?>"><?php _e('Discover Lango'); ?><span><?= icon('arrow') ?></span></a>
        <a class="hx__ghost" href="<?= e(url('language/dictionary')) ?>"><?= icon('search') ?> <?php _e('Search the dictionary'); ?></a>
      </div>
    </div>
    <div class="hx__cards" aria-hidden="false">
      <figure class="hx__card hx__card--l"><img src="<?= e(asset('assets/img/culture/pot-dance.jpg')) ?>" alt="<?= e(__('A dancer balancing a pot')) ?>"></figure>
      <figure class="hx__card hx__card--c"><img src="<?= e(asset('assets/img/culture/warrior-rock.jpg')) ?>" alt="<?= e(__('Spear, shield and feathered headdress')) ?>"></figure>
      <figure class="hx__card hx__card--r"><img src="<?= e(asset('assets/img/culture/dancers-group.jpg')) ?>" alt="<?= e(__('Dancers in traditional dress')) ?>"></figure>
    </div>
  </div>
  <a class="hx__credit" href="<?= e(url('media')) ?>"><?= icon('camera') ?> <?php _e('Photo credits'); ?></a>
</section>

<div class="container hx-stats-wrap dsk"><ul class="hx-stats">
  <li><span class="ico"><?= icon('users') ?></span><div><b><?= (int)$counts['clans'] ?></b><small><?php _e('Clans listed'); ?></small></div></li>
  <li><span class="ico"><?= icon('pin') ?></span><div><b><?= (int)$counts['districts'] ?></b><small><?php _e('Districts'); ?></small></div></li>
  <li><span class="ico"><?= icon('book') ?></span><div><b><?= number_format($counts['words']) ?></b><small><?php _e('Words documented'); ?></small></div></li>
  <li><span class="ico"><?= icon('image') ?></span><div><b><?= number_format($counts['archived']) ?></b><small><?php _e('Heritage items'); ?></small></div></li>
</ul></div>

<section class="section dsk">
  <div class="container">
    <div class="sec-head hx-head"><span class="eyebrow"><?php _e('Explore Lango'); ?></span><h2><?php _e('Four ways in'); ?></h2>
      <p><?php _e('Start with the dances and ceremonies, the clans, the places or the history, and follow what interests you.'); ?></p></div>
    <div class="hx-explore">
      <?php foreach ([
          ['heritage', 'assets/img/lango/abuda-dancers.jpg', __('Culture & heritage'), __('Attire, crafts, music, dance and ceremonies.')],
          ['clans', 'assets/img/culture/dancers-group.jpg', __('Clans'), __('Read about each clan and its prohibitions.')],
          ['districts', 'assets/img/lango/ngetta-hill.jpg', __('Districts & places'), __('Ten districts, lakes, hills and monuments.')],
          ['history', 'assets/img/lango/heritage-centre.jpg', __('History'), __('From the first Lango to the new districts.')],
      ] as [$pt, $img, $ttl, $sub]): ?>
        <a class="hx-ex reveal" href="<?= e(url($pt)) ?>"><img loading="lazy" src="<?= e(asset($img)) ?>" alt=""><span class="hx-ex__t"><b><?= e($ttl) ?></b><small><?= e($sub) ?></small></span><span class="hx-ex__go"><?= icon('arrow') ?></span></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (community() === 'lango'): ?>
<section class="section section--cream dsk">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Gallery'); ?></span><h2><?php _e('Moments from Lango'); ?></h2></div><a class="link" href="<?= e(url('media')) ?>"><?php _e('All photos'); ?> <?= icon('arrow') ?></a></div>
    <div class="hx-mosaic">
      <?php foreach ([
          ['assets/img/lango/lira-dance.jpg', __('Traditional dance at a cultural gala'), 'big'],
          ['assets/img/culture/harvest.jpg', __('Gathering the harvest'), ''],
          ['assets/img/lango/lango-house.jpg', __('A Lango house'), ''],
          ['assets/img/lango/museum-crafts.jpg', __('Crafted work in the museum'), 'wide'],
          ['assets/img/lango/kraal-cows.jpg', __('Cows resting in a kraal, Oyam'), ''],
          ['assets/img/lango/lake-kwania.jpg', __('Lake Kwania'), ''],
          ['assets/img/lango/traditional-marriage.jpg', __('A Lango traditional marriage'), 'wide'],
      ] as [$img, $cap, $cls]): ?>
        <figure class="hx-m <?= e($cls) ?> reveal"><img loading="lazy" src="<?= e(asset($img)) ?>" alt="<?= e($cap) ?>"><figcaption><?= e($cap) ?></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--tight dsk">
  <div class="container"><div class="langband">
    <div class="langband__txt">
      <span class="eyebrow"><?php _e('Leb Lango'); ?></span>
      <h2><?php _e('Search the dictionary. Translate a word.'); ?></h2>
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

<?php if ($notables): ?>
<section class="section dsk">
  <div class="container">
    <div class="sec-row"><div><span class="eyebrow"><?php _e('Notable Langos'); ?></span><h2><?php _e('Sons and daughters of Lango every young person should know'); ?></h2></div><a class="link" href="<?= e(url('people')) ?>"><?php _e('Meet them all'); ?> <?= icon('arrow') ?></a></div>
    <div class="grid g4"><?php foreach ($notables as $n) echo person_card($n); ?></div>
  </div>
</section>
<?php endif; ?>

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

<section class="container hx-cta dsk">
  <img loading="lazy" src="<?= e(asset('assets/img/lango/kwania-rock.jpg')) ?>" alt="">
  <div class="hx-cta__body">
    <span class="eyebrow"><?php _e('Add your voice'); ?></span>
    <h2><?php _e('Know something about Lango?'); ?></h2>
    <p><?php _e('Add a word, a photograph, a story or your clan. We only ask you to create a free account when you send it, and our reviewers check everything before it is published.'); ?></p>
    <a class="hx__btn" href="<?= e(url('contribute')) ?>"><?php _e('Share what you know'); ?><span><?= icon('arrow') ?></span></a>
  </div>
</section>

<?php page_end(); ?>
