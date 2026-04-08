-- Member Kit Schema Documentation

-- member_sessions: Track active user sessions with device info
-- Used for: Session management, device verification, trusted device tracking
CREATE TABLE member_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    session_token_hash VARCHAR(255) NOT NULL UNIQUE,      -- SHA256 hash of token
    device_id VARCHAR(64) NOT NULL,                         -- localStorage device identifier
    device_fingerprint VARCHAR(255),                        -- Browser fingerprint (user agent + screen + etc)
    ip_address VARCHAR(45),                                 -- IPv4 or IPv6
    user_agent TEXT,                                        -- Browser user agent
    trusted BOOLEAN DEFAULT FALSE,                          -- User marked "Remember this device"
    trusted_until TIMESTAMP NULL,                           -- Trusted device expiry (default 30 days)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,     -- Updated on every request
    expires_at TIMESTAMP NOT NULL,                          -- Session expiry (default 1 hour)

    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_member_sessions (member_id, expires_at),     -- Cleanup query
    INDEX idx_device_id (device_id),                        -- Device lookup
    INDEX idx_trusted (trusted, trusted_until),             -- Trusted device check
    INDEX idx_expires (expires_at)                          -- Session cleanup
);

-- member_2fa: Two-factor authentication setup
-- Used for: 2FA enrollment, backup codes, TOTP verification
CREATE TABLE member_2fa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL UNIQUE,                 -- One 2FA record per user
    secret_key VARCHAR(255) NOT NULL,                       -- Base32 TOTP secret
    backup_codes JSON,                                      -- Array of 8-char backup codes (one-time use)
    enabled BOOLEAN DEFAULT FALSE,                          -- 2FA active for this user
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_enabled (enabled)
);

-- member_login_activity: Audit trail of all login attempts
-- Used for: Login history, fraud detection, security audit
CREATE TABLE member_login_activity (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED,                                 -- NULL if account not found
    login_method VARCHAR(50) NOT NULL,                      -- 'email', 'google', 'sso', 'magic_link', 'webauthn'
    ip_address VARCHAR(45),
    device_fingerprint VARCHAR(255),
    success BOOLEAN DEFAULT TRUE,                           -- TRUE for successful logins
    failure_reason VARCHAR(255),                            -- 'invalid_password', 'account_locked', etc
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_member_created (member_id, created_at),      -- User login history
    INDEX idx_success_created (success, created_at),       -- Failed login attempts
    INDEX idx_method (login_method)                         -- Login method usage stats
);

-- Cleanup cron job (recommended daily):
-- DELETE FROM member_sessions WHERE expires_at < NOW();
-- DELETE FROM member_login_activity WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
