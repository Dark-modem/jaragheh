<?php
require_once __DIR__ . '/../../../backend/core/helpers.php';
require_once __DIR__ . '/../../../backend/core/icons.php';
$me = require_admin();
$active = $active ?? 'dash';
$pageTitle = $pageTitle ?? 'پنل مدیریت';
$initial = mb_substr($me['username'], 0, 1, 'UTF-8');
function anav(string $key, string $active): string { return $key === $active ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | مدیریت <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= url('frontend/assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M13 2 4.5 13H11l-1 9 9.5-12H13l1-8Z' fill='%233f82ff'/></svg>">
</head>
<body>
<div class="panel-shell">
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <aside class="sidebar" id="sidebar">
    <a href="<?= url('admin/index.php') ?>" class="brand">
      <span class="spark"><?= icon('shield') ?></span>
      <span class="brand-text"><b><?= e(SITE_NAME) ?></b><br><span class="tag" style="font-size:.68rem;color:var(--muted)">پنل مدیریت</span></span>
      <button class="sidebar-collapse" id="sidebarCollapse" type="button" aria-label="جمع کردن منو"><?= icon('chevron') ?></button>
    </a>
    <nav class="side-nav">
      <a href="<?= url('admin/index.php') ?>" class="<?= anav('dash',$active) ?>" title="داشبورد"><?= icon('gauge') ?> <span class="nav-label">داشبورد</span></a>
      <a href="<?= url('admin/users.php') ?>" class="<?= anav('users',$active) ?>" title="کاربران"><?= icon('users') ?> <span class="nav-label">کاربران</span></a>
      <a href="<?= url('admin/orders.php') ?>" class="<?= anav('orders',$active) ?>" title="سفارشات"><?= icon('orders') ?> <span class="nav-label">سفارشات</span></a>
      <a href="<?= url('admin/products.php') ?>" class="<?= anav('products',$active) ?>" title="محصولات"><?= icon('box') ?> <span class="nav-label">محصولات</span></a>
      <a href="<?= url('admin/games.php') ?>" class="<?= anav('games',$active) ?>" title="بازی‌ها"><?= icon('gamepad') ?> <span class="nav-label">بازی‌ها</span></a>
      <a href="<?= url('admin/support.php') ?>" class="<?= anav('support',$active) ?>" title="پشتیبانی"><?= icon('ticket') ?> <span class="nav-label">پشتیبانی</span></a>
      <a href="<?= url('admin/settings.php') ?>" class="<?= anav('settings',$active) ?>" title="درگاه‌ها"><?= icon('gateway') ?> <span class="nav-label">درگاه‌ها</span></a>
    </nav>
    <div class="side-foot">
      <nav class="side-nav">
        <a href="<?= url('index.php') ?>" title="مشاهده سایت"><?= icon('home') ?> <span class="nav-label">مشاهده سایت</span></a>
        <a href="<?= url('logout.php') ?>" class="logout" title="خروج"><?= icon('logout') ?> <span class="nav-label">خروج</span></a>
      </nav>
    </div>
  </aside>

  <div class="panel-main">
    <div class="panel-mobile-bar">
      <a href="<?= url('admin/index.php') ?>" class="brand" style="font-size:1.1rem">
        <span class="spark"><?= icon('shield') ?></span><b>مدیریت</b>
      </a>
      <button class="menu-toggle" data-target="sidebar" aria-label="منو"><?= icon('menu') ?></button>
    </div>

    <div class="panel-top">
      <div>
        <h1><?= e($pageTitle) ?></h1>
        <div class="crumb">مدیریت <?= e(SITE_NAME) ?></div>
      </div>
      <div class="profile-btn" style="cursor:default">
        <span class="who" style="text-align:left">
          <small>مدیر</small><b><?= e($me['username']) ?></b>
        </span>
        <span class="av"><img src="<?= e(avatar_url($me['username'], 64)) ?>" alt="" width="32" height="32"></span>
      </div>
    </div>

    <div class="panel-body">
      <?php foreach (get_flashes() as $f): ?>
        <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
