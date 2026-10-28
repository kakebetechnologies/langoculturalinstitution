<?php
declare(strict_types=1);

/* ==========================================================================
   Daily-habit features for the mobile app. Everything here is built from content that already exists in the archive (the dictionary,
   heritage items, notable Langos, clans, approved contributions, events); nothing is invented. When the pool is empty the answer is
   empty and the app shows nothing.

     POST /me/checkin                     record that the member opened the app today → streak, milestone
     GET  /daily/word                     today's word with four meanings to choose from (same for everyone), and whether the member answered
     POST /daily/word/answer              { choice } → correct or not; counts as "learned today" for the word streak
     GET  /challenge                      this week's Language Challenge: ten "what does this word mean?" questions (same for everyone)
     POST /challenge/submit               { answers: { "<word id>": "<chosen meaning>" } } → score (one attempt per week)
     GET  /leaderboards?kind=challenge|learners
     GET  /clans/leaderboard?sort=members|contributions|learners
     GET  /clans/{id}/stats               (in clan_group_api.php)
   Streaks: one missed day is forgiven once every seven days (the "streak freeze"), so a single busy day does not wipe out a long run.
   ========================================================================== */

/** The first (main) meaning of a dictionary entry, as shown on a quiz button. */
function daily_meaning(string $english): string
{
    $first = trim((string)preg_split('/[;,]/', $english)[0]);
    return mb_substr($first !== '' ? $first : $english, 0, 36);
}

/** All of a word's meanings in lower case (a distractor must not be any of them). */
function daily_meanings(string $english): array
{
    return array_values(array_filter(array_map(fn($s) => mb_strtolower(trim($s)), preg_split('/[;,]/', $english) ?: [])));
}

/** Four meanings (the right one among three others), in an order that depends only on $seed, so everyone sees the same quiz. */
function daily_options(array $word, string $seed): array
{
    $right = daily_meaning($word['english']);
    $skip = daily_meanings($word['english']);
    $out = [mb_strtolower($right) => $right];
    $rows = all('SELECT english FROM dictionary d WHERE ' . daily_word_filter('d') . " AND d.id<>? AND CHAR_LENGTH(d.english)<40 ORDER BY CRC32(CONCAT(?, d.id)) LIMIT 40", [$word['id'], $seed]);
    foreach ($rows as $r) {
        $m = daily_meaning($r['english']);
        $k = mb_strtolower($m);
        if ($m === '' || isset($out[$k]) || array_intersect(daily_meanings($r['english']), $skip)) continue;
        $out[$k] = $m;
        if (count($out) === 4) break;
    }
    $opts = array_values($out);
    usort($opts, fn($a, $b) => crc32($seed . $a) <=> crc32($seed . $b));
    return $opts;
}

/* ---------- streaks ---------- */
function activity_mark(int $userId, string $kind, ?string $day = null): bool
{
    return q('INSERT IGNORE INTO activity_days (user_id, kind, day) VALUES (?,?,?)', [$userId, $kind, $day ?? date('Y-m-d')])->rowCount() > 0;
}

/** Current run of days with activity, counting a single missed day as forgiven once every seven days. */
function streak_calc(array $days, string $today): array
{
    $date = isset($days[$today]) ? strtotime($today) : strtotime('-1 day', strtotime($today));
    $cur = 0;
    $since = 99;          // active days counted since the last forgiven day
    $forgiven = false;
    while (true) {
        $d = date('Y-m-d', $date);
        if (isset($days[$d])) { $cur++; $since++; $date = strtotime('-1 day', $date); continue; }
        $prev = date('Y-m-d', strtotime('-1 day', $date));
        if ($since >= 6 && isset($days[$prev])) { $since = 0; $forgiven = true; $date = strtotime('-1 day', $date); continue; }
        break;
    }
    return ['current' => $cur, 'freeze_available' => $since >= 6, 'freeze_used' => $forgiven];
}

function streak_info(int $userId, string $kind = 'open'): array
{
    $rows = all('SELECT day FROM activity_days WHERE user_id=? AND kind=? AND day >= (CURDATE() - INTERVAL 400 DAY)', [$userId, $kind]);
    $days = array_fill_keys(array_column($rows, 'day'), true);
    $today = date('Y-m-d');
    $c = streak_calc($days, $today);
    $c['today'] = isset($days[$today]);
    $c['total_days'] = count($days);
    return $c;
}

