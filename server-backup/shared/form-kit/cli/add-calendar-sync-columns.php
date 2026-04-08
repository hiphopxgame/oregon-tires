<?php
declare(strict_types=1);

/**
 * Migration: Add calendar sync tracking columns to appointment tables.
 *
 * Adds calendar_sync_status, calendar_sync_error, calendar_synced_at
 * to both oretir_appointments and nisatax_appointments.
 *
 * Run on server:
 *   php /home/hiphopwo/shared/form-kit/cli/add-calendar-sync-columns.php --site=all
 */

require_once __DIR__ . '/helpers.php';
requireCli();

echo "Calendar Sync Columns Migration\n";
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
    exit(1);
}

foreach ($sites as $siteName => $config) {
    echo "--- {$siteName} ---\n";

    $envPath = $config['env_path'];
    if (!file_exists($envPath)) {
        echo "  SKIP: .env not found at {$envPath}\n\n";
        continue;
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

    try {
        $pdo = new PDO(
            "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=" . ($env['DB_CHARSET'] ?? 'utf8mb4'),
            $env['DB_USER'],
            $env['DB_PASSWORD'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $table = $config['table'];
        $added = 0;

        $columns = [
            'calendar_sync_status' => "ENUM('pending','success','failed') DEFAULT NULL",
            'calendar_sync_error'  => "TEXT DEFAULT NULL",
            'calendar_synced_at'   => "DATETIME DEFAULT NULL",
        ];

        foreach ($columns as $col => $definition) {
            $check = $pdo->query("SHOW COLUMNS FROM {$table} LIKE '{$col}'")->fetchAll();
            if (!empty($check)) {
                echo "  OK: {$col} already exists\n";
                continue;
            }
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$col} {$definition}");
            echo "  ADDED: {$col}\n";
            $added++;
        }

        echo $added > 0 ? "  DONE: {$added} column(s) added\n\n" : "  DONE: All columns already exist\n\n";

    } catch (\Throwable $e) {
        echo "  ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "Migration complete.\n";
