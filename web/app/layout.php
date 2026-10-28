<?php
declare(strict_types=1);

/* Inline icon set (stroke icons, 24x24) */
function icon(string $n, string $cls = ''): string
{
    static $p = [
        'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'camera' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
        'book' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'phone-app' => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'chat' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
        'play' => '<path d="M5 3l14 9-14 9z"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'leaf' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>',
        'mic' => '<path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v4M8 23h8"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/>',
        'music' => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
        'video' => '<path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/>',
        'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'arrow' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
        'check' => '<path d="M20 6L9 17l-5-5"/>',
        'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'x' => '<path d="M18 6L6 18M6 6l12 12"/>',
        'chevron' => '<path d="M6 9l6 6 6-6"/>',
        'award' => '<circle cx="12" cy="8" r="7"/><path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'x-social' => '<path d="M4 4l16 16M20 4L4 20"/>',
        'youtube' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><path d="M9.75 15.02l5.75-3.27-5.75-3.27z"/>',
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37zM17.5 6.5h.01"/>',
        'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'chart' => '<path d="M12 20V10M18 20V4M6 20v-4"/>',
        'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/>',
        'trash' => '<path d="M3 6h18M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/>',
        'db' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
        'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/>',
        'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>',
        'dollar' => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'star' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>',
        'copy' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'history' => '<path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/>',
        'code' => '<path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>',
        'cube' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/>',
    ];
    return '<svg class="icon ' . $cls . '" viewBox="0 0 24 24" aria-hidden="true">' . ($p[$n] ?? '') . '</svg>';
}

function nav_items(): array
{
    return [
        [__('About'), 'about', [[__('About LCI'), 'about'], [__('Won Nyaci & Leadership'), 'leadership'], [__('Our History'), 'history'], [__('Districts & Lira City'), 'districts'], [__('Notable Langos'), 'people']]],
        [__('Heritage'), 'heritage', [[__('Clan Explorer'), 'clans'], [__('Ceremonies & Traditions'), 'heritage/ceremonies'], [__('Attire & Crafts'), 'heritage/attire'], [__('Music, Dance & Stories'), 'heritage/music'], [__('Symbols & Sites'), 'heritage/symbols'], [__('Biodiversity'), 'species'], [__('360° Heritage Tours'), 'tours']]],
        [__('Language'), 'language', [[__('Dictionary'), 'language/dictionary'], [__('AI Translator'), 'language/translate'], [__('Proverbs'), 'language/proverbs'], [__('Learn Leb Lango'), 'language/lessons'], [__('Suggest a Word'), 'contribute?type=language']]],
        [__('Library'), 'library', [[__('Data Center'), 'library'], [__('Documents'), 'library/documents'], [__('Media Center'), 'media'], [__('Professionals'), 'professionals'], [__('AI Cultural Assistant'), 'assistant'], [__('Open Data & API'), 'developers']]],
        [__('News & Events'), 'news', [[__('News & Blog'), 'news'], [__('Events'), 'events']]],
        [__('Programs'), 'programs', [[__('Our Programs'), 'programs'], [__('Scholarships & Grants'), 'apply/scholarship'], [__('Diaspora Partnership'), 'diaspora'], [__('Transparency'), 'transparency']]],
        [__('Contribute'), 'contribute', []],
        [__('Contact'), 'contact', []],
    ];
}

