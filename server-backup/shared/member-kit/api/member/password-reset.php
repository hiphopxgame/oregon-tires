<?php
declare(strict_types=1);

/**
 * POST /api/member/password-reset.php
 * Request a password reset link via email
 *
 * Rate limited: 3 requests per hour per email address
 * Security: Never reveals whether an email exists (prevents enumeration)
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

if ($email === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email address is required']);
    exit;
}

// Rate limiting: 3 requests per hour per email
// Using generic action rate_limit check with 3600 seconds (1 hour) window
if (!MemberAuth::checkActionRateLimit('password_reset_' . md5($email), 3, 3600)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error'   => 'Too many password reset requests. Please try again in 1 hour.',
        'retry_after' => 3600
    ]);
    exit;
}

// Always return success to prevent email enumeration
// But only process if user exists
$table = MemberAuth::getMembersTable();
$stmt = MemberAuth::getPdo()->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$member = $stmt->fetch();

if ($member) {
    // Member exists, generate token and send password reset email via sendPasswordReset
    try {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30 minutes

        // Invalidate existing reset tokens
        $stmt = MemberAuth::getPdo()->prepare(
            "UPDATE password_resets SET used_at = NOW() WHERE member_id = ? AND used_at IS NULL"
        );
        $stmt->execute([(int) $member['id']]);

        // Store new reset token
        $stmt = MemberAuth::getPdo()->prepare(
            "INSERT INTO password_resets (member_id, token_hash, expires_at, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([(int) $member['id'], $tokenHash, $expiresAt]);

        // Send password reset email via sendPasswordReset
        $config = MemberAuth::getConfig();
        MemberMail::sendPasswordReset(
            $member['email'],
            $token,
            $config['site_name'] ?? 'Site',
            $config['site_url'] ?? ''
        );

        // Log activity
        MemberProfile::logActivity((int) $member['id'], 'password_reset_requested');
    } catch (\Throwable $e) {
        // Log but don't expose error details
        error_log('Password reset request failed: ' . $e->getMessage());
    }
}

// Always return success response (don't reveal if email exists)
echo json_encode([
    'success' => true,
    'message' => 'If an account with that email exists, you will receive a password reset link shortly.'
]);
