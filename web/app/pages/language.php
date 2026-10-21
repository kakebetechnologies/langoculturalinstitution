<?php
declare(strict_types=1);

$sub = $seg[1] ?? '';
$wordsTotal = (int)val("SELECT COUNT(*) FROM dictionary WHERE status='published'");
$communityWords = (int)val("SELECT COUNT(*) FROM dictionary WHERE status='published' AND source LIKE 'Community%'");

/** One dictionary entry card. */
$entry = function (array $r): string {
    $senses = array_filter(array_map('trim', explode(';', (string)$r['english'])));
    $h = '<article class="entry"><div class="entry__top"><h3 class="entry__w">' . e($r['lango']) . '</h3>';
    if ($r['pos']) $h .= '<span class="entry__pos">' . e($r['pos']) . '</span>';
    if ($r['category'] && isset(WORD_CATS[$r['category']])) $h .= '<a class="entry__cat" href="' . e(url('language/dictionary', ['cat' => $r['category']])) . '">' . e(__(WORD_CATS[$r['category']])) . '</a>';
    $h .= '<span class="entry__flags">';
    if (str_starts_with((string)$r['source'], 'Community')) $h .= '<span class="flag flag--community" title="' . e(__('Suggested by the community and approved by LCI')) . '">' . icon('users') . ' ' . e(__('Community')) . '</span>';
    elseif ($r['verified']) $h .= '<span class="flag flag--ok" title="' . e(__('Checked by LCI language experts')) . '">' . icon('check') . ' ' . e(__('Checked')) . '</span>';
    $h .= '</span></div><p class="entry__m">' . implode('<i> · </i>', array_map('e', $senses)) . '</p>';
    if ($r['example_lg']) $h .= '<p class="entry__ex"><em>' . e($r['example_lg']) . '</em>' . ($r['example_en'] ? ' — ' . e($r['example_en']) : '') . '</p>';
    if ($r['audio']) $h .= '<audio controls preload="none" src="' . e(media_url($r['audio'])) . '"></audio>';
    if ($r['contributor']) $h .= '<p class="entry__by">' . e(sprintf(__('Suggested by %s'), $r['contributor'])) . '</p>';
    return $h . '</article>';
};

$suggestCard = fn() => '<div class="box box--dark"><h4>' . icon('plus') . ' ' . e(__('Know a word we are missing?')) . '</h4><p>' . e(__('Names of animals, birds, plants, places, foods, everyday words — add it with its English meaning. Our language team reviews every suggestion before it joins the dictionary.')) . '</p><a class="btn btn--gold btn--sm" href="' . e(url('contribute', ['type' => 'language'])) . '">' . e(__('Suggest a word')) . '</a></div>';

