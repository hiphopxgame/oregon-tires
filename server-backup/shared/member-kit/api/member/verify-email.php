<?php
declare(strict_types=1);

/**
 * GET /api/member/verify-email.php?token=...
 * Verify email address with token from email link
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

$token = $_GET['token'] ?? '';
$config = MemberAuth::getConfig();

if ($token === '') {
    header('Location: ' . $config['site_url'] . '/member/login?error=' . urlencode('Missing verification token'));
    exit;
}

try {
    $result = MemberAuth::verifyEmail($token);

    if ($result) {
        header('Location: ' . $config['site_url'] . '/member/login?verified=1');
    } else {
        header('Location: ' . $config['site_url'] . '/member/login?error=' . urlencode('Invalid or expired verification link'));
    }
} catch (\Throwable $e) {
    error_log('Email verification error: ' . $e->getMessage());
    header('Location: ' . $config['site_url'] . '/member/login?error=' . urlencode('Verification failed'));
}
