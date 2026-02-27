<?php
declare(strict_types=1);

/**
 * Oregon Tires — Google OAuth callback for connect mode
 *
 * Handles the Google OAuth callback when linking Google to an existing member account.
 * Login-mode callbacks go through /api/auth/google-callback.php instead.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

// Check for errors from Google
if (!empty($_GET['error'])) {
    error_log('Google OAuth connect error: ' . ($_GET['error_description'] ?? $_GET['error']));
    header('Location: /members?tab=settings&error=' . urlencode('Google sign-in was cancelled or denied.'));
    exit;
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    header('Location: /members?tab=settings&error=' . urlencode('Invalid response from Google.'));
    exit;
}

try {
    $result = MemberGoogle::exchangeCodeForProfile($_GET['code'], $_GET['state']);
    $profile = $result['profile'];
    $mode = $result['mode'];
    $returnUrl = $result['return_url'];

    if ($mode === 'connect') {
        if (!MemberAuth::isMemberLoggedIn()) {
            header('Location: /members?error=' . urlencode('Session expired. Please sign in and try again.'));
            exit;
        }
        $memberId = (int) $_SESSION['member_id'];
        MemberGoogle::linkAccount($memberId, $profile['sub'], $profile['email'] ?? null);

        $redirect = $returnUrl ?? '/members?tab=settings';
        $sep = str_contains($redirect, '?') ? '&' : '?';
        header('Location: ' . $redirect . $sep . 'success=' . urlencode('Google account connected!'));
        exit;
    }

    // Login mode shouldn't reach here (should go to /api/auth/google-callback.php)
    header('Location: /api/auth/google-callback.php?' . $_SERVER['QUERY_STRING']);
    exit;

} catch (\Throwable $e) {
    error_log('Google connect callback error: ' . $e->getMessage());
    $msg = str_contains($e->getMessage(), 'already linked') ? $e->getMessage() : 'Failed to connect Google. Please try again.';
    header('Location: /members?tab=settings&error=' . urlencode($msg));
    exit;
}
