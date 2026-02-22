<?php
/**
 * Oregon Tires — Appointment Cancel Endpoint (Public, token-protected)
 *
 * GET  /api/appointment-cancel.php?token=abc123  — Fetch appointment details
 * POST /api/appointment-cancel.php               — Cancel appointment
 *      Body: {"token": "abc123..."}
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

try {
    requireMethod('GET', 'POST');

    // Rate limit: 10 per hour per IP
    checkRateLimit('appointment_cancel', 10, 3600);

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // ─── Fetch appointment details by token ──────────────────────────────
        $token = $_GET['token'] ?? '';

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            jsonError('Invalid cancellation link.', 400);
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
            jsonError('Invalid or expired cancellation link.', 404);
        }

        $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));

        // Format date
        $dateObj = new \DateTime($appointment['preferred_date']);
        $displayDate = $dateObj->format('m/d/Y');

        // Format time
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
            'customer_name'    => $appointment['first_name'] . ' ' . $appointment['last_name'],
            'status'           => $appointment['status'],
        ]);
    }

    // ─── POST: Cancel the appointment ────────────────────────────────────────
    $data = getJsonBody();
    $cancelReason = isset($data['reason']) ? substr(trim($data['reason']), 0, 500) : null;
    $token = $data['token'] ?? '';

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        jsonError('Invalid cancellation link.', 400);
    }

    // Find appointment by token
    $stmt = $db->prepare(
        'SELECT id, reference_number, service, preferred_date, preferred_time,
                first_name, last_name, email, phone, language, google_event_id, status
         FROM oretir_appointments
         WHERE cancel_token = ?
           AND cancel_token_expires > NOW()
           AND status NOT IN (?, ?)'
    );
    $stmt->execute([$token, 'cancelled', 'completed']);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        jsonError('Invalid or expired cancellation link.', 404);
    }

    // Update status to cancelled and clear the token
    $db->prepare(
        'UPDATE oretir_appointments
         SET status = ?, cancel_reason = ?, cancel_token = NULL, cancel_token_expires = NULL, updated_at = NOW()
         WHERE id = ?'
    )->execute(['cancelled', $cancelReason, $appointment['id']]);

    // Delete Google Calendar event if exists
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
            ]);

            GoogleCalendarAction::deleteEvent($appointment['google_event_id']);

            // Audit trail
            $db->prepare("INSERT INTO oretir_email_logs (log_type, description, admin_email, created_at) VALUES (?, ?, ?, NOW())")
               ->execute([
                   'calendar_sync',
                   "Calendar: event_deleted for {$appointment['reference_number']} (customer cancellation)",
                   $appointment['email'],
               ]);
        } catch (\Throwable $e) {
            error_log("appointment-cancel.php: Google Calendar delete error for #{$appointment['id']}: " . $e->getMessage());
        }
    }

    // Send cancellation confirmation email to customer
    try {
        $customerName = $appointment['first_name'] . ' ' . $appointment['last_name'];
        $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));

        $dateObj = new \DateTime($appointment['preferred_date']);
        $customerLang = ($appointment['language'] ?? 'english') === 'spanish' ? 'es' : 'en';
        $displayDate = $customerLang === 'es' ? $dateObj->format('d/m/Y') : $dateObj->format('m/d/Y');

        $timeParts = explode(':', $appointment['preferred_time']);
        $hour = (int) $timeParts[0];
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        $displayTime = $displayHour . ':00 ' . $suffix;

        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $subjectEn = "Appointment Cancelled — {$h($appointment['reference_number'])}";
        $subjectEs = "Cita Cancelada — {$h($appointment['reference_number'])}";
        $subject = $customerLang === 'en'
            ? "{$subjectEn} | {$subjectEs}"
            : "{$subjectEs} | {$subjectEn}";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f0fdf4;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;">
<tr><td align="center" style="padding:30px 15px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <tr><td style="background:linear-gradient(135deg,#15803d 0%,#166534 50%,#1a1a2e 100%);padding:32px 30px 24px;text-align:center;">
    <img src="{$baseUrl}/assets/logo.png" alt="Oregon Tires Auto Care" width="100" style="display:block;margin:0 auto 12px;">
    <p style="color:#86efac;font-size:13px;margin:0;letter-spacing:2px;text-transform:uppercase;">Appointment Cancelled / Cita Cancelada</p>
  </td></tr>
  <tr><td style="padding:32px 36px;">
    <h2 style="color:#dc2626;font-size:22px;margin:0 0 16px;">Your appointment has been cancelled</h2>
    <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 8px;"><strong>Reference:</strong> {$h($appointment['reference_number'])}</p>
    <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 8px;"><strong>Service:</strong> {$h($serviceDisplay)}</p>
    <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 8px;"><strong>Date:</strong> {$h($displayDate)}</p>
    <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;"><strong>Time:</strong> {$h($displayTime)}</p>
    <p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0 0 8px;">If you'd like to book a new appointment, please visit our website.</p>
    <p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0;">Si desea programar una nueva cita, visite nuestro sitio web.</p>
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;">
      <tr><td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;">
        <a href="{$baseUrl}" target="_blank" style="display:inline-block;padding:14px 36px;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;">Book New Appointment / Nueva Cita</a>
      </td></tr>
    </table>
  </td></tr>
  <tr><td style="background-color:#1a1a2e;padding:20px 30px;text-align:center;">
    <p style="color:#9ca3af;font-size:12px;margin:0;">Oregon Tires Auto Care | (503) 367-9714</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>
HTML;

        sendMail($appointment['email'], $subject, $htmlBody);
        logEmail('appointment_cancelled', "Cancellation confirmation sent to {$appointment['email']} for {$appointment['reference_number']}");
    } catch (\Throwable $e) {
        error_log("appointment-cancel.php: Cancellation email failed for #{$appointment['id']}: " . $e->getMessage());
    }

    // Send cancellation notification to shop owner
    try {
        $customerName = ($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? '');
        $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));

        $dateObj = new \DateTime($appointment['preferred_date']);
        $displayDate = $dateObj->format('m/d/Y');

        $timeParts = explode(':', $appointment['preferred_time']);
        $hour = (int) $timeParts[0];
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        $displayTime = $displayHour . ':00 ' . $suffix;

        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $reasonHtml = $cancelReason
            ? "<tr style=\"background:#fef2f2;\"><td style=\"padding:8px 12px;font-weight:bold;color:#555;\">Cancel Reason:</td><td style=\"padding:8px 12px;color:#dc2626;\">{$h($cancelReason)}</td></tr>"
            : '';

        $ownerSubject = "Appointment Cancelled — {$appointment['reference_number']}";
        $ownerBody = <<<HTML
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
    <div style="background:linear-gradient(135deg,#dc2626,#991b1b);color:#fff;padding:20px;text-align:center;border-radius:8px 8px 0 0;">
        <h2 style="margin:0;">Appointment Cancelled</h2>
        <p style="margin:8px 0 0;opacity:0.9;font-size:14px;">{$h($appointment['reference_number'])} — {$h($serviceDisplay)}</p>
    </div>
    <div style="background:#fff;padding:24px;border:1px solid #e0e0e0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="background:#f0f9f0;"><td style="padding:8px 12px;font-weight:bold;color:#555;width:140px;">Reference:</td><td style="padding:8px 12px;font-weight:bold;color:#15803d;font-size:16px;">{$h($appointment['reference_number'])}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Service:</td><td style="padding:8px 12px;">{$h($serviceDisplay)}</td></tr>
            <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Date:</td><td style="padding:8px 12px;">{$h($displayDate)}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Time:</td><td style="padding:8px 12px;">{$h($displayTime)}</td></tr>
            <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Customer:</td><td style="padding:8px 12px;">{$h(trim($customerName))}</td></tr>
            <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Email:</td><td style="padding:8px 12px;"><a href="mailto:{$h($appointment['email'])}">{$h($appointment['email'])}</a></td></tr>
            <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Phone:</td><td style="padding:8px 12px;"><a href="tel:{$h($appointment['phone'] ?? '')}">{$h($appointment['phone'] ?? 'N/A')}</a></td></tr>
            {$reasonHtml}
        </table>
    </div>
    <div style="background:#1a1a2e;padding:12px;text-align:center;font-size:12px;color:#9ca3af;border-radius:0 0 8px 8px;">
        Oregon Tires Auto Care — Cancellation Notification
    </div>
</div>
HTML;

        notifyOwner($ownerSubject, $ownerBody, $appointment['email']);
        logEmail('cancellation_owner_notified', "Owner notified of cancellation for {$appointment['reference_number']} by {$appointment['email']}");
    } catch (\Throwable $e) {
        error_log("appointment-cancel.php: Owner notification failed for #{$appointment['id']}: " . $e->getMessage());
    }

    jsonSuccess(['message' => 'Appointment cancelled successfully.']);

} catch (\Throwable $e) {
    error_log("appointment-cancel.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
