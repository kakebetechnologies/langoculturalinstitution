<?php
declare(strict_types=1);

/* Bilingual support: English (en) and Leb Lango (lg).
   UI strings are keyed by their English text. Every string seen is registered in the
   `translations` table so language experts can supply the Leb Lango text in the admin panel.
   Anything without a Leb Lango entry falls back to English. */

function lang(): string
{
    static $l = null;
    if ($l !== null) return $l;
    $want = $_GET['lang'] ?? null;
    if ($want === 'en' || $want === 'lg') {
        $_SESSION['lang'] = $want;
        if (!headers_sent()) setcookie('lci_lang', $want, ['expires' => time() + 31536000, 'path' => BASE === '' ? '/' : BASE . '/', 'samesite' => 'Lax']);
    }
    $l = $_SESSION['lang'] ?? ($_COOKIE['lci_lang'] ?? 'en');
    return $l = ($l === 'lg' ? 'lg' : 'en');
}

function translations(): array
{
    static $t = null;
    if ($t === null) {
        try { $t = array_column(all('SELECT h, lg FROM translations'), 'lg', 'h'); } catch (Throwable $e) { $t = []; }
    }
    return $t;
}

function __(string $s): string
{
    $tr = &i18n_cache();
    $h = md5($s);
    if (!array_key_exists($h, $tr)) {
        try { q('INSERT IGNORE INTO translations (h,k) VALUES (?,?)', [$h, $s]); } catch (Throwable $e) {}
        $tr[$h] = null;
    }
    if (lang() === 'lg' && !empty($tr[$h])) return (string)$tr[$h];
    return $s;
}

function &i18n_cache(): array
{
    static $c = null;
    if ($c === null) $c = translations();
    return $c;
}

function _e(string $s): void { echo e(__($s)); }

/** Bilingual field: returns $row[field_lg] when Leb Lango is active and filled, else $row[field]. */
function t(array $row, string $field): string
{
    if (lang() === 'lg' && !empty($row[$field . '_lg'])) return (string)$row[$field . '_lg'];
    return (string)($row[$field] ?? '');
}
