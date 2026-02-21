<?php
declare(strict_types=1);

/**
 * Oregon Tires — Form Kit Config API
 * Thin wrapper around form-kit config endpoint.
 * PUBLIC — frontend needs this to render forms.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

$pdo = getDB();

$formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../../---form-kit';
require_once $formKitPath . '/loader.php';

FormManager::init($pdo, [
    'site_key'        => 'oregon.tires',
    'recipient_email' => $_ENV['CONTACT_EMAIL'] ?? '',
    'subject_prefix'  => '[Oregon Tires]',
    'success_message' => 'Thank you for your message. We will get back to you soon.',
]);

require $formKitPath . '/api/form/config.php';
