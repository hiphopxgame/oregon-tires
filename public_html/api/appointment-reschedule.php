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
require_once __DIR__ . '/../includes/google-calendar.php';

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

        $displayTime = formatTimeDisplay($appointment['preferred_time']);

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

    // ─── Update Google Calendar event ──────────────────────────────────
    try {
        if (isCalendarSyncEnabled()) {
            updateCalendarEvent($db, (int) $appointment['id']);
        }
    } catch (\Throwable $calErr) {
        error_log("appointment-reschedule.php: Calendar update failed for #{$appointment['id']}: " . $calErr->getMessage());
    }

    // Send reschedule confirmation email
    try {
        $customerName = $appointment['first_name'] . ' ' . $appointment['last_name'];
        $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));
        $customerLang = ($appointment['language'] ?? 'english') === 'spanish' ? 'es' : 'en';

        $newDateObj = new \DateTime($newDate);
        $displayNewDate = $customerLang === 'es' ? $newDateObj->format('d/m/Y') : $newDateObj->format('m/d/Y');

        $displayNewTime = formatTimeDisplay($newTime);

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

    // Send reschedule notification to shop owner
    try {
        $customerName = $appointment['first_name'] . ' ' . $appointment['last_name'];
        $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));

        // Format previous date/time
        $prevDateObj = new \DateTime($appointment['preferred_date']);
        $prevDisplayDate = $prevDateObj->format('m/d/Y');
        $prevDisplayTime = formatTimeDisplay($appointment['preferred_time']);

        // Format new date/time for owner
        $ownerNewDateObj = new \DateTime($newDate);
        $ownerNewDisplayDate = $ownerNewDateObj->format('m/d/Y');
        $ownerNewDisplayTime = formatTimeDisplay($newTime);

        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $ownerSubject = "Appointment Rescheduled — {$appointment['reference_number']}";
        $ownerBody = <<<HTML
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
    <div style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:20px;text-align:center;border-radius:8px 8px 0 0;">
        <h2 style="margin:0;">Appointment Rescheduled</h2>
        <p style="margin:8px 0 0;opacity:0.9;font-size:14px;">{$h($appointment['reference_number'])} — {$h($serviceDisplay)}</p>
    </div>
    <div style="background:#fff;padding:24px;border:1px solid #e0e0e0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="background:#f0f9f0;"><td style="padding:8px 12px;font-weight:bold;color:#555;width:140px;">Reference:</td><td style="padding:8px 12px;font-weight:bold;color:#15803d;font-size:16px;">{$h($appointment['reference_number'])}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Service:</td><td style="padding:8px 12px;">{$h($serviceDisplay)}</td></tr>
            <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Customer:</td><td style="padding:8px 12px;">{$h(trim($customerName))}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Email:</td><td style="padding:8px 12px;"><a href="mailto:{$h($appointment['email'])}">{$h($appointment['email'])}</a></td></tr>
            <tr style="background:#fef3c7;"><td style="padding:8px 12px;font-weight:bold;color:#92400e;">Previous Date:</td><td style="padding:8px 12px;color:#92400e;text-decoration:line-through;">{$h($prevDisplayDate)} at {$h($prevDisplayTime)}</td></tr>
            <tr style="background:#d1fae5;"><td style="padding:8px 12px;font-weight:bold;color:#065f46;">New Date:</td><td style="padding:8px 12px;color:#065f46;font-weight:bold;">{$h($ownerNewDisplayDate)} at {$h($ownerNewDisplayTime)}</td></tr>
            <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Status:</td><td style="padding:8px 12px;">Reset to <strong>new</strong> (needs confirmation)</td></tr>
        </table>
    </div>
    <div style="background:#1a1a2e;padding:12px;text-align:center;font-size:12px;color:#9ca3af;border-radius:0 0 8px 8px;">
        Oregon Tires Auto Care — Reschedule Notification
    </div>
</div>
HTML;

        notifyOwner($ownerSubject, $ownerBody, $appointment['email']);
        logEmail('reschedule_owner_notified', "Owner notified of reschedule for {$appointment['reference_number']} by {$appointment['email']}");
    } catch (\Throwable $e) {
        error_log("appointment-reschedule.php: Owner notification failed for #{$appointment['id']}: " . $e->getMessage());
    }

    // Format response
    $newDateObj = new \DateTime($newDate);
    $responseDateDisplay = $newDateObj->format('m/d/Y');
    $responseTimeDisplay = formatTimeDisplay($newTime);

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
