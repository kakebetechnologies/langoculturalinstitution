<?php
declare(strict_types=1);
// Usage: php database/install-cli.php [--base=/langoculturalinstitution] [--db=lci] [--email=admin@example.org]
if (PHP_SAPI !== 'cli') exit('CLI only');
require __DIR__ . '/install_lib.php';

$opt = ['base' => '/langoculturalinstitution', 'db' => 'lci', 'email' => 'admin@langoculturalinstitution.local', 'host' => '127.0.0.1', 'user' => 'root', 'pass' => ''];
foreach (array_slice($argv, 1) as $a) if (preg_match('/^--([a-z]+)=(.*)$/', $a, $m)) $opt[$m[1]] = $m[2];

$password = rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'Kx'), '=');
$db = ['host' => $opt['host'], 'port' => 3306, 'name' => $opt['db'], 'user' => $opt['user'], 'pass' => $opt['pass']];
lci_install($db, ['name' => 'Site Administrator', 'email' => $opt['email'], 'password' => $password, 'must_change' => 1], $opt['base']);

$cfg = "<?php\nreturn " . var_export(['db' => $db, 'base' => $opt['base'], 'debug' => true], true) . ";\n";
file_put_contents(dirname(__DIR__) . '/config/config.php', $cfg);
file_put_contents(dirname(__DIR__) . '/config/first-run-credentials.txt',
    "LCI admin first-run login (delete this file after your first sign-in)\nURL:      {$opt['base']}/admin/\nEmail:    {$opt['email']}\nPassword: $password\n");
echo "Installed. Credentials saved to config/first-run-credentials.txt\n";
