<?php
/**
 * Oregon Tires — Google OAuth Initiator
 * Redirects to Google's OAuth consent screen with PKCE.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

startSecureSession();

// Verify Google OAuth is configured
$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
if (empty($clientId)) {
    error_log('Google OAuth: GOOGLE_CLIENT_ID not configured');
    header('Location: /members?error=google_not_configured');
    exit;
}

$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? 'https://oregon.tires/api/auth/google-callback.php';

// Save return URL in session
if (!empty($_GET['return'])) {
    $return = $_GET['return'];
    if (str_starts_with($return, '/')) {
        $_SESSION['google_return_url'] = $return;
    }
}

// Generate CSRF state token
$state = bin2hex(random_bytes(32));
$_SESSION['google_oauth_state'] = $state;

// Generate PKCE code verifier + challenge
$codeVerifier = bin2hex(random_bytes(32));
$_SESSION['google_code_verifier'] = $codeVerifier;
$codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

// Build Google auth URL
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
