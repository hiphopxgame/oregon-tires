<?php
declare(strict_types=1);

/**
 * Migration 001: Create form-kit tables
 *
 * Usage: php migrations/001-form-tables.php
 *
 * Environment variables:
 *   FORM_KIT_TABLE_PREFIX — optional prefix for table names (e.g. "oretir_")
 *
 * Creates tables:
 *   - {prefix}form_submissions  — submitted form data
 *   - {prefix}form_configs      — per-site form configuration
 *   - {prefix}form_rate_limits  — rate limiting records
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabase();
$prefix = trim($_ENV['FORM_KIT_TABLE_PREFIX'] ?? '');

if ($prefix !== '' && !str_ends_with($prefix, '_')) {
    $prefix .= '_';
}

$submissionsTable = $prefix . 'form_submissions';
$configsTable     = $prefix . 'form_configs';
$rateLimitsTable  = $prefix . 'form_rate_limits';

echo "Running Form Kit migration (prefix: '{$prefix}')\n";
echo "================================================\n\n";

// 1. form_submissions
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `{$submissionsTable}` (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        site_key VARCHAR(64) NOT NULL,
        form_type VARCHAR(32) NOT NULL DEFAULT 'contact',
        name VARCHAR(128) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(32) DEFAULT NULL,
        subject VARCHAR(255) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        form_data JSON DEFAULT NULL,
        action_results JSON DEFAULT NULL,
        ip_hash VARCHAR(64) DEFAULT NULL,
        user_agent VARCHAR(512) DEFAULT NULL,
        status ENUM('new','read','replied','archived','spam') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_site_status (site_key, status),
        INDEX idx_site_created (site_key, created_at DESC),
        INDEX idx_form_type (form_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: {$submissionsTable}\n";

// 2. form_configs
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `{$configsTable}` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        site_key VARCHAR(64) NOT NULL UNIQUE,
        form_type VARCHAR(32) NOT NULL DEFAULT 'contact',
        recipient_email VARCHAR(255) DEFAULT NULL,
        subject_prefix VARCHAR(64) DEFAULT '[Contact]',
        auto_reply TINYINT(1) DEFAULT 0,
        auto_reply_subject VARCHAR(255) DEFAULT NULL,
        auto_reply_body TEXT DEFAULT NULL,
        success_message VARCHAR(512) DEFAULT NULL,
        rate_limit_max INT UNSIGNED DEFAULT 5,
        rate_limit_window INT UNSIGNED DEFAULT 3600,
        actions JSON DEFAULT NULL,
        custom_fields JSON DEFAULT NULL,
        template VARCHAR(64) DEFAULT 'default',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: {$configsTable}\n";

// 3. form_rate_limits
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `{$rateLimitsTable}` (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        site_key VARCHAR(64) NOT NULL,
        action VARCHAR(32) NOT NULL,
        identifier VARCHAR(64) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_lookup (site_key, action, identifier, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: {$rateLimitsTable}\n";

echo "\nForm Kit migration complete!\n";
