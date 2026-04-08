<?php
declare(strict_types=1);

/**
 * POST /api/member/logout.php
 * Destroy session and log out
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

// Check if user is logged in
$memberId = $_SESSION['member_id'] ?? null;
if (!$memberId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Verify CSRF token from request body
try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? null;

    if (!$csrfToken || !MemberAuth::verifyCsrf($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Log activity before logout
if ($memberId) {
    MemberProfile::logActivity((int) $memberId, 'logout');
}

MemberAuth::logout();

echo json_encode([
    'success'  => true,
    'message'  => 'Logged out successfully',
    'redirect' => '/',
]);
