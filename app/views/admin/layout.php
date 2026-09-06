<?php defined('GAMEHUB') or exit;
$fl = flash();
$cur = (string)($_GET['r'] ?? '');
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? '管理端') ?> - GameHub Admin</title>
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin">
<aside class="side">
  <div class="side-logo">🎮 GameHub <em>管理端</em></div>
  <nav>
    <?php if (Auth::check()): ?>
    <a class="<?= $cur === 'admin/dashboard' ? 'on' : '' ?>" href="<?= e(url('admin/dashboard')) ?>">▤ 游戏管理</a>
    <a class="<?= $cur === 'admin/game_form' ? 'on' : '' ?>" href="<?= e(url('admin/game_form')) ?>">＋ 新增游戏</a>
    <a class="<?= $cur === 'admin/password' ? 'on' : '' ?>" href="<?= e(url('admin/password')) ?>">🔑 修改密码</a>
    <hr>
    <a href="<?= e(url('home')) ?>" target="_blank">↗ 查看大厅</a>
    <a href="<?= e(url('admin/logout')) ?>">⏻ 退出登录（<?= e(Auth::name()) ?>）</a>
    <?php endif; ?>
  </nav>
</aside>
<main class="admin-main">
  <?php if ($fl): ?><div class="flash flash-<?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
  <?= $content ?>
</main>
<script src="assets/js/main.js"></script>
</body>
</html>
