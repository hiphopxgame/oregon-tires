<?php
/**
 * Oregon Tires — Password Setup Endpoint
 * POST /api/admin/setup-password.php
 *
 * Validates a password reset token and sets the admin's password.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/mail.php';

try {
    requireMethod('POST');

    // Rate limit: 5 password setup attempts per IP per hour
    checkRateLimit('setup_password', 5, 3600);

    $data = getJsonBody();

    $missing = requireFields($data, ['token', 'password']);
    if (!empty($missing)) {
        jsonError('Missing required fields: ' . implode(', ', $missing));
    }

    $token    = sanitize((string) $data['token'], 64);
    $password = (string) $data['password'];

    // Validate token format (hex, 64 chars)
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        jsonError('Invalid token format.');
    }

    // Validate password strength
    if (mb_strlen($password) < 8) {
        jsonError('Password must be at least 8 characters.');
    }
    if (mb_strlen($password) > 72) {
        jsonError('Password must not exceed 72 characters.');
    }
    if (!preg_match('/[A-Z]/', $password)) {
        jsonError('Password must contain at least one uppercase letter.');
    }
    if (!preg_match('/[a-z]/', $password)) {
        jsonError('Password must contain at least one lowercase letter.');
    }
    if (!preg_match('/[0-9]/', $password)) {
        jsonError('Password must contain at least one number.');
    }

    $db = getDB();

    // Find admin with this valid, non-expired token
    $stmt = $db->prepare(
        'SELECT id, email, display_name FROM oretir_admins
         WHERE password_reset_token = ? AND password_reset_expires > NOW() AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$token]);
    $admin = $stmt->fetch();

    if (!$admin) {
        jsonError('Invalid or expired token. Please request a new setup link.', 401);
    }

    // Hash and save password, clear token, mark setup complete
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $db->prepare(
        'UPDATE oretir_admins SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL, login_attempts = 0, locked_until = NULL, setup_completed_at = NOW() WHERE id = ?'
    )->execute([$hash, $admin['id']]);

    logEmail('password_setup', "Password set for admin: {$admin['email']}");

    jsonSuccess([
        'message' => 'Password set successfully. You can now log in.',
        'name'    => $admin['display_name'],
    ]);

} catch (\Throwable $e) {
    error_log("Oregon Tires setup-password.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
