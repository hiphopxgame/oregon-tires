<?php
declare(strict_types=1);

/**
 * POST /api/member/magic-link.php
 * Passwordless Authentication — Send magic link to user email
 *
 * Request body:
 *   { "email": "user@example.com", "csrf_token": "..." }
 *
 * Response:
 *   { "success": true, "message": "Check your email for a sign-in link" }
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
$csrfToken = $input['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$email = trim($input['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

try {
    // Rate limit check: 3 requests per hour per email
    $pdo = getDatabase();
    $prefix = MemberAuth::prefixedTable('');

    // Check rate limit
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM {$prefix}rate_limit_actions
        WHERE action = 'magic_link'
        AND identifier = ?
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([$email]);
    $result = $stmt->fetch();

    if (($result['count'] ?? 0) >= 3) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Too many magic link requests. Please try again in 1 hour.'
        ]);
        exit;
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 minutes

    // Store in database
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}magic_link_tokens
        (email, token_hash, expires_at, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$email, $tokenHash, $expiresAt]);

    // Log rate limit action
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}rate_limit_actions
        (action, identifier, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute(['magic_link', $email]);

    // Send email with magic link
    $magicLink = sprintf(
        '%s/api/member/magic-link-verify.php?token=%s&email=%s',
        $_ENV['APP_URL'] ?? 'https://example.com',
        urlencode($token),
        urlencode($email)
    );

    try {
        MemberMail::sendMagicLink($email, $magicLink);
    } catch (\Throwable $e) {
        error_log('Magic link email failed: ' . $e->getMessage());
        // Still return success so attackers can't enumerate emails
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Check your email for a sign-in link'
    ]);

} catch (\Throwable $e) {
    error_log('Magic link error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Could not process request'
    ]);
}
