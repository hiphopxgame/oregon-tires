<?php
declare(strict_types=1);

/**
 * Migration: Create Form Kit tables for nisa.tax
 * Uses nisatax_ prefix, connects to hiphopwo_nisa_tax database.
 *
 * Run on server:
 *   php /home/hiphopwo/shared/form-kit/cli/run-migration-nisatax.php
 */

require_once __DIR__ . '/helpers.php';
requireCli();

echo "Form Kit Migration — nisa.tax\n";
echo str_repeat('=', 50) . "\n\n";

$envPath = '/home/hiphopwo/public_html/---nisa.tax/.env';
if (!file_exists($envPath)) {
    echo "ERROR: .env not found at {$envPath}\n";
    exit(1);
}

$envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($envLines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    $eqPos = strpos($line, '=');
    if ($eqPos === false) continue;
    $key = trim(substr($line, 0, $eqPos));
    $val = trim(substr($line, $eqPos + 1));
    if ((str_starts_with($val, '"') && str_ends_with($val, '"'))
        || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
        $val = substr($val, 1, -1);
    }
    $env[$key] = $val;
}

$host    = $env['DB_HOST'] ?? 'localhost';
$dbname  = $env['DB_NAME'] ?? '';
$user    = $env['DB_USER'] ?? '';
$pass    = $env['DB_PASSWORD'] ?? '';
$charset = $env['DB_CHARSET'] ?? 'utf8mb4';

echo "Database: {$dbname}\n";
echo "Prefix:   nisatax_\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset={$charset}",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS nisatax_form_submissions (
            id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_key        VARCHAR(50)  NOT NULL DEFAULT 'nisa.tax',
            form_type       VARCHAR(50)  NOT NULL DEFAULT 'contact',
            name            VARCHAR(200) DEFAULT NULL,
            email           VARCHAR(254) DEFAULT NULL,
            phone           VARCHAR(30)  DEFAULT NULL,
            subject         VARCHAR(200) DEFAULT NULL,
            message         TEXT         DEFAULT NULL,
            form_data       JSON         DEFAULT NULL,
            ip_address      VARCHAR(45)  DEFAULT NULL,
            user_agent      VARCHAR(500) DEFAULT NULL,
            status          ENUM('new','read','replied','archived','spam') NOT NULL DEFAULT 'new',
            is_read         TINYINT(1)   NOT NULL DEFAULT 0,
            action_results  JSON         DEFAULT NULL,
            created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at      DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_site_key (site_key),
            INDEX idx_form_type (form_type),
            INDEX idx_status (status),
            INDEX idx_is_read (is_read),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  DONE: nisatax_form_submissions\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS nisatax_form_configs (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_key   VARCHAR(50)  NOT NULL,
            form_type  VARCHAR(50)  NOT NULL DEFAULT 'contact',
            config     JSON         NOT NULL,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE INDEX idx_site_form (site_key, form_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  DONE: nisatax_form_configs\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS nisatax_form_rate_limits (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            action     VARCHAR(50) NOT NULL DEFAULT 'submit',
            hits       INT UNSIGNED NOT NULL DEFAULT 1,
            window_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_action (ip_address, action),
            INDEX idx_window (window_start)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  DONE: nisatax_form_rate_limits\n";

    echo "\nAll tables created successfully.\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
