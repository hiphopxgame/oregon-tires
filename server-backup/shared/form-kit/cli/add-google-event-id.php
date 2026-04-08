<?php
declare(strict_types=1);

/**
 * Migration: Add google_event_id to appointment tables.
 *
 * Usage:
 *   php add-google-event-id.php --site=oregon.tires
 *   php add-google-event-id.php --site=nisa.tax
 *   php add-google-event-id.php --site=all
 *
 * On server:
 *   php /home/hiphopwo/shared/form-kit/cli/add-google-event-id.php --site=all
 */

require_once __DIR__ . '/helpers.php';
requireCli();

echo "Google Calendar Event ID Migration\n";
echo str_repeat('=', 50) . "\n\n";

$site = 'all';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--site=')) {
        $site = substr($arg, 7);
    }
}

$sites = [];

if ($site === 'all' || $site === 'oregon.tires') {
    $sites['oregon.tires'] = [
        'env_path' => '/home/hiphopwo/public_html/---oregon.tires/.env',
        'table'    => 'oretir_appointments',
    ];
}

if ($site === 'all' || $site === 'nisa.tax') {
    $sites['nisa.tax'] = [
        'env_path' => '/home/hiphopwo/public_html/---nisa.tax/.env',
        'table'    => 'nisatax_appointments',
    ];
}

if (empty($sites)) {
    echo "Unknown site: {$site}\n";
    echo "Usage: php add-google-event-id.php --site=oregon.tires|nisa.tax|all\n";
    exit(1);
}

foreach ($sites as $siteName => $config) {
    echo "--- {$siteName} ---\n";

    $envPath = $config['env_path'];
    if (!file_exists($envPath)) {
        echo "  SKIP: .env not found at {$envPath}\n\n";
        continue;
    }

    // Parse .env manually (avoid requiring Composer autoload)
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $eqPos = strpos($line, '=');
        if ($eqPos === false) continue;
        $key = trim(substr($line, 0, $eqPos));
        $val = trim(substr($line, $eqPos + 1));
        // Strip surrounding quotes
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

    if (!$dbname || !$user) {
        echo "  SKIP: Missing DB credentials in .env\n\n";
        continue;
    }

    try {
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset={$charset}",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $table = $config['table'];

        // Check if column already exists
        $cols = $pdo->query("SHOW COLUMNS FROM {$table} LIKE 'google_event_id'")->fetchAll();
        if (!empty($cols)) {
            echo "  OK: google_event_id column already exists\n\n";
            continue;
        }

        // Add the column
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN google_event_id VARCHAR(255) DEFAULT NULL");
        echo "  DONE: Added google_event_id column to {$table}\n\n";

    } catch (\Throwable $e) {
        echo "  ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "Migration complete.\n";
