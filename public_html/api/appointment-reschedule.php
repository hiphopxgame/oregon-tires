<?php
/**
 * Oregon Tires — Appointment Reschedule Endpoint (Public, token-protected)
 *
 * GET  /api/appointment-reschedule.php?token=abc123  — Fetch current appointment details
 * POST /api/appointment-reschedule.php               — Reschedule appointment
 *      Body: {"token": "abc123...", "preferred_date": "2026-03-01", "preferred_time": "10:00"}
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

try {
    requireMethod('GET', 'POST');

    // Rate limit: 10 per hour per IP
    checkRateLimit('appointment_reschedule', 10, 3600);

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // ─── Fetch current appointment details ──────────────────────────────
        $token = $_GET['token'] ?? '';

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            jsonError('Invalid reschedule link.', 400);
        }

        $stmt = $db->prepare(
            'SELECT id, reference_number, service, preferred_date, preferred_time,
                    first_name, last_name, status
             FROM oretir_appointments
             WHERE cancel_token = ?
               AND cancel_token_expires > NOW()
               AND status NOT IN (?, ?)'
        );
        $stmt->execute([$token, 'cancelled', 'completed']);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            jsonError('Invalid or expired reschedule link.', 404);
        }

        $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));

        $dateObj = new \DateTime($appointment['preferred_date']);
        $displayDate = $dateObj->format('m/d/Y');

        $timeParts = explode(':', $appointment['preferred_time']);
        $hour = (int) $timeParts[0];
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        $displayTime = $displayHour . ':00 ' . $suffix;

        jsonSuccess([
            'reference_number' => $appointment['reference_number'],
            'service'          => $serviceDisplay,
            'date'             => $displayDate,
            'time'             => $displayTime,
            'raw_date'         => $appointment['preferred_date'],
            'raw_time'         => $appointment['preferred_time'],
            'customer_name'    => $appointment['first_name'] . ' ' . $appointment['last_name'],
            'status'           => $appointment['status'],
        ]);
    }

    // ─── POST: Reschedule the appointment ────────────────────────────────────
    $data = getJsonBody();
    $token = $data['token'] ?? '';

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        jsonError('Invalid reschedule link.', 400);
    }

    // Validate new date and time
    $newDate = sanitize((string) ($data['preferred_date'] ?? ''), 10);
    $newTime = sanitize((string) ($data['preferred_time'] ?? ''), 20);

    if (!$newDate || !$newTime) {
        jsonError('Please select a new date and time.');
    }

    if (!isValidAppointmentDate($newDate)) {
        jsonError('Invalid appointment date. Must be a future date and not a Sunday.');
    }

    if (!isValidTimeSlot($newTime)) {
        jsonError('Invalid time slot. Please select a valid appointment time.');
    }

    // Find appointment by token
    $stmt = $db->prepare(
        'SELECT id, reference_number, service, preferred_date, preferred_time,
                first_name, last_name, email, phone, language,
                vehicle_year, vehicle_make, vehicle_model,
                google_event_id, status
         FROM oretir_appointments
         WHERE cancel_token = ?
           AND cancel_token_expires > NOW()
           AND status NOT IN (?, ?)'
    );
    $stmt->execute([$token, 'cancelled', 'completed']);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        jsonError('Invalid or expired reschedule link.', 404);
    }

    // Check slot availability (same as book.php)
    $maxPerSlot = 2;
    $conflictStmt = $db->prepare(
        'SELECT COUNT(*) FROM oretir_appointments
         WHERE preferred_date = ? AND preferred_time = ?
           AND status NOT IN (?, ?)
           AND id != ?'
    );
    $conflictStmt->execute([$newDate, $newTime, 'cancelled', 'completed', $appointment['id']]);
    $slotCount = (int) $conflictStmt->fetchColumn();

    if ($slotCount >= $maxPerSlot) {
        jsonError('This time slot is fully booked. Please choose a different time.', 409);
    }

    // Generate new cancel token (old one is now stale)
    $newCancelToken = bin2hex(random_bytes(32));
    $newCancelExpires = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Update appointment with new date/time and token
    $db->prepare(
        'UPDATE oretir_appointments
         SET preferred_date = ?, preferred_time = ?, status = ?,
             cancel_token = ?, cancel_token_expires = ?, updated_at = NOW()
         WHERE id = ?'
    )->execute([$newDate, $newTime, 'new', $newCancelToken, $newCancelExpires, $appointment['id']]);

    // Update Google Calendar event if exists
    if (!empty($appointment['google_event_id']) && !empty($_ENV['GOOGLE_CALENDAR_CREDENTIALS'])) {
        try {
            $formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../---form-kit';
            require_once $formKitPath . '/loader.php';
            require_once $formKitPath . '/actions/google-calendar.php';

            FormManager::init($db, ['site_key' => 'oregon.tires']);
            GoogleCalendarAction::register([
                'credentials_path' => $_ENV['GOOGLE_CALENDAR_CREDENTIALS'],
                'calendar_id'      => $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary',
                'send_invites'     => true,
                'timezone'         => 'America/Los_Angeles',
                'default_duration' => 60,
            ]);

            // Build updated event data
            $tz = new \DateTimeZone('America/Los_Angeles');
            $start = new \DateTime("{$newDate} {$newTime}", $tz);
            $end = clone $start;
            $end->modify('+1 hour');

            GoogleCalendarAction::updateEvent($appointment['google_event_id'], [
                'start' => [
                    'dateTime' => $start->format('c'),
                    'timeZone' => 'America/Los_Angeles',
                ],
                'end' => [
                    'dateTime' => $end->format('c'),
                    'timeZone' => 'America/Los_Angeles',
                ],
            ]);

            $db->prepare("INSERT INTO oretir_email_logs (log_type, description, admin_email, created_at) VALUES (?, ?, ?, NOW())")
               ->execute([
                   'calendar_sync',
                   "Calendar: event_rescheduled for {$appointment['reference_number']} ({$appointment['preferred_date']} -> {$newDate})",
                   $appointment['email'],
               ]);
        } catch (\Throwable $e) {
            error_log("appointment-reschedule.php: Google Calendar update error for #{$appointment['id']}: " . $e->getMessage());
        }
    }

    // Send reschedule confirmation email
    try {
        $customerName = $appointment['first_name'] . ' ' . $appointment['last_name'];
        $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));
        $customerLang = ($appointment['language'] ?? 'english') === 'spanish' ? 'es' : 'en';

        $newDateObj = new \DateTime($newDate);
        $displayNewDate = $customerLang === 'es' ? $newDateObj->format('d/m/Y') : $newDateObj->format('m/d/Y');

        $timeParts = explode(':', $newTime);
        $hour = (int) $timeParts[0];
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        $displayNewTime = $displayHour . ':00 ' . $suffix;

        $vehicleParts = array_filter([$appointment['vehicle_year'], $appointment['vehicle_make'], $appointment['vehicle_model']]);
        $vehicleInfo = implode(' ', $vehicleParts);

        // Send full confirmation email with new cancel token
        sendBookingConfirmationEmail(
            $appointment['email'],
            $customerName,
            $serviceDisplay,
            $displayNewDate,
            $displayNewTime,
            $vehicleInfo,
            $customerLang,
            $appointment['reference_number'],
            $appointment['service'],
            $newDate,
            $newTime,
            $newCancelToken
        );

        logEmail('appointment_rescheduled', "Reschedule confirmation sent to {$appointment['email']} for {$appointment['reference_number']}");
    } catch (\Throwable $e) {
        error_log("appointment-reschedule.php: Reschedule email failed for #{$appointment['id']}: " . $e->getMessage());
    }

    // Format response
    $newDateObj = new \DateTime($newDate);
    $responseDateDisplay = $newDateObj->format('m/d/Y');
    $timeParts = explode(':', $newTime);
    $hour = (int) $timeParts[0];
    $suffix = $hour >= 12 ? 'PM' : 'AM';
    $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
    $responseTimeDisplay = $displayHour . ':00 ' . $suffix;

    jsonSuccess([
        'message'          => 'Appointment rescheduled successfully.',
        'reference_number' => $appointment['reference_number'],
        'new_date'         => $responseDateDisplay,
        'new_time'         => $responseTimeDisplay,
    ]);

} catch (\Throwable $e) {
    error_log("appointment-reschedule.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
