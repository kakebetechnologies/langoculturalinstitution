<?php
declare(strict_types=1);

/**
 * Loads the Acholi dictionary data (acholi-data/acholi_dictionary.json) into the Acholi community's dictionary table (ac_dictionary), cleaned.
 *
 *   php database/import-acholi-dictionary.php <path to acholi_dictionary.json> [--publish] [--replace]
 *
 * The data is an OCR scan of Blackings, "Acholi English - English Acholi Dictionary" (LINCOM, 2009), a book that is under copyright.
 * Without --publish every entry is stored as a DRAFT: nothing is shown in the app or on the website until it is published, which should only
 * happen once the publisher's (or the author's) permission is in hand. Every entry is stored as unchecked (verified = 0) and says where it came from.
 * --replace removes the entries of this source first.
 *
 * Cleaning (nothing is invented: a word is only ever one the scan contains):
 *  - the book's two halves are cross-checked. An Acholi word from the English-Acholi half (written there as it is pronounced, tones and long vowels
 *    marked) is stored in the spelling of the Acholi-English half (no tones, a long vowel written once, i/u before a vowel written y/w where that is
 *    how the other half spells it). Each entry gets a `grade`:
 *      3  both halves give the word with the same English meaning;
 *      2  one half gives it, and its spelling is a headword of the Acholi-English half;
 *      1  the spelling cannot be matched to the other half (it may be a scanning error), or the scan flags the entry itself;
 *  - each entry carries `gloss`, its short English meanings separated by pipes (|water|), which the translator and the comparison match exactly, so a
 *    long definition ("short form of the first person pronoun") never matches an English word that happens to appear in it;
 *  - left out as scanning debris: senses that are only a stray letter or carry the book's cross-reference numbers (and what follows such a number in
 *    the same sense: it belongs to the next entry), English inflections ("abandoning"), words with capitals or digits inside them, unmatched spellings
 *    with letters Acholi does not use (f, h, q, v, x, z), and phrases that contain the book's own labels ("the suffix -e", "noun");
 *  - a headword the scan repeats in part ("dwe dw") is read as the word itself ("dwe");
 *  - an example the scan could not read (it contains the unreadable-character mark) is dropped, and the book's soft hyphens are removed.
 */
if (PHP_SAPI !== 'cli') exit('CLI only');
require dirname(__DIR__) . '/app/bootstrap.php';
community('acholi');

$file = $argv[1] ?? '';
if (!is_file($file)) exit("Give the path to acholi_dictionary.json\n");
$publish = in_array('--publish', $argv, true);
$data = json_decode((string)file_get_contents($file), true);
if (!is_array($data['entries'] ?? null)) exit("That file has no entries.\n");

const ACHOLI_SOURCE = 'Blackings 2009 (scanned, unchecked)';
if (in_array('--replace', $argv, true)) q('DELETE FROM dictionary WHERE source=?', [ACHOLI_SOURCE]);

$posMap = ['noun' => 'n.', 'verb' => 'v.', 'adjective' => 'adj.', 'adverb' => 'adv.', 'pronoun' => 'pron.', 'preposition' => 'prep.', 'conjunction' => 'conj.', 'interjection' => 'interj.',
    'prefix' => 'prefix', 'suffix' => 'suffix', 'particle' => 'part.', 'numeral' => 'num.', 'ideophone' => 'ideoph.'];
$strip = function (string $s): string {   // the plain letters of an Acholi word: tone marks and dots removed
    if (class_exists('Normalizer')) $s = (string)Normalizer::normalize($s, Normalizer::FORM_D);
    return mb_strtolower((string)preg_replace('/\p{M}+/u', '', $s));
};
$clean = fn(string $s): string => trim((string)preg_replace('/\s+/u', ' ', str_replace("\u{00AD}", '', $s)));   // soft hyphens (the book's line breaks) are dropped
$validWord = fn(string $w): bool => (bool)preg_match("/^[a-z][a-z'’\- ]{0,38}$/", $w);
$collapse = fn(string $w): string => (string)preg_replace('/([aeiou])\1+/', '$1', $w);   // the Acholi-English half writes a long vowel once
// a headword as the Acholi-English half spells it; the scan sometimes repeats the start of the word after it ("dwe dw", "kwoko kw")
$headword = function (string $raw) use ($strip, $clean): string {
    $w = $strip($clean($raw));
    if (preg_match('/^([a-z]+) ([a-z]+)$/', $w, $m) && strlen($m[2]) < strlen($m[1]) && str_starts_with($m[1], $m[2])) return $m[1];
    return $w;
};
// a sense that is only a stray letter, or carries the book's cross-reference numbers, is scanning debris
$sound = fn(string $t): bool => mb_strlen($t) >= 3 && !preg_match('/\[\d+[a-z]?\]/i', $t) && !preg_match('/^[a-z]\s/i', $t);
// the short English meanings of a sense, as single words or short phrases ("(to) sew, tailor" gives sew|tailor)
$items = function (string $sense): array {
    $t = mb_strtolower(preg_replace('/\([^)]*\)/u', ' ', $sense));
    $out = [];
    foreach (preg_split('/[;,\/·]/u', $t) as $g) {
        $g = trim((string)preg_replace('/^(?:a|an|the|to)\s+/u', '', trim((string)preg_replace('/\s+/u', ' ', $g))));
        if ($g === '' || mb_strlen($g) > 36 || substr_count($g, ' ') > 3 || !preg_match("/^[a-z][a-z' \-]*$/", $g)) continue;
        if (preg_match('/ [a-z]$/', $g)) continue;   // ends in a stray letter: cut off by the scan ("on w")
        $out[] = $g;
    }
    return array_values(array_unique($out));
};
// an example is kept when both sides are there and neither is scanning debris (a bracketed pronunciation, a part-of-speech label, cross-reference numbers)
$goodExample = function (array $x) use ($clean): bool {
    $lg = $clean((string)($x['acholi'] ?? '')); $en = $clean((string)($x['english'] ?? ''));
    return $lg !== '' && $en !== '' && !str_contains($lg . $en, "\u{FFFD}") && !preg_match('/[\[\]\d]/', $lg . $en) && !preg_match('/^(?:noun|verb|adjective|adverb|pronoun)\b/i', $en);
};
$weak = fn(array $e): bool => in_array('headword_pronunciation_mismatch', $e['quality_flags'] ?? [], true);   // the scan's own doubt about the headword

