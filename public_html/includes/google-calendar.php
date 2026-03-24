<?php
/**
 * Oregon Tires — Google Calendar Sync Service
 * Uses a service account to manage events on a shared Google Calendar.
 * Non-blocking: failures are logged and retried by cron.
 */

declare(strict_types=1);

/**
 * Check if calendar sync is enabled.
 */
function isCalendarSyncEnabled(): bool
{
    return ($_ENV['GOOGLE_CALENDAR_SYNC_ENABLED'] ?? '0') === '1'
        && !empty($_ENV['GOOGLE_CALENDAR_ID'])
        && !empty($_ENV['GOOGLE_SERVICE_ACCOUNT_JSON']);
}

/**
 * Get an authenticated Google Calendar service client.
 */
function getGoogleCalendarClient(): ?\Google\Service\Calendar
{
    static $service = null;
    if ($service !== null) return $service;

    $jsonPath = $_ENV['GOOGLE_SERVICE_ACCOUNT_JSON'] ?? '';
    if ($jsonPath === '') return null;

    // Resolve relative paths from project root
    if ($jsonPath[0] !== '/') {
        $jsonPath = dirname(__DIR__) . '/' . $jsonPath;
    }

    if (!file_exists($jsonPath)) {
        error_log("Google Calendar: service account JSON not found at {$jsonPath}");
        return null;
    }

    try {
        $client = new \Google\Client();
        $client->setAuthConfig($jsonPath);
        $client->addScope(\Google\Service\Calendar::CALENDAR);
        $service = new \Google\Service\Calendar($client);
        return $service;
    } catch (\Throwable $e) {
        error_log("Google Calendar: auth failed — " . $e->getMessage());
        return null;
    }
}

/**
 * Create a Google Calendar event for an appointment.
 *
 * @return array{success: bool, event_id: ?string, error: ?string}
 */
function createCalendarEvent(PDO $db, int $appointmentId): array
{
    if (!isCalendarSyncEnabled()) {
        return ['success' => false, 'event_id' => null, 'error' => 'Calendar sync disabled'];
    }

    $calendarService = getGoogleCalendarClient();
    if (!$calendarService) {
        markSyncFailed($db, 'oretir_appointments', $appointmentId, 'Calendar client unavailable');
        return ['success' => false, 'event_id' => null, 'error' => 'Calendar client unavailable'];
    }

    $stmt = $db->prepare(
        'SELECT a.id, a.reference_number, a.service, a.services, a.preferred_date, a.preferred_time,
                a.first_name, a.last_name, a.phone, a.email, a.vehicle_year, a.vehicle_make, a.vehicle_model, a.notes
         FROM oretir_appointments a WHERE a.id = ?'
    );
    $stmt->execute([$appointmentId]);
    $appt = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$appt) {
        return ['success' => false, 'event_id' => null, 'error' => 'Appointment not found'];
    }

    try {
        $calendarId = $_ENV['GOOGLE_CALENDAR_ID'];
        $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));
        $customerName = trim($appt['first_name'] . ' ' . $appt['last_name']);

        // Build vehicle info
        $vehicleParts = array_filter([$appt['vehicle_year'], $appt['vehicle_make'], $appt['vehicle_model']]);
        $vehicleInfo = implode(' ', $vehicleParts);

        // Parse start time
        $startDt = new \DateTime($appt['preferred_date'] . ' ' . $appt['preferred_time'], new \DateTimeZone('America/Los_Angeles'));
        $endDt = clone $startDt;
        $endDt->modify('+1 hour');

        // Build description
        $descParts = [
            "Ref: {$appt['reference_number']}",
            "Customer: {$customerName}",
            "Phone: {$appt['phone']}",
            "Email: {$appt['email']}",
        ];
        if ($vehicleInfo) $descParts[] = "Vehicle: {$vehicleInfo}";
        if ($appt['notes']) $descParts[] = "Notes: {$appt['notes']}";

        $event = new \Google\Service\Calendar\Event([
            'summary' => "{$serviceDisplay} — {$customerName}" . ($vehicleInfo ? " ({$vehicleInfo})" : ''),
            'description' => implode("\n", $descParts),
            'start' => [
                'dateTime' => $startDt->format(\DateTimeInterface::RFC3339),
                'timeZone' => 'America/Los_Angeles',
            ],
            'end' => [
                'dateTime' => $endDt->format(\DateTimeInterface::RFC3339),
                'timeZone' => 'America/Los_Angeles',
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'popup', 'minutes' => 30],
                ],
            ],
        ]);

        $created = $calendarService->events->insert($calendarId, $event);
        $eventId = $created->getId();

        // Update appointment with event ID
        $db->prepare(
            'UPDATE oretir_appointments
             SET google_event_id = ?, calendar_sync_status = ?, calendar_synced_at = NOW(), calendar_sync_error = NULL
             WHERE id = ?'
        )->execute([$eventId, 'success', $appointmentId]);

        return ['success' => true, 'event_id' => $eventId, 'error' => null];
    } catch (\Throwable $e) {
        $msg = $e->getMessage();
        error_log("Google Calendar createEvent failed for appointment #{$appointmentId}: {$msg}");
        markSyncFailed($db, 'oretir_appointments', $appointmentId, $msg);
        return ['success' => false, 'event_id' => null, 'error' => $msg];
    }
}

/**
 * Update an existing Google Calendar event (e.g., after reschedule).
 */
