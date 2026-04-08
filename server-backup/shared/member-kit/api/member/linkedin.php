<?php
declare(strict_types=1);

if (!function_exists('getDatabase')) require_once __DIR__ . '/../../config/database.php';
if (!defined('MEMBER_KIT_PATH')) require_once __DIR__ . '/../../loader.php';
if (function_exists('initSession')) { initSession(); }
elseif (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
MemberAuth::init(getDatabase());

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!MemberLinkedIn::isEnabled()) {
    http_response_code(503);
    echo json_encode(['error' => 'LinkedIn OAuth is not configured']);
    exit;
}

$mode = ($_GET['mode'] ?? 'login') === 'connect' ? 'connect' : 'login';
$returnUrl = $_GET['return'] ?? null;

if ($mode === 'connect' && !MemberAuth::isLoggedIn()) {
    header('Location: /members?error=' . urlencode('Please sign in first.'));
    exit;
}

try {
    header('Location: ' . MemberLinkedIn::getAuthorizeUrl($returnUrl, $mode));
    exit;
} catch (\Throwable $e) {
    error_log('LinkedIn OAuth start failed: ' . $e->getMessage());
    header('Location: /members?error=' . urlencode('Could not start LinkedIn sign-in.'));
    exit;
}
