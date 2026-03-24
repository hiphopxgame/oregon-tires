-- Migration 066: Health monitoring system
-- Stores uptime checks, SSL status, backup logs, feature test results, and daily digests

CREATE TABLE IF NOT EXISTS oretir_health_checks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    check_type ENUM('uptime','ssl','backup','feature_test','cron_freshness','disk','email','database') NOT NULL,
    status ENUM('ok','warn','fail','skip') NOT NULL DEFAULT 'ok',
    label VARCHAR(100) NOT NULL COMMENT 'Human-readable check name',
    details TEXT DEFAULT NULL COMMENT 'JSON payload with check-specific data',
    response_time_ms INT UNSIGNED DEFAULT NULL COMMENT 'Latency for HTTP/DB checks',
    checked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_checked (check_type, checked_at),
    INDEX idx_status_checked (status, checked_at),
    INDEX idx_checked_at (checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
