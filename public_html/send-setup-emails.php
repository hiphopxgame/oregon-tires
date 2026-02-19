<?php
/**
 * Oregon Tires — Send Password Setup Emails
 * Run via CLI: php send-setup-emails.php
 * DELETE after sending.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/mail.php';

$db = getDB();

// Get all admins with valid tokens
$admins = $db->query(
    'SELECT id, email, display_name, role, password_reset_token
     FROM oretir_admins
     WHERE password_reset_token IS NOT NULL AND password_reset_expires > NOW() AND is_active = 1
     ORDER BY id'
)->fetchAll();

if (empty($admins)) {
    echo "No admins with pending setup tokens.\n";
    exit;
}

echo "Sending setup emails to " . count($admins) . " admins...\n\n";

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

foreach ($admins as $admin) {
    $name    = htmlspecialchars($admin['display_name'], ENT_QUOTES, 'UTF-8');
    $email   = $admin['email'];
    $token   = $admin['password_reset_token'];
    $role    = $admin['role'] === 'superadmin' ? 'Super Administrador' : 'Administrador';
    $roleEn  = $admin['role'] === 'superadmin' ? 'Super Admin' : 'Administrator';
    $setupUrl = "{$baseUrl}/admin/setup-password.html?token={$token}";

    $subject = "Oregon Tires — Configurar su Cuenta de Administrador / Set Up Your Admin Account";

    $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  <!-- Header with Logo -->
  <tr>
    <td style="background:linear-gradient(135deg,#15803d 0%,#166534 50%,#1a1a2e 100%);padding:32px 40px;text-align:center;border-radius:16px 16px 0 0;">
      <img src="{$baseUrl}/assets/logo.png" alt="Oregon Tires Auto Care" width="180" style="max-width:180px;height:auto;margin-bottom:16px;">
      <h1 style="color:#ffffff;font-size:24px;margin:0;font-weight:700;">Panel de Administración</h1>
      <p style="color:#bbf7d0;font-size:14px;margin:8px 0 0;">Admin Panel</p>
    </td>
  </tr>

  <!-- Spanish Section -->
  <tr>
    <td style="background:#ffffff;padding:32px 40px;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;">
      <div style="background:#f0fdf4;border-left:4px solid #15803d;padding:16px 20px;border-radius:0 8px 8px 0;margin-bottom:24px;">
        <p style="margin:0;color:#15803d;font-size:18px;font-weight:700;">¡Hola, {$name}!</p>
        <p style="margin:6px 0 0;color:#166534;font-size:14px;">Rol: {$role}</p>
      </div>

      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">
        Se le ha otorgado acceso al <strong>Panel de Administración de Oregon Tires Auto Care</strong>.
        Desde aquí podrá gestionar citas, mensajes de contacto, empleados, galería de imágenes y contenido del sitio web.
      </p>

      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 24px;">
        Para comenzar, haga clic en el botón de abajo para configurar su contraseña segura. Este enlace expira en <strong>7 días</strong>.
      </p>

      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" style="padding:8px 0 24px;">
            <a href="{$setupUrl}" style="display:inline-block;background:#15803d;color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;padding:14px 40px;border-radius:8px;box-shadow:0 4px 12px rgba(21,128,61,0.3);">
              Configurar Contraseña
            </a>
          </td>
        </tr>
      </table>

      <!-- Divider -->
      <hr style="border:none;border-top:2px solid #e5e7eb;margin:8px 0 24px;">

      <!-- English Translation -->
      <div style="background:#f9fafb;padding:20px 24px;border-radius:8px;border:1px solid #e5e7eb;">
        <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin:0 0 12px;font-weight:600;">English Translation</p>

        <p style="color:#4b5563;font-size:14px;line-height:1.7;margin:0 0 12px;">
          <strong>Hello, {$name}!</strong> (Role: {$roleEn})
        </p>

        <p style="color:#4b5563;font-size:14px;line-height:1.7;margin:0 0 12px;">
          You've been granted access to the <strong>Oregon Tires Auto Care Admin Panel</strong>.
          From here you can manage appointments, contact messages, employees, gallery images, and website content.
        </p>

        <p style="color:#4b5563;font-size:14px;line-height:1.7;margin:0 0 16px;">
          Click the button below to set up your secure password. This link expires in <strong>7 days</strong>.
        </p>

        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center">
              <a href="{$setupUrl}" style="display:inline-block;background:#166534;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;padding:10px 32px;border-radius:6px;">
                Set Up Password
              </a>
            </td>
          </tr>
        </table>
      </div>
    </td>
  </tr>

  <!-- Contact Info Bar -->
  <tr>
    <td style="background:#fefce8;padding:16px 40px;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="color:#92400e;font-size:13px;">
            <strong>Oregon Tires Auto Care</strong><br>
            8536 SE 82nd Ave, Portland, OR 97266<br>
            (503) 367-9714 &bull; <a href="https://oregon.tires" style="color:#15803d;text-decoration:none;">oregon.tires</a>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#1a1a2e;padding:20px 40px;text-align:center;border-radius:0 0 16px 16px;">
      <p style="color:#9ca3af;font-size:11px;margin:0;line-height:1.6;">
        Este correo fue enviado automáticamente. No responda a este mensaje.<br>
        This email was sent automatically. Please do not reply.
      </p>
      <p style="color:#6b7280;font-size:11px;margin:8px 0 0;">
        &copy; 2026 Oregon Tires Auto Care &bull; Powered by <a href="https://1vsM.com" style="color:#facc15;text-decoration:none;">1vsM.com</a>
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;

    $textBody = "Hola {$admin['display_name']},\n\n"
        . "Se le ha otorgado acceso al Panel de Administración de Oregon Tires.\n"
        . "Configure su contraseña aquí: {$setupUrl}\n\n"
        . "Este enlace expira en 7 días.\n\n"
        . "---\n\n"
        . "Hello {$admin['display_name']},\n\n"
        . "You've been granted access to the Oregon Tires Admin Panel.\n"
        . "Set up your password here: {$setupUrl}\n\n"
        . "This link expires in 7 days.\n\n"
        . "Oregon Tires Auto Care\n"
        . "8536 SE 82nd Ave, Portland, OR 97266\n"
        . "(503) 367-9714";

    echo "Sending to {$email} ({$admin['display_name']})... ";

    $result = sendMail($email, $subject, $htmlBody, $textBody);

    if ($result['success']) {
        echo "SENT\n";
        logEmail('admin_setup', "Setup email sent to {$email} ({$admin['display_name']})");
    } else {
        echo "FAILED: {$result['error']}\n";
        logEmail('admin_setup_failed', "Setup email FAILED for {$email}: {$result['error']}");
    }
}

echo "\nDone. Delete this file: rm send-setup-emails.php\n";
