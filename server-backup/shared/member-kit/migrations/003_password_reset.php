<?php
declare(strict_types=1);

/**
 * Migration 003: Verify password reset tables
 *
 * Usage: php migrations/003_password_reset.php
 *
 * This migration ensures the password_resets and email_verifications tables
 * exist. They are created in migration 001, but this provides a checkpoint.
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabase();
$mode = $_ENV['MEMBER_MODE'] ?? 'independent';

echo "Verifying password reset tables (mode: {$mode})\n";

if ($mode === 'independent') {
    // Verify password_resets table exists
    $result = $pdo->query("
        SELECT COUNT(*) as count FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'password_resets'
    ");
    $row = $result->fetch();

    if ($row['count'] > 0) {
        echo "  Verified: password_resets table exists\n";
    } else {
        echo "  ERROR: password_resets table not found. Run migration 001.\n";
        exit(1);
    }

    // Verify email_verifications table exists
    $result = $pdo->query("
        SELECT COUNT(*) as count FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'email_verifications'
    ");
    $row = $result->fetch();

    if ($row['count'] > 0) {
        echo "  Verified: email_verifications table exists\n";
    } else {
        echo "  ERROR: email_verifications table not found. Run migration 001.\n";
        exit(1);
    }
} else {
    // HW mode: just verify members table exists
    $result = $pdo->query("
        SELECT COUNT(*) as count FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
    ");
    $row = $result->fetch();

    if ($row['count'] > 0) {
        echo "  Verified: users table exists\n";
    } else {
        echo "  ERROR: users table not found\n";
        exit(1);
    }
}

echo "\nMigration complete!\n";
