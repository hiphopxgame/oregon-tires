<?php
declare(strict_types=1);

/**
 * POST /api/member/login.php
 * Authenticate with email + password
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

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF check
$csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$returnUrl = trim($input['return_url'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
    exit;
}

// Rate limit check
if (!MemberAuth::checkRateLimit($email)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many login attempts. Please wait 15 minutes.']);
    exit;
}

$member = MemberAuth::login($email, $password);

if ($member === false) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    exit;
}

// Check if email is verified (independent mode)
if (!MemberAuth::isHwMode() && ($member['status'] ?? '') === 'unverified') {
    MemberAuth::logout();
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error'   => 'Please verify your email address before logging in',
        'unverified' => true,
    ]);
    exit;
}

MemberProfile::logActivity((int) $member['id'], 'login', null, null, [
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
]);

$isAdmin = (bool) ($member['is_admin'] ?? false);

if (MemberAuth::isHwMode()) {
    // HW mode (hiphop.id): must establish a hub session on hiphop.world via SSO hop.
    // Even for hub-destined URLs, hiphop.id has a different session domain than hiphop.world.
    $hubUrl = rtrim($_ENV['HH_HUB_URL'] ?? 'https://hiphop.world', '/');

    // Validate return URL: only relative paths or URLs on the hub domain
    $validReturn = '';
    if ($returnUrl !== '') {
        $isRelative = str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//');
        $isHubUrl   = str_starts_with($returnUrl, $hubUrl . '/');
        if ($isRelative || $isHubUrl) {
            $validReturn = $returnUrl;
        }
    }
    $destination = $validReturn !== '' ? $validReturn : ($isAdmin ? '/admin' : '/member/profile');

    // Generate single-use SSO token (300s TTL) in shared engine_sso_tokens table
    $pdo    = MemberAuth::getPdo();
    $userId = (int) $member['id'];
    $token  = bin2hex(random_bytes(32));
    $pdo->prepare("DELETE FROM engine_sso_tokens WHERE expires_at < NOW()")->execute();
    $pdo->prepare("INSERT INTO engine_sso_tokens (token, user_id, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 300 SECOND))")
        ->execute([$token, $userId]);

    // Build SSO hop: hiphop.world/sso validates token, creates hub session, redirects to $destination
    $redirectUrl = $hubUrl . '/sso?token=' . $token . '&return=' . urlencode($destination);

} else {
    // Independent mode: return_url must be a same-site relative path only
    $isRelative = str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//');
    $redirectUrl = ($returnUrl !== '' && $isRelative)
        ? $returnUrl
        : ($isAdmin ? '/admin' : '/members');
}

echo json_encode([
    'success'          => true,
    'member'           => [
        'id'           => (int) $member['id'],
        'email'        => $member['email'],
        'username'     => $member['username'] ?? null,
        'display_name' => $member['display_name'] ?? null,
        'avatar_url'   => $member['avatar_url'] ?? null,
        'is_admin'     => $isAdmin,
    ],
    'redirect'         => $redirectUrl,
    'server_validated' => true,
]);