const STREAK_MILESTONES = [3, 7, 14, 30, 60, 100, 200, 365];

/** What the app shows about a member's habit: opening streak, best, freeze, and the word-learning streak. */
function streaks_json(array $u): array
{
    $open = streak_info((int)$u['id'], 'open');
    $word = streak_info((int)$u['id'], 'word');
    $best = max((int)($u['best_streak'] ?? 0), $open['current']);
    return ['current' => $open['current'], 'best' => $best, 'freeze_available' => $open['freeze_available'], 'opened_today' => $open['today'],
        'words' => $word['current'], 'words_total' => $word['total_days'], 'learned_today' => $word['today'], 'next_milestone' => array_values(array_filter(STREAK_MILESTONES, fn($m) => $m > $open['current']))[0] ?? null];
}

/* ---------- the word of the day as a quiz ---------- */
function daily_word_view(?array $u): array
{
    $w = word_of_day();
    if (!$w) return ['word' => null];
    $seed = date('Y-m-d');
    $answered = $u ? one("SELECT score FROM quiz_scores WHERE user_id=? AND kind='word' AND period=?", [$u['id'], $seed]) : null;
    return ['date' => $seed, 'word' => api_word($w), 'options' => daily_options($w, $seed), 'answered' => $answered !== null, 'was_correct' => $answered ? (int)$answered['score'] === 1 : null,
        'answer' => $answered ? daily_meaning($w['english']) : null, 'all_meanings' => $answered ? $w['english'] : null,
        'share_text' => sprintf('I just learned the Lango word "%s": %s. Learn Leb Lango with the Lango App.', $w['lango'], daily_meaning($w['english']))];
}

/* ---------- the weekly Language Challenge ---------- */
function challenge_week(): string { return date('o-\WW'); }

function challenge_days_left(): int { return (int)ceil((strtotime('monday next week') - time()) / 86400); }

function challenge_questions(string $week): array
{
    $rows = all('SELECT d.* FROM dictionary d WHERE ' . daily_word_filter('d') . ' AND CHAR_LENGTH(d.english)<40 ORDER BY CRC32(CONCAT(?, d.id)) LIMIT 10', [$week]);
    return array_map(fn($w) => ['id' => (int)$w['id'], 'word' => $w['lango'], 'options' => daily_options($w, $week . '-' . $w['id']), 'answer' => daily_meaning($w['english'])], $rows);
}

function challenge_view(?array $u): array
{
    $week = challenge_week();
    $qs = challenge_questions($week);
    $mine = $u ? one("SELECT score, total FROM quiz_scores WHERE user_id=? AND kind='weekly' AND period=?", [$u['id'], $week]) : null;
    return ['week' => $week, 'days_left' => challenge_days_left(), 'total' => count($qs), 'done' => $mine !== null, 'score' => $mine ? (int)$mine['score'] : null,
        'questions' => $mine ? [] : array_map(fn($q) => ['id' => $q['id'], 'word' => $q['word'], 'options' => $q['options']], $qs)];
}

/* ---------- leaderboards ---------- */
function daily_leaders(string $kind, ?array $u): array
{
    if ($kind === 'learners') {
        $rows = all("SELECT u.id, u.name, COUNT(*) n FROM activity_days a JOIN users u ON u.id=a.user_id WHERE a.kind='word' AND a.day >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND u.is_active=1 GROUP BY u.id ORDER BY n DESC, MIN(a.created_at) LIMIT 10");
        $unit = 'days';
    } else {
        $rows = all("SELECT u.id, u.name, q.score n FROM quiz_scores q JOIN users u ON u.id=q.user_id WHERE q.kind='weekly' AND q.period=? AND u.is_active=1 ORDER BY q.score DESC, q.created_at LIMIT 10", [challenge_week()]);
        $unit = 'points';
    }
    $list = [];
    foreach ($rows as $i => $r) $list[] = ['rank' => $i + 1, 'name' => short_person_name($r['name']), 'score' => (int)$r['n'], 'you' => $u && (int)$r['id'] === (int)$u['id']];
    return ['kind' => $kind, 'unit' => $unit, 'period' => $kind === 'learners' ? date('F Y') : challenge_week(), 'data' => $list];
}

