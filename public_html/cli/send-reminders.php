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
require_once __DIR__ . '/../includes/sms.php';

$tomorrow = (new DateTime('tomorrow'))->format('Y-m-d');
$smsEnabled = !empty($_ENV['TWILIO_SID']);

echo "[" . date('Y-m-d H:i:s') . "] Sending reminders for appointments on {$tomorrow}\n";
echo "SMS reminders: " . ($smsEnabled ? "enabled" : "disabled") . "\n";

try {
    $db = getDB();

    // Find appointments for tomorrow that haven't been cancelled/completed
    // and haven't already received a reminder
    $stmt = $db->prepare(
        "SELECT id, first_name, last_name, email, phone, service,
                preferred_date, preferred_time, vehicle_year, vehicle_make,
                vehicle_model, language, notes,
                COALESCE(sms_reminder_sent, 0) as sms_reminder_sent,
                COALESCE(reminder_sent, 0) as reminder_sent
         FROM oretir_appointments
         WHERE preferred_date = ?
           AND status IN ('new', 'pending', 'confirmed')
           AND ((reminder_sent IS NULL OR reminder_sent = 0) OR (sms_reminder_sent IS NULL OR sms_reminder_sent = 0))
         ORDER BY preferred_time ASC"
    );
    $stmt->execute([$tomorrow]);
    $appointments = $stmt->fetchAll();

    if (empty($appointments)) {
        echo "No appointments to remind.\n";
        exit(0);
    }

    echo "Found " . count($appointments) . " appointment(s) to remind.\n";

    $emailSent = 0;
    $emailFailed = 0;
    $smsSent = 0;
    $smsFailed = 0;

    foreach ($appointments as $appt) {
        // Send email reminder if not already sent
        if (empty($appt['reminder_sent'])) {
            $success = sendAppointmentReminderEmail($appt);

            if ($success) {
                $emailSent++;
                $db->prepare("UPDATE oretir_appointments SET reminder_sent = 1 WHERE id = ?")
                   ->execute([$appt['id']]);
                echo "  ✓ Email reminder sent to {$appt['email']} (#{$appt['id']})\n";
            } else {
                $emailFailed++;
                echo "  ✗ Email FAILED for {$appt['email']} (#{$appt['id']})\n";
            }
        }

        // Send SMS reminder if enabled and not already sent
        if ($smsEnabled && empty($appt['sms_reminder_sent']) && !empty($appt['phone'])) {
            try {
                $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));

                // Format time for SMS
                $timeParts = explode(':', $appt['preferred_time']);
                $hour = (int) $timeParts[0];
                $suffix = $hour >= 12 ? 'PM' : 'AM';
                $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
                $smsTime = $displayHour . ':00 ' . $suffix;

                $apptLang = ($appt['language'] ?? 'english') === 'spanish' ? 'es' : 'en';

                if ($apptLang === 'es') {
                    $smsMessage = "Oregon Tires: Recordatorio - su cita de {$serviceDisplay} es manana a las {$smsTime}. Llame al (503) 367-9714 para reprogramar.";
                } else {
                    $smsMessage = "Oregon Tires: Reminder - your {$serviceDisplay} appointment is tomorrow at {$smsTime}. Call (503) 367-9714 to reschedule.";
                }

                $smsResult = sendSMS($appt['phone'], $smsMessage);

                if ($smsResult) {
                    $smsSent++;
                    $db->prepare("UPDATE oretir_appointments SET sms_reminder_sent = 1 WHERE id = ?")
                       ->execute([$appt['id']]);
                    logSMS('appointment_reminder', "SMS reminder sent to {$appt['phone']} for appointment #{$appt['id']}");
                    echo "  ✓ SMS reminder sent to {$appt['phone']} (#{$appt['id']})\n";
                } else {
                    $smsFailed++;
                    logSMS('appointment_reminder_failed', "SMS reminder FAILED for {$appt['phone']} appointment #{$appt['id']}");
                    echo "  ✗ SMS FAILED for {$appt['phone']} (#{$appt['id']})\n";
                }
            } catch (\Throwable $e) {
                $smsFailed++;
                error_log("send-reminders.php: SMS error for #{$appt['id']}: " . $e->getMessage());
                echo "  ✗ SMS ERROR for {$appt['phone']} (#{$appt['id']}): {$e->getMessage()}\n";
            }
        }
    }

    echo "\nDone: Email {$emailSent} sent / {$emailFailed} failed";
    if ($smsEnabled) {
        echo ", SMS {$smsSent} sent / {$smsFailed} failed";
    }
    echo ".\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Oregon Tires send-reminders.php error: " . $e->getMessage());
    exit(1);
}
