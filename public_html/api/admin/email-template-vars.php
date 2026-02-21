<?php
/**
 * Oregon Tires — Email Template Variable Documentation
 * GET /api/admin/email-template-vars.php
 *
 * Admin-only endpoint returning available template variables per template type.
 * Helps admins edit email templates in Site Settings knowing which {{variables}} are available.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET');

    $admin = requireAdmin();

    jsonSuccess([
        'templates' => [
            'booking' => [
                'description' => 'Appointment confirmation email sent to the customer after booking',
                'variables' => [
                    'name'             => 'Customer full name (e.g., "John Smith")',
                    'service'          => 'Service type display name (e.g., "Tire Installation")',
                    'date'             => 'Appointment date, formatted for display (e.g., "03/15/2026" or "15/03/2026")',
                    'time'             => 'Appointment time (e.g., "2:00 PM")',
                    'vehicle_line'     => 'Vehicle info HTML line with label (e.g., "<br><strong>Vehicle:</strong> 2024 Toyota Camry"). Empty if no vehicle provided.',
                    'reference_line'   => 'Reference number HTML line with label (e.g., "<br><strong>Reference:</strong> OT-ABCD1234")',
                    'reference_number' => 'Booking reference code (e.g., "OT-ABCD1234")',
                    'email'            => 'Customer email address',
                ],
                'notes' => 'This template is bilingual. Fields are stored as subject_en, subject_es, greeting_en, greeting_es, body_en, body_es, button_en, button_es, footer_en, footer_es. Vehicle and reference lines are auto-localized (Vehicle/Vehiculo, Reference/Referencia).',
            ],
            'booking_owner' => [
                'description' => 'New appointment notification sent to the shop owner',
                'variables' => [
                    'appointment_id'   => 'Internal appointment ID number',
                    'reference_number' => 'Booking reference code (e.g., "OT-ABCD1234")',
                    'service'          => 'Service type display name (e.g., "Tire Installation")',
                    'date'             => 'Appointment date (YYYY-MM-DD format)',
                    'time'             => 'Appointment time (e.g., "2:00 PM")',
                    'name'             => 'Customer full name',
                    'email'            => 'Customer email address',
                    'phone'            => 'Customer phone number',
                    'vehicle'          => 'Vehicle info or "N/A" if not provided',
                    'language'         => 'Customer language preference ("english" or "spanish")',
                    'notes'            => 'Customer notes or "None"',
                ],
            ],
            'reminder' => [
                'description' => 'Appointment reminder sent the day before (via cron job)',
                'variables' => [
                    'name'             => 'Customer full name',
                    'service'          => 'Service type display name',
                    'date'             => 'Appointment date, formatted for display',
                    'time'             => 'Appointment time (e.g., "2:00 PM")',
                    'reference_number' => 'Booking reference code',
                    'email'            => 'Customer email address',
                ],
                'notes' => 'Bilingual template. Button links to Google Maps for the shop location.',
            ],
            'contact' => [
                'description' => 'Contact form notification sent to the shop owner',
                'variables' => [
                    'name'    => 'Sender full name',
                    'email'   => 'Sender email address',
                    'message' => 'Message content from the contact form',
                ],
                'notes' => 'Bilingual template. Button links to admin dashboard.',
            ],
            'welcome' => [
                'description' => 'Admin account setup invitation email',
                'variables' => [
                    'name'        => 'Admin display name',
                    'setup_url'   => 'Full setup URL with token',
                    'role'        => 'Admin role label (e.g., "Admin")',
                    'expiry_days' => 'Number of days until setup link expires (default: 7)',
                    'email'       => 'Admin email address',
                ],
                'notes' => 'Bilingual template. Shows password requirements box. Button links to setup URL.',
            ],
            'reset' => [
                'description' => 'Password reset email for admin accounts',
                'variables' => [
                    'name'      => 'Admin display name',
                    'setup_url' => 'Full password reset URL with token',
                    'email'     => 'Admin email address',
                ],
                'notes' => 'Bilingual template. Shows password requirements box. Button links to reset URL.',
            ],
        ],
        'syntax' => [
            'usage'   => 'Wrap variable names in double curly braces: {{variable_name}}',
            'example' => 'Hello {{name}}, your appointment for {{service}} on {{date}} at {{time}} has been confirmed.',
            'html'    => 'Variables in body fields support HTML. Variables are NOT escaped in template rendering (raw replacement).',
        ],
    ]);

} catch (\Throwable $e) {
    error_log('email-template-vars.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
