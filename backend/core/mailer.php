<?php
/**
 * ارسال کد تایید.
 * -----------------------------------------------------------------
 * در این نسخه پنل پیامک و سرویس ایمیل متصل نشده‌اند، بنابراین کد فقط
 * در یک فایل لاگ ذخیره می‌شود و در حالت توسعه (DEV_MODE) روی صفحه هم
 * به کاربر نشان داده می‌شود.
 *
 * برای اتصال واقعی، داخل همین تابع کد سرویس خود را اضافه کنید:
 *   - پیامک: مثلاً کاوه‌نگار / sms.ir / ملی‌پیامک (با curl به API آن‌ها)
 *   - ایمیل: تابع mail() یا کتابخانه PHPMailer روی SMTP هاست
 * -----------------------------------------------------------------
 */

function send_code(int $userId, string $type, string $code): void
{
    // مسیر لاگ - دو سطح بالاتر از backend/core به ریشه سایت
    $log = __DIR__ . '/../../uploads/codes.log';
    $line = date('Y-m-d H:i:s') . " | user=$userId | $type | code=$code" . PHP_EOL;
    @file_put_contents($log, $line, FILE_APPEND);

    if (DEV_MODE) {
        $_SESSION['dev_last_code'] = $code;
    }

    // ----- نمونه اتصال پیامک (نمونه کاوه‌نگار) -----
    // if ($type === 'phone') {
    //     $u = db()->prepare('SELECT phone FROM users WHERE id=?'); $u->execute([$userId]);
    //     $phone = $u->fetchColumn();
    //     $api = 'YOUR_API_KEY';
    //     $msg = "کد تایید جرقه: $code";
    //     @file_get_contents("https://api.kavenegar.com/v1/$api/sms/send.json?receptor=$phone&sender=PUBLIC&message=" . urlencode($msg));
    // }

    // ----- نمونه اتصال ایمیل -----
    // if ($type === 'email') {
    //     $u = db()->prepare('SELECT email FROM users WHERE id=?'); $u->execute([$userId]);
    //     $email = $u->fetchColumn();
    //     @mail($email, 'کد تایید جرقه', "کد تایید شما: $code", 'From: no-reply@yourdomain.ir');
    // }
}
