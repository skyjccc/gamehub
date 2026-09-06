<?php
/** GameHub 唯一入口：路由分发 */
define('GAMEHUB', '1.0.0');
define('GAMEHUB_ROOT', __DIR__);

require __DIR__ . '/app/bootstrap.php';

$route = isset($_GET['r']) ? trim((string)$_GET['r']) : 'home';

try {
    DB::pdo(); // 触发连接 + 自动建库建表，失败时给出安装指引
} catch (Throwable $e) {
    http_response_code(500);
    view('setup_error', ['error' => $e->getMessage()], '');
}

switch (true) {
    case $route === 'home':
    case $route === '':
        Site::home();
        break;
    case $route === 'game':
        Site::game();
        break;
    case $route === 'play':
        Site::play();
        break;
    case $route === 'go':
        Site::go();
        break;
    case strpos($route, 'admin/') === 0:
        Admin::route(substr($route, 6));
        break;
    default:
        Site::notFound();
}
