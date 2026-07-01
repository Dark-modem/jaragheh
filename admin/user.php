<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'users';
$pageTitle = 'مدیریت کاربر';

$uid = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$uid]);
$u = $stmt->fetch();
if (!$u) { flash('error', 'کاربر یافت نشد.'); redirect('admin/users.php'); }

$me = require_admin(); // درخواست دسترسی ادمین

// مقادیر فعلی ستون‌ها (با پیش‌فرض امن برای دیتابیس قدیمی)
$banned       = (int)($u['banned'] ?? 0) === 1;
$canUseGateway = (int)($u['can_use_gateway'] ?? 1) === 1;

// پردازش فرم‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_ban') {
        if ($u['role'] === 'admin') {
            flash('error', 'مدیر اصلی را نمی‌توان مسدود کرد.');
        } else {
            $new = $banned ? 0 : 1;
            db()->prepare('UPDATE users SET banned = ? WHERE id = ?')->execute([$new, $uid]);
            flash('success', $new ? 'کاربر از سایت مسدود شد.' : 'مسدودیت کاربر لغو شد.');
        }
        redirect('admin/user.php?id=' . $uid);
    }

    if ($action === 'toggle_gateway') {
        $new = $canUseGateway ? 0 : 1;
        db()->prepare('UPDATE users SET can_use_gateway = ? WHERE id = ?')->execute([$new, $uid]);
        flash('success', $new ? 'دسترسی درگاه آنلاین برای کاربر فعال شد.' : 'دسترسی درگاه آنلاین برای کاربر غیرفعال شد.');
        redirect('admin/user.php?id=' . $uid);
    }

    if ($action === 'send_message') {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        if ($title === '' || $body === '') {
            flash('error', 'عنوان و متن پیام را وارد کنید.');
        } else {
            db()->prepare('INSERT INTO notifications (user_id, title, body) VALUES (?, ?, ?)')
                ->execute([$uid, $title, $body]);
            flash('success', 'پیام برای کاربر ارسال شد و در بخش اعلان‌های او نمایش داده می‌شود.');
        }
        redirect('admin/user.php?id=' . $uid);
    }

    flash('error', 'درخواست نامعتبر بود.');
    redirect('admin/user.php?id=' . $uid);
}

// تاریخچهٔ اعلان‌های ارسال‌شده به این کاربر
$notifStmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 10');
$notifStmt->execute([$uid]);
$notifs = $notifStmt->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>مدیریت کاربر</h2>
  <p>تنظیمات دسترسی و ارسال پیام برای «<?= e($u['username']) ?>».</p>
</div>

<div class="grid-cols cols-2">
  <!-- اطلاعات کاربر -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('user') ?> اطلاعات کاربر</h3>
    <div class="profile-row"><div class="info"><small>شناسه عددی</small><b><span class="id-chip"><?= fa_num($u['id']) ?></span></b></div></div>
    <div class="profile-row"><div class="info"><small>نام کاربری</small><b><?= e($u['username']) ?></b></div></div>
    <div class="profile-row"><div class="info"><small>ایمیل</small><b dir="ltr"><?= e($u['email']) ?></b></div></div>
    <div class="profile-row"><div class="info"><small>موبایل</small><b dir="ltr"><?= fa_num($u['phone']) ?></b></div></div>
    <div class="profile-row"><div class="info"><small>نقش</small><b><?= $u['role'] === 'admin' ? 'مدیر' : 'کاربر' ?></b></div></div>
    <div class="profile-row"><div class="info"><small>عضویت</small><b><?= jdate_fa($u['created_at']) ?></b></div></div>
  </div>

  <!-- کنترل‌های دسترسی -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('shield') ?> کنترل دسترسی</h3>

    <div class="profile-row">
      <div class="info">
        <small>وضعیت ورود به سایت</small>
        <b>
          <?php if ($banned): ?><span class="badge no"><?= icon('ban') ?> مسدود</span>
          <?php else: ?><span class="badge ok"><?= icon('check') ?> فعال</span><?php endif; ?>
        </b>
      </div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="toggle_ban">
        <?php if ($u['role'] === 'admin'): ?>
          <button class="btn btn-soft btn-sm" type="button" disabled title="مدیر اصلی قابل مسدودسازی نیست">
            <?= icon('ban') ?> مسدودسازی
          </button>
        <?php else: ?>
          <button class="btn <?= $banned ? 'btn-primary' : 'btn-soft' ?> btn-sm" type="submit">
            <?= icon('ban') ?> <?= $banned ? 'رفع مسدودیت' : 'مسدود کردن' ?>
          </button>
        <?php endif; ?>
      </form>
    </div>

    <div class="profile-row">
      <div class="info">
        <small>دسترسی درگاه آنلاین (آقای پرداخت)</small>
        <b>
          <?php if ($canUseGateway): ?><span class="badge ok"><?= icon('gateway') ?> مجاز</span>
          <?php else: ?><span class="badge no"><?= icon('gateway') ?> غیرمجاز</span><?php endif; ?>
        </b>
      </div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="toggle_gateway">
        <button class="btn <?= $canUseGateway ? 'btn-soft' : 'btn-primary' ?> btn-sm" type="submit">
          <?= icon('gateway') ?> <?= $canUseGateway ? 'غیرفعال‌کردن' : 'فعال‌کردن' ?>
        </button>
      </form>
    </div>
    <p style="color:var(--muted);font-size:.82rem;margin:1rem 0 0">
      درگاه کارت‌به‌کارت برای همهٔ کاربران در دسترس است و فقط در صورت غیرفعال‌بودن سراسری محدود می‌شود.
    </p>
  </div>
</div>

<!-- ارسال پیام / اعلان -->
<div class="card" style="margin-top:1.4rem">
  <h3 style="margin-bottom:1rem"><?= icon('message') ?> ارسال پیام به کاربر</h3>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="send_message">
    <div class="field">
      <label>عنوان پیام</label>
      <input class="input" name="title" placeholder="مثلاً: تأیید پرداخت" required>
    </div>
    <div class="field">
      <label>متن پیام</label>
      <textarea class="textarea" name="body" rows="4" placeholder="متن پیام خود را بنویسید..." required></textarea>
    </div>
    <button class="btn btn-primary" type="submit"><?= icon('bell') ?> ارسال اعلان</button>
  </form>
</div>

<!-- تاریخچه اعلان‌ها -->
<?php if ($notifs): ?>
<div class="card" style="margin-top:1.4rem">
  <h3 style="margin-bottom:1rem"><?= icon('list') ?> اعلان‌های اخیر این کاربر</h3>
  <?php foreach ($notifs as $n): ?>
    <div class="profile-row">
      <div class="info">
        <small><?= jdate_fa($n['created_at'], true) ?><?= (int)$n['is_read'] === 1 ? ' • خوانده‌شده' : ' • جدید' ?></small>
        <b><?= e($n['title']) ?></b>
        <div style="color:var(--muted);font-size:.85rem;margin-top:.2rem"><?= e($n['body']) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="margin-top:1.4rem">
  <a href="<?= url('admin/users.php') ?>" class="btn btn-soft"><?= icon('arrow') ?> بازگشت به فهرست کاربران</a>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
