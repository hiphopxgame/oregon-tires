<?php
/**
 * Oregon Tires — CLI: Fetch Google Reviews
 * Cron: 0 3 * * * php /home/hiphopwo/public_html/---oregon.tires/cli/fetch-google-reviews.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/google-reviews.php';

try {
    $db = getDB();
    $result = fetchGoogleReviews($db);

    if (isset($result['error'])) {
        echo "Error: {$result['error']}\n";
        exit(1);
    }

    echo "Google reviews fetched: {$result['total']} total, {$result['imported']} new\n";
} catch (\Throwable $e) {
    error_log('fetch-google-reviews.php error: ' . $e->getMessage());
    echo "Fatal error: {$e->getMessage()}\n";
    exit(1);
}
