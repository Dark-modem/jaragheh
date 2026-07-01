<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'buy';
$pageTitle = 'خرید بسته';
$packages = db()->query('SELECT * FROM packages WHERE active = 1 ORDER BY sort_order, id')->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<div class="page-title">
  <h2>خرید بسته</h2>
  <p>بسته‌ی مورد نظر را انتخاب کنید و روش پرداخت را مشخص کنید.</p>
</div>

<?php if (!$packages): ?>
  <div class="card empty"><?= icon('box') ?><h3>فعلاً بسته‌ای موجود نیست</h3><p>به‌زودی بسته‌های جدید اضافه می‌شوند.</p></div>
<?php else: ?>
  <div class="pkg-grid">
    <?php foreach ($packages as $i => $p):
      $hasOff = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
      $final = $hasOff ? $p['discount_price'] : $p['price'];
      $off = $hasOff ? round((1 - $p['discount_price'] / $p['price']) * 100) : 0;
      $featured = $i === 1;
    ?>
      <div class="card pkg <?= $featured ? 'featured' : '' ?>">
        <?php if ($featured): ?><span class="ribbon">پرفروش</span><?php endif; ?>
        <h3><?= e($p['title']) ?></h3>
        <div class="desc"><?= e($p['description']) ?></div>
        <div class="price">
          <span class="now"><?= toman($final) ?></span>
          <?php if ($hasOff): ?><span class="old"><?= toman($p['price']) ?></span><?php endif; ?>
        </div>
        <?php if ($hasOff): ?><span class="off"><?= fa_num($off) ?>٪ تخفیف</span><?php endif; ?>
        <ul>
          <li><?= icon('database') ?> حجم <?= fa_num($p['volume_gb']) ?> گیگابایت</li>
          <li><?= icon('clock') ?> اعتبار <?= fa_num($p['duration_days']) ?> روزه</li>
          <li><?= icon('server') ?> دسترسی به هر ۶ سرور</li>
        </ul>
        <a href="<?= url('panel/checkout.php?package=' . $p['id']) ?>" class="btn btn-primary btn-block">
          <?= icon('cart') ?> انتخاب و پرداخت
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
