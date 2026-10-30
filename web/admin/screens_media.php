<?php
declare(strict_types=1);

/* Admin screens for "Listen & read". The podcast and radio lists are ordinary modules (modules.php); this file is the Bible text import,
   which needs to understand book names. */

/** Import the text of the Lango Bible from a CSV (book, chapter, verse, text) and show how much of each book is in. */
function screen_bible_import(): void
{
    if (is_post()) {
        csrf_check();
        $f = $_FILES['csv'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) { flash('Please choose a CSV file.', 'error'); redirect(aurl('bible_import')); }
        @set_time_limit(300);

        // a book can be written as its number (1 to 66), its three-letter code, its English name or its Leb Lango name
        $norm = fn(string $s): string => (string)preg_replace('/[\s.\-]+/u', '', mb_strtolower(trim($s)));
        $map = ['psalm' => 19, 'songofsongs' => 22, 'revelations' => 66];
        foreach (all('SELECT id, code, name_en, name_lg FROM bible_books') as $b) {
            foreach ([(string)$b['id'], (string)$b['code'], (string)$b['name_en'], (string)$b['name_lg']] as $k) if ($k !== '') $map[$norm($k)] = (int)$b['id'];
        }

        $h = fopen($f['tmp_name'], 'r');
        $first = (string)fgets($h);
        rewind($h);
        if (fread($h, 3) !== "\xEF\xBB\xBF") rewind($h);
        $delim = substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',';

        $rows = []; $done = 0; $skipped = 0; $unknown = []; $line = 0;
        $flush = function () use (&$rows, &$done): void {
            if (!$rows) return;
            q('INSERT INTO bible_verses (book_id, chapter, verse, text) VALUES ' . implode(',', array_fill(0, count($rows), '(?,?,?,?)')) . ' ON DUPLICATE KEY UPDATE text=VALUES(text)', array_merge(...$rows));
            $done += count($rows);
            $rows = [];
        };
        while (($r = fgetcsv($h, 0, $delim)) !== false) {
            $line++;
            if (!array_filter($r, fn($x) => trim((string)$x) !== '')) continue;
            if (count($r) < 4) { $skipped++; continue; }
            [$book, $ch, $vs] = [trim((string)$r[0]), trim((string)$r[1]), trim((string)$r[2])];
            if ($line === 1 && !ctype_digit($ch)) continue;   // the header row
            $text = trim(implode($delim, array_slice($r, 3)));
            $id = $map[$norm($book)] ?? null;
            if (!$id) { $unknown[$book] = true; $skipped++; continue; }
            if (!ctype_digit($ch) || !ctype_digit($vs) || (int)$ch < 1 || (int)$ch > 300 || (int)$vs < 1 || (int)$vs > 300 || $text === '') { $skipped++; continue; }
            $rows[] = [$id, (int)$ch, (int)$vs, mb_substr($text, 0, 4000)];
            if (count($rows) >= 400) $flush();
        }
        $flush();
        fclose($h);

        audit('import', 'bible', null, "$done verses");
        $msg = number_format($done) . ' verses saved.';
        if ($skipped) $msg .= ' ' . number_format($skipped) . ' lines were skipped.';
        if ($unknown) $msg .= ' Book names not recognised: ' . implode(', ', array_slice(array_keys($unknown), 0, 6)) . (count($unknown) > 6 ? '…' : '') . '.';
        flash($msg, $done && !$skipped ? 'success' : ($done ? 'info' : 'error'));
        redirect(aurl('bible_import'));
    }

    $books = all('SELECT b.id, b.name_en, b.name_lg, b.testament, COALESCE(MAX(v.chapter), 0) chapters, COUNT(v.id) verses FROM bible_books b LEFT JOIN bible_verses v ON v.book_id=b.id GROUP BY b.id ORDER BY b.id');
    $total = array_sum(array_column($books, 'verses'));
    admin_start('Import Bible text', 'bible_import');
    ?>
    <div class="cols two">
      <div class="panel">
        <h3>Add the Lango Bible text</h3>
        <p>Upload a CSV file with a header row and four columns: <code>book, chapter, verse, text</code>. The book can be its number (1 to 66), its three-letter code (GEN, EXO, … REV), its English name or its Leb Lango name. A verse that is already in is replaced, so the same file can be uploaded again after a correction. A tab-separated file works too.</p>
        <p><a href="data:text/csv;charset=utf-8,<?= e(rawurlencode("book,chapter,verse,text\n")) ?>" download="bible-template.csv">Download an empty template</a></p>
        <form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="file" name="csv" accept=".csv,.tsv,.txt,text/csv,text/plain" required> <button class="btn btn--primary">Import</button></form>
        <p class="dim" style="margin-top:16px"><b>Rights.</b> Only import text LCI has permission to publish, for example from the Bible Society of Uganda or another holder of the Lango translation. Write the copyright line the app shows under every chapter in <a href="<?= e(aurl('settings')) ?>">Site settings</a>. The app shows no Bible text until a file is imported.</p>
      </div>
      <div class="panel">
        <h3>What is in so far</h3>
        <p><?= $total ? '<b>' . number_format($total) . '</b> verses in ' . count(array_filter($books, fn($b) => $b['verses'])) . ' of 66 books.' : 'Nothing imported yet.' ?> Names and fixes for single verses are under <a href="<?= e(aurl('bible_books')) ?>">Bible books</a> and <a href="<?= e(aurl('bible_verses')) ?>">Bible verses</a>.</p>
      </div>
    </div>
    <div class="panel flush"><div class="tablewrap"><table class="table"><thead><tr><th>#</th><th>Book</th><th>In Leb Lango</th><th>Chapters</th><th>Verses</th></tr></thead><tbody>
      <?php foreach ($books as $b): ?><tr><td><?= (int)$b['id'] ?></td><td><?= e($b['name_en']) ?></td><td><?= $b['name_lg'] ? e($b['name_lg']) : '<span class="dim">—</span>' ?></td><td><?= (int)$b['chapters'] ?: '<span class="dim">—</span>' ?></td><td><?= (int)$b['verses'] ?: '<span class="dim">—</span>' ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
    <?php admin_end();
}
