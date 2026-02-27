<?php
/**
 * Oregon Tires — Google OAuth Callback
 * Exchanges authorization code for user info, finds or creates customer, logs in.
 *
 * Uses independent mode: site-specific `members` table via member-kit.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();

$pdo = getDB();
initMemberKit($pdo);

// ── Check for errors from Google ────────────────────────────────────────────
if (!empty($_GET['error'])) {
    error_log('Google OAuth error: ' . ($_GET['error_description'] ?? $_GET['error']));
    header('Location: /members?error=' . urlencode('Google sign-in was cancelled or denied.'));
    exit;
}

// ── Validate required params ────────────────────────────────────────────────
if (empty($_GET['code']) || empty($_GET['state'])) {
    error_log('Google OAuth: missing code or state');
    header('Location: /members?error=' . urlencode('Invalid response from Google. Please try again.'));
    exit;
}

// ── Verify CSRF state ───────────────────────────────────────────────────────
$expectedState = $_SESSION['google_oauth_state'] ?? '';
if (!hash_equals($expectedState, $_GET['state'])) {
    error_log('Google OAuth: state mismatch');
    header('Location: /members?error=' . urlencode('Session expired. Please try signing in again.'));
    exit;
}
unset($_SESSION['google_oauth_state']);

// ── Exchange code for access token ──────────────────────────────────────────
$clientId     = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
$redirectUri  = $_ENV['GOOGLE_REDIRECT_URI'] ?? 'https://oregon.tires/api/auth/google-callback.php';
$codeVerifier = $_SESSION['google_code_verifier'] ?? '';
unset($_SESSION['google_code_verifier']);

$tokenPayload = http_build_query([
    'grant_type'    => 'authorization_code',
    'code'          => $_GET['code'],
    'redirect_uri'  => $redirectUri,
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'code_verifier' => $codeVerifier,
]);

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $tokenPayload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch) || $httpCode !== 200) {
    $curlErrno = curl_errno($ch);
    error_log("Google OAuth token exchange failed: HTTP {$httpCode}, curl error: " . curl_error($ch) . ", response: {$tokenResponse}");
    curl_close($ch);
    $msg = in_array($curlErrno, [CURLE_OPERATION_TIMEDOUT, CURLE_COULDNT_CONNECT, CURLE_COULDNT_RESOLVE_HOST])
        ? 'Google is temporarily unavailable. Please sign in with email/password.'
        : 'Google sign-in failed. Please try again.';
    header('Location: /members?error=' . urlencode($msg));
    exit;
}
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData['access_token'] ?? '';
if (empty($accessToken)) {
    error_log('Google OAuth: no access_token in response');
    header('Location: /members?error=' . urlencode('Google sign-in failed. Please try again.'));
    exit;
}

// ── Fetch user profile ──────────────────────────────────────────────────────
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
]);
$profileResponse = curl_exec($ch);
$profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch) || $profileHttpCode !== 200) {
    $curlErrno = curl_errno($ch);
    error_log("Google OAuth userinfo failed: HTTP {$profileHttpCode}, response: {$profileResponse}");
    curl_close($ch);
    $msg = in_array($curlErrno, [CURLE_OPERATION_TIMEDOUT, CURLE_COULDNT_CONNECT, CURLE_COULDNT_RESOLVE_HOST])
        ? 'Google is temporarily unavailable. Please sign in with email/password.'
        : 'Could not retrieve your Google profile. Please try again.';
    header('Location: /members?error=' . urlencode($msg));
    exit;
}
curl_close($ch);

$profile = json_decode($profileResponse, true);
if (empty($profile['email'])) {
    error_log('Google OAuth: no email in profile response');
    header('Location: /members?error=' . urlencode('Your Google account has no email address.'));
    exit;
}

// ── Find or create customer in members table ────────────────────────────────
try {
    $user = findOrCreateGoogleCustomer($pdo, $profile);
} catch (\Throwable $e) {
    error_log('Google OAuth customer creation failed: ' . $e->getMessage());
    header('Location: /members?error=' . urlencode('Could not create your account. Please try again.'));
    exit;
}

// ── Log the customer in via member-kit ──────────────────────────────────────
session_regenerate_id(true);
$_SESSION['member_id'] = (int) $user['id'];
$_SESSION['member_email'] = $user['email'] ?? '';
$_SESSION['is_customer'] = true;

if (class_exists('MemberAuth')) {
    MemberAuth::startAuthenticatedSession($user);
}

// Track login method (graceful — column may not exist)
try {
    $pdo->prepare('UPDATE members SET last_login_method = ? WHERE id = ?')
        ->execute(['google', $user['id']]);
} catch (\Throwable $_) {}

// ── Redirect ────────────────────────────────────────────────────────────────
$returnUrl = $_SESSION['google_return_url'] ?? '/members';
unset($_SESSION['google_return_url']);
if (!is_string($returnUrl) || $returnUrl === '' || !str_starts_with($returnUrl, '/') || str_starts_with($returnUrl, '//')) {
    $returnUrl = '/members';
}
header('Location: ' . $returnUrl);
exit;

// ═════════════════════════════════════════════════════════════════════════════

function findOrCreateGoogleCustomer(PDO $pdo, array $profile): array
{
    $googleId    = $profile['sub'] ?? '';
    $email       = $profile['email'] ?? '';
    $displayName = $profile['name'] ?? '';
    $givenName   = $profile['given_name'] ?? '';
    $avatarUrl   = $profile['picture'] ?? '';

    // Check if google_id column exists (graceful migration)
    $hasGoogleId = false;
    try {
        $pdo->query('SELECT google_id FROM members LIMIT 0');
        $hasGoogleId = true;
    } catch (\Throwable $_) {
        try {
            $pdo->exec('ALTER TABLE members ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER email');
            $pdo->exec('ALTER TABLE members ADD INDEX idx_google_id (google_id)');
            $hasGoogleId = true;
        } catch (\Throwable $e2) {
            error_log('Could not add google_id column: ' . $e2->getMessage());
        }
    }

    // Check if avatar_url column exists (graceful migration)
    $hasAvatar = false;
    try {
        $pdo->query('SELECT avatar_url FROM members LIMIT 0');
        $hasAvatar = true;
    } catch (\Throwable $_) {
        try {
            $pdo->exec('ALTER TABLE members ADD COLUMN avatar_url VARCHAR(512) DEFAULT NULL AFTER display_name');
            $hasAvatar = true;
        } catch (\Throwable $e2) {
            error_log('Could not add avatar_url column: ' . $e2->getMessage());
        }
    }

    // 1. Find by google_id (if column exists)
    if ($hasGoogleId && !empty($googleId)) {
        $stmt = $pdo->prepare('SELECT * FROM members WHERE google_id = ? LIMIT 1');
        $stmt->execute([$googleId]);
        $user = $stmt->fetch();

        if ($user) {
            $sql = 'UPDATE members SET last_login_at = NOW()';
            $params = [];
            if ($hasAvatar && !empty($avatarUrl)) {
                $sql .= ', avatar_url = COALESCE(?, avatar_url)';
                $params[] = $avatarUrl;
            }
            $sql .= ' WHERE id = ?';
            $params[] = $user['id'];
            $pdo->prepare($sql)->execute($params);
            return $user;
        }
    }

    // 2. Find by email
    $stmt = $pdo->prepare('SELECT * FROM members WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Link Google to existing account
        $updates = ['last_login_at = NOW()'];
        $params = [];
        if ($hasGoogleId) {
            $updates[] = 'google_id = ?';
            $params[] = $googleId;
        }
        if (!empty($displayName)) {
            $updates[] = 'display_name = COALESCE(display_name, ?)';
            $params[] = $displayName;
        }
        if ($hasAvatar && !empty($avatarUrl)) {
            $updates[] = 'avatar_url = COALESCE(avatar_url, ?)';
            $params[] = $avatarUrl;
        }
        $updates[] = 'email_verified_at = COALESCE(email_verified_at, NOW())';
        $params[] = $user['id'];
        $pdo->prepare('UPDATE members SET ' . implode(', ', $updates) . ' WHERE id = ?')
            ->execute($params);
        return $user;
    }

    // 3. Create new customer
    $username = preg_replace('/[^a-zA-Z0-9]/', '', $givenName ?: explode('@', $email)[0]);
    if (empty($username)) {
        $username = 'customer';
    }

    // Ensure unique username
    $baseUsername = $username;
    $counter = 0;
    while ($counter < 100) {
        $check = $pdo->prepare('SELECT id FROM members WHERE username = ? LIMIT 1');
        $check->execute([$username]);
        if (!$check->fetch()) break;
        $counter++;
        $username = $baseUsername . $counter;
    }
    if ($counter >= 100) {
        throw new \RuntimeException('Could not generate unique username');
    }

    $cols = 'email, username, display_name, is_active, email_verified_at, created_at, last_login_at';
    $vals = '?, ?, ?, ?, NOW(), NOW(), NOW()';
    $params = [$email, $username, $displayName, 1];

    if ($hasGoogleId) {
        $cols .= ', google_id';
        $vals .= ', ?';
        $params[] = $googleId;
    }

    if ($hasAvatar && !empty($avatarUrl)) {
        $cols .= ', avatar_url';
        $vals .= ', ?';
        $params[] = $avatarUrl;
    }

    $pdo->prepare("INSERT INTO members ({$cols}) VALUES ({$vals})")
        ->execute($params);

    $newId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT * FROM members WHERE id = ? LIMIT 1');
    $stmt->execute([$newId]);
    return $stmt->fetch();
}
