<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'settings';
$pageTitle = 'درگاه‌ها';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    set_setting('gateway_card_enabled', isset($_POST['card_enabled']) ? '1' : '0');
    set_setting('gateway_card_text', trim($_POST['card_text'] ?? ''));
    set_setting('telegram_id', trim($_POST['telegram_id'] ?? ''));
    // درگاه آقای پرداخت در فایل JSON ذخیره می‌شود
    aqaye_cfg_save([
        'enabled' => isset($_POST['aqaye_enabled']),
        'pin'     => trim($_POST['aqaye_pin'] ?? 'sandbox'),
    ]);
    flash('success', 'تنظیمات درگاه‌ها ذخیره شد.');
    redirect('admin/settings.php');
}

$cardEnabled  = setting('gateway_card_enabled', '1') === '1';
$aqayeEnabled = (bool)aqaye_cfg('enabled');
$cardText     = setting('gateway_card_text', '');
$telegram     = setting('telegram_id', 'https://t.me/supjaragheh');
$aqayePin     = aqaye_cfg('pin');

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>درگاه‌های پرداخت</h2>
  <p>دو روش پرداخت در دسترس است: کارت‌به‌کارت (با راهنمایی تلگرام) و درگاه آنلاین آقای پرداخت.</p>
</div>

<form method="post">
  <?= csrf_field() ?>
  <div class="grid-cols cols-2">

    <!-- کارت‌به‌کارت -->
    <div class="card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
        <h3 style="margin:0"><?= icon('tg') ?> درگاه کارت‌به‌کارت</h3>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem">
          <input type="checkbox" name="card_enabled" <?= $cardEnabled ? 'checked' : '' ?> style="accent-color:var(--spark);width:18px;height:18px"> فعال
        </label>
      </div>
      <div class="field">
        <label>متن نمایش به کاربر هنگام انتخاب کارت‌به‌کارت</label>
        <textarea class="textarea" name="card_text" style="min-height:90px"><?= e($cardText) ?></textarea>
        <div class="help">این متن در صفحهٔ پرداخت به کاربر نشان داده می‌شود.</div>
      </div>
      <div class="field">
        <label>آیدی/لینک پشتیبانی تلگرام</label>
        <input class="input" name="telegram_id" value="<?= e($telegram) ?>" dir="ltr">
      </div>
    </div>

    <!-- آقای پرداخت -->
    <div class="card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
        <h3 style="margin:0"><?= icon('wallet') ?> درگاه آقای پرداخت</h3>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem">
          <input type="checkbox" name="aqaye_enabled" <?= $aqayeEnabled ? 'checked' : '' ?> style="accent-color:var(--spark);width:18px;height:18px"> فعال
        </label>
      </div>
      <div class="field">
        <label>کد مرچنت / پین درگاه (PIN)</label>
        <input class="input" name="aqaye_pin" value="<?= e($aqayePin) ?>" dir="ltr">
        <div class="help">کد مرچنت را از پنل آقای پرداخت دریافت کنید. برای تست از مقدار <code>sandbox</code> استفاده کنید. این مقدار در فایل <code>config/aqayepardakht.json</code> ذخیره می‌شود و در صورت نیاز می‌توانید همان فایل را مستقیماً ویرایش کنید.</div>
      </div>
      <div class="notice">
        <?= icon('shield') ?>
        <div>پرداخت‌های موفق به‌صورت خودکار تأیید و اشتراک کاربر فعال می‌شود. آدرس بازگشت (callback) به‌طور خودکار تنظیم می‌گردد.</div>
      </div>
    </div>

  </div>

  <button class="btn btn-primary" type="submit" style="margin-top:1.4rem"><?= icon('check') ?> ذخیره تنظیمات</button>
</form>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
