<?php
declare(strict_types=1);

/**
 * Oregon Tires — Google OAuth redirect wrapper
 *
 * Connect mode: uses member-kit's MemberGoogle flow to link Google to existing account.
 * Login mode: forwards to site-level /api/auth/google.php for Google sign-in/registration.
 */

$mode = $_GET['mode'] ?? 'login';

if ($mode === 'connect') {
    // Connect mode: use member-kit's MemberGoogle flow
    require_once __DIR__ . '/../../includes/bootstrap.php';
    require_once __DIR__ . '/../../includes/member-kit-init.php';
    startSecureSession();
    $pdo = getDB();
    initMemberKit($pdo);

    if (!MemberAuth::isLoggedIn()) {
        header('Location: /members?error=' . urlencode('Please sign in first to connect Google.'));
        exit;
    }
    if (!MemberGoogle::isEnabled()) {
        header('Location: /members?tab=settings&error=' . urlencode('Google OAuth is not configured.'));
        exit;
    }

    $returnUrl = $_GET['return'] ?? '/members?tab=settings';
    $url = MemberGoogle::getAuthorizeUrl($returnUrl, 'connect');
    header('Location: ' . $url);
    exit;
}

// Login mode: forward to existing site-level Google OAuth
header('Location: /api/auth/google.php?' . ($_SERVER['QUERY_STRING'] ?? ''));
exit;
