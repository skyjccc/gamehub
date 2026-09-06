<?php defined('GAMEHUB') or exit; ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>无法连接数据库 - GameHub</title>
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0b0f1a;color:#e2e8f0;font-family:"Microsoft YaHei",system-ui,sans-serif}
  .box{max-width:560px;padding:40px;background:#151b2c;border:1px solid #2c3653;border-radius:16px;line-height:1.8}
  h1{margin-top:0;font-size:20px;color:#f87171}
  code{background:#0b0f1a;padding:2px 8px;border-radius:6px;font-size:13px;color:#fbbf24}
  li{margin:4px 0}
</style>
</head>
<body>
<div class="box">
  <h1>⚠ 数据库连不上</h1>
  <p><?= e($error) ?></p>
  <ul>
    <li>打开 phpStudy 面板，启动 <b>MySQL</b>（首次访问会自动建库建表）</li>
    <li>数据库账号密码在 <code>app/config.php</code> 里修改</li>
  </ul>
</div>
</body>
</html>
