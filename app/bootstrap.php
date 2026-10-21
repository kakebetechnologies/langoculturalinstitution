<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
mb_internal_encoding('UTF-8');
date_default_timezone_set('Africa/Kampala');

$cfgFile = ROOT . '/config/config.php';
if (!is_file($cfgFile)) {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $base = preg_replace('#/admin$#', '', $base);
    header('Location: ' . $base . '/install.php');
    exit;
}
$CFG = require $cfgFile;
define('BASE', rtrim((string)($CFG['base'] ?? ''), '/'));
define('DEBUG', !empty($CFG['debug']));

if (DEBUG) { error_reporting(E_ALL); ini_set('display_errors', '1'); }
else { error_reporting(E_ALL & ~E_DEPRECATED); ini_set('display_errors', '0'); }

if (session_status() === PHP_SESSION_NONE) {
    session_name('lcisess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE === '' ? '/' : BASE . '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/i18n.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/upload.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
