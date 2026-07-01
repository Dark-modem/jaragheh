<?php
require_once __DIR__ . '/../../../backend/core/helpers.php';
require_once __DIR__ . '/../../../backend/core/icons.php';
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="<?= url('index.php') ?>" class="brand" style="margin-bottom:1rem">
          <span class="spark"><?= icon('spark') ?></span>
          <span><b><?= e(SITE_NAME) ?></b></span>
        </a>
        <p>جرقه با شبکه‌ای از سرورهای بهینه، پینگ شما را در بازی‌های محبوب کاهش می‌دهد و اتصالی پایدار برای رقابت حرفه‌ای فراهم می‌کند.</p>
      </div>
      <div>
        <h4>دسترسی سریع</h4>
        <a href="<?= url('index.php#games') ?>">بازی‌های پشتیبانی‌شده</a>
        <a href="<?= url('index.php#packages') ?>">بسته‌ها و تعرفه‌ها</a>
        <a href="<?= url('index.php#reviews') ?>">نظرات کاربران</a>
        <a href="<?= url('index.php#faq') ?>">سوالات متداول</a>
        <a href="<?= url('terms.php') ?>">قوانین و مقررات</a>
        <a href="<?= url('register.php') ?>">ساخت حساب کاربری</a>
      </div>
      <div>
        <h4>پشتیبانی</h4>
        <a href="<?= e(setting('telegram_id', 'https://t.me/supjaragheh')) ?>" target="_blank" rel="noopener">پشتیبانی تلگرام</a>
        <a href="<?= url('login.php') ?>">سامانه تیکت</a>
      </div>
    </div>

    <?php if (!empty($showEnamad)): ?>
    <!-- نماد اعتماد الکترونیکی - فقط صفحه اصلی -->
    <div class="enamad-wrap">
      <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=744634&Code=P8WemA63Zz1UgCe2BrbggHLk83Bn8huS'>
        <img referrerpolicy='origin'
             src='https://trustseal.enamad.ir/logo.aspx?id=744634&Code=P8WemA63Zz1UgCe2BrbggHLk83Bn8huS'
             alt='نماد اعتماد الکترونیکی'
             style='cursor:pointer'
             code='P8WemA63Zz1UgCe2BrbggHLk83Bn8huS'>
      </a>
    </div>
    <?php endif; ?>

    <div class="footer-bottom">
      © <?= fa_num(date('Y')) ?> — تمامی حقوق برای <?= e(SITE_NAME) ?> محفوظ است.
    </div>
  </div>
</footer>

<script src="<?= url('frontend/assets/js/app.js') ?>"></script>
</body>
</html>
