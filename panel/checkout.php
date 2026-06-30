<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'buy';
$pageTitle = 'تکمیل خرید';
$me = require_login();

// بارگذاری بسته
$pkgId = (int)($_GET['package'] ?? $_POST['package'] ?? 0);
$stmt = db()->prepare('SELECT * FROM packages WHERE id = ? AND active = 1');
$stmt->execute([$pkgId]);
$pkg = $stmt->fetch();
if (!$pkg) {
    flash('error', 'بسته انتخابی یافت نشد.');
    redirect('panel/buy.php');
}

$hasOff = !empty($pkg['discount_price']) && $pkg['discount_price'] < $pkg['price'];
$amount = (int)($hasOff ? $pkg['discount_price'] : $pkg['price']);

$cardEnabled  = setting('gateway_card_enabled', '1') === '1';
$aqayeEnabled = (bool)aqaye_cfg('enabled');

// ثبت سفارش
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $gateway = $_POST['gateway'] ?? '';
    if (!in_array($gateway, ['card', 'aqayepardakht'], true)) {
        flash('error', 'روش پرداخت را انتخاب کنید.');
        redirect('panel/checkout.php?package=' . $pkgId);
    }
    if ($gateway === 'card' && !$cardEnabled) { flash('error', 'درگاه کارت‌به‌کارت غیرفعال است.'); redirect('panel/checkout.php?package=' . $pkgId); }
    if ($gateway === 'aqayepardakht' && !$aqayeEnabled) { flash('error', 'درگاه آنلاین غیرفعال است.'); redirect('panel/checkout.php?package=' . $pkgId); }

    $orderNo = make_order_number();
    $status  = $gateway === 'card' ? 'awaiting' : 'pending';
    $stmt = db()->prepare(
        'INSERT INTO orders (order_number, user_id, package_id, package_title, amount, gateway, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$orderNo, $me['id'], $pkg['id'], $pkg['title'], $amount, $gateway, $status]);
    $orderId = (int)db()->lastInsertId();

    if ($gateway === 'aqayepardakht') {
        redirect('payment/start.php?order=' . $orderId);
    }
    // کارت‌به‌کارت → صفحه سفارش با راهنمای تلگرام
    flash('info', 'سفارش شما ثبت شد. برای تکمیل پرداخت کارت‌به‌کارت طبق راهنما اقدام کنید.');
    redirect('panel/orders.php');
}

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<div class="page-title">
  <h2>تکمیل خرید</h2>
  <p>بررسی سفارش و انتخاب روش پرداخت.</p>
</div>

<div class="grid-cols cols-2">
  <!-- خلاصه سفارش -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('box') ?> خلاصه سفارش</h3>
    <div class="profile-row"><div class="info"><small>بسته</small><b><?= e($pkg['title']) ?></b></div></div>
    <div class="profile-row"><div class="info"><small>حجم</small><b><?= fa_num($pkg['volume_gb']) ?> گیگابایت</b></div></div>
    <div class="profile-row"><div class="info"><small>مدت اعتبار</small><b><?= fa_num($pkg['duration_days']) ?> روز</b></div></div>
    <div class="profile-row">
      <div class="info"><small>مبلغ قابل پرداخت</small><b style="font-size:1.3rem;color:#fff"><?= toman($amount) ?></b></div>
      <?php if ($hasOff): ?><span class="badge ok"><?= fa_num(round((1 - $pkg['discount_price'] / $pkg['price']) * 100)) ?>٪ تخفیف</span><?php endif; ?>
    </div>
  </div>

  <!-- انتخاب درگاه -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('gateway') ?> روش پرداخت</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="package" value="<?= (int)$pkg['id'] ?>">

      <?php if ($aqayeEnabled): ?>
      <label class="gateway-opt">
        <input type="radio" name="gateway" value="aqayepardakht">
        <span>
          <span class="g-title"><?= icon('wallet') ?> پرداخت آنلاین (آقای پرداخت)</span>
          <span class="g-desc">انتقال به درگاه بانکی و پرداخت آنی با کارت.</span>
        </span>
      </label>
      <?php endif; ?>

      <?php if ($cardEnabled): ?>
      <label class="gateway-opt">
        <input type="radio" name="gateway" value="card">
        <span>
          <span class="g-title"><?= icon('tg') ?> کارت‌به‌کارت</span>
          <span class="g-desc">دریافت شماره کارت از پشتیبانی تلگرام.</span>
        </span>
      </label>
      <?php endif; ?>

      <div class="notice" id="cardNote" style="display:none;margin:.4rem 0 1.2rem">
        <?= icon('tg') ?>
        <div>
          <?= e(setting('gateway_card_text', 'برای دریافت شماره کارت به پشتیبانی تلگرام پیام دهید.')) ?>
          <div style="margin-top:.6rem">
            <a href="<?= e(setting('telegram_id', 'https://t.me/supjaragheh')) ?>" target="_blank" rel="noopener" class="btn btn-soft btn-sm">
              <?= icon('tg') ?> پشتیبانی تلگرام
            </a>
          </div>
        </div>
      </div>

      <button class="btn btn-primary btn-block" type="submit"><?= icon('check') ?> ثبت و ادامه</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
