<?php
declare(strict_types=1);

/**
 * GET /api/member/magic-link-verify.php
 * Passwordless Authentication — Verify magic link token and create session
 *
 * Query parameters:
 *   token=<token> — Magic link token from email
 *   email=<email> — Email address for verification
 *   return=<url> — Optional redirect URL after success
 *
 * Response: Redirects to profile or error page on failure
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

try {
    $token = trim($_GET['token'] ?? '');
    $email = trim($_GET['email'] ?? '');

    if (!$token || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \RuntimeException('Invalid link');
    }

    // Hash token to compare with database
    $tokenHash = hash('sha256', $token);

    $pdo = getDatabase();
    $prefix = MemberAuth::prefixedTable('');

    // Find and verify the token
    $stmt = $pdo->prepare("
        SELECT id, created_at FROM {$prefix}magic_link_tokens
        WHERE email = ?
        AND token_hash = ?
        AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$email, $tokenHash]);
    $verification = $stmt->fetch();

    if (!$verification) {
        throw new \RuntimeException('Invalid or expired link');
    }

    // Mark the token as used
    $stmt = $pdo->prepare("
        UPDATE {$prefix}magic_link_tokens
        SET used_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$verification['id']]);

    // Find or create member by email
    $membersTable = MemberAuth::getMembersTable();
    $memberIdColumn = MemberAuth::getMemberIdColumn();

    // Try to find existing member
    $stmt = $pdo->prepare("SELECT * FROM {$membersTable} WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $member = $stmt->fetch();

    if (!$member) {
        // Create new member for independent mode
        if (MemberAuth::isHwMode()) {
            throw new \RuntimeException('Account not found');
        }

        // Auto-register new member
        $username = explode('@', $email)[0];
        $displayName = $username;

        $stmt = $pdo->prepare("
            INSERT INTO {$membersTable}
            (email, username, display_name, status, created_at, updated_at)
            VALUES (?, ?, ?, 'active', NOW(), NOW())
        ");
        $stmt->execute([$email, $username, $displayName]);

        // Fetch the newly created member
        $stmt = $pdo->prepare("SELECT * FROM {$membersTable} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $member = $stmt->fetch();
    }

    if (!$member) {
        throw new \RuntimeException('Failed to create account');
    }

    // Create authenticated session
    MemberAuth::startAuthenticatedSession($member);

    // Log activity
    MemberProfile::logActivity(
        (int) $member[$memberIdColumn],
        'login_magic_link',
        null,
        null,
        ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
    );

    // Determine redirect URL
    $returnUrl = $_GET['return'] ?? null;
    if ($returnUrl && filter_var($returnUrl, FILTER_VALIDATE_URL) === false && strpos($returnUrl, '/') === 0) {
        // Allow relative URLs only
        $redirectUrl = $returnUrl;
    } else {
        $redirectUrl = '/member/profile';
    }

    header('Location: ' . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8'));
    exit;

} catch (\Throwable $e) {
    error_log('Magic link verification error: ' . $e->getMessage());
    $errorMsg = urlencode($e->getMessage());
    header('Location: /member/login?error=' . $errorMsg . '&method=magic_link');
    exit;
}
