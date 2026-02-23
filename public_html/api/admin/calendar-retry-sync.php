<?php
/**
 * Oregon Tires — Calendar Retry Sync
 * POST /api/admin/calendar-retry-sync.php
 *
 * Retries failed Google Calendar syncs for appointments.
 * Supports single retry (appointment_id) and bulk retry (retry_all_failed).
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('POST');
    $admin = requireAdmin();
    verifyCsrf();
    $db = getDB();
    $body = getJsonBody();

    // ─── Load Form Kit + GoogleCalendarAction ────────────────────────────
    $credentialsPath = $_ENV['GOOGLE_CALENDAR_CREDENTIALS'] ?? '';
    if (empty($credentialsPath) || !file_exists($credentialsPath)) {
        jsonError('Google Calendar credentials not configured.', 400);
    }

    $calendarId  = $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary';
    $formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../---form-kit';
    require_once $formKitPath . '/loader.php';
    require_once $formKitPath . '/actions/google-calendar.php';

    FormManager::init($db, ['site_key' => 'oregon.tires']);
    GoogleCalendarAction::register([
        'credentials_path' => $credentialsPath,
        'calendar_id'      => $calendarId,
        'send_invites'     => true,
        'timezone'         => 'America/Los_Angeles',
        'default_duration' => 60,
        'service_colors'   => [
            'tire-installation'     => '9',
            'tire-repair'           => '9',
            'oil-change'            => '6',
            'brake-service'         => '11',
            'wheel-alignment'       => '3',
            'tuneup'                => '2',
            'mechanical-inspection' => '7',
            'mobile-service'        => '5',
        ],
    ]);

    // ─── Mode 1: Single retry ────────────────────────────────────────────
    if (!empty($body['appointment_id'])) {
        $appointmentId = (int) $body['appointment_id'];

        $stmt = $db->prepare('SELECT * FROM oretir_appointments WHERE id = ?');
        $stmt->execute([$appointmentId]);
        $appt = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$appt) {
            jsonError('Appointment not found.', 404);
        }

        if ($appt['calendar_sync_status'] !== 'failed' && !empty($appt['google_event_id'])) {
            jsonError('Appointment is already synced successfully.', 400);
        }

        $result = syncAppointmentToCalendar($db, $appt);

        jsonSuccess([
            'appointment_id' => $appointmentId,
            'sync_status'    => $result['status'],
            'google_event_id' => $result['event_id'] ?? null,
            'error'          => $result['error'] ?? null,
        ]);
    }

    // ─── Mode 2: Bulk retry ─────────────────────────────────────────────
    if (!empty($body['retry_all_failed'])) {
        $stmt = $db->prepare(
            "SELECT * FROM oretir_appointments WHERE calendar_sync_status = 'failed' ORDER BY id ASC"
        );
        $stmt->execute();
        $failedAppts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $total     = count($failedAppts);
        $succeeded = 0;
        $failed    = 0;
        $errors    = [];

        foreach ($failedAppts as $appt) {
            $result = syncAppointmentToCalendar($db, $appt);
            if ($result['status'] === 'success') {
                $succeeded++;
            } else {
                $failed++;
                $errors[] = [
                    'appointment_id' => (int) $appt['id'],
                    'reference'      => $appt['reference_number'] ?? '',
                    'error'          => $result['error'] ?? 'Unknown error',
                ];
            }
        }

        jsonSuccess([
            'total'     => $total,
            'succeeded' => $succeeded,
            'failed'    => $failed,
            'errors'    => $errors,
        ]);
    }

    jsonError('Invalid request. Provide appointment_id or retry_all_failed.', 400);

} catch (\Throwable $e) {
    error_log('calendar-retry-sync.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}

/**
 * Attempt to sync a single appointment to Google Calendar.
 *
 * @param PDO   $db   Database connection
 * @param array $appt Appointment row
 * @return array Result with 'status', 'event_id', 'error'
 */
function syncAppointmentToCalendar(\PDO $db, array $appt): array
{
    try {
        $appointmentData = [
            'id'               => (int) $appt['id'],
            'reference_number' => $appt['reference_number'] ?? '',
            'service'          => $appt['service'] ?? '',
            'preferred_date'   => $appt['preferred_date'] ?? '',
            'preferred_time'   => $appt['preferred_time'] ?? '',
            'first_name'       => $appt['first_name'] ?? '',
            'last_name'        => $appt['last_name'] ?? '',
            'email'            => $appt['email'] ?? '',
            'phone'            => $appt['phone'] ?? '',
            'vehicle_year'     => $appt['vehicle_year'] ?? '',
            'vehicle_make'     => $appt['vehicle_make'] ?? '',
            'vehicle_model'    => $appt['vehicle_model'] ?? '',
            'notes'            => $appt['notes'] ?? '',
        ];

        $existingEventId = $appt['google_event_id'] ?? null;

        if ($existingEventId) {
            // Update existing event
            $calEvent  = GoogleCalendarAction::buildEventFromAppointment($appointmentData);
            $calResult = GoogleCalendarAction::updateEvent($existingEventId, $calEvent);
            $eventId   = $calResult['id'] ?? $existingEventId;
        } else {
            // Create new event
            $calEvent  = GoogleCalendarAction::buildEventFromAppointment($appointmentData);
            $calResult = GoogleCalendarAction::createEvent($calEvent);
            $eventId   = $calResult['id'] ?? null;
        }

        if ($eventId) {
            $db->prepare(
                'UPDATE oretir_appointments
                 SET google_event_id = ?, calendar_sync_status = ?, calendar_sync_error = NULL, calendar_synced_at = NOW()
                 WHERE id = ?'
            )->execute([$eventId, 'success', $appt['id']]);

            return ['status' => 'success', 'event_id' => $eventId];
        }

        return ['status' => 'failed', 'error' => 'No event ID returned from Google Calendar API'];
    } catch (\Throwable $e) {
        $errorMsg = substr($e->getMessage(), 0, 500);
        $db->prepare(
            'UPDATE oretir_appointments SET calendar_sync_status = ?, calendar_sync_error = ? WHERE id = ?'
        )->execute(['failed', $errorMsg, $appt['id']]);

        error_log("calendar-retry-sync.php: sync failed for appointment #{$appt['id']}: " . $e->getMessage());
        return ['status' => 'failed', 'error' => $errorMsg];
    }
}
