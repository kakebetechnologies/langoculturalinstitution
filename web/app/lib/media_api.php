<?php
declare(strict_types=1);

/* ==========================================================================
   Listen & read, for the app: the Lango Podcast (elders' stories), Lango Radio, the Lango Bible and how to give.
   Everything here is shown only when LCI has put it in the admin panel: nothing is written for them, so the app shows an
   empty state until there is content. Called from api_app() like daily_api(); returns normally for any other request.
   ========================================================================== */

const GIVE_METHODS = ['mtn_momo' => 'MTN Mobile Money', 'airtel_money' => 'Airtel Money', 'visa' => 'Visa', 'mastercard' => 'Mastercard', 'paypal' => 'PayPal', 'bank' => 'Bank transfer'];

/** An episode the app may show: published, already released and with something to play. */
const PODCAST_LIVE = "e.status='published' AND (e.published_at IS NULL OR e.published_at<=NOW()) AND ((e.audio_file IS NOT NULL AND e.audio_file<>'') OR (e.audio_url IS NOT NULL AND e.audio_url<>''))";

/** The live episodes, newest first, with the clan and district names. */
function podcast_sql(string $where = '', string $tail = ''): string
{
    return "SELECT e.*, c.name clan_name, d.name district_name FROM podcast_episodes e LEFT JOIN clans c ON c.id=e.clan_id LEFT JOIN districts d ON d.id=e.district_id
            WHERE " . PODCAST_LIVE . " $where ORDER BY COALESCE(e.published_at, e.created_at) DESC, e.id DESC $tail";
}

function api_audio_src(array $r): ?string
{
    if (!empty($r['audio_file'])) return api_abs($r['audio_file']);
    return preg_match('#^https?://#i', (string)($r['audio_url'] ?? '')) ? (string)$r['audio_url'] : null;
}

function api_episode(array $e, bool $full = false): array
{
    $out = ['id' => (int)$e['id'], 'title' => $e['title'], 'storyteller' => $e['storyteller'], 'storyteller_note' => $e['storyteller_note'], 'series' => $e['series'],
        'clan' => $e['clan_name'] ?? null, 'district' => $e['district_name'] ?? null, 'recorded_year' => $e['recorded_year'] ? (int)$e['recorded_year'] : null,
        'summary' => $e['summary'], 'minutes' => $e['minutes'] ? (int)$e['minutes'] : null, 'cover' => api_abs($e['cover_image']), 'audio_url' => api_audio_src($e),
        'published_at' => $e['published_at'] ?: $e['created_at'], 'plays' => (int)$e['plays']];
    if ($full) $out['transcript'] = $e['transcript'];
    return $out;
}

/** Pay-to instructions LCI entered in Site settings for one payment method (empty text and no link when it has none). */
function give_pay(string $method): array
{
    $g = ['visa' => 'card', 'mastercard' => 'card'][$method] ?? $method;
    $url = trim((string)setting('give_' . $g . '_url'));
    return ['instructions' => trim((string)setting('give_' . $g)), 'url' => preg_match('#^https?://#i', $url) ? $url : null];
}

