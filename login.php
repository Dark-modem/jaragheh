<?php
require_once __DIR__ . '/backend/core/helpers.php';
require_once __DIR__ . '/backend/core/icons.php';

if ($u = current_user()) {
    redirect($u['role'] === 'admin' ? 'admin/index.php' : 'panel/index.php');
}

$error = '';
$idval = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $idval = trim($_POST['identifier'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if ($idval === '' || $pass === '') {
        $error = 'نام کاربری و رمز عبور را وارد کنید.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE username=? OR email=? OR phone=? LIMIT 1');
        $stmt->execute([$idval, $idval, $idval]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password'])) {
            if ($user['role'] !== 'admin' && (int)($user['banned'] ?? 0) === 1) {
                $error = 'دسترسی شما توسط مدیریت مسدود شده است.';
            } else {
                login_user((int)$user['id']);
                redirect($user['role'] === 'admin' ? 'admin/index.php' : 'panel/index.php');
            }
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    }
}
$pageTitle = 'ورود';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ورود | <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= url('frontend/assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M13 2 4.5 13H11l-1 9 9.5-12H13l1-8Z' fill='%233f82ff'/></svg>">
</head>
<body>
<div class="auth-wrap">
  <?php foreach (get_flashes() as $f): ?>
    <div class="flash <?= e($f['type']) ?>" style="margin:0 0 .8rem"><?= e($f['message']) ?></div>
  <?php endforeach; ?>
  <div class="card auth-card">
    <a href="<?= url('index.php') ?>" class="brand">
      <span class="spark"><?= icon('spark') ?></span><span><b><?= e(SITE_NAME) ?></b></span>
    </a>
    <h1>ورود به حساب</h1>
    <p class="sub">خوش برگشتید! وارد پنل خود شوید.</p>

    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label>نام کاربری، ایمیل یا موبایل</label>
        <input class="input" name="identifier" value="<?= e($idval) ?>" dir="ltr" required autofocus>
      </div>
      <div class="field">
        <label>رمز عبور</label>
        <input class="input" type="password" name="password" required>
      </div>
      <button class="btn btn-primary btn-block" type="submit"><?= icon('lock') ?> ورود</button>
    </form>

    <p class="auth-switch">حساب ندارید؟ <a href="<?= url('register.php') ?>">ثبت‌نام کنید</a></p>
  </div>
</div>
<div class="back-home"><a href="<?= url('index.php') ?>">← بازگشت به سایت</a></div>
</body>
</html>
