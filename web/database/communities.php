<?php
declare(strict_types=1);

/**
 * Communities (Lango, Acholi and the tribes that follow): called at the end of lci_upgrade().
 * The `communities` table lists them and whether each one is switched on (Admin, Communities). Every community except Lango has its own copy of the
 * content tables, named with its prefix (ac_ for Acholi, see app/lib/db.php); this creates them from the Lango tables, adds the member's community
 * to users and contributions, and seeds the Acholi content from content/acholi.php into empty tables.
 * Idempotent: safe to run again, and later columns added to a Lango table are copied to the tables of every other community.
 */
function lci_communities(PDO $pdo): array
{
    require_once dirname(__DIR__) . '/app/lib/db.php';   // COMMUNITY_DEFAULTS and COMMUNITY_TABLES
    $log = [];
    $db = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
    $tables = array_column($pdo->query("SELECT table_name AS t FROM information_schema.tables WHERE table_schema=" . $pdo->quote($db))->fetchAll(PDO::FETCH_ASSOC), 't');
    $has = function (string $t) use (&$tables): bool { return in_array($t, $tables, true); };

    $addCol = function (string $t, string $c, string $ddl) use ($pdo, $db, &$log) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name=?');
        $st->execute([$db, $t, $c]);
        if (!$st->fetchColumn()) { $pdo->exec("ALTER TABLE `$t` ADD COLUMN `$c` $ddl"); $log[] = "added $t.$c"; }
    };
    $addCol('users', 'culture', "VARCHAR(16) NOT NULL DEFAULT 'lango'");
    $addCol('users', 'culture_state', 'TEXT NULL');   // the clan details of the community the member is not in now, kept for when they switch back
    $addCol('contributions', 'culture', "VARCHAR(16) NOT NULL DEFAULT 'lango'");
    // a dictionary entry's short English meanings (|water|, matched exactly) and how far the scan can be trusted (3 = both halves of the book agree, 2 = one half and the spelling matches, 1 = doubtful, NULL = not graded: Lango's)
    $addCol('dictionary', 'gloss', 'VARCHAR(500) NULL');
    $addCol('dictionary', 'grade', 'TINYINT NULL');
    $pdo->exec("ALTER TABLE users MODIFY culture VARCHAR(16) NOT NULL DEFAULT 'lango'");
    $pdo->exec("ALTER TABLE contributions MODIFY culture VARCHAR(16) NOT NULL DEFAULT 'lango'");

    // the list of communities: Lango is on; Acholi is there but off until the admin switches it on
    $pdo->exec("CREATE TABLE IF NOT EXISTS communities (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(16) NOT NULL UNIQUE, name VARCHAR(80) NOT NULL, language VARCHAR(80) NOT NULL, people VARCHAR(80) NOT NULL,
        register_one VARCHAR(40) NOT NULL DEFAULT 'Clan', register_many VARCHAR(40) NOT NULL DEFAULT 'Clans', leader VARCHAR(40) NOT NULL DEFAULT 'Awitong',
        region VARCHAR(120) NULL, capital VARCHAR(120) NULL, prefix VARCHAR(12) NOT NULL DEFAULT '', enabled TINYINT(1) NOT NULL DEFAULT 0, sort INT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    foreach (COMMUNITY_DEFAULTS as $code => $d) {
        $st = $pdo->prepare('INSERT IGNORE INTO communities (code, name, language, people, register_one, register_many, leader, region, capital, prefix, enabled, sort) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $st->execute([$code, $d['name'], $d['language'], $d['people'], $d['register'], $d['registers'], $d['leader'], $d['region'], $d['capital'], $d['prefix'], $d['enabled'], $d['sort']]);
        if ($st->rowCount()) $log[] = "community $code";
    }
    $rows = $pdo->query('SELECT code, prefix FROM communities ORDER BY sort, id')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        if ($row['prefix'] === '') continue;
        foreach (lci_community_tables($pdo, $row['prefix']) as $line) $log[] = $line;
    }
    $tables = array_column($pdo->query("SELECT table_name AS t FROM information_schema.tables WHERE table_schema=" . $pdo->quote($db))->fetchAll(PDO::FETCH_ASSOC), 't');

    // Acholi content (texts only, from the Ker Kwaro Acholi website and cited published sources), loaded into tables that are still empty
    $seed = __DIR__ . '/content/acholi.php';
    if (is_file($seed) && $has('ac_districts')) {
        $columns = function (string $t) use ($pdo, $db): array {
            $st = $pdo->prepare('SELECT column_name FROM information_schema.columns WHERE table_schema=? AND table_name=?');
            $st->execute([$db, $t]);
            return $st->fetchAll(PDO::FETCH_COLUMN);
        };
        foreach ((require $seed) as $table => $rows2) {
            $t = 'ac_' . $table;
            if (!$rows2 || !$has($t) || (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn() > 0) continue;
            $known = $columns($t);
            foreach ($rows2 as $r) {
                $r = array_intersect_key($r, array_flip($known));
                $pdo->prepare("INSERT INTO `$t` (`" . implode('`,`', array_keys($r)) . '`) VALUES (' . implode(',', array_fill(0, count($r), '?')) . ')')->execute(array_values($r));
            }
            $log[] = "$t: " . count($rows2) . ' rows';
        }
    }

    // a community's own wording is a setting named <code>.<key> (its name and tagline come from the community itself); the first version used "ac."
    $pdo->exec("UPDATE IGNORE settings SET k=CONCAT('acholi.', SUBSTRING(k, 4)) WHERE k LIKE 'ac.%'");
    $pdo->exec("DELETE FROM settings WHERE k IN ('acholi.site_name', 'acholi.site_tagline', 'acholi.org_subtitle')");
    $st = $pdo->prepare('INSERT IGNORE INTO settings (k, v) VALUES (?, ?)');
    $st->execute(['acholi.clan_total', '54']);
    if ($st->rowCount()) $log[] = 'setting acholi.clan_total';

    // The Acholi Bible (Acoli Baibul 1985, Bible Society of Uganda): the 66 books under the names that edition gives them, and the link to it on Bible.com.
    // The text is not copied; each chapter opens in the reader there. A name or link an editor changed in the admin is left alone.
    $bibleSeed = __DIR__ . '/content/acholi_bible.php';
    if (is_file($bibleSeed) && $has('ac_bible_books') && $has('bible_books')) {
        $bible = require $bibleSeed;
        $pdo->exec('INSERT IGNORE INTO ac_bible_books (id, code, testament, name_en) SELECT id, code, testament, name_en FROM bible_books');
        $fill = $pdo->prepare("UPDATE ac_bible_books SET name_lg=? WHERE code=? AND (name_lg IS NULL OR name_lg='')");
        $named = 0;
        foreach ($bible['books'] as $code => $name) { $fill->execute([$name, $code]); $named += $fill->rowCount(); }
        if ($named) $log[] = "ac_bible_books: $named book names";
        $st->execute(['acholi.bible_link_full', $bible['url']]);
        if ($st->rowCount()) $log[] = 'setting acholi.bible_link_full';
    } elseif ($has('ac_bible_books')) {
        $log[] = 'WARNING: database/content/acholi_bible.php is missing, so the Acholi Bible books and link were not added';
        error_log('LCI upgrade: database/content/acholi_bible.php is missing');
    }
    return $log;
}

/** Creates, from the Lango tables, the content tables of the community with this prefix, and adds any column the Lango table has gained since. Returns what it did. */
function lci_community_tables(PDO $pdo, string $prefix): array
{
    $log = [];
    $db = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
    $have = array_column($pdo->query("SELECT table_name AS t FROM information_schema.tables WHERE table_schema=" . $pdo->quote($db))->fetchAll(PDO::FETCH_ASSOC), 't');
    $columns = function (string $t) use ($pdo, $db): array {
        $st = $pdo->prepare('SELECT column_name, column_type, is_nullable, column_default, extra FROM information_schema.columns WHERE table_schema=? AND table_name=? ORDER BY ordinal_position');
        $st->execute([$db, $t]);
        return array_column($st->fetchAll(PDO::FETCH_ASSOC), null, 'column_name');
    };
    foreach (COMMUNITY_TABLES as $t) {
        if (!in_array($t, $have, true)) continue;
        if (!in_array($prefix . $t, $have, true)) { $pdo->exec("CREATE TABLE `$prefix$t` LIKE `$t`"); $log[] = "created $prefix$t"; }
        $twin = $columns($prefix . $t);
        foreach ($columns($t) as $name => $c) {
            if (isset($twin[$name])) continue;
            $def = $c['column_default'];
            $ddl = $c['column_type'] . ($c['is_nullable'] === 'NO' ? ' NOT NULL' : ' NULL') . ($def !== null && $def !== 'NULL' && $c['extra'] === '' ? ' DEFAULT ' . $def : '');
            $pdo->exec("ALTER TABLE `$prefix$t` ADD COLUMN `$name` $ddl");
            $log[] = "added $prefix$t.$name";
        }
    }
    return $log;
}
