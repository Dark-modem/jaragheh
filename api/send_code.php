<?php
require_once __DIR__ . '/../backend/core/helpers.php';
header('Content-Type: application/json; charset=utf-8');

$me = current_user();
if (!$me) { http_response_code(401); echo json_encode(['ok' => false, 'msg' => 'ابتدا وارد شوید']); exit; }

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(419); echo json_encode(['ok' => false, 'msg' => 'نشست منقضی شده']); exit;
}

$type = $_POST['type'] ?? '';
if (!in_array($type, ['email', 'phone'], true)) {
    echo json_encode(['ok' => false, 'msg' => 'نوع نامعتبر']); exit;
}

$col = $type === 'email' ? 'email_verified' : 'phone_verified';
if ((int)$me[$col] === 1) {
    echo json_encode(['ok' => false, 'msg' => 'این مورد قبلاً تأیید شده است']); exit;
}

issue_verification((int)$me['id'], $type);

$resp = ['ok' => true, 'msg' => 'کد ارسال شد'];
if (DEV_MODE && !empty($_SESSION['dev_last_code'])) {
    $resp['dev_code'] = $_SESSION['dev_last_code'];
}
echo json_encode($resp);
