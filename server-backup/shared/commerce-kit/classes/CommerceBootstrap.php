<?php
declare(strict_types=1);

/**
 * CommerceBootstrap — Site integration helper.
 *
 * Provides a simple init() method that sites call to get all configured
 * payment providers ready to use.
 *
 * Usage:
 *   require_once COMMERCE_KIT_PATH . '/loader.php';
 *   $providers = CommerceBootstrap::init($pdo, 'oregon.tires', [
 *       'paypal'  => true,
 *       'stripe'  => true,
 *       'crypto'  => false,
 *   ]);
 *   // $providers['manual'] is always available
 *   // $providers['paypal'] is CommercePayPal if enabled
 *   // $providers['stripe'] is CommerceStripe if enabled
 */
class CommerceBootstrap
{
    /**
     * Initialize commerce providers for a site.
     *
     * @param PDO    $pdo     Database connection
     * @param string $siteKey Site identifier
     * @param array  $config  Provider enable flags + optional provider-specific config
     * @return array Keyed array of initialized providers
     */
    public static function init(PDO $pdo, string $siteKey, array $config = []): array
    {
        $providers = [];

        // Manual is always available
        $providers['manual'] = new CommerceManual($pdo);

        // PayPal
        if (!empty($config['paypal'])) {
            $providers['paypal'] = new CommercePayPal($pdo, $config['paypal_config'] ?? []);
        }

        // Stripe
        if (!empty($config['stripe'])) {
            $providers['stripe'] = new CommerceStripe($pdo, $config['stripe_config'] ?? []);
        }

        // Crypto
        if (!empty($config['crypto'])) {
            $providers['crypto'] = new CommerceCrypto($pdo, $config['crypto_config'] ?? []);
        }

        return $providers;
    }
}
