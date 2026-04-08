<?php
declare(strict_types=1);

/**
 * Migration 007: Add site connection tracking
 *
 * Usage: php migrations/007_site_connections.php
 *
 * Adds:
 *   - registered_site_key column to members table (tracks origin site)
 *   - member_site_connections table (tracks all sites a member has accessed)
 *
 * Enables network-wide tracking of member activity across sites.
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabase();

echo "Running migration 007: Add site connection tracking\n";

// Add registered_site_key column to members table
try {
    $pdo->exec("
        ALTER TABLE `members`
        ADD COLUMN IF NOT EXISTS `registered_site_key` VARCHAR(64) DEFAULT NULL
            COMMENT 'site_key where this member first registered'
    ");
    echo "  ✓ Added registered_site_key column to members\n";
} catch (\Throwable $e) {
    echo "  ℹ registered_site_key column already exists or error: " . $e->getMessage() . "\n";
}

// Add index on registered_site_key
try {
    $pdo->exec("
        ALTER TABLE `members`
        ADD INDEX IF NOT EXISTS `idx_registered_site` (`registered_site_key`)
    ");
    echo "  ✓ Added index on registered_site_key\n";
} catch (\Throwable $e) {
    echo "  ℹ Index already exists or error: " . $e->getMessage() . "\n";
}

// Create member_site_connections table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `member_site_connections` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `member_id` INT UNSIGNED NOT NULL,
            `site_key` VARCHAR(64) NOT NULL,
            `first_seen_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                COMMENT 'When this member first accessed this site',
            `last_seen_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                COMMENT 'Most recent login/access from this site',
            `connection_count` INT UNSIGNED NOT NULL DEFAULT 1
                COMMENT 'Total logins from this site',
            UNIQUE KEY `uk_member_site` (`member_id`, `site_key`),
            KEY `idx_site_key` (`site_key`),
            KEY `idx_member_id` (`member_id`),
            CONSTRAINT `fk_msc_member` FOREIGN KEY (`member_id`)
                REFERENCES `members`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Every site a member has connected to, with intro + last-seen timestamps'
    ");
    echo "  ✓ Created member_site_connections table\n";
} catch (\Throwable $e) {
    echo "  ℹ member_site_connections table already exists or error: " . $e->getMessage() . "\n";
}

echo "\nMigration 007 complete!\n";
