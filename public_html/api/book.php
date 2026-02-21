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
    $notes        = sanitize((string) ($data['notes'] ?? ''), 2000);
    $language     = sanitize((string) ($data['language'] ?? 'english'), 20);

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
    $maxPerSlot = 2; // max bookings per time slot
    $conflictStmt = $db->prepare(
        'SELECT COUNT(*) FROM oretir_appointments
         WHERE preferred_date = ? AND preferred_time = ? AND status NOT IN (?, ?)'
    );
    $conflictStmt->execute([$preferredDate, $preferredTime, 'cancelled', 'completed']);
    $slotCount = (int) $conflictStmt->fetchColumn();

    if ($slotCount >= $maxPerSlot) {
        jsonError('This time slot is fully booked. Please choose a different time.', 409);
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
            (reference_number, service, preferred_date, preferred_time, vehicle_year, vehicle_make, vehicle_model,
             first_name, last_name, phone, email, notes, status, language, created_at, updated_at)
         VALUES
            (:reference_number, :service, :preferred_date, :preferred_time, :vehicle_year, :vehicle_make, :vehicle_model,
             :first_name, :last_name, :phone, :email, :notes, :status, :language, NOW(), NOW())'
    );
    $stmt->execute([
        ':reference_number' => $referenceNumber,
        ':service'          => $service,
        ':preferred_date'   => $preferredDate,
        ':preferred_time'   => $preferredTime,
        ':vehicle_year'     => $vehicleYear ?: null,
        ':vehicle_make'     => $vehicleMake ?: null,
        ':vehicle_model'    => $vehicleModel ?: null,
        ':first_name'       => $firstName,
        ':last_name'        => $lastName,
        ':phone'            => $phone,
        ':email'            => $email,
        ':notes'            => $notes ?: null,
        ':status'           => 'new',
        ':language'         => $language,
    ]);

    $appointmentId = (int) $db->lastInsertId();

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
                $db->prepare('UPDATE oretir_appointments SET google_event_id = ? WHERE id = ?')
                   ->execute([$googleEventId, $appointmentId]);
            }
        } catch (\Throwable $e) {
            // Calendar failure should never break the booking
            error_log("Oregon Tires book.php: Google Calendar error for appointment #{$appointmentId}: " . $e->getMessage());
        }
    }

    // ─── Format service name for display ────────────────────────────────────
    $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $serviceDisplay = ucwords(str_replace('-', ' ', $service));

    // ─── Email notification to shop owner ───────────────────────────────────
    $subject = "New Appointment: {$h($serviceDisplay)} — {$h($firstName)} {$h($lastName)}";

    // Build vehicle info line
    $vehicleInfo = '';
    if ($vehicleYear || $vehicleMake || $vehicleModel) {
        $vehicleParts = array_filter([$vehicleYear, $vehicleMake, $vehicleModel]);
        $vehicleInfo = implode(' ', $vehicleParts);
    }

    $htmlBody = <<<HTML
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: #1a1a2e; color: #ffffff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
            <h2 style="margin: 0;">New Appointment Booking</h2>
            <p style="margin: 8px 0 0; opacity: 0.9; font-size: 14px;">#{$appointmentId} &mdash; {$h($referenceNumber)} &mdash; {$h($serviceDisplay)}</p>
        </div>
        <div style="background: #ffffff; padding: 24px; border: 1px solid #e0e0e0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f0f9f0;">
                    <td style="padding: 8px 12px; font-weight: bold; color: #555; width: 140px;">Reference:</td>
                    <td style="padding: 8px 12px; font-weight: bold; color: #15803d; font-size: 16px;">{$h($referenceNumber)}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; font-weight: bold; color: #555; width: 140px;">Service:</td>
                    <td style="padding: 8px 12px;">{$h($serviceDisplay)}</td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Date:</td>
                    <td style="padding: 8px 12px;">{$h($preferredDate)}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Time:</td>
                    <td style="padding: 8px 12px;">{$h($preferredTime)}</td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Customer:</td>
                    <td style="padding: 8px 12px;">{$h($firstName)} {$h($lastName)}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Email:</td>
                    <td style="padding: 8px 12px;"><a href="mailto:{$h($email)}">{$h($email)}</a></td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Phone:</td>
                    <td style="padding: 8px 12px;"><a href="tel:{$h($phone)}">{$h($phone)}</a></td>
                </tr>
    HTML;

    if ($vehicleInfo) {
        $htmlBody .= <<<HTML
                <tr>
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Vehicle:</td>
                    <td style="padding: 8px 12px;">{$h($vehicleInfo)}</td>
                </tr>
        HTML;
    }

    $htmlBody .= <<<HTML
                <tr style="background: #f9f9f9;">
                    <td style="padding: 8px 12px; font-weight: bold; color: #555;">Language:</td>
                    <td style="padding: 8px 12px;">{$h($language)}</td>
                </tr>
            </table>
    HTML;

    if ($notes) {
        $htmlBody .= <<<HTML
            <div style="margin-top: 16px; padding: 16px; background: #f5f5f5; border-left: 4px solid #1a1a2e; border-radius: 4px;">
                <strong style="color: #555;">Notes:</strong>
                <p style="margin: 8px 0 0; color: #333; line-height: 1.6;">{$h($notes)}</p>
            </div>
        HTML;
    }

    $htmlBody .= <<<HTML
        </div>
        <div style="background: #f0f0f0; padding: 12px; text-align: center; font-size: 12px; color: #888; border-radius: 0 0 8px 8px;">
            Oregon Tires Auto Care &mdash; {$h($referenceNumber)} &mdash; Appointment #{$appointmentId}
        </div>
    </div>
    HTML;

    $mailResult = notifyOwner($subject, $htmlBody);

    // Log the email attempt
    $logDesc = $mailResult['success']
        ? "Booking notification sent for appointment #{$appointmentId}"
        : "Booking notification FAILED for appointment #{$appointmentId}: " . ($mailResult['error'] ?? 'unknown');
    logEmail('booking', $logDesc);

    // ─── Send confirmation email to customer ──────────────────────────────
    $customerLang = $language === 'spanish' ? 'es' : 'en';
    $customerName = "{$firstName} {$lastName}";
    $serviceDisplay = ucwords(str_replace('-', ' ', $service));

    // Format date and time for customer display
    $dateObj = new \DateTime($preferredDate);
    $displayDate = $customerLang === 'es'
        ? $dateObj->format('d/m/Y')
        : $dateObj->format('m/d/Y');

    $timeParts = explode(':', $preferredTime);
    $hour = (int) $timeParts[0];
    $suffix = $hour >= 12 ? 'PM' : 'AM';
    $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
    $displayTime = $displayHour . ':00 ' . $suffix;

    try {
        sendBookingConfirmationEmail(
            $email,
            $customerName,
            $serviceDisplay,
            $displayDate,
            $displayTime,
            $vehicleInfo,
            $customerLang,
            $referenceNumber
        );
    } catch (\Throwable $e) {
        // Don't fail the booking if confirmation email fails
        error_log("Booking confirmation email failed for #{$appointmentId}: " . $e->getMessage());
    }

    $response = [
        'appointment_id'   => $appointmentId,
        'reference_number' => $referenceNumber,
    ];

    if ($paymentResponse !== null) {
        $response['payment'] = $paymentResponse;
    }

    jsonSuccess($response);

} catch (\Throwable $e) {
    error_log("Oregon Tires book.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
