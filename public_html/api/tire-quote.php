<?php
/**
 * Oregon Tires — Tire Quote Request Endpoint
 * POST /api/tire-quote.php — submit a tire quote request
 *
 * Rate limited: 10/hr per IP
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mail.php';

try {
    requireMethod('POST');

    // Rate limit: 10 submissions per hour per IP
    checkRateLimit('tire_quote', 10, 3600);

    $data = getJsonBody();

    // ─── Validate required fields ───────────────────────────────────────
    $missing = requireFields($data, ['first_name', 'email']);
    if (!empty($missing)) {
        jsonError('Missing required fields: ' . implode(', ', $missing));
    }

    // ─── Sanitize inputs ────────────────────────────────────────────────
    $firstName     = sanitize((string) $data['first_name'], 100);
    $lastName      = sanitize((string) ($data['last_name'] ?? ''), 100);
    $email         = sanitize((string) $data['email'], 254);
    $phone         = sanitize((string) ($data['phone'] ?? ''), 30);
    $vehicleYear   = sanitize((string) ($data['vehicle_year'] ?? ''), 4);
    $vehicleMake   = sanitize((string) ($data['vehicle_make'] ?? ''), 50);
    $vehicleModel  = sanitize((string) ($data['vehicle_model'] ?? ''), 50);
    $tireSize      = sanitize((string) ($data['tire_size'] ?? ''), 50);
    $tireCount     = max(1, min(20, (int) ($data['tire_count'] ?? 4)));
    $tirePreference = sanitize((string) ($data['tire_preference'] ?? 'either'), 10);
    $budgetRange   = sanitize((string) ($data['budget_range'] ?? 'no_preference'), 20);
    $includeInstallation = (bool) ($data['include_installation'] ?? true);
    $preferredDate = sanitize((string) ($data['preferred_date'] ?? ''), 10);
    $notes         = sanitize((string) ($data['notes'] ?? ''), 2000);
    $language      = sanitize((string) ($data['language'] ?? 'english'), 20);

    // ─── Validate ───────────────────────────────────────────────────────
    if (mb_strlen($firstName) < 1) {
        jsonError('First name is required.');
    }

    if (!isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    if ($phone !== '' && !isValidPhone($phone)) {
        jsonError('Please provide a valid phone number.');
    }

    if (!in_array($tirePreference, ['new', 'used', 'either'], true)) {
        $tirePreference = 'either';
    }

    if (!in_array($budgetRange, ['economy', 'mid', 'premium', 'no_preference'], true)) {
        $budgetRange = 'no_preference';
    }

    if (!in_array($language, ['english', 'spanish'], true)) {
        $language = 'english';
    }

    // Validate preferred_date if provided
    $preferredDateVal = null;
    if ($preferredDate !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $preferredDate);
        if ($d && $d->format('Y-m-d') === $preferredDate) {
            $preferredDateVal = $preferredDate;
        }
    }

    // ─── Find existing customer by email ────────────────────────────────
    $db = getDB();
    $customerId = null;

    $custStmt = $db->prepare('SELECT id FROM oretir_customers WHERE email = ? LIMIT 1');
    $custStmt->execute([$email]);
    $existingCust = $custStmt->fetchColumn();

    if ($existingCust) {
        $customerId = (int) $existingCust;
    } else {
        // Auto-create customer record
        try {
            require_once __DIR__ . '/../includes/vin-decode.php';
            $customerId = findOrCreateCustomer($email, $firstName, $lastName, $phone, $language, $db);
        } catch (\Throwable $custErr) {
            error_log("tire-quote.php: customer auto-create failed: " . $custErr->getMessage());
        }
    }

    // ─── Insert tire quote request ──────────────────────────────────────
    $stmt = $db->prepare(
        'INSERT INTO oretir_tire_quotes
            (customer_id, first_name, last_name, email, phone, vehicle_year, vehicle_make, vehicle_model,
             tire_size, tire_count, tire_preference, budget_range, include_installation, preferred_date,
             notes, status, language)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $customerId,
        $firstName,
        $lastName,
        $email,
        $phone ?: null,
        $vehicleYear ?: null,
        $vehicleMake ?: null,
        $vehicleModel ?: null,
        $tireSize ?: null,
        $tireCount,
        $tirePreference,
        $budgetRange,
        $includeInstallation ? 1 : 0,
        $preferredDateVal,
        $notes ?: null,
        'new',
        $language,
    ]);

    $quoteId = (int) $db->lastInsertId();

    // ─── Send confirmation email to customer ────────────────────────────
    $vehicleDisplay = trim("{$vehicleYear} {$vehicleMake} {$vehicleModel}") ?: 'your vehicle';
    $tireSizeDisplay = $tireSize ?: 'TBD';

    try {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

        sendBrandedTemplateEmail(
            $email,
            'tire_quote',
            [
                'name'       => htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'),
                'tire_count' => (string) $tireCount,
                'tire_size'  => htmlspecialchars($tireSizeDisplay, ENT_QUOTES, 'UTF-8'),
                'vehicle'    => htmlspecialchars($vehicleDisplay, ENT_QUOTES, 'UTF-8'),
            ],
            $language === 'spanish' ? 'es' : 'both',
            $baseUrl
        );

        logEmail('tire_quote_confirm', "Tire quote confirmation sent to {$email} (quote #{$quoteId})");
    } catch (\Throwable $mailErr) {
        error_log("tire-quote.php: confirmation email failed: " . $mailErr->getMessage());
    }

    // ─── Notify shop owner ──────────────────────────────────────────────
    try {
        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $adminUrl = $baseUrl . '/admin/';

        $prefDisplay = ucfirst($tirePreference);
        $budgetDisplay = str_replace('_', ' ', ucfirst($budgetRange));
        $installDisplay = $includeInstallation ? 'Yes' : 'No';

        $htmlBody = <<<HTML
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
            <div style="background:linear-gradient(135deg,#15803d,#166534);color:#fff;padding:20px;text-align:center;border-radius:8px 8px 0 0;">
                <h2 style="margin:0;">New Tire Quote Request</h2>
                <p style="margin:8px 0 0;opacity:0.9;font-size:14px;">Quote #{$quoteId}</p>
            </div>
            <div style="background:#fff;padding:24px;border:1px solid #e0e0e0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr style="background:#f0f9f0;">
                        <td style="padding:8px 12px;font-weight:bold;color:#555;width:160px;">Customer:</td>
                        <td style="padding:8px 12px;">{$h($firstName)} {$h($lastName)}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Email:</td>
                        <td style="padding:8px 12px;"><a href="mailto:{$h($email)}">{$h($email)}</a></td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Phone:</td>
                        <td style="padding:8px 12px;">{$h($phone ?: 'Not provided')}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Vehicle:</td>
                        <td style="padding:8px 12px;">{$h($vehicleDisplay)}</td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Tire Size:</td>
                        <td style="padding:8px 12px;">{$h($tireSizeDisplay)}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Quantity:</td>
                        <td style="padding:8px 12px;">{$tireCount}</td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Preference:</td>
                        <td style="padding:8px 12px;">{$h($prefDisplay)}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Budget:</td>
                        <td style="padding:8px 12px;">{$h($budgetDisplay)}</td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td style="padding:8px 12px;font-weight:bold;color:#555;">Include Install:</td>
                        <td style="padding:8px 12px;">{$installDisplay}</td>
                    </tr>
                </table>
HTML;
        if ($notes) {
            $htmlBody .= <<<HTML
                <div style="margin-top:16px;padding:16px;background:#f0fdf4;border-left:4px solid #15803d;border-radius:4px;">
                    <strong>Customer Notes:</strong>
                    <p style="margin:8px 0 0;color:#333;line-height:1.6;white-space:pre-wrap;">{$h($notes)}</p>
                </div>
HTML;
        }
        $htmlBody .= <<<HTML
                <div style="margin-top:20px;text-align:center;">
                    <a href="{$h($adminUrl)}" style="display:inline-block;padding:12px 28px;background:#15803d;color:#fff;text-decoration:none;font-weight:bold;border-radius:8px;font-size:14px;">
                        View in Admin Panel
                    </a>
                </div>
            </div>
            <div style="background:#15803d;padding:12px;text-align:center;font-size:12px;color:#fff;border-radius:0 0 8px 8px;">
                Oregon Tires Auto Care — Tire Quote #{$quoteId}
            </div>
        </div>
HTML;

        notifyOwner(
            "New Tire Quote Request from {$firstName} {$lastName}",
            $htmlBody,
            $email
        );

        logEmail('tire_quote_notify', "Tire quote owner notification for #{$quoteId} from {$email}");
    } catch (\Throwable $notifyErr) {
        error_log("tire-quote.php: owner notification failed: " . $notifyErr->getMessage());
    }

    jsonSuccess([
        'quote_id' => $quoteId,
    ]);

} catch (\Throwable $e) {
    error_log('tire-quote.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
