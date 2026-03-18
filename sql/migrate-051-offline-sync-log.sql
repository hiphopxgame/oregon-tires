-- Migration 051: Offline sync log for deduplication
-- Prevents duplicate offline form replays via unique sync_id

CREATE TABLE IF NOT EXISTS `oretir_offline_sync_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sync_id` VARCHAR(64) NOT NULL,
    `action_type` VARCHAR(50) NOT NULL DEFAULT 'booking',
    `payload_json` TEXT NOT NULL,
    `status` ENUM('received','processed','failed','duplicate') NOT NULL DEFAULT 'received',
    `result_json` TEXT NULL,
    `source_info` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_sync_id` (`sync_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
