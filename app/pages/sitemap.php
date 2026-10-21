<?php
declare(strict_types=1);
header('Content-Type: application/xml; charset=utf-8');
$host = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$urls = ['', 'about', 'leadership', 'history', 'districts', 'clans', 'heritage', 'species', 'language', 'language/dictionary', 'language/translate', 'language/proverbs', 'people', 'news', 'events', 'programs', 'media', 'library', 'library/documents', 'contribute', 'donate', 'transparency', 'diaspora', 'professionals', 'tours', 'developers', 'contact'];
foreach (all("SELECT slug FROM posts WHERE status='published' AND published_at<=NOW()") as $r) $urls[] = 'news/' . $r['slug'];
foreach (all("SELECT slug FROM events WHERE status='published'") as $r) $urls[] = 'events/' . $r['slug'];
foreach (all("SELECT slug FROM programs WHERE status='published'") as $r) $urls[] = 'programs/' . $r['slug'];
foreach (all("SELECT slug FROM clans WHERE status='published'") as $r) $urls[] = 'clans/' . $r['slug'];
foreach (all("SELECT slug FROM heritage_items WHERE status='published'") as $r) $urls[] = 'heritage/item/' . $r['slug'];
foreach (all("SELECT slug FROM species WHERE status='published'") as $r) $urls[] = 'species/' . $r['slug'];
foreach (all("SELECT slug FROM districts WHERE status='published'") as $r) $urls[] = 'districts/' . $r['slug'];
foreach (all("SELECT slug FROM notables WHERE status='published'") as $r) $urls[] = 'people/' . $r['slug'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) echo '<url><loc>' . e($host . url($u)) . '</loc></url>' . "\n";
echo '</urlset>';
