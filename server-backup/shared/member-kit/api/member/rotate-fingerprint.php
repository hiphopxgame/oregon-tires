<?php
declare(strict_types=1);
if (!function_exists('getDatabase')) { require_once __DIR__ . '/../../config/database.php'; }
if (!defined('MEMBER_KIT_PATH')) { require_once __DIR__ . '/../../loader.php'; }
initSession();
MemberAuth::init(getDatabase());
header('Content-Type: application/json');

try {
    $member = MemberAuth::requireAuth();
    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];
    $pdo = getDatabase();
    $prefix = MemberAuth::getTablePrefix();

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $currentFingerprint = $input['fingerprint'] ?? '';

    if (empty($currentFingerprint)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'fingerprint required']);
        exit;
    }

    $sessionId = session_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $stmt = $pdo->prepare("SELECT current_fingerprint FROM {$prefix}fingerprint_rotation
        WHERE member_id = ? ORDER BY rotated_at DESC LIMIT 1");
    $stmt->execute([$memberId]);
    $prevRow = $stmt->fetch();
    $previousFingerprint = $prevRow ? $prevRow['current_fingerprint'] : null;

    $stmt = $pdo->prepare("INSERT INTO {$prefix}fingerprint_rotation
        (member_id, session_id, current_fingerprint, previous_fingerprint, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->execute([$memberId, $sessionId, $currentFingerprint, $previousFingerprint, $ip, $userAgent]);

    echo json_encode(['success' => true, 'message' => 'Fingerprint rotated']);
} catch (\Throwable $e) {
    error_log('Rotate fingerprint error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
