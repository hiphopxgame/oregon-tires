<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('POST');
    requireCustomerAuth();

    $data = getJsonBody();
    $currentPassword = (string) ($data['current_password'] ?? '');
    $newPassword     = (string) ($data['new_password'] ?? '');

    if (!$currentPassword || !$newPassword) {
        jsonError('Current and new passwords are required.');
    }

    if (strlen($newPassword) < 8) {
        jsonError('New password must be at least 8 characters.');
    }

    $result = MemberAuth::changePassword($_SESSION['member_id'], $currentPassword, $newPassword);

    if (!$result['success']) {
        jsonError($result['error'] ?? 'Password change failed.');
    }

    jsonSuccess(['message' => 'Password updated successfully.']);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/password error: " . $e->getMessage());
    jsonError('Server error', 500);
}
