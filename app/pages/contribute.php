<?php
declare(strict_types=1);

$clans = all("SELECT id, name FROM clans WHERE status='published' ORDER BY name");
$districts = all("SELECT id, name FROM districts WHERE status='published' ORDER BY name");
$sub = $seg[1] ?? '';

/* ---------- track ---------- */
if ($sub === 'track') {
    $code = strtoupper(get('code'));
    $c = $code !== '' ? one('SELECT * FROM contributions WHERE code=?', [$code]) : null;
    page_start(__('Track your contribution'), ['hero' => ['eyebrow' => __('Contribution tracking'), 'title' => __('Track your contribution'), 'crumbs' => [[__('Contribute'), 'contribute'], [__('Track'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:700px">
      <form class="searchbar" method="get"><input type="text" name="code" value="<?= e($code) ?>" placeholder="<?= e(__('Enter your tracking code')) ?>" aria-label="Code" maxlength="16"><button class="btn btn--primary"><?php _e('Track'); ?></button></form>
      <?php if ($code !== '' && !$c): ?><p class="alert alert-error" style="margin-top:24px"><?php _e('No contribution found with that code.'); ?></p><?php endif; ?>
      <?php if ($c): $order = ['pending', 'under_review', 'approved', 'published']; $at = array_search($c['status'], $order); ?>
        <div class="formcard" style="margin-top:30px">
          <h3><?= e($c['title']) ?></h3><p class="meta"><span><?= e(CONTRIB_TYPES[$c['type']][0]) ?></span><span><?= e(fmt_date($c['created_at'])) ?></span></p>
          <div class="wizard-steps" aria-hidden="true"><?php foreach ($order as $i => $s): ?><i class="<?= $at !== false && $i <= $at ? 'on' : '' ?>"></i><?php endforeach; ?></div>
          <p><b><?php _e('Status'); ?>:</b> <span class="tag <?= in_array($c['status'], ['approved', 'published'], true) ? 'tag--green' : ($c['status'] === 'rejected' ? 'tag--red' : 'tag--gold') ?>"><?= e(__(CONTRIB_STATUS[$c['status']])) ?></span></p>
          <?php if ($c['review_note']): ?><div class="alert alert-info"><b><?php _e('Note from our reviewers'); ?>:</b><br><?= nl2br(e($c['review_note'])) ?></div><?php endif; ?>
          <?php if ($c['points_awarded']): ?><p><?= icon('award') ?> <?= (int)$c['points_awarded'] ?> <?php _e('points awarded'); ?></p><?php endif; ?>
        </div>
      <?php endif; ?>
    </div></section>
    <?php page_end(); return;
}
if ($sub !== '' && $sub !== 'done') not_found();

/* ---------- done ---------- */
if ($sub === 'done') {
    $code = strtoupper(get('code'));
    page_start(__('Thank you'), ['hero' => ['eyebrow' => __('Contribution received'), 'title' => __('Thank you for contributing!'), 'crumbs' => [[__('Contribute'), 'contribute'], [__('Done'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:640px"><div class="formcard" style="text-align:center;border-top-color:var(--leaf)">
      <span style="display:grid;place-items:center;width:72px;height:72px;border-radius:50%;background:#e3f7f0;color:var(--teal);margin:0 auto 14px"><?= icon('check') ?></span>
      <h2><?php _e('Your contribution is in the review queue'); ?></h2>
      <p><?php _e('Clan leaders and subject experts will review it. Keep this code to follow its progress:'); ?></p>
      <p style="font:700 2.3rem var(--fd);letter-spacing:.2em;color:var(--clay);margin:6px 0"><?= e($code) ?></p>
      <p style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap"><a class="btn btn--primary" href="<?= e(url('contribute/track', ['code' => $code])) ?>"><?php _e('Track status'); ?></a><a class="btn btn--outline" href="<?= e(url('contribute')) ?>"><?php _e('Contribute another'); ?></a></p>
    </div></div></section>
    <?php page_end(); return;
}

/* ---------- submit ---------- */
if (is_post()) {
    csrf_check();
    keep_old($_POST);
    if (!empty($_POST['website'])) redirect('contribute');
    if (!throttle('contrib', 8)) { flash(__('Too many submissions in a short time. Please wait a few minutes.'), 'error'); redirect('contribute'); }
    $type = post('type'); $title = post('title'); $desc = post('description');
    $name = user()['name'] ?? post('name'); $email = user()['email'] ?? post('email');
    $err = null;
    if (!isset(CONTRIB_TYPES[$type])) $err = __('Please choose a contribution type.');
    elseif ($title === '' || mb_strlen($desc) < 10) $err = __('Please add a title and a short description (at least 10 characters).');
    elseif ($name === '') $err = __('Please tell us your name.');
    elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = __('That email address does not look right.');
    elseif (post('consent') !== '1') $err = __('Please confirm that you agree to the contribution terms.');
    $file = null; $kind = 'none';
    if (!$err) {
        try {
            foreach (['image', 'audio', 'video', 'document'] as $k) {
                if (!empty($_FILES['media_' . $k]['name'])) { $file = save_upload('media_' . $k, $k); $kind = $k; break; }
            }
        } catch (RuntimeException $ex) { $err = $ex->getMessage(); }
    }
    if ($err) { flash($err, 'error'); redirect('contribute' . ($type ? '?type=' . urlencode($type) : '')); }
    $lat = is_numeric(post('lat')) ? round((float)post('lat'), 6) : null; $lng = is_numeric(post('lng')) ? round((float)post('lng'), 6) : null;
    if ($lat !== null && ($lat < -90 || $lat > 90)) $lat = $lng = null;
    $code = new_code(8);
    insert('contributions', [
        'code' => $code, 'user_id' => user()['id'] ?? null, 'name' => mb_substr($name, 0, 150), 'email' => $email ?: null, 'phone' => post('phone') ?: null,
        'type' => $type, 'title' => mb_substr($title, 0, 220), 'description' => mb_substr($desc, 0, 8000),
        'clan_id' => (int)post('clan_id') ?: null, 'district_id' => (int)post('district_id') ?: null, 'location_text' => post('location_text') ?: null,
        'lat' => $lat, 'lng' => $lng, 'observed_on' => preg_match('/^\d{4}-\d{2}-\d{2}$/', post('observed_on')) ? post('observed_on') : null,
        'media_file' => $file, 'media_kind' => $kind, 'rights' => in_array(post('rights'), ['retain', 'cc_by', 'lci'], true) ? post('rights') : 'retain',
    ]);
    clear_old();
    redirect('contribute/done?code=' . $code);
}

page_start(__('Submit a Contribution'), ['hero' => ['eyebrow' => __('Community Contribution Engine'), 'title' => __('Add to the Lango archive'), 'sub' => __('Share a photo, story, recording or observation. Every contribution is reviewed by clan leaders and experts before it is published.'), 'crumbs' => [[__('Contribute'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
$preType = get('type') ?: old('type');
?>
<section class="section"><div class="container" style="max-width:900px">
  <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px;align-items:center">
    <span class="tag tag--green"><?= icon('check') ?> <?php _e('You keep the rights to your work'); ?></span>
    <a href="<?= e(url('contribute/track')) ?>"><?php _e('Already submitted? Track your contribution'); ?> →</a>
  </div>
  <form id="wizard" class="formcard" method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
    <div class="wizard-steps" id="wsteps" aria-hidden="true"><i class="on"></i><i></i><i></i><i></i></div>

    <div class="wstep on">
      <h2 style="font-size:1.6rem"><?php _e('1. What would you like to contribute?'); ?></h2>
      <div class="choice">
        <?php foreach (CONTRIB_TYPES as $k => [$l, $d]): ?><label><input type="radio" name="type" value="<?= e($k) ?>" <?= $preType === $k ? 'checked' : '' ?>><div><b><?= e(__($l)) ?></b><small><?= e(__($d)) ?></small></div></label><?php endforeach; ?>
      </div>
      <p style="margin-top:26px;text-align:right"><button type="button" class="btn btn--primary" data-next><?php _e('Next'); ?> <?= icon('arrow') ?></button></p>
    </div>

    <div class="wstep">
      <h2 style="font-size:1.6rem"><?php _e('2. Tell us about it'); ?></h2>
      <div class="form">
        <div class="field"><label for="t"><?php _e('Title'); ?> *</label><input id="t" type="text" name="title" required maxlength="200" value="<?= e(old('title')) ?>" placeholder="<?= e(__('e.g. Grey crowned crane at the wetland near Lira')) ?>"></div>
        <div class="field"><label for="d"><?php _e('What did you see or experience? Who, what, how?'); ?> *</label><textarea id="d" name="description" required minlength="10"><?= e(old('description')) ?></textarea><small><?php _e('For a word or proverb, write it in Leb Lango, then its meaning in English.'); ?></small></div>
        <div class="fgrid">
          <div class="field"><label for="cl"><?php _e('Which clan or community is it connected to?'); ?></label><select id="cl" name="clan_id"><option value=""><?php _e('Not sure / not applicable'); ?></option><?php foreach ($clans as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="di"><?php _e('District'); ?></label><select id="di" name="district_id"><option value=""><?php _e('Select…'); ?></option><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="ob"><?php _e('When was it? (date)'); ?></label><input id="ob" type="date" name="observed_on" max="<?= date('Y-m-d') ?>"></div>
        </div>
      </div>
      <p style="margin-top:26px;display:flex;justify-content:space-between"><button type="button" class="btn btn--outline" data-prev><?= e(__('Back')) ?></button><button type="button" class="btn btn--primary" data-next><?php _e('Next'); ?> <?= icon('arrow') ?></button></p>
    </div>

    <div class="wstep">
      <h2 style="font-size:1.6rem"><?php _e('3. Add media and location'); ?></h2>
      <div class="form">
        <div class="fgrid">
          <div class="field"><label><?= icon('camera') ?> <?php _e('Photo'); ?></label><input type="file" name="media_image" accept="image/*" capture="environment"><small>JPG, PNG, WebP · 12 MB</small></div>
          <div class="field"><label><?= icon('mic') ?> <?php _e('Audio recording'); ?></label><input type="file" name="media_audio" accept="audio/*"><small>MP3, WAV, M4A · 40 MB</small></div>
          <div class="field"><label><?= icon('video') ?> <?php _e('Video'); ?></label><input type="file" name="media_video" accept="video/*"><small>MP4, WebM · 120 MB</small></div>
          <div class="field"><label><?= icon('file') ?> <?php _e('Document'); ?></label><input type="file" name="media_document" accept=".pdf,.doc,.docx,.txt"><small>PDF, DOCX · 25 MB</small></div>
        </div>
        <small><?php _e('Only one file per contribution; the first one you choose is used.'); ?></small>
        <div class="field"><label for="lt"><?php _e('Where? (place name or description)'); ?></label><input id="lt" type="text" name="location_text" placeholder="<?= e(__('Village, parish, sub-county…')) ?>"></div>
        <div><button type="button" id="gps" class="btn btn--outline btn--sm"><?= icon('pin') ?> <?php _e('Use my current location (GPS)'); ?></button> <span id="gpsout" style="margin-left:8px;color:var(--muted);font-size:.9rem"></span>
          <input type="hidden" name="lat" id="lat"><input type="hidden" name="lng" id="lng"></div>
      </div>
      <p style="margin-top:26px;display:flex;justify-content:space-between"><button type="button" class="btn btn--outline" data-prev><?= e(__('Back')) ?></button><button type="button" class="btn btn--primary" data-next><?php _e('Next'); ?> <?= icon('arrow') ?></button></p>
    </div>

    <div class="wstep">
      <h2 style="font-size:1.6rem"><?php _e('4. About you and your rights'); ?></h2>
      <div class="form">
        <?php if (user()): ?><p class="alert alert-info"><?php _e('Signed in as'); ?> <b><?= e(user()['name']) ?></b>. <?php _e('This contribution will be added to your profile and points.'); ?></p>
        <?php else: ?>
        <div class="fgrid"><div class="field"><label for="nm"><?php _e('Your name'); ?> *</label><input id="nm" type="text" name="name" required value="<?= e(old('name')) ?>"></div>
          <div class="field"><label for="em"><?php _e('Email (for updates)'); ?></label><input id="em" type="email" name="email" value="<?= e(old('email')) ?>"></div>
          <div class="field"><label for="ph"><?php _e('Phone'); ?></label><input id="ph" type="tel" name="phone"></div></div>
        <small><a href="<?= e(url('account/register')) ?>"><?php _e('Create a free account'); ?></a> <?php _e('to earn points and badges and track all your contributions.'); ?></small>
        <?php endif; ?>
        <div class="field"><label for="rt"><?php _e('How may we use your contribution?'); ?></label>
          <select id="rt" name="rights"><option value="retain"><?php _e('I keep all rights — LCI may display it in the archive'); ?></option><option value="cc_by"><?php _e('Share openly with credit (CC BY)'); ?></option><option value="lci"><?php _e('Give LCI full rights to use it'); ?></option></select></div>
        <label class="check"><input type="checkbox" name="consent" value="1" required> <span><?php _e('I confirm this is my own work or I have permission to share it, and I agree to the'); ?> <a href="<?= e(url('terms')) ?>" target="_blank"><?php _e('terms'); ?></a>.</span></label>
      </div>
      <p style="margin-top:26px;display:flex;justify-content:space-between"><button type="button" class="btn btn--outline" data-prev><?= e(__('Back')) ?></button><button class="btn btn--primary"><?= icon('upload') ?> <?php _e('Submit contribution'); ?></button></p>
    </div>
  </form>
</div></section>
<?php page_end(); ?>
