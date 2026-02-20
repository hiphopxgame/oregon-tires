<?php
declare(strict_types=1);

/**
 * Oregon Tires — Commerce Orders API
 * Thin wrapper around commerce-kit orders endpoint.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

// Authenticate admin using Oregon Tires session auth
startSecureSession();
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

// Set session vars expected by commerce-kit
$_SESSION['admin'] = true;

// Bridge Oregon Tires PDO to commerce-kit's expected $pdo variable
$pdo = getDB();

$commerceKitPath = $_ENV['COMMERCE_KIT_PATH'] ?? __DIR__ . '/../../../../---commerce-kit';
require_once $commerceKitPath . '/loader.php';

// Override site_key to always be oregon.tires
$_GET['site_key'] = 'oregon.tires';

require $commerceKitPath . '/api/commerce/orders.php';
