<?php
/**
 * Oregon Tires — Public VIN Decode Endpoint
 * GET /api/vin-decode.php?vin=XXXXXXXXXXXXX
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/vin-decode.php';

try {
    requireMethod('GET');
    checkRateLimit('vin_decode', 10, 3600);

    $vin = sanitize((string) ($_GET['vin'] ?? ''), 17);
    if (empty($vin)) {
        jsonError('VIN parameter is required.');
    }

    $result = decodeVin($vin);

    if ($result['success']) {
        jsonSuccess($result['data']);
    } else {
        jsonError($result['error'] ?? 'VIN decode failed.', 422);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/vin-decode.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
