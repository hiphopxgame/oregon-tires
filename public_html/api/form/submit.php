<?php
declare(strict_types=1);

/**
 * Oregon Tires — Form Kit Submit API
 * Thin wrapper around form-kit submit endpoint.
 * PUBLIC — no admin auth required.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

$pdo = getDB();

$formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../../---form-kit';
require_once $formKitPath . '/loader.php';

FormManager::init($pdo, [
    'site_key'        => 'oregon.tires',
    'recipient_email' => $_ENV['CONTACT_EMAIL'] ?? '',
    'subject_prefix'  => '[Oregon Tires]',
    'mail_from'       => $_ENV['SMTP_FROM'] ?? '',
    'mail_from_name'  => $_ENV['SMTP_FROM_NAME'] ?? 'Oregon Tires Auto Care',
    'mail_helper_path' => __DIR__ . '/../../includes/mail.php',
    'rate_limit_max'  => 5,
    'rate_limit_window' => 3600,
    'success_message' => 'Thank you for your message. We will get back to you soon.',
]);

// Register Google Calendar action (creates event on contact form submission)
if (!empty($_ENV['GOOGLE_CALENDAR_CREDENTIALS'])) {
    require_once $formKitPath . '/actions/google-calendar.php';
    GoogleCalendarAction::register([
        'credentials_path' => $_ENV['GOOGLE_CALENDAR_CREDENTIALS'],
        'calendar_id'      => $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary',
        'send_invites'     => true,
        'timezone'         => 'America/Los_Angeles',
        'default_duration' => 30,
        'event_title'      => 'Oregon Tires Contact: {name}',
    ]);
}

require $formKitPath . '/api/form/submit.php';
