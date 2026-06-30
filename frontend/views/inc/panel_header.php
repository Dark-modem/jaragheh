<?php
require_once __DIR__ . '/../../../backend/core/helpers.php';
require_once __DIR__ . '/../../../backend/core/icons.php';
$me = require_login();
if (($me['role'] ?? '') === 'admin') { redirect('admin/index.php'); }
$active = $active ?? 'home';
$pageTitle = $pageTitle ?? 'پنل کاربری';
$initial = mb_substr($me['username'], 0, 1, 'UTF-8');

function pnav(string $key, string $active): string { return $key === $active ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= url('frontend/assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M13 2 4.5 13H11l-1 9 9.5-12H13l1-8Z' fill='%233f82ff'/></svg>">
<script>window.CSRF = <?= json_encode(csrf_token()) ?>;</script>
</head>
<body>
<div class="panel-shell">

  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <aside class="sidebar" id="sidebar">
    <a href="<?= url('index.php') ?>" class="brand">
      <span class="spark"><?= icon('spark') ?></span>
      <span><b><?= e(SITE_NAME) ?></b></span>
    </a>
    <nav class="side-nav">
      <a href="<?= url('panel/index.php') ?>" class="<?= pnav('home',$active) ?>"><?= icon('home') ?> خانه</a>
      <a href="<?= url('panel/buy.php') ?>" class="<?= pnav('buy',$active) ?>"><?= icon('cart') ?> خرید بسته</a>
      <a href="<?= url('panel/support.php') ?>" class="<?= pnav('support',$active) ?>"><?= icon('ticket') ?> پشتیبانی</a>
      <a href="<?= url('panel/orders.php') ?>" class="<?= pnav('orders',$active) ?>"><?= icon('orders') ?> سفارشات</a>
    </nav>
    <div class="side-foot">
      <nav class="side-nav">
        <a href="<?= url('panel/profile.php') ?>" class="<?= pnav('profile',$active) ?>"><?= icon('user') ?> پروفایل</a>
        <a href="<?= url('logout.php') ?>" class="logout"><?= icon('logout') ?> خروج</a>
      </nav>
    </div>
  </aside>

  <div class="panel-main">
    <div class="panel-mobile-bar">
      <a href="<?= url('panel/index.php') ?>" class="brand" style="font-size:1.1rem">
        <span class="spark"><?= icon('spark') ?></span><b><?= e(SITE_NAME) ?></b>
      </a>
      <button class="menu-toggle" data-target="sidebar" aria-label="منو"><?= icon('menu') ?></button>
    </div>

    <div class="panel-top">
      <div>
        <h1><?= e($pageTitle) ?></h1>
        <div class="crumb">پنل کاربری جرقه</div>
      </div>
      <a href="<?= url('panel/profile.php') ?>" class="profile-btn" title="پروفایل من">
        <span class="who" style="text-align:left">
          <small>شناسه <?= fa_num($me['id']) ?></small>
          <b><?= e($me['username']) ?></b>
        </span>
        <span class="av"><?= e($initial) ?></span>
      </a>
    </div>

    <div class="panel-body">
      <?php foreach (get_flashes() as $f): ?>
        <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
