#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Retry Failed Calendar Sync
 * Cron: every 30 minutes
 * Retries appointments with calendar_sync_status='failed' (max 3 attempts).
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/google-calendar.php';

echo "[" . date('Y-m-d H:i:s') . "] Retrying failed calendar syncs...\n";

if (!isCalendarSyncEnabled()) {
    echo "Calendar sync is disabled. Exiting.\n";
    exit(0);
}

try {
    $db = getDB();

    $stmt = $db->query(
        "SELECT id, reference_number, first_name, last_name, calendar_sync_attempts
         FROM oretir_appointments
         WHERE calendar_sync_status = 'failed'
           AND calendar_sync_attempts < 3
           AND status NOT IN ('cancelled')
         ORDER BY updated_at ASC
         LIMIT 20"
    );
    $failed = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($failed)) {
        echo "No failed syncs to retry.\n";
        exit(0);
    }

    $retried = 0;
    $succeeded = 0;
    $stillFailed = 0;

    foreach ($failed as $appt) {
        $retried++;
        echo "  Retrying #{$appt['id']} ({$appt['reference_number']}) — attempt " . ((int) $appt['calendar_sync_attempts'] + 1) . "... ";

        try {
            $result = createCalendarEvent($db, (int) $appt['id']);
            if ($result['success']) {
                echo "✓ Synced (event: {$result['event_id']})\n";
                $succeeded++;
            } else {
                echo "✗ Failed: {$result['error']}\n";
                $stillFailed++;
            }
        } catch (\Throwable $e) {
            echo "✗ Error: {$e->getMessage()}\n";
            $stillFailed++;
            error_log("retry-calendar-sync: Error for #{$appt['id']}: " . $e->getMessage());
        }
    }

    echo "\nDone: {$retried} retried / {$succeeded} succeeded / {$stillFailed} still failed.\n";
    exit(0);

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("retry-calendar-sync error: " . $e->getMessage());
    exit(1);
}
