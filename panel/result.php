<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'orders';
$pageTitle = 'نتیجه پرداخت';
$me = require_login();

// وضعیت بازگشتی: ok | failed | already | invalid
$st     = $_GET['st']     ?? '';
$orderId = (int)($_GET['order'] ?? 0);

$order = null;
if ($orderId > 0) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$orderId, $me['id']]);
    $order = $stmt->fetch();
}

// نگاشت وضعیت به متن و نوع
$resultMap = [
    'ok'     => ['type' => 'success', 'icon' => 'check',  'title' => 'پرداخت با موفقیت انجام شد',  'desc' => 'اشتراک شما فعال شد. از همینجا می‌توانید خریدها و وضعیت اشتراک را ببینید.'],
    'failed' => ['type' => 'error',   'icon' => 'x',      'title' => 'پرداخت ناموفق بود',          'desc' => 'متأسفانه پرداخت انجام نشد یا توسط شما لغو شد. در صورت کسر وجه، مبلغ تا ۷۲ ساعت بازمی‌گردد.'],
    'already'=> ['type' => 'info',    'icon' => 'check',  'title' => 'این سفارش قبلاً پرداخت شده',  'desc' => 'نیازی به پرداخت مجدد نیست؛ اشتراک مرتبط قبلاً فعال شده است.'],
    'invalid'=> ['type' => 'error',   'icon' => 'x',      'title' => 'اطلاعات بازگشتی نامعتبر بود','desc' => 'نتوانستیم سفارش متناظر این پرداخت را پیدا کنیم. در صورت کسر وجه، با پشتیبانی تماس بگیرید.'],
];
$r = $resultMap[$st] ?? $resultMap['invalid'];

$gwMap   = ['card' => 'کارت‌به‌کارت', 'aqayepardakht' => 'آنلاین (آقای پرداخت)'];
$statusMap = [
    'pending'  => ['در انتظار پرداخت', 'wait'],
    'awaiting' => ['در انتظار تأیید (کارت‌به‌کارت)', 'wait'],
    'paid'     => ['پرداخت شده', 'ok'],
    'failed'   => ['ناموفق', 'no'],
];

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<div class="page-title">
  <h2>نتیجه پرداخت</h2>
  <p>وضعیت تراکنش و جزئیات سفارش.</p>
</div>

<div class="result-hero <?= e($r['type']) ?>">
  <span class="result-ic"><?= icon($r['icon']) ?></span>
  <div>
    <h3><?= e($r['title']) ?></h3>
    <p><?= e($r['desc']) ?></p>
  </div>
</div>

<?php if ($order):
    [$lbl, $cls] = $statusMap[$order['status']] ?? ['نامشخص', 'wait'];
?>
  <div class="card" style="margin-top:1.4rem">
    <h3 style="margin-bottom:1rem"><?= icon('orders') ?> صورت‌حساب سفارش</h3>
    <div class="profile-row"><div class="info"><small>شماره سفارش</small><b><span class="id-chip"><?= e($order['order_number']) ?></span></b></div></div>
    <div class="profile-row"><div class="info"><small>بسته</small><b><?= e($order['package_title']) ?></b></div></div>
    <div class="profile-row"><div class="info"><small>مبلغ</small><b style="font-size:1.2rem;color:#fff"><?= toman($order['amount']) ?></b></div></div>
    <div class="profile-row"><div class="info"><small>روش پرداخت</small><b><?= e($gwMap[$order['gateway']] ?? $order['gateway']) ?></b></div></div>
    <div class="profile-row"><div class="info"><small>کد تراکنش</small><b dir="ltr"><?= e($order['transid'] ?: '-') ?></b></div></div>
    <div class="profile-row">
      <div class="info"><small>وضعیت</small><b><span class="badge <?= $cls ?>"><?= $lbl ?></span></b></div>
    </div>
    <div class="profile-row"><div class="info"><small>تاریخ</small><b><?= jdate_fa($order['created_at'], true) ?></b></div></div>
  </div>

  <div style="margin-top:1.4rem;display:flex;gap:.7rem;flex-wrap:wrap">
    <a href="<?= url('panel/index.php') ?>" class="btn btn-primary"><?= icon('home') ?> بازگشت به خانه</a>
    <a href="<?= url('panel/orders.php') ?>" class="btn btn-soft"><?= icon('orders') ?> سفارش‌های من</a>
  </div>
<?php else: ?>
  <div style="margin-top:1.4rem;display:flex;gap:.7rem;flex-wrap:wrap">
    <a href="<?= url('panel/orders.php') ?>" class="btn btn-primary"><?= icon('orders') ?> سفارش‌های من</a>
    <a href="<?= url('panel/index.php') ?>" class="btn btn-soft"><?= icon('home') ?> بازگشت به خانه</a>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
