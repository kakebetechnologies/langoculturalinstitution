<?php
declare(strict_types=1);

$grp = fn(string $g) => all("SELECT * FROM leaders WHERE grp=? AND status='published' ORDER BY sort, id", [$g]);
$won = $grp('won_nyaci')[0] ?? null;
$elders = $grp('elders'); $cabinet = $grp('cabinet');
$clanTotal = (int)setting('clan_total', '149');
$loaded = (int)val("SELECT COUNT(*) FROM clans WHERE status='published'");
$withLeader = (int)val("SELECT COUNT(*) FROM clans WHERE status='published' AND leader_name IS NOT NULL AND leader_name<>''");
$staff = is_staff();

$person = function (array $p) use ($staff): string {
    $placeholder = $p['name'] === 'Name to be added';
    $h = '<div class="lcard reveal"><div class="lcard__ph">' . (!empty($p['photo']) ? '<img loading="lazy" src="' . e(media_url($p['photo'])) . '" alt="' . ($placeholder ? '' : e($p['name'])) . '">' : icon('user')) . '</div>';
    $h .= '<h3' . ($placeholder ? ' class="lcard__tbc"' : '') . '>' . e($placeholder ? __('Name to be announced') : $p['name']) . '</h3><span class="lcard__role">' . e((string)$p['title']) . '</span>';
    if (!empty($p['bio'])) $h .= '<p>' . e(excerpt($p['bio'], 150)) . '</p>';
    if ($placeholder && $staff) $h .= '<small class="lcard__hint">' . e(__('Sample entry — replace the name and photo in Admin → Leadership.')) . '</small>';
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
        <p class="leader-hero__role"><?= e((string)$won['title']) ?></p>
        <p><?= e((string)$won['bio']) ?></p>
        <ul class="leader-hero__links">
          <li><a href="<?= e(url('about')) ?>"><?= icon('arrow') ?> <?php _e('About the Lango Cultural Institution'); ?></a></li>
          <li><a href="<?= e(url('history')) ?>"><?= icon('arrow') ?> <?php _e('The history behind the title'); ?></a></li>
          <li><a href="<?= e(url('contact')) ?>"><?= icon('arrow') ?> <?php _e('Write to the Office of the Won Nyaci'); ?></a></li>
        </ul>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section--cream"><div class="container">
  <div class="sec-head"><div class="eyebrow"><?php _e('Cabinet & departments'); ?></div><h2><?php _e('Serving the institution'); ?></h2><p><?php _e('The ministers and officers who run LCI’s programmes day to day.'); ?></p></div>
  <?php if ($cabinet): ?><div class="grid g3 lgrid"><?php foreach ($cabinet as $p) echo $person($p); ?></div>
  <?php else: echo empty_state(__('Cabinet profiles coming soon'), __('The Prime Minister, ministers and department heads will be introduced here.'), 'users'); endif; ?>
</div></section>

<?php if ($elders): ?>
<section class="section"><div class="container">
  <div class="split" style="align-items:center;gap:48px">
    <div><div class="eyebrow"><?php _e('Council of Elders'); ?></div><h2><?php _e('Guardians of wisdom'); ?></h2>
      <p><?php _e('Lango society has always valued the counsel of its elders. The Council advises the Won Nyaci on custom, clan relations and the settlement of disputes.'); ?></p></div>
    <div class="grid g2 lgrid"><?php foreach ($elders as $p) echo $person($p); ?></div>
  </div>
</div></section>
<?php endif; ?>

<section class="section section--cream"><div class="container">
  <div class="awit">
    <div>
      <div class="eyebrow"><?php _e('Clan leaders'); ?></div>
      <h2><?php _e('Every clan has its Awitong'); ?></h2>
      <p><?= sprintf(__('LCI represents %d registered clans. Each clan is led by its Awitong, whose name and photograph appear on that clan’s own page — so they are shown only when you look up the clan.'), $clanTotal) ?></p>
      <p style="display:flex;gap:10px;flex-wrap:wrap;margin:0"><a class="btn btn--primary" href="<?= e(url('clans')) ?>"><?= icon('users') ?> <?php _e('Open the Clan Explorer'); ?></a><a class="btn btn--outline" href="<?= e(url('assistant')) ?>"><?= icon('chat') ?> <?php _e('Ask: who leads my clan?'); ?></a></p>
    </div>
    <dl class="awit__stats"><div><dt><?= $loaded ?></dt><dd><?php _e('clans on this website so far'); ?></dd></div><div><dt><?= $withLeader ?></dt><dd><?php _e('with a leader recorded'); ?></dd></div></dl>
  </div>
</div></section>

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
