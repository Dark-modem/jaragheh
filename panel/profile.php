<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'profile';
$pageTitle = 'پروفایل';
$me = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // --- تأیید ایمیل ---
    if ($action === 'verify_email') {
        $code = trim($_POST['code'] ?? '');
        if (check_verification((int)$me['id'], 'email', $code)) {
            flash('success', 'ایمیل با موفقیت تأیید شد.');
        } else {
            flash('error', 'کد واردشده نادرست یا منقضی شده است.');
        }
        redirect('panel/profile.php');
    }

    // --- تأیید شماره ---
    if ($action === 'verify_phone') {
        $code = trim($_POST['code'] ?? '');
        if (check_verification((int)$me['id'], 'phone', $code)) {
            flash('success', 'شماره موبایل با موفقیت تأیید شد.');
        } else {
            flash('error', 'کد واردشده نادرست یا منقضی شده است.');
        }
        redirect('panel/profile.php');
    }

    // --- تغییر نام کاربری ---
    if ($action === 'username') {
        $username = trim($_POST['username'] ?? '');
        if (mb_strlen($username) < 3 || !preg_match('/^[A-Za-z0-9_\.]+$/', $username)) {
            flash('error', 'نام کاربری نامعتبر است.');
        } else {
            $c = db()->prepare('SELECT id FROM users WHERE username=? AND id<>?');
            $c->execute([$username, $me['id']]);
            if ($c->fetch()) {
                flash('error', 'این نام کاربری قبلاً گرفته شده است.');
            } else {
                db()->prepare('UPDATE users SET username=? WHERE id=?')->execute([$username, $me['id']]);
                flash('success', 'نام کاربری به‌روزرسانی شد.');
            }
        }
        redirect('panel/profile.php');
    }

    // --- تغییر ایمیل (وضعیت تأیید صفر می‌شود) ---
    if ($action === 'email') {
        $email = trim($_POST['email'] ?? '');
        if (!valid_email($email)) {
            flash('error', 'ایمیل معتبر نیست.');
        } else {
            $c = db()->prepare('SELECT id FROM users WHERE email=? AND id<>?');
            $c->execute([$email, $me['id']]);
            if ($c->fetch()) {
                flash('error', 'این ایمیل قبلاً ثبت شده است.');
            } else {
                db()->prepare('UPDATE users SET email=?, email_verified=0 WHERE id=?')->execute([$email, $me['id']]);
                flash('success', 'ایمیل تغییر کرد. لطفاً دوباره آن را تأیید کنید.');
            }
        }
        redirect('panel/profile.php');
    }

    // --- تغییر شماره (وضعیت تأیید صفر می‌شود) ---
    if ($action === 'phone') {
        $phone = trim($_POST['phone'] ?? '');
        if (!valid_phone($phone)) {
            flash('error', 'شماره موبایل معتبر نیست.');
        } else {
            $c = db()->prepare('SELECT id FROM users WHERE phone=? AND id<>?');
            $c->execute([$phone, $me['id']]);
            if ($c->fetch()) {
                flash('error', 'این شماره قبلاً ثبت شده است.');
            } else {
                db()->prepare('UPDATE users SET phone=?, phone_verified=0 WHERE id=?')->execute([$phone, $me['id']]);
                flash('success', 'شماره تغییر کرد. لطفاً دوباره آن را تأیید کنید.');
            }
        }
        redirect('panel/profile.php');
    }

    // --- تغییر رمز عبور ---
    if ($action === 'password') {
        $cur = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $new2 = $_POST['new2'] ?? '';
        if (!password_verify($cur, $me['password'])) {
            flash('error', 'رمز فعلی نادرست است.');
        } elseif (mb_strlen($new) < 6) {
            flash('error', 'رمز جدید باید حداقل ۶ کاراکتر باشد.');
        } elseif ($new !== $new2) {
            flash('error', 'تکرار رمز جدید مطابقت ندارد.');
        } else {
            db()->prepare('UPDATE users SET password=? WHERE id=?')
                ->execute([password_hash($new, PASSWORD_BCRYPT), $me['id']]);
            flash('success', 'رمز عبور با موفقیت تغییر کرد.');
        }
        redirect('panel/profile.php');
    }
}

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/panel_header.php';
$devNote = DEV_MODE ? '<div class="dev-code" id="devcode-%s" style="display:none">کد تأیید (حالت تست): <b></b></div>' : '';
?>

<div class="page-title">
  <h2>پروفایل من</h2>
  <p>اطلاعات حساب کاربری خود را مدیریت کنید.</p>
</div>

