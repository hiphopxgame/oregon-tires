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

try {
    $member = MemberAuth::requireAuth();
    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];
    $pdo = getDatabase();

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $credentialId = $input['credential_id'] ?? '';
    $publicKey = $input['public_key'] ?? '';

    if (empty($credentialId) || empty($publicKey)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing credential data']);
        exit;
    }

    $prefix = MemberAuth::getTablePrefix();
    $stmt = $pdo->prepare("INSERT INTO {$prefix}webauthn_credentials
        (member_id, credential_id, public_key, transports)
        VALUES (?, ?, ?, ?)");

    $stmt->execute([
        $memberId,
        base64_decode($credentialId),
        base64_decode($publicKey),
        json_encode(['internal', 'ble', 'nfc', 'usb'])
    ]);

    echo json_encode(['success' => true, 'message' => 'Passkey registered']);
} catch (\Throwable $e) {
    error_log('WebAuthn register complete error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
