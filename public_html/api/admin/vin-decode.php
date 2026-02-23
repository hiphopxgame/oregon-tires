<?php
/**
 * Oregon Tires — Admin VIN Decode (no rate limit)
 * GET /api/admin/vin-decode.php?vin=XXXXXXXXXXXXX
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/vin-decode.php';

try {
    startSecureSession();
    requireAdmin();
    requireMethod('GET');

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
    error_log("Oregon Tires api/admin/vin-decode.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
