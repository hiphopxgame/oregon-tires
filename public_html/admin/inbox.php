<?php
declare(strict_types=1);

/**
 * Oregon Tires — Admin Contact Inbox
 * Uses Form Kit admin-inbox template.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Require admin session
startSession();
if (empty($_SESSION['admin_id'])) {
    http_response_code(302);
    header('Location: /admin/');
    exit;
}

$pdo = getDB();

$formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../../---form-kit';
require_once $formKitPath . '/loader.php';

FormManager::init($pdo, [
    'site_key' => 'oregon.tires',
]);

$inboxConfig = [
    'site_key'   => 'oregon.tires',
    'api_base'   => '/api/form',
    'page_title' => 'Oregon Tires — Contact Inbox',
];

require FormManager::resolveTemplate('form/admin-inbox.php');
