<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('POST');
    MemberAuth::logout();
    jsonSuccess(['message' => 'Logged out successfully.']);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/logout error: " . $e->getMessage());
    jsonError('Server error', 500);
}
