<?php
declare(strict_types=1);

/**
 * GET /api/member/status.php
 * Returns current authentication state for the session.
 * Called by pdx-shared/partials/member_state.php (Alpine component).
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
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['authenticated' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!MemberAuth::isLoggedIn()) {
    echo json_encode(['authenticated' => false]);
    exit;
}

$member = MemberAuth::getCurrentMember();
if (!$member) {
    echo json_encode(['authenticated' => false]);
    exit;
}

echo json_encode([
    'authenticated' => true,
    'id'            => (int) $member['id'],
    'name'          => $member['display_name'] ?? $member['username'] ?? '',
    'email'         => $member['email'] ?? '',
    'avatar_url'    => $member['avatar_url'] ?? null,
]);
