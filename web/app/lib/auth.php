<?php
declare(strict_types=1);

const STAFF_ROLES = ['super_admin', 'admin', 'editor', 'moderator', 'clan_leader'];

function user(bool $reset = false): ?array
{
    static $u = false;
    if ($reset) $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['uid'])) {
            $u = one('SELECT * FROM users WHERE id=? AND is_active=1', [$_SESSION['uid']]);
            if (!$u) unset($_SESSION['uid']);
        }
    }
    return $u;
}

function is_staff(?array $u = null): bool
{
    $u = $u ?? user();
    return $u && in_array($u['role'], STAFF_ROLES, true);
}

function login_user(string $email, string $password): ?array
{
    $row = one('SELECT * FROM users WHERE email=? AND is_active=1', [strtolower(trim($email))]);
    if (!$row || !password_verify($password, $row['password_hash'])) return null;
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$row['id'];
    $_SESSION['csrf'] = bin2hex(random_bytes(24));
    user(true);
    q('UPDATE users SET last_login=NOW() WHERE id=?', [$row['id']]);
    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($password, PASSWORD_DEFAULT), $row['id']]);
    }
    return $row;
}

function logout_user(): void
{
    unset($_SESSION['uid']);
    session_regenerate_id(true);
    user(true);
}

function audit(string $action, ?string $entity = null, ?int $id = null, ?string $detail = null): void
{
    try {
        q('INSERT INTO audit_log (user_id,action,entity,entity_id,detail,ip) VALUES (?,?,?,?,?,?)',
            [user()['id'] ?? null, $action, $entity, $id, $detail ? mb_substr($detail, 0, 500) : null, client_ip()]);
    } catch (Throwable $e) {}
}
