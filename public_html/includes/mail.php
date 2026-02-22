<?php
/**
 * Oregon Tires — PHPMailer Helper + DB-driven Email Templates
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send an email using PHPMailer with SMTP settings from .env
 */
function sendMail(string $to, string $subject, string $htmlBody, string $textBody = '', string $replyTo = ''): array
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? '';
        $mail->Port       = (int) ($_ENV['SMTP_PORT'] ?? 465);
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->CharSet    = 'UTF-8';

        $port = (int) ($_ENV['SMTP_PORT'] ?? 465);
        if ($port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($port === 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->SMTPDebug = (int) ($_ENV['SMTP_DEBUG'] ?? 0);
        $mail->Debugoutput = function (string $str, int $level) {
            error_log("PHPMailer [{$level}]: {$str}");
        };

        $mail->setFrom(
            $_ENV['SMTP_FROM'] ?? $_ENV['SMTP_USER'] ?? '',
            $_ENV['SMTP_FROM_NAME'] ?? 'Oregon Tires Auto Care'
        );

        $mail->addAddress($to);

        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);

        $mail->send();

        return ['success' => true, 'error' => null];
    } catch (\Throwable $e) {
        error_log("Oregon Tires mail error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send notification to the shop owner.
 */
function notifyOwner(string $subject, string $htmlBody, string $replyTo = ''): array
{
    $contactEmail = $_ENV['CONTACT_EMAIL'] ?? $_ENV['SMTP_FROM'] ?? '';
    if (empty($contactEmail)) {
        return ['success' => false, 'error' => 'No contact email configured.'];
    }

    return sendMail($contactEmail, $subject, $htmlBody, '', $replyTo);
}

/**
 * Log an email event to the database.
 */
function logEmail(string $type, string $description, ?string $adminEmail = null): void
{
    try {
        $db = getDB();
        $db->prepare('INSERT INTO oretir_email_logs (log_type, description, admin_email) VALUES (?, ?, ?)')
           ->execute([$type, $description, $adminEmail]);
    } catch (\Throwable $e) {
        error_log("Oregon Tires email log error: " . $e->getMessage());
    }
}

// ─── DB-Driven Email Templates ──────────────────────────────────────────────

/**
 * Load email template fields from oretir_site_settings by prefix.
 *
 * @param string $prefix  Template prefix: 'welcome', 'reset', 'contact'
 * @return array ['subject_en'=>..., 'subject_es'=>..., 'greeting_en'=>..., etc.]
 */
function loadEmailTemplate(string $prefix): array
{
    $db = getDB();
    $keyPrefix = "email_tpl_{$prefix}_";
    $stmt = $db->prepare(
        "SELECT setting_key, value_en, value_es FROM oretir_site_settings WHERE setting_key LIKE ?"
    );
    $stmt->execute([$keyPrefix . '%']);
    $rows = $stmt->fetchAll();

    $tpl = [];
    foreach ($rows as $row) {
        $field = str_replace($keyPrefix, '', $row['setting_key']);
        $tpl[$field . '_en'] = $row['value_en'];
        $tpl[$field . '_es'] = $row['value_es'];
    }

    return $tpl;
}

/**
 * Replace {{variable}} placeholders in a string.
 */
function replaceTemplateVars(string $text, array $vars): string
{
    foreach ($vars as $key => $value) {
        $text = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $text);
    }
    return $text;
}

/**
 * Replace {{variable}} placeholders — no HTML escaping (for pre-escaped template content).
 */
function replaceTemplateVarsRaw(string $text, array $vars): string
{
    foreach ($vars as $key => $value) {
        $text = str_replace('{{' . $key . '}}', (string) $value, $text);
    }
    return $text;
}

/**
 * Build a single language section (HTML) for an email.
 */
function buildLanguageSection(
    string $flag,
    string $label,
    string $greeting,
    string $body,
    string $buttonText,
    string $buttonUrl,
    string $footer,
    string $headingTag = 'h1',
    string $borderColors = ''
): string {
    $borderBar = $borderColors ?: 'linear-gradient(90deg,#d1d5db,#d1d5db)';

    return <<<HTML
  <tr>
    <td style="padding:0;">
      <div style="height:3px;background:{$borderBar};"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:32px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">{$flag} {$label}</p>
            <{$headingTag} style="color:#15803d;font-size:24px;margin:0 0 8px;font-weight:800;">{$greeting}</{$headingTag}>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              {$body}
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;box-shadow:0 4px 14px rgba(21,128,61,0.35);">
                  <a href="{$buttonUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    🔐 {$buttonText}
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 36px 28px;">
            <p style="color:#6b7280;font-size:13px;line-height:1.6;margin:0;">
              {$footer}
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
HTML;
}

/**
 * Wrap language sections in the branded email shell (header + footer).
 */
function wrapBrandedEmail(string $bodySections, string $baseUrl, string $buttonUrl, bool $showPasswordReqs = false): string
{
    $reqSection = '';
    if ($showPasswordReqs) {
        $reqSection = <<<HTML
  <!-- PASSWORD REQUIREMENTS -->
  <tr>
    <td style="padding:0 36px 28px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#374151;font-size:13px;font-weight:700;margin:0 0 10px;">📋 Requisitos / Requirements:</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#6b7280;">
              <tr><td style="padding:3px 0;">✓ Mínimo 8 caracteres / Min 8 characters</td></tr>
              <tr><td style="padding:3px 0;">✓ Una letra mayúscula / One uppercase letter</td></tr>
              <tr><td style="padding:3px 0;">✓ Una letra minúscula / One lowercase letter</td></tr>
              <tr><td style="padding:3px 0;">✓ Un número / One number</td></tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- FALLBACK URL -->
  <tr>
    <td style="padding:0 36px 28px;">
      <p style="color:#9ca3af;font-size:12px;line-height:1.5;margin:0;">
        Si los botones no funcionan, copia y pega este enlace en tu navegador:<br>
        If the buttons don't work, copy and paste this link in your browser:<br>
        <a href="{$buttonUrl}" style="color:#15803d;word-break:break-all;font-size:11px;">{$buttonUrl}</a>
      </p>
    </td>
  </tr>
HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oregon Tires Auto Care</title>
</head>
<body style="margin:0;padding:0;background-color:#f0fdf4;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;">
<tr><td align="center" style="padding:30px 15px;">

<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

  <!-- HEADER -->
  <tr>
    <td style="background:linear-gradient(135deg,#15803d 0%,#166534 50%,#1a1a2e 100%);padding:0;">
      <div style="height:4px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:32px 30px 24px;">
            <img src="{$baseUrl}/assets/logo.png" alt="Oregon Tires Auto Care" width="140" style="display:block;max-width:140px;height:auto;margin-bottom:16px;">
            <p style="color:#86efac;font-size:13px;margin:0;letter-spacing:2px;text-transform:uppercase;font-weight:600;">Panel de Administración</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

{$bodySections}

{$reqSection}

  <!-- FOOTER -->
  <tr>
    <td style="background-color:#1a1a2e;padding:0;">
      <div style="height:3px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:24px 30px;">
            <p style="color:#d4a843;font-size:14px;font-weight:700;margin:0 0 6px;">Oregon Tires Auto Care</p>
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">8536 SE 82nd Ave, Portland, OR 97266</p>
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">📞 (503) 367-9714</p>
            <p style="color:#9ca3af;font-size:12px;margin:0;">Lunes–Sábado 7:00 AM – 7:00 PM</p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 30px 20px;">
            <p style="color:#6b7280;font-size:10px;margin:0;">
              Este correo fue enviado desde una dirección que no acepta respuestas.<br>
              This email was sent from a no-reply address.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>
</td></tr>
</table>

</body>
</html>
HTML;
}

/**
 * Build bilingual plain-text email from template fields.
 */
function buildBilingualPlainText(array $tpl, array $vars, string $language, string $buttonUrl): string
{
    $esGreeting = replaceTemplateVarsRaw(strip_tags($tpl['greeting_es'] ?? ''), $vars);
    $esBody     = replaceTemplateVarsRaw(strip_tags($tpl['body_es'] ?? ''), $vars);
    $esButton   = replaceTemplateVarsRaw(strip_tags($tpl['button_es'] ?? ''), $vars);
    $esFooter   = replaceTemplateVarsRaw(strip_tags($tpl['footer_es'] ?? ''), $vars);

    $enGreeting = replaceTemplateVarsRaw(strip_tags($tpl['greeting_en'] ?? ''), $vars);
    $enBody     = replaceTemplateVarsRaw(strip_tags($tpl['body_en'] ?? ''), $vars);
    $enButton   = replaceTemplateVarsRaw(strip_tags($tpl['button_en'] ?? ''), $vars);
    $enFooter   = replaceTemplateVarsRaw(strip_tags($tpl['footer_en'] ?? ''), $vars);

    $spanish = "🇲🇽 ESPAÑOL\n\n{$esGreeting}\n\n{$esBody}\n\n🔐 {$buttonUrl}\n\n{$esFooter}";
    $english = "🇺🇸 ENGLISH\n\n{$enGreeting}\n\n{$enBody}\n\n🔐 {$buttonUrl}\n\n{$enFooter}";

    $divider = "\n\n═══════════════════════════════════════\n\n";

    $body = "═══════════════════════════════════════\n";
    $body .= "OREGON TIRES AUTO CARE — Panel de Administración\n";
    $body .= "═══════════════════════════════════════\n\n";

    // Primary language first
    if ($language === 'en') {
        $body .= $english . $divider . $spanish;
    } else {
        $body .= $spanish . $divider . $english;
    }

    $body .= "\n\n═══════════════════════════════════════\n\n";
    $body .= "Oregon Tires Auto Care\n";
    $body .= "8536 SE 82nd Ave, Portland, OR 97266\n";
    $body .= "📞 (503) 367-9714\n";
    $body .= "Lunes–Sábado 7:00 AM – 7:00 PM";

    return $body;
}

/**
 * Send a branded bilingual template email.
 *
 * @param string $to           Recipient email
 * @param string $templateKey  Template prefix: 'welcome', 'reset', 'contact'
 * @param array  $vars         Variables: name, setup_url, role, expiry_days, email, message, etc.
 * @param string $language     User language preference: 'en', 'es', or 'both' (default)
 * @param string $buttonUrl    The main action URL (setup link, reset link, admin panel link)
 * @param bool   $showPasswordReqs  Show password requirements box (for welcome/reset)
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendBrandedTemplateEmail(
    string $to,
    string $templateKey,
    array $vars,
    string $language = 'both',
    string $buttonUrl = '',
    bool $showPasswordReqs = false
): array {
    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
    $tpl = loadEmailTemplate($templateKey);

    if (empty($tpl)) {
        error_log("Oregon Tires: No email template found for key '{$templateKey}', using hardcoded fallback");
        return ['success' => false, 'error' => "Email template '{$templateKey}' not found in database."];
    }

    // Replace variables in all template fields
    $esGreeting = replaceTemplateVarsRaw($tpl['greeting_es'] ?? '', $vars);
    $esBody     = replaceTemplateVarsRaw($tpl['body_es'] ?? '', $vars);
    $esButton   = replaceTemplateVarsRaw($tpl['button_es'] ?? '', $vars);
    $esFooter   = replaceTemplateVarsRaw($tpl['footer_es'] ?? '', $vars);

    $enGreeting = replaceTemplateVarsRaw($tpl['greeting_en'] ?? '', $vars);
    $enBody     = replaceTemplateVarsRaw($tpl['body_en'] ?? '', $vars);
    $enButton   = replaceTemplateVarsRaw($tpl['button_en'] ?? '', $vars);
    $enFooter   = replaceTemplateVarsRaw($tpl['footer_en'] ?? '', $vars);

    // Build language sections — primary language first
    $mexicanBar = 'linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%)';
    $usBar = 'linear-gradient(90deg,#002868 0%,#002868 33%,#bf0a30 33%,#bf0a30 66%,#002868 66%,#002868 100%)';

    $esSection = buildLanguageSection('🇲🇽', 'Español', $esGreeting, $esBody, $esButton, $buttonUrl, $esFooter, 'h1', $mexicanBar);
    $enSection = buildLanguageSection('🇺🇸', 'English', $enGreeting, $enBody, $enButton, $buttonUrl, $enFooter, 'h2', $usBar);

    // Divider between sections
    $divider = <<<HTML
  <tr>
    <td style="padding:0 36px;">
      <div style="height:1px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);"></div>
    </td>
  </tr>
HTML;

    // Order: primary language first
    if ($language === 'en') {
        $bodySections = $enSection . $divider . $esSection;
    } else {
        // 'es' or 'both' — Spanish first
        $bodySections = $esSection . $divider . $enSection;
    }

    $htmlBody = wrapBrandedEmail($bodySections, $baseUrl, $buttonUrl, $showPasswordReqs);
    $textBody = buildBilingualPlainText($tpl, $vars, $language, $buttonUrl);

    // Build subject — primary language first
    $subjectEs = replaceTemplateVarsRaw($tpl['subject_es'] ?? '', $vars);
    $subjectEn = replaceTemplateVarsRaw($tpl['subject_en'] ?? '', $vars);

    if ($language === 'en') {
        $subject = "🔐 {$subjectEn} | {$subjectEs}";
    } else {
        $subject = "🔐 {$subjectEs} | {$subjectEn}";
    }

    return sendMail($to, $subject, $htmlBody, $textBody);
}

/**
 * Send a branded bilingual setup email to an admin.
 * Now uses DB-driven templates with language-ordered sections.
 *
 * @param string $email    Recipient email
 * @param string $name     Admin display name
 * @param string $setupUrl Full setup URL with token
 * @param string $language 'en', 'es', or 'both' (default)
 * @param string $role     Admin role label for template
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendBrandedSetupEmail(string $email, string $name, string $setupUrl, string $language = 'both', string $role = 'Admin'): array
{
    $vars = [
        'name'        => $name,
        'setup_url'   => $setupUrl,
        'role'        => $role,
        'expiry_days' => '7',
        'email'       => $email,
    ];

    $result = sendBrandedTemplateEmail($email, 'welcome', $vars, $language, $setupUrl, true);

    if ($result['success']) {
        logEmail('admin_setup', "Setup email sent to {$email} (lang: {$language})", $email);
    }

    return $result;
}

/**
 * Send a branded password reset email.
 */
function sendBrandedResetEmail(string $email, string $name, string $resetUrl, string $language = 'both'): array
{
    $vars = [
        'name'      => $name,
        'setup_url' => $resetUrl,
        'email'     => $email,
    ];

    $result = sendBrandedTemplateEmail($email, 'reset', $vars, $language, $resetUrl, true);

    if ($result['success']) {
        logEmail('password_reset', "Password reset email sent to {$email}", $email);
    }

    return $result;
}

/**
 * Build "Add to Calendar" links for an appointment.
 *
 * @return array ['google_url' => string, 'ics_url' => string]
 */
function buildCalendarLinks(
    string $service,
    string $preferredDate,
    string $preferredTime,
    string $referenceNumber
): array {
    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
    $serviceDisplay = ucwords(str_replace('-', ' ', $service));

    // Build Google Calendar URL
    $tz = new \DateTimeZone('America/Los_Angeles');
    $start = new \DateTime("{$preferredDate} {$preferredTime}", $tz);
    $end   = clone $start;
    $end->modify('+1 hour');

    // Google Calendar uses UTC format
    $startUtc = clone $start;
    $startUtc->setTimezone(new \DateTimeZone('UTC'));
    $endUtc = clone $end;
    $endUtc->setTimezone(new \DateTimeZone('UTC'));

    $gcalParams = http_build_query([
        'action'   => 'TEMPLATE',
        'text'     => "{$serviceDisplay} — Oregon Tires Auto Care",
        'dates'    => $startUtc->format('Ymd\THis\Z') . '/' . $endUtc->format('Ymd\THis\Z'),
        'details'  => "Appointment Ref: {$referenceNumber}\nService: {$serviceDisplay}\n\nOregon Tires Auto Care\n(503) 367-9714",
        'location' => '8536 SE 82nd Ave, Portland, OR 97266',
        'ctz'      => 'America/Los_Angeles',
    ]);

    return [
        'google_url' => 'https://calendar.google.com/calendar/render?' . $gcalParams,
        'ics_url'    => "{$baseUrl}/api/calendar-event.php?ref=" . urlencode($referenceNumber),
    ];
}

/**
 * Send a branded booking confirmation email to the customer.
 */
function sendBookingConfirmationEmail(
    string $email,
    string $name,
    string $service,
    string $date,
    string $time,
    string $vehicleInfo,
    string $language = 'both',
    string $referenceNumber = '',
    string $rawService = '',
    string $rawDate = '',
    string $rawTime = '',
    string $cancelToken = ''
): array {
    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

    // Build vehicle line (only if vehicle info provided)
    $vehicleLine = $vehicleInfo ? "<br><strong>Vehicle:</strong> {$vehicleInfo}" : '';
    $vehicleLineEs = $vehicleInfo ? "<br><strong>Vehículo:</strong> {$vehicleInfo}" : '';

    // Build reference line (only if reference number provided)
    $refLine = $referenceNumber ? "<br><strong>Reference:</strong> {$referenceNumber}" : '';
    $refLineEs = $referenceNumber ? "<br><strong>Referencia:</strong> {$referenceNumber}" : '';

    // Build calendar links if raw values are available
    $calendarHtml = '';
    if ($rawDate && $rawTime && $referenceNumber) {
        $calLinks = buildCalendarLinks(
            $rawService ?: strtolower(str_replace(' ', '-', $service)),
            $rawDate,
            $rawTime,
            $referenceNumber
        );
        $gcalUrl = htmlspecialchars($calLinks['google_url'], ENT_QUOTES, 'UTF-8');
        $icsUrl  = htmlspecialchars($calLinks['ics_url'], ENT_QUOTES, 'UTF-8');

        $calendarHtml = <<<HTML
  <tr>
    <td style="padding:0 36px 24px;">
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px;text-align:center;">
        <p style="color:#15803d;font-size:14px;font-weight:700;margin:0 0 12px;">📅 Add to Your Calendar / Agregar a su Calendario</p>
        <table role="presentation" cellpadding="0" cellspacing="0" align="center">
          <tr>
            <td style="padding:0 6px;">
              <a href="{$gcalUrl}" target="_blank" style="display:inline-block;padding:10px 20px;background:#4285F4;color:#fff;text-decoration:none;font-size:13px;font-weight:600;border-radius:8px;">📅 Google Calendar</a>
            </td>
            <td style="padding:0 6px;">
              <a href="{$icsUrl}" target="_blank" style="display:inline-block;padding:10px 20px;background:#374151;color:#fff;text-decoration:none;font-size:13px;font-weight:600;border-radius:8px;">📥 Apple / Outlook (.ics)</a>
            </td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
HTML;
    }

    // Build cancel/reschedule links (only if token provided)
    $cancelRescheduleHtml = '';
    if ($cancelToken !== '') {
        $cancelUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/') . '/cancel.php?token=' . urlencode($cancelToken);
        $rescheduleUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/') . '/reschedule.php?token=' . urlencode($cancelToken);
        $cancelUrlSafe = htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8');
        $rescheduleUrlSafe = htmlspecialchars($rescheduleUrl, ENT_QUOTES, 'UTF-8');

        $cancelRescheduleHtml = <<<HTML
  <tr>
    <td style="padding:0 36px 24px;">
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:20px;text-align:center;">
        <p style="color:#6b7280;font-size:13px;margin:0 0 12px;">
          Need to change your plans? / &iquest;Necesita cambiar sus planes?
        </p>
        <table role="presentation" cellpadding="0" cellspacing="0" align="center">
          <tr>
            <td style="padding:0 6px;">
              <a href="{$rescheduleUrlSafe}" target="_blank" style="display:inline-block;padding:10px 20px;background:#f59e0b;color:#fff;text-decoration:none;font-size:13px;font-weight:600;border-radius:8px;">Reschedule / Reprogramar</a>
            </td>
            <td style="padding:0 6px;">
              <a href="{$cancelUrlSafe}" target="_blank" style="display:inline-block;padding:10px 20px;background:#6b7280;color:#fff;text-decoration:none;font-size:13px;font-weight:600;border-radius:8px;">Cancel / Cancelar</a>
            </td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
HTML;
    }

    // Build status check link
    $statusHtml = '';
    if ($referenceNumber !== '') {
        $statusUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/') . '/status/?ref=' . urlencode($referenceNumber) . '&email=' . urlencode($email);
        $statusUrlSafe = htmlspecialchars($statusUrl, ENT_QUOTES, 'UTF-8');

        $statusHtml = <<<HTML
  <tr>
    <td style="padding:0 36px 24px;">
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px;text-align:center;">
        <p style="color:#1e40af;font-size:13px;font-weight:600;margin:0 0 8px;">
          Check your appointment status anytime / Consulte el estado de su cita
        </p>
        <a href="{$statusUrlSafe}" target="_blank" style="display:inline-block;padding:8px 24px;background:#3b82f6;color:#fff;text-decoration:none;font-size:13px;font-weight:600;border-radius:8px;">View Status / Ver Estado</a>
      </div>
    </td>
  </tr>
HTML;
    }

    $vars = [
        'name'             => $name,
        'service'          => $service,
        'date'             => $date,
        'time'             => $time,
        'vehicle_line'     => $vehicleLine,
        'reference_line'   => $refLine,
        'reference_number' => $referenceNumber,
        'email'            => $email,
    ];

    // Load and build using the branded template system
    $tpl = loadEmailTemplate('booking');

    if (empty($tpl)) {
        // Fallback: send a simple confirmation with calendar links
        $refText = $referenceNumber ? " (Ref: {$referenceNumber})" : '';
        $subject = "Appointment Requested{$refText} — Oregon Tires Auto Care";
        $htmlBody = "<p>Thank you, {$name}! Your appointment for {$service} on {$date} at {$time} has been received.{$refText} We will call you to confirm.</p>";
        if ($calendarHtml) {
            $htmlBody .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $calendarHtml . '</table>';
        }
        $result = sendMail($email, $subject, $htmlBody);
        if ($result['success']) {
            logEmail('booking_confirmation', "Booking confirmation sent to {$email} (fallback)");
        }
        return $result;
    }

    // For customer emails, replace vehicle_line and reference_line with localized versions in ES fields
    $varsEs = $vars;
    $varsEs['vehicle_line'] = $vehicleLineEs;
    $varsEs['reference_line'] = $refLineEs;

    // Build language sections manually for the vehicle line localization
    $esGreeting = replaceTemplateVarsRaw($tpl['greeting_es'] ?? '', $varsEs);
    $esBody     = replaceTemplateVarsRaw($tpl['body_es'] ?? '', $varsEs);
    $esButton   = replaceTemplateVarsRaw($tpl['button_es'] ?? '', $varsEs);
    $esFooter   = replaceTemplateVarsRaw($tpl['footer_es'] ?? '', $varsEs);

    $enGreeting = replaceTemplateVarsRaw($tpl['greeting_en'] ?? '', $vars);
    $enBody     = replaceTemplateVarsRaw($tpl['body_en'] ?? '', $vars);
    $enButton   = replaceTemplateVarsRaw($tpl['button_en'] ?? '', $vars);
    $enFooter   = replaceTemplateVarsRaw($tpl['footer_en'] ?? '', $vars);

    $mexicanBar = 'linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%)';
    $usBar = 'linear-gradient(90deg,#002868 0%,#002868 33%,#bf0a30 33%,#bf0a30 66%,#002868 66%,#002868 100%)';

    $esSection = buildLanguageSection('🇲🇽', 'Español', $esGreeting, $esBody, $esButton, $baseUrl, $esFooter, 'h1', $mexicanBar);
    $enSection = buildLanguageSection('🇺🇸', 'English', $enGreeting, $enBody, $enButton, $baseUrl, $enFooter, 'h2', $usBar);

    $divider = '<tr><td style="padding:0 36px;"><div style="height:1px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);"></div></td></tr>';

    if ($language === 'en') {
        $bodySections = $enSection . $divider . $esSection;
    } else {
        $bodySections = $esSection . $divider . $enSection;
    }

    // Insert calendar links after the language sections
    $bodySections .= $calendarHtml;

    // Insert cancel/reschedule links after calendar links
    $bodySections .= $cancelRescheduleHtml;

    // Insert status check link after cancel/reschedule
    $bodySections .= $statusHtml;

    $htmlBody = wrapBrandedEmail($bodySections, $baseUrl, $baseUrl, false);

    // Build subject
    $subjectEs = replaceTemplateVarsRaw($tpl['subject_es'] ?? '', $vars);
    $subjectEn = replaceTemplateVarsRaw($tpl['subject_en'] ?? '', $vars);
    $subject = ($language === 'en') ? "✅ {$subjectEn} | {$subjectEs}" : "✅ {$subjectEs} | {$subjectEn}";

    // Build plain text (include calendar links)
    $textBody = buildBilingualPlainText($tpl, $vars, $language, $baseUrl);
    if ($rawDate && $rawTime && $referenceNumber) {
        $calLinks = buildCalendarLinks(
            $rawService ?: strtolower(str_replace(' ', '-', $service)),
            $rawDate, $rawTime, $referenceNumber
        );
        $textBody .= "\n\n📅 Add to Calendar / Agregar a Calendario:\n";
        $textBody .= "Google Calendar: {$calLinks['google_url']}\n";
        $textBody .= "Apple/Outlook (.ics): {$calLinks['ics_url']}\n";
    }

    // Add cancel/reschedule links to plain text
    if ($cancelToken !== '') {
        $cancelUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/') . '/cancel.php?token=' . urlencode($cancelToken);
        $rescheduleUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/') . '/reschedule.php?token=' . urlencode($cancelToken);
        $textBody .= "\n\nNeed to change your plans? / Necesita cambiar sus planes?\n";
        $textBody .= "Reschedule / Reprogramar: {$rescheduleUrl}\n";
        $textBody .= "Cancel / Cancelar: {$cancelUrl}\n";
    }

    $result = sendMail($email, $subject, $htmlBody, $textBody);

    if ($result['success']) {
        logEmail('booking_confirmation', "Booking confirmation sent to {$email}");
    }

    return $result;
}

/**
 * Send a branded appointment reminder email to the customer.
 *
 * Uses DB-driven templates (email_tpl_reminder_*) with the branded bilingual layout.
 * The button links to Google Maps for the shop location.
 *
 * @param array $appointment  Row from oretir_appointments (must include: id, first_name,
 *                            last_name, email, service, preferred_date, preferred_time,
 *                            language, and optionally vehicle_year/make/model)
 * @return bool  True if the email was sent successfully
 */
function sendAppointmentReminderEmail(array $appointment): bool
{
    $baseUrl  = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
    $mapsUrl  = 'https://www.google.com/maps/place/8536+SE+82nd+Ave,+Portland,+OR+97266';

    $customerName = trim($appointment['first_name'] . ' ' . $appointment['last_name']);
    $customerLang = ($appointment['language'] === 'spanish') ? 'es' : 'en';
    $serviceDisplay = ucwords(str_replace('-', ' ', $appointment['service']));

    // Format date for display (locale-aware)
    $dateObj = new DateTime($appointment['preferred_date']);
    $displayDate = ($customerLang === 'es')
        ? $dateObj->format('d/m/Y')
        : $dateObj->format('m/d/Y');

    // Format time for display
    $timeParts = explode(':', $appointment['preferred_time']);
    $hour = (int) $timeParts[0];
    $suffix = $hour >= 12 ? 'PM' : 'AM';
    $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
    $displayTime = $displayHour . ':00 ' . $suffix;

    // Reference number = appointment ID zero-padded
    $referenceNumber = str_pad((string) $appointment['id'], 5, '0', STR_PAD_LEFT);

    $vars = [
        'name'             => $customerName,
        'service'          => $serviceDisplay,
        'date'             => $displayDate,
        'time'             => $displayTime,
        'reference_number' => $referenceNumber,
        'email'            => $appointment['email'],
    ];

    // Load DB-driven reminder template
    $tpl = loadEmailTemplate('reminder');

    if (empty($tpl)) {
        error_log("Oregon Tires: No reminder email template found, cannot send reminder for appointment #{$appointment['id']}");
        return false;
    }

    // Build language sections with variable replacement
    $esGreeting = replaceTemplateVarsRaw($tpl['greeting_es'] ?? '', $vars);
    $esBody     = replaceTemplateVarsRaw($tpl['body_es'] ?? '', $vars);
    $esButton   = replaceTemplateVarsRaw($tpl['button_es'] ?? '', $vars);
    $esFooter   = replaceTemplateVarsRaw($tpl['footer_es'] ?? '', $vars);

    $enGreeting = replaceTemplateVarsRaw($tpl['greeting_en'] ?? '', $vars);
    $enBody     = replaceTemplateVarsRaw($tpl['body_en'] ?? '', $vars);
    $enButton   = replaceTemplateVarsRaw($tpl['button_en'] ?? '', $vars);
    $enFooter   = replaceTemplateVarsRaw($tpl['footer_en'] ?? '', $vars);

    $mexicanBar = 'linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%)';
    $usBar      = 'linear-gradient(90deg,#002868 0%,#002868 33%,#bf0a30 33%,#bf0a30 66%,#002868 66%,#002868 100%)';

    $esSection = buildLanguageSection('🇲🇽', 'Español', $esGreeting, $esBody, $esButton, $mapsUrl, $esFooter, 'h1', $mexicanBar);
    $enSection = buildLanguageSection('🇺🇸', 'English', $enGreeting, $enBody, $enButton, $mapsUrl, $enFooter, 'h2', $usBar);

    $divider = '<tr><td style="padding:0 36px;"><div style="height:1px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);"></div></td></tr>';

    if ($customerLang === 'en') {
        $bodySections = $enSection . $divider . $esSection;
    } else {
        $bodySections = $esSection . $divider . $enSection;
    }

    $htmlBody = wrapBrandedEmail($bodySections, $baseUrl, $mapsUrl, false);

    // Build subject — primary language first
    $subjectEs = replaceTemplateVarsRaw($tpl['subject_es'] ?? '', $vars);
    $subjectEn = replaceTemplateVarsRaw($tpl['subject_en'] ?? '', $vars);

    if ($customerLang === 'en') {
        $subject = "⏰ {$subjectEn} | {$subjectEs}";
    } else {
        $subject = "⏰ {$subjectEs} | {$subjectEn}";
    }

    // Build plain text version
    $textBody = buildBilingualPlainText($tpl, $vars, $customerLang, $mapsUrl);

    $result = sendMail($appointment['email'], $subject, $htmlBody, $textBody);

    if ($result['success']) {
        logEmail('appointment_reminder', "Reminder sent to {$appointment['email']} for appointment #{$appointment['id']} (ref: {$referenceNumber})");
    } else {
        logEmail('appointment_reminder_failed', "Reminder FAILED for {$appointment['email']} appointment #{$appointment['id']}: " . ($result['error'] ?? 'unknown'));
    }

    return $result['success'];
}

/**
 * Send a branded booking notification email to the shop owner.
 */
function sendBookingOwnerNotification(
    int $appointmentId,
    string $referenceNumber,
    string $service,
    string $preferredDate,
    string $preferredTime,
    string $firstName,
    string $lastName,
    string $email,
    string $phone,
    string $vehicleInfo,
    string $language,
    string $notes
): array {
    $contactAddr = $_ENV['CONTACT_EMAIL'] ?? $_ENV['SMTP_FROM'] ?? '';
    if (empty($contactAddr)) {
        return ['success' => false, 'error' => 'No contact email configured.'];
    }

    $baseUrl  = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
    $adminUrl = $baseUrl . '/admin/';

    $serviceDisplay = ucwords(str_replace('-', ' ', $service));

    // Format time for display
    $timeParts = explode(':', $preferredTime);
    $hour = (int) $timeParts[0];
    $suffix = $hour >= 12 ? 'PM' : 'AM';
    $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
    $displayTime = $displayHour . ':00 ' . $suffix;

    $vars = [
        'appointment_id'   => $appointmentId,
        'reference_number' => $referenceNumber,
        'service'          => $serviceDisplay,
        'date'             => $preferredDate,
        'time'             => $displayTime,
        'name'             => "{$firstName} {$lastName}",
        'email'            => $email,
        'phone'            => $phone,
        'vehicle'          => $vehicleInfo ?: 'N/A',
        'language'         => $language,
        'notes'            => $notes ?: 'None',
    ];

    $result = sendBrandedTemplateEmail($contactAddr, 'booking_owner', $vars, 'both', $adminUrl, false);

    // Fallback: if no DB template exists, send simple HTML notification
    if (!$result['success'] && str_contains(($result['error'] ?? ''), 'not found')) {
        $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $subject = "New Appointment: {$h($serviceDisplay)} — {$h($firstName)} {$h($lastName)}";

        $htmlBody = <<<HTML
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
            <div style="background:linear-gradient(135deg,#15803d,#166534);color:#fff;padding:20px;text-align:center;border-radius:8px 8px 0 0;">
                <h2 style="margin:0;">New Appointment Booking</h2>
                <p style="margin:8px 0 0;opacity:0.9;font-size:14px;">#{$appointmentId} — {$h($referenceNumber)} — {$h($serviceDisplay)}</p>
            </div>
            <div style="background:#fff;padding:24px;border:1px solid #e0e0e0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr style="background:#f0f9f0;"><td style="padding:8px 12px;font-weight:bold;color:#555;width:140px;">Reference:</td><td style="padding:8px 12px;font-weight:bold;color:#15803d;font-size:16px;">{$h($referenceNumber)}</td></tr>
                    <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Service:</td><td style="padding:8px 12px;">{$h($serviceDisplay)}</td></tr>
                    <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Date:</td><td style="padding:8px 12px;">{$h($preferredDate)}</td></tr>
                    <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Time:</td><td style="padding:8px 12px;">{$h($displayTime)}</td></tr>
                    <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Customer:</td><td style="padding:8px 12px;">{$h($firstName)} {$h($lastName)}</td></tr>
                    <tr><td style="padding:8px 12px;font-weight:bold;color:#555;">Email:</td><td style="padding:8px 12px;"><a href="mailto:{$h($email)}">{$h($email)}</a></td></tr>
                    <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Phone:</td><td style="padding:8px 12px;"><a href="tel:{$h($phone)}">{$h($phone)}</a></td></tr>
        HTML;

        if ($vehicleInfo) {
            $htmlBody .= "<tr><td style=\"padding:8px 12px;font-weight:bold;color:#555;\">Vehicle:</td><td style=\"padding:8px 12px;\">{$h($vehicleInfo)}</td></tr>";
        }

        $htmlBody .= <<<HTML
                    <tr style="background:#f9f9f9;"><td style="padding:8px 12px;font-weight:bold;color:#555;">Language:</td><td style="padding:8px 12px;">{$h($language)}</td></tr>
                </table>
        HTML;

        if ($notes) {
            $htmlBody .= "<div style=\"margin-top:16px;padding:16px;background:#f5f5f5;border-left:4px solid #15803d;border-radius:4px;\"><strong style=\"color:#555;\">Notes:</strong><p style=\"margin:8px 0 0;color:#333;line-height:1.6;\">{$h($notes)}</p></div>";
        }

        $htmlBody .= <<<HTML
            </div>
            <div style="background:#15803d;padding:12px;text-align:center;font-size:12px;color:#fff;border-radius:0 0 8px 8px;">
                Oregon Tires Auto Care — {$h($referenceNumber)} — Appointment #{$appointmentId}
            </div>
        </div>
        HTML;

        $result = sendMail($contactAddr, $subject, $htmlBody);
    }

    $logDesc = $result['success']
        ? "Booking notification sent for appointment #{$appointmentId}"
        : "Booking notification FAILED for appointment #{$appointmentId}: " . ($result['error'] ?? 'unknown');
    logEmail('booking', $logDesc);

    return $result;
}

/**
 * Send a contact notification email to the owner.
 */
function sendContactNotificationEmail(string $contactName, string $contactEmail, string $message): array
{
    $contactAddr = $_ENV['CONTACT_EMAIL'] ?? $_ENV['SMTP_FROM'] ?? '';
    if (empty($contactAddr)) {
        return ['success' => false, 'error' => 'No contact email configured.'];
    }

    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
    $adminUrl = $baseUrl . '/admin/';

    $vars = [
        'name'    => $contactName,
        'email'   => $contactEmail,
        'message' => $message,
    ];

    $result = sendBrandedTemplateEmail($contactAddr, 'contact', $vars, 'both', $adminUrl, false);

    if ($result['success']) {
        logEmail('contact_notification', "Contact notification for message from {$contactEmail}", null);
    }

    return $result;
}

/**
 * Send a post-service Google Review request email to the customer.
 *
 * Sends a branded bilingual email thanking the customer for their visit
 * and inviting them to leave a Google review with a prominent CTA button.
 *
 * @param array $appt  Row from oretir_appointments (must include: id, first_name,
 *                     last_name, email, service, preferred_date, preferred_time, language)
 * @return bool  True if the email was sent successfully
 */
function sendReviewRequestEmail(array $appt): bool
{
    $baseUrl   = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
    $reviewUrl    = 'https://search.google.com/local/writereview?placeid=ChIJLSxZDQyflVQRWXEi9LpJGxs';
    $refEncoded   = urlencode($appt['reference_number'] ?? '');
    $emailEncoded = urlencode($appt['email'] ?? '');
    $feedbackBase = $baseUrl . '/feedback/?ref=' . $refEncoded . '&email=' . $emailEncoded . '&rating=';
    $fb1 = $feedbackBase . '1';
    $fb2 = $feedbackBase . '2';
    $fb3 = $feedbackBase . '3';

    $customerName   = trim($appt['first_name'] . ' ' . $appt['last_name']);
    $customerLang   = ($appt['language'] === 'spanish') ? 'es' : 'en';
    $serviceDisplay = ucwords(str_replace('-', ' ', $appt['service']));

    // Format date for display (locale-aware)
    $dateObj     = new DateTime($appt['preferred_date']);
    $displayDate = ($customerLang === 'es')
        ? $dateObj->format('d/m/Y')
        : $dateObj->format('m/d/Y');

    // Format time for display
    $timeParts   = explode(':', $appt['preferred_time']);
    $hour        = (int) $timeParts[0];
    $suffix      = $hour >= 12 ? 'PM' : 'AM';
    $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
    $displayTime = $displayHour . ':00 ' . $suffix;

    $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

    // ── Build bilingual body sections ─────────────────────────────────────

    $mexicanBar = 'linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%)';
    $usBar      = 'linear-gradient(90deg,#002868 0%,#002868 33%,#bf0a30 33%,#bf0a30 66%,#002868 66%,#002868 100%)';

    // Spanish section
    $esSection = <<<HTML
  <tr>
    <td style="padding:0;">
      <div style="height:3px;background:{$mexicanBar};"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:32px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">&#x1F1F2;&#x1F1FD; Espa&ntilde;ol</p>
            <h1 style="color:#15803d;font-size:24px;margin:0 0 8px;font-weight:800;">&iexcl;Gracias por su visita, {$h($customerName)}!</h1>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 8px;">
              Esperamos que haya tenido una excelente experiencia en Oregon Tires Auto Care.
            </p>
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;margin:0 0 20px;">
              <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">
                <strong>Servicio:</strong> {$h($serviceDisplay)}<br>
                <strong>Fecha:</strong> {$h($displayDate)}
              </p>
            </div>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              Su opini&oacute;n es muy importante para nosotros. &iquest;Podr&iacute;a tomarse un momento para compartir su experiencia?
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 24px;">
            <p style="color:#374151;font-size:13px;font-weight:600;margin:0 0 14px;">&iquest;C&oacute;mo calificar&iacute;a su visita?</p>
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:3px;"><a href="{$fb1}" target="_blank" style="display:inline-block;padding:10px 11px;background:#f3f4f6;color:#374151;text-decoration:none;border:2px solid #d1d5db;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;<br><small style="font-size:10px;color:#6b7280;">1</small></a></td>
                <td style="padding:3px;"><a href="{$fb2}" target="_blank" style="display:inline-block;padding:10px 11px;background:#f3f4f6;color:#374151;text-decoration:none;border:2px solid #d1d5db;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;<br><small style="font-size:10px;color:#6b7280;">2</small></a></td>
                <td style="padding:3px;"><a href="{$fb3}" target="_blank" style="display:inline-block;padding:10px 11px;background:#f3f4f6;color:#374151;text-decoration:none;border:2px solid #d1d5db;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;&#9733;<br><small style="font-size:10px;color:#6b7280;">3</small></a></td>
                <td style="padding:3px;"><a href="{$reviewUrl}" target="_blank" style="display:inline-block;padding:10px 11px;background:#d4a843;color:#1a1a2e;text-decoration:none;border:2px solid #b8922a;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;&#9733;&#9733;<br><small style="font-size:10px;color:#78350f;">4</small></a></td>
                <td style="padding:3px;"><a href="{$reviewUrl}" target="_blank" style="display:inline-block;padding:10px 11px;background:#d4a843;color:#1a1a2e;text-decoration:none;border:2px solid #b8922a;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;&#9733;&#9733;&#9733;<br><small style="font-size:10px;color:#78350f;">5</small></a></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 36px 28px;">
            <p style="color:#6b7280;font-size:13px;line-height:1.6;margin:0;">
              Gracias por elegir Oregon Tires Auto Care. &iexcl;Lo esperamos pronto!
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
HTML;

    // English section
    $enSection = <<<HTML
  <tr>
    <td style="padding:0;">
      <div style="height:3px;background:{$usBar};"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:32px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">&#x1F1FA;&#x1F1F8; English</p>
            <h2 style="color:#15803d;font-size:24px;margin:0 0 8px;font-weight:800;">Thank you for your visit, {$h($customerName)}!</h2>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 8px;">
              We hope you had a great experience at Oregon Tires Auto Care.
            </p>
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;margin:0 0 20px;">
              <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">
                <strong>Service:</strong> {$h($serviceDisplay)}<br>
                <strong>Date:</strong> {$h($displayDate)}
              </p>
            </div>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              Your feedback means a lot to us. Would you take a moment to share your experience?
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 24px;">
            <p style="color:#374151;font-size:13px;font-weight:600;margin:0 0 14px;">How would you rate your visit?</p>
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:3px;"><a href="{$fb1}" target="_blank" style="display:inline-block;padding:10px 11px;background:#f3f4f6;color:#374151;text-decoration:none;border:2px solid #d1d5db;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;<br><small style="font-size:10px;color:#6b7280;">1</small></a></td>
                <td style="padding:3px;"><a href="{$fb2}" target="_blank" style="display:inline-block;padding:10px 11px;background:#f3f4f6;color:#374151;text-decoration:none;border:2px solid #d1d5db;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;<br><small style="font-size:10px;color:#6b7280;">2</small></a></td>
                <td style="padding:3px;"><a href="{$fb3}" target="_blank" style="display:inline-block;padding:10px 11px;background:#f3f4f6;color:#374151;text-decoration:none;border:2px solid #d1d5db;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;&#9733;<br><small style="font-size:10px;color:#6b7280;">3</small></a></td>
                <td style="padding:3px;"><a href="{$reviewUrl}" target="_blank" style="display:inline-block;padding:10px 11px;background:#d4a843;color:#1a1a2e;text-decoration:none;border:2px solid #b8922a;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;&#9733;&#9733;<br><small style="font-size:10px;color:#78350f;">4</small></a></td>
                <td style="padding:3px;"><a href="{$reviewUrl}" target="_blank" style="display:inline-block;padding:10px 11px;background:#d4a843;color:#1a1a2e;text-decoration:none;border:2px solid #b8922a;border-radius:10px;font-size:14px;font-weight:700;text-align:center;">&#9733;&#9733;&#9733;&#9733;&#9733;<br><small style="font-size:10px;color:#78350f;">5</small></a></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 36px 28px;">
            <p style="color:#6b7280;font-size:13px;line-height:1.6;margin:0;">
              Thank you for choosing Oregon Tires Auto Care. We look forward to seeing you again!
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
HTML;

    $divider = '<tr><td style="padding:0 36px;"><div style="height:1px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);"></div></td></tr>';

    if ($customerLang === 'en') {
        $bodySections = $enSection . $divider . $esSection;
    } else {
        $bodySections = $esSection . $divider . $enSection;
    }

    // ── Build full HTML email ─────────────────────────────────────────────

    $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="{$customerLang}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oregon Tires Auto Care</title>
</head>
<body style="margin:0;padding:0;background-color:#f0fdf4;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;">
<tr><td align="center" style="padding:30px 15px;">

<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

  <!-- HEADER -->
  <tr>
    <td style="background:linear-gradient(135deg,#15803d 0%,#166534 50%,#1a1a2e 100%);padding:0;">
      <div style="height:4px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:32px 30px 24px;">
            <img src="{$baseUrl}/assets/logo.png" alt="Oregon Tires Auto Care" width="140" style="display:block;max-width:140px;height:auto;margin-bottom:16px;">
            <p style="color:#f5d78e;font-size:13px;margin:0;letter-spacing:2px;text-transform:uppercase;font-weight:600;">&#11088; &#11088; &#11088; &#11088; &#11088;</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

{$bodySections}

  <!-- FOOTER -->
  <tr>
    <td style="background-color:#1a1a2e;padding:0;">
      <div style="height:3px;background:linear-gradient(90deg,#d4a843,#f5d78e,#d4a843);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:24px 30px;">
            <p style="color:#d4a843;font-size:14px;font-weight:700;margin:0 0 6px;">Oregon Tires Auto Care</p>
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">8536 SE 82nd Ave, Portland, OR 97266</p>
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">&#128222; (503) 367-9714</p>
            <p style="color:#9ca3af;font-size:12px;margin:0;">Lunes&ndash;S&aacute;bado 7:00 AM &ndash; 7:00 PM</p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 30px 20px;">
            <p style="color:#6b7280;font-size:10px;margin:0;">
              Este correo fue enviado desde una direcci&oacute;n que no acepta respuestas.<br>
              This email was sent from a no-reply address.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>
</td></tr>
</table>

</body>
</html>
HTML;

    // ── Build plain-text version ──────────────────────────────────────────

    $textEs  = "OREGON TIRES AUTO CARE\n";
    $textEs .= "========================================\n\n";
    $textEs .= "Gracias por su visita, {$customerName}!\n\n";
    $textEs .= "Servicio: {$serviceDisplay}\n";
    $textEs .= "Fecha: {$displayDate}\n\n";
    $textEs .= "Su opinion es muy importante para nosotros.\n";
    $textEs .= "Calificacion 1-3 estrellas: {$fb3}\n";
    $textEs .= "Calificacion 4-5 estrellas: {$reviewUrl}\n\n";
    $textEs .= "Gracias por elegir Oregon Tires Auto Care.\n";

    $textEn  = "OREGON TIRES AUTO CARE\n";
    $textEn .= "========================================\n\n";
    $textEn .= "Thank you for your visit, {$customerName}!\n\n";
    $textEn .= "Service: {$serviceDisplay}\n";
    $textEn .= "Date: {$displayDate}\n\n";
    $textEn .= "Your feedback means a lot to us.\n";
    $textEn .= "Rate 1-3 stars (private feedback): {$fb3}\n";
    $textEn .= "Rate 4-5 stars (Google Reviews): {$reviewUrl}\n\n";
    $textEn .= "Thank you for choosing Oregon Tires Auto Care.\n";

    $textFooter  = "\n========================================\n";
    $textFooter .= "Oregon Tires Auto Care\n";
    $textFooter .= "8536 SE 82nd Ave, Portland, OR 97266\n";
    $textFooter .= "(503) 367-9714\n";
    $textFooter .= "Mon-Sat 7:00 AM - 7:00 PM";

    if ($customerLang === 'en') {
        $textBody = $textEn . "\n========================================\n\n" . $textEs . $textFooter;
    } else {
        $textBody = $textEs . "\n========================================\n\n" . $textEn . $textFooter;
    }

    // ── Build subject line ────────────────────────────────────────────────

    if ($customerLang === 'en') {
        $subject = "How was your visit? | Como fue su visita? — Oregon Tires";
    } else {
        $subject = "Como fue su visita? | How was your visit? — Oregon Tires";
    }

    // ── Send ──────────────────────────────────────────────────────────────

    $result = sendMail($appt['email'], $subject, $htmlBody, $textBody);

    if ($result['success']) {
        logEmail('review_request', "Review request sent to {$appt['email']} for appointment #{$appt['id']}");
    } else {
        logEmail('review_request_failed', "Review request FAILED for {$appt['email']} appointment #{$appt['id']}: " . ($result['error'] ?? 'unknown'));
    }

    return $result['success'];
}
