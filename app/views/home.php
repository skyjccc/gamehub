<?php defined('GAMEHUB') or exit;

/** 游戏卡片的"开始游戏"按钮属性；返回 [href, target, label] */
function launch_btn(array $g): array
{
    $u = Game::launchUrl($g);
    if ($u === '') {
        return ['', '', '去抖音玩'];
    }
    return $g['play_mode'] === 'new_tab'
        ? [$u, '_blank', '开始游戏']
        : [$u, '_self', '开始游戏'];
}
?>

<?php if ($featured): ?>
<section class="hero wrap">
  <div class="hero-grid">
    <?php foreach ($featured as $g): list($href, $target, $label) = launch_btn($g); ?>
    <article class="hero-card" style="background-image:url('<?= e(cover_url($g)) ?>')">
      <div class="hero-mask">
        <div class="hero-badges">
          <?php foreach (Game::platforms($g) as $pf): ?><span class="badge"><?= e($pf) ?></span><?php endforeach; ?>
        </div>
        <h2><?= e($g['name']) ?></h2>
        <p><?= e($g['short_desc']) ?></p>
        <div class="hero-actions">
          <?php if ($href): ?>
            <a class="btn btn-primary" href="<?= e($href) ?>" target="<?= e($target) ?>" rel="<?= $target === '_blank' ? 'noopener' : '' ?>">▶ <?= e($label) ?></a>
          <?php else: ?>
            <a class="btn btn-primary" href="<?= e(url('game', ['id' => $g['id']])) ?>">▶ <?= e($label) ?></a>
          <?php endif; ?>
          <a class="btn btn-ghost" href="<?= e(url('game', ['id' => $g['id']])) ?>">详情</a>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="wrap">
  <div class="list-head">
    <h1>全部游戏 <small><?= count($games) ?></small></h1>
    <nav class="cats">
      <a class="cat <?= $cat === '' ? 'on' : '' ?>" href="<?= e(url('home', $q ? ['q' => $q] : [])) ?>">全部</a>
      <?php foreach ($cats as $c): ?>
      <a class="cat <?= $cat === $c ? 'on' : '' ?>" href="<?= e(url('home', array_filter(['q' => $q, 'cat' => $c]))) ?>"><?= e($c) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>

  <?php if (!$games): ?>
  <div class="empty">
    <p>:( 没有找到匹配的游戏</p>
    <p><a class="btn btn-ghost" href="<?= e(url('home')) ?>">看看全部</a></p>
  </div>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($games as $g): list($href, $target, $label) = launch_btn($g); ?>
    <article class="card">
      <a class="thumb" href="<?= e(url('game', ['id' => $g['id']])) ?>">
        <img src="<?= e(cover_url($g)) ?>" alt="<?= e($g['name']) ?>" loading="lazy">
        <span class="badges">
          <?php foreach (array_slice(Game::platforms($g), 0, 2) as $pf): ?><span><?= e($pf) ?></span><?php endforeach; ?>
        </span>
      </a>
      <div class="card-body">
        <h3><a href="<?= e(url('game', ['id' => $g['id']])) ?>"><?= e($g['name']) ?></a>
          <?php foreach (Game::tags($g) as $t): ?><em class="tag tag-<?= $t === '热门' ? 'hot' : ($t === '新游' ? 'new' : 'rec') ?>"><?= e($t) ?></em><?php endforeach; ?>
        </h3>
        <p><?= e($g['short_desc']) ?></p>
        <div class="card-foot">
          <span class="plays">▶ <?= e(number_format($g['play_count'])) ?></span>
          <?php if ($href): ?>
          <a class="btn btn-play" href="<?= e($href) ?>" target="<?= e($target) ?>" rel="<?= $target === '_blank' ? 'noopener' : '' ?>"><?= e($label) ?></a>
          <?php else: ?>
          <a class="btn btn-play" href="<?= e(url('game', ['id' => $g['id']])) ?>">去抖音玩</a>
          <?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
