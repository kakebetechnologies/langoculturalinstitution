<?php
declare(strict_types=1);

/**
 * Creates the database, tables, starter content and the first super-admin.
 * Used by install.php (web) and database/install-cli.php (command line).
 * Cultural facts are limited to what is published in the sources named in the content files;
 * clan names, proverbs and Leb Lango translations are intentionally NOT invented.
 */
require_once __DIR__ . '/upgrade.php';

function lci_install(array $db, array $admin, string $base, bool $withSamples = true): void
{
    $name = preg_replace('/[^A-Za-z0-9_]/', '', $db['name']);
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    try {
        // Shared hosting hands over a ready-made database whose user may not create databases, so use it directly when it exists.
        $pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $name), $db['user'], $db['pass'], $opts);
    } catch (PDOException $e) {
        if ((int)($e->errorInfo[1] ?? $e->getCode()) !== 1049) throw $e;   // 1049 = unknown database; anything else (wrong password, no server) is reported as it is
        $pdo = new PDO(sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']), $db['user'], $db['pass'], $opts);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");
    }
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    foreach (array_filter(array_map('trim', explode(';', (string)file_get_contents(__DIR__ . '/schema.sql')))) as $stmt) {
        if (preg_match('/^(--[^\n]*\n?)*$/', $stmt)) continue;
        $pdo->exec($stmt);
    }

    $ins = function (string $table, array $row) use ($pdo): int {
        $cols = array_keys($row);
        $st = $pdo->prepare("INSERT INTO `$table` (`" . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
        $st->execute(array_values($row));
        return (int)$pdo->lastInsertId();
    };
    $count = fn(string $t) => (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    // ----- admin -----
    if (!$count('users')) {
        $ins('users', [
            'name' => $admin['name'], 'email' => strtolower($admin['email']),
            'password_hash' => password_hash($admin['password'], PASSWORD_DEFAULT),
            'role' => 'super_admin', 'must_change_password' => (int)($admin['must_change'] ?? 0),
        ]);
    }

    // ----- settings -----
    if (!$count('settings')) {
        $set = [
            'site_name'    => 'Lango',
            'site_tagline' => 'Everything about Lango, in one place.',
            'org_subtitle' => '',
            'email'        => 'info@myculture.online',
            'phone'        => '',
            'address'      => '',
                        'facebook'     => '', 'x' => '', 'youtube' => '', 'instagram' => '', 'whatsapp' => '',
            'map_lat'      => '2.2499', 'map_lng' => '32.8998',
                        'app_status'   => 'coming_soon',
                                    'logo' => '', 'hero_image_1' => '', 'hero_image_2' => '', 'about_image_1' => '', 'about_image_2' => '', 'cta_image' => '',
        ];
        foreach ($set as $k => $v) $ins('settings', ['k' => $k, 'v' => $v]);
    }

    // ----- districts (approximate centre points, for the map) -----
    if (!$count('districts')) {
        $d = [['Lira', 2.2499, 32.8998], ['Alebtong', 2.2500, 33.2500], ['Amolatar', 1.6333, 32.8167], ['Apac', 1.9758, 32.5386],
              ['Dokolo', 1.9167, 33.1667], ['Kole', 2.4000, 32.8000], ['Kwania', 1.7000, 32.7000], ['Oyam', 2.2333, 32.3833], ['Otuke', 2.5000, 33.4800]];
        foreach ($d as $i => [$n, $la, $ln]) $ins('districts', ['name' => $n, 'slug' => strtolower($n), 'lat' => $la, 'lng' => $ln, 'sort' => $i]);
    }

    // ----- blog categories -----
    if (!$count('post_categories')) {
        foreach (['Announcements', 'Culture & Heritage', 'Youth Voices', 'Diaspora Stories', 'Conservation', 'Development'] as $i => $n) {
            $ins('post_categories', ['name' => $n, 'slug' => strtolower(preg_replace('/[^a-z]+/i', '-', $n)), 'sort' => $i]);
        }
    }

    // ----- heritage items (two dances, described in general terms) -----
    if (!$count('heritage_items') && $withSamples) {
        $ins('heritage_items', ['category' => 'dance', 'title' => 'Okeme', 'slug' => 'okeme', 'summary' => 'A traditional Lango dance.',
            'body' => "Okeme is one of the traditional dances of the Lango people.\n\nThis entry is a starting point. Clan elders, cultural experts and dancers can extend it with history, costume, instruments, songs and recordings through the admin panel or the Community Contribution Engine.",
            'featured' => 1, 'status' => 'published', 'image' => 'assets/img/photos/hero-site.jpg']);
        $ins('heritage_items', ['category' => 'dance', 'title' => 'Abuda', 'slug' => 'abuda', 'summary' => 'A traditional Lango dance.',
            'body' => "Abuda is one of the traditional dances of the Lango people.\n\nThis entry is a starting point to be enriched with verified history, performance details and media.",
            'status' => 'published', 'image' => 'assets/img/photos/hero-field.jpg']);
    }

    // ----- assistant knowledge base (facts from published sources) -----
    if (!$count('knowledge')) {
        $kb = [
            ['Clans', 'What is an Awitong?', 'Awitong is the title of a clan leader, who represents the clan.'],
            ['Geography', 'Which districts make up Lango?', 'The Lango sub-region is made up of nine districts: Lira, Alebtong, Amolatar, Apac, Dokolo, Kole, Kwania, Oyam and Otuke.'],
            ['Culture', 'What are the traditional dances of the Lango?', 'Traditional Lango dances include Okeme and Abuda.'],
            ['History', 'Where do the Lango people come from?', 'The Lango are a Nilotic ethnic group from mid-northern Uganda. Oral traditions trace their migration from the Bahr el Ghazal region of present-day South Sudan between the 15th and 17th centuries.'],
        ];
        foreach ($kb as [$tp, $qn, $an]) $ins('knowledge', ['topic' => $tp, 'question' => $qn, 'answer' => $an, 'reviewed' => 1]);
    }

    // ----- dictionary (imported from the Lango dictionary project; unverified until reviewed) -----
    $dictFile = __DIR__ . '/seed/lango_dictionary.json';
    if (!$count('dictionary') && is_file($dictFile)) {
        $data = json_decode((string)file_get_contents($dictFile), true) ?: [];
        $pdo->beginTransaction();
        $st = $pdo->prepare('INSERT INTO dictionary (lango, english, pos, source, verified, status) VALUES (?,?,?,?,0,?)');
        foreach ($data as $w) {
            $lango = trim((string)($w['lango'] ?? ''));
            $eng = implode('; ', array_map('trim', $w['english_meanings'] ?? []));
            if ($lango === '' || $eng === '') continue;
            $st->execute([mb_substr($lango, 0, 190), mb_substr($eng, 0, 500), mb_substr(implode(', ', $w['parts_of_speech'] ?? []), 0, 40) ?: null, 'Lango dictionary project import', 'published']);
        }
        $pdo->commit();
    }

    lci_upgrade($pdo);
}
