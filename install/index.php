<?php
/**
 * ===========================================================
 *  جرقه | نصب‌کننده و به‌روزرسان خودکار
 *  این فایل را در مرورگر باز کنید:  https://دامنه/install/
 *  فرم را پر کنید تا دیتابیس ساخته/به‌روزرسانی شود و config نوشته شود.
 *  پس از پایان نصب، این پوشه (install) را حتماً حذف کنید.
 * ===========================================================
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
@date_default_timezone_set('Asia/Tehran');

$ROOT = dirname(__DIR__);                 // مسیر ریشهٔ سایت
$CONFIG_FILE = $ROOT . '/config.php';
$AQAYE_FILE  = $ROOT . '/config/aqayepardakht.json';
$SQL_FILE    = $ROOT . '/install.sql';

$errors  = [];
$success = false;
$report  = [];

/* ---------- پیش‌فرض‌های فرم (در صورت وجود config فعلی، پیش‌پر می‌شوند) ---------- */
$defaults = [
    'db_host' => 'localhost', 'db_name' => 'jaragheh', 'db_user' => 'root', 'db_pass' => '',
    'site_name' => 'جرقه', 'base_url' => '',
    'admin_user' => 'admin', 'admin_email' => 'admin@jaragheh.ir', 'admin_phone' => '09120000000', 'admin_pass' => '',
    'telegram' => 'https://t.me/supjaragheh', 'aqaye_pin' => 'sandbox', 'dev_mode' => '1',
];
if (is_file($CONFIG_FILE)) {
    $c = file_get_contents($CONFIG_FILE);
    foreach ([
        'db_host' => 'DB_HOST', 'db_name' => 'DB_NAME', 'db_user' => 'DB_USER', 'db_pass' => 'DB_PASS',
        'site_name' => 'SITE_NAME', 'base_url' => 'BASE_URL',
    ] as $field => $const) {
        if (preg_match("/define\\(\\s*'" . $const . "'\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*\\)/", $c, $m)) {
            $defaults[$field] = stripcslashes($m[1]);
        }
    }
}

function v(string $k): string { return trim($_POST[$k] ?? ''); }
function php_str(string $s): string { return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $s) . "'"; }

