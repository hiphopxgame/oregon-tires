<?php
/**
 * Oregon Tires — Verify Password Reset Token
 * GET /api/admin/verify-token.php?token=...
 *
 * Returns admin name if token is valid and not expired.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

try {
    requireMethod('GET');

    $token = sanitize((string) ($_GET['token'] ?? ''), 64);

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        jsonError('Invalid token format.');
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT display_name, email, language FROM oretir_admins
         WHERE password_reset_token = ? AND password_reset_expires > NOW() AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$token]);
    $admin = $stmt->fetch();

    if (!$admin) {
        jsonError('Invalid or expired token.', 401);
    }

    jsonSuccess([
        'name'     => $admin['display_name'],
        'email'    => substr($admin['email'], 0, 3) . '***@' . explode('@', $admin['email'])[1],
        'language' => $admin['language'] ?? 'both',
    ]);

} catch (\Throwable $e) {
    error_log("Oregon Tires verify-token.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
