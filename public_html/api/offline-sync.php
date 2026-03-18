<?php
/**
 * Oregon Tires — Offline Sync Endpoint
 * POST /api/offline-sync.php
 * Receives queued offline form submissions, deduplicates via sync_id,
 * and delegates to the booking flow.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';
require_once __DIR__ . '/../includes/vin-decode.php';

try {
    requireMethod('POST');
    checkRateLimit('offline_sync', 10, 3600);

    $data = getJsonBody();

    $syncId = trim((string) ($data['sync_id'] ?? ''));
    $actionType = trim((string) ($data['action_type'] ?? 'booking'));
    $payload = $data['payload'] ?? null;

    if (empty($syncId) || strlen($syncId) > 64) {
        jsonError('Missing or invalid sync_id');
    }

    if ($payload === null || !is_array($payload)) {
        jsonError('Missing payload');
    }

    $db = getDB();

    // Check for duplicate sync_id
    $dupeStmt = $db->prepare("SELECT id, status FROM oretir_offline_sync_log WHERE sync_id = ? LIMIT 1");
    $dupeStmt->execute([$syncId]);
    $existing = $dupeStmt->fetch(\PDO::FETCH_ASSOC);

    if ($existing) {
        if ($existing['status'] === 'processed') {
            jsonSuccess([
                'duplicate' => true,
                'sync_id' => $syncId,
                'message' => 'Already processed',
            ]);
        }
        // Mark as duplicate if it was received but not yet processed
        $db->prepare("UPDATE oretir_offline_sync_log SET status = 'duplicate' WHERE id = ?")->execute([$existing['id']]);
        jsonSuccess([
            'duplicate' => true,
            'sync_id' => $syncId,
            'message' => 'Duplicate submission',
        ]);
    }

    // Log the sync attempt
    $sourceInfo = sanitize((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 255);
    $logStmt = $db->prepare(
        "INSERT INTO oretir_offline_sync_log (sync_id, action_type, payload_json, status, source_info, created_at)
         VALUES (?, ?, ?, 'received', ?, NOW())"
    );
    $logStmt->execute([$syncId, $actionType, json_encode($payload), $sourceInfo]);
    $logId = (int) $db->lastInsertId();

    // Process based on action type
    if ($actionType === 'booking') {
        // Forward to booking endpoint internally
        // Simulate the same validation+creation that book.php does
        $result = processOfflineBooking($payload, $db);

        $db->prepare(
            "UPDATE oretir_offline_sync_log SET status = 'processed', result_json = ?, processed_at = NOW() WHERE id = ?"
        )->execute([json_encode($result), $logId]);

        jsonSuccess([
            'sync_id' => $syncId,
            'processed' => true,
            'result' => $result,
        ]);
    } else {
        $db->prepare("UPDATE oretir_offline_sync_log SET status = 'failed', result_json = '{\"error\":\"Unknown action\"}', processed_at = NOW() WHERE id = ?")
           ->execute([$logId]);
        jsonError('Unknown action_type: ' . $actionType);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires offline-sync.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}

/**
 * Process an offline booking submission.
 * Mirrors the core logic from api/book.php without payment/calendar integrations.
 */
