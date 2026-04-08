<?php
declare(strict_types=1);
if (!function_exists('getDatabase')) { require_once __DIR__ . '/../../config/database.php'; }
if (!defined('MEMBER_KIT_PATH')) { require_once __DIR__ . '/../../loader.php'; }
initSession();
MemberAuth::init(getDatabase());
header('Content-Type: application/json');
try {
    $member = MemberAuth::requireAuth();
    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];
    $pdo = getDatabase();
    $table = MemberAuth::getMembersTable();
    
    // Email verification guard
    $stmt = $pdo->prepare("SELECT email_verified_at FROM {$table} WHERE id = ? LIMIT 1");
    $stmt->execute([$memberId]);
    $row = $stmt->fetch();
    
    if (!$row || $row['email_verified_at'] === null) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error'   => 'Email not verified',
            'code'    => 'email_verified_required',
        ]);
        exit;
    }
    
    $action = trim($_GET['action'] ?? 'init');
    
    if ($action === 'init') {
        $secret = bin2hex(random_bytes(20));
        $issuer = $_ENV['APP_NAME'] ?? '1vsM Network';
        $accountName = $member['email'] ?? 'user@example.com';
        $provisionalUri = sprintf('otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($issuer), rawurlencode($accountName), $secret, rawurlencode($issuer));
        $_SESSION['pending_2fa_secret'] = $secret;
        echo json_encode(['success' => true, 'data' => ['secret' => $secret, 'provisioning_uri' => $provisionalUri]]);
    } elseif ($action === 'verify') {
        echo json_encode(['success' => true, 'message' => '2FA enabled']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (\Throwable $e) {
    error_log('2FA setup error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
