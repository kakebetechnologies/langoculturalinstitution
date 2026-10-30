<?php
declare(strict_types=1);

define('LCI_ADMIN', true);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/layout.php';
require dirname(__DIR__) . '/app/lib/content.php';
require __DIR__ . '/lib.php';
require __DIR__ . '/screens.php';
require __DIR__ . '/screens_community.php';
require __DIR__ . '/screens_clans.php';
require __DIR__ . '/screens_media.php';

header('Cache-Control: no-store');
$m = get('m', 'dashboard');
$a = get('a');

/* ---------- logout ---------- */
if ($a === 'logout' && is_post()) { csrf_check(); audit('logout'); logout_user(); redirect(aurl()); }

/* ---------- login ---------- */
if (!user() || !is_staff()) {
    $err = '';
    if (is_post() && $a === 'login') {
        csrf_check();
        if (!throttle('adminlogin', 6, 900)) $err = 'Too many attempts. Please wait 15 minutes.';
        else {
            $row = login_user(post('email'), (string)($_POST['password'] ?? ''));
            if ($row && in_array($row['role'], STAFF_ROLES, true)) { audit('login'); redirect(aurl()); }
            if ($row) { logout_user(); $err = 'This account does not have admin access.'; }
            else $err = 'Incorrect email or password.';
        }
    }
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in · LCI Admin</title><meta name="robots" content="noindex">
    <link rel="icon" href="<?= e(logo_url() ?: asset('assets/img/favicon.svg')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>"></head>
    <body class="login"><div class="login__box">
      <?= logo_url() ? '<img class="login__logo" src="' . e(logo_url()) . '" alt="">' : '<span class="mark big">LCI</span>' ?>
      <h1>Lango Cultural Institution</h1><p class="dim">Admin control panel</p>
      <?php if ($err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endif; ?>
      <form method="post" action="<?= e(aurl('', 'login')) ?>"><?= csrf_field() ?>
        <div class="field"><label>Email</label><input type="email" name="email" required autofocus autocomplete="username"></div>
        <div class="field"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div>
        <button class="btn btn--primary btn--block">Sign in</button>
      </form>
      <p class="dim" style="margin-top:18px"><a href="<?= e(url()) ?>">← Back to the website</a></p>
    </div></body></html><?php
    exit;
}

/* ---------- force password change ---------- */
if (user()['must_change_password'] && $m !== 'profile') { flash('Please set a new password to continue.', 'info'); redirect(aurl('profile')); }

/* ---------- route ---------- */
$mods = modules();
if (isset($mods[$m])) {
    if (!can_module($m)) deny();
    crud_run($m, $mods[$m]);
    exit;
}
$special = ['dashboard', 'notifications', 'contributions', 'words', 'clan_quick', 'clan_requests', 'clan_messages', 'bible_import', 'settings', 'translations', 'audit', 'backup', 'profile'];
if (!in_array($m, $special, true)) { http_response_code(404); admin_start('Not found', ''); echo '<div class="panel"><h2>Page not found</h2></div>'; admin_end(); exit; }
if (!can_module($m)) deny();
('screen_' . $m)();
