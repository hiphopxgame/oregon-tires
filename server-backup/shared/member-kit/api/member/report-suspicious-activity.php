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
    $activityType = $input['activity_type'] ?? '';

    if (empty($activityType)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'activity_type required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO {$prefix}login_anomalies
        (member_id, anomaly_reason, require_additional_verification)
        VALUES (?, ?, TRUE)");

    $stmt->execute([$memberId, $activityType]);

    echo json_encode(['success' => true, 'message' => 'Suspicious activity reported']);
} catch (\Throwable $e) {
    error_log('Report suspicious activity error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
