<?php
declare(strict_types=1);

/**
 * POST /api/member/forgot-password.php
 * Request a password reset link
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

$email = trim($input['email'] ?? '');

if ($email === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email is required']);
    exit;
}

try {
    MemberAuth::requestPasswordReset($email);

    // Always return success to not leak email existence
    echo json_encode([
        'success' => true,
        'message' => 'If an account with that email exists, a password reset link has been sent.',
    ]);
} catch (\Throwable $e) {
    error_log('Password reset request error: ' . $e->getMessage());
    // Still return success to not leak information
    echo json_encode([
        'success' => true,
        'message' => 'If an account with that email exists, a password reset link has been sent.',
    ]);
}
