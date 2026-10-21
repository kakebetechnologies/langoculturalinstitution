<?php
declare(strict_types=1);

$tot = donation_totals();
$programs = all("SELECT p.*, (SELECT COALESCE(SUM(amount),0) FROM donations d WHERE d.program_id=p.id AND d.status='confirmed' AND d.currency='UGX') confirmed FROM programs p WHERE p.status='published' ORDER BY p.sort");
$recent = all("SELECT donor_name, anonymous, amount, currency, created_at FROM donations WHERE status='confirmed' ORDER BY created_at DESC LIMIT 12");
page_start(__('Transparency'), ['hero' => ['eyebrow' => __('Accountability'), 'title' => __('Transparency dashboard'), 'sub' => __('Funds raised, programs supported and the impact of every contribution.'), 'crumbs' => [[__('Programs'), 'programs'], [__('Transparency'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
?>
<section class="section"><div class="container">
  <div class="stats" style="background:var(--earth);border-radius:var(--radius);padding:34px 10px;margin-bottom:56px">
    <div class="stat"><b><?= number_format((int)$tot['n']) ?></b><span><?php _e('Confirmed gifts'); ?></span></div>
    <div class="stat"><b><?= number_format((float)$tot['ugx']) ?></b><span>UGX <?php _e('raised'); ?></span></div>
    <div class="stat"><b><?= number_format((float)$tot['usd']) ?></b><span>USD <?php _e('raised'); ?></span></div>
    <div class="stat"><b><?= count($programs) ?></b><span><?php _e('Active programs'); ?></span></div>
  </div>
  <h2><?php _e('Programs & funding'); ?></h2>
  <div class="table-wrap"><table class="table"><thead><tr><th><?php _e('Program'); ?></th><th><?php _e('Progress'); ?></th><th><?php _e('Funds (UGX)'); ?></th></tr></thead><tbody>
    <?php foreach ($programs as $p): $raised = (float)$p['raised_amount'] + (float)$p['confirmed']; ?>
      <tr><td><a href="<?= e(url('programs/' . $p['slug'])) ?>"><b><?= e(t($p, 'title')) ?></b></a></td><td style="min-width:180px"><div class="progress"><i style="width:<?= (int)$p['progress'] ?>%"></i></div> <small><?= (int)$p['progress'] ?>%</small></td>
        <td><?= number_format($raised) ?><?= $p['goal_amount'] > 0 ? ' / ' . number_format((float)$p['goal_amount']) : '' ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
  <?php if ($recent): ?><h2 style="margin-top:56px"><?php _e('Donor recognition'); ?></h2><div class="grid g3"><?php foreach ($recent as $r): ?><div class="box"><b><?= e($r['anonymous'] ? __('Anonymous friend of Lango') : $r['donor_name']) ?></b><br><small style="color:var(--muted)"><?= e(fmt_date($r['created_at'])) ?></small></div><?php endforeach; ?></div><?php endif; ?>
  <p style="margin-top:44px;text-align:center"><a class="btn btn--primary" href="<?= e(url('donate')) ?>"><?= icon('heart') ?> <?php _e('Donate'); ?></a></p>
</div></section>
<?php page_end(); ?>
