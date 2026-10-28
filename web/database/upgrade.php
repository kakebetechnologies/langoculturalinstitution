<?php
declare(strict_types=1);

/**
 * Idempotent schema upgrade + content seeding (v2).
 * Safe to run repeatedly: columns/tables are only added when missing, and content is only inserted when absent.
 * Called by install_lib.php for new installs and by database/upgrade-cli.php for existing ones.
 */
function lci_upgrade(PDO $pdo): array
{
    $log = [];
    $pdo->exec("SET NAMES utf8mb4");
    $db = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();

    $hasCol = function (string $t, string $c) use ($pdo, $db): bool {
        $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name=?');
        $st->execute([$db, $t, $c]);
        return (bool)$st->fetchColumn();
    };
    $addCol = function (string $t, string $c, string $ddl) use ($pdo, $hasCol, &$log) {
        if (!$hasCol($t, $c)) { $pdo->exec("ALTER TABLE `$t` ADD COLUMN `$c` $ddl"); $log[] = "added $t.$c"; }
    };
    $one = function (string $sql, array $p = []) use ($pdo) { $st = $pdo->prepare($sql); $st->execute($p); $r = $st->fetch(PDO::FETCH_ASSOC); return $r === false ? null : $r; };
    $val = function (string $sql, array $p = []) use ($pdo) { $st = $pdo->prepare($sql); $st->execute($p); $v = $st->fetchColumn(); return $v === false ? null : $v; };
    $ins = function (string $table, array $row) use ($pdo): int {
        $cols = array_keys($row);
        $st = $pdo->prepare("INSERT INTO `$table` (`" . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
        $st->execute(array_values($row));
        return (int)$pdo->lastInsertId();
    };
    $upd = function (string $table, array $row, string $where, array $p) use ($pdo): void {
        $set = implode(',', array_map(fn($c) => "`$c`=?", array_keys($row)));
        $pdo->prepare("UPDATE `$table` SET $set WHERE $where")->execute(array_merge(array_values($row), $p));
    };

    /* ---------------- schema ---------------- */
    foreach ([
        'kind' => "ENUM('district','city') NOT NULL DEFAULT 'district'", 'population' => 'INT UNSIGNED NULL', 'pop_year' => 'SMALLINT UNSIGNED NULL',
        'area_km2' => 'DECIMAL(9,1) NULL', 'created_year' => 'SMALLINT UNSIGNED NULL', 'parent' => 'VARCHAR(120) NULL', 'neighbours' => 'VARCHAR(255) NULL',
        'distance' => 'VARCHAR(255) NULL', 'known_for' => 'TEXT NULL', 'subdivisions' => 'TEXT NULL',
        'demography' => 'MEDIUMTEXT NULL', 'economy' => 'MEDIUMTEXT NULL', 'attractions' => 'MEDIUMTEXT NULL', 'history' => 'MEDIUMTEXT NULL',
    ] as $c => $ddl) $addCol('districts', $c, $ddl);

    $pdo->exec("CREATE TABLE IF NOT EXISTS notables (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(160) NOT NULL UNIQUE, name VARCHAR(190) NOT NULL, short_name VARCHAR(120) NULL,
        category VARCHAR(40) NOT NULL DEFAULT 'statesman', title VARCHAR(190) NULL, born VARCHAR(20) NULL, died VARCHAR(20) NULL, place VARCHAR(120) NULL,
        district_id INT UNSIGNED NULL, summary VARCHAR(500) NULL, body MEDIUMTEXT NULL, photo VARCHAR(255) NULL,
        featured TINYINT(1) NOT NULL DEFAULT 0, sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS history_sections (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) NOT NULL UNIQUE, title VARCHAR(190) NOT NULL, era VARCHAR(60) NULL, icon VARCHAR(30) NULL,
        summary VARCHAR(400) NULL, body MEDIUMTEXT NULL, body_lg MEDIUMTEXT NULL, sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS glossary (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, term VARCHAR(120) NOT NULL, meaning VARCHAR(400) NOT NULL, note VARCHAR(400) NULL,
        sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, type VARCHAR(30) NOT NULL, title VARCHAR(190) NOT NULL, body VARCHAR(400) NULL, url VARCHAR(255) NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY (is_read, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // mobile app: API tokens, push device tokens, per-member notifications, simple rate limiting
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_tokens (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, token_hash CHAR(64) NOT NULL UNIQUE, device_name VARCHAR(120) NULL,
        last_used DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS device_tokens (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, token VARCHAR(190) NOT NULL UNIQUE, platform VARCHAR(20) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL, KEY (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_notifications (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, type VARCHAR(30) NOT NULL DEFAULT 'info', title VARCHAR(190) NOT NULL, body VARCHAR(400) NULL,
        data VARCHAR(500) NULL, is_read TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY (user_id, is_read, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
        k VARCHAR(120) NOT NULL PRIMARY KEY, hits INT UNSIGNED NOT NULL DEFAULT 0, reset_at INT UNSIGNED NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $addCol('contributions', 'client_ref', 'VARCHAR(40) NULL');

    // clan groups: a member opts in to the group of the clan they chose (users.clan_id), can hide from the member list, and reads/writes messages
    $addCol('users', 'clan_joined_at', 'DATETIME NULL');
    $addCol('users', 'clan_hidden', 'TINYINT(1) NOT NULL DEFAULT 0');
    $addCol('users', 'clan_seen_id', 'INT UNSIGNED NOT NULL DEFAULT 0');
    $pdo->exec("CREATE TABLE IF NOT EXISTS clan_messages (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, clan_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NULL, body VARCHAR(1000) NOT NULL,
        status ENUM('visible','hidden') NOT NULL DEFAULT 'visible', hidden_by INT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY (clan_id, id), KEY (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS clan_message_reports (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, message_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, reason VARCHAR(200) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY (message_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // "Continue with Google": the Google account id on the member, and short-lived one-time codes handed to the app after Google signs the person in
    $addCol('users', 'google_sub', 'VARCHAR(40) NULL');
    try { $pdo->exec('ALTER TABLE users ADD UNIQUE KEY google_sub (google_sub)'); } catch (Throwable $e) { /* already there */ }
    $pdo->exec("CREATE TABLE IF NOT EXISTS google_codes (
        code_hash CHAR(64) NOT NULL PRIMARY KEY, challenge CHAR(43) NOT NULL, user_id INT UNSIGNED NULL, google_sub VARCHAR(40) NULL, email VARCHAR(190) NULL,
        name VARCHAR(150) NULL, expires_at INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $addCol('contributions', 'word_cat', 'VARCHAR(40) NULL');
    $addCol('contributions', 'word_pos', 'VARCHAR(40) NULL');
    $addCol('contributions', 'example_lg', 'VARCHAR(500) NULL');
    $addCol('contributions', 'contributor_clan_id', 'INT UNSIGNED NULL');
    $addCol('contributions', 'contributor_clan_other', 'VARCHAR(120) NULL');
    $addCol('contributions', 'credit', 'TINYINT(1) NOT NULL DEFAULT 1');
    $addCol('contributions', 'word_note', 'VARCHAR(500) NULL');
    $addCol('dictionary', 'fold', 'VARCHAR(200) NULL');
    $addCol('proverbs', 'contributor', 'VARCHAR(150) NULL');
    $addCol('proverbs', 'source', 'VARCHAR(100) NULL');
    $addCol('dictionary', 'category', 'VARCHAR(40) NULL');
    $addCol('dictionary', 'contributor', 'VARCHAR(150) NULL');
    $addCol('users', 'clan_other', 'VARCHAR(120) NULL');
    $addCol('applications', 'clan_id', 'INT UNSIGNED NULL');
    $addCol('applications', 'clan_other', 'VARCHAR(120) NULL');
    $addCol('event_registrations', 'clan_id', 'INT UNSIGNED NULL');
    $addCol('event_registrations', 'clan_other', 'VARCHAR(120) NULL');
    $addCol('professionals', 'clan_other', 'VARCHAR(120) NULL');
    $addCol('clans', 'source_note', 'VARCHAR(255) NULL');

    // folded spelling (ε→e, ɔ→o, ŋ→ng) so words typed on an ordinary keyboard are found by the dictionary search
    $fold = fn(string $s) => strtr(mb_strtolower($s), ['ε' => 'e', 'ɛ' => 'e', 'ɔ' => 'o', 'ŋ' => 'ng', 'ë' => 'e', 'ö' => 'o', 'ï' => 'i', 'é' => 'e', 'è' => 'e', 'ó' => 'o', 'ò' => 'o', 'á' => 'a', 'à' => 'a', 'í' => 'i', 'ì' => 'i', 'ú' => 'u', 'ù' => 'u']);
    $todo = $pdo->query('SELECT id, lango FROM dictionary WHERE fold IS NULL')->fetchAll(PDO::FETCH_ASSOC);
    if ($todo) {
        $st = $pdo->prepare('UPDATE dictionary SET fold=? WHERE id=?');
        $pdo->beginTransaction();
        foreach ($todo as $r) $st->execute([mb_substr($fold($r['lango']), 0, 200), $r['id']]);
        $pdo->commit();
        $log[] = 'folded ' . count($todo) . ' dictionary spellings';
    }

    /* ---------------- content ---------------- */
    $dir = __DIR__ . '/content';

    // districts (upsert by slug)
    $districts = require $dir . '/districts.php';
    foreach ($districts as $slug => $d) {
        $row = [
            'name' => $d['name'], 'kind' => $d['kind'], 'headquarters' => $d['hq'], 'lat' => $d['lat'], 'lng' => $d['lng'], 'sort' => $d['sort'],
            'population' => $d['population'], 'pop_year' => $d['pop_year'], 'area_km2' => $d['area_km2'], 'created_year' => $d['created_year'], 'parent' => $d['parent'],
            'neighbours' => $d['neighbours'], 'distance' => $d['distance'], 'known_for' => $d['known_for'], 'subdivisions' => $d['subdivisions'],
            'description' => $d['overview'], 'demography' => $d['demography'], 'economy' => $d['economy'], 'attractions' => $d['attractions'], 'history' => $d['history'],
            'status' => 'published',
        ];
        $exists = $val('SELECT id FROM districts WHERE slug=?', [$slug]);
        if ($exists) {
            // do not overwrite text an editor already changed: only fill empty fields
            $cur = $one('SELECT * FROM districts WHERE id=?', [$exists]);
            $fill = [];
            foreach ($row as $k => $v) if (in_array($k, ['kind', 'sort', 'lat', 'lng', 'status', 'name'], true) || $cur[$k] === null || $cur[$k] === '') $fill[$k] = $v;
            if ($fill) $upd('districts', $fill, 'id=?', [$exists]);
        } else { $ins('districts', ['slug' => $slug] + $row); $log[] = "district $slug"; }
    }
    $districtId = fn(string $slug) => $val('SELECT id FROM districts WHERE slug=?', [$slug]);

    $misc = require $dir . '/misc.php';

    // clans
    foreach ($misc['clans'] as [$name, $note]) {
        if (!$val('SELECT id FROM clans WHERE name=?', [$name])) {
            $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
            $ins('clans', ['name' => $name, 'slug' => $slug, 'status' => 'published', 'verified' => 0, 'leader_title' => 'Awitong', 'source_note' => $note]);
            $log[] = "clan $name";
        }
    }

    // leaders: real photo for the Won Nyaci; placeholder cabinet entries replace the earlier "To be announced" drafts
    $upd('leaders', ['photo' => 'assets/img/people/won-nyaci.jpg'], "grp='won_nyaci' AND (photo IS NULL OR photo='')", []);
    if (!$val("SELECT COUNT(*) FROM leaders WHERE name='Name to be added'")) {
        $pdo->exec("DELETE FROM leaders WHERE name='To be announced' AND status='draft'");
        foreach ($misc['leaders'] as $l) { $ins('leaders', $l + ['status' => 'published']); }
        $log[] = 'sample leaders';
    }

    // programmes
    foreach ($misc['programs'] as [$title, $cat, $summary, $progress, $featured]) {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
        if (!$val('SELECT id FROM programs WHERE slug=?', [$slug])) {
            $ins('programs', ['title' => $title, 'slug' => $slug, 'category' => $cat, 'summary' => $summary, 'body' => $summary, 'progress' => $progress, 'featured' => $featured, 'sort' => 20, 'status' => 'published']);
            $log[] = "program $slug";
        }
    }

    // heritage
    $o = $misc['heritage']['okeme'];
    $upd('heritage_items', ['summary' => $o['summary'], 'body' => $o['body']], "slug='okeme' AND (body IS NULL OR body LIKE '%starting point to be enriched%' OR body LIKE '%This entry is a starting point%')", []);
    foreach ($misc['heritage']['new'] as $h) {
        if ($val('SELECT id FROM heritage_items WHERE slug=?', [$h['slug']])) continue;
        $dslug = $h['district'] ?? null; unset($h['district']);
        $ins('heritage_items', $h + ['district_id' => $dslug ? $districtId($dslug) : null, 'status' => 'published', 'verified' => 0]);
        $log[] = "heritage {$h['slug']}";
    }

    // notable people
    foreach (require $dir . '/people.php' as $n) {
        if ($val('SELECT id FROM notables WHERE slug=?', [$n['slug']])) continue;
        $ins('notables', $n + ['status' => 'published']);
        $log[] = "notable {$n['slug']}";
    }

    // history
    $hist = require $dir . '/history.php';
    foreach ($hist['sections'] as $s) {
        if (!$val('SELECT id FROM history_sections WHERE slug=?', [$s['slug']])) { $ins('history_sections', $s + ['status' => 'published']); $log[] = "history {$s['slug']}"; }
    }
    if (!$val('SELECT COUNT(*) FROM timeline')) {
        foreach ($hist['timeline'] as $i => [$year, $title, $body]) $ins('timeline', ['year' => $year, 'title' => $title, 'body' => $body, 'sort' => $i + 1]);
        $log[] = 'timeline';
    }
    if (!$val('SELECT COUNT(*) FROM glossary')) {
        $i = 0;
        foreach ($hist['glossary'] as [$key, $note]) {
            $r = $key === 'myel' ? $one("SELECT lango, english FROM dictionary WHERE lango LIKE 'my_l' AND english='dance' LIMIT 1")
                                 : $one('SELECT lango, english FROM dictionary WHERE lango=? LIMIT 1', [$key]);
            if ($r) $ins('glossary', ['term' => $r['lango'], 'meaning' => $r['english'], 'note' => $note, 'sort' => ++$i]);
        }
        foreach ($hist['terms'] as [$term, $meaning]) $ins('glossary', ['term' => $term, 'meaning' => $meaning, 'note' => 'Institutional term', 'sort' => ++$i]);
        $log[] = 'glossary';
    }

    // assistant knowledge
    foreach ([
        ['Geography', 'How many people live in the Lango sub-region?', 'The 2024 census counted 2,546,118 people in the Lango sub-region, up from 2,061,694 in 2014.'],
        ['Geography', 'What is Lira City?', 'Lira became a city on 1 July 2020. It is the commercial, educational and administrative centre of the Lango sub-region and recorded 245,132 people in the 2024 census.'],
        ['History', 'Who was Milton Obote?', 'Dr. Apollo Milton Obote (1925–2005) was born at Akokoro in Apac. He led Uganda to independence in 1962 as Prime Minister and served as President in 1966–1971 and 1980–1985.'],
        ['Culture', 'What is Okeme?', 'Okeme is a Lango folk dance and thumb-piano tradition, usually performed at weddings, introduction ceremonies and graduations.'],
    ] as [$tp, $qn, $an]) {
        if (!$val('SELECT id FROM knowledge WHERE question=?', [$qn])) $ins('knowledge', ['topic' => $tp, 'question' => $qn, 'answer' => $an, 'reviewed' => 1]);
    }

    return $log;
}
