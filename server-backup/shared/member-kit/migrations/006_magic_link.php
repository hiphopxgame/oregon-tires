<?php
declare(strict_types=1);

/**
 * Migration 006: Magic Link Tables
 *
 * Usage: php migrations/006_magic_link.php
 *
 * Creates tables for passwordless magic link authentication:
 *   - rate_limit_actions — tracks rate limited actions (magic_link, etc)
 *   - magic_link_tokens — stores magic link tokens with expiry
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabase();
$mode = $_ENV['MEMBER_MODE'] ?? 'independent';
$prefix = trim($_ENV['MEMBER_TABLE_PREFIX'] ?? '', '_');

if ($mode === 'hw' && empty($prefix)) {
    echo "ERROR: MEMBER_TABLE_PREFIX is required in HW mode\n";
    exit(1);
}

$tablePrefix = $mode === 'hw' ? "{$prefix}_" : '';

echo "Running Magic Link Tables migration (mode: {$mode})\n";

try {
    // Create rate_limit_actions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$tablePrefix}rate_limit_actions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(100) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action_identifier (action, identifier, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: {$tablePrefix}rate_limit_actions\n";

    // Create magic_link_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$tablePrefix}magic_link_tokens (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token_hash),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Created: {$tablePrefix}magic_link_tokens\n";

    echo "\nMigration complete!\n";

} catch (\Throwable $e) {
    echo "ERROR: {$e->getMessage()}\n";
    exit(1);
}