function processOfflineBooking(array $data, PDO $db): array
{
    // Sanitize required fields
    $service       = sanitize((string) ($data['service'] ?? ''), 50);
    $preferredDate = sanitize((string) ($data['preferred_date'] ?? ''), 10);
    $preferredTime = sanitize((string) ($data['preferred_time'] ?? ''), 20);
    $firstName     = sanitize((string) ($data['first_name'] ?? ''), 100);
    $lastName      = sanitize((string) ($data['last_name'] ?? ''), 100);
    $phone         = sanitize((string) ($data['phone'] ?? ''), 30);
    $email         = sanitize((string) ($data['email'] ?? ''), 254);
    $language      = in_array($data['language'] ?? '', ['english', 'spanish'], true) ? $data['language'] : 'english';

    // Validate
    if (empty($service) || empty($preferredDate) || empty($preferredTime) ||
        empty($firstName) || empty($lastName) || empty($phone) || empty($email)) {
        throw new \RuntimeException('Missing required booking fields');
    }

    if (!isValidEmail($email)) {
        throw new \RuntimeException('Invalid email');
    }

    // Duplicate check
    $dupeStmt = $db->prepare(
        'SELECT id FROM oretir_appointments
         WHERE email = ? AND preferred_date = ? AND preferred_time = ? AND status != ?
         LIMIT 1'
    );
    $dupeStmt->execute([$email, $preferredDate, $preferredTime, 'cancelled']);
    if ($dupeStmt->fetch()) {
        return ['duplicate_booking' => true, 'message' => 'Appointment already exists at this time'];
    }

    // Generate reference number
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $referenceNumber = '';
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $code = '';
        $bytes = random_bytes(8);
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[ord($bytes[$i]) % strlen($chars)];
        }
        $candidate = 'OT-' . $code;
        $checkStmt = $db->prepare('SELECT COUNT(*) FROM oretir_appointments WHERE reference_number = ?');
        $checkStmt->execute([$candidate]);
        if ((int) $checkStmt->fetchColumn() === 0) {
            $referenceNumber = $candidate;
            break;
        }
    }

    if ($referenceNumber === '') {
        throw new \RuntimeException('Failed to generate reference number');
    }

    // Optional fields
    $vehicleYear  = sanitize((string) ($data['vehicle_year'] ?? ''), 4);
    $vehicleMake  = sanitize((string) ($data['vehicle_make'] ?? ''), 50);
    $vehicleModel = sanitize((string) ($data['vehicle_model'] ?? ''), 50);
    $vehicleVin   = sanitize((string) ($data['vehicle_vin'] ?? ''), 17);
    $tireSize     = sanitize((string) ($data['tire_size'] ?? ''), 30);
    $notes        = sanitize((string) ($data['notes'] ?? ''), 2000);
    $smsOptIn     = !empty($data['sms_opt_in']) ? 1 : 0;

    // Insert
    $stmt = $db->prepare(
        'INSERT INTO oretir_appointments
            (reference_number, service, preferred_date, preferred_time, vehicle_year, vehicle_make, vehicle_model, vehicle_vin, tire_size,
             first_name, last_name, phone, email, notes, sms_opt_in, status, language, created_at, updated_at)
         VALUES
            (:ref, :service, :date, :time, :vy, :vm, :vmod, :vvin, :ts,
             :fn, :ln, :phone, :email, :notes, :sms, :status, :lang, NOW(), NOW())'
    );
    $stmt->execute([
        ':ref'     => $referenceNumber,
        ':service' => $service,
        ':date'    => $preferredDate,
        ':time'    => $preferredTime,
        ':vy'      => $vehicleYear ?: null,
        ':vm'      => $vehicleMake ?: null,
        ':vmod'    => $vehicleModel ?: null,
        ':vvin'    => $vehicleVin ?: null,
        ':ts'      => $tireSize ?: null,
        ':fn'      => $firstName,
        ':ln'      => $lastName,
        ':phone'   => $phone,
        ':email'   => $email,
        ':notes'   => $notes ?: null,
        ':sms'     => $smsOptIn,
        ':status'  => 'new',
        ':lang'    => $language,
    ]);

    $appointmentId = (int) $db->lastInsertId();

    // Auto-create customer/vehicle (non-critical)
    try {
        $customerId = findOrCreateCustomer($email, $firstName, $lastName, $phone, $language, $db);
        if ($customerId) {
            $vehicleId = findOrCreateVehicle($customerId, $vehicleYear ?: null, $vehicleMake ?: null, $vehicleModel ?: null, $vehicleVin ?: null, $db);
            $db->prepare('UPDATE oretir_appointments SET customer_id = ?, vehicle_id = ? WHERE id = ?')
               ->execute([$customerId, $vehicleId, $appointmentId]);
        }
    } catch (\Throwable $e) {
        error_log("offline-sync booking: customer/vehicle creation failed for #{$appointmentId}: " . $e->getMessage());
    }

    // Generate cancel token
    $cancelToken = bin2hex(random_bytes(32));
    $db->prepare('UPDATE oretir_appointments SET cancel_token = ?, cancel_token_expires = ? WHERE id = ?')
       ->execute([$cancelToken, date('Y-m-d H:i:s', strtotime('+30 days')), $appointmentId]);

    // Send confirmation email (non-critical)
    try {
        $vehicleParts = array_filter([$vehicleYear, $vehicleMake, $vehicleModel]);
        $vehicleInfo = implode(' ', $vehicleParts);

        sendBookingOwnerNotification(
            $appointmentId, $referenceNumber, $service, $preferredDate, $preferredTime,
            $firstName, $lastName, $email, $phone, $vehicleInfo, $language, $notes
        );

        $customerLang = $language === 'spanish' ? 'es' : 'en';
        $dateObj = new \DateTime($preferredDate);
        $displayDate = $customerLang === 'es' ? $dateObj->format('d/m/Y') : $dateObj->format('m/d/Y');
        $displayTime = formatTimeDisplay($preferredTime);
        $serviceDisplay = ucwords(str_replace('-', ' ', $service));

        sendBookingConfirmationEmail(
            $email, "{$firstName} {$lastName}", $serviceDisplay,
            $displayDate, $displayTime, $vehicleInfo, $customerLang,
            $referenceNumber, $service, $preferredDate, $preferredTime, $cancelToken, 0
        );
    } catch (\Throwable $e) {
        error_log("offline-sync booking: email failed for #{$appointmentId}: " . $e->getMessage());
    }

    // Queue push notification for booking confirmation
    try {
        require_once __DIR__ . '/../includes/push.php';
        if ($customerId ?? null) {
            queueNotificationForCustomer(
                $customerId,
                'booking_confirmed',
                'Booking Confirmed!',
                'Cita Confirmada!',
                "Your {$serviceDisplay} appointment on {$displayDate} at {$displayTime} is confirmed. Ref: {$referenceNumber}",
                "Su cita de {$serviceDisplay} el {$displayDate} a las {$displayTime} est\u00e1 confirmada. Ref: {$referenceNumber}",
                '/book-appointment/'
            );
        }
    } catch (\Throwable $e) {
        error_log("offline-sync booking: push notification queue failed for #{$appointmentId}: " . $e->getMessage());
    }

    return [
        'appointment_id' => $appointmentId,
        'reference_number' => $referenceNumber,
    ];
}
