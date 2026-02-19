<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('POST');
    requireAdmin();

    adminLogout();

    jsonSuccess(['message' => 'Logged out successfully.']);
} catch (\Throwable $e) {
    error_log('Logout error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
