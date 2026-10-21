<?php
declare(strict_types=1);

/** Deterministic "word of the day": changes at midnight, same for every visitor. */
function word_of_day(): ?array
{
    $n = (int)val("SELECT COUNT(*) FROM dictionary WHERE status='published' AND CHAR_LENGTH(english)<70 AND CHAR_LENGTH(lango)<24");
    if (!$n) return null;
    $off = (int)(crc32(date('Y-m-d')) % $n);
    return one("SELECT * FROM dictionary WHERE status='published' AND CHAR_LENGTH(english)<70 AND CHAR_LENGTH(lango)<24 ORDER BY id LIMIT 1 OFFSET " . $off);
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
    'clan' => 'Clan', 'heritage' => 'Heritage', 'word' => 'Dictionary', 'proverb' => 'Proverb', 'news' => 'News', 'event' => 'Event',
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

/** Public stats used by the transparency page and API. */
function donation_totals(): array
{
    $r = one("SELECT COUNT(*) n, COALESCE(SUM(CASE WHEN currency='UGX' THEN amount END),0) ugx, COALESCE(SUM(CASE WHEN currency='USD' THEN amount END),0) usd FROM donations WHERE status='confirmed'");
    return $r ?: ['n' => 0, 'ugx' => 0, 'usd' => 0];
}
