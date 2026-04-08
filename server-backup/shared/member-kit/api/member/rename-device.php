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
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}
try {
    $member = MemberAuth::requireAuth();
    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];
    $deviceId = trim($input['device_id'] ?? $_POST['device_id'] ?? '');
    $deviceName = trim($input['device_name'] ?? $_POST['device_name'] ?? '');
    if ($deviceId === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'device_id is required']);
        exit;
    }
    if (strlen($deviceName) > 64) { $deviceName = substr($deviceName, 0, 64); }
    $pdo = getDatabase();
    $table = MemberAuth::prefixedTable('sessions');
    $stmt = $pdo->prepare("UPDATE {$table} SET device_name = ? WHERE device_id = ? AND member_id = ?");
    $stmt->execute([$deviceName ?: null, $deviceId, $memberId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Device not found']);
        exit;
    }
    MemberProfile::logActivity($memberId, 'device_renamed', ['device_id' => $deviceId]);
    echo json_encode(['success' => true]);
} catch (\RuntimeException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log('Rename device error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not rename device']);
}
