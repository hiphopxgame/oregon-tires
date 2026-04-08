<?php
declare(strict_types=1);

/**
 * GET /api/member/sso.php
 * Redirect to OAuth authorize URL for SSO
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

if (!MemberSSO::isEnabled()) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'SSO is not configured for this site']);
    exit;
}

$returnUrl = $_GET['return'] ?? $_SERVER['HTTP_REFERER'] ?? '/member/profile';

try {
    $authorizeUrl = MemberSSO::getAuthorizeUrl($returnUrl);
    header('Location: ' . $authorizeUrl);
    exit;
} catch (\Throwable $e) {
    error_log('SSO redirect error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'SSO initialization failed']);
}
