<?php
declare(strict_types=1);

$cats = all('SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id=c.id AND p.status=\'published\' AND p.published_at<=NOW()) n FROM post_categories c ORDER BY c.sort');

/* /news/{slug} */
if (!empty($seg[1]) && $seg[1] !== 'category') {
    $p = one("SELECT p.*, c.name cat, c.slug cat_slug FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.slug=? AND p.status='published' AND p.published_at<=NOW()", [$seg[1]]);
    if (!$p) not_found();
    if (empty($_SESSION['viewed'][$p['id']])) { q('UPDATE posts SET views=views+1 WHERE id=?', [$p['id']]); $_SESSION['viewed'][$p['id']] = 1; }
    $comments = all("SELECT * FROM comments WHERE post_id=? AND status='approved' ORDER BY created_at", [$p['id']]);
    $related = all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE p.status='published' AND p.published_at<=NOW() AND p.id<>? ORDER BY (p.category_id<=>?) DESC, p.published_at DESC LIMIT 3", [$p['id'], $p['category_id']]);
    $embed = embed_url($p['video_url']);
    $self = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];
    $ttl = urlencode(t($p, 'title'));
    page_start(t($p, 'title'), ['image' => $p['image'], 'desc' => t($p, 'excerpt'), 'hero' => ['eyebrow' => (string)$p['cat'], 'title' => t($p, 'title'), 'crumbs' => [[__('News'), 'news'], [(string)$p['cat'], $p['cat_slug'] ? 'news?category=' . $p['cat_slug'] : null]], 'image' => $p['image']]]);
    ?>
    <section class="section"><div class="container article">
      <article>
        <p class="meta" style="margin-bottom:22px"><span><?= icon('calendar') ?> <?= e(fmt_date($p['published_at'], 'j F Y')) ?></span><?php if ($p['author_name']): ?><span><?= icon('user') ?> <?= e($p['author_name']) ?></span><?php endif; ?><span><?= icon('eye') ?> <?= number_format((int)$p['views']) ?></span>
          <?php if (lang() === 'lg' && !$p['title_lg']): ?><span class="tag tag--gold"><?php _e('Not yet translated'); ?></span><?php endif; ?></p>
        <?php if ($p['image']): ?><img src="<?= e(media_url($p['image'])) ?>" alt="" style="border-radius:16px;margin-bottom:28px;width:100%"><?php endif; ?>
        <div class="prose"><?= rich(t($p, 'body')) ?></div>
        <?php if ($embed): ?><div class="video" style="margin:28px 0"><iframe src="<?= e($embed) ?>" title="Video" allowfullscreen loading="lazy"></iframe></div><?php endif; ?>
        <?php if ($p['audio']): ?><audio controls style="width:100%;margin:20px 0" src="<?= e(media_url($p['audio'])) ?>"></audio><?php endif; ?>
        <div class="share" style="margin:34px 0"><b style="align-self:center"><?php _e('Share'); ?>:</b>
          <a target="_blank" rel="noopener" href="https://wa.me/?text=<?= $ttl ?>%20<?= urlencode($self) ?>">WhatsApp</a>
          <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($self) ?>">Facebook</a>
          <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text=<?= $ttl ?>&url=<?= urlencode($self) ?>">X</a>
          <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($self) ?>">LinkedIn</a></div>

        <h3 id="comments"><?= count($comments) ?> <?php _e('Comments'); ?></h3>
        <?php foreach ($comments as $c): ?><div class="box" style="margin-bottom:12px;border-top-width:3px"><b><?= e($c['name']) ?></b> <small style="color:var(--muted)"><?= e(fmt_date($c['created_at'])) ?></small><p style="margin:6px 0 0"><?= nl2br(e($c['body'])) ?></p></div><?php endforeach; ?>
        <form class="form formcard" method="post" action="<?= e(url('comment')) ?>" style="margin-top:20px">
          <?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>"><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
          <h4 style="margin:0"><?php _e('Leave a comment'); ?></h4>
          <div class="fgrid"><div class="field"><label><?php _e('Name'); ?></label><input type="text" name="name" required maxlength="100" value="<?= e(user()['name'] ?? '') ?>"></div><div class="field"><label><?php _e('Email (not published)'); ?></label><input type="email" name="email" value="<?= e(user()['email'] ?? '') ?>"></div></div>
          <div class="field"><label><?php _e('Comment'); ?></label><textarea name="body" required maxlength="2000"></textarea><small><?php _e('Comments are moderated before they appear.'); ?></small></div>
          <div><button class="btn btn--primary"><?php _e('Post comment'); ?></button></div>
        </form>
      </article>
      <aside class="aside">
        <div class="box"><h4><?php _e('Categories'); ?></h4><p style="line-height:2"><?php foreach ($cats as $c): ?><a href="<?= e(url('news', ['category' => $c['slug']])) ?>"><?= e(__($c['name'])) ?></a> <small style="color:var(--muted)">(<?= (int)$c['n'] ?>)</small><br><?php endforeach; ?></p></div>
        <?php if ($related): ?><div class="box"><h4><?php _e('More stories'); ?></h4><?php foreach ($related as $r): ?><p style="margin-bottom:12px"><a href="<?= e(url('news/' . $r['slug'])) ?>"><b><?= e(t($r, 'title')) ?></b></a><br><small style="color:var(--muted)"><?= e(fmt_date($r['published_at'])) ?></small></p><?php endforeach; ?></div><?php endif; ?>
      </aside>
    </div></section>
    <?php page_end(); return;
}

