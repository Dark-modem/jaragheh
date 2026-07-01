<?php
/**
 * ===========================================================
 *  پیکربندی اصلی سایت جرقه
 *  این تنها فایلی است که برای راه‌اندازی روی هاست باید ویرایش شود.
 * ===========================================================
 */

// ---------- اطلاعات دیتابیس (از کنترل پنل هاست بگیرید) ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'onvipir1_chat');      // نام دیتابیس
define('DB_USER', 'onvipir1_chat');          // یوزرنیم دیتابیس
define('DB_PASS', 'g]HFzJF,4bi#a_-o');              // پسورد دیتابیس
define('DB_CHARSET', 'utf8mb4');

// ---------- تنظیمات سایت ----------
define('SITE_NAME', 'جرقه');
define('SITE_SLOGAN', 'بهینه‌ساز شبکه مخصوص گیم');

// آدرس پایه سایت بدون اسلش انتهایی. اگر در ساب‌دامین یا روت اصلی است خالی بگذارید
// مثال برای روت اصلی دامنه:  ''  | مثال برای پوشه:  '/jaragheh'
define('BASE_URL', '');

// شناسه عددی کاربران از این عدد شروع می‌شود (در install.sql هم تنظیم شده)
define('USER_ID_START', 1000);

// ---------- درگاه آقای پرداخت ----------
// کد پین درگاه را از پنل آقای پرداخت بگیرید.
// برای تست بدون پول واقعی، مقدار را 'sandbox' بگذارید.
define('AQAYE_PIN', 'sandbox');
define('AQAYE_CREATE_URL', 'https://panel.aqayepardakht.ir/api/v2/create');
define('AQAYE_VERIFY_URL', 'https://panel.aqayepardakht.ir/api/v2/verify');
define('AQAYE_STARTPAY',  'https://panel.aqayepardakht.ir/startpay/');

// ---------- حالت توسعه ----------
// در حالت true کدهای تایید ایمیل/پیامک روی صفحه نمایش داده می‌شوند
// (چون پنل پیامک/ایمیل هنوز متصل نشده). در محیط واقعی false کنید.
define('DEV_MODE', true);

// منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// نمایش خطاها فقط در حالت توسعه
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
