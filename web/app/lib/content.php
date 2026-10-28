<?php
declare(strict_types=1);

/** Deterministic "word of the day": changes at midnight, same for every visitor. */
/** English words that must not come up as a daily word or a quiz question (a family app, used in schools and churches). */
const DAILY_SENSITIVE = 'prostitut|whore|sex|penis|vagina|vulva|testicl|genital|rape|semen|erect|anus|buttock|faec|excrement|urine|menstru|intercourse|fornicat|adulter|condom|lust|naked|nude|breast|vomit|corpse';

/** SQL that keeps only dictionary entries that are short, clear and suitable for a quiz. */
function daily_word_filter(string $t = ''): string
{
    static $verifiedOnly = null;   // once LCI's language experts have checked a good number of entries, only those are used
    $verifiedOnly ??= (int)val("SELECT COUNT(*) FROM dictionary WHERE status='published' AND verified=1") >= 100;
    $p = $t === '' ? '' : $t . '.';
    return ($verifiedOnly ? "{$p}verified=1 AND " : '') . "{$p}status='published' AND CHAR_LENGTH({$p}english)<70 AND CHAR_LENGTH({$p}lango)<24 AND {$p}lango NOT LIKE '% %' AND {$p}english NOT REGEXP '" . DAILY_SENSITIVE . "' AND {$p}lango NOT REGEXP '[0-9]'";
}

function word_of_day(): ?array
{
    $n = (int)val('SELECT COUNT(*) FROM dictionary WHERE ' . daily_word_filter());
    if (!$n) return null;
    $off = (int)(crc32(date('Y-m-d')) % $n);
    return one('SELECT * FROM dictionary WHERE ' . daily_word_filter() . ' ORDER BY id LIMIT 1 OFFSET ' . $off);
}

function like_escape(string $s): string { return addcslashes($s, '%_\\'); }

/**
 * Search across the whole Data Center. Returns a flat list of ['type','title','text','url','img'].
 */
