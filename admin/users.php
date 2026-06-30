<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'users';
$pageTitle = 'کاربران';

$users = db()->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>کاربران</h2>
  <p>فهرست کاربران به همراه شناسهٔ عددی.</p>
</div>

<div class="table-wrap">
  <table class="tbl">
    <thead>
      <tr><th>شناسه عددی</th><th>نام کاربری</th><th>ایمیل</th><th>موبایل</th><th>تأیید</th><th>نقش</th><th>عضویت</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><span class="id-chip"><?= fa_num($u['id']) ?></span></td>
          <td><b><?= e($u['username']) ?></b></td>
          <td dir="ltr" style="text-align:right"><?= e($u['email']) ?></td>
          <td dir="ltr" style="text-align:right"><?= fa_num($u['phone']) ?></td>
          <td style="white-space:nowrap">
            <span class="badge <?= $u['email_verified'] ? 'ok' : 'no' ?>" title="ایمیل"><?= icon('mail') ?></span>
            <span class="badge <?= $u['phone_verified'] ? 'ok' : 'no' ?>" title="موبایل"><?= icon('phone') ?></span>
          </td>
          <td><?= $u['role'] === 'admin' ? '<span class="badge info">مدیر</span>' : 'کاربر' ?></td>
          <td style="white-space:nowrap"><?= jdate_fa($u['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
