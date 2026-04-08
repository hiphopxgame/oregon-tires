<?php
declare(strict_types=1);

/**
 * POST /api/member/session-extend.php
 * Session Extension — Prevent session timeout
 *
 * Request body:
 *   { "csrf_token": "..." }
 *
 * Response:
 *   { "success": true, "new_expires": 1708876800 }
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

try {
    // Parse JSON body (also accept POST form data)
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';

    // CSRF check
    if (!MemberAuth::verifyCsrf($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    // Check authentication
    $member = MemberAuth::getCurrentMember();
    if (!$member) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }

    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];
    $pdo = getDatabase();
    $prefix = MemberAuth::prefixedTable('');

    // Extend session lifetime
    $sessionLifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 86400);
    $newExpires = time() + $sessionLifetime;

    // Update last activity time in session
    $_SESSION['_last_activity'] = time();

    // If using database-backed sessions, update expiry there too
    try {
        $stmt = $pdo->prepare("
            UPDATE {$prefix}sessions
            SET expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND)
            WHERE member_id = ?
            LIMIT 1
        ");
        $stmt->execute([$sessionLifetime, $memberId]);
    } catch (\Throwable $e) {
        // Table may not exist in all implementations, silently continue
        error_log('Session update error (non-critical): ' . $e->getMessage());
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'new_expires' => $newExpires,
        'message' => 'Session extended'
    ]);

} catch (\Throwable $e) {
    error_log('Session extend error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Could not extend session'
    ]);
}
