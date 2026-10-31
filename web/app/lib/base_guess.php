<?php
declare(strict_types=1);

/**
 * The folder the site is served from: '' at a domain's root, '/langoculturalinstitution' on XAMPP.
 * When the domain points at the whole repository, the root .htaccess maps every request into web/, so PHP sees a script path with an
 * extra "/web" that the visitor never typed; that part is dropped. Used before config.php exists (first visit, installer).
 */
function lci_guess_base(): string
{
    $dir = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
    $dir = (string)preg_replace('#/admin$#', '', $dir);
    $uri = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    if (preg_match('#^(.*)/web$#', $dir, $m) && !str_starts_with($uri, $dir . '/') && $uri !== $dir) return $m[1];
    return $dir;
}
