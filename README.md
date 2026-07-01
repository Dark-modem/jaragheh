# 🎮 جرقه — بهینه‌ساز شبکه مخصوص گیم

## ساختار پروژه (جدید)

```
jaragheh/
├── backend/                  ← 🔧 قلمرو توسعه‌دهنده بک‌اند
│   ├── core/                 ← هسته PHP (دیتابیس، توابع، ایمیل)
│   │   ├── db.php            ← اتصال PDO به MySQL
│   │   ├── helpers.php       ← توابع کمکی، auth، payment config
│   │   ├── icons.php         ← آیکون‌های SVG
│   │   └── mailer.php        ← ارسال کد تایید (SMS/Email)
│   ├── config/               ← فایل‌های پیکربندی (محافظت‌شده)
│   │   ├── .htaccess         ← جلوگیری از دسترسی مستقیم
│   │   └── aqayepardakht.json ← تنظیمات درگاه آقای پرداخت
│   ├── api/                  ← (اختیاری) لاجیک‌های API جداگانه
│   └── payment/              ← (اختیاری) لاجیک پرداخت جداگانه
│
├── frontend/                 ← 🎨 قلمرو توسعه‌دهنده فرانت‌اند
│   ├── assets/
│   │   ├── css/style.css     ← استایل اصلی سایت
│   │   ├── js/app.js         ← جاوااسکریپت (وانیلا)
│   │   └── fonts/            ← فونت‌های وزیرمتن (محلی)
│   └── views/inc/            ← قالب‌های HTML/PHP
│       ├── header.php        ← هدر سایت عمومی
│       ├── footer.php        ← فوتر سایت عمومی
│       ├── panel_header.php  ← هدر پنل کاربری
│       ├── panel_footer.php  ← فوتر پنل کاربری
│       ├── admin_header.php  ← هدر پنل ادمین
│       └── admin_footer.php  ← فوتر پنل ادمین
│
├── admin/      ← صفحات پنل ادمین (ورودی)
├── panel/      ← صفحات پنل کاربری (ورودی)
├── payment/    ← پردازش پرداخت (قابل دسترس از وب)
├── api/        ← نقاط پایانی API (قابل دسترس از وب)
├── uploads/    ← آپلودهای کاربران
├── install/    ← نصب‌کننده (حذف پس از نصب)
│
├── config.php  ← ⚙️ پیکربندی اصلی (دیتابیس، حالت توسعه)
├── index.php   ← صفحه اصلی سایت
├── login.php   ← ورود
├── register.php← ثبت‌نام
└── ...
```

## راه‌اندازی

۱. فایل `config.php` را با اطلاعات دیتابیس پر کنید
۲. `install/index.php` را در مرورگر باز کنید
۳. پس از نصب، پوشه `install/` را حذف کنید
۴. پین درگاه آقای پرداخت را در `backend/config/aqayepardakht.json` وارد کنید

## نکات مهم برای توسعه‌دهندگان

### فرانت‌اند
- همه استایل‌ها در `frontend/assets/css/style.css`
- همه JS در `frontend/assets/js/app.js`
- قالب‌های HTML در `frontend/views/inc/`

### بک‌اند
- توابع کمکی در `backend/core/helpers.php`
- هیچ‌گاه `config.php` را در Git commit نکنید (اطلاعات دیتابیس)
- برای تست پرداخت، `pin` را روی `sandbox` نگه دارید
