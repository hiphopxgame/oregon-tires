-- ============================================================================
-- Oregon Tires — Member Kit Tables + Appointment FK
-- Run against: hiphopwo_oregon_tires
-- ============================================================================

-- Members table (customer accounts)
CREATE TABLE IF NOT EXISTS members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hw_user_id INT UNSIGNED DEFAULT NULL COMMENT 'HW SSO linked user ID',
    email VARCHAR(254) NOT NULL,
    username VARCHAR(100) DEFAULT NULL,
    display_name VARCHAR(200) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    avatar_url VARCHAR(500) DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    sso_provider VARCHAR(50) DEFAULT NULL,
    language VARCHAR(10) DEFAULT 'english',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_email (email),
    UNIQUE KEY uk_hw_user_id (hw_user_id),
    KEY idx_sso_provider (sso_provider),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Member preferences (key-value store)
CREATE TABLE IF NOT EXISTS member_preferences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    pref_key VARCHAR(100) NOT NULL,
    pref_value TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_member_pref (member_id, pref_key),
    CONSTRAINT fk_pref_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Member activity log
CREATE TABLE IF NOT EXISTS member_activity (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_member_activity (member_id, created_at),
    CONSTRAINT fk_activity_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_token (token),
    KEY idx_member_reset (member_id),
    CONSTRAINT fk_reset_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email verification tokens
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL,
    new_email VARCHAR(254) DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_token (token),
    KEY idx_member_verify (member_id),
    CONSTRAINT fk_verify_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add member_id FK to appointments
ALTER TABLE oretir_appointments
    ADD COLUMN IF NOT EXISTS member_id INT UNSIGNED DEFAULT NULL AFTER id,
    ADD INDEX IF NOT EXISTS idx_member_id (member_id);

-- Note: MariaDB 10.6 may not support IF NOT EXISTS for constraints.
-- Run this separately if the constraint doesn't exist:
-- ALTER TABLE oretir_appointments
--     ADD CONSTRAINT fk_appointment_member
--     FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL;
