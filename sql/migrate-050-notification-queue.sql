-- Migration 050: Notification queue for push notifications
-- Supports bilingual messages, targeting, scheduling, and retry logic

CREATE TABLE IF NOT EXISTS `oretir_notification_queue` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` INT UNSIGNED NULL,
    `target_type` ENUM('subscription','customer','member','broadcast') NOT NULL DEFAULT 'subscription',
    `target_id` INT UNSIGNED NULL,
    `notification_type` VARCHAR(50) NOT NULL,
    `title_en` VARCHAR(255) NOT NULL DEFAULT '',
    `title_es` VARCHAR(255) NOT NULL DEFAULT '',
    `body_en` TEXT NOT NULL,
    `body_es` TEXT NOT NULL,
    `url` VARCHAR(500) NULL,
    `icon` VARCHAR(255) NOT NULL DEFAULT '/assets/icon-192.png',
    `badge` VARCHAR(255) NOT NULL DEFAULT '/assets/favicon.png',
    `data_json` TEXT NULL,
    `status` ENUM('pending','sent','failed','expired') NOT NULL DEFAULT 'pending',
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `max_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `scheduled_at` DATETIME NULL,
    `sent_at` DATETIME NULL,
    `error_message` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status_scheduled` (`status`, `scheduled_at`),
    KEY `idx_subscription` (`subscription_id`),
    KEY `idx_type` (`notification_type`),
    KEY `idx_target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
