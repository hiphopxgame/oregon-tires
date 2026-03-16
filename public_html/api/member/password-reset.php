<?php
/**
 * Oregon Tires — Password Reset Request
 * POST /api/member/password-reset.php
 *
 * Overrides member-kit's MemberMail sender with Oregon Tires' own branded
 * email system (sendBrandedResetEmail) for reliable deliverability.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
require_once __DIR__ . '/../../includes/mail.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('POST');
    checkRateLimit('forgot_password', 3, 3600);

    $data = getJsonBody();
    $email = sanitize((string) ($data['email'] ?? ''), 254);

    if (!$email || !isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    $email = strtolower(trim($email));

    // Look up member
    $stmt = $pdo->prepare("SELECT id, email, display_name, username FROM members WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $member = $stmt->fetch();

    if ($member) {
        $memberId = (int) $member['id'];

        // Invalidate existing reset tokens
        $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE member_id = ? AND used_at IS NULL")
            ->execute([$memberId]);

        // Generate token + hash
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30 minutes

        $pdo->prepare("INSERT INTO password_resets (member_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$memberId, $tokenHash, $expiresAt]);

        // Build reset URL
        $siteUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $resetUrl = $siteUrl . '/reset-password/' . $token;

        // Detect language preference
        $lang = $_GET['lang'] ?? $_SESSION['member_lang'] ?? $_COOKIE['lang'] ?? 'both';

        // Send via Oregon Tires' branded mailer
        $name = $member['display_name'] ?? $member['username'] ?? $email;
        $result = sendBrandedResetEmail($email, $name, $resetUrl, $lang);

        if (!$result['success']) {
            error_log("Oregon Tires password reset email failed for {$email}: " . ($result['error'] ?? 'unknown'));
        }

        // Log activity
        if (class_exists('MemberProfile')) {
            MemberProfile::logActivity($memberId, 'password_reset_requested');
        }
    }

    // Always return success to prevent email enumeration
    jsonSuccess(['message' => 'If an account exists with that email, a reset link has been sent.']);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/password-reset error: " . $e->getMessage());
    jsonError('Server error', 500);
}
