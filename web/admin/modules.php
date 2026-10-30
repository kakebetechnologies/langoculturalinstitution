<?php
declare(strict_types=1);

/* Module registry for the generic CRUD engine (see admin/lib.php).
   Add a new managed content type here — no other code needed. */

$PUB   = ['published' => 'Published', 'draft' => 'Draft'];
$cats  = array_map(fn($c) => $c[0], HERITAGE_CATS);
$bool  = ['type' => 'bool'];
$cont  = ADM_CONTENT;
$mod   = ADM_MOD;

$clanSel = ['type' => 'select', 'options' => 'clans:id:name', 'label' => 'Clan'];
$distSel = ['type' => 'select', 'options' => 'districts:id:name', 'label' => 'District'];
$now = fn() => date('Y-m-d H:i:s');

return [
    /* ------------------------------ MODERATION ------------------------------ */
    'comments' => [
        'label' => 'Comments', 'icon' => 'chat', 'group' => 'Moderation', 'table' => 'comments', 'roles' => $mod, 'title' => 'name', 'order' => 'created_at DESC',
        'create' => false, 'list' => ['name', 'body', 'post_id', 'status', 'created_at'], 'search' => ['name', 'body'], 'filters' => ['status'],
        'actions' => ['approve' => ['Approve', 'status', 'approved'], 'reject' => ['Reject', 'status', 'rejected']],
        'fields' => [
            'post_id' => ['type' => 'select', 'options' => 'posts:id:title', 'label' => 'Article', 'readonly' => true],
            'name' => ['readonly' => true], 'email' => ['readonly' => true], 'body' => ['type' => 'textarea', 'label' => 'Comment'],
            'status' => ['type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'], 'required' => true],
            'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Received'],
        ],
    ],
    'messages' => [
        'label' => 'Messages', 'icon' => 'inbox', 'group' => 'Moderation', 'table' => 'messages', 'roles' => $mod, 'title' => 'subject', 'order' => 'created_at DESC',
        'create' => false, 'list' => ['name', 'subject', 'email', 'status', 'created_at'], 'search' => ['name', 'email', 'subject', 'body'], 'filters' => ['status'],
        'actions' => ['read' => ['Mark read', 'status', 'read'], 'replied' => ['Replied', 'status', 'replied']],
        'fields' => [
            'name' => ['readonly' => true], 'email' => ['readonly' => true], 'phone' => ['readonly' => true], 'subject' => ['readonly' => true],
            'body' => ['type' => 'textarea', 'readonly' => true, 'label' => 'Message'],
            'status' => ['type' => 'select', 'options' => ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'], 'required' => true],
            'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Received'],
        ],
    ],
    'applications' => [
        'label' => 'Applications', 'icon' => 'file', 'group' => 'Moderation', 'table' => 'applications', 'roles' => $mod, 'title' => 'name', 'order' => 'created_at DESC',
        'create' => false, 'list' => ['name', 'type', 'organization', 'country', 'status', 'created_at'], 'search' => ['name', 'email', 'organization', 'details'], 'filters' => ['type', 'status'],
        'actions' => ['accept' => ['Accept', 'status', 'accepted'], 'decline' => ['Decline', 'status', 'declined']],
        'fields' => [
            'type' => ['type' => 'select', 'options' => ['scholarship' => 'Scholarship', 'grant' => 'Grant', 'volunteer' => 'Volunteer', 'diaspora' => 'Diaspora', 'partner' => 'Partner', 'clan' => 'Clan registration'], 'readonly' => true],
            'name' => ['readonly' => true], 'email' => ['readonly' => true], 'phone' => ['readonly' => true], 'organization' => ['readonly' => true], 'country' => ['readonly' => true],
            'clan_id' => $clanSel + ['readonly' => true], 'clan_other' => ['readonly' => true, 'label' => 'Clan (typed, not in register)'],
            'details' => ['type' => 'textarea', 'readonly' => true],
            'status' => ['type' => 'select', 'options' => ['new' => 'New', 'reviewing' => 'Reviewing', 'accepted' => 'Accepted', 'declined' => 'Declined'], 'required' => true],
            'notes' => ['type' => 'textarea', 'label' => 'Internal notes'],
            'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Received'],
        ],
    ],
    'professionals' => [
        'label' => 'Professionals', 'icon' => 'users', 'group' => 'Moderation', 'table' => 'professionals', 'roles' => $mod, 'title' => 'name', 'order' => 'created_at DESC',
        'list' => ['name', 'industry', 'clan_id', 'status', 'created_at'], 'search' => ['name', 'industry', 'expertise'], 'filters' => ['status'],
        'actions' => ['publish' => ['Verify', 'status', 'published'], 'reject' => ['Reject', 'status', 'rejected']],
        'fields' => [
            'name' => ['required' => true], 'industry' => ['required' => true], 'expertise' => [], 'clan_id' => $clanSel, 'clan_other' => ['label' => 'Clan (typed, not in register)'], 'location' => [], 'email' => ['type' => 'email'], 'phone' => [],
            'bio' => ['type' => 'textarea'], 'photo' => ['type' => 'image'],
            'status' => ['type' => 'select', 'options' => ['pending' => 'Pending', 'published' => 'Verified & published', 'rejected' => 'Rejected'], 'required' => true],
            'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Submitted'],
        ],
    ],

    /* ------------------------------- CONTENT ------------------------------- */
    'posts' => [
        'label' => 'News & Blog', 'icon' => 'file', 'group' => 'Content', 'table' => 'posts', 'roles' => $cont, 'title' => 'title', 'order' => 'COALESCE(published_at, created_at) DESC',
        'list' => ['title', 'category_id', 'status', 'published_at', 'views'], 'search' => ['title', 'excerpt'], 'filters' => ['status', 'category_id'],
        'view_url' => fn($r) => url('news/' . $r['slug']),
        'defaults' => fn() => ['author_name' => user()['name'], 'published_at' => date('Y-m-d H:i:s'), 'status' => 'draft'],
        'before' => function (array &$d, bool $new) { if (($d['status'] ?? '') === 'published' && empty($d['published_at'])) $d['published_at'] = date('Y-m-d H:i:s'); },
        'fields' => [
            'title' => ['required' => true, 'full' => true], 'title_lg' => ['label' => 'Title (Leb Lango)', 'full' => true], 'slug' => ['type' => 'slug', 'from' => 'title'],
            'category_id' => ['type' => 'select', 'options' => 'post_categories:id:name', 'label' => 'Category'],
            'excerpt' => ['type' => 'textarea', 'rows' => 3, 'help' => 'Shown on cards and in search results.'], 'excerpt_lg' => ['type' => 'textarea', 'rows' => 3, 'label' => 'Excerpt (Leb Lango)'],
            'body' => ['type' => 'rich', 'label' => 'Article (English)'], 'body_lg' => ['type' => 'rich', 'label' => 'Article (Leb Lango)'],
            'image' => ['type' => 'image', 'label' => 'Cover image'], 'video_url' => ['type' => 'url', 'label' => 'Video link (YouTube / Vimeo)'], 'audio' => ['type' => 'file', 'kind' => 'audio', 'label' => 'Audio clip'],
            'author_name' => ['label' => 'Author'], 'published_at' => ['type' => 'datetime', 'label' => 'Publish date'], 'featured' => $bool + ['label' => 'Featured (pinned first)'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
            'views' => ['type' => 'number', 'readonly' => true, 'label' => 'Views'],
        ],
    ],
    'post_categories' => [
        'label' => 'Blog categories', 'icon' => 'list', 'group' => 'Content', 'table' => 'post_categories', 'roles' => $cont, 'title' => 'name', 'order' => 'sort, name',
        'list' => ['name', 'slug', 'sort'], 'search' => ['name'],
        'fields' => ['name' => ['required' => true], 'slug' => ['type' => 'slug', 'from' => 'name'], 'sort' => ['type' => 'number', 'default' => 0]],
    ],
    'events' => [
        'label' => 'Events', 'icon' => 'calendar', 'group' => 'Content', 'table' => 'events', 'roles' => $cont, 'title' => 'title', 'order' => 'starts_at DESC',
        'list' => ['title', 'starts_at', 'location', 'status'], 'search' => ['title', 'location'], 'filters' => ['status'], 'view_url' => fn($r) => url('events/' . $r['slug']),
        'fields' => [
            'title' => ['required' => true, 'full' => true], 'title_lg' => ['label' => 'Title (Leb Lango)', 'full' => true], 'slug' => ['type' => 'slug', 'from' => 'title'],
            'summary' => ['type' => 'textarea', 'rows' => 3], 'body' => ['type' => 'rich', 'label' => 'Details (English)'], 'body_lg' => ['type' => 'rich', 'label' => 'Details (Leb Lango)'],
            'image' => ['type' => 'image'], 'location' => [], 'starts_at' => ['type' => 'datetime', 'required' => true], 'ends_at' => ['type' => 'datetime'],
            'capacity' => ['type' => 'number', 'help' => 'Leave empty for unlimited'], 'registration_open' => $bool + ['default' => 1, 'label' => 'Online registration open'], 'featured' => $bool,
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'event_registrations' => [
        'label' => 'Event registrations', 'icon' => 'users', 'group' => 'Community', 'table' => 'event_registrations', 'roles' => $cont, 'title' => 'name', 'order' => 'created_at DESC',
        'create' => false, 'list' => ['name', 'email', 'event_id', 'tickets', 'code', 'created_at'], 'search' => ['name', 'email', 'code'], 'filters' => ['event_id'],
        'fields' => [
            'event_id' => ['type' => 'select', 'options' => 'events:id:title', 'label' => 'Event', 'readonly' => true], 'name' => ['readonly' => true], 'email' => ['readonly' => true], 'phone' => ['readonly' => true],
            'tickets' => ['readonly' => true], 'clan_id' => $clanSel + ['readonly' => true], 'clan_other' => ['readonly' => true, 'label' => 'Clan (typed, not in register)'],
            'code' => ['readonly' => true], 'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Registered'],
        ],
    ],
    'programs' => [
        'label' => 'Programs', 'icon' => 'leaf', 'group' => 'Content', 'table' => 'programs', 'roles' => $cont, 'title' => 'title', 'order' => 'sort, id',
        'list' => ['title', 'category', 'progress', 'featured', 'status'], 'search' => ['title'], 'filters' => ['category', 'status'], 'view_url' => fn($r) => url('programs/' . $r['slug']),
        'fields' => [
            'title' => ['required' => true, 'full' => true], 'title_lg' => ['label' => 'Title (Leb Lango)', 'full' => true], 'slug' => ['type' => 'slug', 'from' => 'title'],
            'category' => ['type' => 'select', 'options' => PROGRAM_CATS, 'required' => true], 'summary' => ['type' => 'textarea', 'rows' => 3],
            'body' => ['type' => 'rich', 'label' => 'Description (English)'], 'body_lg' => ['type' => 'rich', 'label' => 'Description (Leb Lango)'], 'image' => ['type' => 'image'],
            'goal_amount' => ['type' => 'number', 'step' => '0.01', 'label' => 'Funding goal (UGX)', 'default' => 0], 'raised_amount' => ['type' => 'number', 'step' => '0.01', 'label' => 'Raised outside the site (UGX)', 'default' => 0],
            'progress' => ['type' => 'number', 'min' => 0, 'max' => 100, 'label' => 'Progress %', 'default' => 0], 'accepts_donations' => $bool + ['default' => 1], 'featured' => $bool,
            'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'media' => [
        'label' => 'Media library', 'icon' => 'image', 'group' => 'Content', 'table' => 'media', 'roles' => $cont, 'title' => 'title', 'order' => 'id DESC',
        'list' => ['title', 'type', 'album', 'source', 'status'], 'search' => ['title', 'album'], 'filters' => ['type', 'status', 'source'],
        'fields' => [
            'type' => ['type' => 'select', 'options' => ['photo' => 'Photo', 'video' => 'Video', 'audio' => 'Audio', 'podcast' => 'Podcast episode'], 'required' => true],
            'title' => ['required' => true], 'description' => ['type' => 'textarea'],
            'file' => ['type' => 'file', 'kind_from' => 'type', 'kind_map' => ['photo' => 'image', 'video' => 'video', 'audio' => 'audio', 'podcast' => 'audio'], 'label' => 'File', 'help' => 'Photo, MP4/WebM video, or MP3/WAV/M4A audio.'],
            'url' => ['type' => 'url', 'label' => 'Or video link (YouTube / Vimeo)'], 'album' => [], 'credit' => [],
            'source' => ['type' => 'select', 'options' => ['lci' => 'LCI', 'community' => 'Community'], 'required' => true, 'default' => 'lci'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'documents' => [
        'label' => 'Official documents', 'icon' => 'file', 'group' => 'Content', 'table' => 'documents', 'roles' => $cont, 'title' => 'title', 'order' => 'year DESC, id DESC',
        'list' => ['title', 'category', 'year', 'downloads', 'status'], 'search' => ['title', 'description'], 'filters' => ['category', 'status'],
        'fields' => [
            'title' => ['required' => true, 'full' => true],
            'category' => ['type' => 'select', 'options' => ['policy' => 'Policy', 'governance' => 'Governance record', 'historical' => 'Historical', 'research' => 'Research paper', 'report' => 'Report', 'other' => 'Other'], 'required' => true],
            'year' => ['type' => 'number', 'min' => 1800, 'max' => 2100], 'description' => ['type' => 'textarea'], 'file' => ['type' => 'file', 'kind' => 'document', 'label' => 'File (PDF, DOCX…)'],
            'downloads' => ['type' => 'number', 'readonly' => true, 'label' => 'Downloads'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'partners' => [
        'label' => 'Partners', 'icon' => 'globe', 'group' => 'Community', 'table' => 'partners', 'roles' => $cont, 'title' => 'name', 'order' => 'sort, id',
        'list' => ['name', 'category', 'sort', 'status'], 'search' => ['name'],
        'fields' => ['name' => ['required' => true], 'logo' => ['type' => 'image'], 'url' => ['type' => 'url'], 'category' => [], 'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published']],
    ],

    /* ------------------------------- HERITAGE ------------------------------- */
    'clans' => [
        'label' => 'Clans', 'icon' => 'users', 'group' => 'Heritage', 'table' => 'clans', 'roles' => array_merge($cont, ['clan_leader']), 'title' => 'name', 'order' => 'name',
        'create_roles' => $cont, 'clan_scope' => 'id=?', 'list' => ['name', 'totem', 'district_id', 'leader_name', 'verified', 'status'], 'search' => ['name', 'totem', 'leader_name'], 'filters' => ['district_id', 'status'],
        'links' => [['Add clans in bulk', 'clan_quick', 'plus']],
        'view_url' => fn($r) => url('clans/' . $r['slug']),
        'import' => ['columns' => ['name', 'totem', 'totem_type', 'district', 'summary', 'leader_name', 'leader_title'], 'required' => 'name', 'status' => 'draft'],
        'before' => function (array &$d) { if (user()['role'] === 'clan_leader') { unset($d['status'], $d['verified'], $d['slug']); } },
        'fields' => [
            'name' => ['required' => true], 'slug' => ['type' => 'slug', 'from' => 'name'], 'totem' => [], 'totem_type' => ['type' => 'select', 'options' => ['animal' => 'Animal', 'plant' => 'Plant', 'bird' => 'Bird', 'other' => 'Other']],
            'district_id' => $distSel, 'summary' => ['type' => 'textarea', 'rows' => 2], 'history' => ['type' => 'rich', 'label' => 'History (English)'], 'history_lg' => ['type' => 'rich', 'label' => 'History (Leb Lango)'],
            'lineage' => ['type' => 'rich'], 'notable_members' => ['type' => 'textarea'], 'leader_name' => ['label' => 'Clan leader'], 'leader_title' => ['label' => 'Leader title', 'default' => 'Awitong'],
            'leader_photo' => ['type' => 'image', 'label' => 'Clan leader photo', 'help' => 'Shown on this clan page and used when someone asks the AI assistant who leads the clan.'],
            'image' => ['type' => 'image', 'label' => 'Clan image'], 'source_note' => ['label' => 'Where this name comes from', 'full' => true],
            'verified' => $bool + ['label' => 'Verified by clan leaders / LCI'], 'status' => ['type' => 'select', 'options' => $PUB, 'default' => 'draft'],
        ],
    ],
    'heritage_items' => [
        'label' => 'Heritage items', 'icon' => 'star', 'group' => 'Heritage', 'table' => 'heritage_items', 'roles' => $cont, 'title' => 'title', 'order' => 'id DESC',
        'list' => ['title', 'category', 'verified', 'featured', 'status'], 'search' => ['title', 'summary'], 'filters' => ['category', 'status'], 'view_url' => fn($r) => url('heritage/item/' . $r['slug']),
        'fields' => [
            'category' => ['type' => 'select', 'options' => $cats, 'required' => true], 'title' => ['required' => true], 'title_lg' => ['label' => 'Title (Leb Lango)'], 'slug' => ['type' => 'slug', 'from' => 'title'],
            'summary' => ['type' => 'textarea', 'rows' => 2], 'body' => ['type' => 'rich', 'label' => 'Story (English)'], 'body_lg' => ['type' => 'rich', 'label' => 'Story (Leb Lango)'],
            'image' => ['type' => 'image'], 'video_url' => ['type' => 'url', 'label' => 'Video link'], 'audio' => ['type' => 'file', 'kind' => 'audio'],
            'clan_id' => $clanSel, 'district_id' => $distSel, 'lat' => ['type' => 'number', 'step' => 'any', 'label' => 'Latitude'], 'lng' => ['type' => 'number', 'step' => 'any', 'label' => 'Longitude'],
            'featured' => $bool, 'verified' => $bool + ['label' => 'Verified by experts'], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'species' => [
        'label' => 'Biodiversity', 'icon' => 'leaf', 'group' => 'Heritage', 'table' => 'species', 'roles' => $cont, 'title' => 'name', 'order' => 'name',
        'list' => ['name', 'kind', 'conservation', 'medicinal', 'status'], 'search' => ['name', 'name_lg', 'scientific_name'], 'filters' => ['kind', 'conservation', 'status'], 'view_url' => fn($r) => url('species/' . $r['slug']),
        'fields' => [
            'name' => ['required' => true], 'name_lg' => ['label' => 'Name (Leb Lango)'], 'slug' => ['type' => 'slug', 'from' => 'name'], 'scientific_name' => [],
            'kind' => ['type' => 'select', 'options' => ['plant' => 'Plant', 'animal' => 'Animal', 'bird' => 'Bird', 'insect' => 'Insect', 'other' => 'Other'], 'required' => true],
            'conservation' => ['type' => 'select', 'options' => CONSERVATION, 'required' => true, 'default' => 'unknown'], 'medicinal' => $bool + ['label' => 'Traditional medicinal plant'],
            'uses' => ['type' => 'textarea', 'label' => 'Traditional uses'], 'habitat' => ['type' => 'textarea'], 'body' => ['type' => 'rich', 'label' => 'Description'], 'image' => ['type' => 'image'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'districts' => [
        'label' => 'Districts', 'icon' => 'pin', 'group' => 'Heritage', 'table' => 'districts', 'roles' => $cont, 'title' => 'name', 'order' => 'sort, name',
        'list' => ['name', 'kind', 'headquarters', 'population', 'status'], 'search' => ['name'], 'filters' => ['kind', 'status'], 'view_url' => fn($r) => url('districts/' . $r['slug']),
        'fields' => [
            'name' => ['required' => true], 'slug' => ['type' => 'slug', 'from' => 'name'], 'kind' => ['type' => 'select', 'options' => ['district' => 'District', 'city' => 'City'], 'required' => true, 'default' => 'district'],
            'headquarters' => [], 'image' => ['type' => 'image', 'label' => 'Photo (optional)'],
            'population' => ['type' => 'number', 'min' => 0], 'pop_year' => ['type' => 'number', 'label' => 'Census year', 'min' => 1900, 'max' => 2100],
            'area_km2' => ['type' => 'number', 'step' => '0.1', 'label' => 'Area (km²)'], 'created_year' => ['type' => 'number', 'label' => 'Year created'],
            'parent' => ['label' => 'Carved out of'], 'neighbours' => ['label' => 'Neighbouring districts', 'full' => true], 'distance' => ['label' => 'Distances', 'full' => true],
            'known_for' => ['type' => 'textarea', 'rows' => 6, 'label' => 'Best known for', 'help' => 'One item per line. Shown as the “Best known for” list.'],
            'subdivisions' => ['type' => 'textarea', 'rows' => 3, 'label' => 'Counties / divisions', 'help' => 'Comma-separated.'],
            'description' => ['type' => 'rich', 'label' => 'Overview'], 'description_lg' => ['type' => 'textarea', 'label' => 'Overview (Leb Lango)'],
            'demography' => ['type' => 'rich', 'label' => 'People & demography'], 'economy' => ['type' => 'rich', 'label' => 'Economy & livelihoods'],
            'attractions' => ['type' => 'rich', 'label' => 'Places to visit'], 'history' => ['type' => 'rich', 'label' => 'History of the district'],
            'lat' => ['type' => 'number', 'step' => 'any', 'label' => 'Latitude'], 'lng' => ['type' => 'number', 'step' => 'any', 'label' => 'Longitude'],
            'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published'],
        ],
    ],
    'notables' => [
        'label' => 'Notable Langos', 'icon' => 'award', 'group' => 'Heritage', 'table' => 'notables', 'roles' => $cont, 'title' => 'name', 'order' => 'featured DESC, sort, name',
        'list' => ['name', 'category', 'born', 'featured', 'photo', 'status'], 'search' => ['name', 'title', 'summary'], 'filters' => ['category', 'status'], 'view_url' => fn($r) => url('people/' . $r['slug']),
        'fields' => [
            'name' => ['required' => true, 'full' => true], 'short_name' => ['label' => 'Short name (for cards)'], 'slug' => ['type' => 'slug', 'from' => 'name'],
            'category' => ['type' => 'select', 'options' => PEOPLE_CATS, 'required' => true], 'title' => ['label' => 'Title / what they are known as'],
            'born' => ['help' => 'Year, e.g. 1925'], 'died' => ['help' => 'Leave empty if living'], 'place' => ['label' => 'Home place'], 'district_id' => $distSel,
            'summary' => ['type' => 'textarea', 'rows' => 3, 'help' => 'One or two sentences for the card.'], 'body' => ['type' => 'rich', 'label' => 'Their story'],
            'photo' => ['type' => 'image', 'label' => 'Portrait', 'help' => 'Use a photograph you own or that is properly licensed (for example public domain or Creative Commons), and keep the credit with the story.'],
            'featured' => $bool + ['label' => 'Feature at the top of the page'], 'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published'],
        ],
    ],
    'history_sections' => [
        'label' => 'History chapters', 'icon' => 'history', 'group' => 'Heritage', 'table' => 'history_sections', 'roles' => $cont, 'title' => 'title', 'order' => 'sort, id',
        'list' => ['title', 'era', 'sort', 'status'], 'search' => ['title', 'summary'], 'filters' => ['status'], 'view_url' => fn($r) => url('history') . '#' . $r['slug'],
        'fields' => [
            'title' => ['required' => true, 'full' => true], 'slug' => ['type' => 'slug', 'from' => 'title'], 'era' => ['help' => 'Shown as a label, e.g. 1962–1971'],
            'icon' => ['type' => 'select', 'options' => ['users' => 'People', 'globe' => 'Globe', 'star' => 'Star', 'flag' => 'Flag', 'book' => 'Book', 'award' => 'Award', 'clock' => 'Clock', 'shield' => 'Shield', 'pin' => 'Pin', 'heart' => 'Heart', 'history' => 'History']],
            'summary' => ['type' => 'textarea', 'rows' => 2, 'help' => 'Shown on the chapter header and in the contents.'], 'body' => ['type' => 'rich', 'label' => 'Chapter (English)'], 'body_lg' => ['type' => 'rich', 'label' => 'Chapter (Leb Lango)'],
            'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published'],
        ],
    ],
    'glossary' => [
        'label' => 'History glossary', 'icon' => 'book', 'group' => 'Heritage', 'table' => 'glossary', 'roles' => $cont, 'title' => 'term', 'order' => 'sort, term',
        'list' => ['term', 'meaning', 'sort', 'status'], 'search' => ['term', 'meaning'],
        'fields' => ['term' => ['required' => true], 'meaning' => ['required' => true, 'full' => true], 'note' => ['full' => true, 'help' => 'Why this word matters in Lango history.'], 'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published']],
    ],
    'leaders' => [
        'label' => 'Leadership', 'icon' => 'award', 'group' => 'Heritage', 'table' => 'leaders', 'roles' => $cont, 'title' => 'name', 'order' => "FIELD(grp,'won_nyaci','elders','cabinet','awitong'), sort, id",
        'list' => ['name', 'title', 'grp', 'status'], 'search' => ['name', 'title'], 'filters' => ['grp', 'status'],
        'fields' => [
            'grp' => ['type' => 'select', 'label' => 'Group', 'options' => ['won_nyaci' => "Won Nyaci's Office", 'elders' => 'Council of Elders', 'cabinet' => 'Cabinet & departments', 'awitong' => 'Clan leaders (Awitong)'], 'required' => true],
            'name' => ['required' => true], 'title' => [], 'bio' => ['type' => 'textarea', 'rows' => 5], 'photo' => ['type' => 'image'], 'clan_id' => $clanSel,
            'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'timeline' => [
        'label' => 'History timeline', 'icon' => 'history', 'group' => 'Heritage', 'table' => 'timeline', 'roles' => $cont, 'title' => 'title', 'order' => 'sort, year',
        'list' => ['year', 'title', 'sort'], 'search' => ['title', 'year'],
        'fields' => ['year' => ['required' => true, 'help' => 'e.g. 1500s or 1962'], 'title' => ['required' => true], 'body' => ['type' => 'textarea'], 'sort' => ['type' => 'number', 'default' => 0]],
    ],
    'tours' => [
        'label' => '360° tours', 'icon' => 'cube', 'group' => 'Heritage', 'table' => 'tours', 'roles' => $cont, 'title' => 'title', 'order' => 'sort, id',
        'list' => ['title', 'location', 'status'], 'search' => ['title'],
        'fields' => [
            'title' => ['required' => true], 'location' => [], 'description' => ['type' => 'textarea'],
            'panorama' => ['type' => 'image', 'label' => '360° photo (equirectangular, 2:1)', 'help' => 'Upload a panoramic photo from a 360° camera or phone panorama.'],
            'sort' => ['type' => 'number', 'default' => 0], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],

    /* ------------------------------- LANGUAGE ------------------------------- */
    'dictionary' => [
        'label' => 'Dictionary', 'icon' => 'book', 'group' => 'Language', 'table' => 'dictionary', 'roles' => $cont, 'title' => 'lango', 'order' => 'lango',
        'list' => ['lango', 'english', 'pos', 'category', 'verified', 'status'], 'search' => ['lango', 'english'], 'filters' => ['category', 'verified', 'status'],
        'import' => ['columns' => ['lango', 'english', 'pos', 'example_lg', 'example_en'], 'required' => 'lango', 'status' => 'published'],
        'before' => function (array &$d) { if (isset($d['lango'])) $d['fold'] = mb_substr(lango_fold($d['lango']), 0, 200); },
        'fields' => [
            'lango' => ['label' => 'Leb Lango', 'required' => true], 'english' => ['label' => 'English meaning', 'required' => true], 'pos' => ['label' => 'Part of speech'],
            'category' => ['type' => 'select', 'options' => WORD_CATS], 'contributor' => ['label' => 'Credit (contributor)', 'help' => 'Shown beside community-suggested words.'],
            'example_lg' => ['label' => 'Example (Leb Lango)'], 'example_en' => ['label' => 'Example (English)'], 'audio' => ['type' => 'file', 'kind' => 'audio', 'label' => 'Pronunciation audio'],
            'source' => [], 'verified' => $bool + ['label' => 'Verified by a language expert'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published'],
        ],
    ],
    'proverbs' => [
        'label' => 'Proverbs', 'icon' => 'chat', 'group' => 'Language', 'table' => 'proverbs', 'roles' => $cont, 'title' => 'lango', 'order' => 'id DESC',
        'list' => ['lango', 'english', 'verified', 'status'], 'search' => ['lango', 'english', 'meaning'], 'filters' => ['status'],
        'import' => ['columns' => ['lango', 'english', 'meaning'], 'required' => 'lango', 'status' => 'draft'],
        'fields' => [
            'lango' => ['type' => 'textarea', 'label' => 'Proverb (Leb Lango)', 'required' => true, 'rows' => 3], 'english' => ['type' => 'textarea', 'label' => 'English translation', 'rows' => 3], 'meaning' => ['type' => 'textarea', 'label' => 'Meaning / when it is used'],
            'clan_id' => $clanSel, 'verified' => $bool + ['label' => 'Verified'], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'lessons' => [
        'label' => 'Lessons', 'icon' => 'star', 'group' => 'Language', 'table' => 'lessons', 'roles' => $cont, 'title' => 'title', 'order' => "FIELD(level,'beginner','intermediate','advanced'), sort, id",
        'list' => ['title', 'level', 'sort', 'status'], 'search' => ['title'], 'filters' => ['level', 'status'],
        'fields' => [
            'level' => ['type' => 'select', 'options' => ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'], 'required' => true], 'title' => ['required' => true],
            'body' => ['type' => 'rich', 'label' => 'Lesson'], 'audio' => ['type' => 'file', 'kind' => 'audio'], 'sort' => ['type' => 'number', 'default' => 0],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'knowledge' => [
        'label' => 'Assistant knowledge', 'icon' => 'chat', 'group' => 'Language', 'table' => 'knowledge', 'roles' => $cont, 'title' => 'question', 'order' => 'topic, id',
        'list' => ['question', 'topic', 'reviewed', 'status'], 'search' => ['question', 'answer'], 'filters' => ['status'],
        'fields' => [
            'topic' => [], 'question' => ['required' => true, 'full' => true, 'help' => 'The assistant matches visitor questions against this.'], 'answer' => ['type' => 'textarea', 'required' => true, 'rows' => 6],
            'reviewed' => $bool + ['label' => 'Reviewed by a cultural expert'], 'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published'],
        ],
    ],
    'chat_log' => [
        'label' => 'Assistant questions', 'icon' => 'flag', 'group' => 'Language', 'table' => 'chat_log', 'roles' => $cont, 'title' => 'question', 'order' => 'flagged DESC, id DESC',
        'create' => false, 'list' => ['question', 'answer', 'flagged', 'created_at'], 'search' => ['question', 'answer'], 'filters' => ['flagged'],
        'fields' => ['question' => ['readonly' => true], 'answer' => ['type' => 'textarea', 'readonly' => true], 'flagged' => $bool + ['label' => 'Flagged as incorrect by a visitor'], 'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Asked']],
    ],

    /* ------------------------------- COMMUNITY ------------------------------- */
    'subscribers' => [
        'label' => 'Newsletter subscribers', 'icon' => 'mail', 'group' => 'Community', 'table' => 'subscribers', 'roles' => $mod, 'title' => 'email', 'order' => 'created_at DESC',
        'list' => ['email', 'created_at'], 'search' => ['email'],
        'fields' => ['email' => ['type' => 'email', 'required' => true], 'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Joined']],
    ],
    'donations' => [
        'label' => 'Donations', 'icon' => 'dollar', 'group' => 'Community', 'table' => 'donations', 'roles' => ADM_ALL, 'title' => 'reference', 'order' => 'created_at DESC',
        'list' => ['reference', 'donor_name', 'amount', 'currency', 'method', 'purpose', 'status', 'created_at'], 'search' => ['reference', 'donor_name', 'email', 'phone'], 'filters' => ['status', 'method', 'purpose', 'program_id'],
        'actions' => ['confirm' => ['Confirm', 'status', 'confirmed'], 'fail' => ['Failed', 'status', 'failed']],
        'before' => function (array &$d, bool $new) { if ($new) { $d['reference'] = 'LCI' . new_code(7); } },
        'fields' => [
            'reference' => ['readonly' => true, 'label' => 'Reference'], 'created_at' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Received'],
            'donor_name' => ['required' => true], 'email' => ['type' => 'email'], 'phone' => [], 'country' => [],
            'amount' => ['type' => 'number', 'step' => '0.01', 'required' => true], 'currency' => ['type' => 'select', 'options' => ['UGX' => 'UGX', 'USD' => 'USD'], 'required' => true, 'default' => 'UGX'],
            'method' => ['type' => 'select', 'options' => ['mtn_momo' => 'MTN Mobile Money', 'airtel_money' => 'Airtel Money', 'visa' => 'Visa', 'mastercard' => 'Mastercard', 'paypal' => 'PayPal', 'bank' => 'Bank transfer', 'cash' => 'Cash'], 'required' => true],
            'purpose' => ['type' => 'select', 'options' => ['general' => 'Where it is needed most', 'app' => 'Keeping the Lango App running'], 'required' => true, 'default' => 'general', 'label' => 'Given for'],
            'program_id' => ['type' => 'select', 'options' => 'programs:id:title', 'label' => 'Program'], 'recurring' => ['type' => 'select', 'options' => ['once' => 'Once', 'monthly' => 'Monthly', 'yearly' => 'Yearly'], 'required' => true, 'default' => 'once'],
            'anonymous' => $bool, 'message' => ['type' => 'textarea', 'rows' => 2],
            'status' => ['type' => 'select', 'options' => ['pledged' => 'Pledged (awaiting payment)', 'confirmed' => 'Confirmed (received)', 'failed' => 'Failed'], 'required' => true, 'default' => 'pledged'],
        ],
    ],

    /* ------------------------------ LISTEN & READ ------------------------------ */
    // What the app's Listen and Bible screens show. Nothing is pre-filled: LCI adds the recordings and the Bible text it has the right to publish.
    'podcast_episodes' => [
        'label' => 'Podcast episodes', 'icon' => 'mic', 'group' => 'Listen & read', 'table' => 'podcast_episodes', 'roles' => $cont, 'title' => 'title', 'order' => 'COALESCE(published_at, created_at) DESC, id DESC',
        'list' => ['title', 'storyteller', 'series', 'recorded_year', 'plays', 'status'], 'search' => ['title', 'storyteller', 'summary'], 'filters' => ['status'],
        'actions' => ['publish' => ['Publish', 'status', 'published'], 'unpublish' => ['Move to draft', 'status', 'draft']],
        'before' => function (array &$d, bool $new, ?array $old) {
            if (($d['status'] ?? '') === 'published' && empty($d['published_at']) && empty($old['published_at'])) $d['published_at'] = date('Y-m-d H:i:s');
        },
        'fields' => [
            'title' => ['required' => true, 'full' => true],
            'storyteller' => ['label' => 'Storyteller (the elder telling the story)'], 'series' => ['label' => 'Series', 'help' => 'Episodes with the same series name are grouped in the app, for example "Stories of the old days".'],
            'storyteller_note' => ['label' => 'About the storyteller', 'full' => true, 'help' => 'For example: elder from Ogur, born 1941. Write only what the storyteller agreed to share.'],
            'clan_id' => $clanSel, 'district_id' => $distSel, 'recorded_year' => ['type' => 'number', 'min' => 1900, 'max' => 2100, 'label' => 'Year recorded'],
            'summary' => ['type' => 'textarea', 'rows' => 3, 'label' => 'What the episode is about'],
            'audio_file' => ['type' => 'file', 'kind' => 'audio', 'label' => 'Audio file', 'help' => 'MP3, M4A, WAV or OGG, up to 40 MB. An MP3 at 64 to 96 kbps keeps a 30-minute story small.'],
            'audio_url' => ['type' => 'url', 'label' => 'Or a link to the audio (https)'], 'minutes' => ['type' => 'number', 'min' => 0, 'label' => 'Length in minutes (optional)'],
            'cover_image' => ['type' => 'image', 'label' => 'Picture (optional)'],
            'transcript' => ['type' => 'textarea', 'rows' => 10, 'label' => 'Written version of the story (optional)'],
            'featured' => $bool, 'published_at' => ['type' => 'datetime', 'label' => 'Publish on (leave empty to publish now)'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'radio_channels' => [
        'label' => 'Radio channels', 'icon' => 'music', 'group' => 'Listen & read', 'table' => 'radio_channels', 'roles' => $cont, 'title' => 'name', 'order' => 'sort, id',
        'list' => ['name', 'source', 'sort', 'status'], 'search' => ['name'], 'filters' => ['status'],
        'fields' => [
            'name' => ['required' => true], 'description' => ['type' => 'textarea', 'rows' => 2, 'help' => 'One or two lines shown under the name in the app.'],
            'source' => ['type' => 'select', 'options' => ['playlist' => 'A playlist I build (add tracks under "Radio tracks")', 'podcast' => 'Every published podcast episode', 'stream' => 'A live stream from another station'], 'required' => true, 'default' => 'playlist'],
            'stream_url' => ['type' => 'url', 'label' => 'Live stream address (live streams only)', 'help' => 'The https address of the station\'s MP3 or AAC stream. A channel with no tracks or no address stays hidden in the app.'],
            'cover_image' => ['type' => 'image'], 'sort' => ['type' => 'number', 'default' => 0, 'help' => 'Smaller numbers come first.'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'draft'],
        ],
    ],
    'radio_items' => [
        'label' => 'Radio tracks', 'icon' => 'play', 'group' => 'Listen & read', 'table' => 'radio_items', 'roles' => $cont, 'title' => 'title', 'order' => 'channel_id, sort, id',
        'list' => ['title', 'channel_id', 'credit', 'sort', 'status'], 'search' => ['title', 'credit'], 'filters' => ['channel_id', 'status'],
        'fields' => [
            'channel_id' => ['type' => 'select', 'options' => 'radio_channels:id:name', 'label' => 'Channel', 'required' => true],
            'title' => ['required' => true, 'full' => true], 'credit' => ['label' => 'Who it is by or who performs it'],
            'audio_file' => ['type' => 'file', 'kind' => 'audio', 'label' => 'Audio file', 'help' => 'MP3, M4A, WAV or OGG, up to 40 MB.'], 'audio_url' => ['type' => 'url', 'label' => 'Or a link to the audio (https)'],
            'minutes' => ['type' => 'number', 'min' => 0, 'label' => 'Length in minutes (optional)'], 'sort' => ['type' => 'number', 'default' => 0, 'help' => 'The order the channel plays in; smaller numbers first.'],
            'status' => ['type' => 'select', 'options' => $PUB, 'required' => true, 'default' => 'published'],
        ],
    ],
    'bible_books' => [
        'label' => 'Bible books', 'icon' => 'book', 'group' => 'Listen & read', 'table' => 'bible_books', 'roles' => $cont, 'title' => 'name_en', 'order' => 'id', 'create' => false, 'delete' => false,
        'list' => ['name_en', 'name_lg', 'testament'], 'search' => ['name_en', 'name_lg'], 'filters' => ['testament'],
        'fields' => [
            'name_en' => ['readonly' => true, 'label' => 'Book (English)'], 'code' => ['readonly' => true, 'label' => 'Code'],
            'testament' => ['type' => 'select', 'options' => ['old' => 'Old Testament', 'new' => 'New Testament'], 'readonly' => true],
            'name_lg' => ['label' => 'Book name in Leb Lango', 'help' => 'Type the name exactly as the Lango Bible writes it. The app shows the English name until you do.'],
        ],
    ],
    'bible_verses' => [
        'label' => 'Bible verses', 'icon' => 'list', 'group' => 'Listen & read', 'table' => 'bible_verses', 'roles' => $cont, 'title' => 'text', 'order' => 'book_id, chapter, verse', 'create' => false,
        'list' => ['book_id', 'chapter', 'verse', 'text'], 'search' => ['text'], 'filters' => ['book_id'],
        'fields' => [
            'book_id' => ['type' => 'select', 'options' => 'bible_books:id:name_en', 'label' => 'Book', 'readonly' => true], 'chapter' => ['type' => 'number', 'readonly' => true], 'verse' => ['type' => 'number', 'readonly' => true],
            'text' => ['type' => 'textarea', 'rows' => 4, 'required' => true, 'label' => 'Verse text', 'help' => 'To add or replace many verses at once use "Import Bible text".'],
        ],
    ],

    /* --------------------------------- SYSTEM --------------------------------- */
    'users' => [
        'label' => 'Users & roles', 'icon' => 'lock', 'group' => 'System', 'table' => 'users', 'roles' => ADM_ALL, 'delete_roles' => ['super_admin'], 'title' => 'name', 'order' => 'role, name',
        'list' => ['name', 'email', 'role', 'points', 'is_active', 'last_login'], 'search' => ['name', 'email'], 'filters' => ['role', 'is_active'],
        'before' => function (array &$d, bool $new, ?array $old) {
            if (user()['role'] !== 'super_admin' && (($d['role'] ?? '') === 'super_admin' || ($old['role'] ?? '') === 'super_admin')) { flash('Only a super administrator can manage super administrators.', 'error'); redirect(aurl('users')); }
            if (isset($d['email'])) $d['email'] = strtolower($d['email']);
            if ($old && (int)$old['id'] === (int)user()['id']) { $d['is_active'] = 1; $d['role'] = $old['role']; }
        },
        'fields' => [
            'name' => ['required' => true], 'email' => ['type' => 'email', 'required' => true], 'password' => ['type' => 'password', 'label' => 'Password'],
            'role' => ['type' => 'select', 'required' => true, 'options' => ['super_admin' => 'Super administrator', 'admin' => 'Administrator', 'editor' => 'Editor', 'moderator' => 'Moderator', 'clan_leader' => 'Clan leader', 'contributor' => 'Contributor (public)'],
                'help' => 'Editors manage content. Moderators review community submissions. Clan leaders can edit only their own clan.'],
            'clan_id' => $clanSel + ['help' => 'Required for clan leaders. Members choose their clan when they register.'], 'clan_other' => ['label' => 'Clan (typed by member, not in register)'],
            'phone' => [], 'country' => [], 'points' => ['type' => 'number', 'default' => 0],
            'is_active' => $bool + ['default' => 1, 'label' => 'Account active'], 'must_change_password' => $bool + ['label' => 'Must change password at next sign-in'],
            'last_login' => ['type' => 'datetime', 'readonly' => true, 'label' => 'Last sign-in'],
        ],
    ],
];
