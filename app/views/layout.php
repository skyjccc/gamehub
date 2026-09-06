<?php defined('GAMEHUB') or exit; ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? config('site.name')) ?></title>
<meta name="theme-color" content="#0b0f1a">
<link rel="manifest" href="manifest.webmanifest">
<link rel="icon" href="assets/img/icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="GameHub">
<link rel="apple-touch-icon" href="assets/img/icon-180.png">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="logo" href="<?= e(url('home')) ?>"><span class="logo-icon">🎮</span> GameHub <em>游戏大厅</em></a>
    <form class="search" method="get" action="index.php">
      <input type="hidden" name="r" value="home">
      <input type="search" name="q" value="<?= e($q ?? '') ?>" placeholder="搜索游戏…" aria-label="搜索游戏">
      <button type="submit">搜索</button>
    </form>
  </div>
</header>

<main><?= $content ?></main>

<footer class="site-footer">
  <div class="wrap">
    <span>© <?= date('Y') ?> GameHub · 我的游戏们</span>
    <a href="<?= e(url('admin/login')) ?>">管理端</a>
  </div>
</footer>
<script src="assets/js/main.js"></script>
</body>
</html>
