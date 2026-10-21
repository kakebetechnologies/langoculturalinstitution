<?php
declare(strict_types=1);

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
          <p><b><?php _e('Status'); ?>:</b> <span class="tag <?= in_array($c['status'], ['approved', 'published'], true) ? 'tag--green' : ($c['status'] === 'rejected' ? 'tag--red' : 'tag--gold') ?>"><?= e(__($c['type'] === 'language' && $c['status'] === 'published' ? 'Added to the dictionary' : CONTRIB_STATUS[$c['status']])) ?></span></p>
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
    $c = $code !== '' ? one('SELECT type, word_cat FROM contributions WHERE code=?', [$code]) : null;
    $isWord = $c && $c['type'] === 'language';
    page_start(__('Thank you'), ['hero' => ['eyebrow' => __('Contribution received'), 'title' => __('Thank you for contributing!'), 'crumbs' => [[__('Contribute'), 'contribute'], [__('Done'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:640px"><div class="formcard" style="text-align:center;border-top:4px solid var(--sky)">
      <span class="okmark"><?= icon('check') ?></span>
      <h2><?= $isWord ? e(__('Your suggestion is with our language team')) : e(__('Your contribution is in the review queue')) ?></h2>
      <p><?= $isWord ? e(__('Once a reviewer approves it, it appears in the public dictionary and the AI translator. Keep this code to follow its progress:')) : e(__('Clan leaders and subject experts will review it. Keep this code to follow its progress:')) ?></p>
      <p class="codebig"><?= e($code) ?></p>
      <p style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap"><a class="btn btn--primary" href="<?= e(url('contribute/track', ['code' => $code])) ?>"><?php _e('Track status'); ?></a>
        <a class="btn btn--outline" href="<?= e(url('contribute', $isWord ? ['type' => 'language', 'kind' => $c['word_cat'] === 'proverb' ? 'proverb' : 'word'] : [])) ?>"><?= $isWord ? e(__('Suggest another')) : e(__('Contribute another')) ?></a></p>
    </div></div></section>
    <?php page_end(); return;
}

/* ---------- which form? ---------- */
$type = get('type') ?: (string)($_POST['type'] ?? '');
$type = isset(CONTRIB_TYPES[$type]) ? $type : '';
$kind = ($type === 'language' && (get('kind') === 'proverb' || post('kind') === 'proverb')) ? 'proverb' : 'word';
$self = 'contribute' . ($type ? '?type=' . $type . ($type === 'language' && $kind === 'proverb' ? '&kind=proverb' : '') : '');

/* ---------- submit ---------- */
if (is_post()) {
    csrf_check();
    keep_old($_POST);
    if (!empty($_POST['website'])) redirect('contribute');
    if (!throttle('contrib', 8)) { flash(__('Too many submissions in a short time. Please wait a few minutes.'), 'error'); redirect($self); }
    if (!$type) { flash(__('Please choose what you would like to contribute.'), 'error'); redirect('contribute'); }

    $isWord = $type === 'language';
    $title = post('title'); $desc = post('description');
    $err = null;
    if ($isWord) {
        $title = post('word'); $desc = post('meaning');
        if (mb_strlen($title) < 2 || $desc === '') $err = $kind === 'proverb' ? __('Please write the proverb and its English translation.') : __('Please write the Leb Lango word and its English meaning.');
    } elseif ($title === '' || mb_strlen($desc) < 10) $err = __('Please add a title and a short description (at least 10 characters).');

    $u = user();
    $name = $u['name'] ?? post('name'); $email = strtolower($u['email'] ?? post('email'));
    $makeAccount = !$u && post('make_account') === '1';
    [$clanId, $clanOther] = read_clan_input();
    if (!$err && $name === '') $err = __('Please tell us your name.');
    elseif (!$err && ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) $err = __('That email address does not look right.');
    elseif (!$err && post('clan_id') === '') $err = __('Please choose your clan, or “My clan is not listed”.');
    elseif (!$err && post('clan_id') === 'other' && !$clanOther) $err = __('Please type the name of your clan.');
    elseif (!$err && $makeAccount) {
        $pw = (string)($_POST['password'] ?? '');
        if ($email === '') $err = __('An email address is needed to create your account.');
        elseif (strlen($pw) < 8) $err = __('Choose a password of at least 8 characters.');
        elseif (val('SELECT COUNT(*) FROM users WHERE email=?', [$email])) $err = __('An account with this email already exists. Please sign in first.');
    }
    if (!$err && post('consent') !== '1') $err = __('Please confirm that you agree to the contribution terms.');

    $file = null; $mkind = 'none';
    if (!$err) {
        try {
            foreach (['image', 'audio', 'video', 'document'] as $k) {
                if (!empty($_FILES['media_' . $k]['name'])) { $file = save_upload('media_' . $k, $k); $mkind = $k; break; }
            }
        } catch (RuntimeException $ex) { $err = $ex->getMessage(); }
    }
    if ($err) { flash($err, 'error'); redirect($self); }

    if ($makeAccount) {
        insert('users', ['name' => mb_substr($name, 0, 150), 'email' => $email, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'role' => 'contributor', 'clan_id' => $clanId, 'clan_other' => $clanOther, 'phone' => post('phone') ?: null]);
        login_user($email, $pw);
        $u = user();
    }
    $lat = is_numeric(post('lat')) ? round((float)post('lat'), 6) : null; $lng = is_numeric(post('lng')) ? round((float)post('lng'), 6) : null;
    if ($lat !== null && ($lat < -90 || $lat > 90)) $lat = $lng = null;
    $code = new_code(8);
    $row = [
        'code' => $code, 'user_id' => $u['id'] ?? null, 'name' => mb_substr($name, 0, 150), 'email' => $email ?: null, 'phone' => post('phone') ?: null,
        'type' => $type, 'title' => mb_substr($title, 0, 220), 'description' => mb_substr($desc, 0, 8000),
        'clan_id' => $clanId, 'contributor_clan_id' => $clanId, 'contributor_clan_other' => $clanOther,
        'district_id' => (int)post('district_id') ?: null, 'location_text' => post('location_text') ?: null,
        'lat' => $lat, 'lng' => $lng, 'observed_on' => preg_match('/^\d{4}-\d{2}-\d{2}$/', post('observed_on')) ? post('observed_on') : null,
        'media_file' => $file, 'media_kind' => $mkind, 'rights' => in_array(post('rights'), ['retain', 'cc_by', 'lci'], true) ? post('rights') : 'retain',
    ];
    if ($isWord) {
        $row += ['word_cat' => $kind === 'proverb' ? 'proverb' : (isset(WORD_CATS[post('word_cat')]) ? post('word_cat') : null),
            'word_pos' => $kind === 'proverb' ? 'phrase' : (isset(WORD_POS[post('word_pos')]) ? post('word_pos') : null),
            'example_lg' => $kind === 'proverb' ? null : (mb_substr(post('example_lg'), 0, 500) ?: null),
            'word_note' => $kind === 'proverb' ? (mb_substr(post('note'), 0, 500) ?: null) : null, 'credit' => post('credit') === '1' ? 1 : 0];
    }
    insert('contributions', $row);
    clear_old();
    if ($makeAccount) flash(__('Your account is ready — you are signed in. Welcome to the Lango community!'));
    redirect('contribute/done?code=' . $code);
}

/* ---------- type chooser ---------- */
$icons = ['cultural' => 'star', 'natural' => 'leaf', 'language' => 'book', 'oral_history' => 'mic', 'document' => 'file', 'heritage_site' => 'pin'];
if (!$type) {
    page_start(__('Contribute'), ['hero' => ['eyebrow' => __('Community Contribution Engine'), 'title' => __('Add to the Lango archive'), 'sub' => __('Share a word, a photo, a story or a place. Every contribution is checked by LCI reviewers before it is published, and you keep your rights.'), 'crumbs' => [[__('Contribute'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
    ?>
    <section class="section"><div class="container">
      <div class="sec-row"><div><span class="eyebrow"><?php _e('Step 1 of 2'); ?></span><h2><?php _e('What would you like to share?'); ?></h2></div><a class="link" href="<?= e(url('contribute/track')) ?>"><?php _e('Already submitted? Track it'); ?> <?= icon('arrow') ?></a></div>
      <div class="grid g3 ctype">
        <?php foreach (CONTRIB_TYPES as $k => [$l, $d]): ?>
          <a class="tile<?= $k === 'language' ? ' tile--hl' : '' ?>" href="<?= e(url('contribute', ['type' => $k])) ?>"><span class="ico"><?= icon($icons[$k]) ?></span><h3><?= e(__($k === 'language' ? 'Suggest a word' : $l)) ?></h3><p><?= e(__($k === 'language' ? 'Names of animals, places, everyday words — with the English meaning' : $d)) ?></p><span class="link"><?php _e('Start'); ?> <?= icon('arrow') ?></span></a>
        <?php endforeach; ?>
      </div>
      <div class="ctype__more"><?= icon('chat') ?> <span><?php _e('Know a proverb?'); ?> <a href="<?= e(url('contribute', ['type' => 'language', 'kind' => 'proverb'])) ?>"><?php _e('Add it to the collection'); ?></a></span></div>
    </div></section>
    <section class="section section--cream"><div class="container">
      <div class="sec-head"><span class="eyebrow"><?php _e('How it works'); ?></span><h2><?php _e('Simple, and always reviewed'); ?></h2></div>
      <div class="grid g3">
        <div class="pillar"><span class="pillar__n">01</span><h3><?php _e('Fill in one short form'); ?></h3><p><?php _e('Choose your clan, tell us what you are sharing and add a photo or recording if you have one. No account is needed.'); ?></p></div>
        <div class="pillar"><span class="pillar__n">02</span><h3><?php _e('Our team reviews it'); ?></h3><p><?php _e('LCI reviewers and clan leaders check every submission and are notified the moment it arrives. You can follow it with your code.'); ?></p></div>
        <div class="pillar"><span class="pillar__n">03</span><h3><?php _e('It joins the archive'); ?></h3><p><?php _e('Approved words go into the dictionary and the AI translator; stories, photos and places go into the heritage archive.'); ?></p></div>
      </div>
    </div></section>
    <?php page_end(); return;
}

/* ---------- the form ---------- */
[$typeLabel, $typeDesc] = CONTRIB_TYPES[$type];
$isWord = $type === 'language'; $isProv = $isWord && $kind === 'proverb';
$u = user();
$heroTitle = $isProv ? __('Add a proverb') : ($isWord ? __('Suggest a word') : __($typeLabel));
$heroSub = $isProv ? __('Write it as you heard it, add what it means, and our language team will review it.') : ($isWord ? __('Give us the Leb Lango word and its English meaning. Once approved it joins the dictionary for everyone.') : __($typeDesc));
page_start($heroTitle, ['hero' => ['eyebrow' => __('Community Contribution Engine'), 'title' => $heroTitle, 'sub' => $heroSub, 'crumbs' => [[__('Contribute'), 'contribute'], [$heroTitle, null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
$prefWord = old('word', get('word'));
?>
<section class="section"><div class="container article article--form">
  <form class="formcard cform" method="post" enctype="multipart/form-data" action="<?= e(url($self)) ?>">
    <?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
    <input type="hidden" name="type" value="<?= e($type) ?>"><input type="hidden" name="kind" value="<?= e($kind) ?>">

    <div class="cform__sec">
      <h2><span class="num">1</span> <?= $isProv ? e(__('The proverb')) : ($isWord ? e(__('The word')) : e(__('Your contribution'))) ?></h2>
      <?php if ($isProv): ?>
        <div class="form">
          <div class="field"><label for="w"><?php _e('Proverb in Leb Lango'); ?> *</label><textarea id="w" name="word" rows="3" required maxlength="500"><?= e($prefWord) ?></textarea></div>
          <div class="field"><label for="m"><?php _e('English translation'); ?> *</label><textarea id="m" name="meaning" rows="2" required maxlength="500"><?= e(old('meaning')) ?></textarea></div>
          <div class="field"><label for="nt"><?php _e('What does it mean, and when is it used?'); ?></label><textarea id="nt" name="note" rows="3" maxlength="500"><?= e(old('note')) ?></textarea></div>
        </div>
      <?php elseif ($isWord): ?>
        <div class="form">
          <div class="fgrid">
            <div class="field"><label for="w"><?php _e('Leb Lango word'); ?> *</label><input id="w" type="text" name="word" required maxlength="190" value="<?= e($prefWord) ?>" placeholder="<?= e(__('e.g. the name of an animal, a place, a food')) ?>"></div>
            <div class="field"><label for="m"><?php _e('English meaning'); ?> *</label><input id="m" type="text" name="meaning" required maxlength="400" value="<?= e(old('meaning')) ?>" placeholder="<?= e(__('e.g. wild cat')) ?>"></div>
            <div class="field"><label for="wc"><?php _e('What kind of word is it?'); ?></label><select id="wc" name="word_cat"><option value=""><?php _e('Not sure'); ?></option><?php foreach (WORD_CATS as $k => $l): ?><option value="<?= e($k) ?>" <?= old('word_cat') === $k ? 'selected' : '' ?>><?= e(__($l)) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="wp"><?php _e('Part of speech'); ?></label><select id="wp" name="word_pos"><option value=""><?php _e('Not sure'); ?></option><?php foreach (WORD_POS as $k => $l): ?><option value="<?= e($k) ?>" <?= old('word_pos') === $k ? 'selected' : '' ?>><?= e(__($l)) ?></option><?php endforeach; ?></select></div>
          </div>
          <div class="field"><label for="ex"><?php _e('A sentence using the word (optional)'); ?></label><input id="ex" type="text" name="example_lg" maxlength="500" value="<?= e(old('example_lg')) ?>"></div>
          <details class="more"><summary><?= icon('mic') ?> <?php _e('Add a recording of how it is pronounced'); ?></summary><div class="field"><input type="file" name="media_audio" accept="audio/*"><small>MP3, WAV, M4A · 40 MB</small></div></details>
        </div>
      <?php else: ?>
        <div class="form">
          <div class="field"><label for="t"><?php _e('Title'); ?> *</label><input id="t" type="text" name="title" required maxlength="200" value="<?= e(old('title')) ?>" placeholder="<?= e(__($type === 'natural' ? 'e.g. Grey crowned crane at the wetland near Lira' : 'Give it a short, clear title')) ?>"></div>
          <div class="field"><label for="d"><?php _e('Tell us about it'); ?> *</label><textarea id="d" name="description" required minlength="10" rows="6"><?= e(old('description')) ?></textarea><small><?php _e('Who, what, where, when and how. The more detail, the easier it is to verify.'); ?></small></div>
          <div class="fgrid">
            <div class="field"><label for="di"><?php _e('District'); ?></label><select id="di" name="district_id"><option value=""><?php _e('Select…'); ?></option><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>" <?= (int)old('district_id') === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="lt"><?php _e('Village, parish or place'); ?></label><input id="lt" type="text" name="location_text" value="<?= e(old('location_text')) ?>"></div>
          </div>
          <details class="more"><summary><?= icon('camera') ?> <?php _e('Add a photo, recording, video or document'); ?></summary>
            <div class="fgrid" style="margin-top:14px">
              <div class="field"><label><?= icon('camera') ?> <?php _e('Photo'); ?></label><input type="file" name="media_image" accept="image/*"><small>JPG, PNG, WebP · 12 MB</small></div>
              <div class="field"><label><?= icon('mic') ?> <?php _e('Audio recording'); ?></label><input type="file" name="media_audio" accept="audio/*"><small>MP3, WAV, M4A · 40 MB</small></div>
              <div class="field"><label><?= icon('video') ?> <?php _e('Video'); ?></label><input type="file" name="media_video" accept="video/*"><small>MP4, WebM · 120 MB</small></div>
              <div class="field"><label><?= icon('file') ?> <?php _e('Document'); ?></label><input type="file" name="media_document" accept=".pdf,.doc,.docx,.txt"><small>PDF, DOCX · 25 MB</small></div>
            </div>
            <p class="dim" style="margin:8px 0 12px"><?php _e('One file per contribution; the first one you choose is used.'); ?></p>
            <div class="field"><label for="ob"><?php _e('When was it? (date)'); ?></label><input id="ob" type="date" name="observed_on" max="<?= date('Y-m-d') ?>" style="max-width:240px"></div>
            <div><button type="button" id="gps" class="btn btn--outline btn--sm"><?= icon('pin') ?> <?php _e('Use my current location (GPS)'); ?></button> <span id="gpsout" class="dim" style="margin-left:8px;font-size:.9rem"></span>
              <input type="hidden" name="lat" id="lat"><input type="hidden" name="lng" id="lng"></div>
          </details>
        </div>
      <?php endif; ?>
    </div>

    <div class="cform__sec">
      <h2><span class="num">2</span> <?php _e('About you'); ?></h2>
      <?php if ($u): ?>
        <p class="alert alert-info" style="margin-bottom:14px"><?php _e('Signed in as'); ?> <b><?= e($u['name']) ?></b>. <?php _e('This will be added to your profile and points.'); ?></p>
      <?php else: ?>
        <div class="fgrid form">
          <div class="field"><label for="nm"><?php _e('Your name'); ?> *</label><input id="nm" type="text" name="name" required maxlength="150" value="<?= e(old('name')) ?>" autocomplete="name"></div>
          <div class="field"><label for="em"><?php _e('Email (for updates)'); ?></label><input id="em" type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email"></div>
        </div>
      <?php endif; ?>
      <div class="form" style="margin-top:16px">
        <div class="field"><label for="clan"><?php _e('Your clan'); ?> *</label><?= clan_select('clan', $u ? ($u['clan_id'] ?? null) : (old('clan_id') ?: null), true, (string)old('clan_other', $u['clan_other'] ?? '')) ?><small><?php _e('Pick the clan you belong to. If it is not in the list, choose “My clan is not listed” and type its name — we will add it.'); ?></small></div>
      </div>
      <?php if (!$u): ?>
        <div class="acct" data-acct>
          <label class="check"><input type="checkbox" name="make_account" value="1" <?= old('make_account') ? 'checked' : '' ?>> <span><b><?php _e('Create my free account'); ?></b> — <?php _e('track this and every future contribution, and earn points and badges.'); ?></span></label>
          <div class="field acct__pw"><label for="pw"><?php _e('Choose a password'); ?></label><input id="pw" type="password" name="password" minlength="8" autocomplete="new-password"><small><?php _e('At least 8 characters. We will use the email above for your sign-in.'); ?></small></div>
        </div>
      <?php endif; ?>
    </div>

    <div class="cform__sec cform__sec--last">
      <h2><span class="num">3</span> <?php _e('Send it'); ?></h2>
      <div class="form">
        <?php if ($isWord): ?>
          <label class="check"><input type="checkbox" name="credit" value="1" <?= (!is_post() || old('credit') === '1') ? 'checked' : '' ?>> <span><?php _e('Show my first name and clan beside the word so I get the credit.'); ?></span></label>
        <?php else: ?>
          <div class="field"><label for="rt"><?php _e('How may we use your contribution?'); ?></label>
            <select id="rt" name="rights"><option value="retain"><?php _e('I keep all rights — LCI may display it in the archive'); ?></option><option value="cc_by"><?php _e('Share openly with credit (CC BY)'); ?></option><option value="lci"><?php _e('Give LCI full rights to use it'); ?></option></select></div>
        <?php endif; ?>
        <label class="check"><input type="checkbox" name="consent" value="1" required> <span><?php _e('I confirm this is my own knowledge or work, or I have permission to share it, and I agree to the'); ?> <a href="<?= e(url('terms')) ?>" target="_blank"><?php _e('terms'); ?></a>.</span></label>
        <div><button class="btn btn--primary"><?= icon('upload') ?> <?= $isProv ? e(__('Submit proverb')) : ($isWord ? e(__('Submit word')) : e(__('Submit contribution'))) ?></button></div>
      </div>
    </div>
  </form>

  <aside class="aside">
    <div class="box"><h4><?php _e('What happens next'); ?></h4>
      <ol class="nextsteps"><li><?php _e('LCI reviewers are notified straight away.'); ?></li><li><?= $isWord ? e(__('They check the spelling and meaning, and may ask you for more detail.')) : e(__('They check the details, and may ask you for more.')) ?></li><li><?= $isWord ? e(__('Approved entries join the public dictionary and the AI translator.')) : e(__('Approved entries join the heritage archive.')) ?></li></ol></div>
    <?php if (!$u): ?><div class="box"><h4><?php _e('Already have an account?'); ?></h4><p style="font-size:.9rem"><?php _e('Sign in so this contribution is saved to your profile.'); ?></p><a class="btn btn--outline btn--sm" href="<?= e(url('account/login', ['next' => $self])) ?>"><?php _e('Sign in'); ?></a></div><?php endif; ?>
    <div class="box"><h4><?php _e('Something else to share?'); ?></h4>
      <p style="margin:0;font-size:.9rem"><a href="<?= e(url('contribute')) ?>"><?php _e('Choose a different type of contribution'); ?></a><br><a href="<?= e(url('contribute/track')) ?>"><?php _e('Track a contribution you already sent'); ?></a></p></div>
  </aside>
</div></section>
<?php page_end(); ?>
