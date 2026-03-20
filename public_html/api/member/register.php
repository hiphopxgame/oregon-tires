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

    // Build safe username: strip non-alphanumeric, ensure 3+ chars
    $rawUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $firstName . $lastName));
    if (strlen($rawUsername) < 3) {
        $rawUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]));
    }
    if (strlen($rawUsername) < 3) {
        $rawUsername = 'member';
    }
    $rawUsername = substr($rawUsername, 0, 40);

    // Ensure uniqueness
    $username = $rawUsername;
    $suffix = 0;
    while (true) {
        $check = $pdo->prepare('SELECT id FROM members WHERE username = ? LIMIT 1');
        $check->execute([$username]);
        if (!$check->fetch()) break;
        $suffix++;
        $username = $rawUsername . $suffix;
        if ($suffix > 99) { $username = $rawUsername . '_' . substr(bin2hex(random_bytes(3)), 0, 6); break; }
    }

    $result = MemberAuth::register([
        'email'        => $email,
        'password'     => $password,
        'display_name' => trim($firstName . ' ' . $lastName),
        'username'     => $username,
        'phone'        => $phone,
    ]);

    if (!$result['success']) {
        jsonError($result['error'] ?? 'Registration failed.', 400);
    }

    jsonSuccess([
        'member_id' => $result['member']['id'] ?? null,
        'email'     => $email,
    ]);
} catch (\RuntimeException $e) {
    // MemberAuth throws RuntimeException for validation errors (email taken, username taken, etc.)
    jsonError($e->getMessage(), 400);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/register error: " . $e->getMessage());
    jsonError('Server error', 500);
}
