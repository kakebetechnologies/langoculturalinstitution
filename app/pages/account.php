<?php
declare(strict_types=1);

$sub = $seg[1] ?? '';

if ($sub === 'logout') {
    if (is_post()) { csrf_check(); logout_user(); }
    redirect('');
}

if ($sub === 'login') {
    if (user()) redirect(is_staff() ? url('admin/') : 'account');
    if (is_post()) {
        csrf_check();
        if (!throttle('login', 8, 900)) { flash(__('Too many sign-in attempts. Please wait 15 minutes.'), 'error'); redirect('account/login'); }
        $next = safe_next(get('next'));
        if (login_user(post('email'), (string)($_POST['password'] ?? ''))) { audit('login'); redirect(is_staff() ? url('admin/') : ($next ?: 'account')); }
        flash(__('Incorrect email or password.'), 'error'); redirect('account/login' . ($next ? '?next=' . urlencode($next) : ''));
    }
    page_start(__('Sign in'), ['hero' => ['title' => __('Sign in'), 'crumbs' => [[__('Account'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:480px"><form class="formcard form" method="post"><?= csrf_field() ?>
      <div class="field"><label><?php _e('Email'); ?></label><input type="email" name="email" required autofocus></div>
      <div class="field"><label><?php _e('Password'); ?></label><input type="password" name="password" required></div>
      <button class="btn btn--primary btn--block"><?php _e('Sign in'); ?></button>
      <p style="text-align:center;margin:0"><?php _e('New here?'); ?> <a href="<?= e(url('account/register', get('next') ? ['next' => get('next')] : [])) ?>"><?php _e('Create a free account'); ?></a></p>
    </form></div></section>
    <?php page_end(); return;
}

if ($sub === 'register') {
    if (user()) redirect('account');
    if (is_post()) {
        csrf_check(); keep_old($_POST);
        if (!empty($_POST['website'])) redirect('');
        $next = safe_next(get('next'));
        $back = 'account/register' . ($next ? '?next=' . urlencode($next) : '');
        if (!throttle('register', 5, 1800)) { flash(__('Too many attempts. Please try later.'), 'error'); redirect($back); }
        $name = post('name'); $email = strtolower(post('email')); $pw = (string)($_POST['password'] ?? '');
        [$clanId, $clanOther] = read_clan_input();
        $err = null;
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = __('Please enter your name and a valid email.');
        elseif (post('clan_id') === '') $err = __('Please choose your clan, or “My clan is not listed”.');
        elseif (post('clan_id') === 'other' && !$clanOther) $err = __('Please type the name of your clan.');
        elseif (strlen($pw) < 8) $err = __('Choose a password of at least 8 characters.');
        elseif (val('SELECT COUNT(*) FROM users WHERE email=?', [$email])) $err = __('An account with this email already exists. Try signing in.');
        if ($err) { flash($err, 'error'); redirect($back); }
        insert('users', ['name' => mb_substr($name, 0, 150), 'email' => $email, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'role' => 'contributor', 'clan_id' => $clanId, 'clan_other' => $clanOther,
            'country' => post('country') ?: null, 'phone' => post('phone') ?: null]);
        clear_old();
        login_user($email, $pw);
        flash(__('Welcome to the Lango community!'));
        redirect($next ?: 'account');
    }
    page_start(__('Create an account'), ['hero' => ['title' => __('Join the community'), 'sub' => __('Earn points and badges, and track every contribution you make.'), 'crumbs' => [[__('Account'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:520px"><form class="formcard form" method="post"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
      <div class="field"><label><?php _e('Full name'); ?></label><input type="text" name="name" required value="<?= e(old('name')) ?>"></div>
      <div class="field"><label><?php _e('Email'); ?></label><input type="email" name="email" required value="<?= e(old('email')) ?>"></div>
      <div class="field"><label for="rc"><?php _e('Your clan'); ?> *</label><?= clan_select('rc', old('clan_id') ?: null, true, (string)old('clan_other')) ?><small><?php _e('Not in the list? Choose “My clan is not listed” and type its name — we will add it.'); ?></small></div>
      <div class="fgrid"><div class="field"><label><?php _e('Country'); ?></label><input type="text" name="country" value="<?= e(old('country', 'Uganda')) ?>"></div>
        <div class="field"><label><?php _e('Phone (optional)'); ?></label><input type="tel" name="phone" value="<?= e(old('phone')) ?>"></div></div>
      <div class="field"><label><?php _e('Password'); ?></label><input type="password" name="password" required minlength="8" autocomplete="new-password"><small><?php _e('At least 8 characters.'); ?></small></div>
      <button class="btn btn--primary btn--block"><?php _e('Create account'); ?></button>
      <p style="text-align:center;margin:0"><?php _e('Already have an account?'); ?> <a href="<?= e(url('account/login', get('next') ? ['next' => get('next')] : [])) ?>"><?php _e('Sign in'); ?></a></p>
    </form></div></section>
    <?php page_end(); return;
}

/* dashboard */
if (!user()) redirect('account/login');
$u = user();
$mine = all('SELECT * FROM contributions WHERE user_id=? ORDER BY created_at DESC', [$u['id']]);
$published = count(array_filter($mine, fn($c) => $c['status'] === 'published'));
[$badge] = badge_for_points((int)$u['points']);
$lead = all("SELECT name, points FROM users WHERE points>0 AND role='contributor' ORDER BY points DESC LIMIT 5");
$myClan = $u['clan_id'] ? (string)val('SELECT name FROM clans WHERE id=?', [$u['clan_id']]) : (string)($u['clan_other'] ?? '');
page_start(__('My account'), ['hero' => ['eyebrow' => __('Contributor profile'), 'title' => $u['name'], 'sub' => $badge . ' · ' . (int)$u['points'] . ' ' . __('points') . ($myClan !== '' ? ' · ' . $myClan . ' ' . __('clan') : ''), 'crumbs' => [[__('Account'), null]], 'image' => 'assets/img/photos/hero-field.jpg']]);
?>
<section class="section"><div class="container article">
  <div>
    <div class="grid g3" style="margin-bottom:34px"><div class="box"><b style="font:700 2.2rem var(--fd);color:var(--clay)"><?= count($mine) ?></b><br><?php _e('Contributions'); ?></div><div class="box"><b style="font:700 2.2rem var(--fd);color:var(--leaf)"><?= $published ?></b><br><?php _e('Published'); ?></div><div class="box"><b style="font:700 2.2rem var(--fd);color:var(--ochre)"><?= (int)$u['points'] ?></b><br><?php _e('Points'); ?></div></div>
    <h2 style="font-size:1.6rem"><?php _e('My contributions'); ?></h2>
    <?php if ($mine): ?><div class="table-wrap"><table class="table"><thead><tr><th><?php _e('Title'); ?></th><th><?php _e('Type'); ?></th><th><?php _e('Status'); ?></th><th><?php _e('Code'); ?></th></tr></thead><tbody>
      <?php foreach ($mine as $c): ?><tr><td><?= e($c['title']) ?></td><td><?= e(CONTRIB_TYPES[$c['type']][0]) ?></td><td><span class="tag <?= in_array($c['status'], ['approved', 'published'], true) ? 'tag--green' : ($c['status'] === 'rejected' ? 'tag--red' : 'tag--gold') ?>"><?= e(__(CONTRIB_STATUS[$c['status']])) ?></span></td><td><a href="<?= e(url('contribute/track', ['code' => $c['code']])) ?>"><?= e($c['code']) ?></a></td></tr><?php endforeach; ?></tbody></table></div>
    <?php else: echo empty_state(__('No contributions yet'), __('Your first approved contribution earns the “First Contributor” badge.'), 'camera', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute')) . '">' . e(__('Contribute now')) . '</a></p>'); endif; ?>
  </div>
  <aside class="aside">
    <div class="box"><h4><?= icon('award') ?> <?php _e('Top contributors'); ?></h4><?php foreach ($lead as $i => $l): ?><p style="margin:6px 0"><b><?= $i + 1 ?>.</b> <?= e($l['name']) ?> <small style="color:var(--muted)">· <?= (int)$l['points'] ?></small></p><?php endforeach; if (!$lead) echo '<p style="color:var(--muted)">' . e(__('The leaderboard starts with the first approved contribution.')) . '</p>'; ?></div>
    <div class="box"><?php if (is_staff()): ?><a class="btn btn--dark btn--block" href="<?= e(url('admin/')) ?>"><?= icon('settings') ?> <?php _e('Open admin panel'); ?></a><br><?php endif; ?>
      <form method="post" action="<?= e(url('account/logout')) ?>"><?= csrf_field() ?><button class="btn btn--outline btn--block"><?= icon('logout') ?> <?php _e('Sign out'); ?></button></form></div>
  </aside>
</div></section>
<?php page_end(); ?>
