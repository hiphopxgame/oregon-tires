-- ============================================================================
-- Migration 036: Fix member-kit schema mismatch
-- Fixes: password_resets + email_verifications column names (token → token_hash)
-- Adds: missing member-kit tables + missing members columns
-- Run against: hiphopwo_oregon_tires
-- ============================================================================

-- 1. Fix password_resets: rename token → token_hash (member-kit stores SHA-256 hashes)
ALTER TABLE password_resets
    DROP INDEX uk_token,
    CHANGE COLUMN token token_hash VARCHAR(255) NOT NULL,
    ADD UNIQUE KEY uk_token_hash (token_hash);

-- 2. Fix email_verifications: rename token → token_hash
ALTER TABLE email_verifications
    DROP INDEX uk_token,
    CHANGE COLUMN token token_hash VARCHAR(255) NOT NULL,
    ADD UNIQUE KEY uk_token_hash (token_hash);

-- 3. Add missing columns to members table
ALTER TABLE members
    ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) DEFAULT NULL AFTER last_login_at,
    ADD COLUMN IF NOT EXISTS login_count INT UNSIGNED DEFAULT 0 AFTER last_login_ip,
    ADD COLUMN IF NOT EXISTS login_attempts INT UNSIGNED NOT NULL DEFAULT 0 AFTER login_count,
    ADD COLUMN IF NOT EXISTS locked_until DATETIME DEFAULT NULL AFTER login_attempts,
    ADD COLUMN IF NOT EXISTS registered_site_key VARCHAR(64) DEFAULT NULL AFTER locked_until;

-- 4. Create member_sessions table
CREATE TABLE IF NOT EXISTS member_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    session_token_hash VARCHAR(255) NOT NULL UNIQUE,
    device_id VARCHAR(64) NOT NULL,
    device_fingerprint VARCHAR(255),
    device_name VARCHAR(64) NULL,
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

-- 5. Create member_login_activity table
CREATE TABLE IF NOT EXISTS member_login_activity (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED,
    login_method VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    device_fingerprint VARCHAR(255),
    geo_location VARCHAR(255) NULL,
    success BOOLEAN DEFAULT TRUE,
    failure_reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_member_created (member_id, created_at),
    INDEX idx_success_created (success, created_at),
    INDEX idx_method (login_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create member_site_connections table
CREATE TABLE IF NOT EXISTS member_site_connections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    site_key VARCHAR(64) NOT NULL,
    first_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    connection_count INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY uk_member_site (member_id, site_key),
    KEY idx_site_key (site_key),
    KEY idx_member_id (member_id),
    CONSTRAINT fk_msc_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Create member_login_anomalies table
CREATE TABLE IF NOT EXISTS member_login_anomalies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    login_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_fingerprint VARCHAR(255),
    geo_location VARCHAR(255),
    ip_address VARCHAR(45),
    is_suspicious BOOLEAN DEFAULT FALSE,
    anomaly_reason VARCHAR(255),
    require_additional_verification BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(64) UNIQUE,
    verified_at TIMESTAMP NULL,
    INDEX (member_id),
    INDEX (login_timestamp),
    INDEX (is_suspicious)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
