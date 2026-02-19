<?php
/**
 * Oregon Tires — Forgot Password
 * POST /api/admin/forgot-password.php
 *
 * Accepts { "email": "..." }, generates a password reset token,
 * and sends the reset email using the DB-driven template.
 *
 * Always returns success (even if email not found) to prevent enumeration.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/mail.php';

try {
    requireMethod('POST');

    // Rate limit: 3 reset requests per IP per hour
    checkRateLimit('forgot_password', 3, 3600);

    $data  = getJsonBody();
    $email = sanitize((string) ($data['email'] ?? ''), 255);

    if (!isValidEmail($email)) {
        // Return success regardless to prevent email enumeration
        jsonSuccess(['message' => 'If an account with that email exists, a reset link has been sent.']);
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, display_name, email, language FROM oretir_admins WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Generate reset token (1-hour expiry for security)
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->prepare('UPDATE oretir_admins SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?')
           ->execute([$token, $expires, $admin['id']]);

        // Build reset URL
        $baseUrl  = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $resetUrl = $baseUrl . '/admin/setup-password.html?token=' . $token;

        $language = $admin['language'] ?? 'both';

        sendBrandedResetEmail($admin['email'], $admin['display_name'], $resetUrl, $language);
    }

    // Always return the same response to prevent email enumeration
    jsonSuccess(['message' => 'If an account with that email exists, a reset link has been sent.']);

} catch (\Throwable $e) {
    error_log("Oregon Tires forgot-password.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