function updateCalendarEvent(PDO $db, int $appointmentId): array
{
    if (!isCalendarSyncEnabled()) {
        return ['success' => false, 'error' => 'Calendar sync disabled'];
    }

    $stmt = $db->prepare(
        'SELECT id, google_event_id, reference_number, service, preferred_date, preferred_time,
                first_name, last_name, phone, email, vehicle_year, vehicle_make, vehicle_model, notes
         FROM oretir_appointments WHERE id = ?'
    );
    $stmt->execute([$appointmentId]);
    $appt = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$appt || empty($appt['google_event_id'])) {
        // No existing event — create one instead
        return createCalendarEvent($db, $appointmentId);
    }

    $calendarService = getGoogleCalendarClient();
    if (!$calendarService) {
        markSyncFailed($db, 'oretir_appointments', $appointmentId, 'Calendar client unavailable');
        return ['success' => false, 'error' => 'Calendar client unavailable'];
    }

    try {
        $calendarId = $_ENV['GOOGLE_CALENDAR_ID'];
        $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));
        $customerName = trim($appt['first_name'] . ' ' . $appt['last_name']);
        $vehicleParts = array_filter([$appt['vehicle_year'], $appt['vehicle_make'], $appt['vehicle_model']]);
        $vehicleInfo = implode(' ', $vehicleParts);

        $startDt = new \DateTime($appt['preferred_date'] . ' ' . $appt['preferred_time'], new \DateTimeZone('America/Los_Angeles'));
        $endDt = clone $startDt;
        $endDt->modify('+1 hour');

        $descParts = [
            "Ref: {$appt['reference_number']}",
            "Customer: {$customerName}",
            "Phone: {$appt['phone']}",
            "Email: {$appt['email']}",
        ];
        if ($vehicleInfo) $descParts[] = "Vehicle: {$vehicleInfo}";
        if ($appt['notes']) $descParts[] = "Notes: {$appt['notes']}";

        $event = $calendarService->events->get($calendarId, $appt['google_event_id']);
        $event->setSummary("{$serviceDisplay} — {$customerName}" . ($vehicleInfo ? " ({$vehicleInfo})" : ''));
        $event->setDescription(implode("\n", $descParts));
        $event->setStart(new \Google\Service\Calendar\EventDateTime([
            'dateTime' => $startDt->format(\DateTimeInterface::RFC3339),
            'timeZone' => 'America/Los_Angeles',
        ]));
        $event->setEnd(new \Google\Service\Calendar\EventDateTime([
            'dateTime' => $endDt->format(\DateTimeInterface::RFC3339),
            'timeZone' => 'America/Los_Angeles',
        ]));

        $calendarService->events->update($calendarId, $appt['google_event_id'], $event);

        $db->prepare(
            'UPDATE oretir_appointments SET calendar_sync_status = ?, calendar_synced_at = NOW(), calendar_sync_error = NULL WHERE id = ?'
        )->execute(['success', $appointmentId]);

        return ['success' => true, 'error' => null];
    } catch (\Throwable $e) {
        $msg = $e->getMessage();
        error_log("Google Calendar updateEvent failed for appointment #{$appointmentId}: {$msg}");
        markSyncFailed($db, 'oretir_appointments', $appointmentId, $msg);
        return ['success' => false, 'error' => $msg];
    }
}

/**
 * Delete a Google Calendar event (e.g., on cancellation).
 */
function deleteCalendarEvent(PDO $db, int $appointmentId): array
{
    if (!isCalendarSyncEnabled()) {
        return ['success' => false, 'error' => 'Calendar sync disabled'];
    }

    $stmt = $db->prepare('SELECT google_event_id FROM oretir_appointments WHERE id = ?');
    $stmt->execute([$appointmentId]);
    $eventId = $stmt->fetchColumn();

    if (!$eventId) {
        return ['success' => true, 'error' => null]; // Nothing to delete
    }

    $calendarService = getGoogleCalendarClient();
    if (!$calendarService) {
        return ['success' => false, 'error' => 'Calendar client unavailable'];
    }

    try {
        $calendarId = $_ENV['GOOGLE_CALENDAR_ID'];
        $calendarService->events->delete($calendarId, $eventId);

        $db->prepare(
            'UPDATE oretir_appointments SET google_event_id = NULL, calendar_sync_status = NULL, calendar_synced_at = NULL WHERE id = ?'
        )->execute([$appointmentId]);

        return ['success' => true, 'error' => null];
    } catch (\Google\Service\Exception $e) {
        // 404/410 = already deleted — treat as success
        if ($e->getCode() === 404 || $e->getCode() === 410) {
            $db->prepare('UPDATE oretir_appointments SET google_event_id = NULL, calendar_sync_status = NULL WHERE id = ?')
               ->execute([$appointmentId]);
            return ['success' => true, 'error' => null];
        }
        error_log("Google Calendar deleteEvent failed for appointment #{$appointmentId}: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    } catch (\Throwable $e) {
        error_log("Google Calendar deleteEvent failed for appointment #{$appointmentId}: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Mark a sync as failed on a given table/row.
 */
function markSyncFailed(PDO $db, string $table, int $id, string $error): void
{
    $db->prepare(
        "UPDATE {$table}
         SET calendar_sync_status = 'failed',
             calendar_sync_error = ?,
             calendar_sync_attempts = COALESCE(calendar_sync_attempts, 0) + 1
         WHERE id = ?"
    )->execute([$error, $id]);
}
