<?php
declare(strict_types=1);
$d = one("SELECT * FROM documents WHERE id=? AND status='published'", [(int)($seg[1] ?? 0)]);
if ($d && preg_match('#^https://#i', (string)$d['file'])) {   // a reading-list entry: it lives on the publisher's or archive's own page
    q('UPDATE documents SET downloads=downloads+1 WHERE id=?', [$d['id']]);
    header('Location: ' . $d['file'], true, 302);
    exit;
}
if (!$d || !$d['file'] || !is_file(ROOT . '/' . $d['file'])) not_found();
q('UPDATE documents SET downloads=downloads+1 WHERE id=?', [$d['id']]);
$f = ROOT . '/' . $d['file'];
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($f) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($f));
header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]+/', '-', $d['title']) . '.' . pathinfo($f, PATHINFO_EXTENSION) . '"');
readfile($f);
exit;
