<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $user = requireStaff();

    requireMethod('GET');

    jsonSuccess([
        'id'          => $user['id'],
        'email'       => $user['email'],
        'role'        => $user['role'],
        'name'        => $user['name'],
        'type'        => $user['type'],
        'language'    => $user['language'] ?? 'both',
        'employee_id' => $user['employee_id'] ?? null,
        'csrf_token'  => $_SESSION['csrf_token'] ?? '',
    ]);
} catch (\Throwable $e) {
    error_log('Session check error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