/** Every published clan with its numbers: registered members, people in the group, approved contributions, learners in the last 30 days. */
function clan_stats_all(): array
{
    $clans = all("SELECT id, name, slug, totem, verified FROM clans WHERE status='published' ORDER BY name");
    $by = fn(string $sql) => array_column(all($sql), 'n', 'k');
    $members = $by("SELECT clan_id k, COUNT(*) n FROM users WHERE clan_id IS NOT NULL AND is_active=1 GROUP BY clan_id");
    $joined = $by("SELECT clan_id k, COUNT(*) n FROM users WHERE clan_id IS NOT NULL AND clan_joined_at IS NOT NULL AND is_active=1 GROUP BY clan_id");
    $contrib = $by("SELECT COALESCE(contributor_clan_id, clan_id) k, COUNT(*) n FROM contributions WHERE status IN ('approved','published') AND COALESCE(contributor_clan_id, clan_id) IS NOT NULL GROUP BY k");
    $learn = $by("SELECT u.clan_id k, COUNT(DISTINCT u.id) n FROM activity_days a JOIN users u ON u.id=a.user_id WHERE a.kind='word' AND a.day >= (CURDATE() - INTERVAL 30 DAY) AND u.clan_id IS NOT NULL GROUP BY u.clan_id");
    foreach ($clans as &$c) {
        $id = (int)$c['id'];
        $c = ['id' => $id, 'name' => $c['name'], 'slug' => $c['slug'], 'totem' => $c['totem'], 'verified' => (bool)$c['verified'],
            'members' => (int)($members[$id] ?? 0), 'group_members' => (int)($joined[$id] ?? 0), 'contributions' => (int)($contrib[$id] ?? 0), 'learners' => (int)($learn[$id] ?? 0)];
    }
    return $clans;
}

function clan_rank_by(array $rows, string $sort): array
{
    usort($rows, fn($a, $b) => [$b[$sort], $a['name']] <=> [$a[$sort], $b['name']]);
    foreach ($rows as $i => &$r) $r['rank'] = $i + 1;
    return $rows;
}

/* ---------- Clan of the Week, Today's story, Photo of the day ---------- */
function clan_of_the_week(?array $u): ?array
{
    $clans = all("SELECT c.*, d.name district FROM clans c LEFT JOIN districts d ON d.id=c.district_id WHERE c.status='published' ORDER BY c.id");
    if (!$clans) return null;
    $pin = (int)setting('clan_of_week_id', '0');
    $c = null;
    foreach ($clans as $x) if ($pin && (int)$x['id'] === $pin) $c = $x;
    $c ??= $clans[(int)floor(strtotime('monday this week') / 604800) % count($clans)];
    $stats = array_values(array_filter(clan_stats_all(), fn($s) => $s['id'] === (int)$c['id']))[0] ?? ['members' => 0, 'group_members' => 0];
    $ld = clan_leader_of($c);
    return ['id' => (int)$c['id'], 'slug' => $c['slug'], 'name' => $c['name'], 'totem' => $c['totem'], 'district' => $c['district'], 'summary' => $c['summary'] ?: excerpt($c['history'], 220),
        'image' => api_abs($c['image']), 'verified' => (bool)$c['verified'], 'leader' => $ld ? ['name' => $ld['name'], 'title' => $ld['title']] : null,
        'members' => $stats['members'], 'group_members' => $stats['group_members'], 'is_mine' => $u && (int)$u['clan_id'] === (int)$c['id'], 'week_ends_in' => challenge_days_left()];
}

