#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Push Notification Queue Processor
 *
 * Processes pending push notifications from oretir_notification_queue.
 * Follows the same pattern as send-reminders.php.
 *
 * Usage:  php send-push-notifications.php
 * Cron:   every 5 minutes
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/push.php';

echo "[" . date('Y-m-d H:i:s') . "] Processing push notification queue...\n";

try {
    // Check VAPID keys are configured
    $publicKey = getVapidPublicKey();
    if (empty($publicKey)) {
        echo "VAPID keys not configured. Run: php cli/generate-vapid-keys.php\n";
        exit(0);
    }

    $stats = processNotificationQueue(50);

    echo "Done: {$stats['sent']} sent, {$stats['failed']} failed, {$stats['skipped']} skipped.\n";

    // Clean up old processed/failed entries (30-day retention)
    $db = getDB();
    $cleaned = $db->exec(
        "DELETE FROM oretir_notification_queue
         WHERE status IN ('sent', 'failed', 'expired')
           AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    if ($cleaned > 0) {
        echo "Cleaned up {$cleaned} old queue entries.\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("send-push-notifications.php error: " . $e->getMessage());
    exit(1);
}
