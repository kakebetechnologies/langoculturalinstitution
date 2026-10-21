<?php
declare(strict_types=1);

$clans = all("SELECT id, name FROM clans WHERE status='published' ORDER BY name");
if (($seg[1] ?? '') === 'join') {
    if (is_post()) {
        csrf_check(); keep_old($_POST);
        if (!empty($_POST['website'])) redirect('professionals');
        if (!throttle('prof', 4)) { flash(__('Too many submissions. Please wait a few minutes.'), 'error'); redirect('professionals/join'); }
        $name = post('name'); $ind = post('industry');
        if ($name === '' || $ind === '') { flash(__('Please enter your name and industry.'), 'error'); redirect('professionals/join'); }
        $photo = null;
        try { $photo = save_upload('photo', 'image'); } catch (RuntimeException $e) { flash($e->getMessage(), 'error'); redirect('professionals/join'); }
        insert('professionals', ['name' => $name, 'email' => post('email') ?: null, 'phone' => post('phone') ?: null, 'clan_id' => (int)post('clan_id') ?: null, 'industry' => $ind,
            'expertise' => post('expertise') ?: null, 'location' => post('location') ?: null, 'bio' => mb_substr(post('bio'), 0, 2000) ?: null, 'photo' => $photo]);
        clear_old();
        flash(__('Thank you! Your profile will appear in the directory after verification.'));
        redirect('professionals');
    }
    page_start(__('Join the directory'), ['hero' => ['eyebrow' => __('Professionals directory'), 'title' => __('Add yourself to the directory'), 'crumbs' => [[__('Professionals'), 'professionals'], [__('Join'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:780px"><form class="formcard form" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
      <div class="fgrid"><div class="field"><label><?php _e('Full name'); ?> *</label><input type="text" name="name" required value="<?= e(old('name')) ?>"></div><div class="field"><label><?php _e('Industry / profession'); ?> *</label><input type="text" name="industry" required value="<?= e(old('industry')) ?>" placeholder="<?= e(__('e.g. Medicine, Law, Engineering, Agriculture')) ?>"></div>
      <div class="field"><label><?php _e('Clan'); ?></label><select name="clan_id"><option value=""><?php _e('Select…'); ?></option><?php foreach ($clans as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div><div class="field"><label><?php _e('Where you work'); ?></label><input type="text" name="location" value="<?= e(old('location')) ?>"></div>
      <div class="field"><label><?php _e('Email'); ?></label><input type="email" name="email" value="<?= e(old('email')) ?>"></div><div class="field"><label><?php _e('Phone'); ?></label><input type="tel" name="phone"></div></div>
      <div class="field"><label><?php _e('Expertise'); ?></label><input type="text" name="expertise" value="<?= e(old('expertise')) ?>"></div>
      <div class="field"><label><?php _e('Short bio'); ?></label><textarea name="bio"><?= e(old('bio')) ?></textarea></div>
      <div class="field"><label><?php _e('Photo (optional)'); ?></label><input type="file" name="photo" accept="image/*"></div>
      <div><button class="btn btn--primary"><?php _e('Submit for verification'); ?></button></div></form></div></section>
    <?php page_end(); return;
}

$qs = get('q'); $ind = get('industry'); $cl = (int)get('clan');
$where = ["p.status='published'"]; $par = [];
if ($qs !== '') { $where[] = '(p.name LIKE ? OR p.expertise LIKE ? OR p.industry LIKE ?)'; $l = '%' . like_escape($qs) . '%'; array_push($par, $l, $l, $l); }
if ($ind !== '') { $where[] = 'p.industry=?'; $par[] = $ind; }
if ($cl) { $where[] = 'p.clan_id=?'; $par[] = $cl; }
$rows = all('SELECT p.*, c.name clan FROM professionals p LEFT JOIN clans c ON c.id=p.clan_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.name', $par);
$inds = all("SELECT DISTINCT industry FROM professionals WHERE status='published' ORDER BY industry");
page_start(__('Professionals Directory'), ['hero' => ['eyebrow' => __('Connect & mentor'), 'title' => __('Lango professionals directory'), 'sub' => __('Verified Lango professionals by clan, industry and expertise.'), 'crumbs' => [[__('Library'), 'library'], [__('Professionals'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
?>
<section class="section"><div class="container">
  <form method="get" class="filters" style="align-items:stretch"><input type="search" name="q" value="<?= e($qs) ?>" placeholder="<?= e(__('Name, expertise, industry…')) ?>" style="max-width:320px" aria-label="Search">
    <select name="industry" style="max-width:220px" aria-label="Industry"><option value=""><?php _e('All industries'); ?></option><?php foreach ($inds as $i): ?><option <?= $ind === $i['industry'] ? 'selected' : '' ?>><?= e($i['industry']) ?></option><?php endforeach; ?></select>
    <select name="clan" style="max-width:220px" aria-label="Clan"><option value=""><?php _e('All clans'); ?></option><?php foreach ($clans as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cl === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
    <button class="btn btn--dark btn--sm"><?= icon('search') ?> <?php _e('Filter'); ?></button><span style="flex:1"></span><a class="btn btn--primary btn--sm" href="<?= e(url('professionals/join')) ?>"><?= icon('plus') ?> <?php _e('Add yourself'); ?></a></form>
  <?php if ($rows): ?><div class="grid g4"><?php foreach ($rows as $p): ?>
    <div class="person box reveal" style="border-top-color:var(--clay)"><div class="ph" style="width:110px;height:110px"><?= $p['photo'] ? '<img loading="lazy" src="' . e(media_url($p['photo'])) . '" alt="">' : icon('user') ?></div><h3 style="font-size:1.1rem"><?= e($p['name']) ?></h3><small><?= e($p['industry']) ?></small><p style="font-size:.85rem;color:var(--muted);margin:8px 0 0"><?= e((string)$p['expertise']) ?><?= $p['clan'] ? '<br>' . e(__('Clan')) . ': ' . e($p['clan']) : '' ?><?= $p['location'] ? '<br>' . e($p['location']) : '' ?></p></div>
  <?php endforeach; ?></div>
  <?php else: echo empty_state(__('The directory is just starting'), __('Be among the first Lango professionals listed, and help mentor the next generation.'), 'users', '<p><a class="btn btn--primary btn--sm" href="' . e(url('professionals/join')) . '">' . e(__('Add yourself')) . '</a></p>'); endif; ?>
</div></section>
<?php page_end(); ?>
