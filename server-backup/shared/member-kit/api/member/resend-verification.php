<?php
declare(strict_types=1);
if (!function_exists('getDatabase')) { require_once __DIR__ . '/../../config/database.php'; }
if (!defined('MEMBER_KIT_PATH')) { require_once __DIR__ . '/../../loader.php'; }
initSession();
MemberAuth::init(getDatabase());
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$csrfToken = $input['csrf_token'] ?? '';
if (!MemberAuth::verifyCsrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}
try {
    $memberId = 0;
    if (MemberAuth::isLoggedIn()) {
        $member = MemberAuth::getCurrentMember();
        $memberId = (int) ($member[MemberAuth::getMemberIdColumn()] ?? 0);
    } elseif (!empty($input['member_id'])) {
        $memberId = (int) $input['member_id'];
    }
    if ($memberId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }
    if (!MemberAuth::checkActionRateLimit('resend_verification_' . $memberId, 1, 3600)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'Too many requests. Please wait an hour before requesting another verification email.']);
        exit;
    }
    $pdo = getDatabase();
    $logStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM member_email_resend_log WHERE member_id = ? AND sent_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $logStmt->execute([$memberId]);
    $logRow = $logStmt->fetch();
    if ((int) $logRow['cnt'] > 0) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'A verification email was already sent recently. Please wait an hour before requesting another.']);
        exit;
    }
    $sent = MemberAuth::resendVerification($memberId);
    if (!$sent) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Could not send verification email. Your account may already be verified or not found.']);
        exit;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $insertStmt = $pdo->prepare("INSERT INTO member_email_resend_log (member_id, ip_address, sent_at) VALUES (?, ?, NOW())");
    $insertStmt->execute([$memberId, $ip]);
    echo json_encode(['success' => true, 'message' => 'Verification email sent. Please check your inbox.']);
} catch (\Throwable $e) {
    error_log('Resend verification error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not process request']);
}
