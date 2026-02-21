<?php
declare(strict_types=1);

/**
 * Oregon Tires — Commerce Crypto Confirmation API
 * Thin wrapper around commerce-kit crypto-confirm endpoint.
 * CUSTOMER-FACING — no admin auth required.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

$pdo = getDB();

$commerceKitPath = $_ENV['COMMERCE_KIT_PATH'] ?? __DIR__ . '/../../../../---commerce-kit';
require_once $commerceKitPath . '/loader.php';

$siteKey = 'oregon.tires';
$providers = CommerceBootstrap::init($pdo, $siteKey, [
    'stripe' => true,
    'stripe_config' => [
        'secret_key'     => $_ENV['STRIPE_SECRET_KEY'] ?? '',
        'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
    ],
    'paypal' => true,
    'paypal_config' => [
        'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
        'secret'    => $_ENV['PAYPAL_SECRET'] ?? '',
        'mode'      => $_ENV['PAYPAL_MODE'] ?? 'sandbox',
    ],
    'crypto' => true,
    'crypto_config' => [
        'wallet_addresses' => [
            'ETH'  => $_ENV['CRYPTO_ETH_ADDRESS'] ?? '',
            'BTC'  => $_ENV['CRYPTO_BTC_ADDRESS'] ?? '',
            'SOL'  => $_ENV['CRYPTO_SOL_ADDRESS'] ?? '',
            'USDT' => $_ENV['CRYPTO_USDT_ADDRESS'] ?? '',
            'USDC' => $_ENV['CRYPTO_USDC_ADDRESS'] ?? '',
        ],
        'expiry_minutes' => 30,
    ],
]);

require $commerceKitPath . '/api/commerce/crypto-confirm.php';
