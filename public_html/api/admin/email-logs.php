<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET');
    requireAdmin();

    $db = getDB();
    $stmt = $db->query('SELECT * FROM oretir_email_logs ORDER BY created_at DESC LIMIT 50');

    jsonSuccess($stmt->fetchAll());

} catch (\Throwable $e) {
    error_log('email-logs.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
