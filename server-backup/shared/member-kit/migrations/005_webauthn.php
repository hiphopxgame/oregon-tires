<?php
return function (PDO $pdo, string $tablePrefix = 'member_', string $mode = 'independent') {
    if ($mode === 'hw') {
        $membersTable = 'network_users';
    } else {
        $membersTable = $tablePrefix . 'members';
    }
    $sql = "CREATE TABLE IF NOT EXISTS {$tablePrefix}webauthn_credentials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        credential_id VARBINARY(1024) NOT NULL UNIQUE,
        public_key LONGBLOB NOT NULL,
        sign_count INT DEFAULT 0,
        transports JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_used_at TIMESTAMP NULL,
        FOREIGN KEY (member_id) REFERENCES {$membersTable}(id) ON DELETE CASCADE,
        INDEX (member_id),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
};
