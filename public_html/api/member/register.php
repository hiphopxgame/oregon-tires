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
    $displayName = sanitize((string) ($data['display_name'] ?? ''), 100);
    $username    = sanitize((string) ($data['username'] ?? ''), 50);

    if (!$email || !$password) {
        jsonError('Email and password are required.');
    }

    if (!isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    if (strlen($password) < 8) {
        jsonError('Password must be at least 8 characters.');
    }

    // Auto-generate username if not provided
    if ($username === '') {
        $rawUsername = $displayName !== ''
            ? strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $displayName))
            : strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]));
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
    }

    $member = MemberAuth::register([
        'email'        => $email,
        'password'     => $password,
        'display_name' => $displayName ?: null,
        'username'     => $username,
    ]);

    if (!$member || empty($member['id'])) {
        jsonError('Registration failed.', 500);
    }

    jsonSuccess([
        'member_id' => $member['id'],
        'email'     => $email,
    ]);
} catch (\RuntimeException $e) {
    // MemberAuth throws RuntimeException for validation errors (email taken, username taken, etc.)
    jsonError($e->getMessage(), 400);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/register error: " . $e->getMessage());
    jsonError('Server error', 500);
}
