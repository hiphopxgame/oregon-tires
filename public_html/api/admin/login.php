<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('POST');

    // Rate limit: 10 login attempts per IP per hour
    checkRateLimit('login', 10, 3600);

    $body = getJsonBody();
    $email = sanitize($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (!isValidEmail($email)) {
        jsonError('Valid email is required.', 400);
    }

    if ($password === '') {
        jsonError('Password is required.', 400);
    }

    $result = adminLogin($email, $password);

    if (is_string($result)) {
        jsonError($result, 401);
    }

    // Track last login timestamp
    $db = getDB();
    $db->prepare('UPDATE oretir_admins SET last_login_at = NOW() WHERE id = ?')
       ->execute([$result['id']]);

    jsonSuccess([
        'id'         => $result['id'],
        'email'      => $result['email'],
        'role'       => $result['role'],
        'name'       => $result['display_name'],
        'language'   => $result['language'] ?? 'en',
        'csrf_token' => $_SESSION['csrf_token'] ?? '',
    ]);
} catch (\Throwable $e) {
    error_log('Login error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
