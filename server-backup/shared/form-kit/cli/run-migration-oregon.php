<?php
/**
 * Run Form Kit migration for Oregon Tires.
 * Execute on server: php /home/hiphopwo/shared/form-kit/cli/run-migration-oregon.php
 */

require_once __DIR__ . '/helpers.php';
requireCli();

// Load Oregon Tires' database config
$dotenvPath = '/home/hiphopwo/public_html/---oregon.tires/.env';
if (file_exists($dotenvPath)) {
    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (str_contains($line, '=')) {
            putenv($line);
            [$key, $val] = explode('=', $line, 2);
            $val = trim($val, '"\'');
            $_ENV[trim($key)] = $val;
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$name = $_ENV['DB_NAME'] ?? '';
$user = $_ENV['DB_USER'] ?? '';
$pass = $_ENV['DB_PASSWORD'] ?? '';

$pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$prefix = 'oretir_';
echo "Running Form Kit migration (prefix: {$prefix})\n";
echo "Database: {$name}\n\n";

// 1. form_submissions
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `{$prefix}form_submissions` (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        site_key VARCHAR(64) NOT NULL,
        form_type VARCHAR(32) NOT NULL DEFAULT 'contact',
        name VARCHAR(128) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(32) DEFAULT NULL,
        subject VARCHAR(255) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        form_data JSON DEFAULT NULL,
        action_results JSON DEFAULT NULL,
        ip_hash VARCHAR(64) DEFAULT NULL,
        user_agent VARCHAR(512) DEFAULT NULL,
        status ENUM('new','read','replied','archived','spam') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_site_status (site_key, status),
        INDEX idx_site_created (site_key, created_at DESC),
        INDEX idx_form_type (form_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: {$prefix}form_submissions\n";

// 2. form_configs
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `{$prefix}form_configs` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        site_key VARCHAR(64) NOT NULL UNIQUE,
        form_type VARCHAR(32) NOT NULL DEFAULT 'contact',
        recipient_email VARCHAR(255) DEFAULT NULL,
        subject_prefix VARCHAR(64) DEFAULT '[Contact]',
        auto_reply TINYINT(1) DEFAULT 0,
        auto_reply_subject VARCHAR(255) DEFAULT NULL,
        auto_reply_body TEXT DEFAULT NULL,
        success_message VARCHAR(512) DEFAULT NULL,
        rate_limit_max INT UNSIGNED DEFAULT 5,
        rate_limit_window INT UNSIGNED DEFAULT 3600,
        actions JSON DEFAULT NULL,
        custom_fields JSON DEFAULT NULL,
        template VARCHAR(64) DEFAULT 'default',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: {$prefix}form_configs\n";

// 3. form_rate_limits
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `{$prefix}form_rate_limits` (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        site_key VARCHAR(64) NOT NULL,
        action VARCHAR(32) NOT NULL,
        identifier VARCHAR(64) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_lookup (site_key, action, identifier, created_at),
        INDEX idx_cleanup (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: {$prefix}form_rate_limits\n";

echo "\nMigration complete!\n";
