<?php
/**
 * Oregon Tires — Admin Google OAuth Initiator
 * Redirects to Google's OAuth consent screen with PKCE.
 * Sets session flag so the shared callback knows this is an admin flow.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

startSecureSession();

$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
if (empty($clientId)) {
    error_log('Admin Google OAuth: GOOGLE_CLIENT_ID not configured');
    header('Location: /admin/?error=google_not_configured');
    exit;
}

// Use the SAME redirect URI registered in Google Console
$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? 'https://oregon.tires/api/auth/google-callback.php';

// Mark this as an admin OAuth flow
$mode = $_GET['mode'] ?? 'login';
$_SESSION['google_oauth_mode'] = in_array($mode, ['admin_login', 'admin_connect'], true)
    ? $mode
    : ($mode === 'connect' ? 'admin_connect' : 'admin_login');

// Generate CSRF state token
$state = bin2hex(random_bytes(32));
$_SESSION['google_oauth_state'] = $state;

// Generate PKCE code verifier + challenge
$codeVerifier = bin2hex(random_bytes(32));
$_SESSION['google_code_verifier'] = $codeVerifier;
$codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

$params = http_build_query([
    'client_id'             => $clientId,
    'redirect_uri'          => $redirectUri,
    'response_type'         => 'code',
    'scope'                 => 'openid email profile',
    'state'                 => $state,
    'code_challenge'        => $codeChallenge,
    'code_challenge_method' => 'S256',
    'access_type'           => 'online',
    'prompt'                => 'select_account',
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