function page_start(string $title, array $o = []): void
{
    global $seg;
    $site = setting('site_name', 'Lango Cultural Institution');
    $full = $title === '' ? $site . ' — ' . setting('site_tagline') : $title . ' — ' . $site;
    $desc = $o['desc'] ?? 'The official digital home of the Lango Cultural Institution (Won Nyaci me Lango): clans, language, heritage, news, events and a community archive for the Lango people at home and abroad.';
    $img = media_url($o['image'] ?? null, 'assets/img/photos/hero-road.jpg');
    $cur = $seg[0] ?? '';
    $here = strtok($_SERVER['REQUEST_URI'], '?');
    $isLg = lang() === 'lg';
    $logo = logo_url();
    ?><!doctype html>
<html lang="<?= $isLg ? 'luo' : 'en' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($full) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($full) ?>"><meta property="og:description" content="<?= e($desc) ?>"><meta property="og:image" content="<?= e($img) ?>"><meta property="og:type" content="website">
<meta name="theme-color" content="#16181d">
<script>document.documentElement.className="js"</script>
<link rel="icon" href="<?= e($logo ?: asset('assets/img/favicon.svg')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
<?php if (!empty($o['leaflet'])): ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"><?php endif; ?>
</head>
<body class="<?= e($o['class'] ?? '') ?>">
<a class="skip" href="#main"><?php _e('Skip to content'); ?></a>
<div class="site">
<div class="flagbar" aria-hidden="true"></div>
<div class="topbar"><div class="container">
  <div class="topbar__l">
    <?php if (setting('phone')): ?><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><?= icon('phone') ?> <?= e(setting('phone')) ?></a><?php endif; ?>
    <?php if (setting('email')): ?><a href="mailto:<?= e(setting('email')) ?>"><?= icon('mail') ?> <?= e(setting('email')) ?></a><?php endif; ?>
  </div>
  <div class="topbar__r">
    <?php foreach (['facebook' => 'facebook', 'x' => 'x-social', 'youtube' => 'youtube', 'instagram' => 'instagram'] as $k => $ic): if (setting($k)): ?>
      <a class="topbar__soc" href="<?= e(setting($k)) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($k)) ?>"><?= icon($ic) ?></a>
    <?php endif; endforeach; ?>
    <span class="langsw" role="group" aria-label="Language"><a href="<?= e($here . '?lang=en') ?>" class="<?= !$isLg ? 'on' : '' ?>" hreflang="en">English</a><i>/</i><a href="<?= e($here . '?lang=lg') ?>" class="<?= $isLg ? 'on' : '' ?>" hreflang="luo">Leb Lango</a></span>
    <?php if (user()): ?><a href="<?= e(url('account')) ?>"><?= icon('user') ?> <?= e(explode(' ', user()['name'])[0]) ?></a>
    <?php else: ?><a href="<?= e(url('account/login')) ?>"><?php _e('Sign in'); ?></a><?php endif; ?>
  </div>
</div></div>
<header class="header" id="top"><div class="container header__in">
  <?= brand_html() ?>
  <ul class="nav" id="nav">
    <?php foreach (nav_items() as [$label, $path, $kids]): $on = $cur === explode('/', $path)[0] || ($path === 'about' && in_array($cur, ['leadership', 'history', 'districts', 'people'], true)) || ($path === 'heritage' && in_array($cur, ['clans', 'species', 'tours'], true)) || ($path === 'library' && in_array($cur, ['media', 'professionals', 'assistant', 'developers'], true)) || ($path === 'news' && $cur === 'events') || ($path === 'programs' && in_array($cur, ['apply', 'diaspora', 'transparency', 'donate'], true)); ?>
      <li class="<?= $kids ? 'has-sub' : '' ?>"><a href="<?= e(url($path)) ?>" class="<?= $on ? 'on' : '' ?>"<?= $kids ? ' aria-haspopup="true" aria-expanded="false"' : '' ?>><span><?= e($label) ?></span><?= $kids ? icon('chevron') : '' ?></a>
        <?php if ($kids): ?><ul><?php foreach ($kids as [$l, $p]): ?><li><a href="<?= e(url($p)) ?>"><?= e($l) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
      </li>
    <?php endforeach; ?>
    <li class="nav__foot">
      <span class="langsw" role="group" aria-label="Language"><a href="<?= e($here . '?lang=en') ?>" class="<?= !$isLg ? 'on' : '' ?>">English</a><i>/</i><a href="<?= e($here . '?lang=lg') ?>" class="<?= $isLg ? 'on' : '' ?>">Leb Lango</a></span>
      <?php if (user()): ?><a class="nav__acct" href="<?= e(url('account')) ?>"><?= icon('user') ?> <?= e(user()['name']) ?></a>
      <?php else: ?><a class="nav__acct" href="<?= e(url('account/login')) ?>"><?= icon('user') ?> <?php _e('Sign in'); ?></a><?php endif; ?>
      <a class="btn btn--primary btn--block" href="<?= e(url('donate')) ?>"><?= icon('heart') ?> <?php _e('Donate'); ?></a>
    </li>
  </ul>
  <button class="navclose" id="navclose" aria-label="Close menu"><?= icon('x') ?></button>
  <div class="header__cta">
    <a class="btn btn--primary btn-don" href="<?= e(url('donate')) ?>"><?= icon('heart') ?> <?php _e('Donate'); ?></a>
    <button class="burger" id="burger" aria-label="Open menu" aria-controls="nav" aria-expanded="false"><?= icon('menu') ?></button>
  </div>
</div></header>
<div class="scrim" id="scrim"></div>
<main id="main">
<?php
    if (!empty($o['hero'])) {
        $h = $o['hero'];
        $bg = media_url($h['image'] ?? null, 'assets/img/photos/hero-hill.jpg');
        echo '<section class="pagehero"><div class="container"><div class="pagehero__panel" style="background-image:url(\'' . e($bg) . '\')">';
        if (!empty($h['crumbs'])) {
            echo '<nav class="crumbs" aria-label="Breadcrumb"><a href="' . e(url()) . '">' . e(__('Home')) . '</a>';
            foreach ($h['crumbs'] as [$cl, $cu]) echo '<span>/</span>' . ($cu ? '<a href="' . e(url($cu)) . '">' . e($cl) . '</a>' : '<b>' . e($cl) . '</b>');
            echo '</nav>';
        }
        if (!empty($h['eyebrow'])) echo '<div class="eyebrow">' . e($h['eyebrow']) . '</div>';
        echo '<h1>' . e($h['title'] ?? $title) . '</h1>';
        if (!empty($h['sub'])) echo '<p>' . e($h['sub']) . '</p>';
        echo '</div></div></section>';
    }
    $fl = flash_html();
    if ($fl) echo '<div class="container" style="padding-top:24px">' . $fl . '</div>';
}

