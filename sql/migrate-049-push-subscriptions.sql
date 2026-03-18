-- Migration 049: Push notification subscriptions
-- Oregon Tires PWA push notification subscription storage

CREATE TABLE IF NOT EXISTS `oretir_push_subscriptions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `endpoint` TEXT NOT NULL,
    `p256dh_key` VARCHAR(255) NOT NULL DEFAULT '',
    `auth_key` VARCHAR(128) NOT NULL DEFAULT '',
    `customer_id` INT UNSIGNED NULL,
    `member_id` INT UNSIGNED NULL,
    `language` ENUM('english','spanish') NOT NULL DEFAULT 'english',
    `user_agent` VARCHAR(255) NULL,
    `notify_booking_confirm` TINYINT(1) NOT NULL DEFAULT 1,
    `notify_reminders` TINYINT(1) NOT NULL DEFAULT 1,
    `notify_status_updates` TINYINT(1) NOT NULL DEFAULT 1,
    `notify_promotions` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_endpoint` (`endpoint`(500)),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_member` (`member_id`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
