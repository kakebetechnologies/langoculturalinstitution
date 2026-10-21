<?php
declare(strict_types=1);

$grp = fn(string $g) => all("SELECT * FROM leaders WHERE grp=? AND status='published' ORDER BY sort, id", [$g]);
$won = $grp('won_nyaci')[0] ?? null;
$elders = $grp('elders'); $cabinet = $grp('cabinet'); $awitong = $grp('awitong');
$clanLeaders = all("SELECT name, slug, leader_name, leader_title, leader_photo FROM clans WHERE status='published' AND leader_name IS NOT NULL AND leader_name<>'' ORDER BY name LIMIT 60");
$timeline = all('SELECT * FROM timeline ORDER BY sort, year');

$person = function (array $p, string $sub = '') {
    $h = '<div class="person reveal"><div class="ph">' . (!empty($p['photo']) ? '<img loading="lazy" src="' . e(media_url($p['photo'])) . '" alt="' . e($p['name']) . '">' : icon('user')) . '</div>';
    $h .= '<h3>' . e($p['name']) . '</h3><small>' . e($sub ?: ($p['title'] ?? '')) . '</small>';
    if (!empty($p['bio'])) $h .= '<p style="font-size:.9rem;color:var(--muted);margin-top:8px">' . e(excerpt($p['bio'], 140)) . '</p>';
    return $h . '</div>';
};

page_start(__('Leadership'), ['hero' => ['eyebrow' => __('Leadership & governance'), 'title' => __("Won Nyaci's Office & Leadership"), 'sub' => __('The institution that guides, unites and serves the Lango people.'), 'crumbs' => [[__('About'), 'about'], [__('Leadership'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section">
  <div class="container">
    <?php if ($won): ?>
    <div class="leader-hero reveal">
      <div class="ph"><?= $won['photo'] ? '<img src="' . e(media_url($won['photo'])) . '" alt="' . e($won['name']) . '">' : icon('user') ?></div>
      <div>
        <div class="eyebrow"><?php _e('Paramount Chief'); ?></div>
        <h2 style="margin-bottom:6px"><?= e($won['name']) ?></h2>
        <p style="color:var(--clay);font-weight:700;margin-bottom:14px"><?= e($won['title']) ?></p>
        <p><?= e($won['bio']) ?></p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($elders): ?>
<section class="section section--sand"><div class="container">
  <div class="sec-head"><div class="eyebrow"><?php _e('Council of Elders'); ?></div><h2><?php _e('Guardians of wisdom'); ?></h2></div>
  <div class="grid g4"><?php foreach ($elders as $p) echo $person($p); ?></div>
</div></section>
<?php endif; ?>

<section class="section <?= $elders ? '' : 'section--sand' ?>"><div class="container">
  <div class="sec-head"><div class="eyebrow"><?php _e('Cabinet & departments'); ?></div><h2><?php _e('Serving the institution'); ?></h2></div>
  <?php if ($cabinet): ?><div class="grid g4"><?php foreach ($cabinet as $p) echo $person($p); ?></div>
  <?php else: echo empty_state(__('Cabinet profiles coming soon'), __('The Prime Minister, ministers and department heads will be introduced here.'), 'users'); endif; ?>
</div></section>

<section class="section"><div class="container">
  <div class="sec-head"><div class="eyebrow"><?php _e('Clan leaders'); ?></div><h2><?php _e('The Awitong of our 149 clans'); ?></h2><p><?php _e('Each clan is represented by its Awitong, who serves the clan within the Lango Cultural Institution.'); ?></p></div>
  <?php if ($awitong || $clanLeaders): ?>
    <div class="grid g4">
      <?php foreach ($awitong as $p) echo $person($p); ?>
      <?php foreach ($clanLeaders as $c) echo $person(['name' => $c['leader_name'], 'photo' => $c['leader_photo']], ($c['leader_title'] ?: 'Awitong') . ' · ' . $c['name']); ?>
    </div>
  <?php else: echo empty_state(__('Clan leader directory coming soon'), __('As each clan is verified, its Awitong will appear here and on the clan page.'), 'users', '<p><a class="btn btn--primary btn--sm" href="' . e(url('apply/clan')) . '">' . e(__('Register your clan')) . '</a></p>'); endif; ?>
</div></section>

<?php if ($timeline): ?>
<section class="section section--sand"><div class="container">
  <div class="sec-head"><div class="eyebrow"><?php _e('Leadership timeline'); ?></div><h2><?php _e('Through the years'); ?></h2></div>
  <ul class="tl" style="padding-left:34px"><?php foreach ($timeline as $t): ?><li class="reveal"><b><?= e($t['year']) ?></b><h3 style="margin:2px 0 6px"><?= e($t['title']) ?></h3><p><?= e($t['body']) ?></p></li><?php endforeach; ?></ul>
</div></section>
<?php endif; ?>

<?php
$docs = all("SELECT * FROM documents WHERE status='published' AND category IN ('governance','policy') ORDER BY year DESC, id DESC LIMIT 6");
if ($docs): ?>
<section class="section"><div class="container">
  <div class="sec-head"><div class="eyebrow"><?php _e('Official documents'); ?></div><h2><?php _e('Policies & governance records'); ?></h2></div>
  <div class="grid g3"><?php foreach ($docs as $d): ?>
    <a class="box" style="display:block;color:inherit" href="<?= e(url('download/' . $d['id'])) ?>"><span class="tag"><?= e($d['category']) ?></span><h4 style="margin-top:8px"><?= e($d['title']) ?></h4><p style="color:var(--muted);font-size:.9rem"><?= e(excerpt($d['description'], 100)) ?></p><b><?= icon('download') ?> <?php _e('Download'); ?></b></a>
  <?php endforeach; ?></div>
</div></section>
<?php endif; ?>
<?php page_end(); ?>
