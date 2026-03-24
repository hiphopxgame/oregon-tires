#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Google Business Profile Weekly Sync
 * Cron: 0 7 * * 1 (Monday 7AM)
 * Syncs business hours to GBP, fetches insights and Q&A.
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/google-business.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Google Business Profile sync...\n";

$accountId = $_ENV['GOOGLE_GBP_ACCOUNT_ID'] ?? '';
$locationId = $_ENV['GOOGLE_GBP_LOCATION_ID'] ?? '';

if (!$accountId || !$locationId) {
    echo "GBP not configured (missing GOOGLE_GBP_ACCOUNT_ID or GOOGLE_GBP_LOCATION_ID). Exiting.\n";
    exit(0);
}

try {
    $db = getDB();

    // 1. Sync business hours
    echo "\n1. Syncing business hours...\n";
    $hoursResult = syncBusinessHours($db);
    if ($hoursResult['success']) {
        echo "   ✓ Business hours synced.\n";
    } else {
        echo "   ✗ Hours sync failed: " . ($hoursResult['error'] ?? 'unknown') . "\n";
    }

    // 2. Fetch insights
    echo "\n2. Fetching insights...\n";
    $insightsResult = fetchGbpInsights($db);
    if ($insightsResult['success']) {
        echo "   ✓ Insights fetched ({$insightsResult['stored']} days stored).\n";
    } else {
        echo "   ✗ Insights fetch failed: " . ($insightsResult['error'] ?? 'unknown') . "\n";
    }

    // 3. Fetch Q&A
    echo "\n3. Fetching Q&A...\n";
    $qnaResult = fetchGbpQnA($db);
    if ($qnaResult['success']) {
        echo "   ✓ Q&A fetched ({$qnaResult['stored']} questions stored).\n";
    } else {
        echo "   ✗ Q&A fetch failed: " . ($qnaResult['error'] ?? 'unknown') . "\n";
    }

    echo "\nSync complete.\n";
    exit(0);

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("sync-google-business error: " . $e->getMessage());
    exit(1);
}
