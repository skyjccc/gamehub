<?php defined('GAMEHUB') or exit;
$g = $g;
$selectedPlatforms = array_map('trim', explode(',', (string)$g['platforms']));
$selectedTags = array_map('trim', explode(',', (string)$g['tags']));
?>
<form class="gform" method="post" action="<?= e(url('admin/game_save')) ?>" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="id" value="<?= e($g['id']) ?>">

  <div class="page-head">
    <h1><?= $g['id'] ? '编辑游戏：' . e($g['name']) : '新增游戏' ?></h1>
    <a class="btn btn-ghost" href="<?= e(url('admin/dashboard')) ?>">‹ 返回列表</a>
  </div>

  <div class="gform-grid">
    <div class="gform-col">
      <fieldset>
        <legend>基本信息</legend>
        <label class="row">游戏名称 *
          <input type="text" name="name" required maxlength="100" value="<?= e($g['name']) ?>" placeholder="例如：德州扑克">
        </label>
        <label class="row">URL标识 slug（留空自动生成，字母/数字/连字符）
          <input type="text" name="slug" maxlength="100" value="<?= e($g['slug']) ?>" placeholder="texas-holdem">
        </label>
        <label class="row">分类
          <select name="category">
            <?php foreach ($cats as $c): ?>
            <option value="<?= e($c) ?>" <?= $g['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="row">一句话简介（列表卡片上显示）
          <input type="text" name="short_desc" maxlength="200" value="<?= e($g['short_desc']) ?>" placeholder="用一句话介绍这款游戏">
        </label>
        <label class="row">详细介绍
          <textarea name="description" rows="8" placeholder="玩法、特色、操作说明……"><?= e($g['description']) ?></textarea>
        </label>
        <label class="row">封面图（jpg/png/webp/gif，≤5MB）
          <input type="file" name="cover" accept="image/*" data-preview="#cover-preview">
          <input type="hidden" name="cover_current" value="<?= e($g['cover']) ?>">
          <span class="preview"><img id="cover-preview" src="<?= $g['cover'] ? 'uploads/' . e($g['cover']) : '' ?>" alt="" <?= $g['cover'] ? '' : 'hidden' ?>></span>
        </label>
      </fieldset>

      <fieldset>
        <legend>启动方式（玩家点“开始游戏”后怎么打开）</legend>
        <label class="radio"><input type="radio" name="play_mode" value="new_tab" <?= $g['play_mode'] === 'new_tab' ? 'checked' : '' ?>>
          <b>新窗口直开</b> —— 网游推荐，给游戏一个完整浏览器窗口
        </label>
        <label class="radio"><input type="radio" name="play_mode" value="iframe" <?= $g['play_mode'] === 'iframe' ? 'checked' : '' ?>>
          <b>站内播放器</b> —— H5 休闲游戏推荐，进入 GameHub 全屏播放页
        </label>
        <label class="row">游戏入口地址 entry_url
          <input type="text" name="entry_url" maxlength="500" value="<?= e($g['entry_url']) ?>" placeholder="/poker/ 或 https://你的域名/">
        </label>
        <p class="tip">站内部署的游戏填相对路径（如 <code>/poker/</code>，指向 WWW 下的目录）；部署在别处的填完整 URL，局域网联机游戏用 <code>{host}</code> 占位符（如 <code>http://{host}:8642/</code>，手机访问时自动变成局域网 IP）。<b>留空则玩家端不显示“开始游戏”，只显示小程序引导。</b></p>
      </fieldset>
    </div>

    <div class="gform-col">
      <fieldset>
        <legend>展示与排序</legend>
        <div class="row">平台标签
          <div class="checks">
            <?php foreach ($platformOpts as $pf): ?>
            <label><input type="checkbox" name="platforms[]" value="<?= e($pf) ?>" <?= in_array($pf, $selectedPlatforms, true) ? 'checked' : '' ?>> <?= e($pf) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="row">角标标签
          <div class="checks">
            <?php foreach ($tagOpts as $t): ?>
            <label><input type="checkbox" name="tags[]" value="<?= e($t) ?>" <?= in_array($t, $selectedTags, true) ? 'checked' : '' ?>> <?= e($t) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <label class="row">排序权重（越大越靠前）
          <input type="number" name="sort_order" value="<?= e($g['sort_order']) ?>">
        </label>
        <label class="radio"><input type="checkbox" name="featured" value="1" <?= $g['featured'] ? 'checked' : '' ?>> <b>首页推荐位</b>（大厅顶部大图展示）</label>
        <div class="row">状态
          <div class="checks">
            <label><input type="radio" name="status" value="online" <?= $g['status'] === 'online' ? 'checked' : '' ?>> 上架</label>
            <label><input type="radio" name="status" value="offline" <?= $g['status'] === 'offline' ? 'checked' : '' ?>> 下架</label>
          </div>
        </div>
      </fieldset>

      <fieldset>
        <legend>小程序信息（可留空）</legend>
        <label class="row">小程序平台
          <select name="miniapp_platform">
            <?php foreach ($miniappOpts as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= (string)$g['miniapp_platform'] === (string)$k ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="row">AppID
          <input type="text" name="miniapp_appid" maxlength="64" value="<?= e($g['miniapp_appid']) ?>" placeholder="抖音开放平台的小程序 AppID">
        </label>
        <label class="row">小程序码（开放平台可下载，玩家扫码直达）
          <input type="file" name="miniapp_qr" accept="image/*" data-preview="#qr-preview">
          <input type="hidden" name="qr_current" value="<?= e($g['miniapp_qr']) ?>">
          <span class="preview"><img id="qr-preview" src="<?= $g['miniapp_qr'] ? 'uploads/' . e($g['miniapp_qr']) : '' ?>" alt="" <?= $g['miniapp_qr'] ? '' : 'hidden' ?>></span>
        </label>
      </fieldset>

      <button class="btn btn-primary btn-lg" type="submit"><?= $g['id'] ? '保存修改' : '创建游戏' ?></button>
      <p class="tip">保存后到大厅页面确认展示效果；上下架可随时在列表里一键切换。</p>
    </div>
  </div>
</form>
