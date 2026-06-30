<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'orders';
$pageTitle = 'سفارشات';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if (!in_array($newStatus, ['pending', 'awaiting', 'paid', 'failed'], true)) {
        flash('error', 'وضعیت نامعتبر است.');
    } else {
        if ($newStatus === 'paid') {
            // تأیید پرداخت → فعال‌سازی اشتراک
            activate_order($orderId);
            flash('success', 'سفارش تأیید و اشتراک کاربر فعال شد.');
        } else {
            db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$newStatus, $orderId]);
            flash('success', 'وضعیت سفارش به‌روزرسانی شد.');
        }
    }
    redirect('admin/orders.php');
}

$orders = db()->query(
    'SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC'
)->fetchAll();

$statusMap = [
    'pending' => ['در انتظار پرداخت', 'wait'], 'awaiting' => ['کارت‌به‌کارت', 'wait'],
    'paid' => ['پرداخت شده', 'ok'], 'failed' => ['ناموفق', 'no'],
];
$gwMap = ['card' => 'کارت‌به‌کارت', 'aqayepardakht' => 'آنلاین'];

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>سفارشات</h2>
  <p>شناسهٔ عددی کاربر به‌همراه شمارهٔ سفارش. برای تأیید پرداخت کارت‌به‌کارت، وضعیت را «پرداخت شده» کنید.</p>
</div>

<div class="table-wrap">
  <table class="tbl">
    <thead>
      <tr><th>شناسه کاربر</th><th>کاربر</th><th>شماره سفارش</th><th>بسته</th><th>مبلغ</th><th>درگاه</th><th>وضعیت</th><th>تغییر وضعیت</th></tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): [$lbl, $cls] = $statusMap[$o['status']]; ?>
        <tr>
          <td><span class="id-chip"><?= fa_num($o['user_id']) ?></span></td>
          <td><?= e($o['username']) ?></td>
          <td><span class="id-chip"><?= e($o['order_number']) ?></span></td>
          <td><?= e($o['package_title']) ?></td>
          <td style="white-space:nowrap"><?= toman($o['amount']) ?></td>
          <td><?= e($gwMap[$o['gateway']] ?? $o['gateway']) ?></td>
          <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
          <td>
            <form method="post" style="display:flex;gap:.4rem;align-items:center">
              <?= csrf_field() ?>
              <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
              <select name="status" class="select" style="padding:.4rem .6rem;width:auto">
                <?php foreach ($statusMap as $k => $v): ?>
                  <option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= $v[0] ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-soft btn-sm" type="submit"><?= icon('check') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
