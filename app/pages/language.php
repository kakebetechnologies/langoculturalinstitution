<?php
declare(strict_types=1);

$sub = $seg[1] ?? '';

/* ---------- dictionary ---------- */
if ($sub === 'dictionary') {
    $qs = get('q'); $letter = mb_strtolower(mb_substr(get('letter'), 0, 1));
    $dir = get('dir', 'all'); // all | lg | en
    $where = ["status='published'"]; $par = [];
    if ($qs !== '') {
        $l = '%' . like_escape($qs) . '%';
        if ($dir === 'lg') { $where[] = 'lango LIKE ?'; $par[] = $l; }
        elseif ($dir === 'en') { $where[] = 'english LIKE ?'; $par[] = $l; }
        else { $where[] = '(lango LIKE ? OR english LIKE ?)'; array_push($par, $l, $l); }
    } elseif ($letter !== '') { $where[] = 'lango LIKE ?'; $par[] = like_escape($letter) . '%'; }
    $w = implode(' AND ', $where);
    $order = $qs !== '' ? 'ORDER BY (lango=?) DESC, (english=?) DESC, CHAR_LENGTH(lango), lango' : 'ORDER BY lango';
    $ordPar = $qs !== '' ? [$qs, $qs] : [];
    $total = (int)val("SELECT COUNT(*) FROM dictionary WHERE $w", $par);
    $pg = paginate($total, 30);
    $rows = all("SELECT * FROM dictionary WHERE $w $order LIMIT {$pg['per']} OFFSET {$pg['offset']}", array_merge($par, $ordPar));
    $all = (int)val("SELECT COUNT(*) FROM dictionary WHERE status='published'");

    page_start(__('Lango–English Dictionary'), ['hero' => ['eyebrow' => __('Language & translation'), 'title' => __('Lango–English Dictionary'), 'sub' => sprintf(__('%s entries and growing — search in Leb Lango or English.'), number_format($all)), 'crumbs' => [[__('Language'), 'language'], [__('Dictionary'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:980px">
      <form class="searchbar" method="get" action="<?= e(url('language/dictionary')) ?>">
        <input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search a word… e.g. yoo or path')) ?>" aria-label="<?= e(__('Search')) ?>" autofocus>
        <select name="dir" style="width:auto;border:0;background:var(--sand);border-radius:999px" aria-label="Direction">
          <option value="all" <?= $dir === 'all' ? 'selected' : '' ?>><?php _e('Both'); ?></option><option value="lg" <?= $dir === 'lg' ? 'selected' : '' ?>>Leb Lango</option><option value="en" <?= $dir === 'en' ? 'selected' : '' ?>>English</option>
        </select>
        <button class="btn btn--primary"><?= icon('search') ?></button>
      </form>
      <div class="filters" style="justify-content:center;margin:26px 0 30px;gap:6px">
        <?php foreach (range('a', 'z') as $ch): ?><a class="pill <?= $letter === $ch ? 'on' : '' ?>" style="padding:5px 11px" href="<?= e(url('language/dictionary', ['letter' => $ch])) ?>"><?= strtoupper($ch) ?></a><?php endforeach; ?>
      </div>
      <?php if ($qs !== '' || $letter !== ''): ?><p style="color:var(--muted)"><?= number_format($total) ?> <?php _e('results'); ?></p><?php endif; ?>
      <?php if ($rows): ?>
        <div class="grid" style="gap:12px">
          <?php foreach ($rows as $r): ?>
            <div class="word"><div style="flex:1"><h3><?= e($r['lango']) ?> <?php if ($r['pos']): ?><span class="pos"><?= e($r['pos']) ?></span><?php endif; ?></h3><p><?= e($r['english']) ?></p>
              <?php if ($r['example_lg']): ?><p style="font-style:italic;margin-top:6px"><?= e($r['example_lg']) ?><?= $r['example_en'] ? ' — ' . e($r['example_en']) : '' ?></p><?php endif; ?></div>
              <?php if ($r['audio']): ?><audio controls preload="none" src="<?= e(media_url($r['audio'])) ?>" style="height:34px;max-width:170px"></audio><?php endif; ?>
              <?= $r['verified'] ? '<span class="tag tag--green">' . e(__('Verified')) . '</span>' : '<span class="tag">' . e(__('Draft')) . '</span>' ?></div>
          <?php endforeach; ?>
        </div>
        <?= pager($pg) ?>
      <?php elseif ($qs !== '' || $letter !== ''): ?>
        <?= empty_state(__('No entry found'), __('This word may not be in the dictionary yet. Know it? Suggest it and our language experts will review it.'), 'book', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute?type=language')) . '">' . e(__('Suggest a word')) . '</a></p>') ?>
      <?php else: ?>
        <p style="text-align:center;color:var(--muted)"><?php _e('Type a word above, or pick a letter to browse.'); ?></p>
      <?php endif; ?>
      <p style="margin-top:34px;font-size:.85rem;color:var(--muted);text-align:center"><?php _e('Entries marked “Draft” were imported from LCI’s language project and are awaiting expert verification.'); ?></p>
    </div></section>
    <?php page_end(); return;
}

/* ---------- proverbs ---------- */
if ($sub === 'proverbs') {
    $qs = get('q');
    $where = ["status='published'"]; $par = [];
    if ($qs !== '') { $where[] = '(lango LIKE ? OR english LIKE ? OR meaning LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
    $rows = all('SELECT * FROM proverbs WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC', $par);
    page_start(__('Proverbs & Idioms'), ['hero' => ['eyebrow' => __('Language & translation'), 'title' => __('Proverbs & idioms'), 'sub' => __('The wisdom of the elders, with meanings and translations.'), 'crumbs' => [[__('Language'), 'language'], [__('Proverbs'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:980px">
      <form class="searchbar" method="get"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search proverbs…')) ?>" aria-label="Search"><button class="btn btn--primary"><?= icon('search') ?></button></form>
      <div class="grid" style="margin-top:36px">
        <?php foreach ($rows as $r): ?><div class="proverb reveal"><p><?= e($r['lango']) ?></p><?php if ($r['english']): ?><small><b><?php _e('In English'); ?>:</b> <?= e($r['english']) ?></small><?php endif; ?><?php if ($r['meaning']): ?><small style="margin-top:6px"><b><?php _e('Meaning'); ?>:</b> <?= e($r['meaning']) ?></small><?php endif; ?></div><?php endforeach; ?>
      </div>
      <?php if (!$rows): echo empty_state(__('The proverb collection is just beginning'), __('Elders and Lango speakers: if you know a proverb that is not yet written down, add it here with its meaning.'), 'book', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute?type=language')) . '">' . e(__('Contribute a proverb')) . '</a></p>'); endif; ?>
    </div></section>
    <?php page_end(); return;
}

/* ---------- lessons ---------- */
if ($sub === 'lessons') {
    $les = all("SELECT * FROM lessons WHERE status='published' ORDER BY FIELD(level,'beginner','intermediate','advanced'), sort, id");
    if (!empty($seg[2])) $one = one("SELECT * FROM lessons WHERE id=? AND status='published'", [(int)$seg[2]]);
    page_start(__('Learn Leb Lango'), ['hero' => ['eyebrow' => __('Language & translation'), 'title' => __('Learn Leb Lango'), 'sub' => __('Lessons for the diaspora, for youth and for everyone who wants to speak with their elders.'), 'crumbs' => [[__('Language'), 'language'], [__('Lessons'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:900px">
      <?php if (!empty($one)): ?><a href="<?= e(url('language/lessons')) ?>">← <?php _e('All lessons'); ?></a><h2 style="margin-top:14px"><?= e($one['title']) ?></h2><div class="prose"><?= rich($one['body']) ?></div><?php if ($one['audio']): ?><audio controls style="width:100%" src="<?= e(media_url($one['audio'])) ?>"></audio><?php endif; ?>
      <?php elseif ($les): ?><div class="grid"><?php foreach ($les as $l): ?><a class="evrow" href="<?= e(url('language/lessons/' . $l['id'])) ?>"><span class="tag"><?= e($l['level']) ?></span><div><h4><?= e($l['title']) ?></h4></div></a><?php endforeach; ?></div>
      <?php else: echo empty_state(__('Lessons are being prepared'), __('Our language experts are preparing the first beginner lessons. Meanwhile, explore the dictionary and the word of the day.'), 'book', '<p><a class="btn btn--primary btn--sm" href="' . e(url('language/dictionary')) . '">' . e(__('Open the dictionary')) . '</a></p>'); endif; ?>
    </div></section>
    <?php page_end(); return;
}
if ($sub !== '') not_found();

/* ---------- language hub ---------- */
$wotd = word_of_day();
$n = (int)val("SELECT COUNT(*) FROM dictionary WHERE status='published'");
$np = (int)val("SELECT COUNT(*) FROM proverbs WHERE status='published'");
page_start(__('Language'), ['hero' => ['eyebrow' => __('Leb Lango'), 'title' => __('Our language, our identity'), 'sub' => __('A living dictionary, proverbs, lessons and a way for every Lango speaker to help preserve Leb Lango.'), 'crumbs' => [[__('Language'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section"><div class="container">
  <form class="searchbar" action="<?= e(url('language/dictionary')) ?>"><input type="search" name="q" placeholder="<?= e(__('Search the dictionary…')) ?>" aria-label="Search"><button class="btn btn--primary"><?= icon('search') ?> <?php _e('Search'); ?></button></form>
  <div class="split" style="margin-top:56px;align-items:start">
    <?php if ($wotd): ?><div class="wotd reveal"><small><?php _e('Word of the day'); ?></small><h3><?= e($wotd['lango']) ?></h3><p style="font-size:1.2rem"><?= e($wotd['english']) ?></p></div><?php endif; ?>
    <div class="grid reveal">
      <a class="tile" href="<?= e(url('language/dictionary')) ?>"><span class="ico"><?= icon('book') ?></span><h3><?php _e('Dictionary'); ?></h3><p><?= number_format($n) ?> <?php _e('entries'); ?></p></a>
      <a class="tile" href="<?= e(url('language/proverbs')) ?>"><span class="ico"><?= icon('chat') ?></span><h3><?php _e('Proverbs & idioms'); ?></h3><p><?= $np ?> <?php _e('proverbs'); ?></p></a>
      <a class="tile" href="<?= e(url('language/lessons')) ?>"><span class="ico"><?= icon('star') ?></span><h3><?php _e('Learn Leb Lango'); ?></h3><p><?php _e('Beginner lessons'); ?></p></a>
      <a class="tile" href="<?= e(url('contribute?type=language')) ?>"><span class="ico"><?= icon('mic') ?></span><h3><?php _e('Suggest a word'); ?></h3><p><?php _e('Add words, pronunciations, proverbs'); ?></p></a>
    </div>
  </div>
</div></section>
<?php page_end(); ?>
