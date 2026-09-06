<?php
defined('GAMEHUB') or exit('Forbidden');

/** C端（玩家侧）控制器 */
class Site
{
    public static function home(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $cat = trim((string)($_GET['cat'] ?? ''));
        $games = Game::onlineList($q, $cat);
        $featured = ($q === '' && $cat === '') ? Game::featured(2) : [];

        view('home', [
            'title' => config('site.name'),
            'games' => $games,
            'featured' => $featured,
            'cats' => Game::categories(),
            'q' => $q,
            'cat' => $cat,
        ]);
    }

    public static function game(): void
    {
        $g = Game::find((int)($_GET['id'] ?? 0));
        if (!$g || $g['status'] !== 'online') {
            self::notFound();
        }
        view('game_detail', [
            'title' => $g['name'] . ' - ' . config('site.name'),
            'g' => $g,
            'related' => Game::related($g),
        ]);
    }

    /** 站内播放页（iframe 模式） */
    public static function play(): void
    {
        $g = Game::find((int)($_GET['id'] ?? 0));
        if (!$g || $g['status'] !== 'online' || $g['entry_url'] === '') {
            self::notFound();
        }
        Game::logPlay((int)$g['id']);
        view('play', ['title' => $g['name'], 'g' => $g, 'entry' => expand_url($g['entry_url'])], '');
    }

    /** 启动跳板：记日志后 302 跳到游戏入口（new_tab 模式） */
    public static function go(): void
    {
        $g = Game::find((int)($_GET['id'] ?? 0));
        if (!$g || $g['status'] !== 'online' || $g['entry_url'] === '') {
            self::notFound();
        }
        Game::logPlay((int)$g['id']);
        redirect(expand_url($g['entry_url']));
    }

    public static function notFound(): void
    {
        http_response_code(404);
        view('404', ['title' => '404 - ' . config('site.name')]);
    }
}
