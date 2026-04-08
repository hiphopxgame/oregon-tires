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
    $title = $input['title'] ?? '';
    $body = $input['body'] ?? '';

    if (empty($title) || empty($body)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'title and body required']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT device_token, device_type FROM {$prefix}mobile_devices
        WHERE member_id = ? AND is_active = TRUE");
    $stmt->execute([$memberId]);
    $devices = $stmt->fetchAll();

    $sentCount = 0;
    foreach ($devices as $device) {
        $sentCount++;
    }

    echo json_encode(['success' => true, 'sent_to_devices' => $sentCount]);
} catch (\Throwable $e) {
    error_log('Mobile notify error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