function page_end(array $o = []): void
{
    $site = setting('site_name', 'Lango Cultural Institution');
    ?>
</main>
<footer class="footer">
  <div class="flagbar" aria-hidden="true"></div>
  <div class="container footer__main">
    <div class="footer__about">
      <?= brand_html(true) ?>
      <p><?= e(setting('site_tagline')) ?> <?php _e('The official digital home of the Lango people, preserving heritage and inspiring the future.'); ?></p>
      <div class="footer__social">
        <?php foreach (['facebook' => 'facebook', 'x' => 'x-social', 'youtube' => 'youtube', 'instagram' => 'instagram'] as $k => $ic): if (setting($k)): ?>
          <a href="<?= e(setting($k)) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($k)) ?>"><?= icon($ic) ?></a>
        <?php endif; endforeach; ?>
        <?php if (setting('whatsapp')): ?><a href="<?= e(setting('whatsapp')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?= icon('chat') ?></a><?php endif; ?>
      </div>
    </div>
    <div>
      <h4><?php _e('Quick links'); ?></h4>
      <ul>
        <li><a href="<?= e(url('about')) ?>"><?php _e('About LCI'); ?></a></li>
        <li><a href="<?= e(url('clans')) ?>"><?php _e('Clan Explorer'); ?></a></li>
        <li><a href="<?= e(url('language/dictionary')) ?>"><?php _e('Dictionary'); ?></a></li>
        <li><a href="<?= e(url('library')) ?>"><?php _e('Data Center'); ?></a></li>
        <li><a href="<?= e(url('events')) ?>"><?php _e('Events'); ?></a></li>
        <li><a href="<?= e(url('contribute')) ?>"><?php _e('Submit a Contribution'); ?></a></li>
        <li><a href="<?= e(url('developers')) ?>"><?php _e('Open Data & API'); ?></a></li>
      </ul>
    </div>
    <div>
      <h4><?php _e('Our address'); ?></h4>
      <ul class="footer__ct">
        <?php if (setting('address')): ?><li><?= icon('pin') ?><span><?= e(setting('address')) ?></span></li><?php endif; ?>
        <?php if (setting('email')): ?><li><?= icon('mail') ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li><?php endif; ?>
        <?php if (setting('phone')): ?><li><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li><?php endif; ?>
      </ul>
    </div>
    <div>
      <h4><?php _e('Newsletter'); ?></h4>
      <p><?php _e('News, events and heritage stories in your inbox.'); ?></p>
      <form class="footer__news" action="<?= e(url('subscribe')) ?>" method="post">
        <?= csrf_field() ?><input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI']) ?>">
        <input type="email" name="email" placeholder="<?= e(__('Your email')) ?>" required aria-label="Email">
        <button class="btn btn--primary btn--sm"><?php _e('Join'); ?></button>
      </form>
    </div>
  </div>
  <div class="container footer__bottom">
    <span>© <?= date('Y') ?> <?= e($site) ?>. <?php _e('All rights reserved.'); ?></span>
    <span><a href="<?= e(url('privacy')) ?>"><?php _e('Privacy'); ?></a> · <a href="<?= e(url('terms')) ?>"><?php _e('Terms'); ?></a> · <a href="<?= e(url('contact')) ?>"><?php _e('Contact'); ?></a></span>
  </div>
</footer>
</div>
<a class="assistant-fab" href="<?= e(url('assistant')) ?>" aria-label="<?= e(__('Ask the Assistant')) ?>"><?= icon('chat') ?><span class="fab-t"> <?php _e('Ask the Assistant'); ?></span></a>
<button class="toplink" id="toplink" aria-label="Back to top"><?= icon('arrow') ?></button>
<script>window.LCI={base:<?= json_encode(BASE) ?>,csrf:<?= json_encode(csrf_token()) ?>,lang:<?= json_encode(lang()) ?>};</script>
<?php foreach (($o['scripts'] ?? []) as $s): ?><script src="<?= e($s) ?>"></script><?php endforeach; ?>
<script src="<?= e(asset('assets/js/site.js')) ?>"></script>
</body>
</html>
<?php
}

