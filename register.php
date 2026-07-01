<?php
require_once __DIR__ . '/backend/core/helpers.php';
require_once __DIR__ . '/backend/core/icons.php';

if (current_user()) { redirect('panel/index.php'); }

$errors = [];
$old = ['username' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password2'] ?? '';
    $old = compact('username', 'email', 'phone');

    if (mb_strlen($username) < 3) $errors[] = 'نام کاربری باید حداقل ۳ کاراکتر باشد.';
    if (!preg_match('/^[A-Za-z0-9_\.]+$/', $username)) $errors[] = 'نام کاربری فقط شامل حروف انگلیسی، عدد، نقطه و آندرلاین باشد.';
    if (!valid_email($email)) $errors[] = 'ایمیل معتبر نیست.';
    if (!valid_phone($phone)) $errors[] = 'شماره موبایل باید با ۰۹ شروع و ۱۱ رقم باشد.';
    if (mb_strlen($pass) < 6) $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    if ($pass !== $pass2) $errors[] = 'تکرار رمز عبور مطابقت ندارد.';

    if (!$errors) {
        // بررسی یکتایی
        $stmt = db()->prepare('SELECT username, email, phone FROM users WHERE username=? OR email=? OR phone=?');
        $stmt->execute([$username, $email, $phone]);
        if ($row = $stmt->fetch()) {
            if ($row['username'] === $username) $errors[] = 'این نام کاربری قبلاً ثبت شده است.';
            if ($row['email'] === $email) $errors[] = 'این ایمیل قبلاً ثبت شده است.';
            if ($row['phone'] === $phone) $errors[] = 'این شماره قبلاً ثبت شده است.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, $phone, password_hash($pass, PASSWORD_BCRYPT)]);
        $id = (int)db()->lastInsertId();
        login_user($id);
        flash('success', 'حساب شما با شناسه ' . fa_num($id) . ' ساخته شد. ایمیل و شماره را از پروفایل تأیید کنید.');
        redirect('panel/index.php');
    }
}
$pageTitle = 'ثبت‌نام';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ثبت‌نام | <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= url('frontend/assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M13 2 4.5 13H11l-1 9 9.5-12H13l1-8Z' fill='%233f82ff'/></svg>">
</head>
<body>
<div class="auth-wrap">
  <div class="card auth-card">
  <?php foreach (get_flashes() as $f): ?>
    <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>
    <a href="<?= url('index.php') ?>" class="brand">
      <span class="spark"><?= icon('spark') ?></span><span><b><?= e(SITE_NAME) ?></b></span>
    </a>
    <h1>ساخت حساب کاربری</h1>
    <p class="sub">به جرقه بپیوندید و پینگ‌تان را کم کنید.</p>

    <?php foreach ($errors as $er): ?>
      <div class="flash error"><?= e($er) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label>نام کاربری</label>
        <input class="input" name="username" value="<?= e($old['username']) ?>" placeholder="مثلاً: ProGamer" required>
      </div>
      <div class="field">
        <label>ایمیل</label>
        <input class="input" type="email" name="email" value="<?= e($old['email']) ?>" placeholder="you@example.com" dir="ltr" required>
      </div>
      <div class="field">
        <label>شماره موبایل</label>
        <input class="input" name="phone" value="<?= e($old['phone']) ?>" placeholder="09xxxxxxxxx" dir="ltr" inputmode="numeric" required>
      </div>
      <div class="field">
        <label>رمز عبور</label>
        <input class="input" type="password" name="password" placeholder="حداقل ۶ کاراکتر" required>
      </div>
      <div class="field">
        <label>تکرار رمز عبور</label>
        <input class="input" type="password" name="password2" required>
      </div>
      <button class="btn btn-primary btn-block" type="submit"><?= icon('spark') ?> ثبت‌نام</button>
    </form>

    <p class="auth-switch">حساب دارید؟ <a href="<?= url('login.php') ?>">وارد شوید</a></p>
  </div>
</div>
<div class="back-home"><a href="<?= url('index.php') ?>">← بازگشت به سایت</a></div>
</body>
</html>
