-- Error Tracking: engine_error_log table
-- Used by engine-kit/includes/error-tracking.php

CREATE TABLE IF NOT EXISTS `engine_error_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_key` VARCHAR(50) NOT NULL,
    `request_id` VARCHAR(32) NOT NULL DEFAULT '',
    `level` ENUM('fatal', 'error', 'warning', 'notice', 'info') NOT NULL DEFAULT 'error',
    `message` TEXT NOT NULL,
    `file` VARCHAR(500) NULL,
    `line` INT UNSIGNED NULL,
    `trace` TEXT NULL,
    `url` VARCHAR(2000) NULL,
    `method` VARCHAR(10) NULL,
    `user_id` INT UNSIGNED NULL,
    `user_agent` VARCHAR(500) NULL,
    `ip_hash` CHAR(64) NULL COMMENT 'SHA-256 of IP for privacy',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_site_key_level` (`site_key`, `level`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_request_id` (`request_id`),
    INDEX `idx_site_key_created` (`site_key`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
