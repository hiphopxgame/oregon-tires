<?php
declare(strict_types=1);

/**
 * Form Kit — Rate Limit Cleanup
 *
 * Deletes expired rate limit entries from the database.
 * Run via cron every 6 hours:
 *   0 */6 * * * php /path/to/form-kit/cli/cleanup-rate-limits.php
 *
 * Options:
 *   --max-age=SECONDS   Maximum age to retain (default: 86400 = 24 hours)
 */

require_once __DIR__ . '/helpers.php';
requireCli();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../loader.php';

// Parse --max-age argument
$maxAge = 86400;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--max-age=')) {
        $maxAge = max(60, (int) substr($arg, 10));
    }
}

try {
    FormManager::init(getDatabase());
    $deleted = FormRateLimiter::cleanup($maxAge);
    echo date('Y-m-d H:i:s') . " | Cleaned up {$deleted} expired rate limit entries (max-age: {$maxAge}s)\n";
} catch (\Throwable $e) {
    error_log('Form Kit cleanup error: ' . $e->getMessage());
    echo date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
