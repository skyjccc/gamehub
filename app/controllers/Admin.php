<?php
defined('GAMEHUB') or exit('Forbidden');

/** 管理端控制器 */
class Admin
{
    public static function route(string $action): void
    {
        $public = ['login' => true];
        if (empty($public[$action]) && !Auth::check()) {
            redirect(url('admin/login'));
        }
        switch ($action) {
            case 'login':
                self::login();
                break;
            case 'logout':
                Auth::logout();
                redirect(url('admin/login'));
                break;
            case '':
            case 'dashboard':
                self::dashboard();
                break;
            case 'game_form':
                self::form();
                break;
            case 'game_save':
                self::save();
                break;
            case 'game_toggle':
                self::toggle();
                break;
            case 'game_delete':
                self::delete();
                break;
            case 'password':
                self::password();
                break;
            case 'password_save':
                self::savePassword();
                break;
            default:
                Site::notFound();
        }
    }

    private static function login(): void
    {
        if (Auth::check()) {
            redirect(url('admin/dashboard'));
        }
        if (is_post()) {
            if (!csrf_valid(post('_csrf'))) {
                flash('页面已过期，请重试', 'error');
                redirect(url('admin/login'));
            }
            if (Auth::attempt(post('username'), post('password'))) {
                flash('欢迎回来，' . Auth::name());
                redirect(url('admin/dashboard'));
            }
            flash('用户名或密码错误', 'error');
        }
        view('admin/login', ['title' => '管理端登录'], 'admin/layout');
    }

    private static function dashboard(): void
    {
        view('admin/dashboard', [
            'title' => '游戏管理',
            'games' => Game::adminList(),
            'stats' => Game::adminStats(),
            'logs' => Game::recentLogs(10),
        ], 'admin/layout');
    }

    private static function form(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $g = $id ? Game::find($id) : null;
        if ($id && !$g) {
            Site::notFound();
        }
        view('admin/game_form', [
            'title' => $g ? '编辑游戏：' . $g['name'] : '新增游戏',
            'g' => $g ?: self::blankGame(),
            'cats' => config('categories'),
            'platformOpts' => config('platform_options'),
            'tagOpts' => config('tag_options'),
            'miniappOpts' => config('miniapp_platforms'),
        ], 'admin/layout');
    }

    private static function blankGame(): array
    {
        return [
            'id' => 0, 'name' => '', 'slug' => '', 'category' => '休闲', 'play_mode' => 'iframe',
            'entry_url' => '', 'platforms' => '', 'miniapp_platform' => '', 'miniapp_appid' => '',
            'miniapp_qr' => '', 'cover' => '', 'short_desc' => '', 'description' => '',
            'tags' => '', 'status' => 'offline', 'featured' => 0, 'sort_order' => 0,
        ];
    }

    private static function save(): void
    {
        if (!is_post() || !csrf_valid(post('_csrf'))) {
            flash('页面已过期，请重试', 'error');
            redirect(url('admin/dashboard'));
        }
        $id = (int)post('id') ?: null;

        $name = post('name');
        if ($name === '') {
            flash('请填写游戏名称', 'error');
            redirect(url('admin/game_form', $id ? ['id' => $id] : []));
        }

        // 上传封面 / 小程序二维码（未重新上传则沿用隐藏域里的旧值）
        $cover = self::upload('cover', 'covers', post('cover_current'));
        $qr = self::upload('miniapp_qr', 'qr', post('qr_current'));

        $platforms = array_values(array_intersect((array)post('platforms', []), config('platform_options')));
        $tags = array_values(array_intersect((array)post('tags', []), config('tag_options')));

        $data = [
            'name' => $name,
            'slug' => post('slug'),
            'category' => post('category', '其他'),
            'play_mode' => post('play_mode') === 'new_tab' ? 'new_tab' : 'iframe',
            'entry_url' => post('entry_url'),
            'platforms' => implode(',', $platforms),
            'miniapp_platform' => array_key_exists(post('miniapp_platform'), config('miniapp_platforms')) ? post('miniapp_platform') : '',
            'miniapp_appid' => post('miniapp_appid'),
            'miniapp_qr' => $qr,
            'cover' => $cover,
            'short_desc' => mb_substr(post('short_desc'), 0, 200),
            'description' => post('description'),
            'tags' => implode(',', $tags),
            'status' => post('status') === 'online' ? 'online' : 'offline',
            'featured' => post('featured') ? 1 : 0,
            'sort_order' => (int)post('sort_order'),
        ];

        $newId = Game::save($data, $id);
        flash(($id ? '已保存' : '已上架') . '游戏「' . $name . '」');
        redirect(url('admin/game_form', ['id' => $newId]));
    }

    /** 校验并保存上传图片；未上传时返回 $fallback（当前值） */
    private static function upload(string $field, string $subdir, string $fallback = ''): string
    {
        $f = $_FILES[$field] ?? null;
        if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE || ($f['error'] === UPLOAD_ERR_OK && $f['name'] === '')) {
            return $fallback;
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            flash('图片上传失败（错误码 ' . $f['error'] . '）', 'error');
            return $fallback;
        }
        if ($f['size'] > 5 * 1024 * 1024) {
            flash('图片不能超过 5MB', 'error');
            return $fallback;
        }
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allow, true) || !@getimagesize($f['tmp_name'])) {
            flash('只支持 jpg / png / webp / gif 图片', 'error');
            return $fallback;
        }
        $dir = GAMEHUB_ROOT . '/uploads/' . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
            flash('图片保存失败，请检查 uploads 目录权限', 'error');
            return $fallback;
        }
        return $subdir . '/' . $name;
    }

    private static function toggle(): void
    {
        if (is_post() && csrf_valid(post('_csrf'))) {
            Game::toggle((int)post('id'));
        }
        redirect(url('admin/dashboard'));
    }

    private static function delete(): void
    {
        if (is_post() && csrf_valid(post('_csrf'))) {
            Game::delete((int)post('id'));
            flash('游戏已删除');
        }
        redirect(url('admin/dashboard'));
    }

    private static function password(): void
    {
        view('admin/password', ['title' => '修改密码'], 'admin/layout');
    }

    private static function savePassword(): void
    {
        if (!is_post() || !csrf_valid(post('_csrf'))) {
            redirect(url('admin/password'));
        }
        $new = post('password_new');
        if (mb_strlen($new) < 6) {
            flash('新密码至少 6 位', 'error');
            redirect(url('admin/password'));
        }
        if (!Auth::changePassword(post('password_old'), $new)) {
            flash('旧密码不正确', 'error');
            redirect(url('admin/password'));
        }
        flash('密码已修改');
        redirect(url('admin/dashboard'));
    }
}
