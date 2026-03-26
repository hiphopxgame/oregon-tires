<?php
/**
 * Oregon Tires — Google OAuth Callback
 * Exchanges authorization code for user info, finds or creates customer, logs in.
 *
 * Uses independent mode: site-specific `members` table via member-kit.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

startSecureSession();

$pdo = getDB();

// ═════════════════════════════════════════════════════════════════════════════
// ADMIN GOOGLE OAUTH — handle admin_login / admin_connect modes
// ═════════════════════════════════════════════════════════════════════════════
$oauthMode = $_SESSION['google_oauth_mode'] ?? '';

if (in_array($oauthMode, ['admin_login', 'admin_connect'], true)) {
    require_once __DIR__ . '/../../includes/auth.php';

    $adminUrl = '/admin/';

    // Handle errors from Google
    if (!empty($_GET['error'])) {
        error_log('Admin Google OAuth error: ' . ($_GET['error_description'] ?? $_GET['error']));
        unset($_SESSION['google_oauth_mode']);
        header('Location: ' . $adminUrl . '?error=' . urlencode('Google sign-in was cancelled.'));
        exit;
    }

    // Validate params
    if (empty($_GET['code']) || empty($_GET['state'])) {
        unset($_SESSION['google_oauth_mode']);
        header('Location: ' . $adminUrl . '?error=' . urlencode('Invalid response from Google.'));
        exit;
    }

    // Verify CSRF state
    $expectedState = $_SESSION['google_oauth_state'] ?? '';
    if (!hash_equals($expectedState, $_GET['state'])) {
        error_log('Admin Google OAuth: state mismatch');
        unset($_SESSION['google_oauth_mode']);
        header('Location: ' . $adminUrl . '?error=' . urlencode('Session expired. Please try again.'));
        exit;
    }
    unset($_SESSION['google_oauth_state']);

    // Exchange code for access token
    $clientId     = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
    $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
    $redirectUri  = $_ENV['GOOGLE_REDIRECT_URI'] ?? 'https://oregon.tires/api/auth/google-callback.php';
    $codeVerifier = $_SESSION['google_code_verifier'] ?? '';
    unset($_SESSION['google_code_verifier'], $_SESSION['google_oauth_mode']);

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
        error_log("Admin Google OAuth token exchange failed: HTTP {$httpCode}, response: {$tokenResponse}");
        curl_close($ch);
        header('Location: ' . $adminUrl . '?error=' . urlencode('Google sign-in failed. Please try again.'));
        exit;
    }
    curl_close($ch);

    $tokenData   = json_decode($tokenResponse, true);
    $accessToken = $tokenData['access_token'] ?? '';
    if (empty($accessToken)) {
        header('Location: ' . $adminUrl . '?error=' . urlencode('Google sign-in failed.'));
        exit;
    }

    // Fetch user profile
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
    ]);
    $profileResponse = curl_exec($ch);
    $profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch) || $profileHttpCode !== 200) {
        error_log("Admin Google OAuth userinfo failed: HTTP {$profileHttpCode}");
        curl_close($ch);
        header('Location: ' . $adminUrl . '?error=' . urlencode('Could not retrieve Google profile.'));
        exit;
    }
    curl_close($ch);

    $profile  = json_decode($profileResponse, true);
    $googleId = $profile['sub'] ?? '';
    $gEmail   = strtolower(trim($profile['email'] ?? ''));

    if (empty($gEmail)) {
        header('Location: ' . $adminUrl . '?error=' . urlencode('Your Google account has no email.'));
        exit;
    }

    // Ensure google_id column exists (graceful migration)
    try {
        $pdo->query('SELECT google_id FROM oretir_admins LIMIT 0');
    } catch (\Throwable $_) {
        try {
            $pdo->exec('ALTER TABLE oretir_admins ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER notification_email');
            $pdo->exec('ALTER TABLE oretir_admins ADD UNIQUE INDEX idx_admin_google_id (google_id)');
        } catch (\Throwable $e2) {
            error_log('Admin Google OAuth: could not add google_id column: ' . $e2->getMessage());
        }
    }

    // ── ADMIN CONNECT MODE ──────────────────────────────────────────────────
    if ($oauthMode === 'admin_connect') {
        if (empty($_SESSION['admin_id'])) {
            header('Location: ' . $adminUrl . '?error=' . urlencode('Session expired. Please sign in and try again.'));
            exit;
        }

        $adminId = (int) $_SESSION['admin_id'];

        // Check google_id not already linked to another admin
        $check = $pdo->prepare('SELECT id FROM oretir_admins WHERE google_id = ? AND id != ? LIMIT 1');
        $check->execute([$googleId, $adminId]);
        if ($check->fetch()) {
            header('Location: ' . $adminUrl . '?error=' . urlencode('This Google account is already linked to another admin.'));
            exit;
        }

        $pdo->prepare('UPDATE oretir_admins SET google_id = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$googleId, $adminId]);

        header('Location: ' . $adminUrl . '?success=' . urlencode('Google account connected!'));
        exit;
    }

    // ── ADMIN LOGIN MODE ────────────────────────────────────────────────────
    // 1. Try by google_id
    $stmt = $pdo->prepare('SELECT * FROM oretir_admins WHERE google_id = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$googleId]);
    $admin = $stmt->fetch();

    // 2. If not found, try by email (auto-link on first Google login)
    if (!$admin) {
        $stmt = $pdo->prepare('SELECT * FROM oretir_admins WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$gEmail]);
        $admin = $stmt->fetch();

        if ($admin) {
            $pdo->prepare('UPDATE oretir_admins SET google_id = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$googleId, $admin['id']]);
        }
    }

    if (!$admin) {
        header('Location: ' . $adminUrl . '?error=' . urlencode('No admin account found for this Google account.'));
        exit;
    }

    // Check lockout
    if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
        $remaining = ceil((strtotime($admin['locked_until']) - time()) / 60);
        header('Location: ' . $adminUrl . '?error=' . urlencode("Account locked. Try again in {$remaining} minute(s)."));
        exit;
    }

    // Start admin session
    $pdo->prepare('UPDATE oretir_admins SET login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?')
        ->execute([$admin['id']]);

    session_regenerate_id(true);

    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_email']    = $admin['email'];
    $_SESSION['admin_role']     = $admin['role'];
    $_SESSION['admin_name']     = $admin['display_name'];
    $_SESSION['admin_language'] = $admin['language'] ?? 'both';
    $_SESSION['login_time']     = time();
    $_SESSION['csrf_token']     = bin2hex(random_bytes(32));
    $_SESSION['dashboard_role'] = 'admin';

    // Detect if admin is also an employee
    $empStmt = $pdo->prepare('SELECT id, name, role, group_id FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
    $empStmt->execute([$admin['email']]);
    $emp = $empStmt->fetch();
    if ($emp) {
        $_SESSION['employee_id']   = (int) $emp['id'];
        $_SESSION['employee_name'] = $emp['name'];
        $_SESSION['employee_role'] = $emp['role'];
        if ($emp['group_id']) {
            $grpStmt = $pdo->prepare('SELECT name_en, name_es, permissions FROM oretir_employee_groups WHERE id = ? LIMIT 1');
            $grpStmt->execute([$emp['group_id']]);
            $grp = $grpStmt->fetch();
            if ($grp) {
                $_SESSION['employee_group_id']     = (int) $emp['group_id'];
                $_SESSION['employee_group_name']    = $grp['name_en'];
                $_SESSION['employee_group_name_es'] = $grp['name_es'];
                $_SESSION['employee_permissions']   = json_decode($grp['permissions'], true) ?: ['my_work'];
            }
        }
    }

    header('Location: ' . $adminUrl);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════
// MEMBER GOOGLE OAUTH — existing member/customer flow
// ═════════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/../../includes/member-kit-init.php';
initMemberKit($pdo);

// ── Handle connect mode (linking Google to existing account) ────────────────
if (($_SESSION['google_oauth_mode'] ?? '') === 'connect') {
    if (!empty($_GET['error'])) {
        header('Location: /members?tab=settings&error=' . urlencode('Google sign-in was cancelled or denied.'));
        exit;
    }
    if (empty($_GET['code']) || empty($_GET['state'])) {
        header('Location: /members?tab=settings&error=' . urlencode('Invalid response from Google.'));
        exit;
    }
    try {
        $result = MemberGoogle::exchangeCodeForProfile($_GET['code'], $_GET['state']);
        if (empty($_SESSION['member_id'])) {
            header('Location: /members?error=' . urlencode('Session expired. Please sign in and try again.'));
            exit;
        }
        $memberId = (int) $_SESSION['member_id'];
        MemberGoogle::linkAccount($memberId, $result['profile']['sub'], $result['profile']['email'] ?? null, $result['profile']['picture'] ?? null);
        $redirect = $result['return_url'] ?? '/members?tab=settings';
        $sep = str_contains($redirect, '?') ? '&' : '?';
        header('Location: ' . $redirect . $sep . 'success=' . urlencode('Google account connected!'));
        exit;
    } catch (\Throwable $e) {
        error_log('Google connect error: ' . $e->getMessage());
        $msg = str_contains($e->getMessage(), 'already linked') ? $e->getMessage() : 'Failed to connect Google. Please try again.';
        header('Location: /members?tab=settings&error=' . urlencode($msg));
        exit;
    }
}

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
