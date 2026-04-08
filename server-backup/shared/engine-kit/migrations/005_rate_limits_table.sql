-- Migration 005: Shared rate limits table for the 1vsM network
-- Used by engine-kit RateLimiter class across all sites

CREATE TABLE IF NOT EXISTS engine_rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    identifier VARCHAR(45) NOT NULL COMMENT 'IP address or user identifier',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_lookup (site_key, action, identifier, created_at),
    INDEX idx_cleanup (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
