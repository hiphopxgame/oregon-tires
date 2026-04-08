<?php
declare(strict_types=1);

/**
 * GET /api/member/google.php
 * Redirect to Google OAuth authorize URL
 *
 * Query params:
 *   mode=login|connect  — 'connect' requires authentication (links Google to existing account)
 *   return=<url>        — URL to redirect to after flow completes
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!MemberGoogle::isEnabled()) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Google OAuth is not configured']);
    exit;
}

$mode = ($_GET['mode'] ?? 'login') === 'connect' ? 'connect' : 'login';
$returnUrl = $_GET['return'] ?? null;

// If connect mode, require authentication
if ($mode === 'connect' && !MemberAuth::isLoggedIn()) {
    header('Location: /members?error=' . urlencode('Please sign in first to connect Google.'));
    exit;
}

try {
    $url = MemberGoogle::getAuthorizeUrl($returnUrl, $mode);
    header('Location: ' . $url);
    exit;
} catch (\Throwable $e) {
    error_log('MemberGoogle redirect failed: ' . $e->getMessage());
    header('Location: /members?error=' . urlencode('Could not start Google sign-in. Please try again.'));
    exit;
}
