<?php
/**
 * Oregon Tires — Send Welcome Email to All Admins
 * Includes admin panel usage instructions (Spanish first, English second)
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
$baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

foreach ($admins as $admin) {
    $email = $admin['email'];
    $name  = $admin['display_name'];
    $role  = $admin['role'];

    echo "--- {$name} <{$email}> ({$role}) ---\n";

    // Generate a password setup token (7-day expiry so they have time)
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    $db->prepare('UPDATE oretir_admins SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?')
       ->execute([$token, $expires, $admin['id']]);

    $setupUrl = $baseUrl . '/admin/setup-password.html?token=' . $token;
    echo "  Setup URL: {$setupUrl}\n";

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
        $mail->addReplyTo('oregontirespdx@gmail.com', 'Oregon Tires Auto Care');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = "Bienvenido al Panel de Admin | Welcome to the Admin Panel — Oregon Tires";
        $mail->Body    = buildWelcomeHtml($name, $role, $loginUrl, $setupUrl);
        $mail->AltBody = buildWelcomeText($name, $role, $loginUrl, $setupUrl);

        $mail->send();
        echo "  Sent!\n\n";
        $sent++;

    } catch (\Throwable $e) {
        echo "  FAILED: " . $e->getMessage() . "\n\n";
        $failed++;
    }
}

echo "--- Done! Sent: {$sent}, Failed: {$failed} ---\n";


// --- Email Templates ---

function buildWelcomeHtml(string $name, string $role, string $loginUrl, string $setupUrl): string
{
    $roleEs = match ($role) {
        'superadmin' => 'Super Administrador',
        'admin'      => 'Administrador',
        default      => 'Staff',
    };
    $roleEn = match ($role) {
        'superadmin' => 'Super Admin',
        'admin'      => 'Admin',
        default      => 'Staff',
    };

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
            <p style="color:#86efac;font-size:13px;margin:0;letter-spacing:2px;text-transform:uppercase;font-weight:600;">Panel de Administraci&oacute;n</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ===================== SPANISH SECTION ===================== -->
  <tr>
    <td style="padding:0;">
      <div style="height:3px;background:linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:32px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">Espa&ntilde;ol</p>
            <h1 style="color:#15803d;font-size:24px;margin:0 0 8px;font-weight:800;">&iexcl;Bienvenido/a, {$name}!</h1>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">
              El <strong style="color:#15803d;">Panel de Administraci&oacute;n de Oregon Tires</strong> est&aacute; listo para ti. Desde aqu&iacute; puedes manejar todo el negocio del taller.
            </p>
            <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 20px;">
              Tu rol: <strong>{$roleEs}</strong>
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 28px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;box-shadow:0 4px 14px rgba(21,128,61,0.35);">
                  <a href="{$loginUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    Iniciar Sesi&oacute;n
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- SPANISH: PASSWORD SETUP -->
  <tr>
    <td style="padding:0 36px 24px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 12px;">C&oacute;mo Empezar</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;line-height:1.7;">
              <tr><td style="padding:3px 0;"><strong>1.</strong> Haz clic en el bot&oacute;n de abajo para <strong>crear tu contrase&ntilde;a</strong></td></tr>
              <tr><td style="padding:3px 0;"><strong>2.</strong> Elige una contrase&ntilde;a segura (m&iacute;nimo 8 caracteres, con may&uacute;scula, min&uacute;scula y n&uacute;mero)</td></tr>
              <tr><td style="padding:3px 0;"><strong>3.</strong> Despu&eacute;s, inicia sesi&oacute;n en <a href="{$loginUrl}" style="color:#15803d;font-weight:600;">{$loginUrl}</a></td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:8px 24px 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#d97706,#b45309);border-radius:12px;box-shadow:0 4px 14px rgba(217,119,6,0.35);">
                  <a href="{$setupUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    Crear Mi Contrase&ntilde;a
                  </a>
                </td>
              </tr>
            </table>
            <p style="color:#9ca3af;font-size:11px;margin:10px 0 0;">Este enlace expira en 7 d&iacute;as</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- SPANISH: TABS GUIDE -->
  <tr>
    <td style="padding:0 36px 24px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 14px;">Gu&iacute;a de Pesta&ntilde;as del Panel</p>
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="font-size:13px;color:#374151;line-height:1.6;">
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top" width="36">&#128202;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Dashboard</strong> &mdash; Resumen general: citas de hoy, mensajes nuevos, &oacute;rdenes de reparaci&oacute;n activas y estad&iacute;sticas del taller.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128197;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Citas</strong> &mdash; Ver, crear y editar citas. Puedes ver los detalles del cliente y veh&iacute;culo. Las citas nuevas del sitio web aparecen aqu&iacute; autom&aacute;ticamente.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128101;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Clientes</strong> &mdash; Buscar clientes por nombre, correo o tel&eacute;fono. Ver historial de veh&iacute;culos, citas anteriores y &oacute;rdenes de reparaci&oacute;n.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128295;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>&Oacute;rdenes de Reparaci&oacute;n (RO)</strong> &mdash; El coraz&oacute;n del taller. Crea una orden desde una cita o como walk-in. Cambia el estado arrastrando en el tablero Kanban. Incluye inspecciones digitales (DVI) con fotos y presupuestos que el cliente puede aprobar desde su tel&eacute;fono.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128172;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Mensajes</strong> &mdash; Todos los mensajes del formulario de contacto del sitio web. Puedes marcarlos como le&iacute;dos o respondidos.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128188;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Empleados</strong> &mdash; Administrar t&eacute;cnicos y personal. Asignar horarios y roles.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128240;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Blog</strong> &mdash; Publicar art&iacute;culos sobre mantenimiento de llantas, ofertas y consejos para clientes.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#11088;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Rese&ntilde;as</strong> &mdash; Ver y gestionar rese&ntilde;as de clientes. Env&iacute;a solicitudes de rese&ntilde;a autom&aacute;ticas despu&eacute;s de completar un servicio.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#127912;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Galer&iacute;a</strong> &mdash; Subir fotos de trabajos realizados y promociones para mostrar en el sitio web.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;" valign="top">&#9881;</td>
                <td style="padding:6px 0;" valign="top"><strong>Configuraci&oacute;n</strong> &mdash; Editar informaci&oacute;n del negocio, horarios, plantillas de correo electr&oacute;nico y otros ajustes del sitio.</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- SPANISH: RO WORKFLOW -->
  <tr>
    <td style="padding:0 36px 24px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb;border-radius:12px;border:1px solid #fde68a;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#92400e;font-size:15px;font-weight:700;margin:0 0 12px;">Flujo de &Oacute;rdenes de Reparaci&oacute;n</p>
            <p style="color:#78350f;font-size:13px;line-height:1.7;margin:0 0 10px;">
              Cada trabajo sigue estos pasos:
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#78350f;line-height:1.7;">
              <tr><td style="padding:2px 0;"><strong>1.</strong> <strong>Recepci&oacute;n</strong> &mdash; El veh&iacute;culo llega (desde cita o walk-in)</td></tr>
              <tr><td style="padding:2px 0;"><strong>2.</strong> <strong>Diagn&oacute;stico</strong> &mdash; Inspecci&oacute;n del veh&iacute;culo con fotos (verde/amarillo/rojo)</td></tr>
              <tr><td style="padding:2px 0;"><strong>3.</strong> <strong>Presupuesto</strong> &mdash; Se genera y env&iacute;a al cliente para aprobaci&oacute;n</td></tr>
              <tr><td style="padding:2px 0;"><strong>4.</strong> <strong>En Progreso</strong> &mdash; Trabajo aprobado, el t&eacute;cnico trabaja</td></tr>
              <tr><td style="padding:2px 0;"><strong>5.</strong> <strong>Listo</strong> &mdash; Se notifica al cliente que puede recoger</td></tr>
              <tr><td style="padding:2px 0;"><strong>6.</strong> <strong>Completado</strong> &mdash; Cliente recogi&oacute;, se factura</td></tr>
            </table>
            <p style="color:#78350f;font-size:12px;line-height:1.5;margin:10px 0 0;font-style:italic;">
              Puedes arrastrar las tarjetas en el tablero Kanban para cambiar el estado r&aacute;pidamente.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- SPANISH: TIPS -->
  <tr>
    <td style="padding:0 36px 28px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border-radius:12px;border:1px solid #bfdbfe;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#1e40af;font-size:15px;font-weight:700;margin:0 0 12px;">Consejos R&aacute;pidos</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#1e3a5f;line-height:1.7;">
              <tr><td style="padding:3px 0;">&#8226; Usa la <strong>b&uacute;squeda</strong> en la pesta&ntilde;a de Clientes para encontrar r&aacute;pidamente a cualquier persona</td></tr>
              <tr><td style="padding:3px 0;">&#8226; El <strong>decodificador VIN</strong> llena autom&aacute;ticamente los datos del veh&iacute;culo &mdash; solo ingresa el VIN</td></tr>
              <tr><td style="padding:3px 0;">&#8226; Las <strong>inspecciones con fotos</strong> generan confianza &mdash; toma fotos de los problemas encontrados</td></tr>
              <tr><td style="padding:3px 0;">&#8226; Los clientes reciben los presupuestos por correo y pueden <strong>aprobar desde su celular</strong></td></tr>
              <tr><td style="padding:3px 0;">&#8226; El panel funciona en celular y tableta &mdash; <strong>puedes usarlo desde el taller</strong></td></tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ===================== DIVIDER ===================== -->
  <tr>
    <td style="padding:0 36px;">
      <div style="height:2px;background:linear-gradient(90deg,transparent,#d1d5db,transparent);margin:8px 0;"></div>
    </td>
  </tr>

  <!-- ===================== ENGLISH SECTION ===================== -->
  <tr>
    <td style="padding:0;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:28px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">English</p>
            <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;font-weight:800;">Welcome, {$name}!</h2>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 16px;">
              The <strong style="color:#15803d;">Oregon Tires Admin Panel</strong> is ready for you. From here you can manage every aspect of the shop.
            </p>
            <p style="color:#374151;font-size:14px;line-height:1.6;margin:0 0 20px;">
              Your role: <strong>{$roleEn}</strong>
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:0 36px 28px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:12px;box-shadow:0 4px 14px rgba(21,128,61,0.35);">
                  <a href="{$loginUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    Log In to Admin Panel
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ENGLISH: PASSWORD SETUP -->
  <tr>
    <td style="padding:0 36px 24px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 12px;">How to Get Started</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;line-height:1.7;">
              <tr><td style="padding:3px 0;"><strong>1.</strong> Click the button below to <strong>create your password</strong></td></tr>
              <tr><td style="padding:3px 0;"><strong>2.</strong> Choose a secure password (min 8 characters, with uppercase, lowercase, and a number)</td></tr>
              <tr><td style="padding:3px 0;"><strong>3.</strong> Then log in at <a href="{$loginUrl}" style="color:#15803d;font-weight:600;">{$loginUrl}</a></td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:8px 24px 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:linear-gradient(135deg,#d97706,#b45309);border-radius:12px;box-shadow:0 4px 14px rgba(217,119,6,0.35);">
                  <a href="{$setupUrl}" target="_blank" style="display:inline-block;padding:16px 40px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;letter-spacing:0.5px;">
                    Create My Password
                  </a>
                </td>
              </tr>
            </table>
            <p style="color:#9ca3af;font-size:11px;margin:10px 0 0;">This link expires in 7 days</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ENGLISH: TABS GUIDE -->
  <tr>
    <td style="padding:0 36px 24px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#15803d;font-size:15px;font-weight:700;margin:0 0 14px;">Admin Panel Tab Guide</p>
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="font-size:13px;color:#374151;line-height:1.6;">
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top" width="36">&#128202;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Dashboard</strong> &mdash; Overview of today's appointments, new messages, active repair orders, and shop stats.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128197;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Appointments</strong> &mdash; View, create, and edit bookings. See customer and vehicle details. New website bookings appear here automatically.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128101;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Customers</strong> &mdash; Search by name, email, or phone. View vehicle history, past appointments, and repair orders.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128295;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Repair Orders (RO)</strong> &mdash; The heart of the shop. Create from an appointment or as a walk-in. Drag cards on the Kanban board to change status. Includes digital inspections (DVI) with photos and estimates that customers can approve from their phone.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128172;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Messages</strong> &mdash; All contact form messages from the website. Mark as read or responded.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128188;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Employees</strong> &mdash; Manage technicians and staff. Assign schedules and roles.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#128240;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Blog</strong> &mdash; Post articles about tire maintenance, promotions, and tips for customers.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#11088;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Feedback</strong> &mdash; View and manage customer reviews. Automatic review request emails are sent after service completion.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top">&#127912;</td>
                <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;" valign="top"><strong>Gallery</strong> &mdash; Upload photos of completed work and promotions to display on the website.</td>
              </tr>
              <tr>
                <td style="padding:6px 0;" valign="top">&#9881;</td>
                <td style="padding:6px 0;" valign="top"><strong>Settings</strong> &mdash; Edit business info, hours, email templates, and other site settings.</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ENGLISH: RO WORKFLOW -->
  <tr>
    <td style="padding:0 36px 24px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb;border-radius:12px;border:1px solid #fde68a;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#92400e;font-size:15px;font-weight:700;margin:0 0 12px;">Repair Order Workflow</p>
            <p style="color:#78350f;font-size:13px;line-height:1.7;margin:0 0 10px;">
              Every job follows these steps:
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#78350f;line-height:1.7;">
              <tr><td style="padding:2px 0;"><strong>1.</strong> <strong>Intake</strong> &mdash; Vehicle arrives (from appointment or walk-in)</td></tr>
              <tr><td style="padding:2px 0;"><strong>2.</strong> <strong>Diagnosis</strong> &mdash; Inspect the vehicle with photos (green/yellow/red)</td></tr>
              <tr><td style="padding:2px 0;"><strong>3.</strong> <strong>Estimate</strong> &mdash; Generate and send to customer for approval</td></tr>
              <tr><td style="padding:2px 0;"><strong>4.</strong> <strong>In Progress</strong> &mdash; Approved work, technician working</td></tr>
              <tr><td style="padding:2px 0;"><strong>5.</strong> <strong>Ready</strong> &mdash; Customer notified for pickup</td></tr>
              <tr><td style="padding:2px 0;"><strong>6.</strong> <strong>Completed</strong> &mdash; Customer picked up, invoice generated</td></tr>
            </table>
            <p style="color:#78350f;font-size:12px;line-height:1.5;margin:10px 0 0;font-style:italic;">
              You can drag cards on the Kanban board to quickly change status.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ENGLISH: TIPS -->
  <tr>
    <td style="padding:0 36px 28px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border-radius:12px;border:1px solid #bfdbfe;">
        <tr>
          <td style="padding:20px 24px;">
            <p style="color:#1e40af;font-size:15px;font-weight:700;margin:0 0 12px;">Quick Tips</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;color:#1e3a5f;line-height:1.7;">
              <tr><td style="padding:3px 0;">&#8226; Use the <strong>search</strong> in the Customers tab to quickly find anyone</td></tr>
              <tr><td style="padding:3px 0;">&#8226; The <strong>VIN decoder</strong> auto-fills vehicle data &mdash; just enter the VIN</td></tr>
              <tr><td style="padding:3px 0;">&#8226; <strong>Photo inspections</strong> build trust &mdash; take photos of any issues found</td></tr>
              <tr><td style="padding:3px 0;">&#8226; Customers receive estimates by email and can <strong>approve from their phone</strong></td></tr>
              <tr><td style="padding:3px 0;">&#8226; The panel works on phone and tablet &mdash; <strong>use it right from the shop floor</strong></td></tr>
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
            <p style="color:#9ca3af;font-size:12px;margin:0 0 4px;">(503) 367-9714</p>
            <p style="color:#9ca3af;font-size:12px;margin:0;">Lunes&ndash;S&aacute;bado 7:00 AM &ndash; 7:00 PM</p>
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

function buildWelcomeText(string $name, string $role, string $loginUrl, string $setupUrl): string
{
    $roleEs = match ($role) {
        'superadmin' => 'Super Administrador',
        'admin'      => 'Administrador',
        default      => 'Staff',
    };
    $roleEn = match ($role) {
        'superadmin' => 'Super Admin',
        'admin'      => 'Admin',
        default      => 'Staff',
    };

    return <<<TEXT
=======================================
OREGON TIRES AUTO CARE
Panel de Administracion
=======================================

--- ESPANOL ---

Bienvenido/a, {$name}!

El Panel de Administracion de Oregon Tires esta listo para ti.
Tu rol: {$roleEs}

COMO EMPEZAR:
1. Haz clic en el enlace de abajo para CREAR TU CONTRASENA
2. Elige una contrasena segura (minimo 8 caracteres, con mayuscula, minuscula y numero)
3. Despues, inicia sesion en {$loginUrl}

CREAR MI CONTRASENA: {$setupUrl}
(Este enlace expira en 7 dias)

GUIA DE PESTANAS:
- Dashboard — Resumen general: citas de hoy, mensajes, ordenes activas, estadisticas
- Citas — Ver, crear y editar citas. Las citas del sitio web aparecen automaticamente
- Clientes — Buscar por nombre, correo o telefono. Ver historial de vehiculos y reparaciones
- Ordenes de Reparacion (RO) — Crear desde cita o walk-in. Tablero Kanban con arrastrar y soltar. Inspecciones digitales con fotos. Presupuestos que el cliente aprueba desde su celular
- Mensajes — Mensajes del formulario de contacto del sitio web
- Empleados — Administrar tecnicos y personal, horarios y roles
- Blog — Publicar articulos sobre llantas, ofertas y consejos
- Resenas — Ver resenas de clientes. Solicitudes automaticas despues del servicio
- Galeria — Subir fotos de trabajos y promociones
- Configuracion — Informacion del negocio, horarios, plantillas de correo

FLUJO DE ORDENES DE REPARACION:
1. Recepcion — El vehiculo llega
2. Diagnostico — Inspeccion con fotos (verde/amarillo/rojo)
3. Presupuesto — Se envia al cliente para aprobacion
4. En Progreso — Trabajo aprobado, tecnico trabaja
5. Listo — Se notifica al cliente
6. Completado — Cliente recogio, se factura

CONSEJOS:
- Usa la busqueda en Clientes para encontrar rapidamente a cualquier persona
- El decodificador VIN llena automaticamente los datos del vehiculo
- Las inspecciones con fotos generan confianza con los clientes
- Los clientes pueden aprobar presupuestos desde su celular
- El panel funciona en celular y tableta

Iniciar sesion: {$loginUrl}

=======================================

--- ENGLISH ---

Welcome, {$name}!

The Oregon Tires Admin Panel is ready for you.
Your role: {$roleEn}

HOW TO GET STARTED:
1. Click the link below to CREATE YOUR PASSWORD
2. Choose a secure password (min 8 characters, with uppercase, lowercase, and a number)
3. Then log in at {$loginUrl}

CREATE MY PASSWORD: {$setupUrl}
(This link expires in 7 days)

TAB GUIDE:
- Dashboard — Overview: today's appointments, messages, active repair orders, stats
- Appointments — View, create, edit bookings. Website bookings appear automatically
- Customers — Search by name, email, or phone. View vehicle history and repair orders
- Repair Orders (RO) — Create from appointment or walk-in. Kanban board with drag-and-drop. Digital inspections with photos. Estimates customers can approve from their phone
- Messages — Contact form messages from the website
- Employees — Manage technicians and staff, schedules and roles
- Blog — Post articles about tire maintenance, promotions, and tips
- Feedback — Customer reviews. Automatic review requests after service
- Gallery — Upload photos of completed work and promotions
- Settings — Business info, hours, email templates, site settings

REPAIR ORDER WORKFLOW:
1. Intake — Vehicle arrives
2. Diagnosis — Inspect with photos (green/yellow/red)
3. Estimate — Send to customer for approval
4. In Progress — Approved work, technician working
5. Ready — Customer notified for pickup
6. Completed — Customer picked up, invoiced

TIPS:
- Use search in Customers tab to quickly find anyone
- VIN decoder auto-fills vehicle data — just enter the VIN
- Photo inspections build trust with customers
- Customers can approve estimates from their phone
- The panel works on phone and tablet — use it from the shop floor

Log in: {$loginUrl}

=======================================

Oregon Tires Auto Care
8536 SE 82nd Ave, Portland, OR 97266
(503) 367-9714
Mon-Sat 7:00 AM - 7:00 PM
TEXT;
}
