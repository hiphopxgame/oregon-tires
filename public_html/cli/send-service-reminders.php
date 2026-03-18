#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Automated Service Reminder Cron Script
 *
 * Scans completed repair orders to create service reminder records, then sends
 * reminder emails to customers whose next service date is within 7 days.
 *
 * Uses DB-driven bilingual templates (email_tpl_service_reminder_*) via sendBrandedTemplateEmail().
 *
 * Usage:  php send-service-reminders.php
 * Cron:   0 9 * * 1 php /home/hiphopwo/public_html/---oregon.tires/cli/send-service-reminders.php >> /tmp/ot-service-reminders.log 2>&1
 *
 * Recommended: Run every Monday at 9 AM.
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

// ─── Service type mapping ────────────────────────────────────────────────────
// Maps appointment service field values to site_settings interval keys
function mapServiceToInterval(string $service): string
{
    $service = strtolower(trim($service));
    $map = [
        'oil_change'        => 'service_interval_oil_change',
        'oil-change'        => 'service_interval_oil_change',
        'oil change'        => 'service_interval_oil_change',
        'tires'             => 'service_interval_tire_rotation',
        'tire_installation' => 'service_interval_tire_rotation',
        'tire-installation' => 'service_interval_tire_rotation',
        'tire_repair'       => 'service_interval_tire_rotation',
        'tire-repair'       => 'service_interval_tire_rotation',
        'tire_rotation'     => 'service_interval_tire_rotation',
        'tire-rotation'     => 'service_interval_tire_rotation',
        'brakes'            => 'service_interval_brake_inspection',
        'brake_service'     => 'service_interval_brake_inspection',
        'brake-service'     => 'service_interval_brake_inspection',
        'alignment'         => 'service_interval_wheel_alignment',
        'wheel_alignment'   => 'service_interval_wheel_alignment',
        'wheel-alignment'   => 'service_interval_wheel_alignment',
        'seasonal_swap'     => 'service_interval_seasonal_swap',
        'seasonal-swap'     => 'service_interval_seasonal_swap',
    ];

    return $map[$service] ?? '';
}

/**
 * Human-readable service type label (English + Spanish).
 */
function serviceTypeLabel(string $intervalKey, string $lang = 'en'): string
{
    $labels = [
        'service_interval_oil_change' => [
            'en' => 'Oil Change',
            'es' => 'Cambio de Aceite',
        ],
        'service_interval_tire_rotation' => [
            'en' => 'Tire Rotation',
            'es' => 'Rotación de Llantas',
        ],
        'service_interval_brake_inspection' => [
            'en' => 'Brake Inspection',
            'es' => 'Inspección de Frenos',
        ],
        'service_interval_wheel_alignment' => [
            'en' => 'Wheel Alignment',
            'es' => 'Alineación de Ruedas',
        ],
        'service_interval_seasonal_swap' => [
            'en' => 'Seasonal Tire Swap',
            'es' => 'Cambio de Llantas de Temporada',
        ],
    ];

    return $labels[$intervalKey][$lang] ?? ucwords(str_replace(['service_interval_', '_'], ['', ' '], $intervalKey));
}

echo "[" . date('Y-m-d H:i:s') . "] Starting automated service reminder processing\n";

