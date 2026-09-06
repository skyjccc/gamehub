<?php defined('GAMEHUB') or exit; ?>
<div class="page-head"><h1>修改密码</h1></div>
<form class="gform narrow" method="post" action="<?= e(url('admin/password_save')) ?>">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <fieldset>
    <label class="row">旧密码<input type="password" name="password_old" required></label>
    <label class="row">新密码（至少 6 位）<input type="password" name="password_new" required minlength="6"></label>
    <button class="btn btn-primary" type="submit">保存</button>
  </fieldset>
</form>
