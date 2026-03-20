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

    $email       = sanitize((string) ($data['email'] ?? ''), 254);
    $password    = (string) ($data['password'] ?? '');
    $firstName   = sanitize((string) ($data['first_name'] ?? ''), 100);
    $lastName    = sanitize((string) ($data['last_name'] ?? ''), 100);
    $phone       = sanitize((string) ($data['phone'] ?? ''), 30);

    if (!$email || !$password || !$firstName || !$lastName) {
        jsonError('Name, email, and password are required.');
    }

    if (!isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    if (strlen($password) < 8) {
        jsonError('Password must be at least 8 characters.');
    }

    $result = MemberAuth::register([
        'email'        => $email,
        'password'     => $password,
        'display_name' => trim($firstName . ' ' . $lastName),
        'username'     => strtolower($firstName) . '.' . strtolower($lastName),
        'phone'        => $phone,
    ]);

    if (!$result['success']) {
        jsonError($result['error'] ?? 'Registration failed.', 400);
    }

    jsonSuccess([
        'member_id' => $result['member']['id'] ?? null,
        'email'     => $email,
    ]);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/register error: " . $e->getMessage());
    jsonError('Server error', 500);
}
