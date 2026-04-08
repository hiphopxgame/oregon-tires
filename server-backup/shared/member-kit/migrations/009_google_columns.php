<?php
/**
 * Migration 009: Add google_email column to members/users tables
 *
 * Oregon Tires already has google_id (runtime ALTER in google-callback.php).
 * 1oh6.events already has google_id.
 * Both need google_email added.
 * 1vsm.com uses user_connections table — no migration needed for that pattern.
 *
 * Usage: php migrations/009_google_columns.php
 */

declare(strict_types=1);

// Detect environment
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

// Try to load database connection
$configPaths = [
    __DIR__ . '/../config/database.php',
    __DIR__ . '/../../includes/bootstrap.php',
];

$pdo = null;
foreach ($configPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        if (function_exists('getDatabase')) {
            $pdo = getDatabase();
        } elseif (function_exists('getDB')) {
            $pdo = getDB();
        }
        break;
    }
}

if (!$pdo) {
    echo "ERROR: Could not establish database connection.\n";
    echo "Run this migration from the site directory that has the database.\n";
    exit(1);
}

$table = $argv[1] ?? 'members';
echo "Migration 009: Adding google_email column to `{$table}` table...\n";

// Check if google_email already exists
try {
    $pdo->query("SELECT google_email FROM {$table} LIMIT 0");
    echo "  google_email column already exists — skipping.\n";
} catch (\Throwable $e1) {
    // Check if google_id exists first
    $hasGoogleId = false;
    try {
        $pdo->query("SELECT google_id FROM {$table} LIMIT 0");
        $hasGoogleId = true;
    } catch (\Throwable $e2) {
        echo "  google_id column does not exist — adding it first.\n";
        try {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN google_id VARCHAR(255) DEFAULT NULL");
            $pdo->exec("ALTER TABLE {$table} ADD INDEX idx_google_id (google_id)");
            $hasGoogleId = true;
            echo "  google_id column added.\n";
        } catch (\Throwable $e3) {
            echo "  ERROR adding google_id: " . $e3->getMessage() . "\n";
        }
    }

    if ($hasGoogleId) {
        try {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN google_email VARCHAR(255) DEFAULT NULL AFTER google_id");
            echo "  google_email column added successfully.\n";
        } catch (\Throwable $e4) {
            echo "  ERROR adding google_email: " . $e4->getMessage() . "\n";
        }
    }
}

echo "Migration 009 complete.\n";
