<?php
/**
 * Oregon Tires — Resend Setup Emails via Exim/Sendmail
 * Uses hiphop.world as sender domain (has valid SPF/DKIM/DMARC on public DNS)
 * Run: php cli/resend-setup-emails.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

// Load env for DB access
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/validate.php';

$db = getDB();
$baseUrl = 'https://oregon.tires';

// Get all admins with active setup tokens
$stmt = $db->prepare(
    'SELECT id, email, display_name, password_reset_token, password_reset_expires
     FROM oretir_admins
     WHERE is_active = 1
       AND password_reset_token IS NOT NULL
       AND password_reset_expires > NOW()
     ORDER BY id'
);
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($admins)) {
    echo "No admins with active setup tokens found.\n";
    exit(0);
}

echo "Found " . count($admins) . " admin(s) with active setup tokens:\n\n";

foreach ($admins as $admin) {
    $email    = $admin['email'];
    $name     = $admin['display_name'];
    $token    = $admin['password_reset_token'];
    $setupUrl = $baseUrl . '/admin/setup-password.html?token=' . $token;

    echo "━━━ Sending to: {$email} ({$name}) ━━━\n";
    echo "  Setup URL: {$setupUrl}\n";

    $mail = new PHPMailer(true);

    try {
        // Use sendmail/Exim directly — NOT SMTP auth
        // This ensures DKIM is signed by the server for the From domain
        $mail->isSendmail();
        $mail->CharSet = 'UTF-8';

        // Send FROM hiphop.world (has valid SPF/DKIM/DMARC on public DNS)
        $mail->setFrom('contact@hiphop.world', 'Oregon Tires Auto Care');
        $mail->addReplyTo('contact@oregon.tires', 'Oregon Tires Auto Care');

        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "🔐 Configura tu Contraseña — Oregon Tires Admin | Set Up Your Password";
        $mail->Body    = buildSetupEmail($name, $setupUrl, $baseUrl);
        $mail->AltBody = buildSetupTextEmail($name, $setupUrl);

        $mail->send();
        echo "  ✅ Email sent successfully!\n\n";

    } catch (\Throwable $e) {
        echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
    }
}

echo "━━━ Done! ━━━\n";


// ─── Email Template ────────────────────────────────────────────────────────

function buildSetupEmail(string $name, string $setupUrl, string $baseUrl): string
{
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oregon Tires — Configuración de Cuenta</title>
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

  <!-- SPANISH SECTION -->
  <tr>
    <td style="padding:0;">
      <div style="height:3px;background:linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:32px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">🇲🇽 Español</p>
            <h1 style="color:#15803d;font-size:24px;margin:0 0 8px;font-weight:800;">¡Bienvenido/a, {$name}!</h1>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              Has sido invitado/a al <strong style="color:#15803d;">Panel de Administración de Oregon Tires Auto Care</strong>. Para activar tu cuenta, configura tu contraseña haciendo clic en el botón de abajo.
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;box-shadow:0 4px 14px rgba(21,128,61,0.35);">
                  <a href="{$setupUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    🔐 Configurar Mi Contraseña
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 36px 28px;">
            <p style="color:#6b7280;font-size:13px;line-height:1.6;margin:0;">
              Este enlace expira en <strong>7 días</strong>. Si no solicitaste esta cuenta, puedes ignorar este correo de forma segura.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- DIVIDER -->
  <tr>
    <td style="padding:0 36px;">
      <div style="height:1px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);"></div>
    </td>
  </tr>

  <!-- ENGLISH SECTION -->
  <tr>
    <td style="padding:0;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:28px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">🇺🇸 English</p>
            <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;font-weight:800;">Welcome, {$name}!</h2>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              You've been invited to the <strong style="color:#15803d;">Oregon Tires Auto Care Admin Panel</strong>. To activate your account, set up your password by clicking the button below.
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;box-shadow:0 4px 14px rgba(21,128,61,0.35);">
                  <a href="{$setupUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    🔐 Set Up My Password
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 36px 28px;">
            <p style="color:#6b7280;font-size:13px;line-height:1.6;margin:0;">
              This link expires in <strong>7 days</strong>. If you didn't request this account, you can safely ignore this email.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

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
        <a href="{$setupUrl}" style="color:#15803d;word-break:break-all;font-size:11px;">{$setupUrl}</a>
      </p>
    </td>
  </tr>

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

function buildSetupTextEmail(string $name, string $setupUrl): string
{
    return <<<TEXT
═══════════════════════════════════════
OREGON TIRES AUTO CARE — Panel de Administración
═══════════════════════════════════════

🇲🇽 ESPAÑOL

¡Bienvenido/a, {$name}!

Has sido invitado/a al Panel de Administración de Oregon Tires Auto Care.
Para activar tu cuenta, configura tu contraseña visitando el siguiente enlace:

🔐 {$setupUrl}

Este enlace expira en 7 días.

Requisitos de contraseña:
✓ Mínimo 8 caracteres
✓ Una letra mayúscula
✓ Una letra minúscula
✓ Un número

═══════════════════════════════════════

🇺🇸 ENGLISH

Welcome, {$name}!

You've been invited to the Oregon Tires Auto Care Admin Panel.
To activate your account, set up your password by visiting the link below:

🔐 {$setupUrl}

This link expires in 7 days.

Password requirements:
✓ Minimum 8 characters
✓ One uppercase letter
✓ One lowercase letter
✓ One number

═══════════════════════════════════════

Oregon Tires Auto Care
8536 SE 82nd Ave, Portland, OR 97266
📞 (503) 367-9714
TEXT;
}
