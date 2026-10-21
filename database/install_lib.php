<?php
declare(strict_types=1);

/**
 * Creates the database, tables, starter content and the first super-admin.
 * Used by install.php (web) and database/install-cli.php (command line).
 * Cultural facts are limited to what LCI has already published on its own pages;
 * clan names, proverbs and Leb Lango translations are intentionally NOT invented.
 */
require_once __DIR__ . '/upgrade.php';

function lci_install(array $db, array $admin, string $base, bool $withSamples = true): void
{
    $pdo = new PDO(sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']), $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $name = preg_replace('/[^A-Za-z0-9_]/', '', $db['name']);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$name`");
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    foreach (array_filter(array_map('trim', explode(';', (string)file_get_contents(__DIR__ . '/schema.sql')))) as $stmt) {
        if (preg_match('/^(--[^\n]*\n?)*$/', $stmt)) continue;
        $pdo->exec($stmt);
    }

    $ins = function (string $table, array $row) use ($pdo): int {
        $cols = array_keys($row);
        $st = $pdo->prepare("INSERT INTO `$table` (`" . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
        $st->execute(array_values($row));
        return (int)$pdo->lastInsertId();
    };
    $count = fn(string $t) => (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    // ----- admin -----
    if (!$count('users')) {
        $ins('users', [
            'name' => $admin['name'], 'email' => strtolower($admin['email']),
            'password_hash' => password_hash($admin['password'], PASSWORD_DEFAULT),
            'role' => 'super_admin', 'must_change_password' => (int)($admin['must_change'] ?? 0),
        ]);
    }

    // ----- settings -----
    if (!$count('settings')) {
        $set = [
            'site_name'    => 'Lango Cultural Institution',
            'site_tagline' => 'Our Culture. Our People. Our Future.',
            'org_subtitle' => 'Won Nyaci me Lango',
            'email'        => 'info@langoculture.org',
            'phone'        => '+256 772 123 456',
            'address'      => 'Lira City, Northern Uganda',
            'clan_total'   => '149',
            'facebook'     => '', 'x' => '', 'youtube' => '', 'instagram' => '', 'whatsapp' => '',
            'map_lat'      => '2.2499', 'map_lng' => '32.8998',
            'donation_note'=> 'Every contribution helps preserve Lango culture for future generations.',
            'app_status'   => 'coming_soon',
            'mission'      => 'To preserve Lango cultural heritage, promote unity among our people, and support sustainable community development through education, culture and partnership.',
            'vision'       => 'A united Lango people whose language, clans and traditions thrive for generations, at home and across the world.',
            'logo' => '', 'hero_image_1' => '', 'hero_image_2' => '', 'about_image_1' => '', 'about_image_2' => '', 'cta_image' => '',
        ];
        foreach ($set as $k => $v) $ins('settings', ['k' => $k, 'v' => $v]);
    }

    // ----- districts (approximate centre points, for the map) -----
    if (!$count('districts')) {
        $d = [['Lira', 2.2499, 32.8998], ['Alebtong', 2.2500, 33.2500], ['Amolatar', 1.6333, 32.8167], ['Apac', 1.9758, 32.5386],
              ['Dokolo', 1.9167, 33.1667], ['Kole', 2.4000, 32.8000], ['Kwania', 1.7000, 32.7000], ['Oyam', 2.2333, 32.3833], ['Otuke', 2.5000, 33.4800]];
        foreach ($d as $i => [$n, $la, $ln]) $ins('districts', ['name' => $n, 'slug' => strtolower($n), 'lat' => $la, 'lng' => $ln, 'sort' => $i]);
    }

    // ----- blog categories -----
    if (!$count('post_categories')) {
        foreach (['Royal Messages', 'Culture & Heritage', 'Youth Voices', 'Diaspora Stories', 'Conservation', 'Development'] as $i => $n) {
            $ins('post_categories', ['name' => $n, 'slug' => strtolower(preg_replace('/[^a-z]+/i', '-', $n)), 'sort' => $i]);
        }
    }

    // ----- leadership (only the publicly stated Won Nyaci; cabinet slots start as drafts) -----
    if (!$count('leaders')) {
        $ins('leaders', ['grp' => 'won_nyaci', 'name' => 'Eng. Dr. Moses Michael Odongo Okune', 'title' => 'Won Nyaci me Lango (Cultural Leader)',
            'bio' => 'Supreme leader of the Lango Cultural Institution, guiding the Lango people with wisdom and vision for cultural preservation and development.', 'sort' => 0]);
        foreach ([['Prime Minister', 'Chief administrative officer overseeing daily operations, coordinating ministries and implementing the institution\'s programmes.'],
                  ['Minister of Culture', 'Preserving and promoting Lango traditions, cultural events, traditional dances and heritage conservation.'],
                  ['Minister of Education', 'Coordinating scholarship programmes, educational partnerships and youth development across Lango.']] as $i => [$t, $b]) {
            $ins('leaders', ['grp' => 'cabinet', 'name' => 'To be announced', 'title' => $t, 'bio' => $b, 'sort' => $i + 1, 'status' => 'draft']);
        }
    }

    // ----- programmes (names and progress figures from LCI's own site) -----
    if (!$count('programs') && $withSamples) {
        $p = [
            ['Digital Heritage Archive', 'digital', 'Collecting clan histories, elders\' testimonies and cultural records from the 149 clans into one secure, searchable digital archive.', 30, 1],
            ['Leb Lango Language & AI', 'digital', 'Building a Lango–English dictionary, pronunciation guides and translation tools with community and expert review.', 70, 1],
            ['Education & Youth Programmes', 'education', 'Scholarships, mentorship and leadership training that keep Lango young people connected to their culture while building modern skills.', 75, 1],
            ['Greening Lango', 'conservation', 'Tree planting, environmental conservation and climate awareness across the Lango sub-region, with a target of 1,000+ trees.', 35, 1],
            ['Cultural Preservation', 'heritage', 'Safeguarding traditional dances such as Okeme and Abuda, protecting sacred sites and documenting oral histories.', 50, 0],
            ['Community Development', 'development', 'Health, livelihood and infrastructure projects that strengthen unity and well-being in every Lango district.', 40, 0],
        ];
        foreach ($p as $i => [$t, $c, $s, $pr, $f]) {
            $ins('programs', ['title' => $t, 'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $t), '-')), 'category' => $c, 'summary' => $s,
                'body' => $s, 'progress' => $pr, 'featured' => $f, 'sort' => $i, 'status' => 'published', 'goal_amount' => 0]);
        }
    }

    // ----- heritage items (only what LCI already states publicly) -----
    if (!$count('heritage_items') && $withSamples) {
        $ins('heritage_items', ['category' => 'dance', 'title' => 'Okeme', 'slug' => 'okeme', 'summary' => 'A traditional Lango dance preserved and promoted by the Lango Cultural Institution.',
            'body' => "Okeme is one of the traditional dances of the Lango people, safeguarded by the Lango Cultural Institution as part of its cultural preservation programme.\n\nThis entry is a starting point. Clan elders, cultural experts and dancers can extend it with history, costume, instruments, songs and recordings through the admin panel or the Community Contribution Engine.",
            'featured' => 1, 'status' => 'published', 'image' => 'assets/img/photos/hero-site.jpg']);
        $ins('heritage_items', ['category' => 'dance', 'title' => 'Abuda', 'slug' => 'abuda', 'summary' => 'A traditional Lango dance preserved and promoted by the Lango Cultural Institution.',
            'body' => "Abuda is one of the traditional dances of the Lango people, safeguarded by the Lango Cultural Institution as part of its cultural preservation programme.\n\nThis entry is a starting point to be enriched with verified history, performance details and media.",
            'status' => 'published', 'image' => 'assets/img/photos/hero-field.jpg']);
    }

    // ----- sample news (platform guides + the festival report from LCI's site) -----
    if (!$count('posts') && $withSamples) {
        $cat = fn(string $slug) => (int)$pdo->query("SELECT id FROM post_categories WHERE slug=" . $pdo->quote($slug))->fetchColumn();
        $ins('posts', ['category_id' => $cat('culture-heritage'), 'title' => 'Lango Cultural Festival: Celebrating Heritage and Unity', 'slug' => 'lango-cultural-festival-celebrating-heritage-and-unity',
            'excerpt' => 'The Lango Cultural Institution hosted its annual Cultural Festival in Lira City, bringing together hundreds of community members from across the sub-region.',
            'body' => "The Lango Cultural Institution successfully hosted its annual Cultural Festival in Lira City, bringing together hundreds of community members from across the sub-region.\n\nThe event featured traditional dances, music performances, storytelling sessions, and exhibitions of local crafts, emphasising the importance of preserving Lango heritage for future generations.",
            'image' => 'assets/img/photos/hero-site.jpg', 'author_name' => 'Sedrick Otolo', 'featured' => 1, 'status' => 'published', 'published_at' => '2026-02-20 10:00:00']);
        $ins('posts', ['category_id' => $cat('development'), 'title' => 'Welcome to the new LCI website and the Lango App', 'slug' => 'welcome-to-the-new-lci-website',
            'excerpt' => 'A new digital home for Lango heritage: a living archive of clans, language, ceremonies and biodiversity, built with and for the Lango people at home and abroad.',
            'body' => "Today the Lango Cultural Institution opens a new digital home for Lango heritage.\n\nThe website brings together the clan register, the Lango dictionary, ceremonies and traditions, oral histories, news and events — and lets any Lango person contribute photographs, stories and observations to a moderated, expert-verified archive.\n\nA companion Lango App, with offline access and a contribution tool for low-data areas, will follow.",
            'image' => 'assets/img/photos/hero-road.jpg', 'author_name' => 'LCI Communications', 'status' => 'published', 'published_at' => date('Y-m-d H:i:s', strtotime('-3 days'))]);
        $ins('posts', ['category_id' => $cat('culture-heritage'), 'title' => 'How to contribute to the Lango heritage archive', 'slug' => 'how-to-contribute-to-the-heritage-archive',
            'excerpt' => 'Seen a rare bird, recorded an elder\'s story, or know a proverb that is not yet written down? Here is how to add it to the archive in a few minutes.',
            'body' => "Anyone can help build the Lango archive.\n\n1. Choose what you are contributing: a cultural observation, natural heritage, a word or proverb, an oral history, a historical document or a heritage site.\n2. Answer a few simple questions: what, where, when and which clan or community.\n3. Add a photo, recording or video and, if you like, the location.\n4. Submit, then track your contribution with the code we give you.\n\nEvery contribution is reviewed by clan leaders and subject experts before it is published, and you keep the rights to your work.",
            'image' => 'assets/img/photos/hero-aerial.jpg', 'author_name' => 'LCI Communications', 'status' => 'published', 'published_at' => date('Y-m-d H:i:s', strtotime('-1 day'))]);
    }

    // ----- sample upcoming events (names from LCI's site; dates are placeholders relative to install day) -----
    if (!$count('events') && $withSamples) {
        $ev = [
            ['Lango Cultural Festival & Heritage Showcase', '+3 weeks', 'Lira City', 'A day dedicated to showcasing traditional dances, clan histories, Leb Lango, cultural artefacts and stories from elders. All Lango and friends of Lango are encouraged to take part.', 1],
            ['Annual Clans Convention', '+6 weeks', 'Lira City', 'Clan leaders and representatives of the 149 clans meet to strengthen unity and plan together.', 0],
            ['Youth & Education Forum', '+9 weeks', 'Lira City', 'A forum on education, mentorship and digital skills for Lango young people.', 0],
            ['Greening Lango Campaign', '+11 weeks', 'Across Lango sub-region', 'A tree-planting campaign targeting greener homesteads across the sub-region.', 0],
        ];
        foreach ($ev as [$t, $off, $loc, $b, $f]) {
            $ins('events', ['title' => $t, 'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $t), '-')), 'summary' => $b, 'body' => $b, 'location' => $loc,
                'starts_at' => date('Y-m-d 10:00:00', strtotime($off)), 'featured' => $f, 'status' => 'published',
                'image' => 'assets/img/photos/' . ['hero-site', 'hero-aerial', 'hero-field', 'hero-ridge'][array_search($t, array_column($ev, 0))] . '.jpg']);
        }
    }

    // ----- assistant knowledge base (facts LCI already publishes) -----
    if (!$count('knowledge')) {
        $kb = [
            ['Leadership', 'Who is the Won Nyaci?', 'Won Nyaci Eng. Dr. Moses Michael Odongo Okune is the Cultural Leader (Won Nyaci) of the Lango Cultural Institution.'],
            ['Clans', 'How many clans are there?', 'The Lango Cultural Institution unites 149 registered clans.'],
            ['Clans', 'What is an Awitong?', 'Awitong is the title of a clan leader. Each of the 149 clans has an Awitong who represents the clan within the Lango Cultural Institution.'],
            ['Geography', 'Which districts make up Lango?', 'The Lango sub-region is made up of nine districts: Lira, Alebtong, Amolatar, Apac, Dokolo, Kole, Kwania, Oyam and Otuke.'],
            ['Culture', 'What are the traditional dances of the Lango?', 'Traditional dances preserved by the Lango Cultural Institution include Okeme and Abuda.'],
            ['History', 'Where do the Lango people come from?', 'The Lango are a Nilotic ethnic group from mid-northern Uganda. Oral traditions trace their migration from the Bahr el Ghazal region of present-day South Sudan between the 15th and 17th centuries.'],
            ['About', 'What is the Lango Cultural Institution?', 'The Lango Cultural Institution (LCI) is the traditional authority representing the Lango people of mid-northern Uganda. It preserves Lango heritage, promotes unity among the clans and supports education and community development.'],
            ['About', 'Where is the Lango Cultural Institution based?', 'The institution is based in Lira City, Northern Uganda.'],
        ];
        foreach ($kb as [$tp, $qn, $an]) $ins('knowledge', ['topic' => $tp, 'question' => $qn, 'answer' => $an, 'reviewed' => 1]);
    }

    // ----- dictionary (imported from the LCI language project; unverified until experts review) -----
    $dictFile = __DIR__ . '/seed/lango_dictionary.json';
    if (!$count('dictionary') && is_file($dictFile)) {
        $data = json_decode((string)file_get_contents($dictFile), true) ?: [];
        $pdo->beginTransaction();
        $st = $pdo->prepare('INSERT INTO dictionary (lango, english, pos, source, verified, status) VALUES (?,?,?,?,0,?)');
        foreach ($data as $w) {
            $lango = trim((string)($w['lango'] ?? ''));
            $eng = implode('; ', array_map('trim', $w['english_meanings'] ?? []));
            if ($lango === '' || $eng === '') continue;
            $st->execute([mb_substr($lango, 0, 190), mb_substr($eng, 0, 500), mb_substr(implode(', ', $w['parts_of_speech'] ?? []), 0, 40) ?: null, 'LCI language project import', 'published']);
        }
        $pdo->commit();
    }

    lci_upgrade($pdo);
}
