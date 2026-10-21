<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/layout.php';
require __DIR__ . '/app/lib/content.php';

$uri  = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$path = trim(substr($uri, strlen(BASE)), '/');

/* Old static-site URLs → new routes (keeps old links and search results working) */
if (preg_match('#^(.+)\.html$#', $path, $m)) {
    $legacy = ['index-2' => '', 'index-3' => '', 'index-4' => '', 'index' => '', 'about' => 'about', 'leadership' => 'leadership', 'history' => 'history',
        'clans' => 'clans', 'traditions' => 'heritage/ceremonies', 'districts' => 'districts', 'tourism' => 'districts', 'programs' => 'programs',
        'news' => 'news', 'blog' => 'news', 'contact' => 'contact', 'gallery' => 'media', 'events' => 'events', 'event' => 'events', 'heritage' => 'heritage',
        'language' => 'language', 'dances' => 'heritage/music', 'donate' => 'donate', 'education' => 'programs', 'development' => 'programs',
        'environment' => 'programs', 'team' => 'leadership', 'causes' => 'programs'];
    header('Location: ' . url($legacy[$m[1]] ?? ''), true, 301);
    exit;
}

$seg   = $path === '' ? [] : explode('/', $path);
$route = $seg[0] ?? 'home';

if ($route === 'robots.txt') { header('Content-Type: text/plain'); echo "User-agent: *\nDisallow: /admin/\nDisallow: /api/\nSitemap: " . (isset($_SERVER['HTTP_HOST']) ? ((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']) : '') . url('sitemap.xml') . "\n"; exit; }
if ($route === 'sitemap.xml') $route = 'sitemap';

$pages = ['home', 'about', 'leadership', 'history', 'districts', 'clans', 'heritage', 'species', 'language', 'news', 'events', 'programs', 'media',
    'library', 'search', 'contribute', 'donate', 'transparency', 'apply', 'diaspora', 'professionals', 'assistant', 'tours', 'developers',
    'contact', 'account', 'privacy', 'terms', 'subscribe', 'api', 'download', 'comment', 'sitemap'];

if (!in_array($route, $pages, true)) not_found();
require __DIR__ . '/app/pages/' . $route . '.php';
