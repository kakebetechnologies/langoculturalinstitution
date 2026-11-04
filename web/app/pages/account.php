<?php
declare(strict_types=1);

require_once ROOT . '/app/lib/app_api.php';   // brings in the Google sign-in helpers

$sub = $seg[1] ?? '';

if ($sub === 'logout') {
    if (is_post()) { csrf_check(); logout_user(); }
    redirect('');
}

/** Delete my account: the web address to give Google Play (and anyone) for deleting an account and its data. Public; signed-in members can do it right here. */
if ($sub === 'delete') {
    $me = user();
    $isMember = $me && ($me['role'] ?? '') === 'contributor';
    if (is_post() && $isMember) {
        csrf_check();
        if (!throttle('delete_account', 6, 900)) { flash(__('Too many attempts. Please wait 15 minutes.'), 'error'); redirect('account/delete'); }
        $bad = member_delete_check($me, (string)($_POST['password'] ?? ''), (string)($_POST['confirm_email'] ?? ''));
        if ($bad !== null) { flash(__($bad), 'error'); redirect('account/delete'); }
        $row = $me;
        logout_user();
        member_delete_account($row);
        flash(__('Your account has been deleted.'), 'success');
        redirect('');
    }
    page_start(__('Delete your account'), ['hero' => ['title' => __('Delete your account'), 'crumbs' => [[__('Account'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
    ?>
<section class="section"><div class="container" style="max-width:760px"><div class="prose">
  <p><?php _e('You can delete your Lango account and the personal data tied to it at any time.'); ?></p>
  <h2><?php _e('In the app'); ?></h2>
  <p><?php _e('Open Me, then Edit profile, scroll to Delete account and confirm.'); ?></p>
  <h2><?php _e('On this website'); ?></h2>
  <?php if ($isMember): $google = !empty($me['google_sub']); ?>
    <p><?php _e('Signed in as'); ?> <strong><?= e($me['email']) ?></strong>.</p>
    <form class="form" method="post" style="max-width:420px"><?= csrf_field() ?>
      <?php if ($google): ?>
        <div class="field"><label for="del-email"><?php _e('Type your email address to confirm'); ?></label><input id="del-email" type="email" name="confirm_email" required autocomplete="off"></div>
      <?php else: ?>
        <div class="field"><label for="del-pw"><?php _e('Enter your password to confirm'); ?></label><input id="del-pw" type="password" name="password" required autocomplete="current-password"></div>
      <?php endif; ?>
      <button class="btn btn--primary" onclick="return confirm('<?= e(__('Delete your account? This cannot be undone.')) ?>')"><?php _e('Delete my account'); ?></button>
    </form>
  <?php else: ?>
    <p><a class="btn btn--primary" href="<?= e(url('account/login', ['next' => 'account/delete'])) ?>"><?php _e('Sign in to delete your account'); ?></a></p>
    <p><?php _e('Cannot sign in? Contact us from the Contact page with the email address of your account and we will delete it.'); ?> <a href="<?= e(url('contact')) ?>"><?php _e('Contact us'); ?></a></p>
  <?php endif; ?>
  <p><?php _e('Want to delete only some of your data and keep your account?'); ?> <a href="<?= e(url('account/data-request')) ?>"><?php _e('Request data deletion'); ?></a>.</p>
  <h2><?php _e('What is deleted and what stays'); ?></h2>
  <ul>
    <li><?php _e('Deleted: your name, email, phone, country, clan, sign-in details, points, badges, streaks, quiz results, clan group messages, notification settings and sign-in tokens.'); ?></li>
    <li><?php _e('Stays: contributions already published in the Lango archive (words, photos, stories) stay, with no link to your name, because they belong to the archive. Ask us on the Contact page if you want one removed.'); ?></li>
  </ul>
</div></div></section>
<?php page_end(); return; }

/** Ask us to delete some or all of your data without deleting the account: the second web address Google Play's Data safety form asks for.
 *  Open to everyone (a person who cannot sign in can still ask); the request lands in Admin → Messages and is answered by email before anything is deleted. */
if ($sub === 'data-request') {
    $me = user();
    $choices = ['photos' => __('Photos I contributed'), 'audio' => __('Audio recordings I made'), 'stories' => __('Words, stories and other text I contributed'),
        'messages' => __('My clan group messages'), 'location' => __('Location details on my contributions'), 'all' => __('All my personal data (and my account)')];
    if (is_post()) {
        csrf_check(); keep_old($_POST);
        if (!empty($_POST['website'])) redirect('account/data-request');
        if (!throttle('data_request', 4, 1800)) { flash(__('Too many requests. Please try again later.'), 'error'); redirect('account/data-request'); }
        $name = post('name'); $email = trim(post('email')); $what = array_values(array_intersect(array_keys($choices), (array)($_POST['what'] ?? []))); $more = trim(post('details'));
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || (!$what && mb_strlen($more) < 5)) {
            flash(__('Please enter your name, a valid email, and choose what you want deleted (or describe it).'), 'error'); redirect('account/data-request');
        }
        $lines = ['Data deletion request (from /account/data-request)', 'Name: ' . $name, 'Email: ' . $email];
        if ($me) $lines[] = 'Signed in as member #' . (int)$me['id'] . ' (' . $me['email'] . ')';
        $lines[] = 'Delete: ' . ($what ? implode('; ', array_map(fn($k) => $choices[$k], $what)) : '(see details)');
        if ($more !== '') $lines[] = 'Details: ' . $more;
        $lines[] = 'Reply to the email above to confirm it is the account owner before deleting anything.';
        insert('messages', ['name' => mb_substr($name, 0, 150), 'email' => $email, 'subject' => 'Data deletion request', 'body' => mb_substr(implode("\n", $lines), 0, 5000)]);
        clear_old();
        flash(__('Thank you. We have your request. We will reply to your email to confirm it is you, then delete the data.'));
        redirect('account/data-request');
    }
    page_start(__('Request data deletion'), ['hero' => ['title' => __('Request data deletion'), 'crumbs' => [[__('Account'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
    ?>
<section class="section"><div class="container" style="max-width:760px"><div class="prose">
  <p><?php _e('Use this form to ask us to delete some or all of the personal data we hold about you, without having to delete your whole account. You do not need to be signed in.'); ?></p>
  <p><?php _e('Want to delete your whole account instead?'); ?> <a href="<?= e(url('account/delete')) ?>"><?php _e('Delete your account'); ?></a>.</p>
  <form class="formcard form" method="post"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
    <div class="fgrid">
      <div class="field"><label for="dr-name"><?php _e('Your name'); ?> *</label><input id="dr-name" type="text" name="name" required value="<?= e($me['name'] ?? old('name')) ?>"></div>
      <div class="field"><label for="dr-email"><?php _e('Email of your Lango account'); ?> *</label><input id="dr-email" type="email" name="email" required value="<?= e($me['email'] ?? old('email')) ?>"></div>
    </div>
    <div class="field"><label><?php _e('What should we delete?'); ?></label>
      <?php foreach ($choices as $k => $label): ?><label style="display:flex;gap:10px;align-items:flex-start;margin:7px 0;font-weight:500"><input type="checkbox" name="what[]" value="<?= e($k) ?>" style="margin-top:5px"><span><?= e($label) ?></span></label><?php endforeach; ?>
    </div>
    <div class="field"><label for="dr-details"><?php _e('Anything else we should know (optional)'); ?></label><textarea id="dr-details" name="details" style="min-height:120px"><?= e(old('details')) ?></textarea></div>
    <div><button class="btn btn--primary"><?php _e('Send request'); ?></button></div>
  </form>
  <h2><?php _e('What happens next'); ?></h2>
  <ul>
    <li><?php _e('We reply to the email above to check that the request comes from the owner of the account.'); ?></li>
    <li><?php _e('Then we delete what you asked for. Contributions already published in the Lango archive stay without your name unless you ask for a specific one to be removed.'); ?></li>
  </ul>
</div></div></section>
<?php page_end(); return; }

/** The two-column sign-in / sign-up page: a photo panel that says what an account gives, and the form. */
function auth_page_start(string $title, string $eyebrow, string $heading, string $lead, array $perks): void
{
    page_start($title, ['class' => 'auth']);
    echo '<section class="au"><div class="container au__in"><aside class="au__side" style="--au-img:url(' . e(asset('assets/img/photos/hero-field.jpg')) . ')">';
    echo '<span class="au__eyebrow">' . e($eyebrow) . '</span><h1>' . e($heading) . '</h1><p>' . e($lead) . '</p><ul class="au__perks">';
    foreach ($perks as $p) echo '<li>' . icon('check') . '<span>' . e($p) . '</span></li>';
    echo '</ul></aside><div class="au__card">';
}
/** "Continue with Google" with an "or with your email" line; empty until the site has Google keys (Admin → Site settings). */
function google_button(string $label): string
{
    if (!google_enabled()) return '';
    $next = safe_next((string)get('next'));
    $g = '<svg viewBox="0 0 48 48" width="20" height="20" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>';
    return '<a class="btn-google" href="' . e(url('auth/google/web', $next !== '' ? ['next' => $next] : [])) . '">' . $g . '<span>' . e($label) . '</span></a><div class="au__or"><span>' . e(__('or with your email')) . '</span></div>';
}

function auth_page_end(): void
{
    echo '</div></div></section>';
    page_end();
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
    auth_page_start(__('Sign in'), __('Welcome back'), __('See your culture'), __('Sign in to your own page: your clan, the word of the day, events and everything you have shared.'),
        [__('Your clan and your culture in one place'), __('Follow your contributions from start to approval'), __('Points and badges for what you share')]);
    ?>
      <h2><?php _e('Sign in'); ?></h2>
      <p class="au__sub"><?php _e('New here?'); ?> <a href="<?= e(url('account/register', get('next') ? ['next' => get('next')] : [])) ?>"><?php _e('Create a free account'); ?></a></p>
      <?= google_button(__('Sign in with Google')) ?>
      <form class="form" method="post"><?= csrf_field() ?>
        <div class="field"><label for="au-email"><?php _e('Email'); ?></label><input id="au-email" type="email" name="email" required autofocus autocomplete="username"></div>
        <div class="field"><label for="au-pw"><?php _e('Password'); ?></label><input id="au-pw" type="password" name="password" required autocomplete="current-password"></div>
        <button class="btn btn--primary btn--lg btn--block"><?php _e('Sign in'); ?></button>
      </form>
    <?php auth_page_end(); return;
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
            'country' => post('country') ?: null, 'phone' => post('phone') ?: null, 'culture' => community()]);
        clear_old();
        login_user($email, $pw);
        flash(__('Welcome to the Lango community!'));
        redirect($next ?: 'account');
    }
    auth_page_start(__('Create an account'), __('Free for everyone'), __('Join your culture'), __('Make an account to find your clan and your culture, and to add what you know to the Lango archive.'),
        [__('Your own “My culture” page'), __('Register under your clan'), __('Suggest words, share photos and stories'), __('Earn points and badges')]);
    ?>
      <h2><?php _e('Create your account'); ?></h2>
      <p class="au__sub"><?php _e('Already have an account?'); ?> <a href="<?= e(url('account/login', get('next') ? ['next' => get('next')] : [])) ?>"><?php _e('Sign in'); ?></a></p>
      <?= google_button(__('Sign up with Google')) ?>
      <form class="form" method="post"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
        <div class="field"><label><?php _e('Full name'); ?></label><input type="text" name="name" required autocomplete="name" value="<?= e(old('name')) ?>"></div>
        <div class="field"><label><?php _e('Email'); ?></label><input type="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>"></div>
        <div class="field"><label for="rc"><?php _e('Your clan'); ?> *</label><?= clan_select('rc', old('clan_id') ?: null, true, (string)old('clan_other')) ?><small><?php _e('Not in the list? Choose “My clan is not listed” and type its name — we will add it.'); ?></small></div>
        <div class="fgrid"><div class="field"><label><?php _e('Country'); ?></label><input type="text" name="country" value="<?= e(old('country', 'Uganda')) ?>"></div>
          <div class="field"><label><?php _e('Phone (optional)'); ?></label><input type="tel" name="phone" autocomplete="tel" value="<?= e(old('phone')) ?>"></div></div>
        <div class="field"><label><?php _e('Password'); ?></label><input type="password" name="password" required minlength="8" autocomplete="new-password"><small><?php _e('At least 8 characters.'); ?></small></div>
        <button class="btn btn--primary btn--lg btn--block"><?php _e('Create account'); ?></button>
        <p class="au__fine"><?php _e('By creating an account you agree to our'); ?> <a href="<?= e(url('terms')) ?>"><?php _e('Terms'); ?></a> <?php _e('and'); ?> <a href="<?= e(url('privacy')) ?>"><?php _e('Privacy policy'); ?></a>.</p>
      </form>
    <?php auth_page_end(); return;
}

if ($sub === 'google') {
    if (user()) redirect('account');
    $g = $_SESSION['gw_new'] ?? null;
    if (!is_array($g) || (int)$g['e'] < time()) { unset($_SESSION['gw_new']); flash(__('Your Google sign-in has expired. Please try again.'), 'error'); redirect('account/login'); }
    if (is_post()) {
        csrf_check();
        if (!throttle('google_finish', 8, 900)) { flash(__('Too many attempts. Please try later.'), 'error'); redirect('account/google'); }
        [$clanId, $clanOther] = read_clan_input();
        $err = null;
        if (post('clan_id') === '') $err = __('Please choose your clan, or “My clan is not listed”.');
        elseif (post('clan_id') === 'other' && !$clanOther) $err = __('Please type the name of your clan.');
        elseif (val('SELECT COUNT(*) FROM users WHERE email=? OR google_sub=?', [$g['email'], $g['sub']])) $err = __('There is already an account for this Google address. Please continue with Google again.');
        if ($err) { flash($err, 'error'); redirect('account/google'); }
        $name = trim(post('name')) ?: (string)$g['name'] ?: (string)strstr((string)$g['email'], '@', true);
        $uid = insert('users', ['name' => mb_substr($name, 0, 150), 'email' => $g['email'], 'password_hash' => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT), 'role' => 'contributor',
            'clan_id' => $clanId, 'clan_other' => $clanOther, 'google_sub' => $g['sub'], 'culture' => community()]);
        unset($_SESSION['gw_new']);
        login_member(one('SELECT * FROM users WHERE id=?', [$uid]));
        flash(__('Welcome to the Lango community!'));
        redirect((string)$g['x'] ?: 'account');
    }
    auth_page_start(__('Choose your clan'), __('One more step'), __('Choose your clan'), __('Every member belongs to a clan. Choose yours to finish creating your account.'),
        [__('Your own “My culture” page'), __('Register under your clan'), __('Suggest words, share photos and stories')]);
    ?>
      <h2><?php _e('Almost there'); ?></h2>
      <p class="au__sub"><?= e($g['email']) ?></p>
      <form class="form" method="post"><?= csrf_field() ?>
        <div class="field"><label><?php _e('Full name'); ?></label><input type="text" name="name" required autocomplete="name" value="<?= e($g['name']) ?>"></div>
        <div class="field"><label for="rc"><?php _e('Your clan'); ?> *</label><?= clan_select('rc', null, true) ?><small><?php _e('Not in the list? Choose “My clan is not listed” and type its name — we will add it.'); ?></small></div>
        <button class="btn btn--primary btn--lg btn--block"><?php _e('Finish creating my account'); ?></button>
        <p class="au__fine"><?php _e('By creating an account you agree to our'); ?> <a href="<?= e(url('terms')) ?>"><?php _e('Terms'); ?></a> <?php _e('and'); ?> <a href="<?= e(url('privacy')) ?>"><?php _e('Privacy policy'); ?></a>.</p>
      </form>
    <?php auth_page_end(); return;
}

/* ---------- My culture (signed in) ---------- */
if (!user()) redirect('account/login');
$u = user();

if ($sub === 'clan' && is_post()) {
    csrf_check();
    if (post('clan_id') === '') { flash(__('Please choose your clan, or “My clan is not listed”.'), 'error'); redirect('account'); }
    [$clanId, $clanOther] = read_clan_input();
    if (post('clan_id') === 'other' && !$clanOther) { flash(__('Please type the name of your clan.'), 'error'); redirect('account'); }
    q('UPDATE users SET clan_id=?, clan_other=? WHERE id=?', [$clanId, $clanOther, $u['id']]);
    flash(__('Your clan has been saved.'));
    redirect('account');
}

$mine = all('SELECT * FROM contributions WHERE user_id=? ORDER BY created_at DESC', [$u['id']]);
$published = count(array_filter($mine, fn($c) => $c['status'] === 'published'));
[$badge] = badge_for_points((int)$u['points']);
$lead = all("SELECT name, points FROM users WHERE points>0 AND role='contributor' ORDER BY points DESC LIMIT 5");
$clan = $u['clan_id'] ? one("SELECT name, slug, totem, summary, verified FROM clans WHERE id=? AND status='published'", [$u['clan_id']]) : null;
$clanOther = (string)($u['clan_other'] ?? '');
$wotd = word_of_day();
$next = one("SELECT * FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT 1");
$first = trim((string)strtok((string)$u['name'], ' ')) ?: (string)$u['name'];
$initials = mb_strtoupper(mb_substr($first, 0, 1) . mb_substr(trim((string)strstr((string)$u['name'], ' ')), 0, 1));
$greet = (int)date('G') < 12 ? __('Good morning') : ((int)date('G') < 17 ? __('Good afternoon') : __('Good evening'));

page_start(__('My culture'), ['class' => 'mc']);
?>
<section class="mc-top"><div class="container">
  <div class="mc-head">
    <span class="mc-avatar" aria-hidden="true"><?= e($initials) ?></span>
    <div class="mc-head__txt">
      <span class="eyebrow"><?= e($greet) ?></span>
      <h1><?= e($first) ?></h1>
      <p><?= e($badge) ?> · <?= (int)$u['points'] ?> <?php _e('points'); ?><?php if ($clan): ?> · <?= e($clan['name']) ?> <?php _e('clan'); ?><?php elseif ($clanOther !== ''): ?> · <?= e($clanOther) ?> <?php _e('clan'); ?><?php endif; ?></p>
    </div>
    <div class="mc-head__act">
      <a class="btn btn--primary" href="<?= e(url('contribute')) ?>"><?= icon('camera') ?> <?php _e('Contribute'); ?></a>
      <?php if (is_staff()): ?><a class="btn btn--dark" href="<?= e(url('admin/')) ?>"><?= icon('settings') ?> <?php _e('Admin panel'); ?></a><?php endif; ?>
    </div>
  </div>
  <ul class="mc-stats">
    <li><b><?= count($mine) ?></b><span><?php _e('Contributions'); ?></span></li>
    <li><b><?= $published ?></b><span><?php _e('Published'); ?></span></li>
    <li><b><?= (int)$u['points'] ?></b><span><?php _e('Points'); ?></span></li>
    <li><b><?= (int)$u['best_streak'] ?></b><span><?php _e('Best learning streak (days)'); ?></span></li>
  </ul>
</div></section>

<section class="section mc-body"><div class="container mc-grid">
  <div class="mc-main">
    <h2><?php _e('Your culture today'); ?></h2>
    <div class="mc-cards">
      <div class="mc-card mc-card--clan">
        <span class="mc-card__k"><?= icon('users') ?> <?php _e('Your clan'); ?></span>
        <?php if ($clan): ?>
          <h3><?= e($clan['name']) ?></h3>
          <?php if (!empty($clan['totem'])): ?><p class="mc-card__meta"><?php _e('Totem'); ?>: <?= e($clan['totem']) ?></p><?php endif; ?>
          <?php if (!empty($clan['summary'])): ?><p><?= e(excerpt((string)$clan['summary'], 150)) ?></p><?php endif; ?>
          <a class="link" href="<?= e(url('clans/' . $clan['slug'])) ?>"><?php _e('Open the clan page'); ?> <?= icon('arrow') ?></a>
        <?php elseif ($clanOther !== ''): ?>
          <h3><?= e($clanOther) ?></h3>
          <p><?php _e('Your clan is not in the register yet. We will check the name and add it.'); ?></p>
        <?php else: ?>
          <h3><?php _e('Choose your clan'); ?></h3>
          <form class="form mc-clanform" method="post" action="<?= e(url('account/clan')) ?>"><?= csrf_field() ?>
            <?= clan_select('mc-clan', null, true) ?>
            <button class="btn btn--dark btn--sm"><?php _e('Save my clan'); ?></button>
          </form>
        <?php endif; ?>
      </div>
      <?php if ($wotd): ?>
      <a class="mc-card mc-card--word" href="<?= e(url('language/dictionary')) ?>">
        <span class="mc-card__k"><?= icon('book') ?> <?php _e('Word of the day'); ?></span>
        <h3><?= e($wotd['lango']) ?></h3>
        <p><?= e(str_replace(';', ' ·', (string)$wotd['english'])) ?></p>
        <span class="link"><?php _e('Open the dictionary'); ?> <?= icon('arrow') ?></span>
      </a>
      <?php endif; ?>
      <?php if ($next): $ts = strtotime((string)$next['starts_at']); ?>
      <a class="mc-card mc-card--event" href="<?= e(url('events/' . $next['slug'])) ?>">
        <span class="mc-card__k"><?= icon('calendar') ?> <?php _e('Next event'); ?></span>
        <h3><?= e(t($next, 'title')) ?></h3>
        <p><?= e(date('j M Y', $ts)) ?><?= $next['location'] ? ' · ' . e((string)$next['location']) : '' ?></p>
        <span class="link"><?php _e('See the event'); ?> <?= icon('arrow') ?></span>
      </a>
      <?php endif; ?>
    </div>

    <h2><?php _e('Keep exploring'); ?></h2>
    <div class="mc-tiles">
      <a href="<?= e(url('language/dictionary')) ?>"><?= icon('book') ?><span><?php _e('Dictionary'); ?></span></a>
      <a href="<?= e(url('clans')) ?>"><?= icon('users') ?><span><?php _e('Clans'); ?></span></a>
      <a href="<?= e(url('heritage')) ?>"><?= icon('globe') ?><span><?php _e('Heritage'); ?></span></a>
      <a href="<?= e(url('library')) ?>"><?= icon('file') ?><span><?php _e('Library'); ?></span></a>
      <a href="<?= e(url('events')) ?>"><?= icon('calendar') ?><span><?php _e('Events'); ?></span></a>
      <a href="<?= e(url('assistant')) ?>"><?= icon('chat') ?><span><?php _e('Assistant'); ?></span></a>
    </div>

    <h2><?php _e('My contributions'); ?></h2>
    <?php if ($mine): ?><div class="table-wrap"><table class="table"><thead><tr><th><?php _e('Title'); ?></th><th><?php _e('Type'); ?></th><th><?php _e('Status'); ?></th><th><?php _e('Code'); ?></th></tr></thead><tbody>
      <?php foreach ($mine as $c): ?><tr><td><?= e($c['title']) ?></td><td><?= e(CONTRIB_TYPES[$c['type']][0]) ?></td><td><span class="tag <?= in_array($c['status'], ['approved', 'published'], true) ? 'tag--green' : ($c['status'] === 'rejected' ? 'tag--red' : 'tag--gold') ?>"><?= e(__(CONTRIB_STATUS[$c['status']])) ?></span></td><td><a href="<?= e(url('contribute/track', ['code' => $c['code']])) ?>"><?= e($c['code']) ?></a></td></tr><?php endforeach; ?></tbody></table></div>
    <?php else: echo empty_state(__('No contributions yet'), __('Your first approved contribution earns the “First Contributor” badge.'), 'camera', '<p><a class="btn btn--primary btn--sm" href="' . e(url('contribute')) . '">' . e(__('Contribute now')) . '</a></p>'); endif; ?>
  </div>

  <aside class="mc-aside">
    <div class="box"><h4><?= icon('award') ?> <?php _e('Top contributors'); ?></h4><?php foreach ($lead as $i => $l): ?><p style="margin:6px 0"><b><?= $i + 1 ?>.</b> <?= e($l['name']) ?> <small style="color:var(--muted)">· <?= (int)$l['points'] ?></small></p><?php endforeach; if (!$lead) echo '<p style="color:var(--muted)">' . e(__('The leaderboard starts with the first approved contribution.')) . '</p>'; ?></div>
    <div class="box mc-app"><h4><?= icon('phone-app') ?> <?php _e('Take it with you'); ?></h4><p><?php _e('The Lango App has the same dictionary, clans and events, and works with little data.'); ?></p><a class="btn btn--outline btn--sm btn--block" href="<?= e(url('') . '#app') ?>"><?= icon('download') ?> <?php _e('Get the app'); ?></a></div>
    <div class="box"><form method="post" action="<?= e(url('account/logout')) ?>"><?= csrf_field() ?><button class="btn btn--outline btn--block"><?= icon('logout') ?> <?php _e('Sign out'); ?></button></form></div>
  </aside>
</div></section>
<?php page_end(); ?>
