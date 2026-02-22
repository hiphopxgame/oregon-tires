<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('POST');

    $data = getJsonBody();
    $token    = sanitize((string) ($data['token'] ?? ''), 64);
    $password = (string) ($data['password'] ?? '');

    if (!$token || !$password) {
        jsonError('Token and new password are required.');
    }

    if (strlen($password) < 8) {
        jsonError('Password must be at least 8 characters.');
    }

    $result = MemberAuth::resetPassword($token, $password);

    if (!$result['success']) {
        jsonError($result['error'] ?? 'Reset failed. Token may be expired.');
    }

    jsonSuccess(['message' => 'Password has been reset. You may now log in.']);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/reset-password error: " . $e->getMessage());
    jsonError('Server error', 500);
}
