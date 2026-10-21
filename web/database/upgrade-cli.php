<?php
declare(strict_types=1);
// Usage: php database/upgrade-cli.php   (applies schema upgrades and loads starter content into the configured database)
if (PHP_SAPI !== 'cli') exit('CLI only');
require __DIR__ . '/upgrade.php';
$cfg = require dirname(__DIR__) . '/config/config.php';
$d = $cfg['db'];
$pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $d['host'], $d['port'] ?? 3306, $d['name']), $d['user'], $d['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$log = lci_upgrade($pdo);
echo $log ? implode("\n", $log) . "\n" : "Nothing to change.\n";