function search_all(string $term, int $limit = 60, ?string $only = null): array
{
    $term = trim($term);
    if (mb_strlen($term) < 2) return [];
    $like = '%' . like_escape($term) . '%';
    $out = [];
    $add = function (string $type, string $sql, array $p, callable $map) use (&$out, $only) {
        if ($only && $only !== $type) return;
        foreach (all($sql, $p) as $r) $out[] = ['type' => $type] + $map($r);
    };
    $add('clan', "SELECT * FROM clans WHERE status='published' AND (name LIKE ? OR totem LIKE ? OR summary LIKE ? OR history LIKE ?) LIMIT 12", [$like, $like, $like, $like],
        fn($r) => ['title' => $r['name'], 'text' => $r['summary'] ?: excerpt($r['history']), 'url' => url('clans/' . $r['slug']), 'img' => $r['image']]);
    $add('heritage', "SELECT * FROM heritage_items WHERE status='published' AND (title LIKE ? OR summary LIKE ? OR body LIKE ?) LIMIT 12", [$like, $like, $like],
        fn($r) => ['title' => t($r, 'title'), 'text' => $r['summary'] ?: excerpt($r['body']), 'url' => url('heritage/item/' . $r['slug']), 'img' => $r['image']]);
    $add('word', "SELECT * FROM dictionary WHERE status='published' AND (lango LIKE ? OR english LIKE ?) ORDER BY (lango=?) DESC, CHAR_LENGTH(lango) LIMIT 15", [$like, $like, $term],
        fn($r) => ['title' => $r['lango'], 'text' => $r['english'], 'url' => url('language/dictionary', ['q' => $r['lango']]), 'img' => null]);
    $add('proverb', "SELECT * FROM proverbs WHERE status='published' AND (lango LIKE ? OR english LIKE ? OR meaning LIKE ?) LIMIT 8", [$like, $like, $like],
        fn($r) => ['title' => excerpt($r['lango'], 80), 'text' => $r['english'] ?? '', 'url' => url('language/proverbs'), 'img' => null]);
    $add('news', "SELECT * FROM posts WHERE status='published' AND published_at<=NOW() AND (title LIKE ? OR excerpt LIKE ? OR body LIKE ?) ORDER BY published_at DESC LIMIT 10", [$like, $like, $like],
        fn($r) => ['title' => t($r, 'title'), 'text' => t($r, 'excerpt') ?: excerpt($r['body']), 'url' => url('news/' . $r['slug']), 'img' => $r['image']]);
    $add('event', "SELECT * FROM events WHERE status='published' AND (title LIKE ? OR summary LIKE ?) ORDER BY starts_at DESC LIMIT 6", [$like, $like],
        fn($r) => ['title' => t($r, 'title'), 'text' => $r['summary'], 'url' => url('events/' . $r['slug']), 'img' => $r['image']]);
    $add('person', "SELECT * FROM notables WHERE status='published' AND (name LIKE ? OR title LIKE ? OR summary LIKE ? OR body LIKE ?) LIMIT 8", [$like, $like, $like, $like],
        fn($r) => ['title' => $r['name'], 'text' => $r['summary'], 'url' => url('people/' . $r['slug']), 'img' => $r['photo']]);
    $add('district', "SELECT * FROM districts WHERE status='published' AND (name LIKE ? OR headquarters LIKE ? OR known_for LIKE ? OR description LIKE ?) LIMIT 8", [$like, $like, $like, $like],
        fn($r) => ['title' => $r['name'], 'text' => excerpt($r['description'], 180), 'url' => url('districts/' . $r['slug']), 'img' => $r['image']]);
    $add('history', "SELECT * FROM history_sections WHERE status='published' AND (title LIKE ? OR summary LIKE ? OR body LIKE ?) LIMIT 6", [$like, $like, $like],
        fn($r) => ['title' => $r['title'], 'text' => $r['summary'], 'url' => url('history') . '#' . $r['slug'], 'img' => null]);
    $add('program', "SELECT * FROM programs WHERE status='published' AND (title LIKE ? OR summary LIKE ?) LIMIT 6", [$like, $like],
        fn($r) => ['title' => t($r, 'title'), 'text' => $r['summary'], 'url' => url('programs/' . $r['slug']), 'img' => $r['image']]);
    $add('species', "SELECT * FROM species WHERE status='published' AND (name LIKE ? OR name_lg LIKE ? OR scientific_name LIKE ? OR body LIKE ?) LIMIT 8", [$like, $like, $like, $like],
        fn($r) => ['title' => $r['name'] . ($r['name_lg'] ? ' (' . $r['name_lg'] . ')' : ''), 'text' => excerpt($r['body']), 'url' => url('species/' . $r['slug']), 'img' => $r['image']]);
    $add('document', "SELECT * FROM documents WHERE status='published' AND (title LIKE ? OR description LIKE ?) LIMIT 8", [$like, $like],
        fn($r) => ['title' => $r['title'], 'text' => excerpt($r['description']), 'url' => url('library/documents'), 'img' => null]);
    $add('media', "SELECT * FROM media WHERE status='published' AND (title LIKE ? OR description LIKE ? OR album LIKE ?) LIMIT 8", [$like, $like, $like],
        fn($r) => ['title' => $r['title'], 'text' => excerpt($r['description']), 'url' => url('media', ['type' => $r['type']]), 'img' => $r['type'] === 'photo' ? $r['file'] : null]);
    return array_slice($out, 0, $limit);
}

const SEARCH_TYPE_LABELS = [
    'clan' => 'Clan', 'heritage' => 'Heritage', 'word' => 'Dictionary', 'proverb' => 'Proverb', 'person' => 'Notable Lango', 'district' => 'District', 'history' => 'History', 'news' => 'News', 'event' => 'Event',
    'program' => 'Program', 'species' => 'Biodiversity', 'document' => 'Document', 'media' => 'Media',
];

/**
 * Retrieval-based cultural assistant. It answers ONLY from reviewed archive content
 * (knowledge base, dictionary, clans, heritage, proverbs) and shows its sources.
 * Swap in a hosted LLM later by replacing assistant_answer() — the same data feeds both.
 */
