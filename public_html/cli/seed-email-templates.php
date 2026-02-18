<?php
/**
 * Oregon Tires — Seed Email Templates
 * Run: php cli/seed-email-templates.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();

$templates = [
    // Welcome / Invite Email
    ['email_tpl_welcome_subject',  'Set Up Your Password — Oregon Tires Admin', 'Configura tu Contraseña — Oregon Tires Admin'],
    ['email_tpl_welcome_greeting', 'Welcome, {{name}}!', '¡Bienvenido/a, {{name}}!'],
    ['email_tpl_welcome_body',     'You\'ve been invited to the <strong style="color:#15803d;">Oregon Tires Auto Care Admin Panel</strong> as <strong>{{role}}</strong>. To activate your account, set up your password by clicking the button below.', 'Has sido invitado/a al <strong style="color:#15803d;">Panel de Administración de Oregon Tires Auto Care</strong> como <strong>{{role}}</strong>. Para activar tu cuenta, configura tu contraseña haciendo clic en el botón de abajo.'],
    ['email_tpl_welcome_button',   'Set Up My Password', 'Configurar Mi Contraseña'],
    ['email_tpl_welcome_footer',   'This link expires in <strong>{{expiry_days}} days</strong>. If you didn\'t request this account, you can safely ignore this email.', 'Este enlace expira en <strong>{{expiry_days}} días</strong>. Si no solicitaste esta cuenta, puedes ignorar este correo de forma segura.'],

    // Password Reset Email
    ['email_tpl_reset_subject',    'Reset Your Password — Oregon Tires Admin', 'Restablece tu Contraseña — Oregon Tires Admin'],
    ['email_tpl_reset_greeting',   'Hello, {{name}}', 'Hola, {{name}}'],
    ['email_tpl_reset_body',       'We received a request to reset your password for the <strong style="color:#15803d;">Oregon Tires Auto Care Admin Panel</strong>. Click the button below to choose a new password.', 'Recibimos una solicitud para restablecer tu contraseña del <strong style="color:#15803d;">Panel de Administración de Oregon Tires Auto Care</strong>. Haz clic en el botón de abajo para elegir una nueva contraseña.'],
    ['email_tpl_reset_button',     'Reset My Password', 'Restablecer Mi Contraseña'],
    ['email_tpl_reset_footer',     'This link expires in <strong>1 hour</strong>. If you didn\'t request this, you can safely ignore this email.', 'Este enlace expira en <strong>1 hora</strong>. Si no solicitaste esto, puedes ignorar este correo de forma segura.'],

    // Contact Notification Email
    ['email_tpl_contact_subject',  'New Contact Message from {{name}}', 'Nuevo Mensaje de Contacto de {{name}}'],
    ['email_tpl_contact_greeting', 'New message received', 'Nuevo mensaje recibido'],
    ['email_tpl_contact_body',     'You have a new contact message from <strong>{{name}}</strong> ({{email}}):<br><br><em>"{{message}}"</em>', 'Tienes un nuevo mensaje de contacto de <strong>{{name}}</strong> ({{email}}):<br><br><em>"{{message}}"</em>'],
    ['email_tpl_contact_button',   'View in Admin Panel', 'Ver en el Panel'],
    ['email_tpl_contact_footer',   'This is an automated notification from Oregon Tires Auto Care.', 'Esta es una notificación automática de Oregon Tires Auto Care.'],
];

$stmt = $db->prepare(
    'INSERT INTO oretir_site_settings (setting_key, value_en, value_es)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE value_en = VALUES(value_en), value_es = VALUES(value_es)'
);

echo "\n━━━ Seeding Email Templates ━━━\n\n";

$count = 0;
foreach ($templates as $t) {
    $stmt->execute($t);
    echo "  ✓ {$t[0]}\n";
    $count++;
}

echo "\n  ✅ Seeded {$count} email template rows.\n";

// Verify
$check = $db->query("SELECT COUNT(*) FROM oretir_site_settings WHERE setting_key LIKE 'email_tpl_%'")->fetchColumn();
echo "  📊 Total email_tpl rows in DB: {$check}\n";

echo "\n━━━ Done! ━━━\n\n";
