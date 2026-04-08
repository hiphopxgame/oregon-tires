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
    $deviceFingerprint = $input['device_fingerprint'] ?? '';
    $geoLocation = $input['geo_location'] ?? '';

    $stmt = $pdo->prepare("SELECT device_fingerprint, geo_location FROM {$prefix}login_anomalies
        WHERE member_id = ? ORDER BY login_timestamp DESC LIMIT 5");
    $stmt->execute([$memberId]);
    $recentLogins = $stmt->fetchAll();

    $suspicious = false;
    $reasons = [];

    $devicesSeen = array_column($recentLogins, 'device_fingerprint');
    if (!in_array($deviceFingerprint, $devicesSeen)) {
        $suspicious = true;
        $reasons[] = 'new_device';
    }

    $locationsSeen = array_column($recentLogins, 'geo_location');
    if (!in_array($geoLocation, $locationsSeen)) {
        $suspicious = true;
        $reasons[] = 'new_location';
    }

    $requireVerification = $suspicious && count($reasons) >= 2;

    echo json_encode([
        'success' => true,
        'data' => [
            'suspicious' => $suspicious,
            'reasons' => $reasons,
            'require_additional_verification' => $requireVerification
        ]
    ]);
} catch (\Throwable $e) {
    error_log('Anomaly check error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
