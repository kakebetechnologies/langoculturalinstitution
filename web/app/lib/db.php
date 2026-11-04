<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        global $CFG;
        $d = $CFG['db'];
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $d['host'], $d['port'] ?? 3306, $d['name']),
            $d['user'],
            $d['pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        $pdo->exec("SET time_zone = '+03:00'");
    }
    return $pdo;
}

/* ---------- communities (Lango, Acholi) ----------
   Each community has its own copy of the content tables (clans, districts, dictionary, ...). The Lango copies are the original tables; the
   Acholi copies carry the prefix ac_ and are created from them by the database upgrade. One current community per request decides which copy
   every query reads and writes: q() renames the content tables in the SQL, so no query anywhere else needs to know. Lango is the default
   and its SQL is never touched. Users, settings, contributions, donations and other shared tables are not renamed. */
/** The communities built in. The `communities` table (Admin, Communities) holds the real list, and whether each one is switched on for the app and the site. */
const COMMUNITY_DEFAULTS = [
    'lango'  => ['code' => 'lango',  'name' => 'Lango',  'language' => 'Leb Lango',  'people' => 'Lango',  'register' => 'Clan',     'registers' => 'Clans',     'leader' => 'Awitong', 'region' => 'mid-northern Uganda', 'capital' => 'Lira City', 'prefix' => '',    'enabled' => 1, 'sort' => 1],
    'acholi' => ['code' => 'acholi', 'name' => 'Acholi', 'language' => 'Leb Acholi', 'people' => 'Acholi', 'register' => 'Chiefdom', 'registers' => 'Chiefdoms', 'leader' => 'Rwot',    'region' => 'northern Uganda',     'capital' => 'Gulu City', 'prefix' => 'ac_', 'enabled' => 0, 'sort' => 2],
];
/** The tables that hold a community's own content. Each has a prefixed copy for every community except Lango. */
const COMMUNITY_TABLES = ['districts', 'clans', 'heritage_items', 'species', 'post_categories', 'posts', 'comments', 'events', 'event_registrations', 'programs', 'leaders', 'timeline', 'documents',
    'media', 'dictionary', 'proverbs', 'lessons', 'knowledge', 'professionals', 'tours', 'notables', 'notable_links', 'history_sections', 'glossary', 'welcome_photos', 'clan_messages',
    'clan_message_reports', 'quiz_sets', 'quiz_questions', 'quiz_results', 'quiz_scores', 'radio_channels', 'radio_items', 'podcast_episodes', 'bible_books', 'bible_verses'];

/**
 * The communities, by code: only the ones switched on, or all of them (the admin panel edits a community before it is switched on).
 * Read straight from the database, not through q(), because q() needs to know the community. Before the upgrade has created the table, Lango alone.
 */
function communities(bool $all = false, bool $reset = false): array
{
    static $rows = null;
    if ($reset) $rows = null;
    if ($rows === null) {
        $rows = [];
        try {
            foreach (db()->query('SELECT * FROM communities ORDER BY sort, id')->fetchAll() as $r) {
                $rows[$r['code']] = ['code' => $r['code'], 'name' => $r['name'], 'language' => $r['language'], 'people' => $r['people'], 'register' => $r['register_one'], 'registers' => $r['register_many'],
                    'leader' => $r['leader'], 'region' => (string)$r['region'], 'capital' => (string)$r['capital'], 'prefix' => $r['prefix'], 'enabled' => (int)$r['enabled'], 'sort' => (int)$r['sort']];
            }
        } catch (Throwable $e) { /* not created yet */ }
        if (!$rows) $rows = ['lango' => COMMUNITY_DEFAULTS['lango']];
    }
    return $all ? $rows : array_filter($rows, fn($r) => $r['enabled'] === 1);
}

/** The community a person gets when they have not chosen one: Lango, or the first one that is switched on. */
function community_default(): string
{
    $on = communities();
    return isset($on['lango']) ? 'lango' : (string)(array_key_first($on) ?? 'lango');
}

/** The community this request is about (a code of a known community); pass a code to change it. */
function community(?string $set = null): string
{
    static $cur = 'lango';
    if ($set !== null && isset(communities(true)[$set])) $cur = $set;
    return $cur;
}

function community_info(?string $c = null): array { return communities(true)[$c ?? community()] ?? COMMUNITY_DEFAULTS['lango']; }

/** Runs $fn with another community selected, then goes back (used to look at both communities in one request). */
function with_community(string $c, callable $fn)
{
    $was = community();
    community($c);
    try { return $fn(); } finally { community($was); }
}

/** The SQL with each content table renamed for the community in use. Lango's SQL is returned as it is. */
function community_sql(string $sql, ?string $c = null): string
{
    $code = $c ?? community();
    $prefix = communities(true)[$code]['prefix'] ?? '';
    // In the admin panel a list of contributions shows the contributions of the community being edited (members' contributions are one table for both).
    if (defined('LCI_ADMIN') && preg_match('/^\s*SELECT\b/i', $sql)) {
        $sql = (string)preg_replace_callback('/\b(FROM|JOIN)\s+contributions\b(?:\s+(?!(?:WHERE|ORDER|GROUP|LEFT|RIGHT|INNER|JOIN|LIMIT|ON|SET|HAVING|USING|UNION)\b)([A-Za-z_]\w*))?/i',
            fn($m) => $m[1] . " (SELECT * FROM contributions WHERE culture='" . $code . "') " . (($m[2] ?? '') !== '' ? $m[2] : 'contributions'), $sql);
    }
    if ($prefix === '') return $sql;
    static $names = null;
    $names ??= implode('|', COMMUNITY_TABLES);
    $sql = preg_replace('/\b(FROM|JOIN|INTO|UPDATE|TABLE|EXISTS)(\s+)(`?)(' . $names . ')\b(`?)/i', '$1$2$3' . $prefix . '$4$5', $sql);
    return preg_replace('/(?<![\w.`])(' . $names . ')\.(?=[a-z_*`])/i', $prefix . '$1.', $sql);   // a qualified column such as clans.id
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare(community_sql($sql));
    $st->execute(array_values($params));
    return $st;
}

function all(string $sql, array $params = []): array { return q($sql, $params)->fetchAll(); }

function one(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

function val(string $sql, array $params = [])
{
    $r = q($sql, $params)->fetchColumn();
    return $r === false ? null : $r;
}

function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')';
    q($sql, array_values($data));
    $id = (int)db()->lastInsertId();
    if (function_exists('notify_on_insert')) notify_on_insert($table, $data, $id);
    return $id;
}

function update(string $table, array $data, int $id): void
{
    $set = implode(',', array_map(fn($c) => "`$c`=?", array_keys($data)));
    q("UPDATE `$table` SET $set WHERE id=?", array_merge(array_values($data), [$id]));
}
