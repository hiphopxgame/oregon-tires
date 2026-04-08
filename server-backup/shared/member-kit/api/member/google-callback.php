<?php
declare(strict_types=1);

/**
 * GET /api/member/google-callback.php
 * Google OAuth callback — handles connect mode (link Google to existing account)
 *
 * For login mode, sites should use their own site-level callback that calls
 * MemberGoogle::exchangeCodeForProfile() and handles user creation/login.
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

// Check for errors from Google
if (!empty($_GET['error'])) {
    error_log('Google OAuth error: ' . ($_GET['error_description'] ?? $_GET['error']));
    header('Location: /members?tab=account&error=' . urlencode('Google sign-in was cancelled or denied.'));
    exit;
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    error_log('Google OAuth: missing code or state');
    header('Location: /members?tab=account&error=' . urlencode('Invalid response from Google. Please try again.'));
    exit;
}

try {
    $result = MemberGoogle::exchangeCodeForProfile($_GET['code'], $_GET['state']);
    $profile = $result['profile'];
    $mode = $result['mode'];
    $returnUrl = $result['return_url'];

    if ($mode === 'connect') {
        // Connect mode: link Google to current logged-in member
        if (!MemberAuth::isLoggedIn()) {
            header('Location: /members?error=' . urlencode('Session expired. Please sign in and try again.'));
            exit;
        }

        $member = MemberAuth::getCurrentMember();
        if (!$member) {
            header('Location: /members?error=' . urlencode('Session expired. Please sign in and try again.'));
            exit;
        }

        MemberGoogle::linkAccount((int) $member['id'], $profile['sub'], $profile['email'] ?? null, $profile['picture'] ?? null);

        $redirect = $returnUrl ?? '/members?tab=account';
        if (!str_contains($redirect, 'success=')) {
            $separator = str_contains($redirect, '?') ? '&' : '?';
            $redirect .= $separator . 'success=' . urlencode('Google account connected!');
        }
        header('Location: ' . $redirect);
        exit;
    }

    // Login mode: redirect to site-level login callback (sites handle login differently)
    // This fallback handles simple sites that don't have their own login callback
    header('Location: /members?error=' . urlencode('Use the Google sign-in button on the login page.'));
    exit;

} catch (\Throwable $e) {
    error_log('Google OAuth callback error: ' . $e->getMessage());
    $errorMsg = str_contains($e->getMessage(), 'already linked')
        ? $e->getMessage()
        : 'Google sign-in failed. Please try again.';
    header('Location: /members?tab=account&error=' . urlencode($errorMsg));
    exit;
}
