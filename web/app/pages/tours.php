<?php
declare(strict_types=1);

$tours = all("SELECT * FROM tours WHERE status='published' ORDER BY sort, id");
$cur = null;
foreach ($tours as $t) if ((int)get('id') === (int)$t['id']) $cur = $t;
$cur = $cur ?? ($tours[0] ?? null);
page_start(__('360° Heritage Tours'), ['hero' => ['eyebrow' => __('Virtual heritage'), 'title' => __('360° heritage tours'), 'sub' => __('Visit the palace, monuments and heritage sites of Lango from anywhere in the world — on a browser, a phone or a VR headset.'), 'crumbs' => [[__('Heritage'), 'heritage'], [__('360° Tours'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
?>
<section class="section"><div class="container">
  <?php if ($cur && $cur['panorama']): ?>
    <div class="pano"><a-scene embedded vr-mode-ui="enabled: true" loading-screen="enabled:false" style="width:100%;height:100%"><a-sky src="<?= e(media_url($cur['panorama'])) ?>" rotation="0 -90 0"></a-sky><a-camera look-controls="reverseMouseDrag: true"></a-camera></a-scene></div>
    <h2 style="margin-top:24px"><?= e($cur['title']) ?></h2><p><?= e((string)$cur['description']) ?></p><p style="color:var(--muted);font-size:.85rem"><?php _e('Drag to look around. Use the headset icon for VR.'); ?></p>
    <?php if (count($tours) > 1): ?><div class="filters"><?php foreach ($tours as $t): ?><a class="pill <?= $t['id'] === $cur['id'] ? 'on' : '' ?>" href="<?= e(url('tours', ['id' => $t['id']])) ?>"><?= e($t['title']) ?></a><?php endforeach; ?></div><?php endif; ?>
  <?php else: ?>
    <?= empty_state(__('Tours are being captured'), __('LCI is photographing the Palace, monuments and heritage sites in 360°. Tours will be published here, and community members will be able to contribute their own 360° photographs.'), 'eye', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute?type=heritage_site')) . '">' . e(__('Suggest a site to capture')) . '</a></p>') ?>
  <?php endif; ?>
</div></section>
<?php page_end($cur && $cur['panorama'] ? ['scripts' => ['https://aframe.io/releases/1.5.0/aframe.min.js']] : []); ?>
