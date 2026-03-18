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

    // Assignment Notification — Customer
    ['email_tpl_assignment_customer_subject',  'Your Appointment Has Been Assigned', 'Su Cita Ha Sido Asignada'],
    ['email_tpl_assignment_customer_greeting', 'Hello, {{name}}!', '¡Hola, {{name}}!'],
    ['email_tpl_assignment_customer_body',     'Your <strong>{{service}}</strong> appointment on <strong>{{date}}</strong> at <strong>{{time}}</strong> has been assigned to <strong>{{employee_name}}</strong>.{{task_line}}<br><br>Reference: <strong>{{reference_number}}</strong>', 'Su cita de <strong>{{service}}</strong> el <strong>{{date}}</strong> a las <strong>{{time}}</strong> ha sido asignada a <strong>{{employee_name}}</strong>.{{task_line}}<br><br>Referencia: <strong>{{reference_number}}</strong>'],
    ['email_tpl_assignment_customer_button',   'View Appointment Status', 'Ver Estado de Cita'],
    ['email_tpl_assignment_customer_footer',   'If you have any questions, please call us at <strong>(503) 206-5923</strong> or reply to this email.', 'Si tiene alguna pregunta, llámenos al <strong>(503) 206-5923</strong> o responda a este correo.'],

    // Assignment Notification — Employee
    ['email_tpl_assignment_employee_subject',  'New Assignment: {{service}} on {{date}}', 'Nueva Asignación: {{service}} el {{date}}'],
    ['email_tpl_assignment_employee_greeting', 'Hi {{employee_name}}!', '¡Hola {{employee_name}}!'],
    ['email_tpl_assignment_employee_body',     'You have been assigned a new appointment:<br><br><strong>Customer:</strong> {{name}}<br><strong>Service:</strong> {{service}}<br><strong>Date:</strong> {{date}} at {{time}}<br><strong>Vehicle:</strong> {{vehicle}}{{task_line}}<br><strong>Reference:</strong> {{reference_number}}', 'Se te ha asignado una nueva cita:<br><br><strong>Cliente:</strong> {{name}}<br><strong>Servicio:</strong> {{service}}<br><strong>Fecha:</strong> {{date}} a las {{time}}<br><strong>Vehículo:</strong> {{vehicle}}{{task_line}}<br><strong>Referencia:</strong> {{reference_number}}'],
    ['email_tpl_assignment_employee_button',   'View in Admin', 'Ver en Admin'],
    ['email_tpl_assignment_employee_footer',   'This is an automated assignment notification from Oregon Tires Auto Care.', 'Esta es una notificación automática de asignación de Oregon Tires Auto Care.'],

    // Job Finished / Vehicle Ready Email
    ['email_tpl_job_finished_subject_en', 'Your Vehicle is Ready! — Oregon Tires', '¡Su Vehículo Está Listo! — Oregon Tires'],
    ['email_tpl_job_finished_greeting_en', 'Hi {{name}}!', '¡Hola {{name}}!'],
    ['email_tpl_job_finished_body_en', 'Great news! Your vehicle <strong>{{vehicle}}</strong> (RO: <strong>{{ro_number}}</strong>) is ready for pickup at Oregon Tires Auto Care.<br><br>Thank you for trusting us with your vehicle!', '¡Buenas noticias! Su vehículo <strong>{{vehicle}}</strong> (Orden: <strong>{{ro_number}}</strong>) está listo para recoger en Oregon Tires Auto Care.<br><br>¡Gracias por confiar en nosotros con su vehículo!'],
    ['email_tpl_job_finished_button_en', 'Get Directions', 'Obtener Direcciones'],
    ['email_tpl_job_finished_footer_en', 'If you have any questions, please call us at <strong>(503) 367-9714</strong> or reply to this email.', 'Si tiene alguna pregunta, llámenos al <strong>(503) 367-9714</strong> o responda a este correo.'],

    // Reminder Settings
    ['reminder_lead_hours', '24', '24'],
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
