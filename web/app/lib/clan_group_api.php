<?php
declare(strict_types=1);

/* ==========================================================================
   Clan groups for the mobile app. A member who has chosen a clan can join that clan's group:
   see how many people have joined, who they are (unless they chose to hide), read and write messages,
   and browse the clan's approved contributions (its archive).

   Endpoints (all under /api/v1, numeric clan id):
     GET    /clans/{id}/group                    public summary: member count, and whether the signed-in person can join
     POST   /clans/{id}/join | /leave            the person's own clan only
     POST   /clans/{id}/visibility               { hidden: true|false }  hide from the member list (still counted)
     GET    /clans/{id}/members                  members of the group only
     GET    /clans/{id}/messages                 ?after=ID (poll for new) | ?before=ID (older) | latest 40
     POST   /clans/{id}/messages                 { body }  plain text, up to 1000 characters
     DELETE /clans/{id}/messages/{mid}           own message, or a clan leader / moderator
     POST   /clans/{id}/messages/{mid}/report    three different reporters hide a message until a moderator looks
     GET    /clans/{id}/archive                  approved contributions that members made for this clan
   ========================================================================== */

const CLAN_MSG_MAX = 1000;

function clan_group_members(int $clanId): int
{
    return (int)val('SELECT COUNT(*) FROM users WHERE clan_id=? AND clan_joined_at IS NOT NULL AND is_active=1', [$clanId]);
}

/** Group sizes of every clan that has one: [clan id => number of members]. */
function clan_group_counts(): array
{
    $out = [];
    foreach (all('SELECT clan_id, COUNT(*) c FROM users WHERE clan_id IS NOT NULL AND clan_joined_at IS NOT NULL AND is_active=1 GROUP BY clan_id') as $r) $out[(int)$r['clan_id']] = (int)$r['c'];
    return $out;
}

/** Messages written by others since the member last opened the group. */
function clan_group_unread(array $u): int
{
    if (!$u['clan_id'] || !$u['clan_joined_at']) return 0;
    return (int)val("SELECT COUNT(*) FROM clan_messages WHERE clan_id=? AND id>? AND status='visible' AND (user_id IS NULL OR user_id<>?)", [$u['clan_id'], $u['clan_seen_id'], $u['id']]);
}

/** The member's own clan group, for the Me and Home screens (part of the user JSON). */
function clan_group_summary(array $u): array
{
    $clanId = (int)($u['clan_id'] ?? 0);
    if (!$clanId) return ['has_clan' => false, 'joined' => false, 'members' => 0, 'unread' => 0];
    return ['has_clan' => true, 'joined' => $u['clan_joined_at'] !== null, 'hidden' => (bool)$u['clan_hidden'], 'members' => clan_group_members($clanId), 'unread' => clan_group_unread($u)];
}

/** Per-member limit that does not depend on the network address (people share phone networks). */
function clan_group_throttle(int $userId, string $what, int $max, int $window): void
{
    $k = 'clan-' . $what . ':' . $userId;
    $now = time();
    $r = one('SELECT hits, reset_at FROM rate_limits WHERE k=?', [$k]);
    if (!$r || (int)$r['reset_at'] < $now) { q('REPLACE INTO rate_limits (k,hits,reset_at) VALUES (?,1,?)', [$k, $now + $window]); return; }
    if ((int)$r['hits'] >= $max) api_err('You are sending messages very quickly. Please wait a few minutes.', 429);
    q('UPDATE rate_limits SET hits=hits+1 WHERE k=?', [$k]);
}

function clan_group_is_moderator(array $u, int $clanId): bool
{
    return in_array($u['role'], ['super_admin', 'admin', 'moderator'], true) || ($u['role'] === 'clan_leader' && (int)$u['clan_id'] === $clanId);
}

function clan_group_need_member(array $u, int $clanId): void
{
    if ((int)$u['clan_id'] !== $clanId) api_err('This group is for members of that clan.', 403);
    if ($u['clan_joined_at'] === null) api_err('Join the group first.', 403);
}

function clan_group_message_json(array $r, int $meId): array
{
    return ['id' => (int)$r['id'], 'body' => $r['body'], 'created_at' => $r['created_at'], 'mine' => $meId > 0 && (int)$r['user_id'] === $meId,
        'user' => $r['user_id'] ? ['id' => (int)$r['user_id'], 'name' => $r['name'] ?: 'Member', 'level' => contributor_level((int)$r['points'])['name']] : ['id' => 0, 'name' => 'Former member', 'level' => 'Bronze']];
}

