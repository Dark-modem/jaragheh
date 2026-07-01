<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'notifs';
$pageTitle = 'اعلان‌ها';
$me = require_login();

// علامت‌گذاری همه به‌عنوان خوانده‌شده (در صورت درخواست)
if (($_GET['mark_read'] ?? '') === '1') {
    db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$me['id']]);
    redirect('panel/notifications.php');
}

$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$me['id']]);
$notifs = $stmt->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<div class="page-title" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h2>اعلان‌ها</h2>
    <p>پیام‌ها و اعلان‌های مدیریت برای شما.</p>
  </div>
  <?php if ($notifs): ?>
    <a href="<?= url('panel/notifications.php?mark_read=1') ?>" class="btn btn-soft btn-sm"><?= icon('check') ?> علامت‌گذاری همه به‌عنوان خوانده‌شده</a>
  <?php endif; ?>
</div>

<?php if (!$notifs): ?>
  <div class="card empty">
    <?= icon('bell') ?>
    <h3>اعلانی وجود ندارد</h3>
    <p>پیام‌های مدیریت و اعلان‌های مهم اینجا نمایش داده می‌شوند.</p>
  </div>
<?php else: ?>
  <?php foreach ($notifs as $n): $unread = (int)$n['is_read'] === 0; ?>
    <div class="notif-item <?= $unread ? 'unread' : '' ?>">
      <span class="n-dot"><?= icon('bell') ?></span>
      <div style="flex:1">
        <h4><?= e($n['title']) ?> <?= $unread ? '<span class="badge ok">جدید</span>' : '' ?></h4>
        <p><?= e($n['body']) ?></p>
        <time><?= jdate_fa($n['created_at'], true) ?></time>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
