<?php
declare(strict_types=1);

/* ==========================================================================
   Communities (Lango, Acholi) for the app and the website: which one a request is about, a member's switch from one to the other, and
   the comparison of words between them. The content itself is kept apart by the table copies described in lib/db.php.
   ========================================================================== */

/** The communities, for the app's chooser and /ping. */
function community_list(): array
{
    $out = [];
    foreach (communities() as $code => $i) {
        $words = (int)with_community($code, fn() => val("SELECT COUNT(*) FROM dictionary WHERE status='published'"));
        $out[] = ['code' => $code, 'name' => $i['name'], 'language' => $i['language'], 'people' => $i['people'], 'register' => $i['register'], 'registers' => $i['registers'], 'leader' => $i['leader'],
            'region' => $i['region'], 'capital' => $i['capital'], 'words' => $words];
    }
    return $out;
}

/**
 * The community a request is about. A signed-in member's is their own (their clan belongs to it); anyone else says which in the request
 * (header X-App-Community or ?community=), and Lango is the default.
 */
function community_pick(?array $user): string
{
    $on = communities();   // a community that has been switched off is not offered, and a member of one falls back to the default
    $own = (string)($user['culture'] ?? '');
    if (isset($on[$own])) return $own;
    $want = strtolower(trim((string)($_SERVER['HTTP_X_APP_COMMUNITY'] ?? ($_GET['community'] ?? ''))));
    return isset($on[$want]) ? $want : community_default();
}

/**
 * Moves a member to another community. Their clan details belong to one community (a clan of one is not a clan of the other), so the ones in use
 * are put aside and the ones put aside earlier for the community they are going to come back.
 */
function member_set_community(array $u, string $to): array
{
    $from = isset(communities(true)[$u['culture'] ?? '']) ? (string)$u['culture'] : 'lango';
    if (!isset(communities()[$to]) || $to === $from) return $u;
    $keys = ['clan_id', 'clan_other', 'clan_joined_at', 'clan_hidden', 'clan_seen_id'];
    $state = json_decode((string)($u['culture_state'] ?? ''), true);
    $state = is_array($state) ? $state : [];
    $state[$from] = array_intersect_key($u, array_flip($keys));
    $next = is_array($state[$to] ?? null) ? $state[$to] : [];
    unset($state[$to]);
    $next += ['clan_id' => null, 'clan_other' => null, 'clan_joined_at' => null, 'clan_hidden' => 0, 'clan_seen_id' => 0];
    update('users', array_intersect_key($next, array_flip($keys)) + ['culture' => $to, 'culture_state' => json_encode($state)], (int)$u['id']);
    return one('SELECT * FROM users WHERE id=?', [$u['id']]) ?? $u;
}

/** One dictionary entry as the comparison shows it. */
function compare_entry(array $r): array
{
    return ['headword' => $r['lango'], 'english' => $r['english'], 'pos' => $r['pos'] ?: null, 'verified' => (bool)$r['verified']];
}

/**
 * Words side by side across the two languages, found through their English meanings (neither dictionary links to the other, so a match is
 * a likely equivalent, never a checked one). The term may be English, Leb Lango or Leb Acholi: its English meanings are worked out first, then
 * each is looked up in both dictionaries. Returns ['term', 'groups' => [['english', 'lango' => [...], 'acholi' => [...]]], 'available' => [community => word count]].
 */
function compare_words(string $term, int $groups = 6, int $each = 5): array
{
    $term = mb_substr(trim($term), 0, 60);
    $available = [];
    $codes = array_keys(communities());
    foreach ($codes as $c) $available[$c] = (int)with_community($c, fn() => val("SELECT COUNT(*) FROM dictionary WHERE status='published'"));
    $names = array_map(fn($i) => ['code' => $i['code'], 'name' => $i['name'], 'language' => $i['language']], array_values(communities()));
    if ($term === '') return ['term' => '', 'groups' => [], 'available' => $available, 'communities' => $names];

    $glosses = [];
    $add = function (string $text) use (&$glosses): void {
        foreach (preg_split('/[;,·\/]/u', preg_replace('/\([^)]*\)/u', '', $text)) as $g) {
            $g = mb_strtolower(trim($g));
            if ($g !== '' && mb_strlen($g) <= 40 && !in_array($g, $glosses, true)) $glosses[] = $g;
        }
    };
    $like = '%' . like_escape($term) . '%'; $fold = '%' . like_escape(lango_fold($term)) . '%';
    $plain = mb_strtolower($term);
    // the term as an English word, and as a word of either language (its English meanings become the English words to compare)
    $isEnglish = false;
    foreach ($codes as $c) {
        if (with_community($c, fn() => val("SELECT COUNT(*) FROM dictionary WHERE status='published' AND english REGEXP ?", ['\\b' . preg_quote($plain, '/') . '\\b']))) { $isEnglish = true; break; }
    }
    if ($isEnglish) $glosses[] = $plain;
    foreach ($codes as $c) {
        $rows = with_community($c, fn() => all("SELECT english FROM dictionary WHERE status='published' AND (lango=? OR fold=? OR fold LIKE ?) ORDER BY (lango=?) DESC, CHAR_LENGTH(english) LIMIT 4", [$term, lango_fold($term), $fold, $term]));
        foreach ($rows as $r) $add($r['english']);
    }
    $glosses = array_slice($glosses, 0, $groups);

    $out = [];
    foreach ($glosses as $g) {
        $row = ['english' => $g];
        foreach ($codes as $c) {
            $row[$c] = with_community($c, fn() => array_map('compare_entry', all(
                "SELECT lango, english, pos, verified FROM dictionary WHERE status='published' AND english REGEXP ? ORDER BY (english=?) DESC, verified DESC, CHAR_LENGTH(english), lango LIMIT " . (int)$each,
                ['\\b' . preg_quote($g, '/') . '\\b', $g])));
        }
        if ($row['lango'] || $row['acholi']) $out[] = $row;
    }
    return ['term' => $term, 'groups' => $out, 'available' => $available, 'communities' => $names];
}