/** One heritage item, notable person or history chapter per day, in a fixed order, so everyone reads the same story. */
function story_of_the_day(): ?array
{
    $pool = [];
    foreach (all("SELECT id, slug, title, summary, body, image FROM heritage_items WHERE status='published' ORDER BY id") as $r) $pool[] = ['kind' => 'heritage', 'label' => 'Heritage', 'route' => '/heritage/' . $r['slug']] + $r;
    foreach (all("SELECT id, slug, name title, summary, body, photo image FROM notables WHERE status='published' ORDER BY id") as $r) $pool[] = ['kind' => 'person', 'label' => 'Notable Lango', 'route' => '/people/' . $r['slug']] + $r;
    foreach (all("SELECT id, slug, title, summary, body, NULL image FROM history_sections WHERE status='published' ORDER BY id") as $r) $pool[] = ['kind' => 'history', 'label' => 'Our history', 'route' => '/history'] + $r;
    $pool = array_values(array_filter($pool, fn($s) => trim(strip_tags((string)($s['body'] ?: $s['summary']))) !== ''));
    if (!$pool) return null;
    $s = $pool[((int)date('Y') * 366 + (int)date('z')) % count($pool)];
    $text = trim(strip_tags((string)($s['body'] ?: $s['summary'])));
    return ['kind' => $s['kind'], 'label' => $s['label'], 'title' => $s['title'], 'excerpt' => excerpt($s['summary'] ?: $text, 260), 'image' => api_abs($s['image']), 'route' => $s['route'],
        'minutes' => max(1, (int)round(str_word_count($text) / 200))];
}

