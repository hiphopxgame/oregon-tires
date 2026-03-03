<?php
/**
 * Oregon Tires — Send Welcome Email to All Admins
 * Notifies admins they can log in at oregon.tires/admin
 * Run: php cli/send-welcome-emails.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

// Load env
$envDir = dirname(__DIR__, 3);
$envFile = '.env.oregon-tires';
if (!file_exists($envDir . '/' . $envFile)) {
    $envDir = __DIR__ . '/..';
    $envFile = '.env';
}
$dotenv = Dotenv\Dotenv::createImmutable($envDir, $envFile);
$dotenv->load();

require_once __DIR__ . '/../includes/db.php';

$db = getDB();
$loginUrl = 'https://oregon.tires/admin';

// Get all active admins
$stmt = $db->query('SELECT id, email, display_name, role FROM oretir_admins WHERE is_active = 1 ORDER BY id');
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($admins)) {
    echo "No active admins found.\n";
    exit(0);
}

echo "Sending welcome email to " . count($admins) . " admin(s)...\n\n";

$sent = 0;
$failed = 0;

foreach ($admins as $admin) {
    $email = $admin['email'];
    $name  = $admin['display_name'];
    $role  = $admin['role'];

    echo "━━━ {$name} <{$email}> ({$role}) ━━━\n";

    $mail = new PHPMailer(true);

    try {
        $mail->isSendmail();
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('contact@hiphop.world', 'Oregon Tires Auto Care');
        $mail->addReplyTo('oregontirespdx@gmail.com', 'Oregon Tires Auto Care');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = "🛞 Oregon Tires Admin Panel is Live! | ¡El Panel de Admin está Listo!";
        $mail->Body    = buildWelcomeHtml($name, $role, $loginUrl);
        $mail->AltBody = buildWelcomeText($name, $role, $loginUrl);

        $mail->send();
        echo "  ✅ Sent!\n\n";
        $sent++;

    } catch (\Throwable $e) {
        echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
        $failed++;
    }
}

echo "━━━ Done! Sent: {$sent}, Failed: {$failed} ━━━\n";


// ─── Email Templates ─────────────────────────────────────────────────────────

function buildWelcomeHtml(string $name, string $role, string $loginUrl): string
{
    $roleLabel = $role === 'superadmin' ? 'Super Admin' : 'Admin';
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <img src="https://oregon.tires/assets/logo.png" alt="Oregon Tires Auto Care" width="140" style="display:block;max-width:140px;height:auto;margin-bottom:16px;">
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
            <h1 style="color:#15803d;font-size:24px;margin:0 0 8px;font-weight:800;">¡Hola, {$name}!</h1>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              El <strong style="color:#15803d;">Panel de Administración de Oregon Tires</strong> está listo. Ya puedes iniciar sesión para administrar citas, clientes, órdenes de reparación, mensajes y más.
            </p>
            <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 20px;">
              Tu rol: <strong>{$roleLabel}</strong>
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;box-shadow:0 4px 14px rgba(21,128,61,0.35);">
                  <a href="{$loginUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    🔑 Iniciar Sesión
                  </a>
                </td>
              </tr>
            </table>
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
            <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;font-weight:800;">Hi, {$name}!</h2>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              The <strong style="color:#15803d;">Oregon Tires Admin Panel</strong> is live. You can now log in to manage appointments, customers, repair orders, messages, and more.
            </p>
            <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 20px;">
              Your role: <strong>{$roleLabel}</strong>
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;box-shadow:0 4px 14px rgba(21,128,61,0.35);">
                  <a href="{$loginUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    🔑 Log In to Admin Panel
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- WHAT YOU CAN DO -->
  <tr>
    <td style="padding:0 36px 28px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#374151;font-size:13px;font-weight:700;margin:0 0 12px;">What you can do / Lo que puedes hacer:</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#6b7280;">
              <tr><td style="padding:4px 0;">📅 View &amp; manage appointments / Ver y gestionar citas</td></tr>
              <tr><td style="padding:4px 0;">👥 Customer &amp; vehicle records / Registros de clientes y vehículos</td></tr>
              <tr><td style="padding:4px 0;">🔧 Repair orders &amp; inspections / Órdenes de reparación e inspecciones</td></tr>
              <tr><td style="padding:4px 0;">💬 Messages &amp; reviews / Mensajes y reseñas</td></tr>
              <tr><td style="padding:4px 0;">📊 Dashboard &amp; analytics / Panel y analíticas</td></tr>
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
        Si los botones no funcionan / If the buttons don't work:<br>
        <a href="{$loginUrl}" style="color:#15803d;word-break:break-all;font-size:11px;">{$loginUrl}</a>
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

function buildWelcomeText(string $name, string $role, string $loginUrl): string
{
    $roleLabel = $role === 'superadmin' ? 'Super Admin' : 'Admin';
    return <<<TEXT
═══════════════════════════════════════
OREGON TIRES AUTO CARE — Admin Panel
═══════════════════════════════════════

🇲🇽 ESPAÑOL

¡Hola, {$name}!

El Panel de Administración de Oregon Tires está listo.
Ya puedes iniciar sesión para administrar citas, clientes, órdenes de reparación, mensajes y más.

Tu rol: {$roleLabel}

🔑 Iniciar sesión: {$loginUrl}

Lo que puedes hacer:
📅 Ver y gestionar citas
👥 Registros de clientes y vehículos
🔧 Órdenes de reparación e inspecciones
💬 Mensajes y reseñas
📊 Panel y analíticas

═══════════════════════════════════════

🇺🇸 ENGLISH

Hi, {$name}!

The Oregon Tires Admin Panel is live.
You can now log in to manage appointments, customers, repair orders, messages, and more.

Your role: {$roleLabel}

🔑 Log in: {$loginUrl}

What you can do:
📅 View & manage appointments
👥 Customer & vehicle records
🔧 Repair orders & inspections
💬 Messages & reviews
📊 Dashboard & analytics

═══════════════════════════════════════

Oregon Tires Auto Care
8536 SE 82nd Ave, Portland, OR 97266
📞 (503) 367-9714
TEXT;
}