try {
    $db = getDB();

    // ─── Step 1: Load service intervals from site_settings ───────────────
    $intervalStmt = $db->query(
        "SELECT setting_key, value_en FROM oretir_site_settings
         WHERE setting_key LIKE 'service_interval_%'"
    );
    $intervals = [];
    foreach ($intervalStmt->fetchAll() as $row) {
        $intervals[$row['setting_key']] = (int) $row['value_en'];
    }

    if (empty($intervals)) {
        echo "WARNING: No service intervals found in site_settings. Using defaults.\n";
        $intervals = [
            'service_interval_oil_change'       => 180,
            'service_interval_tire_rotation'     => 180,
            'service_interval_brake_inspection'  => 365,
            'service_interval_wheel_alignment'   => 365,
            'service_interval_seasonal_swap'     => 180,
        ];
    }

    echo "Loaded " . count($intervals) . " service interval(s).\n";

    // ─── Step 2: Find completed ROs from the last year without a reminder ─
    // Join with appointments to get the service type. Use RO updated_at as
    // the completion date (the date status changed to completed/invoiced).
    $roStmt = $db->prepare(
        "SELECT ro.id AS ro_id, ro.customer_id, ro.vehicle_id,
                ro.mileage_in, ro.updated_at AS completed_at,
                a.service AS appt_service
         FROM oretir_repair_orders ro
         LEFT JOIN oretir_appointments a ON ro.appointment_id = a.id
         WHERE ro.status IN ('completed', 'invoiced')
           AND ro.updated_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
           AND ro.customer_id IS NOT NULL
         ORDER BY ro.updated_at DESC"
    );
    $roStmt->execute();
    $completedROs = $roStmt->fetchAll();

    echo "Found " . count($completedROs) . " completed RO(s) from the last year.\n";

    $remindersCreated = 0;
    $remindersSkipped = 0;

    foreach ($completedROs as $ro) {
        $service = $ro['appt_service'] ?? '';
        $intervalKey = mapServiceToInterval($service);

        // If no mapping found, use a default 180-day interval with the raw service name
        if ($intervalKey === '') {
            $intervalKey = 'service_interval_oil_change'; // default fallback
        }

        $intervalDays = $intervals[$intervalKey] ?? 180;
        $serviceType = str_replace('service_interval_', '', $intervalKey);

        $completedDate = (new DateTime($ro['completed_at']))->format('Y-m-d');
        $nextDueDate = (new DateTime($completedDate))->modify("+{$intervalDays} days")->format('Y-m-d');

        // Check if a reminder already exists for this customer+vehicle+service_type combo
        $existsStmt = $db->prepare(
            "SELECT id FROM oretir_service_reminders
             WHERE customer_id = ?
               AND (vehicle_id = ? OR (vehicle_id IS NULL AND ? IS NULL))
               AND service_type = ?
               AND last_service_date = ?
             LIMIT 1"
        );
        $existsStmt->execute([
            $ro['customer_id'],
            $ro['vehicle_id'],
            $ro['vehicle_id'],
            $serviceType,
            $completedDate,
        ]);

        if ($existsStmt->fetch()) {
            $remindersSkipped++;
            continue;
        }

        // Create the reminder record
        $insertStmt = $db->prepare(
            "INSERT INTO oretir_service_reminders
                (customer_id, vehicle_id, service_type, last_service_date, next_due_date, mileage_at_service, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $insertStmt->execute([
            $ro['customer_id'],
            $ro['vehicle_id'],
            $serviceType,
            $completedDate,
            $nextDueDate,
            $ro['mileage_in'],
        ]);
        $remindersCreated++;
    }

    echo "Reminders created: {$remindersCreated}, skipped (already exist): {$remindersSkipped}\n";

    // ─── Step 3: Send emails for reminders due within 7 days ─────────────
    $dueStmt = $db->prepare(
        "SELECT sr.id, sr.customer_id, sr.vehicle_id, sr.service_type,
                sr.last_service_date, sr.next_due_date, sr.mileage_at_service,
                c.first_name, c.last_name, c.email, c.language,
                v.year AS vehicle_year, v.make AS vehicle_make, v.model AS vehicle_model
         FROM oretir_service_reminders sr
         JOIN oretir_customers c ON sr.customer_id = c.id
         LEFT JOIN oretir_vehicles v ON sr.vehicle_id = v.id
         WHERE sr.status = 'pending'
           AND sr.reminder_sent_at IS NULL
           AND sr.next_due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
           AND sr.next_due_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         ORDER BY sr.next_due_date ASC"
    );
    $dueStmt->execute();
    $dueReminders = $dueStmt->fetchAll();

    echo "\nFound " . count($dueReminders) . " reminder(s) due within 7 days to send.\n";

    $emailSent = 0;
    $emailFailed = 0;

    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

    foreach ($dueReminders as $reminder) {
        $custName = trim($reminder['first_name'] . ' ' . $reminder['last_name']);
        $custLang = ($reminder['language'] ?? 'english') === 'spanish' ? 'es' : 'en';

        // Build vehicle display string
        $vParts = array_filter([
            $reminder['vehicle_year'],
            $reminder['vehicle_make'],
            $reminder['vehicle_model'],
        ]);
        $vehicleDisplay = implode(' ', $vParts) ?: ($custLang === 'es' ? 'su vehículo' : 'your vehicle');

        // Format last service date for display
        $lastDateObj = new DateTime($reminder['last_service_date']);
        $lastDateDisplay = $custLang === 'es'
            ? $lastDateObj->format('d/m/Y')
            : $lastDateObj->format('m/d/Y');

        // Get human-readable service type
        $intervalKey = 'service_interval_' . $reminder['service_type'];
        $serviceLabel = serviceTypeLabel($intervalKey, $custLang);

        $templateVars = [
            'name'              => $custName,
            'service_type'      => $serviceLabel,
            'vehicle'           => $vehicleDisplay,
            'last_service_date' => $lastDateDisplay,
        ];

        $bookingUrl = $baseUrl . '/book-appointment/';

        $result = sendBrandedTemplateEmail(
            $reminder['email'],
            'service_reminder',
            $templateVars,
            $custLang,
            $bookingUrl
        );

        if ($result['success']) {
            $emailSent++;
            $db->prepare(
                "UPDATE oretir_service_reminders SET reminder_sent_at = NOW(), status = 'sent' WHERE id = ?"
            )->execute([$reminder['id']]);

            logEmail(
                'service_reminder',
                "Service reminder ({$reminder['service_type']}) sent to {$reminder['email']} for vehicle {$vehicleDisplay}"
            );

            echo "  ✓ Sent reminder to {$reminder['email']} — {$serviceLabel} ({$vehicleDisplay})\n";
        } else {
            $emailFailed++;
            error_log("send-service-reminders.php: Email failed for #{$reminder['id']}: " . ($result['error'] ?? 'unknown'));
            echo "  ✗ FAILED for {$reminder['email']} — {$serviceLabel}: " . ($result['error'] ?? 'unknown') . "\n";
        }
    }

    echo "\nDone: {$emailSent} sent / {$emailFailed} failed.\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Oregon Tires send-service-reminders.php error: " . $e->getMessage());
    exit(1);
}
