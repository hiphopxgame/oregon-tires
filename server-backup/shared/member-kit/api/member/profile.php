<?php
declare(strict_types=1);

/**
 * GET  /api/member/profile.php — Get current member profile
 * PUT  /api/member/profile.php — Update profile fields
 * POST /api/member/profile.php — Avatar upload (multipart/form-data)
 */

// Bootstrap: skip if already loaded by a site wrapper
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('MEMBER_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}
initSession();
MemberAuth::init(getDatabase());

header('Content-Type: application/json');

$member = MemberAuth::requireAuth();
$memberId = (int) $member['id'];
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: Fetch profile ──────────────────────────────────────────────────
if ($method === 'GET') {
    $profile = MemberProfile::get($memberId);
    if (!$profile) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Profile not found']);
        exit;
    }

    $profile['preferences'] = MemberProfile::getAllPreferences($memberId);

    echo json_encode(['success' => true, 'member' => $profile]);
    exit;
}

// ── POST: Avatar upload ─────────────────────────────────────────────────
if ($method === 'POST' && !empty($_FILES['avatar'])) {
    // CSRF check (multipart forms send token as a POST field)
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!MemberAuth::verifyCsrf($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    try {
        $avatarUrl = MemberProfile::uploadAvatar($memberId, $_FILES['avatar']);

        // Sync avatar to HW if linked
        if (MemberSync::isEnabled() && !empty($member['hw_user_id'])) {
            MemberSync::syncProfile((int) $member['hw_user_id'], ['avatar_url' => $avatarUrl]);
        }

        echo json_encode(['success' => true, 'avatar_url' => $avatarUrl]);
    } catch (\RuntimeException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\Throwable $e) {
        error_log('Avatar upload error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Upload failed']);
    }
    exit;
}

// ── PUT: Update profile fields ──────────────────────────────────────────
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // CSRF check
    $csrfToken = $input['csrf_token'] ?? '';
    if (!MemberAuth::verifyCsrf($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    try {
        // Handle email change separately (not part of generic update)
        $newEmail = trim($input['new_email'] ?? '');
        if ($newEmail !== '') {
            MemberProfile::requestEmailChange($memberId, $newEmail);
            echo json_encode([
                'success' => true,
                'message' => 'A verification link has been sent to your new email address.',
            ]);
            exit;
        }

        MemberProfile::update($memberId, $input);

        // Sync to HW if linked
        if (MemberSync::isEnabled() && !empty($member['hw_user_id'])) {
            MemberSync::syncProfile((int) $member['hw_user_id'], array_intersect_key($input, array_flip([
                'display_name', 'avatar_url', 'bio',
            ])));
        }

        $updated = MemberProfile::get($memberId);
        echo json_encode(['success' => true, 'member' => $updated]);
    } catch (\RuntimeException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\Throwable $e) {
        error_log('Profile update error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
