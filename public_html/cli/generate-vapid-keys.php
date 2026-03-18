#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Generate VAPID Keys
 * One-time CLI script to generate and store VAPID key pair.
 *
 * Usage:  php generate-vapid-keys.php
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/push.php';

echo "[" . date('Y-m-d H:i:s') . "] Generating VAPID keys...\n";

try {
    // Check if keys already exist
    $existing = getVapidPublicKey();
    if (!empty($existing)) {
        echo "WARNING: VAPID keys already exist.\n";
        echo "Public key: {$existing}\n";
        echo "To regenerate, first clear the keys in oretir_site_settings.\n";
        echo "Regenerating will invalidate ALL existing push subscriptions.\n\n";

        echo "Regenerate? (type 'yes' to confirm): ";
        $confirm = trim(fgets(STDIN));
        if ($confirm !== 'yes') {
            echo "Aborted.\n";
            exit(0);
        }
    }

    $keys = generateVapidKeys();

    echo "VAPID keys generated and stored in oretir_site_settings.\n\n";
    echo "Public key:  {$keys['publicKey']}\n";
    echo "Private key: [stored in DB — not displayed for security]\n\n";
    echo "Make sure VAPID_SUBJECT is set in .env:\n";
    echo "  VAPID_SUBJECT=mailto:info@oregon.tires\n\n";
    echo "Done.\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("generate-vapid-keys.php error: " . $e->getMessage());
    exit(1);
}
