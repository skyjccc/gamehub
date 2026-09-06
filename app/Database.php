<?php
defined('GAMEHUB') or exit('Forbidden');

/**
 * PDO 封装 + 首次访问自动建库建表 + 种子数据。
 */
class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        self::connectAndInstall();
        return self::$pdo;
    }

    private static function connectAndInstall(): void
    {
        $cfg = config('db');
        $lastErr = null;

        foreach ($cfg['try_passwords'] as $pass) {
            try {
                $pdo = new PDO(
                    sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $cfg['host'], $cfg['port']),
                    $cfg['user'],
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                $pdo->exec(
                    sprintf(
                        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                        str_replace('`', '', $cfg['name'])
                    )
                );
                $pdo->exec('USE `' . str_replace('`', '', $cfg['name']) . '`');
                self::$pdo = $pdo;
                break;
            } catch (PDOException $e) {
                $lastErr = $e;
            }
        }

        if (!self::$pdo instanceof PDO) {
            throw new RuntimeException(
                '无法连接 MySQL（' . $cfg['host'] . ':' . $cfg['port'] . '）。'
                . '请确认 phpStudy 已启动 MySQL，或在 app/config.php 中修改数据库账号密码。'
                . (config('debug') && $lastErr ? ' 详情：' . $lastErr->getMessage() : '')
            );
        }

        self::ensureSchema();
    }

    private static function ensureSchema(): void
    {
        $engine = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        self::$pdo->exec('CREATE TABLE IF NOT EXISTS admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login_at DATETIME NULL
        )' . $engine);

        self::$pdo->exec('CREATE TABLE IF NOT EXISTS games (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(20) NOT NULL DEFAULT "其他",
            play_mode ENUM("new_tab","iframe") NOT NULL DEFAULT "new_tab",
            entry_url VARCHAR(500) NOT NULL DEFAULT "",
            platforms VARCHAR(200) NOT NULL DEFAULT "",
            miniapp_platform VARCHAR(20) NOT NULL DEFAULT "",
            miniapp_appid VARCHAR(64) NOT NULL DEFAULT "",
            miniapp_qr VARCHAR(255) NOT NULL DEFAULT "",
            cover VARCHAR(255) NOT NULL DEFAULT "",
            short_desc VARCHAR(200) NOT NULL DEFAULT "",
            description TEXT NULL,
            tags VARCHAR(100) NOT NULL DEFAULT "",
            status ENUM("online","offline") NOT NULL DEFAULT "offline",
            featured TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            play_count INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )' . $engine);

        self::$pdo->exec('CREATE TABLE IF NOT EXISTS play_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            game_id INT UNSIGNED NOT NULL,
            ip VARCHAR(45) NOT NULL DEFAULT "",
            user_agent VARCHAR(255) NOT NULL DEFAULT "",
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_game (game_id),
            KEY idx_time (created_at)
        )' . $engine);

        // 种子数据（仅空表时写入）
        if ((int)self::one('SELECT COUNT(*) AS c FROM admins')['c'] === 0) {
            self::$pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
                ->execute([config('admin.initial_user'), password_hash(config('admin.initial_pass'), PASSWORD_DEFAULT)]);
        }
        if ((int)self::one('SELECT COUNT(*) AS c FROM games')['c'] === 0) {
            self::seedGames();
        }
    }

    private static function seedGames(): void
    {
        $rows = [
            [
                'name' => '德州扑克',
                'slug' => 'texas-holdem',
                'category' => '棋牌',
                'play_mode' => 'new_tab',
                'entry_url' => '/poker/',
                'platforms' => 'H5网页,多人联机',
                'miniapp_platform' => '',
                'miniapp_appid' => '',
                'miniapp_qr' => '',
                'cover' => 'covers/poker.svg',
                'short_desc' => '单机挑战三位 AI 或联机多人同桌，实时胜率引擎 + 底池赔率提示',
                'description' => "经典德州扑克，单人与联机双模式。\n\n【单人模式】与三位性格迥异的 AI 同桌（激进老王 / 紧弱莎拉 / 平衡诈唬阿豪），内置蒙特卡洛胜率引擎与底池赔率提示；边池、盲注升级、全下结算一应俱全。\n\n【联机模式】注册账号后创建 / 加入房间，最多 4 人同桌（空位由 AI 补齐），每局筹码自动结算回账号。\n\n操作：弃牌 / 过牌 / 跟注 / 加注 / 全下，支持快捷下注、滑杆与快捷键；单人模式按 P 可偷看所有底牌的真实胜率。",
                'tags' => '热门',
                'status' => 'online',
                'featured' => 1,
                'sort_order' => 100,
            ],
            [
                'name' => '叠了个叠',
                'slug' => 'dieledie',
                'category' => '休闲',
                'play_mode' => 'iframe',
                'entry_url' => '/game/',
                'platforms' => 'H5网页,抖音小游戏,手机竖屏',
                'miniapp_platform' => 'douyin',
                'miniapp_appid' => '',
                'miniapp_qr' => '',
                'cover' => 'covers/dieledie.svg',
                'short_desc' => '抖音爆款叠牌三消：点牌入槽、三连消除，无限关卡 + 每日挑战',
                'description' => "抖音爆款「叠牌三消」玩法：点击桌面上没有被压住的牌移入底部 7 格槽位，集齐 3 张相同图案即消除；槽位塞满还没消完就失败，可看广告救回 3 张牌。\n\n· 无限关卡，层数与牌数逐关爬坡，每一关由逆向可解算法生成 —— 保证有解，但极其考验取舍\n· 每日挑战：按日期种子生成，当天全网玩家同一布局，连胜打卡\n· 道具：撤销 / 移出 / 洗牌；糖果 / 水果 / 麻将三套皮肤一键切换\n· 双端同玩：网页版即点即玩，抖音小游戏版可在抖音里扫码或搜索进入",
                'tags' => '新游',
                'status' => 'online',
                'featured' => 1,
                'sort_order' => 90,
            ],
            [
                'name' => '拍档大冒险',
                'slug' => 'patener',
                'category' => '休闲',
                'play_mode' => 'new_tab',
                'entry_url' => 'http://{host}:8642/',
                'platforms' => 'H5网页,多人联机',
                'miniapp_platform' => '',
                'miniapp_appid' => '',
                'miniapp_qr' => '',
                'cover' => 'covers/patener.svg',
                'short_desc' => '双人合作闯关：一台建房一台加入，配合机关与接力一起到终点，还有岩浆攀爬无尽模式',
                'description' => "灵感来自《双人成行》《Pico Park》的双人合作闯关手游。\n\n【双机联机】两台设备连同一 Wi-Fi，一台创建房间（1P 阿橙·冲刺），一台加入（2P 阿蓝·二段跳），30Hz 同步输入，局域网手感零延迟。\n\n12 个关卡（机关板/箱子/拉杆/跷跷板/冰面/火焰…）+ 岩浆攀爬无尽模式，过关评级、水晶收集与装扮解锁。",
                'tags' => '新游',
                'status' => 'online',
                'featured' => 0,
                'sort_order' => 80,
            ],
            [
                'name' => '双影同行',
                'slug' => 'shadow-duo',
                'category' => '休闲',
                'play_mode' => 'new_tab',
                'entry_url' => 'http://{host}:3000/',
                'platforms' => 'H5网页,多人联机',
                'miniapp_platform' => '',
                'miniapp_appid' => '',
                'miniapp_qr' => '',
                'cover' => 'covers/shadowduo.svg',
                'short_desc' => '暗黑电影感双人合作解谜：各提一盏灯互相照亮，踩机关、推箱子，一起走进光门',
                'description' => "LIMBO / INSIDE 式电影感双人合作解谜闯关。\n\n两名玩家各提一盏灯（琥珀·青蓝）在黑暗中互相照应：压力板开门、拉杆锁定、推箱垫脚、双闸摆渡……两人都进入尽头的光门才算过关。\n\n4 大章节 + 灵珠收集 + 检查点重生；局域网 20Hz 同步，虚拟摇杆 + 键盘双支持，零依赖零素材。",
                'tags' => '新游',
                'status' => 'online',
                'featured' => 0,
                'sort_order' => 75,
            ],
            [
                'name' => '内网棋牌室',
                'slug' => 'qipai',
                'category' => '棋牌',
                'play_mode' => 'new_tab',
                'entry_url' => 'http://{host}:3456/',
                'platforms' => 'H5网页,多人联机',
                'miniapp_platform' => '',
                'miniapp_appid' => '',
                'miniapp_qr' => '',
                'cover' => 'covers/qipai.svg',
                'short_desc' => '局域网真人对战：中国象棋 · 五子棋 · 国际象棋，服务端校验走法，支持悔棋/求和/观战',
                'description' => "同一局域网内真人对战的棋牌室：中国象棋、五子棋、国际象棋。\n\n· 三种棋规则全部在服务端校验（蹩马腿/塞象眼/将帅对脸、王车易位/吃过路兵等），无法作弊\n· 创建房间 + 复制邀请链接 / 房间号加入，人满自动观战\n· 走子提示、将军警示、悔棋（对方确认+倒计时）、求和、认输、再战换先\n· 房内聊天、棋谱记录、掉线 30 秒重连恢复对局",
                'tags' => '新游',
                'status' => 'online',
                'featured' => 1,
                'sort_order' => 70,
            ],
        ];

        $sql = 'INSERT INTO games (name, slug, category, play_mode, entry_url, platforms, miniapp_platform, miniapp_appid, miniapp_qr, cover, short_desc, description, tags, status, featured, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $st = self::$pdo->prepare($sql);
        foreach ($rows as $r) {
            $st->execute(array_values($r));
        }
    }

    /* ---------------- 基础查询 ---------------- */

    public static function q(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::q($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::q($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function scalar(string $sql, array $params = [])
    {
        return self::q($sql, $params)->fetchColumn();
    }

    public static function lastId(): int
    {
        return (int)self::pdo()->lastInsertId();
    }
}
