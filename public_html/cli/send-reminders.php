#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Appointment Reminder Cron Script
 *
 * Sends reminder emails to customers with appointments tomorrow.
 * Only sends to appointments with status 'new' or 'confirmed'.
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
           AND status IN ('new', 'confirmed')
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
        $customerName = $appt['first_name'] . ' ' . $appt['last_name'];
        $customerLang = $appt['language'] === 'spanish' ? 'es' : 'en';
        $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));

        // Format date and time for display
        $dateObj = new DateTime($appt['preferred_date']);
        $displayDate = $customerLang === 'es'
            ? $dateObj->format('d/m/Y')
            : $dateObj->format('m/d/Y');

        $timeParts = explode(':', $appt['preferred_time']);
        $hour = (int) $timeParts[0];
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        $displayTime = $displayHour . ':00 ' . $suffix;

        // Vehicle info
        $vehicleInfo = '';
        if ($appt['vehicle_year'] || $appt['vehicle_make'] || $appt['vehicle_model']) {
            $vehicleParts = array_filter([$appt['vehicle_year'], $appt['vehicle_make'], $appt['vehicle_model']]);
            $vehicleInfo = implode(' ', $vehicleParts);
        }

        // Build reminder email (simple branded HTML)
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $esBody = "Hola {$h($customerName)}, le recordamos que tiene una cita manana.";
        $enBody = "Hi {$h($customerName)}, this is a reminder about your appointment tomorrow.";

        $detailsHtml = <<<HTML
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">
          <tr>
            <td style="padding:8px 0;color:#6b7280;font-size:14px;"><strong>Service:</strong> {$h($serviceDisplay)}</td>
          </tr>
          <tr>
            <td style="padding:8px 0;color:#6b7280;font-size:14px;"><strong>Date:</strong> {$h($displayDate)}</td>
          </tr>
          <tr>
            <td style="padding:8px 0;color:#6b7280;font-size:14px;"><strong>Time:</strong> {$h($displayTime)}</td>
          </tr>
HTML;

        if ($vehicleInfo) {
            $detailsHtml .= <<<HTML
          <tr>
            <td style="padding:8px 0;color:#6b7280;font-size:14px;"><strong>Vehicle:</strong> {$h($vehicleInfo)}</td>
          </tr>
HTML;
        }
        $detailsHtml .= '</table>';

        $phoneLink = '<a href="tel:5033679714" style="color:#15803d;font-weight:700;">(503) 367-9714</a>';

        $esFooter = "Si necesita cambiar o cancelar, llame al {$phoneLink}.";
        $enFooter = "If you need to reschedule or cancel, please call {$phoneLink}.";

        $mexicanBar = 'linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%)';
        $usBar = 'linear-gradient(90deg,#002868 0%,#002868 33%,#bf0a30 33%,#bf0a30 66%,#002868 66%,#002868 100%)';

        $esSection = buildLanguageSection(
            "\xF0\x9F\x87\xB2\xF0\x9F\x87\xBD", 'Español',
            "Recordatorio de Cita",
            $esBody . $detailsHtml,
            'Ver Sitio Web',
            $baseUrl,
            $esFooter,
            'h1',
            $mexicanBar
        );

        $enSection = buildLanguageSection(
            "\xF0\x9F\x87\xBA\xF0\x9F\x87\xB8", 'English',
            "Appointment Reminder",
            $enBody . $detailsHtml,
            'Visit Website',
            $baseUrl,
            $enFooter,
            'h2',
            $usBar
        );

        $divider = '<tr><td style="padding:0 36px;"><div style="height:1px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);"></div></td></tr>';

        if ($customerLang === 'en') {
            $bodySections = $enSection . $divider . $esSection;
            $subject = "⏰ Appointment Reminder — Tomorrow | Recordatorio de Cita — Mañana";
        } else {
            $bodySections = $esSection . $divider . $enSection;
            $subject = "⏰ Recordatorio de Cita — Mañana | Appointment Reminder — Tomorrow";
        }

        $htmlBody = wrapBrandedEmail($bodySections, $baseUrl, $baseUrl, false);

        $textBody = "Appointment Reminder / Recordatorio de Cita\n\n";
        $textBody .= "Service: {$serviceDisplay}\nDate: {$displayDate}\nTime: {$displayTime}\n";
        if ($vehicleInfo) $textBody .= "Vehicle: {$vehicleInfo}\n";
        $textBody .= "\nTo reschedule or cancel, call (503) 367-9714.\n";
        $textBody .= "Para cambiar o cancelar, llame al (503) 367-9714.\n";

        $result = sendMail($appt['email'], $subject, $htmlBody, $textBody);

        if ($result['success']) {
            $sent++;
            // Mark reminder as sent
            $db->prepare("UPDATE oretir_appointments SET reminder_sent = 1 WHERE id = ?")
               ->execute([$appt['id']]);
            logEmail('appointment_reminder', "Reminder sent to {$appt['email']} for appointment #{$appt['id']}");
            echo "  ✓ Sent reminder to {$appt['email']} (#{$appt['id']})\n";
        } else {
            $failed++;
            echo "  ✗ FAILED for {$appt['email']} (#{$appt['id']}): {$result['error']}\n";
            error_log("Reminder failed for appointment #{$appt['id']}: " . ($result['error'] ?? 'unknown'));
        }
    }

    echo "\nDone: {$sent} sent, {$failed} failed.\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Oregon Tires send-reminders.php error: " . $e->getMessage());
    exit(1);
}
