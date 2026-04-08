<?php
declare(strict_types=1);

/**
 * POST /api/member/webauthn-login-begin.php
 * Start a WebAuthn / passkey login flow.
 *
 * Accepts: { "email": "user@example.com" }
 * Returns: { success: true, data: { PublicKeyCredentialRequestOptions: {...} } }
 */

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
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $email = strtolower(trim($input['email'] ?? ''));

    if ($email === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        exit;
    }

    // Rate limit passkey login attempts
    if (!MemberAuth::checkRateLimit($email)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'Too many login attempts. Please wait 15 minutes.']);
        exit;
    }

    $pdo = getDatabase();
    $prefix = MemberAuth::getTablePrefix();
    $membersTable = MemberAuth::getMembersTable();

    // Look up user by email
    $stmt = $pdo->prepare("SELECT * FROM {$membersTable} WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $member = $stmt->fetch();

    if ($member === false) {
        // Return generic error to avoid user enumeration
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No passkey found for this account']);
        exit;
    }

    $memberId = (int) $member['id'];

    // Check if account is locked or disabled
    if (!empty($member['locked_until']) && strtotime($member['locked_until']) > time()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Account is temporarily locked']);
        exit;
    }
    if (!empty($member['disabled_at'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Account is disabled']);
        exit;
    }

    // Fetch registered WebAuthn credentials for this user
    $credStmt = $pdo->prepare(
        "SELECT credential_id, transports FROM {$prefix}webauthn_credentials WHERE member_id = ?"
    );
    $credStmt->execute([$memberId]);
    $credentials = $credStmt->fetchAll();

    if (empty($credentials)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No passkey found for this account']);
        exit;
    }

    // Build allowCredentials list
    $allowCredentials = [];
    foreach ($credentials as $cred) {
        $transports = [];
        if (!empty($cred['transports'])) {
            $decoded = json_decode($cred['transports'], true);
            if (is_array($decoded)) {
                $transports = $decoded;
            }
        }
        $allowCredentials[] = [
            'id' => base64_encode($cred['credential_id']),
            'type' => 'public-key',
            'transports' => $transports,
        ];
    }

    // Generate challenge and store in session
    $challenge = base64_encode(random_bytes(32));
    $_SESSION['webauthn_login_challenge'] = $challenge;
    $_SESSION['webauthn_login_member_id'] = $memberId;

    $rpId = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Strip port if present
    if (str_contains($rpId, ':')) {
        $rpId = strtok($rpId, ':');
    }

    $options = [
        'challenge' => $challenge,
        'allowCredentials' => $allowCredentials,
        'timeout' => 60000,
        'rpId' => $rpId,
        'userVerification' => 'preferred',
    ];

    echo json_encode(['success' => true, 'data' => ['PublicKeyCredentialRequestOptions' => $options]]);
} catch (\Throwable $e) {
    error_log('WebAuthn login begin error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
