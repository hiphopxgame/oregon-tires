#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Appointment Reminder Cron Script
 *
 * Sends reminder emails to customers with appointments tomorrow.
 * Uses DB-driven bilingual templates (email_tpl_reminder_*) via sendAppointmentReminderEmail().
 * Only sends to appointments with status 'new', 'pending', or 'confirmed'.
 *
 * Usage:  php send-reminders.php
 * Cron:   0 18 * * * php /home/hiphopwo/public_html/---oregon.tires/cli/send-reminders.php >> /tmp/ot-reminders.log 2>&1
 *
 * Recommended: Run daily at 6 PM to remind for next-day appointments.
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

$tomorrow = (new DateTime('tomorrow'))->format('Y-m-d');

echo "[" . date('Y-m-d H:i:s') . "] Sending reminders for appointments on {$tomorrow}\n";

try {
    $db = getDB();

    // Find appointments for tomorrow that haven't been cancelled/completed
    // and haven't already received a reminder
    $stmt = $db->prepare(
        "SELECT id, first_name, last_name, email, phone, service,
                preferred_date, preferred_time, vehicle_year, vehicle_make,
                vehicle_model, language, notes
         FROM oretir_appointments
         WHERE preferred_date = ?
           AND status IN ('new', 'pending', 'confirmed')
           AND (reminder_sent IS NULL OR reminder_sent = 0)
         ORDER BY preferred_time ASC"
    );
    $stmt->execute([$tomorrow]);
    $appointments = $stmt->fetchAll();

    if (empty($appointments)) {
        echo "No appointments to remind.\n";
        exit(0);
    }

    echo "Found " . count($appointments) . " appointment(s) to remind.\n";

    $sent = 0;
    $failed = 0;

    foreach ($appointments as $appt) {
        $success = sendAppointmentReminderEmail($appt);

        if ($success) {
            $sent++;
            // Mark reminder as sent
            $db->prepare("UPDATE oretir_appointments SET reminder_sent = 1 WHERE id = ?")
               ->execute([$appt['id']]);
            echo "  ✓ Sent reminder to {$appt['email']} (#{$appt['id']})\n";
        } else {
            $failed++;
            echo "  ✗ FAILED for {$appt['email']} (#{$appt['id']})\n";
        }
    }

    echo "\nDone: {$sent} sent, {$failed} failed.\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Oregon Tires send-reminders.php error: " . $e->getMessage());
    exit(1);
}
