<?php
declare(strict_types=1);

/**
 * Writes the Acholi dictionary of this computer's database as a CSV that the website's admin can import (Admin, Dictionary, with the Editing switch on
 * Acholi, then "Import CSV"). It is how the cleaned Acholi entries (with their short meanings and their scan grade) reach another copy of the site, such as
 * the live one, without the data ever being part of the code or of git.
 *
 *   php database/export-acholi-import.php [output.csv]      (default: acholi-data/acholi_dictionary_import.csv, which git ignores)
 *
 * The entries are an OCR scan of Blackings, "Acholi English - English Acholi Dictionary" (LINCOM, 2009), a book that is under copyright: importing the file
 * on a public site publishes them, so that should only be done with the publisher's (or the author's) permission. Every entry stays unchecked (verified = 0).
 */
if (PHP_SAPI !== 'cli') exit('CLI only');
require dirname(__DIR__) . '/app/bootstrap.php';
community('acholi');

$out = $argv[1] ?? dirname(__DIR__, 2) . '/acholi-data/acholi_dictionary_import.csv';
$rows = all("SELECT lango, english, pos, example_lg, example_en, gloss, grade, source FROM dictionary WHERE source LIKE 'Blackings%' ORDER BY fold, lango, id");
$h = fopen($out, 'w');
fwrite($h, "\xEF\xBB\xBF");
fputcsv($h, ['lango', 'english', 'pos', 'example_lg', 'example_en', 'gloss', 'grade', 'source']);
foreach ($rows as $r) fputcsv($h, [$r['lango'], $r['english'], $r['pos'], $r['example_lg'], $r['example_en'], $r['gloss'], $r['grade'], $r['source']]);
fclose($h);
echo count($rows) . " entries written to $out (" . round(filesize($out) / 1024) . " KB).\n";
