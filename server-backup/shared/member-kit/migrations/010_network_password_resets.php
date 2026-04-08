<?php
declare(strict_types=1);

/**
 * Migration 010: Create password_resets and rate_limit_actions for network/hw mode
 *
 * Bug fix: Migration 001 only creates these tables in independent mode.
 * Sites running in network/hw mode (e.g., 1vsm.com) were missing them,
 * causing silent failures in forgot-password flow.
 *
 * Uses CREATE TABLE IF NOT EXISTS — safe to run on any mode.
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabase();

echo "Running migration 010: network password_resets + rate_limit_actions\n";

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
echo "  Created: password_resets (IF NOT EXISTS)\n";

$pdo->exec("
    CREATE TABLE IF NOT EXISTS rate_limit_actions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(100) NOT NULL,
        identifier VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_action_identifier (action, identifier, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: rate_limit_actions (IF NOT EXISTS)\n";

echo "\nMigration 010 complete!\n";
