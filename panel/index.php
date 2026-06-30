<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'home';
$pageTitle = 'خانه';

$me = require_login();
// تمام اشتراک‌های فعال کاربر (پشتیبانی از چند بستهٔ همزمان)
$stmt = db()->prepare(
    'SELECT * FROM subscriptions WHERE user_id = ? AND active = 1 AND end_at > NOW()
     ORDER BY id DESC'
);
$stmt->execute([$me['id']]);
$subs = $stmt->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<?php if ($subs): ?>
  <div class="page-title">
    <h2>اشتراک‌های فعال شما</h2>
    <p>وضعیت لحظه‌ای حجم و زمان باقی‌ماندهٔ هر بسته (<?= fa_num(count($subs)) ?> اشتراک فعال).</p>
  </div>

  <?php foreach ($subs as $sub):
    $totalVol = max(1, (int)$sub['total_volume_gb']);
    $usedVol  = min($totalVol, (int)$sub['used_volume_gb']);
    $remVol   = $totalVol - $usedVol;
    $volPct   = round($remVol / $totalVol * 100);

    $totalDays = max(1, (int)$sub['total_days']);
    $secsLeft  = strtotime($sub['end_at']) - time();
    $daysLeft  = max(0, (int)ceil($secsLeft / 86400));
    $daysLeft  = min($daysLeft, $totalDays);
    $timePct   = round($daysLeft / $totalDays * 100);
  ?>
    <div class="sub-block">
      <div class="sub-head">
        <h3><?= icon('box') ?> بسته «<?= e($sub['title']) ?>»</h3>
      </div>
      <div class="grid-cols cols-2">
        <div class="card donut-card">
          <div class="js-donut"
               data-value="<?= $timePct ?>"
               data-color="#3f82ff" data-color2="#37dbff"
               data-center="<?= fa_num($daysLeft) ?>"
               data-sub="روز مانده"></div>
          <h3>زمان باقی‌مانده</h3>
          <div class="meta">از مجموع <?= fa_num($totalDays) ?> روز • پایان: <?= jdate_fa($sub['end_at']) ?></div>
        </div>

        <div class="card donut-card">
          <div class="js-donut"
               data-value="<?= $volPct ?>"
               data-color="#37dbff" data-color2="#3f82ff"
               data-center="<?= fa_num($remVol) ?>"
               data-sub="گیگابایت"></div>
          <h3>حجم باقی‌مانده</h3>
          <div class="meta">از مجموع <?= fa_num($totalVol) ?> گیگابایت • مصرف: <?= fa_num($usedVol) ?> گیگ</div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="card" style="margin-top:1.4rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
    <div>
      <h3 style="margin-bottom:.3rem">می‌خواهید اشتراک دیگری اضافه کنید؟</h3>
      <p style="color:var(--muted);margin:0">بسته‌های جدید با تخفیف ویژه در دسترس‌اند و کنار اشتراک‌های فعلی فعال می‌شوند.</p>
    </div>
    <a href="<?= url('panel/buy.php') ?>" class="btn btn-primary"><?= icon('cart') ?> خرید بسته جدید</a>
  </div>

<?php else: ?>
  <div class="page-title">
    <h2>خانه</h2>
    <p>هنوز اشتراک فعالی ندارید.</p>
  </div>
  <div class="card empty">
    <?= icon('inbox') ?>
    <h3>اشتراک فعالی یافت نشد</h3>
    <p>برای شروع و کاهش پینگ، یکی از بسته‌های جرقه را تهیه کنید.</p>
    <a href="<?= url('panel/buy.php') ?>" class="btn btn-primary" style="margin-top:1rem"><?= icon('cart') ?> مشاهده بسته‌ها</a>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
