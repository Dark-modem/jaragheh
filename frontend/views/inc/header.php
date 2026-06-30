<?php
require_once __DIR__ . '/../../../backend/core/helpers.php';
require_once __DIR__ . '/../../../backend/core/icons.php';
$u = current_user();
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
<meta name="description" content="جرقه؛ بهینه‌ساز شبکه اینترنت مخصوص گیم. کاهش پینگ و اتصال پایدار روی بازی‌های محبوب.">
<link rel="stylesheet" href="<?= url('frontend/assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M13 2 4.5 13H11l-1 9 9.5-12H13l1-8Z' fill='%233f82ff'/></svg>">
</head>
<body>
<header class="site-header">
  <div class="container bar">
    <a href="<?= url('index.php') ?>" class="brand">
      <span class="spark"><?= icon('spark') ?></span>
      <span><b><?= e(SITE_NAME) ?></b><br><span class="tag"><?= e(SITE_SLOGAN) ?></span></span>
    </a>

    <nav class="nav-links" id="navlinks">
      <a href="<?= url('index.php#home') ?>">خانه</a>
      <a href="<?= url('index.php#games') ?>">بازی‌ها</a>
      <a href="<?= url('index.php#packages') ?>">بسته‌ها</a>
      <a href="<?= url('index.php#features') ?>">چرا جرقه؟</a>
    </nav>

    <div class="nav-actions">
      <?php if ($u): ?>
        <a href="<?= url($u['role'] === 'admin' ? 'admin/index.php' : 'panel/index.php') ?>" class="btn btn-primary btn-sm">
          <?= icon('user') ?> پنل کاربری
        </a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>" class="btn btn-ghost btn-sm">ورود</a>
        <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm">ثبت‌نام</a>
      <?php endif; ?>
      <button class="menu-toggle" data-target="navlinks" aria-label="منو"><?= icon('menu') ?></button>
    </div>
  </div>
</header>
