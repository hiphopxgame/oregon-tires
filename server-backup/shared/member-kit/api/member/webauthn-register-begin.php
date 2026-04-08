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

    $challenge = base64_encode(random_bytes(32));
    $_SESSION['webauthn_registration_challenge'] = $challenge;

    $options = [
        'challenge' => $challenge,
        'rp' => [
            'name' => $_ENV['APP_NAME'] ?? '1vsM Network',
            'id' => parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) ?? 'localhost'
        ],
        'user' => [
            'id' => base64_encode((string) $memberId),
            'name' => $member['email'] ?? 'user@example.com',
            'displayName' => $member['name'] ?? 'User'
        ],
        'pubKeyCredParams' => [
            ['alg' => -7, 'type' => 'public-key'],
            ['alg' => -257, 'type' => 'public-key']
        ],
        'timeout' => 60000,
        'attestation' => 'direct',
        'authenticatorSelection' => [
            'authenticatorAttachment' => 'platform',
            'userVerification' => 'preferred'
        ]
    ];

    echo json_encode(['success' => true, 'data' => ['PublicKeyCredentialCreationOptions' => $options]]);
} catch (\Throwable $e) {
    error_log('WebAuthn register begin error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
