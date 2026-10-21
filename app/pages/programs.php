<?php
declare(strict_types=1);

if (!empty($seg[1])) {
    $p = one("SELECT * FROM programs WHERE slug=? AND status='published'", [$seg[1]]);
    if (!$p) not_found();
    $raised = (float)$p['raised_amount']; $goal = (float)$p['goal_amount'];
    page_start(t($p, 'title'), ['image' => $p['image'], 'desc' => $p['summary'], 'hero' => ['eyebrow' => __(PROGRAM_CATS[$p['category']] ?? 'Program'), 'title' => t($p, 'title'), 'sub' => $p['summary'], 'crumbs' => [[__('Programs'), 'programs'], [t($p, 'title'), null]], 'image' => $p['image'] ?: 'assets/img/photos/hero-aerial.jpg']]);
    ?>
    <section class="section"><div class="container article">
      <div class="prose"><?= rich(t($p, 'body')) ?></div>
      <aside class="aside">
        <div class="box"><h4><?php _e('Progress'); ?></h4><div class="progress"><i style="width:<?= (int)$p['progress'] ?>%"></i></div><div class="pmeta"><span><?= (int)$p['progress'] ?>% <?php _e('complete'); ?></span></div>
          <?php if ($goal > 0): ?><p style="margin:14px 0 0"><b><?= e(money($raised)) ?></b> <?php _e('raised of'); ?> <?= e(money($goal)) ?></p><div class="progress" style="margin-top:8px"><i style="width:<?= min(100, (int)round($raised / $goal * 100)) ?>%"></i></div><?php endif; ?></div>
        <?php if ($p['accepts_donations']): ?><div class="box box--dark"><h4><?php _e('Sponsor this program'); ?></h4><p><?php _e('Choose this program when you donate and your gift goes directly to it.'); ?></p><a class="btn btn--gold btn--block" href="<?= e(url('donate', ['program' => $p['id']])) ?>"><?= icon('heart') ?> <?php _e('Fund this program'); ?></a></div><?php endif; ?>
      </aside>
    </div></section>
    <?php page_end(); return;
}

$cat = get('category');
$where = ["status='published'"]; $par = [];
if (isset(PROGRAM_CATS[$cat])) { $where[] = 'category=?'; $par[] = $cat; }
$rows = all('SELECT * FROM programs WHERE ' . implode(' AND ', $where) . ' ORDER BY featured DESC, sort, id', $par);
$imgs = ['hero-aerial', 'hero-field', 'hero-ridge', 'hero-hill', 'hero-site', 'hero-road'];
page_start(__('Programs'), ['hero' => ['eyebrow' => __('Foundation programs'), 'title' => __('Programs that strengthen Lango'), 'sub' => __('Cultural preservation, education, youth empowerment, conservation, development and digital innovation.'), 'crumbs' => [[__('Programs'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
?>
<section class="section"><div class="container">
  <div class="filters"><a class="pill <?= $cat === '' ? 'on' : '' ?>" href="<?= e(url('programs')) ?>"><?php _e('All'); ?></a><?php foreach (PROGRAM_CATS as $k => $l): ?><a class="pill <?= $cat === $k ? 'on' : '' ?>" href="<?= e(url('programs', ['category' => $k])) ?>"><?= e(__($l)) ?></a><?php endforeach; ?></div>
  <div class="grid g3">
    <?php foreach ($rows as $p): ?>
      <article class="card reveal"><a class="card__img" href="<?= e(url('programs/' . $p['slug'])) ?>"><img loading="lazy" src="<?= e(media_url($p['image'], 'assets/img/photos/' . $imgs[$p['id'] % 6] . '.jpg')) ?>" alt=""></a>
        <div class="card__body"><span class="tag tag--green"><?= e(__(PROGRAM_CATS[$p['category']])) ?></span><h3><a href="<?= e(url('programs/' . $p['slug'])) ?>"><?= e(t($p, 'title')) ?></a></h3><p><?= e(excerpt($p['summary'], 140)) ?></p>
          <div style="margin-top:12px"><div class="progress"><i style="width:<?= (int)$p['progress'] ?>%"></i></div><div class="pmeta"><span><?php _e('Progress'); ?></span><b><?= (int)$p['progress'] ?>%</b></div></div>
          <a class="card__more" href="<?= e(url('programs/' . $p['slug'])) ?>"><?php _e('Learn more'); ?> <?= icon('arrow') ?></a></div></article>
    <?php endforeach; ?>
  </div>
  <?php if (!$rows) echo empty_state(__('No programs in this category yet'), __('Please check another category.'), 'leaf'); ?>
</div></section>
<section class="section section--sand"><div class="container">
  <div class="sec-head"><div class="eyebrow"><?php _e('Get involved'); ?></div><h2><?php _e('Be part of the work'); ?></h2></div>
  <div class="grid g4">
    <a class="tile" href="<?= e(url('apply/scholarship')) ?>"><span class="ico"><?= icon('book') ?></span><h3><?php _e('Scholarships'); ?></h3><p><?php _e('Apply for LCI education support.'); ?></p></a>
    <a class="tile" href="<?= e(url('apply/grant')) ?>"><span class="ico"><?= icon('file') ?></span><h3><?php _e('Research grants'); ?></h3><p><?php _e('Students and researchers of Lango heritage.'); ?></p></a>
    <a class="tile" href="<?= e(url('apply/volunteer')) ?>"><span class="ico"><?= icon('users') ?></span><h3><?php _e('Volunteer'); ?></h3><p><?php _e('Tech camps, internships and field work.'); ?></p></a>
    <a class="tile" href="<?= e(url('diaspora')) ?>"><span class="ico"><?= icon('globe') ?></span><h3><?php _e('Diaspora partners'); ?></h3><p><?php _e('Associations abroad, join the network.'); ?></p></a>
  </div>
</div></section>
<?php page_end(); ?>
