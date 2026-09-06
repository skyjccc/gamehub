<?php defined('GAMEHUB') or exit; ?>
<div class="page-head">
  <h1>游戏管理</h1>
  <a class="btn btn-primary" href="<?= e(url('admin/game_form')) ?>">＋ 新增游戏</a>
</div>

<div class="stat-row">
  <div class="stat"><b><?= e($stats['total']) ?></b><span>游戏总数</span></div>
  <div class="stat"><b><?= e($stats['online']) ?></b><span>已上架</span></div>
  <div class="stat"><b><?= e(number_format($stats['plays'])) ?></b><span>累计开玩</span></div>
  <div class="stat"><b><?= e(number_format($stats['plays_today'])) ?></b><span>今日开玩</span></div>
</div>

<table class="tbl">
  <thead>
  <tr>
    <th class="w60">ID</th>
    <th>游戏</th>
    <th class="w90">分类</th>
    <th class="w110">打开方式</th>
    <th>平台标签</th>
    <th class="w80">人气</th>
    <th class="w80">排序</th>
    <th class="w90">状态</th>
    <th class="w150">操作</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($games as $g): ?>
  <tr>
    <td><?= e($g['id']) ?></td>
    <td>
      <div class="g-cell">
        <img class="g-cover" src="<?= e(cover_url($g)) ?>" alt="">
        <div>
          <a href="<?= e(url('admin/game_form', ['id' => $g['id']])) ?>"><b><?= e($g['name']) ?></b></a>
          <small class="g-slug"><?= e($g['slug']) ?><?= $g['featured'] ? ' · ★推荐位' : '' ?></small>
        </div>
      </div>
    </td>
    <td><?= e($g['category']) ?></td>
    <td>
      <?php if ($g['entry_url'] === ''): ?>
        <span class="pill pill-dim">扫码引导</span>
      <?php else: ?>
        <span class="pill <?= $g['play_mode'] === 'iframe' ? 'pill-iframe' : 'pill-tab' ?>"><?= $g['play_mode'] === 'iframe' ? '站内播放' : '新窗口' ?></span>
      <?php endif; ?>
    </td>
    <td class="tags-cell"><?= e(str_replace(',', ' / ', $g['platforms'])) ?: '—' ?></td>
    <td><?= e(number_format($g['play_count'])) ?></td>
    <td><?= e($g['sort_order']) ?></td>
    <td>
      <form method="post" action="<?= e(url('admin/game_toggle')) ?>" class="inline">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= e($g['id']) ?>">
        <button class="pill <?= $g['status'] === 'online' ? 'pill-on' : 'pill-off' ?>" type="submit" title="点击切换上下架">
          <?= $g['status'] === 'online' ? '● 上架中' : '○ 已下架' ?>
        </button>
      </form>
    </td>
    <td>
      <a class="op" href="<?= e(url('admin/game_form', ['id' => $g['id']])) ?>">编辑</a>
      <form method="post" action="<?= e(url('admin/game_delete')) ?>" class="inline js-confirm" data-confirm="确定删除「<?= e($g['name']) ?>」？该操作不可恢复。">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= e($g['id']) ?>">
        <button class="op op-danger" type="submit">删除</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$games): ?>
  <tr><td colspan="9" class="empty-td">还没有游戏，点右上角「新增游戏」上架第一款吧</td></tr>
  <?php endif; ?>
  </tbody>
</table>

<?php if ($logs): ?>
<h2 class="sec-title">最近开玩记录</h2>
<table class="tbl">
  <thead><tr><th class="w60">ID</th><th>游戏</th><th>IP</th><th>时间</th></tr></thead>
  <tbody>
  <?php foreach ($logs as $l): ?>
  <tr>
    <td><?= e($l['game_id']) ?></td>
    <td><?= e($l['game_name'] ?: '(已删除)') ?></td>
    <td><?= e($l['ip']) ?></td>
    <td><?= e($l['created_at']) ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
