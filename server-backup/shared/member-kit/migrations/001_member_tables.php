<?php
declare(strict_types=1);

/**
 * Migration 001: Create member tables
 *
 * Usage: php migrations/001_member_tables.php
 *
 * Creates tables for independent mode:
 *   - members
 *   - member_preferences
 *   - member_activity
 *   - password_resets
 *   - email_verifications
 *
 * For HW mode, creates prefixed tables:
 *   - {prefix}_member_preferences
 *   - {prefix}_member_activity
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabase();
$mode = $_ENV['MEMBER_MODE'] ?? 'independent';
$prefix = trim($_ENV['MEMBER_TABLE_PREFIX'] ?? '', '_');

echo "Running Member Kit migration (mode: {$mode})\n";

if ($mode === 'independent') {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS members (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            hw_user_id INT UNSIGNED DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            username VARCHAR(50) DEFAULT NULL,
            password_hash VARCHAR(255) DEFAULT NULL,
            display_name VARCHAR(255) DEFAULT NULL,
            avatar_url VARCHAR(500) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            role ENUM('member','moderator','admin') NOT NULL DEFAULT 'member',
            status ENUM('active','suspended','unverified') NOT NULL DEFAULT 'unverified',
            email_verified_at TIMESTAMP NULL DEFAULT NULL,
            last_login_at TIMESTAMP NULL DEFAULT NULL,
            login_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            locked_until TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_email (email),
            UNIQUE KEY uk_username (username),
            INDEX idx_hw_user_id (hw_user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: members\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS member_preferences (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value TEXT DEFAULT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_member_pref (member_id, pref_key),
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: member_preferences\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS member_activity (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50) DEFAULT NULL,
            entity_id INT UNSIGNED DEFAULT NULL,
            details JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            INDEX idx_member_created (member_id, created_at),
            INDEX idx_action (action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: member_activity\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            INDEX idx_token (token_hash),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: password_resets\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS email_verifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            new_email VARCHAR(255) DEFAULT NULL,
            expires_at TIMESTAMP NOT NULL,
            verified_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            INDEX idx_token (token_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: email_verifications\n";

} else {
    // HW Mode: prefixed tables referencing shared users table
    if (empty($prefix)) {
        echo "ERROR: MEMBER_TABLE_PREFIX is required in HW mode\n";
        exit(1);
    }

    $prefixedPrefs = "{$prefix}_member_preferences";
    $prefixedActivity = "{$prefix}_member_activity";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$prefixedPrefs}` (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT(10) UNSIGNED NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value TEXT DEFAULT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_user_pref (user_id, pref_key),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: {$prefixedPrefs}\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$prefixedActivity}` (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT(10) UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50) DEFAULT NULL,
            entity_id INT UNSIGNED DEFAULT NULL,
            details JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_created (user_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: {$prefixedActivity}\n";

    // Password resets + rate limiting (no FK — network mode uses shared users table)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token_hash),
            INDEX idx_expires (expires_at),
            INDEX idx_member (member_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: password_resets\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rate_limit_actions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(100) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action_identifier (action, identifier, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: rate_limit_actions\n";
}

echo "\nMigration complete!\n";
