<?php
require_once __DIR__ . '/../backend/core/helpers.php';

// آقای پرداخت پس از پرداخت، transid و وضعیت را برمی‌گرداند
// چون callback_method = GET ارسال می‌شود، پارامترها از GET می‌آیند
$transid     = $_GET['transid']     ?? $_POST['transid']     ?? '';
$gatewayStat = $_GET['status']      ?? $_POST['status']      ?? '';

if ($transid === '') {
    flash('error', 'اطلاعات بازگشتی از درگاه ناقص است.');
    redirect('panel/result.php?st=invalid');
}

$stmt = db()->prepare('SELECT * FROM orders WHERE transid = ? LIMIT 1');
$stmt->execute([$transid]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'سفارش متناظر یافت نشد.');
    redirect('panel/result.php?st=invalid');
}

// اگر قبلاً پرداخت شده، دوباره فعال نکن
if ($order['status'] === 'paid') {
    redirect('panel/result.php?order=' . $order['id'] . '&st=already');
}

$pin = aqaye_cfg('pin');

// اگر کاربر پرداخت را لغو کرده باشد
if ($gatewayStat !== '' && $gatewayStat !== '1' && strtolower((string)$gatewayStat) !== 'success') {
    db()->prepare("UPDATE orders SET status = 'failed' WHERE id = ?")->execute([$order['id']]);
    redirect('panel/result.php?order=' . $order['id'] . '&st=failed');
}

// تأیید تراکنش
$payload = json_encode([
    'pin'     => $pin,
    'amount'  => (int)$order['amount'],
    'transid' => $transid,
]);

$ch = curl_init(aqaye_cfg('verify_url'));
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
curl_close($ch);

$data = json_decode($res, true);
$ok = is_array($data) && (($data['status'] ?? '') === 'success' || (string)($data['code'] ?? '') === '1');

if (!$ok) {
    db()->prepare("UPDATE orders SET status = 'failed' WHERE id = ?")->execute([$order['id']]);
    redirect('panel/result.php?order=' . $order['id'] . '&st=failed');
}

// موفق: سفارش را پرداخت‌شده کن و اشتراک بساز
activate_order((int)$order['id']);
redirect('panel/result.php?order=' . $order['id'] . '&st=ok');
