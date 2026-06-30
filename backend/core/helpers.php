<?php
/**
 * توابع کمکی عمومی
 */
require_once __DIR__ . '/db.php';

// ---------- شروع نشست امن ----------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------- آدرس‌دهی ----------
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

// ---------- امن‌سازی خروجی ----------
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ---------- توکن CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        die('نشست شما منقضی شده است. صفحه را تازه کنید و دوباره تلاش کنید.');
    }
}

// ---------- پیام‌های فلش ----------
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ---------- احراز هویت ----------
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        redirect('login.php');
    }
    // کاربر مسدودشده از پنل خارج می‌شود (مدیرها مستثنا)
    if (($u['role'] ?? '') !== 'admin' && (int)($u['banned'] ?? 0) === 1) {
        logout_user();
        flash('error', 'دسترسی شما توسط مدیریت مسدود شده است.');
        redirect('login.php');
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login();
    if (($u['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die('دسترسی غیرمجاز');
    }
    return $u;
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}

// ---------- تنظیمات سایت (جدول settings) ----------
function setting(string $key, $default = null)
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (db()->query('SELECT skey, svalue FROM settings') as $row) {
            $all[$row['skey']] = $row['svalue'];
        }
    }
    return $all[$key] ?? $default;
}

function set_setting(string $key, $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (skey, svalue) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)'
    );
    $stmt->execute([$key, (string)$value]);
}

// ---------- پیکربندی آقای پرداخت (فایل JSON) ----------
function aqaye_cfg_path(): string
{
    return __DIR__ . '/../config/aqayepardakht.json';
}

/** کل پیکربندی درگاه آقای پرداخت را از فایل JSON می‌خواند (با مقادیر پیش‌فرض امن) */
function aqaye_cfg_all(): array
{
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $defaults = [
        'pin'          => defined('AQAYE_PIN') ? AQAYE_PIN : 'sandbox',
        'enabled'      => true,
        'create_url'   => defined('AQAYE_CREATE_URL') ? AQAYE_CREATE_URL : 'https://panel.aqayepardakht.ir/api/v2/create',
        'verify_url'   => defined('AQAYE_VERIFY_URL') ? AQAYE_VERIFY_URL : 'https://panel.aqayepardakht.ir/api/v2/verify',
        'startpay_url' => defined('AQAYE_STARTPAY')  ? AQAYE_STARTPAY  : 'https://panel.aqayepardakht.ir/startpay/',
    ];

    $path = aqaye_cfg_path();
    if (is_file($path)) {
        $data = json_decode((string)file_get_contents($path), true);
        if (is_array($data)) {
            $cfg = array_merge($defaults, $data);
            $cfg['enabled'] = !empty($cfg['enabled']);
            return $cfg;
        }
    }
    $cfg = $defaults;
    return $cfg;
}

/** یک مقدار از پیکربندی آقای پرداخت */
function aqaye_cfg(string $key, $default = null)
{
    $all = aqaye_cfg_all();
    return $all[$key] ?? $default;
}

/** نوشتن پیکربندی آقای پرداخت در فایل JSON (فقط کلیدهای مجاز) */
function aqaye_cfg_save(array $values): bool
{
    $current = aqaye_cfg_all();
    foreach (['pin', 'enabled', 'create_url', 'verify_url', 'startpay_url'] as $k) {
        if (array_key_exists($k, $values)) {
            $current[$k] = $k === 'enabled' ? (bool)$values[$k] : trim((string)$values[$k]);
        }
    }
    $current['_help'] = 'مقدار pin همان کد مرچنت/پین درگاه آقای پرداخت است. برای تست بدون پول واقعی مقدار را sandbox بگذارید. در صورت اشتباه بودن، فقط همین فایل را ویرایش کنید.';
    $json = json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $dir = dirname(aqaye_cfg_path());
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    return @file_put_contents(aqaye_cfg_path(), $json) !== false;
}

// ---------- اعداد و قیمت فارسی ----------
function fa_num($input): string
{
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace($en, $fa, (string)$input);
}

function toman($amount): string
{
    return fa_num(number_format((int)$amount)) . ' تومان';
}

// ---------- کد تایید ----------
function generate_code(): string
{
    return (string)random_int(10000, 99999);
}

