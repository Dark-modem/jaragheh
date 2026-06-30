<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'support';
$pageTitle = 'پشتیبانی';

$tickets = db()->query(
    'SELECT t.*, u.username, u.id AS uid FROM tickets t JOIN users u ON u.id = t.user_id ORDER BY
     (t.status = "open") DESC, t.updated_at DESC'
)->fetchAll();

$statusLabel = ['open' => ['در انتظار پاسخ', 'wait'], 'answered' => ['پاسخ داده شد', 'ok'], 'closed' => ['بسته شده', 'info']];

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>تیکت‌های پشتیبانی</h2>
  <p>به تیکت‌های کاربران پاسخ دهید. تیکت‌های در انتظار پاسخ بالاتر نمایش داده می‌شوند.</p>
</div>

<?php if (!$tickets): ?>
  <div class="card empty"><?= icon('inbox') ?><h3>تیکتی وجود ندارد</h3></div>
<?php else: ?>
  <div class="card">
    <div class="ticket-list">
      <?php foreach ($tickets as $t): [$lbl, $cls] = $statusLabel[$t['status']]; ?>
        <a href="<?= url('admin/ticket.php?id=' . $t['id']) ?>">
          <div>
            <b><?= e($t['subject']) ?></b>
            <div style="font-size:.78rem;color:var(--faint)">
              #<?= fa_num($t['id']) ?> • کاربر <span class="id-chip"><?= fa_num($t['uid']) ?></span> <?= e($t['username']) ?> • <?= jdate_fa($t['updated_at'], true) ?>
            </div>
          </div>
          <span class="badge <?= $cls ?>"><?= $lbl ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
