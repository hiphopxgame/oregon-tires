<?php
declare(strict_types=1);

/**
 * PUT /api/member/preferences.php
 * Update member notification preferences.
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

MemberAuth::requireAuth();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF check
$csrfToken = $input['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$memberId = $_SESSION['member_id'] ?? null;
if (!$memberId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    $pdo = getDatabase();

    $marketingEmails = !empty($input['marketing_emails']) ? 1 : 0;
    $digestFrequency = in_array($input['digest_frequency'] ?? '', ['never', 'daily', 'weekly'], true)
        ? $input['digest_frequency']
        : 'never';

    $stmt = $pdo->prepare("
        UPDATE members SET
            marketing_emails = ?,
            digest_frequency = ?
        WHERE id = ?
    ");
    $stmt->execute([$marketingEmails, $digestFrequency, $memberId]);

    echo json_encode([
        'success' => true,
        'message' => 'Preferences saved.',
    ]);
} catch (\Throwable $e) {
    error_log('Preferences update error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save preferences.']);
}
