<?php
declare(strict_types=1);

/**
 * GET /api/member/sso-callback.php
 * Handle OAuth callback from SSO provider
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

$config = MemberAuth::getConfig();
$loginUrl = $config['login_url'] ?: ($config['site_url'] . '/member/login');

// Check for error from OAuth provider
if (!empty($_GET['error'])) {
    $errorDesc = $_GET['error_description'] ?? $_GET['error'];
    error_log('SSO callback error: ' . $errorDesc);
    header('Location: ' . $loginUrl . '?error=' . urlencode('SSO authorization was denied'));
    exit;
}

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if ($code === '' || $state === '') {
    header('Location: ' . $loginUrl . '?error=' . urlencode('Invalid SSO callback'));
    exit;
}

try {
    $member = MemberSSO::handleCallback($code, $state);

    // Start authenticated session (fires onLogin hook for site-specific session keys)
    MemberAuth::startAuthenticatedSession($member);

    // Log activity
    MemberProfile::logActivity((int) $member['id'], 'sso_login', null, null, [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);

    // Report to HW hub if sync is enabled
    $hwUserId = (int) ($member['hw_user_id'] ?? $member['id'] ?? 0);
    if ($hwUserId && MemberSync::isEnabled()) {
        MemberSync::reportActivity($hwUserId, $config['site_url'], 'sso_login');
    }

    // Redirect to return URL or site's login page (which shows dashboard when logged in)
    $defaultReturn = $config['login_url'] ?: '/members';
    $returnUrl = $_SESSION['oauth_return'] ?? $defaultReturn;
    unset($_SESSION['oauth_return']);

    // Validate return URL is same-origin
    $siteHost = parse_url($config['site_url'], PHP_URL_HOST);
    $returnHost = parse_url($returnUrl, PHP_URL_HOST);
    if ($returnHost && $returnHost !== $siteHost) {
        $returnUrl = $defaultReturn;
    }

    header('Location: ' . $returnUrl);
    exit;
} catch (\Throwable $e) {
    error_log('SSO callback failed: ' . $e->getMessage());
    // Forward user-friendly SSO errors (session expired, CSRF) as-is
    $msg = str_contains($e->getMessage(), 'session expired') || str_contains($e->getMessage(), 'try logging in')
        ? $e->getMessage()
        : 'SSO login failed. Please try again.';
    header('Location: ' . $loginUrl . '?error=' . urlencode($msg));
    exit;
}
