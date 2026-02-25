<?php
declare(strict_types=1);

/**
 * POST /api/member/login.php — Oregon Tires
 * Thin wrapper that delegates to the shared member-kit login endpoint.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

// Provide getDatabase() alias so the member-kit endpoint
// reuses the same PDO instead of opening a second connection.
if (!function_exists('getDatabase')) {
    function getDatabase(): PDO { return getDB(); }
}

// Delegate to shared member-kit endpoint
require MEMBER_KIT_PATH . '/api/member/login.php';
