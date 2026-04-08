<?php
declare(strict_types=1);

/**
 * GET /api/member/discord.php
 * Redirect to Discord OAuth authorize URL
 *
 * Query params:
 *   mode=login|connect
 *   return=<url>
 */

if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('MEMBER_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}
if (function_exists('initSession')) { initSession(); }
elseif (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
MemberAuth::init(getDatabase());

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!MemberDiscord::isEnabled()) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Discord OAuth is not configured']);
    exit;
}

$mode = ($_GET['mode'] ?? 'login') === 'connect' ? 'connect' : 'login';
$returnUrl = $_GET['return'] ?? null;

if ($mode === 'connect' && !MemberAuth::isLoggedIn()) {
    header('Location: /members?error=' . urlencode('Please sign in first to connect Discord.'));
    exit;
}

try {
    $url = MemberDiscord::getAuthorizeUrl($returnUrl, $mode);
    header('Location: ' . $url);
    exit;
} catch (\Throwable $e) {
    error_log('MemberDiscord redirect failed: ' . $e->getMessage());
    header('Location: /members?error=' . urlencode('Could not start Discord sign-in. Please try again.'));
    exit;
}
