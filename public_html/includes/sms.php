<?php
/**
 * Oregon Tires — SMS / WhatsApp Service (Twilio)
 *
 * Graceful fallback: SMS failure never blocks email or workflow.
 * Requires env vars: TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM
 */

declare(strict_types=1);

/**
 * Check if SMS is configured (Twilio env vars present).
 */
function isSmsConfigured(): bool
{
    return !empty($_ENV['TWILIO_SID'])
        && !empty($_ENV['TWILIO_TOKEN'])
        && !empty($_ENV['TWILIO_FROM']);
}

/**
 * Send an SMS via Twilio REST API.
 *
 * @param string $to   Phone number (E.164 format preferred)
 * @param string $body Message body (max 1600 chars)
 * @return array{success: bool, sid?: string, error?: string}
 */
function sendSms(string $to, string $body): array
{
    if (!isSmsConfigured()) {
        return ['success' => false, 'error' => 'SMS not configured (missing Twilio credentials)'];
    }

    $to = normalizePhoneForSms($to);
    if (empty($to)) {
        return ['success' => false, 'error' => 'Invalid phone number for SMS'];
    }

    $sid   = $_ENV['TWILIO_SID'];
    $token = $_ENV['TWILIO_TOKEN'];
    $from  = $_ENV['TWILIO_FROM'];

    try {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $postData = http_build_query([
            'From' => $from,
            'To'   => $to,
            'Body' => mb_substr($body, 0, 1600, 'UTF-8'),
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: Basic ' . base64_encode("{$sid}:{$token}"),
                ],
                'content' => $postData,
                'timeout' => 15,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);

        if ($response === false) {
            error_log("Oregon Tires SMS: Failed to reach Twilio API for {$to}");
            return ['success' => false, 'error' => 'Failed to reach SMS service'];
        }

        $json = json_decode($response, true);

        if (!empty($json['sid'])) {
            error_log("Oregon Tires SMS: Sent to {$to} (SID: {$json['sid']})");
            return ['success' => true, 'sid' => $json['sid']];
        }

        $errorMsg = $json['message'] ?? 'Unknown Twilio error';
        error_log("Oregon Tires SMS error for {$to}: {$errorMsg}");
        return ['success' => false, 'error' => $errorMsg];

    } catch (\Throwable $e) {
        error_log("Oregon Tires SMS exception for {$to}: " . $e->getMessage());
        return ['success' => false, 'error' => 'SMS service error'];
    }
}

/**
 * Normalize a phone number to E.164 format for Twilio.
 * Assumes US numbers if no country code provided.
 */
function normalizePhoneForSms(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);

    if (empty($digits) || strlen($digits) < 10) {
        return '';
    }

    if (strlen($digits) === 11 && $digits[0] === '1') {
        return '+' . $digits;
    }

    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }

    if (strlen($digits) > 10) {
        return '+' . $digits;
    }

    return '';
}

/**
 * Send inspection report SMS to customer.
 */
function sendInspectionSms(string $phone, string $name, string $viewUrl, string $language = 'english'): array
{
    if ($language === 'spanish') {
        $body = "Hola {$name}, su reporte de inspección vehicular está listo. Vea los resultados aquí: {$viewUrl} — Oregon Tires Auto Care";
    } else {
        $body = "Hi {$name}, your vehicle inspection report is ready. View results here: {$viewUrl} — Oregon Tires Auto Care";
    }

    return sendSms($phone, $body);
}

/**
 * Send estimate approval SMS to customer.
 */
function sendEstimateSms(string $phone, string $name, string $total, string $approveUrl, string $language = 'english'): array
{
    if ($language === 'spanish') {
        $body = "Hola {$name}, su presupuesto de {$total} está listo para revisión. Apruebe aquí: {$approveUrl} — Oregon Tires";
    } else {
        $body = "Hi {$name}, your estimate of {$total} is ready for review. Approve here: {$approveUrl} — Oregon Tires";
    }

    return sendSms($phone, $body);
}

/**
 * Send vehicle-ready SMS to customer.
 */
function sendReadySms(string $phone, string $name, string $language = 'english'): array
{
    $mapsUrl = 'https://maps.google.com/?q=Oregon+Tires+Auto+Care+Portland';

    if ($language === 'spanish') {
        $body = "¡Hola {$name}! Su vehículo está listo para recoger en Oregon Tires Auto Care. Direcciones: {$mapsUrl}";
    } else {
        $body = "Hi {$name}! Your vehicle is ready for pickup at Oregon Tires Auto Care. Directions: {$mapsUrl}";
    }

    return sendSms($phone, $body);
}

/**
 * Send "Job Finished" SMS when RO status moves to 'ready'.
 */
