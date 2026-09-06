<?php
defined('GAMEHUB') or exit('Forbidden');

/** 管理端登录态 + CSRF */
class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public static function name(): string
    {
        return $_SESSION['admin_name'] ?? '';
    }

    public static function attempt(string $username, string $password): bool
    {
        $row = DB::one('SELECT * FROM admins WHERE username = ?', [$username]);
        if (!$row || !password_verify($password, $row['password_hash'])) {
            // 轻微延迟，减缓爆破
            usleep(500000);
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$row['id'];
        $_SESSION['admin_name'] = $row['username'];
        DB::q('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$row['id']]);
        return true;
    }

    public static function changePassword(string $old, string $new): bool
    {
        $row = DB::one('SELECT * FROM admins WHERE id = ?', [self::id()]);
        if (!$row || !password_verify($old, $row['password_hash'])) {
            return false;
        }
        DB::q('UPDATE admins SET password_hash = ? WHERE id = ?', [
            password_hash($new, PASSWORD_DEFAULT), self::id(),
        ]);
        return true;
    }

    public static function id(): int
    {
        return (int)($_SESSION['admin_id'] ?? 0);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
