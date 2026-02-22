<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

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

    $result = MemberAuth::requestPasswordReset($email);

    // Always return success to prevent email enumeration
    jsonSuccess(['message' => 'If an account exists with that email, a reset link has been sent.']);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/forgot-password error: " . $e->getMessage());
    jsonError('Server error', 500);
}