/**
 * ساخت و «ارسال» کد تایید برای ایمیل یا شماره.
 * فعلاً پنل پیامک/ایمیل متصل نیست؛ کد در دیتابیس ذخیره و در حالت توسعه
 * به کاربر نمایش داده می‌شود. محل اتصال سرویس واقعی در inc/mailer.php است.
 */
function issue_verification(int $userId, string $type): string
{
    $code = generate_code();
    $stmt = db()->prepare(
        'INSERT INTO verification_codes (user_id, vtype, code, expires_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))'
    );
    $stmt->execute([$userId, $type, $code]);
    require_once __DIR__ . '/mailer.php';
    send_code($userId, $type, $code);
    return $code;
}

function check_verification(int $userId, string $type, string $code): bool
{
    $stmt = db()->prepare(
        'SELECT id FROM verification_codes
         WHERE user_id = ? AND vtype = ? AND code = ? AND used = 0 AND expires_at > NOW()
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$userId, $type, $code]);
    $row = $stmt->fetch();
    if (!$row) {
        return false;
    }
    db()->prepare('UPDATE verification_codes SET used = 1 WHERE id = ?')->execute([$row['id']]);
    $col = $type === 'email' ? 'email_verified' : 'phone_verified';
    db()->prepare("UPDATE users SET $col = 1 WHERE id = ?")->execute([$userId]);
    return true;
}

// ---------- اعتبارسنجی ورودی ----------
function valid_email(string $v): bool
{
    return (bool)filter_var($v, FILTER_VALIDATE_EMAIL);
}

function valid_phone(string $v): bool
{
    return (bool)preg_match('/^09\d{9}$/', $v);
}

// ---------- شماره سفارش ----------
function make_order_number(): string
{
    return 'JR-' . date('ym') . '-' . random_int(1000, 9999);
}

// ---------- تاریخ شمسی (بدون وابستگی خارجی) ----------
function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
          + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * intdiv($days, 12053));
    $days %= 12053;
    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv($days - 186, 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return [$jy, $jm, $jd];
}

/** تبدیل یک تاریخ/زمان میلادی (رشته یا timestamp) به رشته شمسی فارسی */
function jdate_fa($datetime, bool $withTime = false): string
{
    $ts = is_numeric($datetime) ? (int)$datetime : strtotime((string)$datetime);
    if (!$ts) return '-';
    [$jy, $jm, $jd] = gregorian_to_jalali((int)date('Y', $ts), (int)date('n', $ts), (int)date('j', $ts));
    $out = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    if ($withTime) $out .= ' ' . date('H:i', $ts);
    return fa_num($out);
}

// ---------- فعال‌سازی سفارش و ساخت اشتراک ----------
function activate_order(int $orderId): void
{
    $o = db()->prepare('SELECT * FROM orders WHERE id = ?');
    $o->execute([$orderId]);
    $order = $o->fetch();
    if (!$order || $order['status'] === 'paid') {
        // اگر قبلاً پرداخت/فعال شده، دوباره اشتراک نساز
        if ($order && $order['status'] !== 'paid') {
            db()->prepare("UPDATE orders SET status='paid', paid_at=NOW() WHERE id=?")->execute([$orderId]);
        }
        return;
    }

    db()->prepare("UPDATE orders SET status='paid', paid_at=NOW() WHERE id=?")->execute([$orderId]);

    $p = db()->prepare('SELECT * FROM packages WHERE id = ?');
    $p->execute([$order['package_id']]);
    $pkg = $p->fetch();
    if (!$pkg) return;

    // هر خرید یک اشتراک مستقل و فعال می‌سازد (امکان چند بستهٔ همزمان)
    $stmt = db()->prepare(
        'INSERT INTO subscriptions
         (user_id, package_id, order_id, title, total_volume_gb, used_volume_gb, total_days, start_at, end_at, active)
         VALUES (?, ?, ?, ?, ?, 0, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 1)'
    );
    $stmt->execute([
        $order['user_id'], $pkg['id'], $orderId, $pkg['title'],
        $pkg['volume_gb'], $pkg['duration_days'], $pkg['duration_days'],
    ]);
}

// ---------- آپلود تصویر ----------
function handle_image_upload(string $field, string $destDir): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES[$field]['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) {
        return null;
    }
    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destDir . '/' . $name)) {
        return $name;
    }
    return null;
}