/** Reusable card for posts/events/etc. */
function card(string $href, ?string $img, string $title, string $text = '', string $tag = '', string $meta = '', string $cta = ''): string
{
    $h = '<article class="card reveal"><a class="card__img" href="' . e($href) . '" tabindex="-1" aria-hidden="true"><img loading="lazy" src="' . e(media_url($img)) . '" alt=""></a>';
    $h .= '<div class="card__body">';
    if ($tag) $h .= '<div><span class="tag">' . e($tag) . '</span></div>';
    $h .= '<h3><a href="' . e($href) . '">' . e($title) . '</a></h3>';
    if ($meta) $h .= '<div class="meta">' . $meta . '</div>';
    if ($text) $h .= '<p>' . e($text) . '</p>';
    $h .= '<a class="card__more" href="' . e($href) . '">' . e($cta ?: __('Read more')) . ' ' . icon('arrow') . '</a>';
    return $h . '</div></article>';
}

/** Card for a notable Lango person; a monogram stands in until a licensed portrait is uploaded. */
function person_card(array $n): string
{
    $href = url('people/' . $n['slug']);
    $nm = trim((string)preg_replace('/^(Maj\.?\s*Gen\.?|Gen\.?|Dr\.?|Rwot)\s+/i', '', (string)($n['short_name'] ?: $n['name'])));
    $words = array_values(array_filter(preg_split('/[^\p{L}]+/u', $nm)));
    $ini = mb_strtoupper(count($words) > 1 ? mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1) : mb_substr($words[0] ?? '?', 0, 2));
    $ph = $n['photo'] ? '<img loading="lazy" src="' . e(media_url($n['photo'])) . '" alt="' . e($n['name']) . '">' : '<span class="ncard__mono m' . ((int)$n['id'] % 4) . '" aria-hidden="true">' . e($ini) . '</span>';
    $years = $n['born'] ? $n['born'] . ($n['died'] ? ' – ' . $n['died'] : '') : '';
    return '<a class="ncard reveal" href="' . e($href) . '"><span class="ncard__ph">' . $ph . '</span><span class="ncard__body"><span class="ncard__cat">' . e(__(PEOPLE_CATS[$n['category']] ?? 'Notable')) . '</span><h3>' . e($n['short_name'] ?: $n['name']) . '</h3>'
        . '<span class="ncard__t">' . e((string)$n['title']) . '</span>' . ($years ? '<span class="ncard__y">' . e($years) . '</span>' : '') . '<p>' . e(excerpt((string)$n['summary'], 110)) . '</p></span></a>';
}