function assistant_answer(string $q): array
{
    $ql = mb_strtolower(trim($q));
    $sources = [];
    $html = '';

    // 1. dictionary look-ups: "what does X mean", "translate X", "X in lango"
    if (preg_match('/(?:what does|meaning of|translate|how do you say|how to say|word for)\s+["\']?([^"\'?]+?)["\']?(?:\s+(?:mean|in lango|in leb lango|in english))?\s*\??$/iu', $q, $m) || preg_match('/^(\p{L}[\p{L}\' ]{1,30})\s+(?:in lango|in english)\??$/iu', $q, $m)) {
        $w = trim($m[1]);
        $rows = all("SELECT * FROM dictionary WHERE status='published' AND (lango=? OR english=? OR english LIKE ? OR lango LIKE ?) ORDER BY (lango=?) DESC, (english=?) DESC, CHAR_LENGTH(english) LIMIT 4",
            [$w, $w, like_escape($w) . '%', like_escape($w) . '%', $w, $w]);
        if ($rows) {
            $html = '<strong>' . e(__('From the Lango dictionary:')) . '</strong><br>';
            foreach ($rows as $r) $html .= '<em>' . e($r['lango']) . '</em> — ' . e($r['english']) . ($r['verified'] ? '' : ' <small>(' . e(__('not yet verified')) . ')</small>') . '<br>';
            $sources[] = ['title' => 'Dictionary', 'url' => url('language/dictionary', ['q' => $w])];
            return ['answer_html' => $html, 'sources' => $sources];
        }
    }

    // 1b. entities: clan leaders and clans, districts, notable people — answered straight from the register
    if (($entity = assistant_entity_answer($q)) !== null) return $entity;

    // 2. knowledge base: score by shared keywords
    $words = array_values(array_filter(preg_split('/[^\p{L}\p{N}]+/u', $ql), fn($w) => mb_strlen($w) > 2 && !in_array($w, ['the', 'and', 'what', 'who', 'are', 'how', 'many', 'does', 'about', 'tell', 'lango', 'which', 'where', 'from', 'for', 'with', 'that', 'this', 'have', 'there'], true)));
    $best = null; $bestScore = 0;
    foreach (all("SELECT * FROM knowledge WHERE status='published'") as $k) {
        $hay = mb_strtolower($k['question'] . ' ' . $k['answer'] . ' ' . $k['topic']);
        $s = 0;
        foreach ($words as $w) if (str_contains($hay, $w)) $s += str_contains(mb_strtolower($k['question']), $w) ? 3 : 1;
        if ($s > $bestScore) { $bestScore = $s; $best = $k; }
    }
    if ($best && $bestScore >= 3) {
        $html = e($best['answer']);
        $sources[] = ['title' => $best['topic'] ?: 'Knowledge base', 'url' => url('about')];
    }

    // 3. supporting archive hits (only when the knowledge base had no direct answer)
    $hits = $html !== '' ? [] : array_values(array_filter(search_all(implode(' ', array_slice($words, 0, 3)) ?: $q, 6), fn($h) => in_array($h['type'], ['clan', 'heritage', 'species', 'proverb', 'news'], true)));
    foreach (array_slice($hits, 0, 3) as $h) {
        if (!$html) $html = e($h['title']) . ' — ' . e(excerpt($h['text'], 220));
        $sources[] = ['title' => $h['title'], 'url' => $h['url']];
        if (count($sources) >= 3) break;
    }

    if (!$html) {
        $html = e(__("I could not find that in the Lango archive yet. I only answer from reviewed LCI content, so I will not guess. You can search the Data Center, or contribute what you know so our cultural experts can add it."));
        $sources[] = ['title' => __('Search the Data Center'), 'url' => url('library')];
    }
    return ['answer_html' => $html, 'sources' => $sources];
}

/** Whole-word match of $needle (already lowercase) inside $hay (lowercase). */
function has_word(string $hay, string $needle): bool
{
    return $needle !== '' && preg_match('/(?<![\p{L}\p{N}])' . preg_quote($needle, '/') . '(?![\p{L}\p{N}])/u', $hay) === 1;
}

/** The published clan a question is about (full name beats a distinctive first word). */
function find_clan_in(string $q): ?array
{
    $hay = mb_strtolower($q);
    $best = null; $score = 0;
    foreach (all("SELECT * FROM clans WHERE status='published'") as $c) {
        $name = mb_strtolower(trim($c['name']));
        $s = has_word($hay, $name) ? mb_strlen($name) + 10 : 0;
        if (!$s) { $first = explode(' ', $name)[0]; if (mb_strlen($first) >= 4 && has_word($hay, $first)) $s = mb_strlen($first); }
        if ($s > $score) { $score = $s; $best = $c; }
    }
    return $best;
}

/** The clan leader on record: the clan's own Awitong fields first, then the leadership directory. */
function clan_leader_of(array $clan): ?array
{
    if (trim((string)$clan['leader_name']) !== '') return ['name' => $clan['leader_name'], 'title' => $clan['leader_title'] ?: 'Awitong', 'photo' => $clan['leader_photo'], 'bio' => null];
    $l = one("SELECT name, title, photo, bio FROM leaders WHERE grp='awitong' AND clan_id=? AND status='published' ORDER BY sort, id LIMIT 1", [$clan['id']]);
    return $l ? ['name' => $l['name'], 'title' => $l['title'] ?: 'Awitong', 'photo' => $l['photo'], 'bio' => $l['bio']] : null;
}

