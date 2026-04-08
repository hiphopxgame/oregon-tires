<?php
declare(strict_types=1);

/**
 * POST /api/member/reset-password.php
 * Complete password reset with token
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF check
$csrfToken = $input['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$token = $input['token'] ?? '';
$newPassword = $input['password'] ?? '';
$passwordConfirm = $input['password_confirm'] ?? '';

if ($token === '' || $newPassword === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token and new password are required']);
    exit;
}

if ($passwordConfirm !== '' && $newPassword !== $passwordConfirm) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
    exit;
}

try {
    // Verify token exists and is valid before attempting reset
    $tokenHash = hash('sha256', $token);
    $prTable = MemberAuth::prefixedTable('password_resets');
    $stmt = MemberAuth::getPdo()->prepare(
        "SELECT * FROM {$prTable}
         WHERE token_hash = ? AND expires_at > NOW() AND used_at IS NULL
         LIMIT 1"
    );
    $stmt->execute([$tokenHash]);
    $resetRecord = $stmt->fetch();

    if (!$resetRecord) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired reset link. Please request a new one.']);
        exit;
    }

    // Reset password with validated token
    $result = MemberAuth::resetPassword($token, $newPassword);

    if (!$result) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired reset link. Please request a new one.']);
        exit;
    }

    echo json_encode([
        'success'  => true,
        'message'  => 'Password has been reset. You can now log in.',
        'redirect' => '/member/login',
    ]);
} catch (\RuntimeException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log('Password reset error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Password reset failed']);
}