/** Compact programme card — four to a row on desktop. */
function program_card(array $p): string
{
    static $imgs = ['hero-aerial', 'hero-field', 'hero-ridge', 'hero-hill', 'hero-site', 'hero-road'];
    $href = url('programs/' . $p['slug']);
    $goal = (float)$p['goal_amount']; $raised = (float)$p['raised_amount'];
    $pct = $goal > 0 ? min(100, (int)round($raised / $goal * 100)) : (int)$p['progress'];
    $h = '<article class="pcard reveal"><a class="pcard__img" href="' . e($href) . '" tabindex="-1" aria-hidden="true"><img loading="lazy" src="' . e(media_url($p['image'], 'assets/img/photos/' . $imgs[$p['id'] % 6] . '.jpg')) . '" alt="">';
    $h .= '<span class="pcard__tag">' . e(__(PROGRAM_CATS[$p['category']] ?? 'Program')) . '</span>' . ($p['featured'] ? '<span class="pcard__star" title="' . e(__('Featured')) . '">' . icon('star') . '</span>' : '') . '</a>';
    $h .= '<div class="pcard__body"><h3><a href="' . e($href) . '">' . e(t($p, 'title')) . '</a></h3><p>' . e(excerpt((string)$p['summary'], 120)) . '</p>';
    $h .= '<div class="pcard__prog"><div class="progress"><i style="width:' . $pct . '%"></i></div><span>' . ($goal > 0 ? e(money($raised)) . ' ' . e(__('raised')) . ' · ' : '') . $pct . '%</span></div>';
    $h .= '<div class="pcard__foot"><a class="link" href="' . e($href) . '">' . e(__('Learn more')) . ' ' . icon('arrow') . '</a>';
    if ($p['accepts_donations']) $h .= '<a class="btn btn--primary btn--sm" href="' . e(url('donate', ['program' => $p['id']])) . '">' . e(__('Donate')) . '</a>';
    return $h . '</div></div></article>';
}

function empty_state(string $title, string $text, string $icon = 'leaf', string $cta = ''): string
{
    return '<div class="empty">' . icon($icon) . '<h3>' . e($title) . '</h3><p>' . e($text) . '</p>' . $cta . '</div>';
}

function not_found(): never
{
    http_response_code(404);
    page_start(__('Page not found'), ['hero' => ['title' => __('Page not found'), 'sub' => __('The page you are looking for has moved or does not exist.'), 'crumbs' => []]]);
    echo '<section class="section"><div class="container" style="text-align:center"><p>' . e(__('Try the search or head back to the home page.')) . '</p>';
    echo '<a class="btn btn--primary" href="' . e(url()) . '">' . e(__('Back home')) . '</a> <a class="btn btn--outline" href="' . e(url('search')) . '">' . e(__('Search the archive')) . '</a></div></section>';
    page_end();
    exit;
}
