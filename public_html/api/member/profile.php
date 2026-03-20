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
    $memberId = (int) $_SESSION['member_id'];

    // ── GET: Fetch profile ──
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $profile = MemberProfile::get($memberId);
        if (!$profile) {
            jsonError('Profile not found.', 404);
        }
        $profile['preferences'] = MemberProfile::getAllPreferences($memberId);
        jsonSuccess($profile);
    }

    // ── POST: Avatar upload ──
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['avatar'])) {
        verifyCsrf();

        $avatarUrl = MemberProfile::uploadAvatar($memberId, $_FILES['avatar']);
        jsonSuccess(['avatar_url' => $avatarUrl]);
    }

    // ── PUT: Update profile fields ──
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();
        MemberProfile::update($memberId, $data);
        $updated = MemberProfile::get($memberId);
        jsonSuccess($updated);
    }

    jsonError('Method not allowed', 405);
} catch (\RuntimeException $e) {
    jsonError($e->getMessage());
} catch (\Throwable $e) {
    error_log("Oregon Tires customer/profile error: " . $e->getMessage());
    jsonError('Server error', 500);
}
