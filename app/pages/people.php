<?php
declare(strict_types=1);

$all = all("SELECT n.*, d.name district, d.slug district_slug FROM notables n LEFT JOIN districts d ON d.id=n.district_id WHERE n.status='published' ORDER BY n.featured DESC, n.sort, n.name");

/* ============================== /people/{slug} ============================== */
if (!empty($seg[1])) {
    $idx = null;
    foreach ($all as $i => $r) if ($r['slug'] === $seg[1]) { $idx = $i; break; }
    if ($idx === null) not_found();
    $n = $all[$idx];
    $others = array_values(array_filter($all, fn($r) => $r['id'] !== $n['id']));
    usort($others, fn($a, $b) => [$b['category'] === $n['category'], $a['sort']] <=> [$a['category'] === $n['category'], $b['sort']]);
    $years = $n['born'] ? $n['born'] . ($n['died'] ? ' – ' . $n['died'] : ' – ' . __('present')) : '';
    $words = array_values(array_filter(preg_split('/[^\p{L}]+/u', (string)preg_replace('/^(Maj\.?\s*Gen\.?|Gen\.?|Dr\.?|Rwot)\s+/i', '', (string)($n['short_name'] ?: $n['name'])))));
    $ini = mb_strtoupper(count($words) > 1 ? mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1) : mb_substr($words[0] ?? '?', 0, 2));
    page_start($n['name'], ['image' => $n['photo'], 'desc' => $n['summary'], 'hero' => ['eyebrow' => __(PEOPLE_CATS[$n['category']] ?? 'Notable Lango'), 'title' => $n['name'], 'sub' => (string)$n['title'], 'crumbs' => [[__('About'), 'about'], [__('Notable Langos'), 'people'], [$n['short_name'] ?: $n['name'], null]], 'image' => 'assets/img/photos/hero-ridge.jpg']]);
    ?>
    <section class="section"><div class="container article">
      <div>
        <div class="pbio">
          <div class="pbio__ph"><?= $n['photo'] ? '<img src="' . e(media_url($n['photo'])) . '" alt="' . e($n['name']) . '">' : '<span class="ncard__mono m' . ((int)$n['id'] % 4) . '" aria-hidden="true">' . e($ini) . '</span>' ?></div>
          <div><?php if ($years): ?><p class="pbio__y"><?= e($years) ?></p><?php endif; ?><p class="pbio__lead"><?= e((string)$n['summary']) ?></p>
            <?php if (!$n['photo']): ?><p class="pbio__note"><?php _e('A portrait will be added once a properly licensed photograph is confirmed.'); ?></p><?php endif; ?></div>
        </div>
        <div class="prose"><?= rich($n['body']) ?></div>
        <div class="pn" style="margin-top:36px"><?php $pv = $all[($idx - 1 + count($all)) % count($all)]; $nx = $all[($idx + 1) % count($all)]; ?>
          <a href="<?= e(url('people/' . $pv['slug'])) ?>"><small>← <?php _e('Previous'); ?></small><b><?= e($pv['short_name'] ?: $pv['name']) ?></b></a><a class="pn__r" href="<?= e(url('people/' . $nx['slug'])) ?>"><small><?php _e('Next'); ?> →</small><b><?= e($nx['short_name'] ?: $nx['name']) ?></b></a></div>
      </div>
      <aside class="aside">
        <div class="box"><h4><?php _e('At a glance'); ?></h4><dl class="kv" style="grid-template-columns:90px 1fr">
          <?php if ($n['born']): ?><dt><?php _e('Born'); ?></dt><dd><?= e($n['born']) ?></dd><?php endif; ?>
          <?php if ($n['died']): ?><dt><?php _e('Died'); ?></dt><dd><?= e($n['died']) ?></dd><?php endif; ?>
          <dt><?php _e('Known as'); ?></dt><dd><?= e((string)$n['title']) ?></dd>
          <?php if ($n['place']): ?><dt><?php _e('From'); ?></dt><dd><?= e($n['place']) ?></dd><?php endif; ?>
          <?php if ($n['district']): ?><dt><?php _e('District'); ?></dt><dd><a href="<?= e(url('districts/' . $n['district_slug'])) ?>"><?= e($n['district']) ?></a></dd><?php endif; ?></dl></div>
        <div class="box"><h4><?php _e('Meet more Langos'); ?></h4><?php foreach (array_slice($others, 0, 4) as $o): ?><p style="margin:7px 0"><a href="<?= e(url('people/' . $o['slug'])) ?>"><b><?= e($o['short_name'] ?: $o['name']) ?></b></a><br><small class="dim"><?= e((string)$o['title']) ?></small></p><?php endforeach; ?><a class="link" href="<?= e(url('people')) ?>"><?php _e('All notable Langos'); ?> <?= icon('arrow') ?></a></div>
        <div class="box box--dark"><h4><?php _e('Know more about them?'); ?></h4><p><?php _e('Families, elders and historians can add stories, dates and photographs.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('contribute', ['type' => 'oral_history'])) ?>"><?php _e('Share what you know'); ?></a></div>
      </aside>
    </div></section>
    <?php page_end(); return;
}