/* ---------- the website ---------- */

/**
 * Picks the community for a page of the website (or the admin panel). A signed-in member's is their own, and choosing another one with
 * ?community= saves it on their account; a visitor's choice is kept in the session and a cookie. The admin panel keeps its own choice (the
 * community whose content is being edited), which is never taken from the cookie.
 */
function community_web_init(): void
{
    $admin = defined('LCI_ADMIN');
    $key = $admin ? 'admin_community' : 'community';
    $want = strtolower(trim((string)($_GET['community'] ?? '')));
    $member = $admin ? null : user();
    $known = communities($admin);   // the admin panel can edit a community before it is switched on; visitors only get the ones that are on
    if (isset($known[$want])) {
        $_SESSION[$key] = $want;
        if (!$admin && !headers_sent()) setcookie('lci_community', $want, ['expires' => time() + 31536000, 'path' => BASE === '' ? '/' : BASE . '/', 'samesite' => 'Lax']);
        if ($member && ($member['culture'] ?? 'lango') !== $want) $member = member_set_community($member, $want);
        if ($member) user(true);
    }
    $c = $admin ? ($_SESSION[$key] ?? 'lango') : ($member['culture'] ?? $_SESSION[$key] ?? $_COOKIE['lci_community'] ?? 'lango');
    community(isset($known[$c]) ? (string)$c : community_default());
}

/**
 * The wording of a Lango page for another community: the names and the clan words become the community's (Lango to Acholi, clan to chiefdom,
 * Awitong to Rwot). Phrases that carry a Lango fact are rewritten first, so the result is still true.
 */
function community_words(string $s): string
{
    $i = community_info();
    if ($i['prefix'] === '') return $s;
    static $facts = null;
    if ($facts === null || ($facts['_for'] ?? '') !== $i['code']) {
        $facts = ['_for' => $i['code']];
        // sentences that state a Lango fact: replaced by a neutral one, or by the community's own region and capital (Admin, Communities)
        $region = $i['region'] !== '' ? $i['region'] : 'Uganda';
        $capital = $i['capital'] !== '' ? $i['capital'] : 'the main city';
        $pairs = [
            'mid-northern Uganda' => $region, 'Lira City, Northern Uganda' => $capital . ', ' . $region, 'Lira City' => $capital,
            'Nine districts and one city make up the Lango sub-region.' => 'The districts and cities of the ' . $i['name'] . ' sub-region.',
            'Histories, totems, lineages and leaders of the 149 Lango clans.' => 'Histories, totems, lineages and leaders of the ' . mb_strtolower($i['registers']) . '.',
        ];
        $pairs['The Lango are a Nilotic people of north-central Uganda. Oral tradition remembers a long migration from the north, a society built on clans rather than kings, a colonial era that created chiefs where there had been none, and a post-independence history in which Lango sons and daughters stood at the centre of the nation.'] = 'The ' . $i['name'] . ' people of ' . $region . '.';
        $pairs['Lango men and women have led a nation, won Olympic gold, commanded armies, healed patients and kept the peace in their villages. Their stories belong to every child in Lango.'] = 'Their stories belong to every child in ' . $i['name'] . '.';
        $pairs += [
            'Songs, dances such as Okeme and Abuda, and the stories told by elders.' => 'Songs, dances and the stories told by elders.',
            ' and the worked examples of the Practical Lango Grammar' => '',
            'Unmarked entries come from the original Lango dictionary project and are still being reviewed.' => 'Unmarked entries have not been checked by a language reviewer yet.',
            'The same API will power the Lango mobile app.' => 'The same API powers the mobile app.',
        ];
        $facts += $pairs;
    }
    $s = strtr($s, array_diff_key($facts, ['_for' => 1]));
    return (string)preg_replace(
        ['/\bLeb Lango\b/', '/\bLangos\b/', '/\bLango\b/', '/\bAwitong\b/', '/\bclans\b/', '/\bClans\b/', '/\bclan\b/', '/\bClan\b/'],
        [$i['language'], $i['people'], $i['name'], $i['leader'], mb_strtolower($i['registers']), $i['registers'], mb_strtolower($i['register']), $i['register']], $s);
}

