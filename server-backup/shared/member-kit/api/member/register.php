<?php
declare(strict_types=1);

/**
 * POST /api/member/register.php
 * Create a new account (independent mode only)
 */

function maskEmail(string $email): string {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return '***@***.***';
    $local = $parts[0];
    $domain = $parts[1];
    $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(strlen($local) - 1, 2));
    return $maskedLocal . '@' . $domain;
}

// Bootstrap: skip if already loaded by a site wrapper
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('MEMBER_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}
initSession();
MemberAuth::init(getDatabase());

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// CSRF check
$csrfToken = $input['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Server-side password confirmation
$password = $input['password'] ?? '';
$passwordConfirm = $input['password_confirm'] ?? '';
if ($password !== $passwordConfirm) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
    exit;
}

try {
    $member = MemberAuth::register([
        'email'        => $input['email'] ?? '',
        'password'     => $password,
        'username'     => $input['username'] ?? '',
        'display_name' => $input['display_name'] ?? '',
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Account created. Please check your email to verify your address.',
        'email_verification' => 'pending',
        'masked_email' => maskEmail($member['email']),
        'member'  => [
            'id'           => (int) $member['id'],
            'email'        => $member['email'],
            'username'     => $member['username'] ?? null,
            'display_name' => $member['display_name'] ?? null,
        ],
    ]);
} catch (\RuntimeException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log('Registration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Registration failed. Please try again.']);
}