/** Answers about clans (leaders in particular), districts and notable people straight from the register. */
function assistant_entity_answer(string $q): ?array
{
    $ql = mb_strtolower($q);
    $leaderIntent = (bool)preg_match('/\b(leader|awitong|head|chief|rwot|lead|leads|heads)\b/u', $ql);
    $clanWord = str_contains($ql, 'clan') || $leaderIntent;

    // clan questions
    $clan = $clanWord ? find_clan_in($q) : null;
    if ($clan) {
        $link = url('clans/' . $clan['slug']);
        $src = [['title' => $clan['name'] . ' clan', 'url' => $link]];
        $ld = clan_leader_of($clan);
        if ($leaderIntent) {
            if ($ld) {
                $img = $ld['photo'] ? '<img class="msg__img" src="' . e(media_url($ld['photo'])) . '" alt="' . e($ld['name']) . '">' : '';
                return ['answer_html' => $img . '<strong>' . e($ld['name']) . '</strong> ' . e(sprintf(__('is the %s of the %s clan.'), $ld['title'], $clan['name'])) . ($ld['bio'] ? '<br>' . e(excerpt($ld['bio'], 220)) : ''), 'sources' => $src];
            }
            return ['answer_html' => e(sprintf(__('The register does not have a clan leader recorded for the %s clan yet. LCI adds leaders and their photographs as each clan is verified. If you are a member, you can help by sending the details through the contact page.'), $clan['name'])), 'sources' => array_merge($src, [['title' => __('Contact LCI'), 'url' => url('contact')]])];
        }
        $d = $clan['district_id'] ? val('SELECT name FROM districts WHERE id=?', [$clan['district_id']]) : null;
        $bits = array_filter([
            $clan['summary'] ?: excerpt($clan['history'], 260),
            $clan['totem'] ? sprintf(__('Totem: %s.'), $clan['totem']) : '',
            $d ? sprintf(__('District: %s.'), $d) : '',
            $ld ? sprintf(__('%s: %s.'), $ld['title'], $ld['name']) : '',
        ]);
        return ['answer_html' => '<strong>' . e($clan['name']) . '</strong> ' . e(__('clan')) . ' — ' . e($bits ? implode(' ', $bits) : __('This clan is in the LCI register and its history is still being documented.')), 'sources' => $src];
    }
    // "clan leader of X" where X is not in the register
    if ($leaderIntent && preg_match('/(?:leader|awitong|head|chief)\s+(?:of|for)\s+(?:the\s+)?([\p{L}\' ]{2,40}?)(?:\s+clan)?\s*[?.!]*$/iu', $q, $m)) {
        return ['answer_html' => e(sprintf(__('I could not find a clan called “%s” in the register. LCI has %d registered clans and is still adding them to this website, so it may not be loaded yet.'), trim($m[1]), (int)setting('clan_total', '149'))), 'sources' => [['title' => __('Clan Explorer'), 'url' => url('clans')]]];
    }

    // notable people
    foreach (all("SELECT * FROM notables WHERE status='published' ORDER BY featured DESC, sort") as $n) {
        $aliases = array_filter(array_unique(array_merge([mb_strtolower($n['name']), mb_strtolower((string)$n['short_name'])],
            array_filter(preg_split('/[^\p{L}]+/u', mb_strtolower($n['name'])), fn($w) => mb_strlen($w) >= 5 && !in_array($w, ['rwot', 'maj.', 'warrior', 'leaders'], true)))));
        foreach ($aliases as $al) {
            if (!has_word($ql, $al)) continue;
            $life = $n['born'] ? $n['born'] . ($n['died'] ? '–' . $n['died'] : '') : '';
            $img = $n['photo'] ? '<img class="msg__img" src="' . e(media_url($n['photo'])) . '" alt="">' : '';
            return ['answer_html' => $img . '<strong>' . e($n['name']) . '</strong>' . ($life ? ' (' . e($life) . ')' : '') . ' — ' . e($n['title'] ?: '') . '. ' . e($n['summary']), 'sources' => [['title' => $n['short_name'] ?: $n['name'], 'url' => url('people/' . $n['slug'])]]];
        }
    }

    // districts (Lira City when the question says city/town/municipal; otherwise Lira District)
    $found = null;
    foreach (all("SELECT * FROM districts WHERE status='published'") as $d) {
        $nm = mb_strtolower(preg_replace('/\s+(city|district)$/i', '', $d['name']));
        if (!has_word($ql, $nm)) continue;
        $isCity = $d['kind'] === 'city';
        $wantsCity = (bool)preg_match('/\b(city|town|municipal|capital)\b/u', $ql);
        if ($nm === 'lira' && $isCity !== $wantsCity) { $found ??= $d; continue; }
        $found = $d; break;
    }
    if ($found) {
        $d = $found;
        $known = array_filter(array_map('trim', explode("\n", (string)$d['known_for'])));
        $facts = array_filter([
            $d['population'] ? sprintf(__('Population: %s (%s census).'), number_format((int)$d['population']), $d['pop_year']) : '',
            $d['headquarters'] ? sprintf(__('Headquarters: %s.'), $d['headquarters']) : '',
            $d['created_year'] ? sprintf(__('Created: %s.'), $d['created_year']) : '',
            $known ? sprintf(__('Best known for: %s.'), implode('; ', array_slice($known, 0, 4))) : '',
        ]);
        return ['answer_html' => '<strong>' . e($d['name']) . '</strong> — ' . e(excerpt($d['description'], 240)) . '<br>' . e(implode(' ', $facts)), 'sources' => [['title' => $d['name'], 'url' => url('districts/' . $d['slug'])]]];
    }
    return null;
}

