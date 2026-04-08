<?php
/**
 * Migration 008 — User Site Roles
 *
 * Creates the user_site_roles table for site-scoped role assignments.
 * Design:
 *   - No "standard" rows — absence = standard (default for all users)
 *   - No "super_admin" rows — determined solely by users.is_admin = 1
 *   - ENUM: admin, manager, support
 *   - granted_by tracks who assigned the role (audit trail)
 *   - UNIQUE(user_id, site_key) — one role per user per site
 */

return function (PDO $pdo): void {
    // Only applies to network/hw mode (shared users table)
    $mode = $_ENV['MEMBER_MODE'] ?? 'independent';
    if ($mode === 'independent') {
        echo "  Skipping 008_user_site_roles - independent mode\n";
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_site_roles (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            site_key VARCHAR(64) NOT NULL,
            role ENUM('admin', 'manager', 'support') NOT NULL,
            granted_by INT UNSIGNED DEFAULT NULL,
            granted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_user_site (user_id, site_key),
            KEY idx_site_key (site_key),
            KEY idx_user_id (user_id),
            CONSTRAINT fk_usr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_usr_granted FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "  Created user_site_roles table\n";
};
