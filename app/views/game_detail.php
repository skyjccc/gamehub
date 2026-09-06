<?php defined('GAMEHUB') or exit;
$launch = Game::launchUrl($g);
$miniappName = config('miniapp_platforms.' . $g['miniapp_platform'], '');
?>
<section class="wrap detail">
  <a class="backlink" href="<?= e(url('home')) ?>">‹ 返回大厅</a>

  <div class="detail-grid">
    <div class="detail-cover" style="background-image:url('<?= e(cover_url($g)) ?>')"></div>

    <div class="detail-info">
      <h1><?= e($g['name']) ?></h1>
      <div class="detail-badges">
        <?php foreach (Game::platforms($g) as $pf): ?><span class="badge"><?= e($pf) ?></span><?php endforeach; ?>
        <?php foreach (Game::tags($g) as $t): ?><span class="tag tag-<?= $t === '热门' ? 'hot' : ($t === '新游' ? 'new' : 'rec') ?>"><?= e($t) ?></span><?php endforeach; ?>
      </div>
      <p class="detail-desc-short"><?= e($g['short_desc']) ?></p>

      <dl class="detail-meta">
        <div><dt>分类</dt><dd><?= e($g['category']) ?></dd></div>
        <div><dt>开玩次数</dt><dd><?= e(number_format($g['play_count'])) ?></dd></div>
        <div><dt>更新时间</dt><dd><?= e($g['updated_at']) ?></dd></div>
      </dl>

      <?php if ($launch !== ''): ?>
      <a class="btn btn-primary btn-lg" href="<?= e($launch) ?>"
         target="<?= $g['play_mode'] === 'new_tab' ? '_blank' : '_self' ?>"
         rel="<?= $g['play_mode'] === 'new_tab' ? 'noopener' : '' ?>"
         <?= $g['play_mode'] === 'new_tab' ? 'data-play="' . e(url('play', ['id' => $g['id']])) . '"' : '' ?>>▶ 开始游戏</a>
      <?php if ($g['play_mode'] === 'new_tab'): ?>
      <p class="hint">将在新窗口打开游戏</p>
      <?php endif; ?>
      <?php elseif ($miniappName): ?>
      <p class="hint hint-lg">本游戏为 <?= e($miniappName) ?>，请在手机端扫码或搜索进入 ↓</p>
      <?php else: ?>
      <p class="hint hint-lg">暂未开放，敬请期待</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($miniappName): ?>
  <div class="miniapp-box">
    <div class="miniapp-head">
      <h2> <?= $miniappName === '抖音小游戏' ? '🎵 ' : '' ?><?= e($miniappName) ?></h2>
      <p>打开<?= $miniappName === '抖音小游戏' ? '抖音' : '微信' ?>APP → 扫一扫下方二维码，或搜索游戏名即可进入</p>
    </div>
    <div class="miniapp-body">
      <div class="qr">
        <?php if ($g['miniapp_qr']): ?>
          <img src="uploads/<?= e($g['miniapp_qr']) ?>" alt="小程序二维码">
        <?php else: ?>
          <div class="qr-placeholder">小程序码<br>待上传<br><small>（管理端可上传）</small></div>
        <?php endif; ?>
      </div>
      <ul>
        <li><b>游戏名：</b><?= e($g['name']) ?>（在<?= $miniappName === '抖音小游戏' ? '抖音' : '微信' ?>里搜索同名小游戏）</li>
        <?php if ($g['miniapp_appid']): ?>
        <li><b>AppID：</b><code id="miniapp-appid"><?= e($g['miniapp_appid']) ?></code>
          <button class="btn btn-mini" type="button" data-copy="#miniapp-appid">复制</button></li>
        <?php endif; ?>
        <li><b>网页版：</b><?= $launch !== '' ? '本页即可直接试玩 ↑' : '暂无网页版' ?></li>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <div class="detail-article">
    <h2>游戏介绍</h2>
    <div class="article"><?= nl2br(e($g['description'] ?: $g['short_desc'])) ?></div>
  </div>

  <?php if ($related): ?>
  <div class="detail-related">
    <h2>同类游戏</h2>
    <div class="grid">
      <?php foreach ($related as $r): ?>
      <article class="card">
        <a class="thumb" href="<?= e(url('game', ['id' => $r['id']])) ?>">
          <img src="<?= e(cover_url($r)) ?>" alt="<?= e($r['name']) ?>" loading="lazy">
        </a>
        <div class="card-body">
          <h3><a href="<?= e(url('game', ['id' => $r['id']])) ?>"><?= e($r['name']) ?></a></h3>
          <p><?= e($r['short_desc']) ?></p>
          <div class="card-foot">
            <span class="plays">▶ <?= e(number_format($r['play_count'])) ?></span>
            <span class="badge"><?= e($r['category']) ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>