function photo_of_the_day(): ?array
{
    $r = one("SELECT c.id, c.title, c.media_file, c.credit, c.created_at, u.name, cl.name clan FROM contributions c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN clans cl ON cl.id=COALESCE(c.contributor_clan_id, c.clan_id)
              WHERE c.status IN ('approved','published') AND c.media_kind='image' AND c.created_at >= (NOW() - INTERVAL 7 DAY) ORDER BY c.id DESC LIMIT 1");
    return $r ? ['id' => (int)$r['id'], 'title' => $r['title'], 'image' => api_abs($r['media_file']), 'shared_by' => !empty($r['credit']) && $r['name'] ? $r['name'] : null, 'clan' => $r['clan'], 'created_at' => $r['created_at']] : null;
}

/** For a signed-in member with a clan: where the clan stands and the latest things its members added. */
function clan_feed(?array $u): ?array
{
    if (!$u || !$u['clan_id']) return null;
    $clanId = (int)$u['clan_id'];
    $rows = clan_rank_by(clan_stats_all(), 'members');
    $me = array_values(array_filter($rows, fn($r) => $r['id'] === $clanId))[0] ?? null;
    if (!$me) return null;
    $latest = all("SELECT c.id, c.type, c.title, c.media_kind, c.media_file, c.created_at FROM contributions c WHERE (c.clan_id=? OR c.contributor_clan_id=?) AND c.status IN ('approved','published') ORDER BY c.id DESC LIMIT 3", [$clanId, $clanId]);
    return ['clan' => ['id' => $clanId, 'name' => $me['name'], 'slug' => $me['slug']], 'rank' => $me['rank'], 'of' => count($rows), 'members' => $me['members'], 'group_members' => $me['group_members'], 'contributions' => $me['contributions'], 'learners' => $me['learners'],
        'latest' => array_map(fn($r) => ['id' => (int)$r['id'], 'title' => $r['title'], 'type_label' => CONTRIB_TYPES[$r['type']][0] ?? $r['type'], 'image' => $r['media_kind'] === 'image' ? api_abs($r['media_file']) : null, 'created_at' => $r['created_at']], $latest)];
}

/** Everything the Home screen adds for the daily habit, in one place. */
function daily_home(?array $u): array
{
    $ch = challenge_view($u);
    return ['story' => story_of_the_day(), 'clan_of_week' => clan_of_the_week($u), 'photo_of_day' => photo_of_the_day(), 'clan_feed' => clan_feed($u),
        'challenge' => ['week' => $ch['week'], 'days_left' => $ch['days_left'], 'total' => $ch['total'], 'done' => $ch['done'], 'score' => $ch['score']],
        'word_quiz' => $u ? ['answered' => (bool)val("SELECT COUNT(*) FROM quiz_scores WHERE user_id=? AND kind='word' AND period=?", [$u['id'], date('Y-m-d')])] : ['answered' => false],
        'streaks' => $u ? streaks_json($u) : null];
}

/* ---------- the routes ---------- */
/** Handles the daily-habit endpoints; returns normally for any other request. */
function daily_api(string $res, string $id, string $sub, string $m, ?array $u): void
{
    if ($res === 'me' && $id === 'checkin' && $m === 'POST') {
        $u ??= api_need_user();
        $fresh = activity_mark((int)$u['id'], 'open');
        $s = streaks_json($u);
        if ($s['current'] > (int)$u['best_streak']) q('UPDATE users SET best_streak=? WHERE id=?', [$s['current'], $u['id']]);
        json_out(['data' => $s + ['milestone' => $fresh && in_array($s['current'], STREAK_MILESTONES, true) ? $s['current'] : null]]);
    }
    if ($res === 'daily' && $id === 'word') {
        if ($m === 'GET' && $sub === '') json_out(['data' => daily_word_view($u)]);
        if ($m === 'POST' && $sub === 'answer') {
            $w = word_of_day() ?? api_err('There is no word today.', 404);
            $choice = trim((string)(api_in()['choice'] ?? ''));
            $right = daily_meaning($w['english']);
            $correct = $choice !== '' && mb_strtolower($choice) === mb_strtolower($right);
            if ($u) {
                q("INSERT IGNORE INTO quiz_scores (user_id, kind, period, score, total) VALUES (?, 'word', ?, ?, 1)", [$u['id'], date('Y-m-d'), $correct ? 1 : 0]);
                activity_mark((int)$u['id'], 'word');
            }
            json_out(['data' => ['correct' => $correct, 'answer' => $right, 'all_meanings' => $w['english'], 'example_lg' => $w['example_lg'], 'example_en' => $w['example_en'],
                'streaks' => $u ? streaks_json($u) : null]]);
        }
    }
    if ($res === 'challenge') {
        if ($m === 'GET' && $id === '') json_out(['data' => challenge_view($u)]);
        if ($m === 'POST' && $id === 'submit') {
            $u ??= api_need_user();
            $week = challenge_week();
            if (one("SELECT id FROM quiz_scores WHERE user_id=? AND kind='weekly' AND period=?", [$u['id'], $week])) api_err('You have already taken this week\'s challenge.', 409);
            $qs = challenge_questions($week);
            if (!$qs) api_err('There is no challenge this week.', 404);
            $answers = (array)(api_in()['answers'] ?? []);
            $score = 0; $review = [];
            foreach ($qs as $q) {
                $choice = trim((string)($answers[(string)$q['id']] ?? ''));
                $ok = $choice !== '' && mb_strtolower($choice) === mb_strtolower($q['answer']);
                $score += $ok ? 1 : 0;
                $review[] = ['id' => $q['id'], 'word' => $q['word'], 'answer' => $q['answer'], 'choice' => $choice, 'correct' => $ok];
            }
            q("INSERT INTO quiz_scores (user_id, kind, period, score, total) VALUES (?, 'weekly', ?, ?, ?)", [$u['id'], $week, $score, count($qs)]);
            $rank = 1 + (int)val("SELECT COUNT(*) FROM quiz_scores q JOIN users u ON u.id=q.user_id AND u.is_active=1 WHERE q.kind='weekly' AND q.period=? AND q.score > ?", [$week, $score]);
            json_out(['data' => ['score' => $score, 'total' => count($qs), 'rank' => $rank, 'review' => $review,
                'share_text' => sprintf('I scored %d/%d on this week\'s Lango Challenge in the Lango App!', $score, count($qs))]], 201);
        }
    }
    if ($res === 'leaderboards' && $m === 'GET') {
        $kind = $id !== '' ? $id : (string)get('kind', 'challenge');
        json_out(daily_leaders($kind === 'learners' ? 'learners' : 'challenge', $u));
    }
    if ($res === 'clans' && $id === 'leaderboard' && $m === 'GET') {
        $sort = in_array(get('sort'), ['members', 'contributions', 'learners'], true) ? get('sort') : 'members';
        $rows = clan_rank_by(clan_stats_all(), $sort);
        $mine = $u && $u['clan_id'] ? (array_values(array_filter($rows, fn($r) => $r['id'] === (int)$u['clan_id']))[0] ?? null) : null;
        json_out(['sort' => $sort, 'data' => $rows, 'mine' => $mine, 'registered_total' => (int)setting('clan_total', '149')]);
    }
}
