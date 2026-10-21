<?php
declare(strict_types=1);
// Web installer for first-time setup on a new server (XAMPP or hosting).
require __DIR__ . '/database/install_lib.php';

$cfgFile = __DIR__ . '/config/config.php';
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$installed = is_file($cfgFile);
$err = ''; $done = false;
$v = ['host' => '127.0.0.1', 'port' => '3306', 'user' => 'root', 'name' => 'lci', 'aname' => '', 'aemail' => ''];

if (!$installed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    $pass = (string)($_POST['pass'] ?? '');
    $apw = (string)($_POST['apass'] ?? '');
    try {
        if ($v['aname'] === '' || !filter_var($v['aemail'], FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter the administrator name and a valid email.');
        if (strlen($apw) < 10) throw new RuntimeException('Choose an administrator password of at least 10 characters.');
        $db = ['host' => $v['host'], 'port' => (int)$v['port'], 'name' => $v['name'], 'user' => $v['user'], 'pass' => $pass];
        lci_install($db, ['name' => $v['aname'], 'email' => $v['aemail'], 'password' => $apw], $base, !empty($_POST['samples']));
        file_put_contents($cfgFile, "<?php\nreturn " . var_export(['db' => $db, 'base' => $base, 'debug' => false], true) . ";\n");
        $done = true;
    } catch (Throwable $e) { $err = $e->getMessage(); }
}
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install — Lango Cultural Institution</title>
<style>
body{font:16px/1.5 system-ui,sans-serif;background:#1c120d;color:#1c120d;margin:0;display:grid;place-items:center;min-height:100vh;padding:24px}
.box{background:#fbf6ea;max-width:520px;width:100%;border-radius:14px;padding:32px;border-top:8px solid #d99a1e}
h1{font-family:Georgia,serif;margin:0 0 4px}label{display:block;font-weight:600;margin:14px 0 4px}
input{width:100%;padding:10px;border:1px solid #cdbb98;border-radius:8px;font:inherit;box-sizing:border-box}
button{margin-top:22px;background:#a8431f;color:#fff;border:0;padding:12px 22px;border-radius:8px;font:inherit;font-weight:700;cursor:pointer}
.row{display:grid;grid-template-columns:2fr 1fr;gap:12px}.err{background:#fde8e4;color:#8a1c0c;padding:10px 14px;border-radius:8px}
.ok{background:#e6f2e6;padding:12px 14px;border-radius:8px}small{color:#6d5a4c}
</style></head><body><div class="box">
<h1>Lango Cultural Institution</h1><p><small>Website &amp; admin control panel — installation</small></p>
<?php if ($installed && !$done): ?>
  <p class="ok">Already installed. To reinstall, delete <code>config/config.php</code> first.</p>
  <p><a href="<?= h($base) ?>/">Open the website</a> · <a href="<?= h($base) ?>/admin/">Admin panel</a></p>
<?php elseif ($done): ?>
  <p class="ok"><strong>Installed.</strong> You can now sign in to the admin panel.</p>
  <p><a href="<?= h($base) ?>/admin/">Go to the admin panel →</a></p>
<?php else: ?>
  <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
  <form method="post">
    <div class="row"><div><label>Database host</label><input name="host" value="<?= h($v['host']) ?>"></div><div><label>Port</label><input name="port" value="<?= h($v['port']) ?>"></div></div>
    <label>Database name <small>(created if missing)</small></label><input name="name" value="<?= h($v['name']) ?>">
    <label>Database user</label><input name="user" value="<?= h($v['user']) ?>">
    <label>Database password</label><input name="pass" type="password" autocomplete="off">
    <hr style="margin:22px 0;border:0;border-top:1px solid #e6d9bf">
    <label>Administrator name</label><input name="aname" value="<?= h($v['aname']) ?>">
    <label>Administrator email</label><input name="aemail" type="email" value="<?= h($v['aemail']) ?>">
    <label>Administrator password <small>(10+ characters)</small></label><input name="apass" type="password" autocomplete="new-password">
    <label style="font-weight:400;margin-top:18px"><input type="checkbox" name="samples" value="1" checked style="width:auto"> Include starter content (sample news, events and programmes)</label>
    <button>Install</button>
  </form>
<?php endif; ?>
</div></body></html>
