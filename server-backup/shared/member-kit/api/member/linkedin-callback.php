<?php
declare(strict_types=1);

if (!function_exists('getDatabase')) require_once __DIR__ . '/../../config/database.php';
if (!defined('MEMBER_KIT_PATH')) require_once __DIR__ . '/../../loader.php';
if (function_exists('initSession')) { initSession(); }
elseif (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$pdo = getDatabase();
MemberAuth::init($pdo);

if (!empty($_GET['error'])) {
    error_log('LinkedIn OAuth error: ' . ($_GET['error_description'] ?? $_GET['error']));
    header('Location: /members?error=' . urlencode('LinkedIn sign-in was cancelled.'));
    exit;
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    header('Location: /members?error=' . urlencode('Invalid response from LinkedIn.'));
    exit;
}

try {
    $result = MemberLinkedIn::exchangeCodeForProfile($_GET['code'], $_GET['state']);
    $profile = $result['profile'];
    $mode = $result['mode'];
    $returnUrl = $result['return_url'];

    $linkedinId = $profile['sub'];
    $name       = $profile['name'] ?? null;
    $email      = $profile['email'] ?? null;
    $avatar     = $profile['picture'] ?? null;

    if ($mode === 'connect') {
        if (!MemberAuth::isLoggedIn()) {
            header('Location: /members?error=' . urlencode('Session expired.'));
            exit;
        }
        $member = MemberAuth::getCurrentMember();
        MemberLinkedIn::linkAccount((int)$member['id'], $linkedinId, $name, $email, $avatar);
        $redirect = $returnUrl ?? '/settings';
        $sep = str_contains($redirect, '?') ? '&' : '?';
        header('Location: ' . $redirect . $sep . 'success=' . urlencode('LinkedIn connected!'));
        exit;
    }

    // Login mode
    $table = MemberAuth::getMembersTable();
    $member = null;

    // Find by linkedin_id
    try {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE linkedin_id = ? LIMIT 1");
        $stmt->execute([$linkedinId]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);
    } catch (\Throwable $_) {}

    // Find by email
    if (!$member && $email) {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($member) {
            MemberLinkedIn::linkAccount((int)$member['id'], $linkedinId, $name, $email, $avatar);
        }
    }

    // Create new
    if (!$member) {
        if (!$email) {
            header('Location: /members?error=' . urlencode('LinkedIn account has no email.'));
            exit;
        }
        $username = preg_replace('/[^a-z0-9_-]/', '', strtolower(explode('@', $email)[0]));
        $base = $username ?: 'user';
        $final = $base;
        $i = 1;
        while (true) {
            $check = $pdo->prepare("SELECT id FROM {$table} WHERE username = ? LIMIT 1");
            $check->execute([$final]);
            if (!$check->fetch()) break;
            $final = $base . $i++;
        }

        $pdo->prepare("INSERT INTO {$table} (email, username, display_name, linkedin_id, linkedin_name, linkedin_email, status, email_verified_at, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())")
            ->execute([$email, $final, $name ?? $final, $linkedinId, $name, $email]);
        $memberId = (int)$pdo->lastInsertId();

        if ($avatar) {
            try { MemberProfile::update($memberId, ['avatar_url' => $avatar]); } catch (\Throwable $_) {}
        }

        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    if ($member) MemberAuth::startAuthenticatedSession($member);

    header('Location: ' . ($returnUrl ?? '/members'));
    exit;

} catch (\Throwable $e) {
    error_log('LinkedIn callback error: ' . $e->getMessage());
    $msg = str_contains($e->getMessage(), 'already linked') ? $e->getMessage() : 'LinkedIn sign-in failed.';
    header('Location: /members?error=' . urlencode($msg));
    exit;
}
