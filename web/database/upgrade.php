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
    $addCol('notables', 'photo_credit', 'VARCHAR(255) NULL');
    $addCol('notables', 'photo_source', 'VARCHAR(255) NULL');
    // photo credits for heritage entries, species and history chapters, and the "near threatened" conservation status
    $addCol('heritage_items', 'image_credit', 'VARCHAR(255) NULL');
    $addCol('species', 'image_credit', 'VARCHAR(255) NULL');
    $addCol('districts', 'image_credit', 'VARCHAR(255) NULL');
    $consType = (string)$pdo->query("SELECT column_type FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='species' AND column_name='conservation'")->fetchColumn();
    if ($consType !== '' && strpos($consType, 'near_threatened') === false) {
        $pdo->exec("ALTER TABLE species MODIFY conservation ENUM('unknown','least_concern','near_threatened','vulnerable','endangered','critical','locally_extinct','reintroduced') NOT NULL DEFAULT 'unknown'");
        $log[] = 'species.conservation: near threatened';
    }
    // further reading about a notable person: articles, profiles, documentaries and records on other websites (we link, never copy)
    $pdo->exec("CREATE TABLE IF NOT EXISTS notable_links (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, notable_id INT UNSIGNED NOT NULL, kind VARCHAR(20) NOT NULL DEFAULT 'article', title VARCHAR(255) NOT NULL,
        source VARCHAR(120) NULL, url VARCHAR(500) NOT NULL, sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published', KEY (notable_id, sort)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // knowledge quizzes about the Lango people: sets of multiple-choice questions, and each member's best score per set
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_sets (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(80) NOT NULL UNIQUE, title VARCHAR(190) NOT NULL, summary VARCHAR(300) NULL, icon VARCHAR(30) NULL,
        sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, set_id INT UNSIGNED NOT NULL, question VARCHAR(400) NOT NULL, answer VARCHAR(200) NOT NULL, wrong VARCHAR(600) NOT NULL,
        why VARCHAR(600) NULL, source VARCHAR(190) NULL, source_url VARCHAR(255) NULL, sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published', KEY (set_id, sort)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_results (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, set_id INT UNSIGNED NOT NULL, best SMALLINT UNSIGNED NOT NULL DEFAULT 0, total SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        plays INT UNSIGNED NOT NULL DEFAULT 0, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY (user_id, set_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // the photographs of the app's opening screen (Admin, Content, App opening photos); the app asks for them at GET /api/v1/welcome
    $pdo->exec("CREATE TABLE IF NOT EXISTS welcome_photos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(190) NULL, image VARCHAR(255) NULL, credit VARCHAR(255) NULL, focus VARCHAR(10) NOT NULL DEFAULT 'upper',
        sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("ALTER TABLE welcome_photos MODIFY image VARCHAR(255) NULL");   // a row saved without a photo is simply not shown
    $pdo->exec("CREATE TABLE IF NOT EXISTS history_sections (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) NOT NULL UNIQUE, title VARCHAR(190) NOT NULL, era VARCHAR(60) NULL, icon VARCHAR(30) NULL,
        summary VARCHAR(400) NULL, body MEDIUMTEXT NULL, body_lg MEDIUMTEXT NULL, sort INT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $addCol('history_sections', 'image', 'VARCHAR(255) NULL');
    $addCol('history_sections', 'image_credit', 'VARCHAR(255) NULL');
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
    // a member can block another member of the clan group: the blocked person's messages and name are hidden from the one who blocked (App Store rule 1.2)
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_blocks (
        blocker_id INT UNSIGNED NOT NULL, blocked_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (blocker_id, blocked_id), KEY (blocked_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // daily habit: one row per member per day per kind of activity (opened the app, learned the word of the day), quiz results, best streak
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_days (
        user_id INT UNSIGNED NOT NULL, day DATE NOT NULL, kind ENUM('open','word') NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, kind, day), KEY (kind, day)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_scores (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, kind ENUM('word','weekly') NOT NULL, period VARCHAR(10) NOT NULL,
        score SMALLINT UNSIGNED NOT NULL DEFAULT 0, total SMALLINT UNSIGNED NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (user_id, kind, period), KEY (kind, period, score)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $addCol('users', 'best_streak', 'SMALLINT UNSIGNED NOT NULL DEFAULT 0');

    // Listen & read. The Lango Podcast (elders' stories), Lango Radio (channels: a live stream, a playlist the editors build, or every podcast episode)
    // and the Lango Bible (the 66 books are the standard canon; the verses are imported from a text the site has the right to publish).
    $pdo->exec("CREATE TABLE IF NOT EXISTS podcast_episodes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, storyteller VARCHAR(150) NULL, storyteller_note VARCHAR(255) NULL, series VARCHAR(120) NULL,
        clan_id INT UNSIGNED NULL, district_id INT UNSIGNED NULL, recorded_year SMALLINT UNSIGNED NULL, summary TEXT NULL, transcript MEDIUMTEXT NULL,
        audio_file VARCHAR(255) NULL, audio_url VARCHAR(500) NULL, minutes SMALLINT UNSIGNED NULL, cover_image VARCHAR(255) NULL,
        status ENUM('published','draft') NOT NULL DEFAULT 'draft', featured TINYINT(1) NOT NULL DEFAULT 0, plays INT UNSIGNED NOT NULL DEFAULT 0,
        published_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY (status, published_at), KEY (series)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS radio_channels (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, description VARCHAR(300) NULL, source ENUM('playlist','podcast','stream') NOT NULL DEFAULT 'playlist',
        stream_url VARCHAR(500) NULL, cover_image VARCHAR(255) NULL, sort SMALLINT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'draft'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS radio_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, channel_id INT UNSIGNED NOT NULL, title VARCHAR(200) NOT NULL, credit VARCHAR(190) NULL, audio_file VARCHAR(255) NULL,
        audio_url VARCHAR(500) NULL, minutes SMALLINT UNSIGNED NULL, sort SMALLINT NOT NULL DEFAULT 0, status ENUM('published','draft') NOT NULL DEFAULT 'published', KEY (channel_id, sort)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS bible_books (
        id TINYINT UNSIGNED NOT NULL PRIMARY KEY, code CHAR(3) NOT NULL UNIQUE, testament ENUM('old','new') NOT NULL, name_en VARCHAR(60) NOT NULL, name_lg VARCHAR(80) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS bible_verses (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, book_id TINYINT UNSIGNED NOT NULL, chapter SMALLINT UNSIGNED NOT NULL, verse SMALLINT UNSIGNED NOT NULL, text TEXT NOT NULL,
        UNIQUE KEY ref (book_id, chapter, verse)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if (!(int)$val('SELECT COUNT(*) FROM bible_books')) {
        $canon = 'GEN Genesis|EXO Exodus|LEV Leviticus|NUM Numbers|DEU Deuteronomy|JOS Joshua|JDG Judges|RUT Ruth|1SA 1 Samuel|2SA 2 Samuel|1KI 1 Kings|2KI 2 Kings|1CH 1 Chronicles|2CH 2 Chronicles|EZR Ezra|NEH Nehemiah|EST Esther|JOB Job|PSA Psalms|PRO Proverbs|ECC Ecclesiastes|SNG Song of Solomon|ISA Isaiah|JER Jeremiah|LAM Lamentations|EZK Ezekiel|DAN Daniel|HOS Hosea|JOL Joel|AMO Amos|OBA Obadiah|JON Jonah|MIC Micah|NAM Nahum|HAB Habakkuk|ZEP Zephaniah|HAG Haggai|ZEC Zechariah|MAL Malachi|'
            . 'MAT Matthew|MRK Mark|LUK Luke|JHN John|ACT Acts|ROM Romans|1CO 1 Corinthians|2CO 2 Corinthians|GAL Galatians|EPH Ephesians|PHP Philippians|COL Colossians|1TH 1 Thessalonians|2TH 2 Thessalonians|1TI 1 Timothy|2TI 2 Timothy|TIT Titus|PHM Philemon|HEB Hebrews|JAS James|1PE 1 Peter|2PE 2 Peter|1JN 1 John|2JN 2 John|3JN 3 John|JUD Jude|REV Revelation';
        foreach (explode('|', $canon) as $i => $b) $ins('bible_books', ['id' => $i + 1, 'code' => substr($b, 0, 3), 'testament' => $i < 39 ? 'old' : 'new', 'name_en' => substr($b, 4)]);
        $log[] = 'seeded the 66 books of the Bible';
    }
    $addCol('donations', 'purpose', "VARCHAR(12) NOT NULL DEFAULT 'general'");

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

    // more about the notable Langos: one new person, licensed portraits with their credit, home districts, further reading, and fuller stories for rows seeded short
    $extra = require $dir . '/people_extra.php';
    foreach ($extra['new'] as $n) {
        if ($val('SELECT id FROM notables WHERE slug=?', [$n['slug']])) continue;
        $ins('notables', $n + ['status' => 'published']);
        $log[] = "notable {$n['slug']}";
    }
    foreach ($extra['photos'] as $slug => [$file, $credit, $page]) {
        $upd('notables', ['photo' => $file, 'photo_credit' => $credit, 'photo_source' => $page], "slug=? AND (photo IS NULL OR photo='')", [$slug]);
    }
    foreach ($extra['districts'] as $slug => $dslug) {
        if ($did = $districtId($dslug)) $upd('notables', ['district_id' => $did], 'slug=? AND district_id IS NULL', [$slug]);
    }
    $added = 0;
    foreach ($extra['links'] as $slug => $rows) {
        if (!($nid = $val('SELECT id FROM notables WHERE slug=?', [$slug]))) continue;
        foreach ($rows as $i => [$kind, $title, $source, $url]) {
            if ($val('SELECT id FROM notable_links WHERE notable_id=? AND url=?', [$nid, $url])) continue;
            $ins('notable_links', ['notable_id' => $nid, 'kind' => $kind, 'title' => $title, 'source' => $source, 'url' => $url, 'sort' => $i + 1]);
            $added++;
        }
    }
    if ($added) $log[] = "$added further-reading links";
    if ($val("SELECT v FROM settings WHERE k='people_rev'") !== 'wiki-1') {   // once: a story an editor has already written at length is never replaced
        foreach ($extra['refresh'] as $slug => $fields) $upd('notables', $fields, 'slug=? AND (body IS NULL OR CHAR_LENGTH(body) < 1500)', [$slug]);
        if ($val("SELECT COUNT(*) FROM settings WHERE k='people_rev'")) $upd('settings', ['v' => 'wiki-1'], "k='people_rev'", []); else $ins('settings', ['k' => 'people_rev', 'v' => 'wiki-1']);
        $log[] = 'people stories refreshed';
    }

    // further notable Langos (Sam Engola): the person, the supplied portrait with its credit, the home district and the sources
    if (is_file($dir . '/people_more.php')) {
        $more = require $dir . '/people_more.php';
        foreach ($more['new'] as $n) {
            if ($val('SELECT id FROM notables WHERE slug=?', [$n['slug']])) continue;
            $ins('notables', $n + ['status' => 'published']);
            $log[] = "notable {$n['slug']}";
        }
        foreach ($more['photos'] as $slug => [$file, $credit, $page]) {
            $upd('notables', ['photo' => $file, 'photo_credit' => $credit, 'photo_source' => $page ?: null], "slug=? AND (photo IS NULL OR photo='')", [$slug]);
        }
        foreach ($more['districts'] as $slug => $dslug) {
            if ($did = $districtId($dslug)) $upd('notables', ['district_id' => $did], 'slug=? AND district_id IS NULL', [$slug]);
        }
        foreach ($more['links'] as $slug => $rows) {
            if (!($nid = $val('SELECT id FROM notables WHERE slug=?', [$slug]))) continue;
            foreach ($rows as $i => [$kind, $title, $source, $url]) {
                if ($val('SELECT id FROM notable_links WHERE notable_id=? AND url=?', [$nid, $url])) continue;
                $ins('notable_links', ['notable_id' => $nid, 'kind' => $kind, 'title' => $title, 'source' => $source, 'url' => $url, 'sort' => $i + 1]);
                $log[] = "link $slug";
            }
        }
    }

    // knowledge quizzes
    foreach (require $dir . '/quiz.php' as $s) {
        $sid = $val('SELECT id FROM quiz_sets WHERE slug=?', [$s['slug']]);
        if (!$sid) { $sid = $ins('quiz_sets', ['slug' => $s['slug'], 'title' => $s['title'], 'summary' => $s['summary'], 'icon' => $s['icon'], 'sort' => $s['sort'], 'status' => 'published']); $log[] = "quiz {$s['slug']}"; }
        foreach ($s['questions'] as $i => [$q, $right, $wrong, $why, $src, $srcUrl]) {
            if ($val('SELECT id FROM quiz_questions WHERE set_id=? AND question=?', [$sid, $q])) continue;
            $ins('quiz_questions', ['set_id' => $sid, 'question' => $q, 'answer' => $right, 'wrong' => implode("\n", $wrong), 'why' => $why, 'source' => $src, 'source_url' => $srcUrl, 'sort' => $i + 1]);
        }
    }

    // pages that would otherwise be empty: heritage entries, proverbs, clans, species and gallery photos, every one from a named source (see more_content.php)
    $more = require $dir . '/more_content.php';
    $n = 0;
    foreach ($more['heritage'] as $h) {
        if ($val('SELECT id FROM heritage_items WHERE slug=?', [$h['slug']])) continue;
        $ins('heritage_items', ['category' => $h['category'], 'title' => html_entity_decode($h['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'slug' => $h['slug'], 'summary' => $h['summary'], 'body' => $h['body'],
            'image' => $h['image'], 'image_credit' => $h['image_credit'], 'status' => 'published', 'verified' => 0]);
        $n++;
    }
    if ($n) $log[] = "$n heritage entries";
    $n = 0;
    foreach ($more['proverbs'] as [$lg, $en, $meaning]) {
        if ($val('SELECT id FROM proverbs WHERE lango=?', [$lg])) continue;
        $ins('proverbs', ['lango' => $lg, 'english' => $en, 'meaning' => $meaning, 'verified' => 0, 'status' => 'published', 'source' => 'J. H. Driberg, The Lango (1923), pp. 136-137; old spelling']);
        $n++;
    }
    if ($n) $log[] = "$n proverbs";
    $n = 0;
    foreach ($more['clans_new'] as $name => [$summary, $totem]) {
        if ($val('SELECT id FROM clans WHERE name=?', [$name])) continue;
        $ins('clans', ['name' => $name, 'slug' => trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-'), 'summary' => $summary, 'totem' => $totem, 'leader_title' => 'Awitong', 'status' => 'published', 'verified' => 0,
            'source_note' => sprintf($more['clan_note'], $name)]);
        $n++;
    }
    foreach ($more['clans_summary'] as $name => $summary) $upd('clans', ['summary' => $summary], "name=? AND (summary IS NULL OR summary='')", [$name]);
    if ($n) $log[] = "$n clans";
    $n = 0;
    $images = require $dir . '/species_images.php';
    foreach ($more['species'] as $slug => [$name, $sci, $kind, $cons, $medicinal, $habitat, $body]) {
        if ($val('SELECT id FROM species WHERE slug=?', [$slug])) continue;
        [$img, $credit] = $images[$slug];
        $ins('species', ['name' => $name, 'slug' => $slug, 'scientific_name' => $sci, 'kind' => $kind, 'conservation' => $cons, 'medicinal' => $medicinal, 'habitat' => $habitat, 'body' => $body,
            'image' => $img, 'image_credit' => $credit, 'status' => 'published']);
        $n++;
    }
    if ($n) $log[] = "$n species";
    $n = 0;
    foreach ($more['media'] as [$file, $title, $desc, $credit]) {
        if ($val('SELECT id FROM media WHERE file=?', ['assets/img/culture/' . $file . '.jpg'])) continue;
        $ins('media', ['type' => 'photo', 'title' => $title, 'description' => $desc, 'file' => 'assets/img/culture/' . $file . '.jpg', 'album' => 'Moments from Lango', 'credit' => $credit, 'status' => 'published']);
        $n++;
    }
    if ($n) $log[] = "$n gallery photos";
    $n = 0;
    foreach (require $dir . '/lango_photos.php' as [$file, $album, $title, $desc, $credit, $page]) {   // free-licence photographs from Wikimedia Commons
        if ($val('SELECT id FROM media WHERE file=?', [$file])) continue;
        $ins('media', ['type' => 'photo', 'title' => $title, 'description' => $desc, 'file' => $file, 'album' => $album, 'credit' => $credit, 'source' => $page, 'status' => 'published']);
        $n++;
    }
    if ($n) $log[] = "$n Commons photos";
    // the photographs on district pages and on heritage entries that only had a stock picture (an editor's own picture is never replaced)
    $lp = require $dir . '/lango_photos.php';
    foreach (['lira-city' => 'sunset-lira', 'lira' => 'ngetta-hill', 'oyam' => 'kraal-cows', 'apac' => 'great-harvest', 'dokolo' => 'olweny-swamp', 'amolatar' => 'uganda-centre-monument', 'kwania' => 'kwania-rock'] as $dist => $k) {
        $upd('districts', ['image' => $lp[$k][0], 'image_credit' => $lp[$k][4]], "slug=? AND (image IS NULL OR image='')", [$dist]);
    }
    foreach (['abuda' => 'abuda-dancers', 'ngetta-rock' => 'ngetta-hill', 'amolatar-monument' => 'uganda-centre-monument', 'lake-kwania-kyoga' => 'lake-kwania', 'pottery' => 'museum-crafts',
              'mats-baskets-and-cords' => 'museum-interior', 'smiths-and-metalwork' => 'museum-spears', 'marriage-customs' => 'traditional-marriage'] as $slug => $k) {
        $upd('heritage_items', ['image' => $lp[$k][0], 'image_credit' => $lp[$k][4]], "slug=? AND (image IS NULL OR image='' OR image LIKE 'assets/img/photos/hero-%')", [$slug]);
    }
    $n = 0;
    foreach ($more['documents'] as [$title, $cat, $year, $desc, $address]) {
        if ($val('SELECT id FROM documents WHERE title=?', [$title])) continue;
        $ins('documents', ['title' => $title, 'category' => $cat, 'year' => $year, 'description' => $desc, 'file' => $address, 'status' => 'published']);
        $n++;
    }
    if ($n) $log[] = "$n reading-list documents";

    if (!(int)$val('SELECT COUNT(*) FROM welcome_photos')) {   // only when nobody has set up the opening photos yet
        foreach (require $dir . '/welcome_photos.php' as $i => [$file, $caption, $credit, $focus, $status]) {
            $ins('welcome_photos', ['title' => $caption, 'image' => $file, 'credit' => $credit, 'focus' => $focus, 'sort' => $i + 1, 'status' => $status]);
        }
        $log[] = 'opening photos';
    }

    // history
    $hist = require $dir . '/history.php';
    foreach ($hist['sections'] as $s) {
        if (!$val('SELECT id FROM history_sections WHERE slug=?', [$s['slug']])) { $ins('history_sections', $s + ['status' => 'published']); $log[] = "history {$s['slug']}"; }
    }
    foreach ([['who-are-the-lango', 'dancers-group', 'Photograph supplied for this project; the photographer is being confirmed.'],
              ['clans-and-society', 'chief-buchta', 'A Lango chief in a horned head-dress. Photograph by Richard Buchta (died 1894), published in The Living Races of Mankind (1902). Public domain.'],
              ['lango-at-a-glance', 'harvest', 'Photograph supplied for this project; the photographer is being confirmed.']] as [$hs, $file, $credit]) {
        $upd('history_sections', ['image' => 'assets/img/culture/' . $file . '.jpg', 'image_credit' => $credit], "slug=? AND (image IS NULL OR image='')", [$hs]);
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
        foreach ($hist['terms'] as [$term, $meaning]) $ins('glossary', ['term' => $term, 'meaning' => $meaning, 'note' => 'Cultural term', 'sort' => ++$i]);
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

    // Bible links (the Bible Society of Uganda's Lango versions on Bible.com); inserted only when absent, so an edit or a cleared field stays.
    foreach (['bible_link_full' => 'https://www.bible.com/versions/1974', 'bible_link_nt' => 'https://www.bible.com/versions/472'] as $k => $v) {
        if ($val('SELECT COUNT(*) FROM settings WHERE k=?', [$k]) == 0) { $ins('settings', ['k' => $k, 'v' => $v]); $log[] = "setting $k"; }
    }

    // communities (Lango, Acholi): their own copies of the content tables, and the Acholi content
    require_once __DIR__ . '/communities.php';
    return array_merge($log, lci_rebrand($pdo), lci_communities($pdo));
}

/**
 * One-time clean-up for sites installed while this website still presented itself as the Lango Cultural Institution (LCI). It is an
 * independent Lango website: settings that still hold the old defaults are replaced, the old starter content (leadership, programmes, sample
 * news and events, answers about the institution) is unpublished, and wording that credited the institution is reworded.
 * Only values that are still exactly as installed are touched, so anything an editor changed stays; nothing is deleted (an editor can publish
 * a row again); the setting brand_rev makes it run once.
 */
function lci_rebrand(PDO $pdo): array
{
    $rev = 'lango-1';
    $get = $pdo->prepare("SELECT v FROM settings WHERE k='brand_rev'");
    $get->execute();
    if ($get->fetchColumn() === $rev) return [];
    $log = [];
    $run = function (string $sql, array $p = []) use ($pdo): int {
        try { $st = $pdo->prepare($sql); $st->execute($p); return $st->rowCount(); } catch (Throwable $e) { return 0; }   // a table or column this site does not have
    };

    foreach ([
        'site_name'    => ['Lango Cultural Institution', 'Lango'],
        'site_tagline' => ['Our Culture. Our People. Our Future.', 'Everything about Lango, in one place.'],
        'org_subtitle' => ['Won Nyaci me Lango', ''],
        'phone'        => ['+256 772 123 456', ''],
        'address'      => ['Lira City, Northern Uganda', ''],
        'email'        => ['info@langoculture.org', ''],
    ] as $k => [$from, $to]) {
        if ($run('UPDATE settings SET v=? WHERE k=? AND v=?', [$to, $k, $from])) $log[] = "setting $k";
    }

    $n = 0;
    foreach ([
        ['posts', 'title', ['Lango Cultural Festival: Celebrating Heritage and Unity', 'Welcome to the new LCI website and the Lango App', 'How to contribute to the Lango heritage archive']],
        ['events', 'title', ['Lango Cultural Festival & Heritage Showcase', 'Annual Clans Convention', 'Youth & Education Forum', 'Greening Lango Campaign']],
    ] as [$t, $c, $titles]) {
        $n += $run("UPDATE `$t` SET status='draft' WHERE status='published' AND `$c` IN (" . implode(',', array_fill(0, count($titles), '?')) . ')', $titles);
    }
    $n += $run("UPDATE programs SET status='draft' WHERE status='published'");
    $n += $run("UPDATE leaders SET status='draft' WHERE status='published'");
    $n += $run("UPDATE history_sections SET status='draft' WHERE slug='lci-today' AND status='published'");
    $n += $run("UPDATE knowledge SET status='draft' WHERE status='published' AND (question LIKE '%Cultural Institution%' OR answer LIKE '%Cultural Institution%' OR question LIKE '%Won Nyaci%' OR answer LIKE '%Won Nyaci%' OR question LIKE '%How many clans are there%')");
    if ($n) $log[] = "$n old rows unpublished";

    $fix = [
        ['A meeting place of the Lango traditional institution, used for cultural events and gatherings.', 'A meeting place in Lira, used for cultural events and gatherings.'],
        ['a meeting place linked to the Lango traditional institution, where cultural events and gatherings are held.', 'a meeting place where cultural events and gatherings are held.'],
        ['<blockquote>The Lango Cultural Institution unites 149 registered clans under the leadership of the Won Nyaci, the cultural leader of the Lango people.</blockquote>', ''],
        ['<h3>The account preserved by LCI</h3>', '<h3>The account preserved in oral tradition</h3>'],
        ['Lango oral traditions recorded by the Lango Cultural Institution trace', 'Lango oral traditions trace'],
        ["In LCI's words, the clan leader is the <strong>Awitong</strong>", 'The clan leader is the <strong>Awitong</strong>'],
        ["LCI's records note that missionary education", 'Records note that missionary education'],
        ['LCI invites families to share their own memories through our oral history programme so that', 'families are invited to share their own memories through the contribution form so that'],
        ['LCI records that the ancestors', 'Oral tradition records that the ancestors'],
        [', as used by the Lango Cultural Institution.', '.'],
        ['Each of the 149 registered clans is represented by its Awitong.', 'Each clan is represented by its Awitong.'],
        ['A traditional Lango dance safeguarded by LCI as part of its cultural preservation programme.', 'A traditional Lango dance.'],
        ['A traditional Lango dance preserved and promoted by the Lango Cultural Institution.', 'A traditional Lango dance.'],
        [', safeguarded by the Lango Cultural Institution as part of its cultural preservation programme.', '.'],
        ['Okeme is one of the dances that the Lango Cultural Institution safeguards through its cultural preservation programme.', 'Okeme is one of the best-known Lango dances.'],
        ['Check with LCI for the dates of festivals and public events before you visit.', 'Check locally for the dates of festivals and public events before you visit.'],
        ['a gathering place linked to the Lango traditional leadership,', 'a gathering place'],
        ['Supplied by the Lango Cultural Institution.', 'Source being confirmed.'],
        ['LCI presents his story here as part of the history that young people should know and discuss.', 'His story is presented here as part of the history that young people should know and discuss.'],
        ['which is why LCI invites elders and families to record what they know of him as part of the oral history programme.', 'which is why elders and families are invited to record what they know of him through the contribution form.'],
    ];
    $changed = 0;
    foreach ([['history_sections', 'summary'], ['history_sections', 'body'], ['timeline', 'body'], ['glossary', 'meaning'], ['glossary', 'note'], ['heritage_items', 'summary'], ['heritage_items', 'body'],
              ['notables', 'summary'], ['notables', 'body'], ['clans', 'source_note'], ['districts', 'description'], ['districts', 'attractions']] as [$t, $c]) {
        foreach ($fix as [$old, $new]) $changed += $run("UPDATE `$t` SET `$c`=REPLACE(`$c`, ?, ?) WHERE `$c` LIKE ?", [$old, $new, '%' . addcslashes($old, '%_\\') . '%']);
    }
    if ($changed) $log[] = "$changed texts reworded";
    $run("UPDATE glossary SET note='Cultural term' WHERE note='Institutional term'");
    if (!$pdo->query("SELECT COUNT(*) FROM post_categories WHERE slug='announcements'")->fetchColumn()) $run("UPDATE post_categories SET name='Announcements', slug='announcements' WHERE slug='royal-messages'");
    if ($run("UPDATE dictionary SET source='Lango dictionary project import' WHERE source='LCI language project import'")) $log[] = 'dictionary source';

    $marked = $pdo->query("SELECT COUNT(*) FROM settings WHERE k='brand_rev'")->fetchColumn();
    $run($marked ? "UPDATE settings SET v=? WHERE k='brand_rev'" : "INSERT INTO settings (v, k) VALUES (?, 'brand_rev')", [$rev]);
    return $log;
}
