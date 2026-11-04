<?php
declare(strict_types=1);

/**
 * Loads the Acholi dictionary data (acholi-data/acholi_dictionary.json) into the Acholi community's dictionary table (ac_dictionary).
 *
 *   php database/import-acholi-dictionary.php <path to acholi_dictionary.json> [--publish] [--replace]
 *
 * The data is an OCR scan of Blackings, "Acholi English - English Acholi Dictionary" (LINCOM, 2009), a book that is under copyright.
 * Without --publish every entry is stored as a DRAFT: nothing is shown in the app or on the website until it is published, which should only
 * happen once the publisher's (or the author's) permission is in hand. Every entry is stored as unchecked (verified = 0) and says where it came from.
 * Entries the OCR flagged as doubtful, and headwords with stray characters, are left out. --replace removes the entries of this source first.
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
$clean = fn(string $s): string => trim((string)preg_replace('/\s+/u', ' ', $s));
$validWord = fn(string $w): bool => (bool)preg_match("/^[a-z][a-z'’\- ]{0,38}$/", $w);

$seen = []; $in = ['ae' => 0, 'ea' => 0]; $skip = ['flagged' => 0, 'headword' => 0, 'empty' => 0, 'duplicate' => 0];
$add = function (string $word, string $english, ?string $pos, ?string $exLg, ?string $exEn) use (&$seen, $publish, $strip): bool {
    $key = $word . '|' . mb_strtolower($english);
    if (isset($seen[$key])) return false;
    $seen[$key] = true;
    insert('dictionary', ['lango' => $word, 'english' => mb_substr($english, 0, 500), 'pos' => $pos, 'example_lg' => $exLg ? mb_substr($exLg, 0, 500) : null, 'example_en' => $exEn ? mb_substr($exEn, 0, 500) : null,
        'source' => ACHOLI_SOURCE, 'verified' => 0, 'status' => $publish ? 'published' : 'draft', 'fold' => lango_fold($strip($word))]);
    return true;
};

foreach ($data['entries'] as $e) {
    if (!empty($e['quality_flags'])) { $skip['flagged']++; continue; }
    $pos = null;
    foreach ($e['pos'] ?? [] as $p) if (isset($posMap[$p])) { $pos = $posMap[$p]; break; }
    // a sense that is only a stray letter, or carries the book's cross-reference numbers, is scanning debris
    $sound = fn(string $t): bool => mb_strlen($t) >= 3 && !preg_match('/\[\d+[a-z]?\]/i', $t) && !preg_match('/^[a-z]\s/i', $t);
    $senses = array_values(array_filter($e['senses'] ?? [], fn($s) => $sound($clean((string)($s['translation'] ?? '')))));
    if (!$senses) { $skip['empty']++; continue; }

    if (($e['direction'] ?? '') === 'acholi-english') {
        $word = $strip($clean((string)($e['headword'] ?? '')));
        if (!$validWord($word)) { $skip['headword']++; continue; }
        $parts = [];
        foreach ($senses as $s) { $t = $clean((string)$s['translation']); if ($t !== '') $parts[] = mb_strlen($t) > 140 ? rtrim(mb_substr($t, 0, 137)) . '…' : $t; }
        $english = implode(' · ', array_slice($parts, 0, 4));
        $ex = null;
        foreach ($senses as $s) foreach ($s['examples'] ?? [] as $x) if (trim((string)($x['acholi'] ?? '')) !== '' && trim((string)($x['english'] ?? '')) !== '') { $ex = $x; break 2; }
        if ($add($word, $english, $pos, $ex ? $clean($ex['acholi']) : null, $ex ? $clean($ex['english']) : null)) $in['ae']++; else $skip['duplicate']++;
    } else {   // English headword, Acholi words as its translation: stored as Acholi word, English meaning
        $english = $clean((string)($e['headword'] ?? ''));
        if (!preg_match("/^[A-Za-z][A-Za-z' \-]{0,40}$/", $english)) { $skip['headword']++; continue; }
        foreach ($senses as $s) {
            foreach (preg_split('/[;,]/u', (string)$s['translation']) as $w) {
                $w = $strip($clean($w));
                if (!$validWord($w)) { $skip['headword']++; continue; }
                if ($add($w, $english, $pos, null, null)) $in['ea']++; else $skip['duplicate']++;
            }
        }
    }
}
echo 'Acholi dictionary: ' . ($in['ae'] + $in['ea']) . " entries stored ({$in['ae']} from the Acholi-English part, {$in['ea']} from the English-Acholi part), " . ($publish ? 'PUBLISHED' : 'as drafts (not shown anywhere)') . ".\n";
echo 'Left out: ' . json_encode($skip) . "\n";
