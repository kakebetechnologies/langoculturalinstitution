<?php
declare(strict_types=1);

$type = get('type'); $album = get('album');
$types = ['photo' => ['Photos', 'image'], 'video' => ['Videos', 'video'], 'audio' => ['Audio archive', 'music'], 'podcast' => ['Podcast', 'mic']];
$where = ["status='published'"]; $par = [];
if (isset($types[$type])) { $where[] = 'type=?'; $par[] = $type; }
if ($album !== '') { $where[] = 'album=?'; $par[] = $album; }
$w = implode(' AND ', $where);
$total = (int)val("SELECT COUNT(*) FROM media WHERE $w", $par);
$pg = paginate($total, 24);
$rows = all("SELECT * FROM media WHERE $w ORDER BY id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
$albums = all("SELECT DISTINCT album FROM media WHERE status='published' AND album IS NOT NULL AND album<>'' ORDER BY album");

page_start(__('Media Center'), ['hero' => ['eyebrow' => __('Media center'), 'title' => __('Photos, video & audio'), 'sub' => __('Galleries of events and heritage sites, documentaries, oral histories, songs and the LCI podcast.'), 'crumbs' => [[__('Library'), 'library'], [__('Media'), null]], 'image' => 'assets/img/photos/hero-aerial.jpg']]);
?>
<section class="section"><div class="container">
  <div class="filters">
    <a class="pill <?= $type === '' ? 'on' : '' ?>" href="<?= e(url('media')) ?>"><?php _e('All'); ?></a>
    <?php foreach ($types as $k => [$l, $ic]): ?><a class="pill <?= $type === $k ? 'on' : '' ?>" href="<?= e(url('media', ['type' => $k])) ?>"><?= icon($ic) ?> <?= e(__($l)) ?></a><?php endforeach; ?>
    <?php if ($albums): ?><form method="get" style="margin-left:auto"><?php if ($type): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?><select name="album" onchange="this.form.submit()" style="padding:.5em 1em;border-radius:999px" aria-label="Album"><option value=""><?php _e('All albums'); ?></option><?php foreach ($albums as $a): ?><option <?= $album === $a['album'] ? 'selected' : '' ?>><?= e($a['album']) ?></option><?php endforeach; ?></select></form><?php endif; ?>
  </div>
  <?php if ($rows): ?>
    <?php $photos = array_filter($rows, fn($r) => $r['type'] === 'photo'); $others = array_filter($rows, fn($r) => $r['type'] !== 'photo'); ?>
    <?php if ($photos): ?><div class="gal"><?php foreach ($photos as $r): ?><a href="<?= e(media_url($r['file'])) ?>" data-lb="<?= e($r['title']) ?>"><img loading="lazy" src="<?= e(media_url($r['file'])) ?>" alt="<?= e($r['title']) ?>"><span><?= e($r['title']) ?></span></a><?php endforeach; ?></div><?php endif; ?>
    <?php if ($others): ?><div class="grid g2" style="margin-top:30px">
      <?php foreach ($others as $r): $emb = embed_url($r['url']); ?>
        <div class="box"><span class="tag"><?= e(__($types[$r['type']][0])) ?></span><h4 style="margin-top:8px"><?= e($r['title']) ?></h4>
          <?php if ($emb): ?><div class="video"><iframe src="<?= e($emb) ?>" title="<?= e($r['title']) ?>" loading="lazy" allowfullscreen></iframe></div>
          <?php elseif ($r['type'] === 'video' && $r['file']): ?><div class="video"><video controls preload="metadata" src="<?= e(media_url($r['file'])) ?>"></video></div>
          <?php elseif ($r['file']): ?><audio controls preload="none" style="width:100%" src="<?= e(media_url($r['file'])) ?>"></audio><?php endif; ?>
          <?php if ($r['description']): ?><p style="margin-top:10px;color:var(--muted)"><?= e(excerpt($r['description'], 160)) ?></p><?php endif; ?>
          <?php if ($r['credit']): ?><small style="color:var(--muted)"><?php _e('Credit'); ?>: <?= e($r['credit']) ?></small><?php endif; ?></div>
      <?php endforeach; ?></div><?php endif; ?>
    <?= pager($pg) ?>
  <?php else: echo empty_state(__('The media archive is being built'), __('Galleries, recordings and videos will appear here. You can help: share photos and recordings through the contribution form.'), 'image', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute')) . '">' . e(__('Contribute media')) . '</a></p>'); endif; ?>
</div></section>
<?php page_end(); ?>
