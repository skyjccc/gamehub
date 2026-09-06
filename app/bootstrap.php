<?php
defined('GAMEHUB') or exit('Forbidden');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Shanghai');

$GLOBALS['CONFIG'] = require __DIR__ . '/config.php';

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
}

require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/Game.php';
require __DIR__ . '/controllers/Site.php';
require __DIR__ . '/controllers/Admin.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* ---------------- 助手函数 ---------------- */

function config(string $key, $default = null)
{
    $val = $GLOBALS['CONFIG'];
    foreach (explode('.', $key) as $p) {
        if (!is_array($val) || !array_key_exists($p, $val)) {
            return $default;
        }
        $val = $val[$p];
    }
    return $val;
}

function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** 生成站内链接，如 index.php?r=game&id=1 */
function url(string $route, array $params = []): string
{
    $qs = http_build_query(array_merge(['r' => $route], $params));
    return 'index.php?' . str_replace('%2F', '/', $qs);
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, $default = '')
{
    if (!isset($_POST[$key])) return $default;
    $v = $_POST[$key];
    return is_array($v) ? $v : trim((string)$v);
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/** 游戏封面完整地址；无封面时用占位图 */
function cover_url(array $g): string
{
    return $g['cover'] !== '' ? 'uploads/' . $g['cover'] : 'assets/img/cover-default.svg';
}

/**
 * 展开入口地址里的 {host} 占位符：替换为当前访问者使用的主机名（IP/域名:端口）。
 * 用于局域网联机游戏 —— 本机访问解析为 127.0.0.1，手机访问解析为局域网 IP。
 */
function expand_url(string $url): string
{
    // HTTP_HOST 可能带端口（如 192.168.1.10:8890），只取主机名部分
    $host = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?: '127.0.0.1'), PHP_URL_HOST) ?: '127.0.0.1';
    return str_replace('{host}', $host, $url);
}

function flash(?string $msg = null, string $type = 'success')
{
    if ($msg === null) {
        if (empty($_SESSION['_flash'])) return null;
        $f = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $f;
    }
    $_SESSION['_flash'] = ['msg' => $msg, 'type' => $type];
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}

function csrf_valid($t): bool
{
    return is_string($t) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $t);
}

/**
 * 渲染视图。$layout 为 '' 时不套布局（独立页面）。
 */
function view(string $template, array $vars = [], string $layout = 'layout'): void
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require GAMEHUB_ROOT . '/app/views/' . $template . '.php';
    $content = ob_get_clean();
    if ($layout !== '') {
        require GAMEHUB_ROOT . '/app/views/' . $layout . '.php';
    } else {
        echo $content;
    }
    exit;
}
