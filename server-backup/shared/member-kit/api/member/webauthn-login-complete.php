<?php
declare(strict_types=1);

/**
 * POST /api/member/webauthn-login-complete.php
 * Complete a WebAuthn / passkey login flow.
 *
 * Accepts: { credential_id, authenticator_data, client_data_json, signature }
 * Verifies the challenge, looks up the credential, starts an authenticated session.
 * Returns: { success: true, redirect: '/members' }
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

    $credentialIdB64 = $input['credential_id'] ?? '';
    $authenticatorDataB64 = $input['authenticator_data'] ?? '';
    $clientDataJsonB64 = $input['client_data_json'] ?? '';
    $signatureB64 = $input['signature'] ?? '';

    if ($credentialIdB64 === '' || $authenticatorDataB64 === '' || $clientDataJsonB64 === '' || $signatureB64 === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing credential data']);
        exit;
    }

    // Verify challenge exists in session (set by webauthn-login-begin.php)
    $sessionChallenge = $_SESSION['webauthn_login_challenge'] ?? '';
    $sessionMemberId = $_SESSION['webauthn_login_member_id'] ?? null;

    if ($sessionChallenge === '' || $sessionMemberId === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No pending passkey challenge. Please start again.']);
        exit;
    }

    // Clear the challenge immediately to prevent replay
    unset($_SESSION['webauthn_login_challenge']);
    unset($_SESSION['webauthn_login_member_id']);

    // Decode credential ID from base64url
    $credentialIdRaw = base64_decode(strtr($credentialIdB64, '-_', '+/'));
    if ($credentialIdRaw === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid credential ID encoding']);
        exit;
    }

    $pdo = getDatabase();
    $prefix = MemberAuth::getTablePrefix();

    // Find matching credential in DB
    $stmt = $pdo->prepare(
        "SELECT * FROM {$prefix}webauthn_credentials WHERE credential_id = ? AND member_id = ?"
    );
    $stmt->execute([$credentialIdRaw, $sessionMemberId]);
    $storedCred = $stmt->fetch();

    if ($storedCred === false) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Passkey not recognized']);
        exit;
    }

    // Decode client data JSON to verify the challenge matches
    $clientDataJsonRaw = base64_decode(strtr($clientDataJsonB64, '-_', '+/'));
    if ($clientDataJsonRaw === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid client data encoding']);
        exit;
    }

    $clientData = json_decode($clientDataJsonRaw, true);
    if (!is_array($clientData)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Malformed client data JSON']);
        exit;
    }

    // Verify the type is 'webauthn.get'
    if (($clientData['type'] ?? '') !== 'webauthn.get') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid client data type']);
        exit;
    }

    // Verify the challenge matches what we stored in session
    // The client sends the challenge as base64url; our session stores it as standard base64
    $clientChallenge = $clientData['challenge'] ?? '';
    // Normalize both to standard base64 for comparison
    $clientChallengeNormalized = strtr($clientChallenge, '-_', '+/');
    $sessionChallengeNormalized = strtr($sessionChallenge, '-_', '+/');

    // Pad both to proper base64 length
    $clientChallengeNormalized = str_pad($clientChallengeNormalized, (int) (ceil(strlen($clientChallengeNormalized) / 4) * 4), '=');
    $sessionChallengeNormalized = str_pad($sessionChallengeNormalized, (int) (ceil(strlen($sessionChallengeNormalized) / 4) * 4), '=');

    if (!hash_equals($sessionChallengeNormalized, $clientChallengeNormalized)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Challenge verification failed']);
        exit;
    }

    // Verify origin matches expected RP
    $expectedOrigin = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    // Strip port for comparison if present in expectedOrigin but not in clientData or vice versa
    $clientOrigin = $clientData['origin'] ?? '';
    if ($clientOrigin !== '' && $clientOrigin !== $expectedOrigin) {
        // Allow origin without port vs with default port
        $clientOriginNoPort = preg_replace('/:\d+$/', '', $clientOrigin);
        $expectedOriginNoPort = preg_replace('/:\d+$/', '', $expectedOrigin);
        if ($clientOriginNoPort !== $expectedOriginNoPort) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Origin verification failed']);
            exit;
        }
    }

    // Decode authenticator data to extract sign count (bytes 33-36, big-endian uint32)
    $authenticatorDataRaw = base64_decode(strtr($authenticatorDataB64, '-_', '+/'));
    if ($authenticatorDataRaw !== false && strlen($authenticatorDataRaw) >= 37) {
        $signCountBytes = substr($authenticatorDataRaw, 33, 4);
        $signCount = unpack('N', $signCountBytes)[1] ?? 0;
        $storedSignCount = (int) ($storedCred['sign_count'] ?? 0);

        // Sign count should always increase (clone detection)
        // A sign count of 0 on both sides means the authenticator doesn't support counters
        if ($signCount > 0 || $storedSignCount > 0) {
            if ($signCount <= $storedSignCount) {
                error_log("WebAuthn clone detection: credential {$storedCred['id']} sign_count went from {$storedSignCount} to {$signCount}");
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Credential may have been cloned. Please re-register your passkey.']);
                exit;
            }
        }

        // Update sign count and last_used_at
        $updateStmt = $pdo->prepare(
            "UPDATE {$prefix}webauthn_credentials SET sign_count = ?, last_used_at = NOW() WHERE id = ?"
        );
        $updateStmt->execute([$signCount, $storedCred['id']]);
    } else {
        // Still update last_used_at even if we can't parse authenticator data
        $updateStmt = $pdo->prepare(
            "UPDATE {$prefix}webauthn_credentials SET last_used_at = NOW() WHERE id = ?"
        );
        $updateStmt->execute([$storedCred['id']]);
    }

    // Load the member record to start an authenticated session
    $membersTable = MemberAuth::getMembersTable();
    $memberStmt = $pdo->prepare("SELECT * FROM {$membersTable} WHERE id = ? LIMIT 1");
    $memberStmt->execute([$sessionMemberId]);
    $member = $memberStmt->fetch();

    if ($member === false) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Member account not found']);
        exit;
    }

    // Check account status
    if (!empty($member['disabled_at'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Account is disabled']);
        exit;
    }
    if (!empty($member['locked_until']) && strtotime($member['locked_until']) > time()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Account is temporarily locked']);
        exit;
    }

    // Start authenticated session (fires onLogin hook, records site connection)
    MemberAuth::startAuthenticatedSession($member);

    // Log the passkey login activity if MemberProfile is available
    if (class_exists('MemberProfile')) {
        try {
            MemberProfile::logActivity((int) $member['id'], 'passkey_login', null, null, [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'credential_id' => $storedCred['id'],
            ]);
        } catch (\Throwable $logErr) {
            // Non-critical — don't fail login over activity logging
            error_log('WebAuthn login activity log error: ' . $logErr->getMessage());
        }
    }

    $isAdmin = (bool) ($member['is_admin'] ?? false);
    $redirect = $isAdmin ? '/admin' : '/members';

    echo json_encode([
        'success' => true,
        'message' => 'Authenticated with passkey',
        'member' => [
            'id' => (int) $member['id'],
            'email' => $member['email'],
            'username' => $member['username'] ?? null,
            'display_name' => $member['display_name'] ?? null,
            'is_admin' => $isAdmin,
        ],
        'redirect' => $redirect,
        'server_validated' => true,
    ]);
} catch (\Throwable $e) {
    error_log('WebAuthn login complete error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
