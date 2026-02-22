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

    $email = sanitize((string) ($data['email'] ?? ''), 254);
    $password = (string) ($data['password'] ?? '');

    if (!$email || !$password) {
        jsonError('Email and password are required.');
    }

    $result = MemberAuth::login($email, $password);

    if (!$result['success']) {
        jsonError($result['error'] ?? 'Invalid credentials.', 401);
    }

    jsonSuccess([
        'member_id' => $result['member']['id'] ?? null,
        'email'     => $result['member']['email'] ?? '',
        'name'      => $result['member']['display_name'] ?? '',
    ]);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/login error: " . $e->getMessage());
    jsonError('Server error', 500);
}
