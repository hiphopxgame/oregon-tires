<?php
/**
 * Migration 002: Session Tracking & 2FA Support
 * Phase 5.1: Added device_name column for user-friendly device labels
 * Phase 5.2: Added geo_location column for login history dashboard
 */
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS member_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL,
            session_token_hash VARCHAR(255) NOT NULL UNIQUE,
            device_id VARCHAR(64) NOT NULL,
            device_fingerprint VARCHAR(255),
            device_name VARCHAR(64) NULL COMMENT 'Phase 5.1: User-defined friendly name',
            ip_address VARCHAR(45),
            user_agent TEXT,
            trusted BOOLEAN DEFAULT FALSE,
            trusted_until TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,

            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            INDEX idx_member_sessions (member_id, expires_at),
            INDEX idx_device_id (device_id),
            INDEX idx_trusted (trusted, trusted_until),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    try {
        $pdo->exec("ALTER TABLE member_sessions ADD COLUMN IF NOT EXISTS device_name VARCHAR(64) NULL");
    } catch (\Throwable $e) {}

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS member_2fa (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL UNIQUE,
            secret_key VARCHAR(255) NOT NULL,
            backup_codes JSON,
            enabled BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            INDEX idx_enabled (enabled)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS member_login_activity (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED,
            login_method VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45),
            device_fingerprint VARCHAR(255),
            geo_location VARCHAR(255) NULL COMMENT 'Phase 5.2: Geographic location of login',
            success BOOLEAN DEFAULT TRUE,
            failure_reason VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            INDEX idx_member_created (member_id, created_at),
            INDEX idx_success_created (success, created_at),
            INDEX idx_method (login_method)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    try {
        $pdo->exec("ALTER TABLE member_login_activity ADD COLUMN IF NOT EXISTS geo_location VARCHAR(255) NULL");
    } catch (\Throwable $e) {}

    $pdo->exec("
        ALTER TABLE members
        ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL,
        ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45),
        ADD COLUMN IF NOT EXISTS login_count INT UNSIGNED DEFAULT 0
    ");
};