function sendJobFinishedSms(string $phone, string $customerName, string $roNumber, string $language = 'english'): array
{
    $mapsUrl = 'https://maps.google.com/?q=Oregon+Tires+Auto+Care+Portland';

    if ($language === 'spanish') {
        $body = "¡Hola {$customerName}! Su vehículo (Orden: {$roNumber}) está listo para recoger en Oregon Tires Auto Care. Direcciones: {$mapsUrl}";
    } else {
        $body = "Hi {$customerName}! Your vehicle (RO: {$roNumber}) is ready for pickup at Oregon Tires Auto Care. Directions: {$mapsUrl}";
    }

    return sendSms($phone, $body);
}

/**
 * Send RO status update SMS to customer.
 * Called from handleStatusTransition() for key status changes.
 * Silently skips if customer has no phone, no sms_opt_in, or SMS not configured.
 *
 * @param PDO    $db     Database connection
 * @param array  $ro     Full RO row (includes customer_id, vehicle_id)
 * @param string $event  Event type: 'check_in', 'estimate_sent', 'in_progress'
 */
function sendRoStatusSms(PDO $db, array $ro, string $event): void
{
    if (!isSmsConfigured()) return;

    try {
        // Get customer with sms_opt_in check
        $custId = $ro['customer_id'] ?? null;
        if (!$custId) return;

        // Check appointment for sms_opt_in
        $smsOptIn = false;
        if (!empty($ro['appointment_id'])) {
            $apptStmt = $db->prepare('SELECT sms_opt_in FROM oretir_appointments WHERE id = ?');
            $apptStmt->execute([$ro['appointment_id']]);
            $smsOptIn = (bool) ($apptStmt->fetchColumn() ?: 0);
        }
        if (!$smsOptIn) return;

        $custStmt = $db->prepare('SELECT first_name, last_name, phone, language FROM oretir_customers WHERE id = ?');
        $custStmt->execute([$custId]);
        $cust = $custStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$cust || empty($cust['phone'])) return;

        $name = trim(($cust['first_name'] ?? '') . ' ' . ($cust['last_name'] ?? ''));
        $lang = ($cust['language'] ?? 'english');
        $isEs = $lang === 'spanish';

        // Build vehicle string
        $vehicle = '';
        if (!empty($ro['vehicle_id'])) {
            $vStmt = $db->prepare('SELECT year, make, model FROM oretir_vehicles WHERE id = ?');
            $vStmt->execute([$ro['vehicle_id']]);
            $v = $vStmt->fetch(\PDO::FETCH_ASSOC);
            if ($v) $vehicle = trim(implode(' ', array_filter([$v['year'], $v['make'], $v['model']])));
        }
        $vehicleLabel = $vehicle ?: ($isEs ? 'su vehículo' : 'your vehicle');

        $messages = [
            'check_in' => [
                'en' => "Oregon Tires: We've received your {$vehicleLabel}. We'll keep you updated on the progress. — Oregon Tires Auto Care",
                'es' => "Oregon Tires: Hemos recibido su {$vehicleLabel}. Le mantendremos informado del progreso. — Oregon Tires Auto Care",
            ],
            'estimate_sent' => [
                'en' => "Oregon Tires: Your estimate for {$vehicleLabel} is ready for review. Please check your email. — Oregon Tires Auto Care",
                'es' => "Oregon Tires: Su presupuesto para {$vehicleLabel} está listo. Por favor revise su correo. — Oregon Tires Auto Care",
            ],
            'in_progress' => [
                'en' => "Oregon Tires: Work has begun on your {$vehicleLabel}. We'll notify you when it's ready. — Oregon Tires Auto Care",
                'es' => "Oregon Tires: El trabajo ha comenzado en su {$vehicleLabel}. Le avisaremos cuando esté listo. — Oregon Tires Auto Care",
            ],
        ];

        if (!isset($messages[$event])) return;
        $body = $isEs ? $messages[$event]['es'] : $messages[$event]['en'];

        sendSms($cust['phone'], $body);
    } catch (\Throwable $e) {
        error_log("sendRoStatusSms error (RO #{$ro['id']}, event={$event}): " . $e->getMessage());
    }
}

/**
 * Send approval confirmation SMS to customer.
 */
function sendApprovalConfirmationSms(string $phone, string $name, string $language = 'english'): array
{
    if ($language === 'spanish') {
        $body = "Gracias {$name}, su presupuesto ha sido aprobado. Nuestro equipo comenzará el trabajo pronto. — Oregon Tires Auto Care";
    } else {
        $body = "Thank you {$name}, your estimate has been approved. Our team will begin work shortly. — Oregon Tires Auto Care";
    }

    return sendSms($phone, $body);
}
