<?php
declare(strict_types=1);

/**
 * Oregon Tires — Form Kit Stats API
 * Thin wrapper around form-kit stats endpoint.
 * ADMIN — requires session auth.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

$pdo = getDB();

$formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../../---form-kit';
require_once $formKitPath . '/loader.php';

FormManager::init($pdo, [
    'site_key' => 'oregon.tires',
]);

require $formKitPath . '/api/form/stats.php';