/* /news list */
$cat = get('category'); $qs = get('q');
$where = ["p.status='published'", 'p.published_at<=NOW()']; $par = [];
if ($cat !== '') { $where[] = 'c.slug=?'; $par[] = $cat; }
if ($qs !== '') { $where[] = '(p.title LIKE ? OR p.excerpt LIKE ? OR p.body LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
$w = implode(' AND ', $where);
$total = (int)val("SELECT COUNT(*) FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE $w", $par);
$pg = paginate($total, 9);
$rows = all("SELECT p.*, c.name cat FROM posts p LEFT JOIN post_categories c ON c.id=p.category_id WHERE $w ORDER BY p.featured DESC, p.published_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);

page_start(__('News & Blog'), ['hero' => ['eyebrow' => __('News & blog'), 'title' => __('News, stories and royal messages'), 'sub' => __('Announcements from the Won Nyaci’s office, clan leaders, cultural experts and young Lango voices.'), 'crumbs' => [[__('News'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
?>
<section class="section"><div class="container">
  <div class="filters">
    <a class="pill <?= $cat === '' ? 'on' : '' ?>" href="<?= e(url('news')) ?>"><?php _e('All'); ?></a>
    <?php foreach ($cats as $c): ?><a class="pill <?= $cat === $c['slug'] ? 'on' : '' ?>" href="<?= e(url('news', ['category' => $c['slug']])) ?>"><?= e(__($c['name'])) ?></a><?php endforeach; ?>
    <form method="get" style="margin-left:auto;display:flex;gap:8px"><?php if ($cat): ?><input type="hidden" name="category" value="<?= e($cat) ?>"><?php endif; ?><input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Search news…')) ?>" style="padding:.55em 1em;border-radius:999px;width:200px" aria-label="Search"></form>
  </div>
  <?php if ($rows): ?><div class="grid g3"><?php foreach ($rows as $p) echo card(url('news/' . $p['slug']), $p['image'], t($p, 'title'), t($p, 'excerpt'), (string)$p['cat'], '<span>' . e(fmt_date($p['published_at'])) . '</span>' . ($p['author_name'] ? '<span>' . e($p['author_name']) . '</span>' : '')); ?></div><?= pager($pg) ?>
  <?php else: echo empty_state(__('No stories found'), __('Check back soon or try another category.'), 'file'); endif; ?>
</div></section>
<?php page_end(); ?>