/** English↔Lango translation engine (dictionary + textbook sentences), extended with community-approved words. */
function translator(): TranslationEngine
{
    static $eng = null;
    if ($eng) return $eng;
    require_once ROOT . '/app/lib/TranslationEngine.php';
    $dir = ROOT . '/app/data/translator/';
    $load = fn(string $f) => json_decode((string)file_get_contents($dir . $f), true) ?: [];
    $en = $load('english_lango_index.json'); $lg = $load('lango_english_dictionary.json'); $ph = $load('sentence_phrases.json');
    $l2e = $ph['lango_to_en'] ?? []; $e2l = $ph['en_to_lango'] ?? [];

    // words approved from community suggestions
    foreach (all("SELECT lango, english FROM dictionary WHERE status='published' AND source LIKE 'Community%'") as $r) {
        $key = mb_strtolower(trim($r['lango']));
        $meanings = array_values(array_filter(array_map('trim', preg_split('/[;,]/', $r['english']))));
        if ($key === '' || !$meanings) continue;
        $lg[$key]['lango'] = $r['lango'];
        $lg[$key]['english_meanings'] = array_values(array_unique(array_merge($lg[$key]['english_meanings'] ?? [], $meanings)));
        foreach ($meanings as $m) {
            $mk = mb_strtolower($m);
            $en[$mk]['headword'] = $en[$mk]['headword'] ?? $m;
            $en[$mk]['translations'] = array_values(array_unique(array_merge($en[$mk]['translations'] ?? [], [$r['lango']])));
        }
    }
    // folded keys so "kop ango" finds "kɔp aŋo" (existing exact keys are kept)
    foreach ($lg as $k => $v) { $f = lango_fold((string)$k); if (!isset($lg[$f])) $lg[$f] = $v; }
    foreach ($l2e as $k => $v) { $f = lango_fold((string)$k); if (!isset($l2e[$f])) $l2e[$f] = $v; }
    return $eng = new TranslationEngine($en, $lg, $e2l, $l2e);
}

/** Translate text; Lango input is folded to plain letters so ordinary keyboards work. */
function lango_translate(string $text, string $direction): array
{
    $text = mb_substr(trim($text), 0, 600);
    if ($direction === 'lango-en') $text = lango_fold($text, false);
    $r = translator()->translate($text, $direction);
    return ['translation' => $r['translation'], 'unknown_words' => $r['unknown_words']];
}

/** Public stats used by the transparency page and API. */
function donation_totals(): array
{
    $r = one("SELECT COUNT(*) n, COALESCE(SUM(CASE WHEN currency='UGX' THEN amount END),0) ugx, COALESCE(SUM(CASE WHEN currency='USD' THEN amount END),0) usd FROM donations WHERE status='confirmed'");
    return $r ?: ['n' => 0, 'ugx' => 0, 'usd' => 0];
}
