<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'dash';
$pageTitle = 'داشبورد';

$userCount   = (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$orderCount  = (int)db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$paidRevenue = (int)db()->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status = 'paid'")->fetchColumn();
$openTickets = (int)db()->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'")->fetchColumn();
$pkgCount    = (int)db()->query('SELECT COUNT(*) FROM packages WHERE active = 1')->fetchColumn();

$recent = db()->query(
    'SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 6'
)->fetchAll();

$statusMap = [
    'pending' => ['در انتظار', 'wait'], 'awaiting' => ['کارت‌به‌کارت', 'wait'],
    'paid' => ['پرداخت شده', 'ok'], 'failed' => ['ناموفق', 'no'],
];

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>داشبورد مدیریت</h2>
  <p>نمای کلی فعالیت‌های جرقه.</p>
</div>

<div class="grid-cols cols-auto" style="margin-bottom:1.6rem">
  <div class="card stat-card">
    <div class="ic"><?= icon('users') ?></div>
    <div><b><?= fa_num($userCount) ?></b><span>کاربران</span></div>
  </div>
  <div class="card stat-card">
    <div class="ic"><?= icon('orders') ?></div>
    <div><b><?= fa_num($orderCount) ?></b><span>کل سفارش‌ها</span></div>
  </div>
  <div class="card stat-card">
    <div class="ic"><?= icon('wallet') ?></div>
    <div><b style="font-size:1.15rem"><?= toman($paidRevenue) ?></b><span>درآمد پرداخت‌شده</span></div>
  </div>
  <div class="card stat-card">
    <div class="ic"><?= icon('ticket') ?></div>
    <div><b><?= fa_num($openTickets) ?></b><span>تیکت‌های باز</span></div>
  </div>
  <div class="card stat-card">
    <div class="ic"><?= icon('box') ?></div>
    <div><b><?= fa_num($pkgCount) ?></b><span>بسته‌های فعال</span></div>
  </div>
</div>

<div class="card">
  <h3 style="margin-bottom:1rem"><?= icon('orders') ?> آخرین سفارش‌ها</h3>
  <?php if (!$recent): ?>
    <div class="empty" style="padding:1.5rem 0"><?= icon('inbox') ?><p>سفارشی ثبت نشده است.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="tbl">
        <thead><tr><th>شناسه کاربر</th><th>کاربر</th><th>شماره سفارش</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $o): [$lbl, $cls] = $statusMap[$o['status']]; ?>
            <tr>
              <td><span class="id-chip"><?= fa_num($o['user_id']) ?></span></td>
              <td><?= e($o['username']) ?></td>
              <td><span class="id-chip"><?= e($o['order_number']) ?></span></td>
              <td><?= toman($o['amount']) ?></td>
              <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
              <td style="white-space:nowrap"><?= jdate_fa($o['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
