<?php
/**
 * Oregon Tires — Create Admin Account for Joslyn
 * Run: php cli/create-joslyn-admin.php
 *
 * Creates admin account for Joslymv13@hotmail.com with Spanish preference
 * and sends branded bilingual invite email (Spanish primary).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mail.php';

$email       = 'Joslymv13@hotmail.com';
$displayName = 'Joslyn';
$role        = 'admin';
$language    = 'es'; // Spanish as primary language

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
$db = getDB();

echo "\n━━━ Creating Admin Account for Joslyn ━━━\n";
echo "  Email:    {$email}\n";
echo "  Name:     {$displayName}\n";
echo "  Role:     {$role}\n";
echo "  Language: {$language} (Spanish primary)\n\n";

// Check if already exists
$check = $db->prepare('SELECT id, password_reset_token FROM oretir_admins WHERE email = ? LIMIT 1');
$check->execute([$email]);
$existing = $check->fetch();

if ($existing) {
    echo "  ⚠ Already exists (id={$existing['id']}). Regenerating token…\n";
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    $db->prepare(
        'UPDATE oretir_admins SET password_reset_token = ?, password_reset_expires = ?, language = ?, updated_at = NOW() WHERE id = ?'
    )->execute([$token, $expires, $language, $existing['id']]);
} else {
    // Create new admin account
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    $hash    = hashPassword(bin2hex(random_bytes(32))); // unusable random hash

    $stmt = $db->prepare(
        'INSERT INTO oretir_admins
            (email, password_hash, display_name, role, language, is_active,
             password_reset_token, password_reset_expires, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 1, ?, ?, NOW(), NOW())'
    );
    $stmt->execute([$email, $hash, $displayName, $role, $language, $token, $expires]);
    echo "  ✓ Created admin account (id=" . $db->lastInsertId() . ")\n";
}

$setupUrl = $baseUrl . '/admin/setup-password.html?token=' . $token;
echo "  ✓ Token generated (expires: {$expires})\n";
echo "  ✓ Setup URL: {$setupUrl}\n";

// Send branded bilingual email with Spanish as primary language
$result = sendBrandedSetupEmail($email, $displayName, $setupUrl, $language, 'Admin');

if ($result['success']) {
    echo "  ✅ Invite email sent successfully! (Spanish primary, English secondary)\n";
} else {
    echo "  ❌ Email FAILED: {$result['error']}\n";
}

echo "\n━━━ Done! ━━━\n\n";
