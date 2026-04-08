<?php
declare(strict_types=1);

/**
 * GET /api/member/discord-callback.php
 * Discord OAuth callback — handles both login and connect modes
 *
 * Login mode: finds or creates member by Discord ID, starts session
 * Connect mode: links Discord to existing logged-in member
 */

if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('MEMBER_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}
if (function_exists('initSession')) { initSession(); }
elseif (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$pdo = getDatabase();
MemberAuth::init($pdo);

// Check for errors from Discord
if (!empty($_GET['error'])) {
    error_log('Discord OAuth error: ' . ($_GET['error_description'] ?? $_GET['error']));
    header('Location: /members?error=' . urlencode('Discord sign-in was cancelled or denied.'));
    exit;
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    error_log('Discord OAuth: missing code or state');
    header('Location: /members?error=' . urlencode('Invalid response from Discord. Please try again.'));
    exit;
}

try {
    $result = MemberDiscord::exchangeCodeForProfile($_GET['code'], $_GET['state']);
    $profile = $result['profile'];
    $mode = $result['mode'];
    $returnUrl = $result['return_url'];

    $discordId   = $profile['id'];
    $username    = $profile['username'] ?? null;
    $email       = $profile['email'] ?? null;
    $avatarHash  = $profile['avatar'] ?? null;
    $displayName = $profile['global_name'] ?? $username;

    if ($mode === 'connect') {
        // Connect mode: link Discord to current member
        if (!MemberAuth::isLoggedIn()) {
            header('Location: /members?error=' . urlencode('Session expired. Please sign in and try again.'));
            exit;
        }

        $member = MemberAuth::getCurrentMember();
        if (!$member) {
            header('Location: /members?error=' . urlencode('Session expired.'));
            exit;
        }

        MemberDiscord::linkAccount((int)$member['id'], $discordId, $username, $avatarHash);

        $redirect = $returnUrl ?? '/members?tab=account';
        $separator = str_contains($redirect, '?') ? '&' : '?';
        header('Location: ' . $redirect . $separator . 'success=' . urlencode('Discord connected!'));
        exit;
    }

    // Login mode: find or create member by Discord ID
    $table = MemberAuth::getMembersTable();

    // Try to find existing member by discord_id
    $member = null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE discord_id = ? LIMIT 1");
        $stmt->execute([$discordId]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\Throwable $_) {}

    // Try by email if no discord_id match
    if (!$member && $email) {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Link Discord to existing email-matched account
        if ($member) {
            MemberDiscord::linkAccount((int)$member['id'], $discordId, $username, $avatarHash);
        }
    }

    // Create new member if not found
    if (!$member) {
        if (!$email) {
            header('Location: /members?error=' . urlencode('Discord account has no email. Please use email/password registration.'));
            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO {$table} (email, username, display_name, discord_id, discord_username, status, email_verified_at, created_at)
             VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())"
        );
        $usernameSlug = preg_replace('/[^a-z0-9_-]/', '', strtolower($username ?? 'user'));
        // Ensure unique username
        $baseSlug = $usernameSlug ?: 'user';
        $finalSlug = $baseSlug;
        $i = 1;
        while (true) {
            $check = $pdo->prepare("SELECT id FROM {$table} WHERE username = ? LIMIT 1");
            $check->execute([$finalSlug]);
            if (!$check->fetch()) break;
            $finalSlug = $baseSlug . $i;
            $i++;
        }

        $stmt->execute([$email, $finalSlug, $displayName, $discordId, $username]);
        $memberId = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Set avatar
        if ($avatarHash) {
            $avatarUrl = "https://cdn.discordapp.com/avatars/{$discordId}/{$avatarHash}.png";
            try {
                MemberProfile::update($memberId, ['avatar_url' => $avatarUrl]);
            } catch (\Throwable $_) {}
        }
    }

    // Start authenticated session
    if ($member) {
        MemberAuth::startAuthenticatedSession($member);
    }

    $redirect = $returnUrl ?? '/members';
    header('Location: ' . $redirect);
    exit;

} catch (\Throwable $e) {
    error_log('Discord OAuth callback error: ' . $e->getMessage());
    $errorMsg = str_contains($e->getMessage(), 'already linked')
        ? $e->getMessage()
        : 'Discord sign-in failed. Please try again.';
    header('Location: /members?error=' . urlencode($errorMsg));
    exit;
}
