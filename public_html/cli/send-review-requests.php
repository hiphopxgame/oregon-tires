#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Post-Service Google Review Request Cron Script
 *
 * Sends review request emails to customers whose appointments were recently completed.
 * Queries for appointments with status 'completed' where preferred_date is yesterday
 * or the day before, and review_request_sent is not yet flagged.
 *
 * Usage:  php send-review-requests.php
 * Cron:   0 10 * * * php /home/hiphopwo/public_html/---oregon.tires/cli/send-review-requests.php >> /tmp/ot-review-requests.log 2>&1
 *
 * Recommended: Run daily at 10 AM to catch recently completed appointments.
 *
 * Required DB migration:
 * -- ALTER TABLE oretir_appointments ADD COLUMN review_request_sent TINYINT(1) DEFAULT 0;
 */

declare(strict_types=1);

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

// Bootstrap (loads .env, DB, helpers)
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

$yesterday  = (new DateTime('yesterday'))->format('Y-m-d');
$dayBefore  = (new DateTime('-2 days'))->format('Y-m-d');

echo "[" . date('Y-m-d H:i:s') . "] Sending review requests for completed appointments on {$dayBefore} and {$yesterday}\n";

try {
    $db = getDB();

    // Find completed appointments from yesterday or the day before
    // that haven't already received a review request email
    $stmt = $db->prepare(
        "SELECT id, first_name, last_name, email, phone, service,
                preferred_date, preferred_time, vehicle_year, vehicle_make,
                vehicle_model, language, notes
         FROM oretir_appointments
         WHERE status = 'completed'
           AND preferred_date IN (?, ?)
           AND (review_request_sent IS NULL OR review_request_sent = 0)
         ORDER BY preferred_date ASC, preferred_time ASC"
    );
    $stmt->execute([$yesterday, $dayBefore]);
    $appointments = $stmt->fetchAll();

    if (empty($appointments)) {
        echo "No completed appointments to send review requests for.\n";
        exit(0);
    }

    echo "Found " . count($appointments) . " completed appointment(s) to request reviews.\n";

    $sent   = 0;
    $failed = 0;

    foreach ($appointments as $appt) {
        try {
            $success = sendReviewRequestEmail($appt);

            if ($success) {
                $sent++;
                $db->prepare("UPDATE oretir_appointments SET review_request_sent = 1 WHERE id = ?")
                   ->execute([$appt['id']]);
                echo "  ✓ Review request sent to {$appt['email']} (#{$appt['id']})\n";
            } else {
                $failed++;
                echo "  ✗ Review request FAILED for {$appt['email']} (#{$appt['id']})\n";
            }
        } catch (\Throwable $e) {
            $failed++;
            error_log("send-review-requests.php: Error for #{$appt['id']}: " . $e->getMessage());
            echo "  ✗ Review request ERROR for {$appt['email']} (#{$appt['id']}): {$e->getMessage()}\n";
        }
    }

    echo "\nDone: {$sent} sent / {$failed} failed.\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Oregon Tires send-review-requests.php error: " . $e->getMessage());
    exit(1);
}