/* =============================== dictionary =============================== */
if ($sub === 'dictionary') {
    $qs = mb_substr(get('q'), 0, 80);
    $dir = in_array(get('dir'), ['lg', 'en'], true) ? get('dir') : 'all';
    $cat = isset(WORD_CATS[get('cat')]) ? get('cat') : '';
    $letter = preg_match('/^[a-z]$/i', get('letter')) ? strtolower(get('letter')) : '';
    $browsing = $qs === '' && $cat === '';
    if ($browsing && $letter === '') $letter = 'a';

    $where = ["status='published'"]; $par = [];
    if ($qs !== '') {
        $l = '%' . like_escape($qs) . '%'; $f = '%' . like_escape(lango_fold($qs)) . '%';
        if ($dir === 'lg') { $where[] = '(lango LIKE ? OR fold LIKE ?)'; array_push($par, $l, $f); }
        elseif ($dir === 'en') { $where[] = 'english LIKE ?'; $par[] = $l; }
        else { $where[] = '(lango LIKE ? OR fold LIKE ? OR english LIKE ?)'; array_push($par, $l, $f, $l); }
    } elseif ($letter !== '' && $browsing) { $where[] = 'fold LIKE ?'; $par[] = $letter . '%'; }
    if ($cat !== '') { $where[] = 'category=?'; $par[] = $cat; }
    $w = implode(' AND ', $where);
    $order = $qs !== '' ? 'ORDER BY (lango=?) DESC, (fold=?) DESC, (english=?) DESC, (fold LIKE ?) DESC, CHAR_LENGTH(lango), lango' : 'ORDER BY fold, lango';
    $ordPar = $qs !== '' ? [$qs, lango_fold($qs), $qs, like_escape(lango_fold($qs)) . '%'] : [];
    $total = (int)val("SELECT COUNT(*) FROM dictionary WHERE $w", $par);
    $pg = paginate($total, 24);
    $rows = all("SELECT * FROM dictionary WHERE $w $order LIMIT {$pg['per']} OFFSET {$pg['offset']}", array_merge($par, $ordPar));
    $cats = all("SELECT category, COUNT(*) n FROM dictionary WHERE status='published' AND category IS NOT NULL AND category<>'' GROUP BY category ORDER BY n DESC");
    $wotd = word_of_day();

    page_start(__('Lango–English Dictionary'), ['hero' => ['eyebrow' => __('Language & translation'), 'title' => __('Lango–English Dictionary'), 'sub' => sprintf(__('%s words and growing. Search in Leb Lango or English.'), number_format($wordsTotal)), 'crumbs' => [[__('Language'), 'language'], [__('Dictionary'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
    ?>
    <section class="section section--tight"><div class="container">
      <form class="dsearch" method="get" action="<?= e(url('language/dictionary')) ?>" role="search">
        <div class="dsearch__row">
          <?= icon('search') ?><input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search a word, e.g. yoo, dano, water or path')) ?>" aria-label="<?= e(__('Search the dictionary')) ?>" autocomplete="off" maxlength="80">
          <button class="btn btn--primary"><?php _e('Search'); ?></button>
        </div>
        <div class="dsearch__opts">
          <span class="seg" role="group" aria-label="<?= e(__('Search in')) ?>">
            <label><input type="radio" name="dir" value="all" <?= $dir === 'all' ? 'checked' : '' ?>><span><?php _e('Both'); ?></span></label>
            <label><input type="radio" name="dir" value="lg" <?= $dir === 'lg' ? 'checked' : '' ?>><span>Leb Lango</span></label>
            <label><input type="radio" name="dir" value="en" <?= $dir === 'en' ? 'checked' : '' ?>><span>English</span></label>
          </span>
          <a class="dsearch__tr" href="<?= e(url('language/translate')) ?>"><?= icon('globe') ?> <?php _e('Translate whole sentences with the AI translator'); ?> <?= icon('arrow') ?></a>
        </div>
      </form>
    </div></section>

    <section class="section section--tight" style="padding-top:8px"><div class="container article">
      <div>
        <div class="alpha" aria-label="<?= e(__('Browse by letter')) ?>">
          <?php foreach (range('a', 'z') as $ch): ?><a class="<?= $browsing && $letter === $ch ? 'on' : '' ?>" href="<?= e(url('language/dictionary', ['letter' => $ch])) ?>"><?= strtoupper($ch) ?></a><?php endforeach; ?>
        </div>
        <?php if ($cats): ?>
        <div class="filters" style="margin-bottom:18px">
          <a class="pill <?= $cat === '' ? 'on' : '' ?>" href="<?= e(url('language/dictionary')) ?>"><?php _e('All words'); ?></a>
          <?php foreach ($cats as $c): if (!isset(WORD_CATS[$c['category']])) continue; ?><a class="pill <?= $cat === $c['category'] ? 'on' : '' ?>" href="<?= e(url('language/dictionary', ['cat' => $c['category']])) ?>"><?= e(__(WORD_CATS[$c['category']])) ?> <small><?= (int)$c['n'] ?></small></a><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <p class="dict__count"><?php
          if ($qs !== '') echo sprintf(__('%s results for “%s”'), '<b>' . number_format($total) . '</b>', e($qs));
          elseif ($cat !== '') echo sprintf(__('%s words in %s'), '<b>' . number_format($total) . '</b>', e(__(WORD_CATS[$cat])));
          else echo sprintf(__('%s words beginning with %s'), '<b>' . number_format($total) . '</b>', '<b>' . strtoupper($letter) . '</b>'); ?></p>
        <?php if ($rows): ?>
          <div class="entries"><?php foreach ($rows as $r) echo $entry($r); ?></div>
          <?= pager($pg) ?>
        <?php else: ?>
          <?= empty_state(__('No entry found'), __('This word may not be in the dictionary yet. Try the translator, or suggest it and our language experts will review it.'), 'book',
              '<p style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap"><a class="btn btn--primary btn--sm" href="' . e(url('contribute', ['type' => 'language', 'word' => $qs])) . '">' . e(__('Suggest this word')) . '</a><a class="btn btn--outline btn--sm" href="' . e(url('language/translate', ['text' => $qs, 'dir' => 'lango-en'])) . '">' . e(__('Try the translator')) . '</a></p>') ?>
        <?php endif; ?>
        <p class="dict__note"><?php _e('“Checked” means an LCI language expert has confirmed the entry. Unmarked entries come from the original Lango dictionary project and are still being reviewed.'); ?></p>
      </div>
      <aside class="aside">
        <?php if ($wotd): ?><div class="wotd"><small><?php _e('Word of the day'); ?></small><h3><?= e($wotd['lango']) ?></h3><p style="color:#fff;margin:0"><?= e(str_replace(';', ' ·', $wotd['english'])) ?></p></div><?php endif; ?>
        <?= $suggestCard() ?>
        <div class="box"><h4><?= icon('globe') ?> <?php _e('AI Lango translator'); ?></h4><p style="font-size:.9rem"><?php _e('Type a phrase in English or Leb Lango and get a translation built from the dictionary and textbook sentences.'); ?></p><a class="btn btn--dark btn--sm" href="<?= e(url('language/translate')) ?>"><?php _e('Open the translator'); ?></a></div>
        <div class="box"><dl class="kv" style="grid-template-columns:1fr auto"><dt><?php _e('Words in the dictionary'); ?></dt><dd><b><?= number_format($wordsTotal) ?></b></dd><dt><?php _e('Added by the community'); ?></dt><dd><b><?= number_format($communityWords) ?></b></dd></dl></div>
      </aside>
    </div></section>
    <?php page_end(); return;
}

/* =============================== translator =============================== */
if ($sub === 'translate') {
    $text = mb_substr(trim((string)($_GET['text'] ?? '')), 0, 600);
    $dir = get('dir') === 'lango-en' ? 'lango-en' : 'en-lango';
    $res = ($text !== '' && throttle('translate', 60, 600)) ? lango_translate($text, $dir) : null;
    $greet = array_slice(require ROOT . '/app/data/greetings.php', 0, 6);
    page_start(__('AI Lango Translator'), ['hero' => ['eyebrow' => __('Language & translation'), 'title' => __('AI Lango translator'), 'sub' => __('Translate between English and Leb Lango using LCI’s dictionary and textbook sentences.'), 'crumbs' => [[__('Language'), 'language'], [__('Translator'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
    ?>
    <section class="section section--tight"><div class="container">
      <form class="trans" id="transform" method="get" action="<?= e(url('language/translate')) ?>">
        <div class="trans__bar">
          <span class="seg" role="group" aria-label="<?= e(__('Direction')) ?>">
            <label><input type="radio" name="dir" value="en-lango" <?= $dir === 'en-lango' ? 'checked' : '' ?>><span>English → Leb Lango</span></label>
            <label><input type="radio" name="dir" value="lango-en" <?= $dir === 'lango-en' ? 'checked' : '' ?>><span>Leb Lango → English</span></label>
          </span>
          <span class="trans__hint"><?php _e('Letters like ε, ɔ and ŋ are optional — type e, o and ng.'); ?></span>
        </div>
        <div class="trans__panes">
          <div class="trans__pane"><label for="tx-in" id="tx-in-label"><?= $dir === 'en-lango' ? e(__('English')) : 'Leb Lango' ?></label>
            <textarea id="tx-in" name="text" rows="6" maxlength="600" placeholder="<?= e(__('Type a word, phrase or sentence…')) ?>"><?= e($text) ?></textarea></div>
          <div class="trans__pane trans__pane--out"><label id="tx-out-label"><?= $dir === 'en-lango' ? 'Leb Lango' : e(__('English')) ?></label>
            <div class="trans__out" id="tx-out" aria-live="polite"><?= $res ? e($res['translation']) : '<span class="trans__ph">' . e(__('The translation appears here.')) . '</span>' ?></div>
            <div class="trans__unk" id="tx-unk"><?php if ($res && $res['unknown_words']): ?><?php _e('Not in the dictionary yet:'); ?> <?php foreach ($res['unknown_words'] as $uw): ?><a href="<?= e(url('contribute', ['type' => 'language', 'word' => $uw])) ?>"><?= e($uw) ?></a> <?php endforeach; ?><?php endif; ?></div></div>
        </div>
        <div class="trans__foot"><button class="btn btn--primary" id="tx-go"><?= icon('globe') ?> <?php _e('Translate'); ?></button><a class="btn btn--outline" href="<?= e(url('language/translate')) ?>"><?php _e('Clear'); ?></a></div>
      </form>
    </div></section>
    <section class="section section--tight" style="padding-top:8px"><div class="container article">
      <div>
        <h2 style="font-size:1.35rem"><?php _e('Try a greeting'); ?></h2>
        <div class="sugg trans__sugg" id="tx-sugg"><?php foreach ($greet as [$lg, $en]): ?><button type="button" data-text="<?= e($lg) ?>" data-dir="lango-en"><?= e($lg) ?></button><?php endforeach; ?></div>
        <h2 style="font-size:1.35rem;margin-top:30px"><?php _e('How the translator works'); ?></h2>
        <div class="prose" style="font-size:.95rem;line-height:1.75">
          <p><?php _e('This is a dictionary-based translator, not a full machine-translation model. It first looks for the whole sentence among the worked examples of the Practical Lango Grammar, then matches the longest phrases in the dictionary, and finally translates word by word. Words it does not know are left as they are and listed under the result.'); ?></p>
          <p><?php _e('Leb Lango grammar is rich — verbs carry the subject as a prefix and tone changes meaning — so treat longer sentences as a starting point and ask a fluent speaker to check them. Every word our community adds to the dictionary is available here as soon as it is approved.'); ?></p>
        </div>
      </div>
      <aside class="aside">
        <?= $suggestCard() ?>
        <div class="box"><h4><?= icon('book') ?> <?php _e('Look up a single word'); ?></h4><p style="font-size:.9rem"><?php _e('The dictionary shows every meaning, with examples where we have them.'); ?></p><a class="btn btn--outline btn--sm" href="<?= e(url('language/dictionary')) ?>"><?php _e('Open the dictionary'); ?></a></div>
      </aside>
    </div></section>
    <?php page_end(); return;
}

/* ================================ proverbs ================================ */
if ($sub === 'proverbs') {
    $qs = mb_substr(get('q'), 0, 80);
    $where = ["p.status='published'"]; $par = [];
    if ($qs !== '') { $where[] = '(p.lango LIKE ? OR p.english LIKE ? OR p.meaning LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
    $rows = all('SELECT p.*, c.name clan FROM proverbs p LEFT JOIN clans c ON c.id=p.clan_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.id DESC', $par);
    $parable = one("SELECT lango, english FROM dictionary WHERE lango='carokop' LIMIT 1");
    page_start(__('Proverbs & Sayings'), ['hero' => ['eyebrow' => __('Language & translation'), 'title' => __('Proverbs & sayings'), 'sub' => __('The wisdom of the elders, with translations and meanings.'), 'crumbs' => [[__('Language'), 'language'], [__('Proverbs'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
    ?>
    <section class="section"><div class="container">
      <?php if ($rows || $qs !== ''): ?>
        <form class="searchbar" method="get"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search proverbs…')) ?>" aria-label="<?= e(__('Search')) ?>"><button class="btn btn--primary"><?= icon('search') ?></button></form>
        <div class="grid g3" style="margin-top:34px">
          <?php foreach ($rows as $r): ?><div class="proverb reveal"><p><?= e($r['lango']) ?></p>
            <?php if ($r['english']): ?><small><b><?php _e('In English'); ?>:</b> <?= e($r['english']) ?></small><?php endif; ?>
            <?php if ($r['meaning']): ?><small style="margin-top:6px"><b><?php _e('Meaning'); ?>:</b> <?= e($r['meaning']) ?></small><?php endif; ?>
            <small class="proverb__by"><?= $r['verified'] ? icon('check') . ' ' . e(__('Checked')) . ' · ' : '' ?><?= e((string)($r['contributor'] ?: ($r['clan'] ? $r['clan'] . ' ' . __('clan') : ''))) ?></small></div><?php endforeach; ?>
        </div>
        <?php if (!$rows) echo empty_state(__('No proverbs match your search'), __('Try another word, or add the proverb yourself.'), 'chat'); ?>
        <p style="text-align:center;margin-top:34px"><a class="btn btn--primary" href="<?= e(url('contribute', ['type' => 'language', 'kind' => 'proverb'])) ?>"><?= icon('plus') ?> <?php _e('Add a proverb'); ?></a></p>
      <?php else: ?>
        <div class="begin">
          <div class="begin__q" aria-hidden="true">“</div>
          <div>
            <span class="eyebrow"><?php _e('A collection waiting for its elders'); ?></span>
            <h2><?php _e('Help us write down the proverbs of Lango'); ?></h2>
            <p><?php _e('Proverbs are how the Lango teach children, settle disputes and speak about life. We would rather publish nothing than publish a proverb that is wrong, so this page only shows sayings that come from Lango speakers and have been reviewed by the LCI language team.'); ?></p>
            <?php if ($parable): ?><p class="begin__note"><?= icon('book') ?> <?= sprintf(__('In the dictionary, a proverb or parable is %s.'), '<b>' . e($parable['lango']) . '</b>') ?></p><?php endif; ?>
            <ol class="begin__steps"><li><?php _e('Write the proverb in Leb Lango, exactly as you heard it.'); ?></li><li><?php _e('Add its English meaning and when it is used.'); ?></li><li><?php _e('Choose your clan so the saying is credited to where it comes from.'); ?></li></ol>
            <p style="display:flex;gap:10px;flex-wrap:wrap;margin:0"><a class="btn btn--primary" href="<?= e(url('contribute', ['type' => 'language', 'kind' => 'proverb'])) ?>"><?= icon('plus') ?> <?php _e('Add a proverb'); ?></a><a class="btn btn--outline" href="<?= e(url('language/dictionary')) ?>"><?php _e('Browse the dictionary'); ?></a></p>
          </div>
        </div>
      <?php endif; ?>
    </div></section>
    <?php page_end(); return;
}

/* ================================= lessons ================================= */
if ($sub === 'lessons') {
    $les = all("SELECT * FROM lessons WHERE status='published' ORDER BY FIELD(level,'beginner','intermediate','advanced'), sort, id");
    $lesson = !empty($seg[2]) ? one("SELECT * FROM lessons WHERE id=? AND status='published'", [(int)$seg[2]]) : null;
    $greet = require ROOT . '/app/data/greetings.php';
    page_start(__('Learn Leb Lango'), ['hero' => ['eyebrow' => __('Language & translation'), 'title' => __('Learn Leb Lango'), 'sub' => __('Greetings and lessons for the diaspora, for youth and for everyone who wants to speak with their elders.'), 'crumbs' => [[__('Language'), 'language'], [__('Lessons'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:920px">
      <?php if ($lesson): ?>
        <a href="<?= e(url('language/lessons')) ?>">← <?php _e('All lessons'); ?></a><h2 style="margin-top:14px"><?= e($lesson['title']) ?></h2><div class="prose"><?= rich($lesson['body']) ?></div><?php if ($lesson['audio']): ?><audio controls style="width:100%" src="<?= e(media_url($lesson['audio'])) ?>"></audio><?php endif; ?>
      <?php else: ?>
        <?php if ($les): ?><h2 style="font-size:1.4rem"><?php _e('Lessons'); ?></h2><div class="grid" style="margin-bottom:40px"><?php foreach ($les as $l): ?><a class="evrow" href="<?= e(url('language/lessons/' . $l['id'])) ?>"><span class="tag"><?= e($l['level']) ?></span><div><h4><?= e($l['title']) ?></h4></div></a><?php endforeach; ?></div><?php endif; ?>
        <h2 style="font-size:1.4rem"><?php _e('Everyday greetings'); ?></h2>
        <p style="color:var(--muted)"><?php _e('Real greetings and replies from the worked examples of the Practical Lango Grammar. Spelling follows the book.'); ?></p>
        <div class="greet"><?php foreach ($greet as [$lg, $en]): ?><div class="greet__row"><b><?= e($lg) ?></b><span><?= e($en) ?></span></div><?php endforeach; ?></div>
        <?php if (!$les): ?><p style="margin-top:26px"><?php _e('More lessons are being prepared by our language experts.'); ?> <a href="<?= e(url('language/dictionary')) ?>"><?php _e('Meanwhile, explore the dictionary.'); ?></a></p><?php endif; ?>
      <?php endif; ?>
    </div></section>
    <?php page_end(); return;
}
if ($sub !== '') not_found();

/* ================================ language hub ================================ */
$wotd = word_of_day();
$np = (int)val("SELECT COUNT(*) FROM proverbs WHERE status='published'");
$cats = all("SELECT category, COUNT(*) n FROM dictionary WHERE status='published' AND category IS NOT NULL AND category<>'' GROUP BY category ORDER BY n DESC LIMIT 8");
$greet = array_slice(require ROOT . '/app/data/greetings.php', 0, 6);
page_start(__('Language'), ['hero' => ['eyebrow' => __('Leb Lango'), 'title' => __('Our language, our identity'), 'sub' => __('A living dictionary, an AI translator, proverbs and lessons — and a way for every Lango speaker to help keep Leb Lango alive.'), 'crumbs' => [[__('Language'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section"><div class="container">
  <div class="lhub">
    <div class="lhub__card">
      <span class="lhub__ico"><?= icon('book') ?></span>
      <h2><?php _e('Dictionary'); ?></h2>
      <p><?= sprintf(__('%s words, with meanings, examples and pronunciation. Find a word in Leb Lango or English.'), number_format($wordsTotal)) ?></p>
      <form class="lhub__search" action="<?= e(url('language/dictionary')) ?>" role="search"><input type="search" name="q" placeholder="<?= e(__('Search a word…')) ?>" aria-label="<?= e(__('Search the dictionary')) ?>"><button class="btn btn--primary"><?= icon('search') ?></button></form>
      <a class="link" href="<?= e(url('language/dictionary')) ?>"><?php _e('Browse A–Z'); ?> <?= icon('arrow') ?></a>
    </div>
    <div class="lhub__card lhub__card--dark">
      <span class="lhub__ico"><?= icon('globe') ?></span>
      <h2><?php _e('AI Lango translator'); ?></h2>
      <p><?php _e('Translate phrases and sentences between English and Leb Lango, built from the dictionary and textbook examples.'); ?></p>
      <form class="lhub__search" action="<?= e(url('language/translate')) ?>"><input type="text" name="text" placeholder="<?= e(__('Type something to translate…')) ?>" aria-label="<?= e(__('Text to translate')) ?>" maxlength="600"><button class="btn btn--gold"><?php _e('Translate'); ?></button></form>
      <a class="link" href="<?= e(url('language/translate')) ?>"><?php _e('Open the translator'); ?> <?= icon('arrow') ?></a>
    </div>
  </div>

  <div class="grid g4" style="margin-top:22px">
    <a class="tile" href="<?= e(url('language/proverbs')) ?>"><span class="ico"><?= icon('chat') ?></span><h3><?php _e('Proverbs & sayings'); ?></h3><p><?= $np ? sprintf(__('%d proverbs'), $np) : __('Help us start the collection') ?></p></a>
    <a class="tile" href="<?= e(url('language/lessons')) ?>"><span class="ico"><?= icon('star') ?></span><h3><?php _e('Learn Leb Lango'); ?></h3><p><?php _e('Greetings and beginner lessons'); ?></p></a>
    <a class="tile" href="<?= e(url('contribute', ['type' => 'language'])) ?>"><span class="ico"><?= icon('plus') ?></span><h3><?php _e('Suggest a word'); ?></h3><p><?php _e('Animals, places, names and more'); ?></p></a>
    <a class="tile" href="<?= e(url('assistant')) ?>"><span class="ico"><?= icon('mic') ?></span><h3><?php _e('Ask the assistant'); ?></h3><p><?php _e('Questions about Lango life and language'); ?></p></a>
  </div>
</div></section>

<section class="section section--cream"><div class="container split" style="align-items:start">
  <div>
    <div class="eyebrow"><?php _e('Start speaking'); ?></div>
    <h2><?php _e('Everyday greetings'); ?></h2>
    <div class="greet"><?php foreach ($greet as [$lg, $en]): ?><div class="greet__row"><b><?= e($lg) ?></b><span><?= e($en) ?></span></div><?php endforeach; ?></div>
    <p style="margin-top:14px"><a class="link" href="<?= e(url('language/lessons')) ?>"><?php _e('More greetings and lessons'); ?> <?= icon('arrow') ?></a></p>
  </div>
  <div style="display:grid;gap:20px">
    <?php if ($wotd): ?><div class="wotd"><small><?php _e('Word of the day'); ?></small><h3><?= e($wotd['lango']) ?></h3><p style="color:#fff;margin:0;font-size:1.1rem"><?= e(str_replace(';', ' ·', $wotd['english'])) ?></p></div><?php endif; ?>
    <?php if ($cats): ?><div class="box"><h4><?php _e('Browse by topic'); ?></h4><div class="filters" style="margin:0"><?php foreach ($cats as $c): if (!isset(WORD_CATS[$c['category']])) continue; ?><a class="pill" href="<?= e(url('language/dictionary', ['cat' => $c['category']])) ?>"><?= e(__(WORD_CATS[$c['category']])) ?> <small><?= (int)$c['n'] ?></small></a><?php endforeach; ?></div></div><?php endif; ?>
    <?= $suggestCard() ?>
  </div>
</div></section>
<?php page_end(); ?>
