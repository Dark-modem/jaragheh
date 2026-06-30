<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$me = require_login();

$orderId = (int)($_GET['order'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $me['id']]);
$order = $stmt->fetch();

if (!$order || $order['gateway'] !== 'aqayepardakht' || $order['status'] === 'paid') {
    flash('error', 'سفارش نامعتبر است.');
    redirect('panel/orders.php');
}

// ساخت آدرس بازگشت - با پشتیبانی از پراکسی معکوس (X-Forwarded-Proto)
$scheme = 'https'; // پیش‌فرض https برای هاست‌های ایرانی با SSL
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $scheme = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' ? 'https' : 'http';
} elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $scheme = 'https';
} elseif (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 80) {
    $scheme = 'http';
}

$base     = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL;
$callback = $base . '/payment/callback.php';

$pin = aqaye_cfg('pin');
$isSandbox = strtolower(trim($pin)) === 'sandbox';

$payload = json_encode([
    'pin'             => $pin,
    'amount'          => (int)$order['amount'],   // به تومان
    'callback'        => $callback,
    'callback_method' => 'GET',                    // توصیه آقای پرداخت
    'mobile'          => $me['phone'],
    'email'           => $me['email'],
    'invoice_id'      => $order['order_number'],
    'description'     => 'خرید بسته ' . $order['package_title'] . ' - جرقه',
]);

$ch = curl_init(aqaye_cfg('create_url'));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload),
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,   // سازگاری با هاست‌های ایرانی
    CURLOPT_SSL_VERIFYHOST => false,
]);

$res = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($res === false || empty($res)) {
    db()->prepare("UPDATE orders SET status = 'failed' WHERE id = ?")->execute([$order['id']]);
    flash('error', 'اتصال به درگاه پرداخت برقرار نشد. (cURL: ' . e($curlErr) . ')');
    redirect('panel/orders.php');
}

$data = json_decode($res, true);

if (is_array($data) && ($data['status'] ?? '') === 'success' && !empty($data['transid'])) {
    db()->prepare('UPDATE orders SET transid = ? WHERE id = ?')
        ->execute([$data['transid'], $order['id']]);

    // تعیین URL صفحه پرداخت (sandbox یا واقعی)
    if ($isSandbox) {
        $startPayUrl = 'https://panel.aqayepardakht.ir/startpay/sandbox/';
    } else {
        $startPayUrl = rtrim(aqaye_cfg('startpay_url'), '/') . '/';
    }

    header('Location: ' . $startPayUrl . urlencode($data['transid']));
    exit;
}

// خطا در ساخت تراکنش
db()->prepare("UPDATE orders SET status = 'failed' WHERE id = ?")->execute([$order['id']]);

$errCode = '';
if (is_array($data)) {
    $errCode = (string)($data['code'] ?? $data['status'] ?? 'نامشخص');
} else {
    $errCode = 'پاسخ نامعتبر از درگاه (HTTP ' . $httpCode . ')';
}

flash('error', 'اتصال به درگاه پرداخت ناموفق بود. کد خطا: ' . e($errCode));
redirect('panel/orders.php');
