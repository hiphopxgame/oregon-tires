<?php
return function (PDO $pdo, string $tablePrefix = 'member_', string $mode = 'independent') {
    if ($mode === 'hw') {
        $membersTable = 'network_users';
    } else {
        $membersTable = $tablePrefix . 'members';
    }
    $sql = "CREATE TABLE IF NOT EXISTS {$tablePrefix}fingerprint_rotation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        current_fingerprint VARCHAR(255) NOT NULL,
        previous_fingerprint VARCHAR(255),
        rotated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        FOREIGN KEY (member_id) REFERENCES {$membersTable}(id) ON DELETE CASCADE,
        INDEX (member_id),
        INDEX (session_id),
        UNIQUE (session_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
};
