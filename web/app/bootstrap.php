<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
mb_internal_encoding('UTF-8');
date_default_timezone_set('Africa/Kampala');

require_once __DIR__ . '/lib/base_guess.php';
$cfgFile = ROOT . '/config/config.php';
if (!is_file($cfgFile)) {
    header('Location: ' . lci_guess_base() . '/install.php');
    exit;
}
$CFG = require $cfgFile;
// Where the site is served from ('' at https://myculture.online, '/langoculturalinstitution' on XAMPP). Worked out from the request itself, so a
// config.php written under another address (an installer run before this was fixed, a copy from another computer) can never put "/web" or a
// folder name into every link. The configured value is only used where there is no request (command line scripts).
define('BASE', PHP_SAPI === 'cli' ? rtrim((string)($CFG['base'] ?? ''), '/') : lci_guess_base());
define('DEBUG', !empty($CFG['debug']));

if (DEBUG) { error_reporting(E_ALL); ini_set('display_errors', '1'); }
else { error_reporting(E_ALL & ~E_DEPRECATED); ini_set('display_errors', '0'); }

// The mobile app's API (/api/v1/) is stateless: it sends a Bearer token, so no session cookie is created for it.
define('API_V1', str_contains((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/api/v1/'));
if (API_V1) $_SESSION = [];
elseif (session_status() === PHP_SESSION_NONE) {
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
