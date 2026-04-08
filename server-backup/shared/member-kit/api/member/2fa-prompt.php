<?php
declare(strict_types=1);
if (!function_exists('getDatabase')) { require_once __DIR__ . '/../../config/database.php'; }
if (!defined('MEMBER_KIT_PATH')) { require_once __DIR__ . '/../../loader.php'; }
initSession();
MemberAuth::init(getDatabase());
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
$member = MemberAuth::requireAuth();
$memberId = (int) $member['id'];
$pdo = getDatabase();
$table = MemberAuth::getMembersTable();
try {
    $stmt = $pdo->prepare("SELECT enabled FROM member_2fa WHERE member_id = ? LIMIT 1");
    $stmt->execute([$memberId]);
    $twoFa = $stmt->fetch();
    if ($twoFa && (bool) $twoFa['enabled']) {
        echo json_encode(['success' => true, 'should_prompt' => false, 'suggested_reason' => '']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT login_count, `2fa_suggested_at` FROM {$table} WHERE id = ? LIMIT 1");
    $stmt->execute([$memberId]);
    $row = $stmt->fetch();
    $loginCount = (int) ($row['login_count'] ?? 0);
    $lastSuggested = $row['2fa_suggested_at'] ?? null;
    if ($lastSuggested !== null && time() < strtotime($lastSuggested) + (7 * 86400)) {
        echo json_encode(['success' => true, 'should_prompt' => false, 'suggested_reason' => '']);
        exit;
    }
    $shouldPrompt = ($loginCount > 5);
    $reason = $shouldPrompt ? sprintf('You have logged in %d times. Protect your account with two-factor authentication.', $loginCount) : '';
    if ($shouldPrompt) {
        $stmt = $pdo->prepare("UPDATE {$table} SET `2fa_suggested_at` = NOW() WHERE id = ?");
        $stmt->execute([$memberId]);
        MemberProfile::logActivity($memberId, '2fa_prompt_shown', null, null, ['login_count' => $loginCount]);
    }
    echo json_encode(['success' => true, 'should_prompt' => $shouldPrompt, 'suggested_reason' => $reason]);
} catch (\Throwable $e) {
    error_log('2FA prompt error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