function media_api(string $res, string $id, string $sub, string $m, ?array $user): void
{
    switch ($res) {
        /* ---------- Lango Podcast ---------- */
        case 'podcasts':
            if ($id === '') {
                if ($m !== 'GET') api_err('Method not allowed.', 405);
                $where = ''; $p = [];
                $q = trim((string)($_GET['q'] ?? ''));
                if ($q !== '') { $like = '%' . addcslashes($q, '%_\\') . '%'; $where .= ' AND (e.title LIKE ? OR e.storyteller LIKE ? OR e.summary LIKE ?)'; array_push($p, $like, $like, $like); }
                $series = trim((string)($_GET['series'] ?? ''));
                if ($series !== '') { $where .= ' AND e.series=?'; $p[] = $series; }
                $rows = all(podcast_sql($where, 'LIMIT 200'), $p);
                $names = array_column(all("SELECT DISTINCT series FROM podcast_episodes WHERE status='published' AND series IS NOT NULL AND series<>'' ORDER BY series"), 'series');
                json_out(['data' => array_map('api_episode', $rows), 'series' => $names]);
            }
            $e = one(podcast_sql('AND e.id=?', 'LIMIT 1'), [(int)$id]) ?? api_err('We could not find that episode.', 404);
            if ($sub === 'play' && $m === 'POST') {
                api_throttle('play' . (int)$id, 20, 3600);
                q('UPDATE podcast_episodes SET plays=plays+1 WHERE id=?', [(int)$id]);
                json_out(['ok' => true]);
            }
            if ($m !== 'GET') api_err('Method not allowed.', 405);
            $cond = []; $pp = [(int)$id];   // more from the same series or the same storyteller
            if ($e['series']) { $cond[] = 'e.series=?'; $pp[] = $e['series']; }
            if ($e['storyteller']) { $cond[] = 'e.storyteller=?'; $pp[] = $e['storyteller']; }
            $more = $cond ? all(podcast_sql('AND e.id<>? AND (' . implode(' OR ', $cond) . ')', 'LIMIT 6'), $pp) : [];
            json_out(['data' => api_episode($e, true), 'more' => array_map('api_episode', $more)]);

        /* ---------- Lango Radio ---------- */
        case 'radio':
            if ($m !== 'GET') api_err('Method not allowed.', 405);
            $sizes = [];
            foreach (all("SELECT channel_id, COUNT(*) n FROM radio_items WHERE status='published' AND (audio_file<>'' OR audio_url<>'') GROUP BY channel_id") as $r) $sizes[(int)$r['channel_id']] = (int)$r['n'];
            $eps = (int)val('SELECT COUNT(*) FROM podcast_episodes e WHERE ' . PODCAST_LIVE);
            $channel = function (array $c) use ($sizes, $eps): ?array {
                $n = $c['source'] === 'playlist' ? ($sizes[(int)$c['id']] ?? 0) : ($c['source'] === 'podcast' ? $eps : null);
                $stream = $c['source'] === 'stream' && preg_match('#^https?://#i', (string)$c['stream_url']) ? (string)$c['stream_url'] : null;
                if ($c['source'] === 'stream' ? !$stream : !$n) return null;   // nothing to play yet: the channel stays hidden
                return ['id' => (int)$c['id'], 'name' => $c['name'], 'description' => $c['description'], 'source' => $c['source'], 'cover' => api_abs($c['cover_image']), 'count' => $n, 'stream_url' => $stream];
            };
            if ($id === '') {
                $list = array_values(array_filter(array_map($channel, all("SELECT * FROM radio_channels WHERE status='published' ORDER BY sort, id"))));
                json_out(['data' => $list]);
            }
            $c = one("SELECT * FROM radio_channels WHERE id=? AND status='published'", [(int)$id]);
            $out = $c ? $channel($c) : null;
            if (!$out) api_err('We could not find that channel.', 404);
            $tracks = [];
            if ($c['source'] === 'playlist') {
                foreach (all("SELECT * FROM radio_items WHERE channel_id=? AND status='published' ORDER BY sort, id", [(int)$id]) as $t) {
                    if ($src = api_audio_src($t)) $tracks[] = ['id' => 'i' . $t['id'], 'title' => $t['title'], 'credit' => $t['credit'], 'minutes' => $t['minutes'] ? (int)$t['minutes'] : null, 'audio_url' => $src, 'cover' => $out['cover']];
                }
            } elseif ($c['source'] === 'podcast') {
                foreach (all(podcast_sql('', 'LIMIT 200')) as $e) $tracks[] = ['id' => 'e' . $e['id'], 'title' => $e['title'], 'credit' => $e['storyteller'], 'minutes' => $e['minutes'] ? (int)$e['minutes'] : null, 'audio_url' => api_audio_src($e), 'cover' => api_abs($e['cover_image']) ?: $out['cover']];
            }
            json_out(['data' => $out + ['tracks' => $tracks]]);

        /* ---------- Lango Bible ---------- */
        case 'bible':
            if ($m !== 'GET') api_err('Method not allowed.', 405);
            if ($id === '') {
                $books = all('SELECT b.id, b.code, b.testament, b.name_en, b.name_lg, COALESCE(MAX(v.chapter), 0) chapters, COUNT(v.id) verses FROM bible_books b LEFT JOIN bible_verses v ON v.book_id=b.id GROUP BY b.id ORDER BY b.id');
                $books = array_map(fn($b) => ['id' => (int)$b['id'], 'code' => $b['code'], 'testament' => $b['testament'], 'name' => $b['name_lg'] ?: $b['name_en'], 'name_en' => $b['name_en'], 'name_lg' => $b['name_lg'] ?: null, 'chapters' => (int)$b['chapters'], 'verses' => (int)$b['verses']], $books);
                json_out(['data' => ['title' => setting('bible_title') ?: 'Lango Bible', 'copyright' => setting('bible_copyright') ?: null, 'ready' => (bool)array_sum(array_column($books, 'verses')), 'books' => $books]]);
            }
            if ($id === 'search') {
                api_throttle('biblesearch', 60, 600);
                $term = trim((string)($_GET['q'] ?? ''));
                if (mb_strlen($term) < 2) json_out(['data' => [], 'more' => false]);
                $rows = all('SELECT v.book_id, v.chapter, v.verse, v.text, b.name_en, b.name_lg FROM bible_verses v JOIN bible_books b ON b.id=v.book_id WHERE v.text LIKE ? ORDER BY v.book_id, v.chapter, v.verse LIMIT 41', ['%' . addcslashes($term, '%_\\') . '%']);
                $more = count($rows) > 40;
                json_out(['data' => array_map(fn($r) => ['book_id' => (int)$r['book_id'], 'book' => $r['name_lg'] ?: $r['name_en'], 'chapter' => (int)$r['chapter'], 'verse' => (int)$r['verse'], 'text' => $r['text']], array_slice($rows, 0, 40)), 'more' => $more]);
            }
            $book = one('SELECT * FROM bible_books WHERE id=?', [(int)$id]) ?? api_err('We could not find that book.', 404);
            $chapters = (int)val('SELECT COALESCE(MAX(chapter),0) FROM bible_verses WHERE book_id=?', [$book['id']]);
            if ($sub === '') {   // the chapters of this book that have text
                json_out(['data' => ['book' => ['id' => (int)$book['id'], 'name' => $book['name_lg'] ?: $book['name_en'], 'name_en' => $book['name_en'], 'chapters' => $chapters],
                    'available' => array_map('intval', array_column(all('SELECT DISTINCT chapter FROM bible_verses WHERE book_id=? ORDER BY chapter', [$book['id']]), 'chapter'))]]);
            }
            $ch = (int)$sub;
            $verses = $ch ? all('SELECT verse, text FROM bible_verses WHERE book_id=? AND chapter=? ORDER BY verse', [$book['id'], $ch]) : [];
            if (!$verses) api_err('This chapter is not available yet.', 404);
            // the chapter before and after: in this book, else the last / first chapter that exists in the nearest book with text
            $bk = (int)$book['id'];
            $prev = $next = null;
            if ($pc = (int)val('SELECT MAX(chapter) FROM bible_verses WHERE book_id=? AND chapter<?', [$bk, $ch])) $prev = ['book_id' => $bk, 'chapter' => $pc];
            elseif ($pb = one('SELECT book_id, MAX(chapter) chapter FROM bible_verses WHERE book_id<? GROUP BY book_id ORDER BY book_id DESC LIMIT 1', [$bk])) $prev = ['book_id' => (int)$pb['book_id'], 'chapter' => (int)$pb['chapter']];
            if ($nc = (int)val('SELECT MIN(chapter) FROM bible_verses WHERE book_id=? AND chapter>?', [$bk, $ch])) $next = ['book_id' => $bk, 'chapter' => $nc];
            elseif ($nb = one('SELECT book_id, MIN(chapter) chapter FROM bible_verses WHERE book_id>? GROUP BY book_id ORDER BY book_id LIMIT 1', [$bk])) $next = ['book_id' => (int)$nb['book_id'], 'chapter' => (int)$nb['chapter']];
            json_out(['data' => ['book' => ['id' => (int)$book['id'], 'name' => $book['name_lg'] ?: $book['name_en'], 'name_en' => $book['name_en'], 'chapters' => $chapters], 'chapter' => $ch,
                'verses' => array_map(fn($v) => ['n' => (int)$v['verse'], 'text' => $v['text']], $verses), 'prev' => $prev, 'next' => $next,
                'title' => setting('bible_title') ?: 'Lango Bible', 'copyright' => setting('bible_copyright') ?: null]]);

        /* ---------- Giving ---------- */
        case 'giving':
            if ($m !== 'GET') api_err('Method not allowed.', 405);
            $methods = [];
            foreach (GIVE_METHODS as $k => $label) $methods[] = ['key' => $k, 'label' => $label] + give_pay($k);
            json_out(['data' => ['note' => setting('donation_note') ?: null, 'app_text' => setting('give_app_text') ?: null, 'methods' => $methods]]);
    }
}
