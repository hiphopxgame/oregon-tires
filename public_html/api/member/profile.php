<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireCustomerAuth();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $profile = MemberProfile::get($_SESSION['member_id']);
        if (!$profile) {
            jsonError('Profile not found.', 404);
        }
        jsonSuccess($profile);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = getJsonBody();
        $result = MemberProfile::update($_SESSION['member_id'], $data);
        if (!$result['success']) {
            jsonError($result['error'] ?? 'Update failed.');
        }
        jsonSuccess($result);
    }

    jsonError('Method not allowed', 405);
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/profile error: " . $e->getMessage());
    jsonError('Server error', 500);
}
