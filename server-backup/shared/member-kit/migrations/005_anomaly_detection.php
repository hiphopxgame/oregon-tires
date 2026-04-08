<?php
return function (PDO $pdo, string $tablePrefix = 'member_', string $mode = 'independent') {
    $sql = "CREATE TABLE IF NOT EXISTS {$tablePrefix}login_anomalies (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
};
