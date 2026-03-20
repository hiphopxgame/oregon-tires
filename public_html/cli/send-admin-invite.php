<?php
/**
 * Oregon Tires — Send Admin Login Invite Email
 * Sends a branded bilingual email inviting an admin to log in via Google or credentials.
 *
 * Usage:
 *   php cli/send-admin-invite.php --preview   # Write HTML to cli/logs/
 *   php cli/send-admin-invite.php --send       # Send email
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

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
require_once __DIR__ . '/../includes/mail.php';

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

$recipient = 'oregontirespdx@gmail.com';
$subject   = 'Tu Panel de Admin Está Listo — Inicia Sesión con Google / Your Admin Panel is Ready';

// ─── Parse CLI args ─────────────────────────────────────────────────────────
$mode = null;
foreach ($argv as $arg) {
    if ($arg === '--preview') $mode = 'preview';
    if ($arg === '--send')    $mode = 'send';
}

if (!$mode) {
    echo "Usage:\n";
    echo "  php cli/send-admin-invite.php --preview   # Write HTML preview\n";
    echo "  php cli/send-admin-invite.php --send       # Send email\n";
    exit(1);
}

$html = buildInviteEmail($baseUrl);

if ($mode === 'preview') {
    file_put_contents(__DIR__ . '/logs/admin-invite.html', $html);
    echo "✓ Preview: cli/logs/admin-invite.html\n";
    exit(0);
}

// ─── Send ───────────────────────────────────────────────────────────────────
$plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</td>', '</tr>', '</li>'], "\n", $html));
$plainText = preg_replace('/\n{3,}/', "\n\n", $plainText);

echo "Sending → {$recipient}... ";
$result = sendMail($recipient, $subject, $html, $plainText, 'tyronenorris@gmail.com');

if ($result['success']) {
    echo "✓ Sent\n";
    try { logEmail('admin_invite', "Admin login invite sent to {$recipient}"); } catch (\Throwable $e) {}
} else {
    echo "✗ Failed: {$result['error']}\n";
    exit(1);
}

// ═════════════════════════════════════════════════════════════════════════════

function buildInviteEmail(string $baseUrl): string
{
    $logoUrl  = $baseUrl . '/assets/logo.png';
    $loginUrl = $baseUrl . '/members';
    $adminUrl = $baseUrl . '/admin';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oregon Tires — Admin Access</title>
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
            <img src="{$logoUrl}" alt="Oregon Tires Auto Care" width="140" style="display:block;max-width:140px;height:auto;margin-bottom:16px;">
            <p style="color:#f5d78e;font-size:14px;margin:0;letter-spacing:1px;font-weight:600;">PANEL DE ADMINISTRACIÓN</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- SPANISH SECTION -->
  <tr>
    <td style="padding:36px 36px 0;">
      <div style="height:3px;background:linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%);border-radius:2px;margin-bottom:20px;"></div>
      <h2 style="color:#15803d;font-size:20px;margin:0 0 16px;">🇲🇽 Hola Damian,</h2>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">Tu cuenta de administrador en <strong>Oregon Tires Auto Care</strong> está lista. Puedes acceder al panel de control para administrar citas, clientes, órdenes de reparación, y todo tu negocio.</p>

      <p style="color:#15803d;font-size:16px;font-weight:700;margin:0 0 12px;">La forma más fácil de entrar:</p>

      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #dcfce7;margin-bottom:20px;">
        <tr>
          <td style="padding:20px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:8px 0;">
                  <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 4px;">Opción 1: Inicia sesión con Google (Recomendado)</p>
                  <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">Haz clic en el botón de abajo, luego selecciona <strong>"Continuar con Google"</strong> y usa tu cuenta <strong>oregontirespdx@gmail.com</strong>. El sistema te reconoce automáticamente como administrador — no necesitas contraseña.</p>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 0 4px;">
                  <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 4px;">Opción 2: Correo y contraseña</p>
                  <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">Si prefieres, puedes usar tu correo y contraseña. Si no recuerdas tu contraseña, haz clic en <strong>"¿Olvidaste tu contraseña?"</strong> en la página de inicio de sesión para crear una nueva.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 8px;">Una vez dentro, haz clic en <strong>"Panel de Admin"</strong> en tu menú para ver el panel completo con todas las herramientas del negocio.</p>
    </td>
  </tr>

  <!-- DIVIDER -->
  <tr>
    <td style="padding:24px 36px;">
      <div style="height:1px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);"></div>
    </td>
  </tr>

  <!-- ENGLISH SECTION -->
  <tr>
    <td style="padding:0 36px;">
      <div style="height:3px;background:linear-gradient(90deg,#002868 0%,#002868 33%,#bf0a30 33%,#bf0a30 66%,#002868 66%,#002868 100%);border-radius:2px;margin-bottom:20px;"></div>
      <h2 style="color:#15803d;font-size:20px;margin:0 0 16px;">🇺🇸 Hi Damian,</h2>
      <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">Your admin account at <strong>Oregon Tires Auto Care</strong> is ready. You can access the dashboard to manage appointments, customers, repair orders, and your entire business.</p>

      <p style="color:#15803d;font-size:16px;font-weight:700;margin:0 0 12px;">The easiest way to get in:</p>

      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #dcfce7;margin-bottom:20px;">
        <tr>
          <td style="padding:20px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:8px 0;">
                  <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 4px;">Option 1: Sign in with Google (Recommended)</p>
                  <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">Click the button below, then select <strong>"Continue with Google"</strong> and use your <strong>oregontirespdx@gmail.com</strong> account. The system automatically recognizes you as an admin — no password needed.</p>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 0 4px;">
                  <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 4px;">Option 2: Email and password</p>
                  <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">If you prefer, you can use your email and password. If you don't remember your password, click <strong>"Forgot password?"</strong> on the login page to create a new one.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">Once logged in, click <strong>"Admin Panel"</strong> in your menu to see the full dashboard with all business tools.</p>
    </td>
  </tr>

  <!-- CTA BUTTONS -->
  <tr>
    <td align="center" style="padding:32px 36px 12px;">
      <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
          <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:8px;">
            <a href="{$loginUrl}" target="_blank" style="display:inline-block;padding:14px 36px;color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;letter-spacing:0.5px;">Iniciar Sesión / Sign In</a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:0 36px 8px;">
      <p style="color:#6b7280;font-size:12px;margin:0;">
        <a href="{$adminUrl}" style="color:#15803d;text-decoration:underline;">Ir directo al panel de admin / Go directly to admin panel</a>
      </p>
    </td>
  </tr>

  <!-- FALLBACK URL -->
  <tr>
    <td style="padding:8px 36px 28px;">
      <p style="color:#9ca3af;font-size:12px;line-height:1.5;margin:0;">
        Si el botón no funciona, copia y pega este enlace en tu navegador / If the button doesn't work, copy and paste this link:<br>
        <a href="{$loginUrl}" style="color:#15803d;word-break:break-all;font-size:11px;">{$loginUrl}</a>
      </p>
    </td>
  </tr>

  <!-- WHAT YOU CAN DO -->
  <tr>
    <td style="padding:0 36px 28px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#374151;font-size:14px;font-weight:700;margin:0 0 12px;">Lo que puedes hacer desde el panel / What you can do from the dashboard:</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;color:#374151;">
              <tr><td style="padding:3px 0;">📅 Ver y administrar citas / View and manage appointments</td></tr>
              <tr><td style="padding:3px 0;">👥 Administrar clientes y vehículos / Manage customers and vehicles</td></tr>
              <tr><td style="padding:3px 0;">🔧 Crear órdenes de reparación / Create repair orders</td></tr>
              <tr><td style="padding:3px 0;">🔍 Hacer inspecciones digitales / Perform digital inspections</td></tr>
              <tr><td style="padding:3px 0;">📝 Enviar estimados a clientes / Send estimates to customers</td></tr>
              <tr><td style="padding:3px 0;">💬 Mensajes con clientes / Message customers</td></tr>
              <tr><td style="padding:3px 0;">📊 Ver analíticas del negocio / View business analytics</td></tr>
              <tr><td style="padding:3px 0;">📣 Administrar blog, promociones, FAQ / Manage blog, promotions, FAQ</td></tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- SIGNATURE -->
  <tr>
    <td style="padding:0 36px 28px;">
      <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 4px;">Si necesitas ayuda, responde a este correo o llámame.</p>
      <p style="color:#374151;font-size:14px;line-height:1.6;margin:0;">If you need help, reply to this email or call me.</p>
      <p style="color:#111827;font-size:15px;font-weight:700;margin:16px 0 0;">— Tyrone</p>
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
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">(503) 367-9714</p>
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
