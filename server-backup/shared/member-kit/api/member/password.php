<?php
declare(strict_types=1);

/**
 * PUT /api/member/password.php
 * Change password (requires current password)
 */

// Bootstrap: skip if already loaded by a site wrapper
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('MEMBER_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}
initSession();
MemberAuth::init(getDatabase());

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$member = MemberAuth::requireAuth();
$memberId = (int) $member['id'];

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF check
$csrfToken = $input['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';
$passwordConfirm = $input['password_confirm'] ?? '';

if ($currentPassword === '' || $newPassword === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Current and new passwords are required']);
    exit;
}

if ($passwordConfirm !== '' && $newPassword !== $passwordConfirm) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'New passwords do not match']);
    exit;
}

try {
    $result = MemberAuth::changePassword($memberId, $currentPassword, $newPassword);

    if (!$result) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} catch (\RuntimeException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log('Password change error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Password change failed']);
}
