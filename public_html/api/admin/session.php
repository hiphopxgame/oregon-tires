<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET');

    $admin = requireAdmin();

    jsonSuccess([
        'id'         => $admin['id'],
        'email'      => $admin['email'],
        'role'       => $admin['role'],
        'name'       => $admin['name'],
        'language'   => $admin['language'] ?? 'both',
        'csrf_token' => $_SESSION['csrf_token'] ?? '',
    ]);
} catch (\Throwable $e) {
    error_log('Session check error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