/* ---------- پردازش فرم ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = [
        'db_host' => v('db_host'), 'db_name' => v('db_name'), 'db_user' => v('db_user'), 'db_pass' => $_POST['db_pass'] ?? '',
        'site_name' => v('site_name') ?: 'جرقه', 'base_url' => rtrim(v('base_url'), '/'),
        'admin_user' => v('admin_user'), 'admin_email' => v('admin_email'), 'admin_phone' => v('admin_phone'), 'admin_pass' => $_POST['admin_pass'] ?? '',
        'telegram' => v('telegram') ?: 'https://t.me/supjaragheh', 'aqaye_pin' => v('aqaye_pin') ?: 'sandbox',
        'dev_mode' => isset($_POST['dev_mode']) ? '1' : '0',
    ];
    $defaults = array_merge($defaults, $f);

    // اعتبارسنجی
    if ($f['db_name'] === '' || $f['db_user'] === '') $errors[] = 'نام دیتابیس و یوزرنیم دیتابیس الزامی است.';
    if ($f['admin_user'] === '') $errors[] = 'نام کاربری مدیر الزامی است.';
    $isUpdate = is_file($CONFIG_FILE);
    if (!$isUpdate && strlen($f['admin_pass']) < 6) {
        $errors[] = 'برای نصب جدید، رمز عبور مدیر باید حداقل ۶ کاراکتر باشد.';
    }

    // اتصال به دیتابیس
    $pdo = null;
    if (!$errors) {
        try {
            $dsn = 'mysql:host=' . $f['db_host'] . ';dbname=' . $f['db_name'] . ';charset=utf8mb4';
            $pdo = new PDO($dsn, $f['db_user'], $f['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec("SET NAMES utf8mb4");
            $report[] = 'اتصال به دیتابیس برقرار شد.';
        } catch (Throwable $e) {
            $errors[] = 'اتصال به دیتابیس ناموفق بود: ' . $e->getMessage();
        }
    }

    // اجرای اسکیما (ساخت/به‌روزرسانی جداول) + داده‌های اولیه
    if (!$errors && $pdo) {
        try {
            $created = 0;
            if (is_file($SQL_FILE)) {
                $sql = file_get_contents($SQL_FILE);
                // حذف کامنت‌های خطی
                $sql = preg_replace('/^--.*$/m', '', $sql);
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    // فقط دستورهای اسکیما را اجرا کن؛ داده‌های اولیه را جداگانه می‌سازیم
                    if (preg_match('/^\s*(CREATE\s+TABLE|ALTER\s+TABLE|SET\s)/i', $stmt)) {
                        $pdo->exec($stmt);
                        if (stripos($stmt, 'CREATE TABLE') !== false) $created++;
                    }
                }
            } else {
                $errors[] = 'فایل install.sql پیدا نشد.';
            }
            if (!$errors) $report[] = "جداول بررسی/ساخته شدند ({$created} جدول).";

            // ---------- مهاجرت امن: افزودن ستون‌های جدید به جدول users موجود ----------
            if (!$errors) {
                $cols = [];
                foreach ($pdo->query("SHOW COLUMNS FROM `users`")->fetchAll() as $col) {
                    $cols[$col['Field']] = true;
                }
                if (!isset($cols['banned'])) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `banned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `role`");
                    $report[] = 'ستون banned به جدول کاربران اضافه شد.';
                }
                if (!isset($cols['can_use_gateway'])) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `can_use_gateway` TINYINT(1) NOT NULL DEFAULT 1 AFTER `banned`");
                    $report[] = 'ستون can_use_gateway به جدول کاربران اضافه شد.';
                }
            }
        } catch (Throwable $e) {
            $errors[] = 'خطا در ساخت جداول: ' . $e->getMessage();
        }
    }

    // داده‌های اولیه (فقط در صورت خالی بودن) + مدیر
    if (!$errors && $pdo) {
        try {
            // تنظیمات پیش‌فرض (بدون بازنویسی مقادیر موجود)
            $set = $pdo->prepare("INSERT INTO settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=svalue");
            $set->execute(['gateway_card_enabled', '1']);
            $set->execute(['gateway_card_text', 'برای دریافت شماره کارت و تکمیل خرید کارت‌به‌کارت، به پشتیبانی تلگرام پیام دهید.']);
            $set->execute(['telegram_id', $f['telegram']]);
            // اگر تلگرام در فرم تغییر کرد، به‌روزرسانی صریح
            $pdo->prepare("UPDATE settings SET svalue=? WHERE skey='telegram_id'")->execute([$f['telegram']]);

            // بازی‌ها فقط اگر خالی باشد
            if ((int)$pdo->query("SELECT COUNT(*) FROM games")->fetchColumn() === 0) {
                $g = $pdo->prepare("INSERT INTO games (name,image,sort_order) VALUES (?,NULL,?)");
                foreach (['کالاف اف دیوتی' => 1, 'فری فایر' => 2, 'پابجی' => 3, 'فورت نایت' => 4] as $name => $s) $g->execute([$name, $s]);
                $report[] = 'بازی‌های پیش‌فرض اضافه شدند.';
            }
            // بسته‌ها فقط اگر خالی باشد
            if ((int)$pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn() === 0) {
                $p = $pdo->prepare("INSERT INTO packages (title,description,volume_gb,duration_days,price,discount_price,active,sort_order) VALUES (?,?,?,?,?,?,1,?)");
                $p->execute(['بسته برنزی', 'مناسب گیمرهای معمولی، کاهش پینگ پایه روی ۶ سرور.', 50, 30, 120000, 99000, 1]);
                $p->execute(['بسته نقره‌ای', 'پینگ پایدار برای رنک‌پوش‌ها، اولویت مسیر اختصاصی.', 100, 30, 220000, 179000, 2]);
                $p->execute(['بسته طلایی', 'حداکثر کارایی، کمترین پینگ ممکن و پشتیبانی ویژه.', 200, 60, 420000, 349000, 3]);
                $report[] = 'بسته‌های نمونه اضافه شدند.';
            }

            // مدیر: ساخت یا به‌روزرسانی
            $exists = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
            if ($f['admin_pass'] !== '') {
                $hash = password_hash($f['admin_pass'], PASSWORD_BCRYPT);
                // اگر مدیری با این نام کاربری هست → به‌روزرسانی، در غیر اینصورت درج
                $u = $pdo->prepare("SELECT id FROM users WHERE username=?");
                $u->execute([$f['admin_user']]);
                if ($uid = $u->fetchColumn()) {
                    $pdo->prepare("UPDATE users SET password=?, role='admin', email_verified=1, phone_verified=1 WHERE id=?")
                        ->execute([$hash, $uid]);
                    $report[] = 'حساب مدیر به‌روزرسانی شد.';
                } else {
                    $ins = $pdo->prepare("INSERT INTO users (username,email,phone,password,email_verified,phone_verified,role) VALUES (?,?,?,?,1,1,'admin')");
                    $ins->execute([$f['admin_user'], $f['admin_email'], $f['admin_phone'], $hash]);
                    $report[] = 'حساب مدیر ساخته شد.';
                }
            } elseif (!$exists) {
                $errors[] = 'هیچ مدیری وجود ندارد؛ لطفاً رمز عبور مدیر را وارد کنید.';
            }
        } catch (Throwable $e) {
            $errors[] = 'خطا در درج داده‌های اولیه: ' . $e->getMessage();
        }
    }

    // نوشتن config.php
    if (!$errors) {
        $cfg = "<?php\n"
            . "/**\n *  پیکربندی اصلی سایت جرقه — ساخته‌شده توسط نصب‌کننده\n */\n\n"
            . "// ---------- دیتابیس ----------\n"
            . "define('DB_HOST', " . php_str($f['db_host']) . ");\n"
            . "define('DB_NAME', " . php_str($f['db_name']) . ");\n"
            . "define('DB_USER', " . php_str($f['db_user']) . ");\n"
            . "define('DB_PASS', " . php_str($f['db_pass']) . ");\n"
            . "define('DB_CHARSET', 'utf8mb4');\n\n"
            . "// ---------- سایت ----------\n"
            . "define('SITE_NAME', " . php_str($f['site_name']) . ");\n"
            . "define('SITE_SLOGAN', 'بهینه‌ساز شبکه مخصوص گیم');\n"
            . "define('BASE_URL', " . php_str($f['base_url']) . ");\n"
            . "define('USER_ID_START', 1000);\n\n"
            . "// ---------- درگاه آقای پرداخت (مقدار اصلی در config/aqayepardakht.json) ----------\n"
            . "define('AQAYE_PIN', " . php_str($f['aqaye_pin']) . ");\n"
            . "define('AQAYE_CREATE_URL', 'https://panel.aqayepardakht.ir/api/v2/create');\n"
            . "define('AQAYE_VERIFY_URL', 'https://panel.aqayepardakht.ir/api/v2/verify');\n"
            . "define('AQAYE_STARTPAY',  'https://panel.aqayepardakht.ir/startpay/');\n\n"
            . "// ---------- حالت توسعه ----------\n"
            . "define('DEV_MODE', " . ($f['dev_mode'] === '1' ? 'true' : 'false') . ");\n\n"
            . "date_default_timezone_set('Asia/Tehran');\n\n"
            . "if (DEV_MODE) { error_reporting(E_ALL); ini_set('display_errors','1'); }\n"
            . "else { error_reporting(0); ini_set('display_errors','0'); }\n";

        if (@file_put_contents($CONFIG_FILE, $cfg) === false) {
            $errors[] = 'نوشتن فایل config.php ناموفق بود. دسترسی نوشتن (مجوز 644/664) روی ریشهٔ سایت را بررسی کنید.';
        } else {
            $report[] = 'فایل config.php نوشته شد.';
        }
    }

    // نوشتن config/aqayepardakht.json
    if (!$errors) {
        @mkdir(dirname($AQAYE_FILE), 0775, true);
        $aq = [
            'pin' => $f['aqaye_pin'], 'enabled' => true,
            'create_url' => 'https://panel.aqayepardakht.ir/api/v2/create',
            'verify_url' => 'https://panel.aqayepardakht.ir/api/v2/verify',
            'startpay_url' => 'https://panel.aqayepardakht.ir/startpay/',
            '_help' => 'مقدار pin همان کد مرچنت/پین درگاه آقای پرداخت است. برای تست مقدار را sandbox بگذارید.',
        ];
        @file_put_contents($AQAYE_FILE, json_encode($aq, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $report[] = 'فایل پیکربندی آقای پرداخت نوشته شد.';
    }

    if (!$errors) $success = true;
}

$d = $defaults;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>نصب جرقه</title>
<style>
  :root{--bg:#05070f;--surface:#0b1222;--surface2:#0e1729;--border:#1c2942;--spark:#3f82ff;--text:#e8edf8;--muted:#8693b1;--green:#35d29a;--red:#f5736f;}
  *{box-sizing:border-box;}
  body{margin:0;background:var(--bg);color:var(--text);font-family:Tahoma,Vazirmatn,sans-serif;line-height:1.9;padding:2rem 1rem;}
  .wrap{max-width:760px;margin:0 auto;}
  .logo{display:flex;align-items:center;gap:.6rem;justify-content:center;margin-bottom:1.4rem;font-size:1.4rem;font-weight:800;}
  .logo svg{width:30px;height:30px;color:var(--spark);}
  .card{background:linear-gradient(180deg,var(--surface),var(--surface2));border:1px solid var(--border);border-radius:16px;padding:1.6rem;margin-bottom:1.2rem;}
  h1{font-size:1.3rem;margin:.2rem 0 .4rem;text-align:center;}
  .sub{color:var(--muted);text-align:center;margin-bottom:1.4rem;font-size:.92rem;}
  h3{font-size:1rem;border-right:3px solid var(--spark);padding-right:.6rem;margin:1.4rem 0 .8rem;}
  label{display:block;font-size:.85rem;margin-bottom:.3rem;color:var(--muted);}
  input[type=text],input[type=password]{width:100%;padding:.6rem .8rem;border-radius:10px;border:1px solid var(--border);background:#0a1120;color:var(--text);font-family:inherit;margin-bottom:.9rem;}
  input:focus{outline:none;border-color:var(--spark);}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:.9rem;}
  .ltr{direction:ltr;text-align:left;}
  .chk{display:flex;align-items:center;gap:.5rem;color:var(--text);margin-bottom:.9rem;}
  .chk input{width:18px;height:18px;accent-color:var(--spark);}
  .btn{display:block;width:100%;padding:.8rem;border:none;border-radius:10px;background:linear-gradient(135deg,var(--spark),#2f6fe6);color:#fff;font-weight:800;font-size:1rem;cursor:pointer;font-family:inherit;}
  .alert{padding:.9rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.9rem;}
  .alert.err{background:rgba(245,115,111,.12);border:1px solid rgba(245,115,111,.3);color:var(--red);}
  .alert.ok{background:rgba(53,210,154,.12);border:1px solid rgba(53,210,154,.3);color:var(--green);}
  .alert ul{margin:.4rem 0 0;padding-right:1.2rem;}
  .help{font-size:.78rem;color:var(--muted);margin:-.6rem 0 1rem;}
  code{background:#0a1120;padding:.1rem .4rem;border-radius:6px;color:#9ec5ff;}
  a.cta{display:inline-block;margin-top:.4rem;color:#9ec5ff;}
  @media(max-width:560px){.row{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 4.5 13H11l-1 9 9.5-12H13l1-8Z"/></svg>
    <span>نصب جرقه</span>
  </div>

  <?php if ($success): ?>
    <div class="card">
      <div class="alert ok">
        <b>✅ نصب با موفقیت انجام شد!</b>
        <ul><?php foreach ($report as $r) echo '<li>' . htmlspecialchars($r) . '</li>'; ?></ul>
      </div>
      <h3>گام‌های بعدی</h3>
      <p>۱. برای امنیت، پوشهٔ <code>install</code> را همین حالا حذف کنید.</p>
      <p>۲. وارد سایت شوید و با حساب مدیر مدیریت کنید.</p>
      <p>۳. برای محیط واقعی، در <code>config.php</code> مقدار <code>DEV_MODE</code> را <code>false</code> کنید و سرویس پیامک/ایمیل را در <code>inc/mailer.php</code> متصل کنید.</p>
      <p style="margin-top:1rem">
        <a class="cta" href="<?= htmlspecialchars(($d['base_url'] ?: '') . '/index.php') ?>">→ مشاهدهٔ سایت</a>
        &nbsp;|&nbsp;
        <a class="cta" href="<?= htmlspecialchars(($d['base_url'] ?: '') . '/login.php') ?>">→ ورود مدیر</a>
      </p>
    </div>
  <?php else: ?>
    <h1><?= is_file($CONFIG_FILE) ? 'به‌روزرسانی / پیکربندی مجدد' : 'نصب اولیه' ?></h1>
    <p class="sub">موارد زیر را پر کنید تا دیتابیس ساخته یا به‌روزرسانی شود و فایل تنظیمات نوشته شود.</p>

    <?php if ($errors): ?>
      <div class="alert err"><b>خطاها را برطرف کنید:</b>
        <ul><?php foreach ($errors as $er) echo '<li>' . htmlspecialchars($er) . '</li>'; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="card">
        <h3>۱) اطلاعات دیتابیس</h3>
        <div class="row">
          <div><label>هاست دیتابیس</label><input type="text" name="db_host" class="ltr" value="<?= htmlspecialchars($d['db_host']) ?>"></div>
          <div><label>نام دیتابیس</label><input type="text" name="db_name" class="ltr" value="<?= htmlspecialchars($d['db_name']) ?>"></div>
        </div>
        <div class="row">
          <div><label>یوزرنیم دیتابیس</label><input type="text" name="db_user" class="ltr" value="<?= htmlspecialchars($d['db_user']) ?>"></div>
          <div><label>پسورد دیتابیس</label><input type="password" name="db_pass" class="ltr" value="<?= htmlspecialchars($d['db_pass']) ?>"></div>
        </div>
        <p class="help">این اطلاعات را از کنترل‌پنل هاست (cPanel/DirectAdmin) بسازید و وارد کنید.</p>
      </div>

      <div class="card">
        <h3>۲) اطلاعات سایت</h3>
        <div class="row">
          <div><label>نام سایت</label><input type="text" name="site_name" value="<?= htmlspecialchars($d['site_name']) ?>"></div>
          <div><label>مسیر نصب (خالی برای روت دامنه)</label><input type="text" name="base_url" class="ltr" placeholder="/folder" value="<?= htmlspecialchars($d['base_url']) ?>"></div>
        </div>
        <label>آیدی/لینک پشتیبانی تلگرام</label><input type="text" name="telegram" class="ltr" value="<?= htmlspecialchars($d['telegram']) ?>">
      </div>

      <div class="card">
        <h3>۳) حساب مدیر</h3>
        <div class="row">
          <div><label>نام کاربری مدیر</label><input type="text" name="admin_user" class="ltr" value="<?= htmlspecialchars($d['admin_user']) ?>"></div>
          <div><label>رمز عبور مدیر <?= is_file($CONFIG_FILE) ? '(خالی = بدون تغییر)' : '' ?></label><input type="password" name="admin_pass" class="ltr" placeholder="حداقل ۶ کاراکتر"></div>
        </div>
        <div class="row">
          <div><label>ایمیل مدیر</label><input type="text" name="admin_email" class="ltr" value="<?= htmlspecialchars($d['admin_email']) ?>"></div>
          <div><label>شمارهٔ مدیر</label><input type="text" name="admin_phone" class="ltr" value="<?= htmlspecialchars($d['admin_phone']) ?>"></div>
        </div>
      </div>

      <div class="card">
        <h3>۴) درگاه و حالت</h3>
        <label>کد مرچنت آقای پرداخت (PIN)</label><input type="text" name="aqaye_pin" class="ltr" value="<?= htmlspecialchars($d['aqaye_pin']) ?>">
        <p class="help">برای تست مقدار <code>sandbox</code> را نگه دارید. بعداً از پنل ادمین یا فایل <code>config/aqayepardakht.json</code> قابل تغییر است.</p>
        <label class="chk"><input type="checkbox" name="dev_mode" <?= $d['dev_mode'] === '1' ? 'checked' : '' ?>> حالت توسعه (نمایش کد تأیید روی صفحه — برای محیط واقعی خاموش کنید)</label>
      </div>

      <button class="btn" type="submit"><?= is_file($CONFIG_FILE) ? 'به‌روزرسانی و ذخیره' : 'نصب کن' ?></button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
