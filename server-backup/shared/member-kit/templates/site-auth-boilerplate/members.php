<?php
/**
 * /members — Login & Dashboard page
 *
 * - Logged out: shows login form with site branding
 * - Logged in: shows member dashboard
 * - Honors ?return= for post-login redirect
 *
 * Copy this file to your site's public_html/members.php
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

// ── Logged-in: Dashboard ─────────────────────────────────────────────────────
if (!empty($_SESSION[$_ENV['MEMBER_SESSION_KEY'] ?? 'member_id'])) {
    // Honor ?return= parameter for post-login redirect
    if (!empty($_GET['return'])) {
        $returnUrl = $_GET['return'];
        // Only allow relative paths (prevent open redirect)
        if (str_starts_with($returnUrl, '/')) {
            header('Location: ' . $returnUrl);
            exit;
        }
    }

    $pageTitle = 'Dashboard — ' . ($_ENV['SITE_NAME'] ?? 'Members');
    // Load dashboard template (customize as needed)
    include __DIR__ . '/templates/member-dashboard.php';
    exit;
}

// ── Logged-out: Login Form ───────────────────────────────────────────────────

// Capture return URL
if (!empty($_GET['return'])) {
    $returnParam = $_GET['return'];
    if (str_starts_with($returnParam, '/')) {
        $_SESSION['login_return_url'] = $returnParam;
    }
}

// Capture referral code
if (!empty($_GET['ref'])) {
    $_SESSION['referral_code'] = trim($_GET['ref']);
}

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

$pageTitle = 'Log In — ' . ($_ENV['SITE_NAME'] ?? 'Members');
$loginError = $error;

// Render login template
include __DIR__ . '/templates/member-login.php';