/** Everyday greetings for the language pages and the app: Lango's come from the Practical Lango Grammar; another community has none until its speakers write them. */
function community_greetings(): array
{
    return community() === 'lango' ? require ROOT . '/app/data/greetings.php' : [];
}

/* ---------- translation between languages ---------- */

/** The languages a translation can go between: English and the language of every community that is switched on. */
function translation_languages(): array
{
    $out = [['code' => 'en', 'name' => 'English']];
    foreach (communities() as $c => $i) $out[] = ['code' => $c, 'name' => $i['language']];
    return $out;
}

/** The English meanings of a word of a community's dictionary (its first few entries), as single words and short phrases. */
function community_glosses(string $term, string $c, int $max = 6): array
{
    $out = [];
    $rows = with_community($c, fn() => all("SELECT english FROM dictionary WHERE status='published' AND (lango=? OR fold=?) ORDER BY (lango=?) DESC, verified DESC, CHAR_LENGTH(english) LIMIT 4", [$term, lango_fold($term), $term]));
    foreach ($rows as $r) {
        foreach (preg_split('/[;,·\/]/u', (string)preg_replace('/\([^)]*\)/u', '', (string)$r['english'])) as $g) {
            $g = mb_strtolower(trim((string)preg_replace('/^(?:a|an|the|to)\s+/u', '', trim($g))));
            if ($g !== '' && mb_strlen($g) <= 40 && !in_array($g, $out, true)) $out[] = $g;
        }
    }
    return array_slice($out, 0, $max);
}

/**
 * Translates between two of the languages in translation_languages(). English to or from a community's language uses that community's own
 * translator. Between two communities' languages there is no direct dictionary: the word's English meanings are looked up in the other dictionary,
 * so the result is a likely equivalent ('likely' => true), never a checked one. Words and set phrases only; sentences are still to come.
 */
function translate_between(string $text, string $from, string $to): array
{
    $text = mb_substr(trim($text), 0, 600);
    if ($from === 'en') return with_community($to, fn() => lango_translate($text, 'en-lango'));
    if ($to === 'en') return with_community($from, fn() => lango_translate($text, 'lango-en'));

    $text = lango_fold($text, false);
    preg_match_all("/[\p{L}\p{M}]+(?:['\-][\p{L}\p{M}]+)*/u", $text, $w);
    $glosses = community_glosses($text, $from);
    if (!$glosses) {
        if (count($w[0]) > 1) return ['translation' => '', 'unknown_words' => [], 'coming_soon' => true, 'words' => array_slice(array_values(array_unique($w[0])), 0, 12),
            'message' => 'Sentence translation is coming soon. For now translate one word at a time.'];
        return ['translation' => '', 'unknown_words' => [$text]];
    }
    $cand = [];
    foreach ($glosses as $g) {
        $rows = with_community($to, fn() => all("SELECT lango, english, verified FROM dictionary WHERE status='published' AND english REGEXP ? ORDER BY (english=?) DESC, verified DESC, CHAR_LENGTH(english), lango LIMIT 4", ['\b' . preg_quote($g, '/') . '\b', $g]));
        foreach ($rows as $r) if (!isset($cand[$r['lango']])) $cand[$r['lango']] = ['headword' => $r['lango'], 'english' => $r['english'], 'meaning' => $g, 'verified' => (bool)$r['verified']];
    }
    $top = array_slice(array_values($cand), 0, 5);
    // the answer is the words whose whole meaning is the English word; the other matches (a water pot, a worm that lives in water) stay in the candidates
    $exact = array_values(array_filter($top, fn($c) => mb_strtolower(trim((string)$c['english'])) === $c['meaning']));
    $answer = array_slice($exact ?: $top, 0, $exact ? 3 : 1);
    return ['translation' => implode('; ', array_column($answer, 'headword')), 'unknown_words' => $top ? [] : [$text], 'likely' => true, 'via' => $glosses, 'candidates' => $top];
}
