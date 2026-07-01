<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'support';
$pageTitle = 'پشتیبانی';
$me = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $subject = trim($_POST['subject'] ?? '');
    $body    = trim($_POST['body'] ?? '');
    if ($subject === '' || $body === '') {
        flash('error', 'موضوع و متن پیام را وارد کنید.');
    } else {
        db()->prepare('INSERT INTO tickets (user_id, subject, status) VALUES (?, ?, "open")')
            ->execute([$me['id'], $subject]);
        $tid = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO ticket_messages (ticket_id, sender, body) VALUES (?, "user", ?)')
            ->execute([$tid, $body]);
        flash('success', 'تیکت شما ثبت شد. پاسخ پشتیبانی به‌زودی اعلام می‌شود.');
        redirect('panel/ticket.php?id=' . $tid);
    }
}

$tickets = db()->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC');
$tickets->execute([$me['id']]);
$tickets = $tickets->fetchAll();

$statusLabel = ['open' => ['در انتظار پاسخ', 'wait'], 'answered' => ['پاسخ داده شد', 'ok'], 'closed' => ['بسته شده', 'info']];

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
?>

<div class="page-title">
  <h2>پشتیبانی</h2>
  <p>تیکت جدید بسازید یا گفتگوهای قبلی را پیگیری کنید.</p>
</div>

<div class="grid-cols cols-2">
  <!-- تیکت جدید -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('plus') ?> تیکت جدید</h3>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label>موضوع</label>
        <input class="input" name="subject" placeholder="مثلاً: مشکل در اتصال" required>
      </div>
      <div class="field">
        <label>متن پیام</label>
        <textarea class="textarea" name="body" placeholder="توضیح کامل مشکل یا سؤال..." required></textarea>
      </div>
      <button class="btn btn-primary" type="submit"><?= icon('ticket') ?> ارسال تیکت</button>
    </form>
  </div>

  <!-- لیست تیکت‌ها -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('inbox') ?> تیکت‌های من</h3>
    <?php if (!$tickets): ?>
      <div class="empty" style="padding:1.5rem 0"><?= icon('inbox') ?><p>هنوز تیکتی ثبت نکرده‌اید.</p></div>
    <?php else: ?>
      <div class="ticket-list">
        <?php foreach ($tickets as $t): [$lbl, $cls] = $statusLabel[$t['status']]; ?>
          <a href="<?= url('panel/ticket.php?id=' . $t['id']) ?>">
            <div>
              <b><?= e($t['subject']) ?></b>
              <div style="font-size:.78rem;color:var(--faint)">#<?= fa_num($t['id']) ?> • <?= jdate_fa($t['updated_at'], true) ?></div>
            </div>
            <span class="badge <?= $cls ?>"><?= $lbl ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
