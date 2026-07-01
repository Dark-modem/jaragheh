<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'users';
$pageTitle = 'کاربران';

$users = db()->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
// تعداد اعلان‌های خوانده‌نشده برای هر کاربر (برای نمایش در ستون اقدامات)
$unreadMap = [];
$unreadStmt = db()->query("SELECT user_id, COUNT(*) c FROM notifications WHERE is_read = 0 GROUP BY user_id");
foreach ($unreadStmt->fetchAll() as $row) {
    $unreadMap[(int)$row['user_id']] = (int)$row['c'];
}

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
      <tr><th>شناسه عددی</th><th>نام کاربری</th><th>ایمیل</th><th>موبایل</th><th>تأیید</th><th>نقش</th><th>عضویت</th><th>اقدامات</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u):
        $banned = (int)($u['banned'] ?? 0) === 1;
      ?>
        <tr>
          <td><span class="id-chip"><?= fa_num($u['id']) ?></span></td>
          <td>
            <b><?= e($u['username']) ?></b>
            <?php if ($banned): ?><span class="badge no" title="مسدود"><?= icon('ban') ?> مسدود</span><?php endif; ?>
          </td>
          <td dir="ltr" style="text-align:right"><?= e($u['email']) ?></td>
          <td dir="ltr" style="text-align:right"><?= fa_num($u['phone']) ?></td>
          <td style="white-space:nowrap">
            <span class="badge <?= $u['email_verified'] ? 'ok' : 'no' ?>" title="ایمیل"><?= icon('mail') ?></span>
            <span class="badge <?= $u['phone_verified'] ? 'ok' : 'no' ?>" title="موبایل"><?= icon('phone') ?></span>
          </td>
          <td><?= $u['role'] === 'admin' ? '<span class="badge info">مدیر</span>' : 'کاربر' ?></td>
          <td style="white-space:nowrap"><?= jdate_fa($u['created_at']) ?></td>
          <td style="white-space:nowrap">
            <a href="<?= url('admin/user.php?id=' . (int)$u['id']) ?>" class="btn btn-soft btn-sm">
              <?= icon('edit') ?> مدیریت
              <?php if (!empty($unreadMap[(int)$u['id']])): ?>
                <span class="badge ok" style="margin-right:.2rem"><?= fa_num($unreadMap[(int)$u['id']]) ?></span>
              <?php endif; ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
