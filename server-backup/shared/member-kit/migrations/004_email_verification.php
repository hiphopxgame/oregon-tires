<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
$pdo = getDatabase();
$mode = $_ENV['MEMBER_MODE'] ?? 'independent';
echo "Running migration 004 — Email Verification Requirement (mode: {$mode})\n";
if ($mode === 'independent') {
    try {
        $pdo->exec("ALTER TABLE members ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL DEFAULT NULL");
        echo "  OK: email_verified_at column present on members\n";
    } catch (\Throwable $e) {}
    try {
        $pdo->exec("ALTER TABLE members ADD INDEX IF NOT EXISTS idx_email_verified_at (email_verified_at)");
        echo "  OK: idx_email_verified_at index present\n";
    } catch (\Throwable $e) {}
    $pdo->exec("CREATE TABLE IF NOT EXISTS member_email_resend_log (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        member_id INT UNSIGNED NOT NULL,
        ip_address VARCHAR(45) NOT NULL DEFAULT '',
        sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
        INDEX idx_member_sent (member_id, sent_at),
        INDEX idx_ip_sent (ip_address, sent_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  OK: member_email_resend_log table ready\n";
} else {
    $result = $pdo->query("SELECT COUNT(*) AS cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'");
    $row = $result->fetch();
    if ((int) $row['cnt'] > 0) {
        echo "  OK: users table exists (HW mode — email_verified_at managed by HHW)\n";
    } else {
        echo "  ERROR: users table not found. Run HHW migrations first.\n";
        exit(1);
    }
}
echo "\nMigration 004 complete!\n";