function clan_group_view(array $clan, ?array $u): array
{
    $mine = $u && (int)$u['clan_id'] === (int)$clan['id'];
    return ['clan' => ['id' => (int)$clan['id'], 'name' => $clan['name'], 'slug' => $clan['slug']], 'members' => clan_group_members((int)$clan['id']),
        'is_my_clan' => $mine, 'joined' => $mine && $u['clan_joined_at'] !== null, 'hidden' => $mine && (bool)$u['clan_hidden'], 'signed_in' => (bool)$u];
}

function clan_group_api(int $clanId, string $sub, array $seg, string $m): never
{
    $clan = one("SELECT id, name, slug FROM clans WHERE id=? AND status='published'", [$clanId]);
    if (!$clan) api_err('We could not find that clan.', 404);
    $u = api_user();

    if ($sub === 'group' && $m === 'GET') json_out(['data' => clan_group_view($clan, $u)]);

    $u = $u ?: api_err('Please sign in again.', 401);
    $reload = fn() => one('SELECT * FROM users WHERE id=?', [$u['id']]);

    if ($sub === 'join' && $m === 'POST') {
        if ((int)$u['clan_id'] !== $clanId) api_err('You can join the group of the clan you chose when you signed up. Change your clan on your profile first.', 403);
        if ($u['clan_joined_at'] === null) q('UPDATE users SET clan_joined_at=NOW(), clan_hidden=0, clan_seen_id=(SELECT COALESCE(MAX(id),0) FROM clan_messages WHERE clan_id=?) WHERE id=?', [$clanId, $u['id']]);
        json_out(['data' => clan_group_view($clan, $reload())]);
    }
    if ($sub === 'leave' && $m === 'POST') {
        q('UPDATE users SET clan_joined_at=NULL, clan_hidden=0, clan_seen_id=0 WHERE id=? AND clan_id=?', [$u['id'], $clanId]);
        json_out(['data' => clan_group_view($clan, $reload())]);
    }
    if ($sub === 'visibility' && $m === 'POST') {
        clan_group_need_member($u, $clanId);
        q('UPDATE users SET clan_hidden=? WHERE id=?', [!empty(api_in()['hidden']) ? 1 : 0, $u['id']]);
        json_out(['data' => clan_group_view($clan, $reload())]);
    }

    clan_group_need_member($u, $clanId);

    if ($sub === 'members' && $m === 'GET') {
        [$page, $limit, $off] = api_paging(50);
        $total = (int)val('SELECT COUNT(*) FROM users WHERE clan_id=? AND clan_joined_at IS NOT NULL AND is_active=1 AND (clan_hidden=0 OR id=?)', [$clanId, $u['id']]);
        $rows = all("SELECT id, name, points, role, clan_joined_at FROM users WHERE clan_id=? AND clan_joined_at IS NOT NULL AND is_active=1 AND (clan_hidden=0 OR id=?)
                     ORDER BY (role='clan_leader') DESC, name LIMIT $limit OFFSET $off", [$clanId, $u['id']]);
        $data = array_map(fn($r) => ['id' => (int)$r['id'], 'name' => $r['name'], 'level' => contributor_level((int)$r['points'])['name'], 'leader' => $r['role'] === 'clan_leader',
            'you' => (int)$r['id'] === (int)$u['id'], 'joined' => $r['clan_joined_at']], $rows);
        json_out(['data' => $data, 'meta' => api_meta($page, $limit, $total) + ['group_size' => clan_group_members($clanId), 'hidden' => clan_group_members($clanId) - (int)val('SELECT COUNT(*) FROM users WHERE clan_id=? AND clan_joined_at IS NOT NULL AND is_active=1 AND clan_hidden=0', [$clanId])]]);
    }

    if ($sub === 'messages') {
        $mid = (int)($seg[5] ?? 0);
        if ($m === 'GET' && $mid === 0) {
            $limit = max(1, min(60, (int)get('limit', '40')));
            $after = (int)get('after', '0'); $before = (int)get('before', '0');
            $sel = "SELECT m.*, u.name, u.points FROM clan_messages m LEFT JOIN users u ON u.id=m.user_id WHERE m.clan_id=? AND m.status='visible'";
            if ($after > 0) $rows = all("$sel AND m.id>? ORDER BY m.id ASC LIMIT $limit", [$clanId, $after]);
            else $rows = array_reverse(all("$sel" . ($before > 0 ? ' AND m.id<?' : '') . " ORDER BY m.id DESC LIMIT $limit", $before > 0 ? [$clanId, $before] : [$clanId]));
            if ($before === 0 && $rows) q('UPDATE users SET clan_seen_id=GREATEST(clan_seen_id, ?) WHERE id=?', [(int)end($rows)['id'], $u['id']]);
            json_out(['data' => array_map(fn($r) => clan_group_message_json($r, (int)$u['id']), $rows), 'has_more' => $before > 0 || $after === 0 ? count($rows) === $limit : false]);
        }
        if ($m === 'POST' && $mid === 0) {
            $body = trim((string)(api_in()['body'] ?? ''));
            $body = (string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $body);
            $body = (string)preg_replace("/\n{3,}/", "\n\n", $body);
            if ($body === '') api_err('Write a message first.', 422, ['body' => 'Write a message first.']);
            if (mb_strlen($body) > CLAN_MSG_MAX) api_err('Please keep a message under ' . CLAN_MSG_MAX . ' characters.', 422, ['body' => 'Please keep a message under ' . CLAN_MSG_MAX . ' characters.']);
            if ($dup = one('SELECT m.*, u.name, u.points FROM clan_messages m LEFT JOIN users u ON u.id=m.user_id WHERE m.clan_id=? AND m.user_id=? AND m.body=? AND m.created_at > (NOW() - INTERVAL 60 SECOND)', [$clanId, $u['id'], $body]))
                json_out(['data' => clan_group_message_json($dup, (int)$u['id'])], 200);
            clan_group_throttle((int)$u['id'], 'msg', 15, 300);
            $id = insert('clan_messages', ['clan_id' => $clanId, 'user_id' => $u['id'], 'body' => $body]);
            q('UPDATE users SET clan_seen_id=GREATEST(clan_seen_id, ?) WHERE id=?', [$id, $u['id']]);
            $row = one('SELECT m.*, u.name, u.points FROM clan_messages m LEFT JOIN users u ON u.id=m.user_id WHERE m.id=?', [$id]);
            json_out(['data' => clan_group_message_json($row, (int)$u['id'])], 201);
        }
        if ($mid > 0) {
            $msg = one("SELECT * FROM clan_messages WHERE id=? AND clan_id=? AND status='visible'", [$mid, $clanId]) ?? api_err('That message is no longer there.', 404);
            if ($m === 'DELETE') {
                if ((int)$msg['user_id'] !== (int)$u['id'] && !clan_group_is_moderator($u, $clanId)) api_err('You can only delete your own messages.', 403);
                q("UPDATE clan_messages SET status='hidden', hidden_by=? WHERE id=?", [$u['id'], $mid]);
                json_out(['ok' => true]);
            }
            if ($m === 'POST' && ($seg[6] ?? '') === 'report') {
                if ((int)$msg['user_id'] === (int)$u['id']) api_err('That is your own message.', 422);
                q('INSERT IGNORE INTO clan_message_reports (message_id, user_id, reason) VALUES (?,?,?)', [$mid, $u['id'], mb_substr(trim((string)(api_in()['reason'] ?? '')), 0, 200) ?: null]);
                $reports = (int)val('SELECT COUNT(*) FROM clan_message_reports WHERE message_id=?', [$mid]);
                if ($reports >= 3) q("UPDATE clan_messages SET status='hidden' WHERE id=?", [$mid]);
                notify('clan_message', 'A clan message was reported (' . $clan['name'] . ')', mb_substr((string)$msg['body'], 0, 200), 'admin/?m=clan_messages');
                json_out(['ok' => true]);
            }
        }
        api_err('Method not allowed.', 405);
    }

    if ($sub === 'archive' && $m === 'GET') {
        [$page, $limit, $off] = api_paging(20);
        $where = "(c.clan_id=? OR c.contributor_clan_id=?) AND c.status IN ('approved','published')";
        $total = (int)val("SELECT COUNT(*) FROM contributions c WHERE $where", [$clanId, $clanId]);
        $rows = all("SELECT c.id, c.type, c.title, c.description, c.media_kind, c.media_file, c.credit, c.created_at, u.name FROM contributions c LEFT JOIN users u ON u.id=c.user_id
                     WHERE $where ORDER BY c.id DESC LIMIT $limit OFFSET $off", [$clanId, $clanId]);
        $data = array_map(fn($r) => ['id' => (int)$r['id'], 'type' => $r['type'], 'type_label' => CONTRIB_TYPES[$r['type']][0] ?? $r['type'], 'title' => $r['title'], 'excerpt' => excerpt($r['description'], 220),
            'media_kind' => $r['media_kind'], 'image' => $r['media_kind'] === 'image' ? api_abs($r['media_file']) : null, 'shared_by' => !empty($r['credit']) && $r['name'] ? $r['name'] : null, 'created_at' => $r['created_at']], $rows);
        json_out(['data' => $data, 'meta' => api_meta($page, $limit, $total)]);
    }

    api_err('Unknown request.', 404);
}
