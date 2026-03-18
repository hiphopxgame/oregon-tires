<?php
/**
 * Oregon Tires — VAPID Public Key Endpoint
 * GET /api/push-vapid-key.php
 * Returns the VAPID public key for push subscription.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/push.php';

try {
    requireMethod('GET');
    checkRateLimit('vapid_key', 30, 60);

    $publicKey = getVapidPublicKey();

    if (empty($publicKey)) {
        jsonError('Push notifications not configured', 503);
    }

    jsonSuccess(['vapid_public_key' => $publicKey]);

} catch (\Throwable $e) {
    error_log("Oregon Tires push-vapid-key.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