/* ================================== /people ================================== */
$cat = get('category');
$cats = array_count_values(array_column($all, 'category'));
$feat = array_values(array_filter($all, fn($r) => $r['featured']));
$list = array_values(array_filter($all, fn($r) => $cat === '' ? true : $r['category'] === $cat));
if ($cat === '') $list = array_values(array_filter($list, fn($r) => !$r['featured']));
page_start(__('Notable Langos'), ['hero' => ['eyebrow' => __('People who shaped Lango'), 'title' => __('Notable Langos'), 'sub' => __('Statesmen, soldiers, champions, chiefs and public servants — the stories every young Lango should know.'), 'crumbs' => [[__('About'), 'about'], [__('Notable Langos'), null]], 'image' => 'assets/img/photos/hero-ridge.jpg']]);
?>
<section class="section"><div class="container">
  <div class="sec-head" style="max-width:760px"><span class="eyebrow"><?php _e('Why this page exists'); ?></span><h2><?php _e('Know where you come from'); ?></h2>
    <p><?php _e('Lango men and women have led a nation, won Olympic gold, commanded armies, healed patients and kept the peace in their villages. Their stories belong to every child in Lango. We write them from published sources, describe contested lives fairly, and will keep adding people — and portraits as licensed photographs are confirmed.'); ?></p></div>

  <?php if ($cat === '' && $feat): ?>
    <div class="grid g3 feat"><?php foreach ($feat as $n) echo person_card($n); ?></div>
    <div class="sec-row" style="margin-top:46px"><div><span class="eyebrow"><?php _e('More stories'); ?></span><h2><?php _e('Chiefs, warriors and public servants'); ?></h2></div></div>
  <?php endif; ?>

  <div class="filters"><a class="pill <?= $cat === '' ? 'on' : '' ?>" href="<?= e(url('people')) ?>"><?php _e('Everyone'); ?></a><?php foreach (PEOPLE_CATS as $k => $l): if (empty($cats[$k])) continue; ?><a class="pill <?= $cat === $k ? 'on' : '' ?>" href="<?= e(url('people', ['category' => $k])) ?>"><?= e(__($l)) ?> <small><?= (int)$cats[$k] ?></small></a><?php endforeach; ?></div>
  <?php if ($list): ?><div class="grid g3"><?php foreach ($list as $n) echo person_card($n); ?></div>
  <?php elseif ($cat !== ''): echo empty_state(__('Nobody in this group yet'), __('Choose another group.'), 'users'); endif; ?>

  <div class="cta-band" style="margin-top:56px">
    <div><h3><?php _e('Who else should be here?'); ?></h3><p><?php _e('Tell us about a Lango man or woman whose story should be told — a teacher, a nurse, an artist, an elder. Send their details and we will research and publish it.'); ?></p></div>
    <div><a class="btn btn--gold" href="<?= e(url('contribute', ['type' => 'oral_history'])) ?>"><?php _e('Nominate someone'); ?></a></div>
  </div>
</div></section>
<?php page_end(); ?>