// only entries with nothing to translate are dropped; the scan's other flags (a pronunciation that does not match, a missing part of speech) lower the grade instead
$entries = array_values(array_filter($data['entries'], fn($e) => !in_array('no_translation', $e['quality_flags'] ?? [], true)));
$flagged = count($data['entries']) - count($entries);

// pass 1: the Acholi-English half's headwords (the spellings the rest is matched to) and their English meanings
$heads = [];
foreach ($entries as $e) {
    if (($e['direction'] ?? '') !== 'acholi-english') continue;
    $w = $headword((string)($e['headword'] ?? ''));
    if (!$validWord($w)) continue;
    $heads[$w] ??= [];
    foreach ($e['senses'] ?? [] as $s) foreach ($items($clean((string)($s['translation'] ?? ''))) as $g) $heads[$w][$g] = true;
}
// an Acholi word of the English-Acholi half, in the spelling of the other half: [word, whether that spelling is a headword there]
$spell = function (string $phon) use ($heads, $collapse): array {
    $coll = implode(' ', array_map($collapse, explode(' ', $phon)));
    $y = (string)preg_replace('/i(?=[aeou])/', 'y', $coll);   // the English-Acholi half writes ario, the other half aryo
    $w = (string)preg_replace('/u(?=[aeio])/', 'w', $coll);
    foreach ([$coll, $y, $w, (string)preg_replace('/u(?=[aeio])/', 'w', $y), $phon] as $v) if (isset($heads[$v])) return [$v, true];
    return [$coll, false];
};

// pass 2: the English-Acholi half: [Acholi word => [English headwords]], graded by whether the Acholi-English half agrees
$ea = []; $skip = ['flagged' => $flagged, 'inflection' => 0, 'debris' => 0, 'headword' => 0, 'empty' => 0, 'duplicate' => 0, 'same_as_other_half' => 0];
foreach ($entries as $e) {
    if (($e['direction'] ?? '') !== 'english-acholi') continue;
    if (!empty($e['derived_from'])) { $skip['inflection']++; continue; }
    $english = mb_strtolower($clean((string)($e['headword'] ?? '')));
    if (!preg_match("/^[a-z][a-z' \-]{0,40}$/", $english)) { $skip['headword']++; continue; }
    $pos = null;
    foreach ($e['pos'] ?? [] as $p) if (isset($posMap[$p])) { $pos = $posMap[$p]; break; }
    $got = 0; $stop = false;
    foreach ($e['senses'] ?? [] as $s) {
        if ($stop) break;
        $raw = $clean((string)($s['translation'] ?? ''));
        $label = preg_match('/^\(([^)]*)\)\s*/u', $raw, $m) ? $m[1] : '';
        $raw = trim((string)preg_replace('/\([^)]*\)/u', ' ', $raw));
        foreach (preg_split('/[;,]/u', $raw) as $w) {
            $w = $clean($w);
            if ($w === '') continue;
            if (preg_match('/\d/', $w)) { $skip['debris']++; $stop = true; break; }   // a cross-reference number: what follows (in this and the next senses) belongs to the next entry
            if (preg_match('/\p{Lu}/u', $w)) { $skip['debris']++; continue; }   // a capital inside an Acholi word: scanning debris
            $phon = $strip($w);
            if (!$validWord($phon) || count(explode(' ', $phon)) > 3) { $skip['debris']++; continue; }
            [$word, $known] = $spell($phon);
            if (!$known && preg_match('/[fhqvxz]/', $word)) { $skip['debris']++; continue; }   // letters Acholi does not use, in a spelling the other half does not have
            if (str_contains($word, ' ') && preg_match('/\b(?:the|suffix|prefix|verb|noun|adjective|form|short|plural|singular|of)\b/', $word)) { $skip['debris']++; continue; }   // the book's own labels
            $grade = isset($heads[$word][$english]) ? 3 : ($known ? 2 : 1);   // 3 = the Acholi-English half gives this word with this English meaning too
            $key = $word . '|' . $english . '|' . $label;
            if (isset($ea[$key])) { $skip['duplicate']++; continue; }
            $ea[$key] = ['word' => $word, 'english' => $english, 'label' => $label, 'pos' => $pos, 'grade' => $grade];
            $got++;
        }
    }
    if (!$got) $skip['empty']++;
}
$pairs = [];   // Acholi word + English meaning given by the English-Acholi half
foreach ($ea as $r) $pairs[$r['word'] . '|' . $r['english']] = true;

