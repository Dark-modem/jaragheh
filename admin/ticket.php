<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'support';
$pageTitle = 'پاسخ تیکت';

$tid = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT t.*, u.username, u.id AS uid FROM tickets t JOIN users u ON u.id = t.user_id WHERE t.id = ?');
$stmt->execute([$tid]);
$ticket = $stmt->fetch();
if (!$ticket) { flash('error', 'تیکت یافت نشد.'); redirect('admin/support.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'reply';

    if ($action === 'close') {
        db()->prepare('UPDATE tickets SET status = "closed", updated_at = NOW() WHERE id = ?')->execute([$tid]);
        flash('success', 'تیکت بسته شد.');
        redirect('admin/ticket.php?id=' . $tid);
    }

    $body = trim($_POST['body'] ?? '');
    if ($body === '') {
        flash('error', 'متن پاسخ خالی است.');
    } else {
        db()->prepare('INSERT INTO ticket_messages (ticket_id, sender, body) VALUES (?, "admin", ?)')->execute([$tid, $body]);
        db()->prepare('UPDATE tickets SET status = "answered", updated_at = NOW() WHERE id = ?')->execute([$tid]);
        flash('success', 'پاسخ شما ثبت شد.');
    }
    redirect('admin/ticket.php?id=' . $tid);
}

$msgs = db()->prepare('SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY id');
$msgs->execute([$tid]);
$msgs = $msgs->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h2><?= e($ticket['subject']) ?></h2>
    <p>تیکت #<?= fa_num($ticket['id']) ?> • کاربر <span class="id-chip"><?= fa_num($ticket['uid']) ?></span> <?= e($ticket['username']) ?></p>
  </div>
  <a href="<?= url('admin/support.php') ?>" class="btn btn-soft btn-sm"><?= icon('arrow') ?> بازگشت</a>
</div>

<div class="card">
  <div class="ticket-thread">
    <?php foreach ($msgs as $m): ?>
      <div class="msg <?= $m['sender'] === 'admin' ? 'admin' : 'user' ?>">
        <?= nl2br(e($m['body'])) ?>
        <div class="meta"><?= $m['sender'] === 'admin' ? 'پشتیبانی' : e($ticket['username']) ?> • <?= jdate_fa($m['created_at'], true) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($ticket['status'] !== 'closed'): ?>
    <form method="post" style="margin-bottom:.8rem">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="reply">
      <div class="field">
        <textarea class="textarea" name="body" placeholder="پاسخ پشتیبانی..." required></textarea>
      </div>
      <div style="display:flex;gap:.6rem">
        <button class="btn btn-primary" type="submit"><?= icon('arrow') ?> ارسال پاسخ</button>
      </div>
    </form>
    <form method="post" onsubmit="return confirm('این تیکت بسته شود؟')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="close">
      <button class="btn btn-danger btn-sm" type="submit"><?= icon('lock') ?> بستن تیکت</button>
    </form>
  <?php else: ?>
    <div class="notice"><?= icon('lock') ?><div>این تیکت بسته شده است.</div></div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
