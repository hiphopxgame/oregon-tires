<?php
/**
 * Oregon Tires — Booking / Appointment Endpoint
 * POST /api/book.php
 *
 * Accepts appointment booking requests, validates input,
 * stores in database, and emails the shop owner.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';
require_once __DIR__ . '/../includes/vin-decode.php';

try {
    requireMethod('POST');

    // Rate limit: 5 per hour per IP
    checkRateLimit('booking', 5, 3600);

    // Parse & validate body
    $data = getJsonBody();

    $missing = requireFields($data, [
        'service', 'preferred_date', 'preferred_time',
        'first_name', 'last_name', 'phone', 'email',
    ]);
    if (!empty($missing)) {
        jsonError('Missing required fields: ' . implode(', ', $missing));
    }

    // Sanitize required fields
    $service       = sanitize((string) $data['service'], 50);
    $preferredDate = sanitize((string) $data['preferred_date'], 10);
    $preferredTime = sanitize((string) $data['preferred_time'], 20);
    $firstName     = sanitize((string) $data['first_name'], 100);
    $lastName      = sanitize((string) $data['last_name'], 100);
    $phone         = sanitize((string) $data['phone'], 30);
    $email         = sanitize((string) $data['email'], 254);

    // Sanitize optional fields
    $vehicleYear  = sanitize((string) ($data['vehicle_year'] ?? ''), 4);
    $vehicleMake  = sanitize((string) ($data['vehicle_make'] ?? ''), 50);
    $vehicleModel = sanitize((string) ($data['vehicle_model'] ?? ''), 50);
    $vehicleVin   = sanitize((string) ($data['vehicle_vin'] ?? ''), 17);
    $notes        = sanitize((string) ($data['notes'] ?? ''), 2000);
    $language     = sanitize((string) ($data['language'] ?? 'english'), 20);
    $smsOptIn     = !empty($data['sms_opt_in']) ? 1 : 0;

    // UTM tracking fields
    $utmSource   = sanitize((string) ($data['utm_source'] ?? ''), 100);
    $utmMedium   = sanitize((string) ($data['utm_medium'] ?? ''), 100);
    $utmCampaign = sanitize((string) ($data['utm_campaign'] ?? ''), 100);
    $utmContent  = sanitize((string) ($data['utm_content'] ?? ''), 100);

    // ─── Validate ───────────────────────────────────────────────────────────

    // Service type
    if (!isValidService($service)) {
        jsonError('Invalid service type.');
    }

    // Date (not past, not Sunday)
    if (!isValidAppointmentDate($preferredDate)) {
        jsonError('Invalid appointment date. Must be a future date and not a Sunday.');
    }

    // Time slot
    if (!isValidTimeSlot($preferredTime)) {
        jsonError('Invalid time slot. Please select a valid appointment time.');
    }

    // Name lengths
    if (mb_strlen($firstName) < 1 || mb_strlen($firstName) > 100) {
        jsonError('First name must be between 1 and 100 characters.');
    }
    if (mb_strlen($lastName) < 1 || mb_strlen($lastName) > 100) {
        jsonError('Last name must be between 1 and 100 characters.');
    }

    // Email
    if (!isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    // Phone
    if (!isValidPhone($phone)) {
        jsonError('Please provide a valid phone number.');
    }

    // Vehicle year (optional but if present must be 4 digits)
    if ($vehicleYear !== '' && !preg_match('/^\d{4}$/', $vehicleYear)) {
        jsonError('Vehicle year must be a 4-digit number.');
    }

    // Language
    if (!in_array($language, ['english', 'spanish'], true)) {
        $language = 'english';
    }

    // ─── Check for time slot conflicts ──────────────────────────────────────
    $db = getDB();

    // Dynamic capacity from employee schedules
    $maxPerSlot = 2; // fallback if schedule tables don't exist
    try {
        $dayOfWeek = (int) (new DateTime($preferredDate))->format('w');
        $slotHour = (int) substr($preferredTime, 0, 2);

        // Check shop-wide closure
        $closureStmt = $db->prepare(
            "SELECT id FROM oretir_schedule_overrides
             WHERE override_date = ? AND employee_id IS NULL AND is_closed = 1 LIMIT 1"
        );
        $closureStmt->execute([$preferredDate]);
        if ($closureStmt->fetch()) {
            jsonError('The shop is closed on this date. Please choose a different day.', 409);
        }

        // Count employees available at this time slot
        $schedStmt = $db->prepare(
            "SELECT s.employee_id, s.start_time, s.end_time
             FROM oretir_schedules s
             JOIN oretir_employees e ON s.employee_id = e.id
             WHERE s.day_of_week = ? AND s.is_available = 1 AND e.is_active = 1"
        );
        $schedStmt->execute([$dayOfWeek]);
        $schedRows = $schedStmt->fetchAll();

        if (count($schedRows) > 0) {
            // Load employee overrides for this date
            $ovStmt = $db->prepare(
                "SELECT employee_id, is_closed, start_time, end_time
                 FROM oretir_schedule_overrides
                 WHERE override_date = ? AND employee_id IS NOT NULL"
            );
            $ovStmt->execute([$preferredDate]);
            $empOverrides = [];
            foreach ($ovStmt->fetchAll() as $ov) {
                $empOverrides[(int) $ov['employee_id']] = $ov;
            }

            $slotCapacity = 0;
            foreach ($schedRows as $sched) {
                $empId = (int) $sched['employee_id'];
                $empStart = (int) substr($sched['start_time'], 0, 2);
                $empEnd   = (int) substr($sched['end_time'], 0, 2);

                if (isset($empOverrides[$empId])) {
                    if ((int) $empOverrides[$empId]['is_closed']) continue;
                    $empStart = (int) substr($empOverrides[$empId]['start_time'], 0, 2);
                    $empEnd   = (int) substr($empOverrides[$empId]['end_time'], 0, 2);
                }

                if ($slotHour >= $empStart && $slotHour < $empEnd) {
                    $slotCapacity++;
                }
            }
            $maxPerSlot = $slotCapacity;
        }
    } catch (\Throwable $schedErr) {
        // Tables don't exist yet — use fallback capacity of 2
        error_log("book.php schedule capacity check skipped: " . $schedErr->getMessage());
    }

    $conflictStmt = $db->prepare(
        'SELECT COUNT(*) FROM oretir_appointments
         WHERE preferred_date = ? AND preferred_time = ? AND status NOT IN (?, ?)'
    );
    $conflictStmt->execute([$preferredDate, $preferredTime, 'cancelled', 'completed']);
    $slotCount = (int) $conflictStmt->fetchColumn();

    if ($slotCount >= $maxPerSlot) {
        jsonError('This time slot is fully booked. Please choose a different time.', 409);
    }

    // ─── Duplicate booking prevention ────────────────────────────────────────
    $dupeStmt = $db->prepare(
        'SELECT id FROM oretir_appointments
         WHERE email = ? AND preferred_date = ? AND preferred_time = ? AND status != ?
         LIMIT 1'
    );
    $dupeStmt->execute([$email, $preferredDate, $preferredTime, 'cancelled']);

    if ($dupeStmt->fetch()) {
        jsonError('You already have an appointment at this time. Please choose a different slot. / Ya tiene una cita a esta hora. Por favor elija otro horario.', 409);
    }

    // ─── Generate unique reference number ────────────────────────────────
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Omit 0/O, 1/I to avoid confusion
    $maxAttempts = 10;
    $referenceNumber = '';

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = '';
        $bytes = random_bytes(8);
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[ord($bytes[$i]) % strlen($chars)];
        }
        $candidate = 'OT-' . $code;

        // Check uniqueness
        $checkStmt = $db->prepare('SELECT COUNT(*) FROM oretir_appointments WHERE reference_number = ?');
        $checkStmt->execute([$candidate]);
        if ((int) $checkStmt->fetchColumn() === 0) {
            $referenceNumber = $candidate;
            break;
        }
    }

    if ($referenceNumber === '') {
        error_log('Oregon Tires book.php: Failed to generate unique reference number after ' . $maxAttempts . ' attempts');
        jsonError('Server error', 500);
    }

    // ─── Insert into database ───────────────────────────────────────────────
    $stmt = $db->prepare(
        'INSERT INTO oretir_appointments
            (reference_number, service, preferred_date, preferred_time, vehicle_year, vehicle_make, vehicle_model, vehicle_vin,
             first_name, last_name, phone, email, notes, sms_opt_in, utm_source, utm_medium, utm_campaign, utm_content, status, language, created_at, updated_at)
         VALUES
            (:reference_number, :service, :preferred_date, :preferred_time, :vehicle_year, :vehicle_make, :vehicle_model, :vehicle_vin,
             :first_name, :last_name, :phone, :email, :notes, :sms_opt_in, :utm_source, :utm_medium, :utm_campaign, :utm_content, :status, :language, NOW(), NOW())'
    );
    $stmt->execute([
        ':reference_number' => $referenceNumber,
        ':service'          => $service,
        ':preferred_date'   => $preferredDate,
        ':preferred_time'   => $preferredTime,
        ':vehicle_year'     => $vehicleYear ?: null,
        ':vehicle_make'     => $vehicleMake ?: null,
        ':vehicle_model'    => $vehicleModel ?: null,
        ':vehicle_vin'      => $vehicleVin ?: null,
        ':first_name'       => $firstName,
        ':last_name'        => $lastName,
        ':phone'            => $phone,
        ':email'            => $email,
        ':notes'            => $notes ?: null,
        ':sms_opt_in'       => $smsOptIn,
        ':utm_source'       => $utmSource ?: null,
        ':utm_medium'       => $utmMedium ?: null,
        ':utm_campaign'     => $utmCampaign ?: null,
        ':utm_content'      => $utmContent ?: null,
        ':status'           => 'new',
        ':language'         => $language,
    ]);

    $appointmentId = (int) $db->lastInsertId();

    // ─── Link booking to customer account if logged in ──────────────────────
    startSecureSession();
    if (!empty($_SESSION['member_id'])) {
        $db->prepare('UPDATE oretir_appointments SET member_id = ? WHERE id = ?')
           ->execute([(int) $_SESSION['member_id'], $appointmentId]);

        // Cross-site activity reporting (fire-and-forget)
        $memberStmt = $db->prepare('SELECT hw_user_id FROM members WHERE id = ? LIMIT 1');
        $memberStmt->execute([(int) $_SESSION['member_id']]);
        $memberRow = $memberStmt->fetch(\PDO::FETCH_ASSOC);

        if (!empty($memberRow['hw_user_id']) && class_exists('MemberSync')) {
            MemberSync::reportActivity(
                (int) $memberRow['hw_user_id'],
                'oregon.tires',
                'appointment_booked',
                ['reference' => $referenceNumber, 'service' => $service]
            );
        }
    }

    // ─── Generate cancel/reschedule token ─────────────────────────────────
    $cancelToken = bin2hex(random_bytes(32));
    $cancelExpires = date('Y-m-d H:i:s', strtotime('+30 days'));
    $db->prepare('UPDATE oretir_appointments SET cancel_token = ?, cancel_token_expires = ? WHERE id = ?')
       ->execute([$cancelToken, $cancelExpires, $appointmentId]);

    // ─── Auto-create Customer & Vehicle records (graceful — failure doesn't break booking) ──
    $bookingCustomerId = null;
    $bookingVehicleId  = null;
    try {
        $bookingCustomerId = findOrCreateCustomer($email, $firstName, $lastName, $phone, $language, $db);
        if ($bookingCustomerId) {
            $bookingVehicleId = findOrCreateVehicle(
                $bookingCustomerId,
                $vehicleYear ?: null,
                $vehicleMake ?: null,
                $vehicleModel ?: null,
                $vehicleVin ?: null,
                $db
            );
            $db->prepare('UPDATE oretir_appointments SET customer_id = ?, vehicle_id = ? WHERE id = ?')
               ->execute([$bookingCustomerId, $bookingVehicleId, $appointmentId]);
        }
    } catch (\Throwable $custErr) {
        error_log("Oregon Tires book.php: customer/vehicle auto-create failed for appointment #{$appointmentId}: " . $custErr->getMessage());
    }

    // ─── Smart Account Creation for guests ──────────────────────────────────
    $newMemberId = null;
    if (empty($_SESSION['member_id'])) {
        try {
            require_once __DIR__ . '/../includes/smart-account.php';
            $newMemberId = findOrCreateMemberAccount($email, $firstName, $lastName, $db, $language);
            if ($newMemberId) {
                $db->prepare('UPDATE oretir_appointments SET member_id = ? WHERE id = ?')
                   ->execute([$newMemberId, $appointmentId]);
            }
        } catch (\Throwable $smartErr) {
            error_log("Oregon Tires book.php: smart account creation failed for appointment #{$appointmentId}: " . $smartErr->getMessage());
        }
    }

    // ─── Optional Payment Integration ─────────────────────────────────────
    $paymentResponse = null;
    $paymentMethod = sanitize((string) ($data['payment_method'] ?? ''), 20);

    if ($paymentMethod !== '' && in_array($paymentMethod, ['stripe', 'paypal', 'crypto'], true)) {
        $servicePrice = (float) ($data['service_price'] ?? 0);
        if ($servicePrice <= 0) {
            // Payment method specified but no valid price — skip payment, booking still succeeds
            error_log("Oregon Tires book.php: payment_method={$paymentMethod} but no valid service_price for appointment #{$appointmentId}");
        } else {
            try {
                $commerceKitPath = $_ENV['COMMERCE_KIT_PATH'] ?? __DIR__ . '/../../../---commerce-kit';
                require_once $commerceKitPath . '/loader.php';

                $siteKey = 'oregon.tires';
                $providers = CommerceBootstrap::init($db, $siteKey, [
                    'stripe' => true,
                    'stripe_config' => [
                        'secret_key'     => $_ENV['STRIPE_SECRET_KEY'] ?? '',
                        'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
                    ],
                    'paypal' => true,
                    'paypal_config' => [
                        'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
                        'secret'    => $_ENV['PAYPAL_SECRET'] ?? '',
                        'mode'      => $_ENV['PAYPAL_MODE'] ?? 'sandbox',
                    ],
                    'crypto' => true,
                    'crypto_config' => [
                        'wallet_addresses' => [
                            'ETH'  => $_ENV['CRYPTO_ETH_ADDRESS'] ?? '',
                            'BTC'  => $_ENV['CRYPTO_BTC_ADDRESS'] ?? '',
                            'SOL'  => $_ENV['CRYPTO_SOL_ADDRESS'] ?? '',
                            'USDT' => $_ENV['CRYPTO_USDT_ADDRESS'] ?? '',
                            'USDC' => $_ENV['CRYPTO_USDC_ADDRESS'] ?? '',
                        ],
                        'expiry_minutes' => 30,
                    ],
                ]);

                if (!isset($providers[$paymentMethod])) {
                    error_log("Oregon Tires book.php: provider '{$paymentMethod}' not available for appointment #{$appointmentId}");
                } else {
                    $serviceDisplay = ucwords(str_replace('-', ' ', $service));
                    $appUrl = $_ENV['APP_URL'] ?? 'https://oregon.tires';

                    $paymentData = [
                        'items' => [[
                            'description' => $serviceDisplay,
                            'quantity'    => 1,
                            'unit_price'  => $servicePrice,
                            'metadata'    => [
                                'appointment_ref' => $referenceNumber,
                                'appointment_id'  => $appointmentId,
                            ],
                        ]],
                        'customer_name'  => "{$firstName} {$lastName}",
                        'customer_email' => $email,
                        'customer_phone' => $phone,
                        'metadata' => [
                            'appointment_ref' => $referenceNumber,
                            'appointment_id'  => $appointmentId,
                        ],
                    ];

                    // Provider-specific URLs and data
                    if ($paymentMethod === 'stripe') {
                        $paymentData['success_url'] = $appUrl . '/booking-success?ref=' . $referenceNumber . '&order_ref={CHECKOUT_SESSION_ID}';
                        $paymentData['cancel_url']  = $appUrl . '/booking-cancelled?ref=' . $referenceNumber;
                    } elseif ($paymentMethod === 'paypal') {
                        $paymentData['return_url'] = $appUrl . '/booking-success?ref=' . $referenceNumber;
                        $paymentData['cancel_url'] = $appUrl . '/booking-cancelled?ref=' . $referenceNumber;
                    } elseif ($paymentMethod === 'crypto') {
                        $paymentData['crypto_currency'] = strtoupper(sanitize((string) ($data['crypto_currency'] ?? 'ETH'), 10));
                        $paymentData['crypto_amount']   = (float) ($data['crypto_amount'] ?? 0);
                    }

                    /** @var CommerceProvider $provider */
                    $provider = $providers[$paymentMethod];
                    $paymentResult = $provider->initiate($siteKey, $paymentData);

                    if ($paymentResult['success'] ?? false) {
                        $paymentResponse = ['payment_initiated' => true];

                        // Include checkout URL for stripe/paypal
                        if ($paymentMethod === 'stripe' && !empty($paymentResult['checkout_url'])) {
                            $paymentResponse['checkout_url'] = $paymentResult['checkout_url'];
                        } elseif ($paymentMethod === 'paypal' && !empty($paymentResult['approval_url'])) {
                            $paymentResponse['checkout_url'] = $paymentResult['approval_url'];
                        } elseif ($paymentMethod === 'crypto') {
                            $paymentResponse['wallet_address']  = $paymentResult['wallet_address'] ?? '';
                            $paymentResponse['crypto_currency'] = $paymentResult['crypto_currency'] ?? '';
                            $paymentResponse['crypto_amount']   = $paymentResult['crypto_amount'] ?? null;
                            $paymentResponse['expires_at']      = $paymentResult['expires_at'] ?? null;
                        }

                        $paymentResponse['order_ref'] = $paymentResult['order_ref'] ?? '';
                        $paymentResponse['total']     = $paymentResult['total'] ?? $servicePrice;
                    } else {
                        // Payment initiation failed — booking still succeeds, log the error
                        $paymentResponse = [
                            'payment_initiated' => false,
                            'payment_error'     => $paymentResult['error'] ?? 'Payment initiation failed',
                        ];
                        error_log("Oregon Tires book.php: payment initiation failed for appointment #{$appointmentId}: " . ($paymentResult['error'] ?? 'unknown'));
                    }
                }
            } catch (\Throwable $e) {
                // Payment failure should never break the booking
                $paymentResponse = [
                    'payment_initiated' => false,
                    'payment_error'     => 'Payment service unavailable',
                ];
                error_log("Oregon Tires book.php: payment exception for appointment #{$appointmentId}: " . $e->getMessage());
            }
        }
    }

    // ─── Google Calendar Integration ────────────────────────────────────────
    $googleEventId = null;
    if (!empty($_ENV['GOOGLE_CALENDAR_CREDENTIALS'])) {
        try {
            $formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../---form-kit';
            require_once $formKitPath . '/loader.php';
            require_once $formKitPath . '/actions/google-calendar.php';

            FormManager::init($db, ['site_key' => 'oregon.tires']);
            GoogleCalendarAction::register([
                'credentials_path' => $_ENV['GOOGLE_CALENDAR_CREDENTIALS'],
                'calendar_id'      => $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary',
                'send_invites'     => true,
                'timezone'         => 'America/Los_Angeles',
                'default_duration' => 60,
                'service_colors'   => [
                    'tire-installation'     => '9',  // blue
                    'tire-repair'           => '9',
                    'oil-change'            => '6',  // orange
                    'brake-service'         => '11', // red
                    'wheel-alignment'       => '3',  // purple
                    'tuneup'                => '2',  // green
                    'mechanical-inspection' => '7',  // cyan
                    'mobile-service'        => '5',  // yellow
                ],
            ]);

            $appointmentData = [
                'id'               => $appointmentId,
                'reference_number' => $referenceNumber,
                'service'          => $service,
                'preferred_date'   => $preferredDate,
                'preferred_time'   => $preferredTime,
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'email'            => $email,
                'phone'            => $phone,
                'vehicle_year'     => $vehicleYear,
                'vehicle_make'     => $vehicleMake,
                'vehicle_model'    => $vehicleModel,
                'notes'            => $notes,
            ];

            $calEvent = GoogleCalendarAction::buildEventFromAppointment($appointmentData);
            $calResult = GoogleCalendarAction::createEvent($calEvent);
            $googleEventId = $calResult['id'] ?? null;

            if ($googleEventId) {
                $db->prepare('UPDATE oretir_appointments SET google_event_id = ?, calendar_sync_status = ?, calendar_synced_at = NOW() WHERE id = ?')
                   ->execute([$googleEventId, 'success', $appointmentId]);
            }
        } catch (\Throwable $e) {
            // Calendar failure should never break the booking — track the error
            $db->prepare('UPDATE oretir_appointments SET calendar_sync_status = ?, calendar_sync_error = ? WHERE id = ?')
               ->execute(['failed', substr($e->getMessage(), 0, 500), $appointmentId]);
            error_log("Oregon Tires book.php: Google Calendar error for appointment #{$appointmentId}: " . $e->getMessage());
        }
    }

    // ─── Email notification to shop owner (branded template) ────────────────
    $vehicleInfo = '';
    if ($vehicleYear || $vehicleMake || $vehicleModel) {
        $vehicleParts = array_filter([$vehicleYear, $vehicleMake, $vehicleModel]);
        $vehicleInfo = implode(' ', $vehicleParts);
    }

    sendBookingOwnerNotification(
        $appointmentId,
        $referenceNumber,
        $service,
        $preferredDate,
        $preferredTime,
        $firstName,
        $lastName,
        $email,
        $phone,
        $vehicleInfo,
        $language,
        $notes
    );

    // ─── Send confirmation email to customer ──────────────────────────────
    $customerLang = $language === 'spanish' ? 'es' : 'en';
    $customerName = "{$firstName} {$lastName}";
    $serviceDisplay = ucwords(str_replace('-', ' ', $service));

    // Format date and time for customer display
    $dateObj = new \DateTime($preferredDate);
    $displayDate = $customerLang === 'es'
        ? $dateObj->format('d/m/Y')
        : $dateObj->format('m/d/Y');

    $displayTime = formatTimeDisplay($preferredTime);

    try {
        sendBookingConfirmationEmail(
            $email,
            $customerName,
            $serviceDisplay,
            $displayDate,
            $displayTime,
            $vehicleInfo,
            $customerLang,
            $referenceNumber,
            $service,          // raw service slug for calendar
            $preferredDate,    // raw YYYY-MM-DD for calendar
            $preferredTime,    // raw HH:MM for calendar
            $cancelToken       // cancel/reschedule token
        );
    } catch (\Throwable $e) {
        // Don't fail the booking if confirmation email fails
        error_log("Booking confirmation email failed for #{$appointmentId}: " . $e->getMessage());
    }

    $response = [
        'appointment_id'   => $appointmentId,
        'reference_number' => $referenceNumber,
        'account_created'  => empty($_SESSION['member_id']) && isset($newMemberId) && $newMemberId > 0,
    ];

    if ($paymentResponse !== null) {
        $response['payment'] = $paymentResponse;
    }

    if (isHtmxRequest()) {
        header('Content-Type: text/html; charset=utf-8');
        header('Vary: HX-Request');
        $lang = ($language === 'spanish') ? 'es' : 'en';
        $bookingData = [
            'service' => $service,
            'preferredDate' => $preferredDate,
            'preferredTime' => $preferredTime,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phone' => $phone,
            'email' => $email,
            'vehicleYear' => $vehicleYear,
            'vehicleMake' => $vehicleMake,
            'vehicleModel' => $vehicleModel,
        ];
        $bookingResponse = $response;
        require __DIR__ . '/../templates/partials/booking-confirmation.php';
        exit;
    }

    jsonSuccess($response);

} catch (\Throwable $e) {
    error_log("Oregon Tires book.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
