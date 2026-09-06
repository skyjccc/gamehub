<?php defined('GAMEHUB') or exit; ?>
<div class="login-wrap">
  <form class="login-box" method="post" action="<?= e(url('admin/login')) ?>">
    <h1>🎮 GameHub 管理端</h1>
    <p class="sub">登录后管理你的游戏</p>
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <label>用户名<input type="text" name="username" required autofocus></label>
    <label>密码<input type="password" name="password" required></label>
    <button class="btn btn-primary" type="submit">登 录</button>
  </form>
</div>
