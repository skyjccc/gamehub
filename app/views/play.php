<?php defined('GAMEHUB') or exit; ?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - 正在玩</title>
<style>
  html,body{margin:0;height:100%;background:#05070d;display:flex;flex-direction:column;overflow:hidden}
  .play-bar{display:flex;align-items:center;gap:14px;padding:0 14px;height:50px;flex:0 0 50px;
    background:#0b0f1a;color:#cbd5e1;border-bottom:1px solid #1e2638}
  .play-bar a{color:#818cf8;text-decoration:none;font-size:14px}
  .play-bar .name{font-weight:700;font-size:15px;color:#f1f5f9;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .play-bar button{background:#1e2638;border:1px solid #2c3653;color:#cbd5e1;border-radius:8px;padding:6px 14px;cursor:pointer;font-size:13px}
  .play-bar button:hover{background:#2c3653}
  iframe{flex:1;width:100%;border:0;background:#000}
</style>
</head>
<body>
<div class="play-bar">
  <a href="<?= e(url('home')) ?>">‹ 返回大厅</a>
  <span class="name"><?= e($g['name']) ?></span>
  <button type="button" id="btn-fs">⛶ 全屏</button>
</div>
<iframe id="play-frame" src="<?= e($g['entry_url']) ?>" allow="fullscreen; autoplay; gamepad" allowfullscreen></iframe>
<script>
document.getElementById('btn-fs').addEventListener('click', function () {
  var f = document.getElementById('play-frame');
  if (document.fullscreenElement) { document.exitFullscreen(); }
  else if (f.requestFullscreen) { f.requestFullscreen(); }
});
</script>
</body>
</html>
