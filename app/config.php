<?php
defined('GAMEHUB') or exit('Forbidden');

return [
    // 开发环境 true（显示错误）；上线改 false
    'debug' => true,

    'site' => [
        'name' => 'GameHub · 游戏大厅',
        'per_page' => 24,
    ],

    'db' => [
        'host' => '127.0.0.1',
        // 本机有两个 MySQL：3306 = WSL 里的 MySQL 8.4（poker 游戏的库也在这里）
        //                 3308 = phpStudy 的 MySQL 5.7
        // 首次访问会自动建库建表（库名 gamehub），账号密码不对会依次尝试 try_passwords
        'port' => 3306,
        'name' => 'gamehub',
        'user' => 'root',
        // phpStudy 默认密码依次尝试；改成你的专用账号更安全
        'try_passwords' => ['root', ''],
    ],

    // 首次自动初始化时创建的管理员（登录后请到管理端改密码）
    'admin' => [
        'initial_user' => 'admin',
        'initial_pass' => 'gamehub123',
    ],

    // 管理端表单可选项
    'categories' => ['棋牌', '休闲', '益智', '竞技', '小游戏', '其他'],
    'platform_options' => ['H5网页', '抖音小游戏', '微信小游戏', '多人联机', '单机离线', '手机竖屏'],
    'tag_options' => ['热门', '新游', '推荐'],

    // 小程序平台 => 展示名
    'miniapp_platforms' => ['' => '无', 'douyin' => '抖音小游戏', 'weixin' => '微信小游戏'],
];