$seen = []; $in = ['ae' => 0, 'ea' => 0, 'grade' => [1 => 0, 2 => 0, 3 => 0]];
$add = function (string $word, string $english, string $gloss, ?string $pos, ?string $exLg, ?string $exEn, int $grade) use (&$seen, $publish, $strip, &$in): void {
    $key = $word . '|' . mb_strtolower($english);
    if (isset($seen[$key])) return;
    $seen[$key] = true;
    insert('dictionary', ['lango' => $word, 'english' => mb_substr($english, 0, 500), 'gloss' => $gloss !== '' ? '|' . $gloss . '|' : null, 'grade' => $grade, 'pos' => $pos,
        'example_lg' => $exLg ? mb_substr($exLg, 0, 500) : null, 'example_en' => $exEn ? mb_substr($exEn, 0, 500) : null,
        'source' => ACHOLI_SOURCE, 'verified' => 0, 'status' => $publish ? 'published' : 'draft', 'fold' => lango_fold($strip($word))]);
    $in['grade'][$grade]++;
};

// the Acholi-English half
foreach ($entries as $e) {
    if (($e['direction'] ?? '') !== 'acholi-english') continue;
    $word = $headword((string)($e['headword'] ?? ''));
    if (!$validWord($word)) { $skip['headword']++; continue; }
    $pos = null;
    foreach ($e['pos'] ?? [] as $p) if (isset($posMap[$p])) { $pos = $posMap[$p]; break; }
    $senses = array_values(array_filter($e['senses'] ?? [], fn($s) => $sound($clean((string)($s['translation'] ?? '')))));
    if (!$senses) { $skip['empty']++; continue; }
    $parts = []; $gl = [];
    foreach ($senses as $s) {
        $t = $clean((string)$s['translation']);
        $parts[] = (string)preg_replace('/^\(to\)\s*/i', 'to ', mb_strlen($t) > 140 ? rtrim(mb_substr($t, 0, 137)) . '…' : $t);
        foreach ($items($t) as $g) $gl[] = $g;
    }
    $ex = null;
    foreach ($senses as $s) foreach ($s['examples'] ?? [] as $x) if ($goodExample($x)) { $ex = $x; break 2; }
    $gl = array_values(array_unique($gl));
    $agree = false;
    foreach ($gl as $g) if (isset($pairs[$word . '|' . $g])) { $agree = true; break; }
    // a long run-on sense, or a great many meanings, is how the scan looks when two entries were merged: such an entry is kept, but at the lowest grade
    $merged = count($gl) > 8;
    foreach ($senses as $s) if (mb_strlen($clean((string)$s['translation'])) > 70) $merged = true;
    $add($word, implode(' · ', array_slice($parts, 0, 4)), implode('|', array_slice($gl, 0, 12)), $pos, $ex ? $clean($ex['acholi']) : null, $ex ? $clean($ex['english']) : null, $agree ? 3 : ($weak($e) || $merged ? 1 : 2));
    $in['ae']++;
}
// the English-Acholi half: Acholi word, English meaning
foreach ($ea as $r) {
    if (isset($heads[$r['word']][$r['english']])) { $skip['same_as_other_half']++; continue; }   // the Acholi-English half's entry already says this: one entry, not two
    $english = $r['english'] . ($r['label'] !== '' ? ' (' . $r['label'] . ')' : '');
    $add($r['word'], $english, $r['english'], $r['pos'], null, null, $r['grade']);
    $in['ea']++;
}
echo 'Acholi dictionary: ' . ($in['ae'] + $in['ea']) . " entries stored ({$in['ae']} from the Acholi-English half, {$in['ea']} from the English-Acholi half), " . ($publish ? 'PUBLISHED' : 'as drafts (not shown anywhere)') . ".\n";
echo "Grades: 3 (both halves agree) {$in['grade'][3]}, 2 (one half, spelling matched) {$in['grade'][2]}, 1 (unmatched or flagged) {$in['grade'][1]}.\n";
echo 'Left out: ' . json_encode($skip) . "\n";
