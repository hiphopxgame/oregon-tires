<?php
declare(strict_types=1);
if (!function_exists('getDatabase')) { require_once __DIR__ . '/../../config/database.php'; }
if (!defined('MEMBER_KIT_PATH')) { require_once __DIR__ . '/../../loader.php'; }
initSession();
MemberAuth::init(getDatabase());
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
try {
    $member = MemberAuth::requireAuth();
    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];
    $pdo = getDatabase();
    $table = MemberAuth::prefixedTable('sessions');
    $stmt = $pdo->prepare("SELECT id, device_id, device_name, device_fingerprint, ip_address, user_agent, trusted, created_at, last_activity, expires_at FROM {$table} WHERE member_id = ? AND expires_at > NOW() ORDER BY last_activity DESC LIMIT 50");
    $stmt->execute([$memberId]);
    $sessions = $stmt->fetchAll();
    $data = [];
    foreach ($sessions as $s) {
        $data[] = [
            'id' => (int) $s['id'],
            'device_id' => $s['device_id'],
            'device_name' => $s['device_name'] ?? null,
            'ip_address' => $s['ip_address'] ?? 'unknown',
            'user_agent' => $s['user_agent'] ?? null,
            'trusted' => (bool) $s['trusted'],
            'created_at' => $s['created_at'],
            'last_activity' => $s['last_activity'],
            'expires_at' => $s['expires_at'],
        ];
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (\RuntimeException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log('Devices list error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not retrieve devices']);
}
