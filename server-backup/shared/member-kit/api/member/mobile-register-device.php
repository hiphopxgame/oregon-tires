<?php
declare(strict_types=1);
if (!function_exists('getDatabase')) { require_once __DIR__ . '/../../config/database.php'; }
if (!defined('MEMBER_KIT_PATH')) { require_once __DIR__ . '/../../loader.php'; }
initSession();
MemberAuth::init(getDatabase());
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $member = MemberAuth::requireAuth();
    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];
    $pdo = getDatabase();
    $prefix = MemberAuth::getTablePrefix();

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $deviceToken = $input['device_token'] ?? '';
    $deviceType = $input['device_type'] ?? '';
    $deviceName = $input['device_name'] ?? '';

    if (empty($deviceToken) || empty($deviceType)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'device_token and device_type required']);
        exit;
    }

    if (!in_array($deviceType, ['ios', 'android', 'web'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid device_type']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO {$prefix}mobile_devices
        (member_id, device_token, device_type, device_name)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE is_active = TRUE, last_activity = NOW()");

    $stmt->execute([$memberId, $deviceToken, $deviceType, $deviceName]);

    echo json_encode(['success' => true, 'message' => 'Device registered']);
} catch (\Throwable $e) {
    error_log('Mobile register device error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
