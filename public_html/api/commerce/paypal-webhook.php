<?php
declare(strict_types=1);

/**
 * Oregon Tires — PayPal Webhook Endpoint
 * Thin wrapper around commerce-kit PayPal webhook handler.
 * NO auth — PayPal verifies itself via signature.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

// Commerce Kit path
$commerceKitPath = $_ENV['COMMERCE_KIT_PATH'] ?? __DIR__ . '/../../../../---commerce-kit';
require_once $commerceKitPath . '/loader.php';

// Bridge Oregon Tires PDO to commerce-kit's expected $pdo variable
$pdo = getDB();

// Initialize PayPal provider directly (same pattern as webhook.php for Stripe)
$paypalProvider = new CommercePayPal($pdo, [
    'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
    'secret'    => $_ENV['PAYPAL_SECRET'] ?? '',
    'mode'      => $_ENV['PAYPAL_MODE'] ?? 'sandbox',
]);

require $commerceKitPath . '/api/commerce/paypal-webhook.php';
