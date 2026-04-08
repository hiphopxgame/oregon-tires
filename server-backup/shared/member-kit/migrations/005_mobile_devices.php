<?php
return function (PDO $pdo, string $tablePrefix = 'member_', string $mode = 'independent') {
    if ($mode === 'hw') {
        $membersTable = 'network_users';
    } else {
        $membersTable = $tablePrefix . 'members';
    }
    $sql = "CREATE TABLE IF NOT EXISTS {$tablePrefix}mobile_devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        device_token VARCHAR(512) NOT NULL UNIQUE,
        device_type ENUM('ios', 'android', 'web') NOT NULL,
        device_name VARCHAR(255),
        app_version VARCHAR(20),
        os_version VARCHAR(20),
        is_active BOOLEAN DEFAULT TRUE,
        last_activity TIMESTAMP NULL,
        registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES {$membersTable}(id) ON DELETE CASCADE,
        INDEX (member_id),
        INDEX (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
};
