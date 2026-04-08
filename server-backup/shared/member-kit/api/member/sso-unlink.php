<?php
declare(strict_types=1);

/**
 * POST /api/member/sso-unlink.php
 * Unlink SSO account (independent mode only, requires password set)
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

try {
    MemberSSO::unlinkAccount($memberId);

    echo json_encode([
        'success' => true,
        'message' => 'SSO account has been unlinked. You can still log in with your email and password.',
    ]);
} catch (\RuntimeException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log('SSO unlink error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to unlink SSO account']);
}