<!-- ====== سرآیند آواتار ====== -->
<div class="card profile-hero">
  <img class="profile-hero-av" src="<?= e(avatar_url($me['username'], 120)) ?>" alt="آواتار <?= e($me['username']) ?>" width="80" height="80">
  <div>
    <h3><?= e($me['username']) ?></h3>
    <p>آواتار شما به‌صورت خودکار بر اساس نام کاربری ساخته می‌شود.</p>
  </div>
</div>

<div class="grid-cols cols-2">

  <!-- ====== ایمیل و شماره + تأیید ====== -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('shield') ?> راه‌های ارتباطی</h3>

    <!-- ایمیل -->
    <div class="profile-row">
      <div class="info">
        <small>ایمیل</small>
        <b><?= e($me['email']) ?></b>
      </div>
      <?php if ($me['email_verified']): ?>
        <span class="badge ok"><?= icon('check') ?> تأیید شده</span>
      <?php else: ?>
        <span class="badge no"><?= icon('x') ?> تأیید نشده</span>
        <button class="btn btn-soft btn-sm" data-send-code="email">ارسال کد تأیید</button>
      <?php endif; ?>
    </div>
    <?php if (!$me['email_verified']): ?>
      <div id="codebox-email" style="display:none;margin:.4rem 0 1rem">
        <?= sprintf($devNote, 'email') ?>
        <form method="post" style="display:flex;gap:.6rem">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="verify_email">
          <input class="input" name="code" placeholder="کد ۵ رقمی" dir="ltr" inputmode="numeric" required>
          <button class="btn btn-primary btn-sm" type="submit">تأیید</button>
        </form>
      </div>
    <?php endif; ?>

    <!-- شماره -->
    <div class="profile-row">
      <div class="info">
        <small>شماره موبایل</small>
        <b><?= fa_num($me['phone']) ?></b>
      </div>
      <?php if ($me['phone_verified']): ?>
        <span class="badge ok"><?= icon('check') ?> تأیید شده</span>
      <?php else: ?>
        <span class="badge no"><?= icon('x') ?> تأیید نشده</span>
        <button class="btn btn-soft btn-sm" data-send-code="phone">ارسال کد تأیید</button>
      <?php endif; ?>
    </div>
    <?php if (!$me['phone_verified']): ?>
      <div id="codebox-phone" style="display:none;margin:.4rem 0 0">
        <?= sprintf($devNote, 'phone') ?>
        <form method="post" style="display:flex;gap:.6rem">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="verify_phone">
          <input class="input" name="code" placeholder="کد ۵ رقمی" dir="ltr" inputmode="numeric" required>
          <button class="btn btn-primary btn-sm" type="submit">تأیید</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <!-- ====== تغییر نام کاربری ====== -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('user') ?> نام کاربری</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="username">
      <div class="field">
        <label>نام کاربری</label>
        <input class="input" name="username" value="<?= e($me['username']) ?>" dir="ltr" required>
      </div>
      <button class="btn btn-primary" type="submit">ذخیره نام کاربری</button>
    </form>
  </div>

  <!-- ====== تغییر ایمیل و شماره ====== -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('mail') ?> تغییر ایمیل</h3>
    <form method="post" style="margin-bottom:1.6rem">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="email">
      <div class="field">
        <label>ایمیل جدید</label>
        <input class="input" type="email" name="email" value="<?= e($me['email']) ?>" dir="ltr" required>
        <div class="help">با تغییر ایمیل، وضعیت تأیید آن صفر می‌شود.</div>
      </div>
      <button class="btn btn-soft" type="submit">به‌روزرسانی ایمیل</button>
    </form>

    <h3 style="margin-bottom:1rem"><?= icon('phone') ?> تغییر شماره</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="phone">
      <div class="field">
        <label>شماره موبایل جدید</label>
        <input class="input" name="phone" value="<?= e($me['phone']) ?>" dir="ltr" inputmode="numeric" required>
        <div class="help">با تغییر شماره، وضعیت تأیید آن صفر می‌شود.</div>
      </div>
      <button class="btn btn-soft" type="submit">به‌روزرسانی شماره</button>
    </form>
  </div>

  <!-- ====== تغییر رمز عبور ====== -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('lock') ?> تغییر رمز عبور</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div class="field">
        <label>رمز فعلی</label>
        <input class="input" type="password" name="current" required>
      </div>
      <div class="field">
        <label>رمز جدید</label>
        <input class="input" type="password" name="new" placeholder="حداقل ۶ کاراکتر" required>
      </div>
      <div class="field">
        <label>تکرار رمز جدید</label>
        <input class="input" type="password" name="new2" required>
      </div>
      <button class="btn btn-primary" type="submit">تغییر رمز</button>
    </form>
  </div>

</div>

<?php require __DIR__ . '/../frontend/views/inc/panel_footer.php'; ?>
