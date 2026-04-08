<?php
/**
 * Oregon Tires — Send Admin Credentials & Google Connect Instructions
 * Run: php cli/send-admin-credentials.php [--preview] [--send]
 *
 * Sends all active admins a bilingual email with:
 *   - Their login credentials (email + password reset link)
 *   - Instructions to connect their Google account for faster login
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mail.php';

$mode = $argv[1] ?? '--preview';

$baseUrl  = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
$adminUrl = $baseUrl . '/admin/';
$db       = getDB();

// Fetch all active admins
$admins = $db->query('SELECT id, email, display_name, language, setup_completed_at, google_id FROM oretir_admins WHERE is_active = 1 ORDER BY id')
             ->fetchAll();

if (empty($admins)) {
    echo "No active admins found.\n";
    exit;
}

echo "\n━━━ Oregon Tires — Admin Credentials Email ━━━\n";
echo "Found " . count($admins) . " active admin(s)\n\n";

foreach ($admins as $admin) {
    $email    = $admin['email'];
    $name     = $admin['display_name'] ?: explode('@', $email)[0];
    $language = $admin['language'] ?? 'both';
    $hasGoogle = !empty($admin['google_id']);
    $hasSetup  = !empty($admin['setup_completed_at']);

    echo "━━━ {$name} <{$email}> ━━━\n";
    echo "  Language: {$language} | Password set: " . ($hasSetup ? 'Yes' : 'No') . " | Google linked: " . ($hasGoogle ? 'Yes' : 'No') . "\n";

    // Generate a fresh setup/reset token (7-day expiry)
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

    $db->prepare(
        'UPDATE oretir_admins SET password_reset_token = ?, password_reset_expires = ?, updated_at = NOW() WHERE id = ?'
    )->execute([$token, $expires, $admin['id']]);

    $setupUrl     = $baseUrl . '/admin/setup-password.html?token=' . $token;
    $googleUrl    = $adminUrl; // They'll see "Sign in with Google" button on the login page

    echo "  Setup URL: {$setupUrl}\n";

    if ($mode === '--preview') {
        echo "  [PREVIEW] Would send email to {$email}\n\n";
        continue;
    }

    // Build and send the email
    $htmlBody = buildCredentialsEmail($name, $email, $setupUrl, $googleUrl, $baseUrl, $hasSetup, $hasGoogle);
    $textBody = buildCredentialsTextEmail($name, $email, $setupUrl, $googleUrl, $hasSetup, $hasGoogle);

    $subject = '🔑 Tus Credenciales de Admin — Oregon Tires | Your Admin Credentials';
    $result  = sendMail($email, $subject, $htmlBody, $textBody);

    if ($result['success']) {
        echo "  ✅ Email sent!\n";
        logEmail('admin_credentials', "Credentials email sent to {$email}", $email, $email, $subject);
    } else {
        echo "  ❌ FAILED: {$result['error']}\n";
    }
    echo "\n";
}

echo "━━━ Done! ━━━\n\n";

if ($mode === '--preview') {
    echo "Run with --send to actually send the emails.\n\n";
}

// ─── Email Template ─────────────────────────────────────────────────────────

function buildCredentialsEmail(string $name, string $email, string $setupUrl, string $googleUrl, string $baseUrl, bool $hasSetup, bool $hasGoogle): string
{
    $pwAction = $hasSetup ? 'restablecer' : 'configurar';
    $pwActionEn = $hasSetup ? 'reset' : 'set up';
    $pwBtnEs = $hasSetup ? '🔐 Restablecer Contraseña' : '🔐 Configurar Mi Contraseña';
    $pwBtnEn = $hasSetup ? '🔐 Reset Password' : '🔐 Set Up My Password';

    $googleStatusEs = $hasGoogle
        ? '<span style="color:#15803d;font-weight:700;">✓ Ya conectada</span> — puedes iniciar sesión con Google directamente.'
        : '<span style="color:#d97706;font-weight:700;">⚡ No conectada aún</span> — sigue las instrucciones abajo para conectarla.';
    $googleStatusEn = $hasGoogle
        ? '<span style="color:#15803d;font-weight:700;">✓ Already connected</span> — you can sign in with Google right away.'
        : '<span style="color:#d97706;font-weight:700;">⚡ Not connected yet</span> — follow the instructions below to connect it.';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oregon Tires — Admin Credentials</title>
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

  <!-- ═══ SPANISH SECTION ═══ -->
  <tr>
    <td style="padding:0;">
      <div style="height:3px;background:linear-gradient(90deg,#c60b1e 0%,#c60b1e 33%,#ffc400 33%,#ffc400 66%,#c60b1e 66%,#c60b1e 100%);"></div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:32px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">🇲🇽 Español</p>
            <h1 style="color:#15803d;font-size:24px;margin:0 0 8px;font-weight:800;">¡Hola, {$name}!</h1>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              Aquí están tus credenciales para acceder al <strong style="color:#15803d;">Panel de Administración de Oregon Tires</strong>.
            </p>
          </td>
        </tr>

        <!-- Credentials Box ES -->
        <tr>
          <td style="padding:0 36px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;">
              <tr>
                <td style="padding:20px 24px;">
                  <p style="color:#15803d;font-size:14px;font-weight:700;margin:0 0 12px;">📋 Tus Credenciales</p>
                  <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;width:100%;">
                    <tr>
                      <td style="padding:4px 0;font-weight:600;width:80px;">Email:</td>
                      <td style="padding:4px 0;font-family:monospace;color:#15803d;">{$email}</td>
                    </tr>
                    <tr>
                      <td style="padding:4px 0;font-weight:600;">URL:</td>
                      <td style="padding:4px 0;"><a href="{$googleUrl}" style="color:#15803d;text-decoration:underline;">{$googleUrl}</a></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Option 1: Google Login ES -->
        <tr>
          <td style="padding:0 36px 16px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border-radius:12px;border:1px solid #bfdbfe;">
              <tr>
                <td style="padding:20px 24px;">
                  <p style="color:#1d4ed8;font-size:14px;font-weight:700;margin:0 0 8px;">⚡ Opción 1: Inicio de Sesión con Google (Recomendado)</p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0 0 8px;">
                    La forma más rápida de acceder al panel. Un solo clic, sin necesidad de recordar contraseñas.
                  </p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0 0 8px;">
                    <strong>Estado de tu Google:</strong> {$googleStatusEs}
                  </p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0;">
                    <strong>Para conectar Google:</strong><br>
                    1. Ve a <a href="{$googleUrl}" style="color:#1d4ed8;">{$googleUrl}</a><br>
                    2. Haz clic en <strong>"Iniciar sesión con Google"</strong><br>
                    3. Selecciona tu cuenta de Google (<strong>{$email}</strong>)<br>
                    4. ¡Listo! La próxima vez, solo haz clic en el botón de Google.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Option 2: Password ES -->
        <tr>
          <td style="padding:0 36px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
              <tr>
                <td style="padding:20px 24px;">
                  <p style="color:#6b7280;font-size:14px;font-weight:700;margin:0 0 8px;">🔐 Opción 2: Contraseña</p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0 0 12px;">
                    También puedes {$pwAction} una contraseña para iniciar sesión de la manera tradicional.
                  </p>
                  <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:10px;">
                        <a href="{$setupUrl}" target="_blank" style="display:inline-block;padding:12px 28px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
                          {$pwBtnEs}
                        </a>
                      </td>
                    </tr>
                  </table>
                  <p style="color:#9ca3af;font-size:12px;margin:8px 0 0;">Este enlace expira en 7 días.</p>
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

  <!-- ═══ ENGLISH SECTION ═══ -->
  <tr>
    <td style="padding:0;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:28px 36px 8px;">
            <p style="color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 12px;font-weight:700;">🇺🇸 English</p>
            <h2 style="color:#15803d;font-size:22px;margin:0 0 8px;font-weight:800;">Hello, {$name}!</h2>
            <p style="color:#374151;font-size:15px;line-height:1.7;margin:0 0 20px;">
              Here are your credentials to access the <strong style="color:#15803d;">Oregon Tires Admin Panel</strong>.
            </p>
          </td>
        </tr>

        <!-- Credentials Box EN -->
        <tr>
          <td style="padding:0 36px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;">
              <tr>
                <td style="padding:20px 24px;">
                  <p style="color:#15803d;font-size:14px;font-weight:700;margin:0 0 12px;">📋 Your Credentials</p>
                  <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;width:100%;">
                    <tr>
                      <td style="padding:4px 0;font-weight:600;width:80px;">Email:</td>
                      <td style="padding:4px 0;font-family:monospace;color:#15803d;">{$email}</td>
                    </tr>
                    <tr>
                      <td style="padding:4px 0;font-weight:600;">URL:</td>
                      <td style="padding:4px 0;"><a href="{$googleUrl}" style="color:#15803d;text-decoration:underline;">{$googleUrl}</a></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Option 1: Google Login EN -->
        <tr>
          <td style="padding:0 36px 16px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border-radius:12px;border:1px solid #bfdbfe;">
              <tr>
                <td style="padding:20px 24px;">
                  <p style="color:#1d4ed8;font-size:14px;font-weight:700;margin:0 0 8px;">⚡ Option 1: Sign in with Google (Recommended)</p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0 0 8px;">
                    The fastest way to access the panel. One click, no passwords to remember.
                  </p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0 0 8px;">
                    <strong>Your Google status:</strong> {$googleStatusEn}
                  </p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0;">
                    <strong>To connect Google:</strong><br>
                    1. Go to <a href="{$googleUrl}" style="color:#1d4ed8;">{$googleUrl}</a><br>
                    2. Click <strong>"Sign in with Google"</strong><br>
                    3. Choose your Google account (<strong>{$email}</strong>)<br>
                    4. Done! Next time, just click the Google button.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Option 2: Password EN -->
        <tr>
          <td style="padding:0 36px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
              <tr>
                <td style="padding:20px 24px;">
                  <p style="color:#6b7280;font-size:14px;font-weight:700;margin:0 0 8px;">🔐 Option 2: Password</p>
                  <p style="color:#374151;font-size:13px;line-height:1.6;margin:0 0 12px;">
                    You can also {$pwActionEn} a password to sign in the traditional way.
                  </p>
                  <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="background:linear-gradient(135deg,#15803d,#166534);border-radius:10px;">
                        <a href="{$setupUrl}" target="_blank" style="display:inline-block;padding:12px 28px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
                          {$pwBtnEn}
                        </a>
                      </td>
                    </tr>
                  </table>
                  <p style="color:#9ca3af;font-size:12px;margin:8px 0 0;">This link expires in 7 days.</p>
                </td>
              </tr>
            </table>
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
            <p style="color:#374151;font-size:13px;font-weight:700;margin:0 0 10px;">📋 Requisitos de Contraseña / Password Requirements:</p>
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
        Si los botones no funcionan / If the buttons don't work:<br>
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

function buildCredentialsTextEmail(string $name, string $email, string $setupUrl, string $googleUrl, bool $hasSetup, bool $hasGoogle): string
{
    $pwActionEs = $hasSetup ? 'restablecer' : 'configurar';
    $pwActionEn = $hasSetup ? 'reset' : 'set up';
    $googleStatusEs = $hasGoogle ? '✓ Ya conectada' : '⚡ No conectada aún';
    $googleStatusEn = $hasGoogle ? '✓ Already connected' : '⚡ Not connected yet';

    return <<<TEXT
═══════════════════════════════════════
OREGON TIRES AUTO CARE — Panel de Administración
═══════════════════════════════════════

🇲🇽 ESPAÑOL
────────────────────

¡Hola, {$name}!

Aquí están tus credenciales para el Panel de Administración de Oregon Tires.

📋 TUS CREDENCIALES
  Email: {$email}
  URL:   {$googleUrl}

⚡ OPCIÓN 1: INICIO DE SESIÓN CON GOOGLE (Recomendado)
  Estado: {$googleStatusEs}

  Para conectar tu cuenta de Google:
  1. Ve a {$googleUrl}
  2. Haz clic en "Iniciar sesión con Google"
  3. Selecciona tu cuenta ({$email})
  4. ¡Listo! La próxima vez, solo haz clic en el botón de Google.

🔐 OPCIÓN 2: CONTRASEÑA
  Puedes {$pwActionEs} tu contraseña aquí:
  {$setupUrl}

  Este enlace expira en 7 días.

  Requisitos:
  ✓ Mínimo 8 caracteres
  ✓ Una letra mayúscula
  ✓ Una letra minúscula
  ✓ Un número

═══════════════════════════════════════

🇺🇸 ENGLISH
────────────────────

Hello, {$name}!

Here are your credentials for the Oregon Tires Admin Panel.

📋 YOUR CREDENTIALS
  Email: {$email}
  URL:   {$googleUrl}

⚡ OPTION 1: SIGN IN WITH GOOGLE (Recommended)
  Status: {$googleStatusEn}

  To connect your Google account:
  1. Go to {$googleUrl}
  2. Click "Sign in with Google"
  3. Choose your account ({$email})
  4. Done! Next time, just click the Google button.

🔐 OPTION 2: PASSWORD
  You can {$pwActionEn} your password here:
  {$setupUrl}

  This link expires in 7 days.

  Requirements:
  ✓ Minimum 8 characters
  ✓ One uppercase letter
  ✓ One lowercase letter
  ✓ One number

═══════════════════════════════════════

Oregon Tires Auto Care
8536 SE 82nd Ave, Portland, OR 97266
📞 (503) 367-9714
Lunes–Sábado 7:00 AM – 7:00 PM
TEXT;
}
