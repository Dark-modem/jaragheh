<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'support';
$pageTitle = 'گفتگوی تیکت';
$me = require_login();

$tid = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM tickets WHERE id = ? AND user_id = ?');
$stmt->execute([$tid, $me['id']]);
$ticket = $stmt->fetch();
if (!$ticket) { flash('error', 'تیکت یافت نشد.'); redirect('panel/support.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $body = trim($_POST['body'] ?? '');
    if ($ticket['status'] === 'closed') {
        flash('error', 'این تیکت بسته شده است.');
    } elseif ($body === '') {
        flash('error', 'متن پیام خالی است.');
    } else {
        db()->prepare('INSERT INTO ticket_messages (ticket_id, sender, body) VALUES (?, "user", ?)')
            ->execute([$tid, $body]);
        db()->prepare('UPDATE tickets SET status = "open", updated_at = NOW() WHERE id = ?')->execute([$tid]);
        flash('success', 'پیام شما ارسال شد.');
    }
    redirect('panel/ticket.php?id=' . $tid);
}

$msgs = db()->prepare('SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY id');
$msgs->execute([$tid]);
$msgs = $msgs->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<div class="page-title" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h2><?= e($ticket['subject']) ?></h2>
    <p>تیکت #<?= fa_num($ticket['id']) ?></p>
  </div>
  <a href="<?= url('panel/support.php') ?>" class="btn btn-soft btn-sm"><?= icon('arrow') ?> بازگشت</a>
</div>

<div class="card">
  <div class="ticket-thread">
    <?php foreach ($msgs as $m): ?>
      <div class="msg <?= $m['sender'] === 'admin' ? 'admin' : 'user' ?>">
        <?= nl2br(e($m['body'])) ?>
        <div class="meta"><?= $m['sender'] === 'admin' ? 'پشتیبانی جرقه' : 'شما' ?> • <?= jdate_fa($m['created_at'], true) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($ticket['status'] !== 'closed'): ?>
    <form method="post" style="display:flex;gap:.7rem;align-items:flex-end">
      <?= csrf_field() ?>
      <div class="field" style="flex:1;margin:0">
        <textarea class="textarea" name="body" placeholder="پاسخ خود را بنویسید..." style="min-height:70px" required></textarea>
      </div>
      <button class="btn btn-primary" type="submit"><?= icon('arrow') ?> ارسال</button>
    </form>
  <?php else: ?>
    <div class="notice"><?= icon('lock') ?><div>این تیکت بسته شده است. برای پیگیری جدید، تیکت تازه‌ای ثبت کنید.</div></div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
