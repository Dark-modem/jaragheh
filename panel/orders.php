<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'orders';
$pageTitle = 'سفارشات';
$me = require_login();

$orders = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$orders->execute([$me['id']]);
$orders = $orders->fetchAll();

$statusMap = [
    'pending'  => ['در انتظار پرداخت', 'wait'],
    'awaiting' => ['در انتظار تأیید (کارت‌به‌کارت)', 'wait'],
    'paid'     => ['پرداخت شده', 'ok'],
    'failed'   => ['ناموفق', 'no'],
];
$gwMap = ['card' => 'کارت‌به‌کارت', 'aqayepardakht' => 'آنلاین (آقای پرداخت)'];

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<div class="page-title">
  <h2>سفارشات من</h2>
  <p>تاریخچهٔ خریدها و وضعیت پرداخت.</p>
</div>

<?php if (!$orders): ?>
  <div class="card empty"><?= icon('orders') ?><h3>سفارشی ثبت نشده</h3><p>پس از اولین خرید، سفارش‌ها اینجا نمایش داده می‌شوند.</p>
    <a href="<?= url('panel/buy.php') ?>" class="btn btn-primary" style="margin-top:1rem"><?= icon('cart') ?> خرید بسته</a>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table class="tbl">
      <thead>
        <tr><th>شماره سفارش</th><th>بسته</th><th>مبلغ</th><th>روش پرداخت</th><th>وضعیت</th><th>تاریخ</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): [$lbl, $cls] = $statusMap[$o['status']]; ?>
          <tr>
            <td><span class="id-chip"><?= e($o['order_number']) ?></span></td>
            <td><?= e($o['package_title']) ?></td>
            <td><?= toman($o['amount']) ?></td>
            <td><?= e($gwMap[$o['gateway']] ?? $o['gateway']) ?></td>
            <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
            <td style="white-space:nowrap"><?= jdate_fa($o['created_at']) ?></td>
            <td>
              <?php if ($o['status'] === 'pending' && $o['gateway'] === 'aqayepardakht'): ?>
                <a href="<?= url('payment/start.php?order=' . $o['id']) ?>" class="btn btn-primary btn-sm">پرداخت</a>
              <?php elseif ($o['status'] === 'awaiting'): ?>
                <a href="<?= e(setting('telegram_id', 'https://t.me/supjaragheh')) ?>" target="_blank" rel="noopener" class="btn btn-soft btn-sm"><?= icon('tg') ?> تلگرام</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
