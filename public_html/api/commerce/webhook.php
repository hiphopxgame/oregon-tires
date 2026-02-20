<?php
declare(strict_types=1);

/**
 * Oregon Tires — Stripe Webhook Endpoint
 * Thin wrapper around commerce-kit webhook handler.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

// Commerce Kit path — adjust for your deployment
$commerceKitPath = $_ENV['COMMERCE_KIT_PATH'] ?? __DIR__ . '/../../../../---commerce-kit';
require_once $commerceKitPath . '/loader.php';

// Bridge Oregon Tires PDO to commerce-kit's expected $pdo variable
$pdo = getDB();

// Initialize Stripe provider
$stripeProvider = new CommerceStripe($pdo, [
    'secret_key'     => $_ENV['STRIPE_SECRET_KEY'] ?? '',
    'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
]);

require $commerceKitPath . '/api/commerce/webhook.php';
