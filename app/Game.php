<?php
defined('GAMEHUB') or exit('Forbidden');

/** 游戏模型：查询 / 保存 / 上下架 / 开玩日志 */
class Game
{
    public static function onlineList(string $q = '', string $category = ''): array
    {
        $sql = 'SELECT * FROM games WHERE status = "online"';
        $p = [];
        if ($q !== '') {
            $sql .= ' AND (name LIKE ? OR short_desc LIKE ?)';
            $p[] = "%{$q}%";
            $p[] = "%{$q}%";
        }
        if ($category !== '') {
            $sql .= ' AND category = ?';
            $p[] = $category;
        }
        $sql .= ' ORDER BY featured DESC, sort_order DESC, id DESC';
        return DB::all($sql, $p);
    }

    public static function featured(int $n = 3): array
    {
        return DB::all(
            'SELECT * FROM games WHERE status = "online" AND featured = 1 ORDER BY sort_order DESC, id DESC LIMIT ' . (int)$n
        );
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM games WHERE id = ?', [$id]);
    }

    public static function related(array $game, int $n = 4): array
    {
        return DB::all(
            'SELECT * FROM games WHERE status = "online" AND id <> ? AND category = ? ORDER BY play_count DESC LIMIT ' . (int)$n,
            [$game['id'], $game['category']]
        );
    }

    /** C端可见的分类（去重） */
    public static function categories(): array
    {
        return array_column(
            DB::all('SELECT DISTINCT category FROM games WHERE status = "online" ORDER BY category'),
            'category'
        );
    }

    /** C端"开始游戏"按钮去向；返回 '' 表示无网页入口（引导去小程序） */
    public static function launchUrl(array $g): string
    {
        if ($g['entry_url'] === '') {
            return '';
        }
        return $g['play_mode'] === 'iframe'
            ? url('play', ['id' => $g['id']])
            : url('go', ['id' => $g['id']]);
    }

    public static function platforms(array $g): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string)$g['platforms']))));
    }

    public static function tags(array $g): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string)$g['tags']))));
    }

    /* ---------------- 管理端 ---------------- */

    public static function adminList(): array
    {
        return DB::all('SELECT * FROM games ORDER BY sort_order DESC, id DESC');
    }

    public static function save(array $d, ?int $id = null): int
    {
        $d['slug'] = self::uniqueSlug($d['slug'], $id);
        if ($id) {
            $sets = 'name=?, slug=?, category=?, play_mode=?, entry_url=?, platforms=?, miniapp_platform=?, miniapp_appid=?, miniapp_qr=?, cover=?, short_desc=?, description=?, tags=?, status=?, featured=?, sort_order=?';
            $p = [
                $d['name'], $d['slug'], $d['category'], $d['play_mode'], $d['entry_url'], $d['platforms'],
                $d['miniapp_platform'], $d['miniapp_appid'], $d['miniapp_qr'], $d['cover'],
                $d['short_desc'], $d['description'], $d['tags'], $d['status'], $d['featured'], $d['sort_order'],
                $id,
            ];
            DB::q("UPDATE games SET {$sets} WHERE id = ?", $p);
            return $id;
        }
        DB::q(
            'INSERT INTO games (name, slug, category, play_mode, entry_url, platforms, miniapp_platform, miniapp_appid, miniapp_qr, cover, short_desc, description, tags, status, featured, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $d['name'], $d['slug'], $d['category'], $d['play_mode'], $d['entry_url'], $d['platforms'],
                $d['miniapp_platform'], $d['miniapp_appid'], $d['miniapp_qr'], $d['cover'],
                $d['short_desc'], $d['description'], $d['tags'], $d['status'], $d['featured'], $d['sort_order'],
            ]
        );
        return DB::lastId();
    }

    private static function uniqueSlug(string $slug, ?int $exceptId): string
    {
        $slug = trim(preg_replace('/[^a-z0-9\-]+/', '-', strtolower($slug)) ?? '', '-');
        if ($slug === '') {
            $slug = 'game-' . substr(bin2hex(random_bytes(4)), 0, 6);
        }
        $base = $slug;
        $i = 2;
        while (true) {
            $hit = DB::one('SELECT id FROM games WHERE slug = ?', [$slug]);
            if (!$hit || (int)$hit['id'] === (int)$exceptId) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }

    public static function toggle(int $id): void
    {
        DB::q('UPDATE games SET status = IF(status = "online", "offline", "online") WHERE id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        DB::q('DELETE FROM games WHERE id = ?', [$id]);
        DB::q('DELETE FROM play_logs WHERE game_id = ?', [$id]);
    }

    /* ---------------- 统计 ---------------- */

    public static function logPlay(int $id): void
    {
        DB::q('UPDATE games SET play_count = play_count + 1 WHERE id = ?', [$id]);
        DB::q('INSERT INTO play_logs (game_id, ip, user_agent) VALUES (?, ?, ?)', [
            $id, client_ip(), mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }

    public static function adminStats(): array
    {
        return [
            'total' => (int)DB::scalar('SELECT COUNT(*) FROM games'),
            'online' => (int)DB::scalar('SELECT COUNT(*) FROM games WHERE status = "online"'),
            'plays' => (int)DB::scalar('SELECT COUNT(*) FROM play_logs'),
            'plays_today' => (int)DB::scalar('SELECT COUNT(*) FROM play_logs WHERE created_at >= CURDATE()'),
        ];
    }

    public static function recentLogs(int $n = 10): array
    {
        return DB::all(
            'SELECT l.*, g.name AS game_name FROM play_logs l
             LEFT JOIN games g ON g.id = l.game_id
             ORDER BY l.id DESC LIMIT ' . (int)$n
        );
    }
}
