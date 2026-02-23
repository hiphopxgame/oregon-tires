<?php
/**
 * Oregon Tires — Admin Tire Fitment Lookup (no rate limit)
 * GET /api/admin/tire-fitment.php?year=2022&make=Toyota&model=Camry&trim=SE
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/tire-fitment.php';

try {
    startSecureSession();
    requireAdmin();
    requireMethod('GET');

    $year  = sanitize((string) ($_GET['year'] ?? ''), 4);
    $make  = sanitize((string) ($_GET['make'] ?? ''), 50);
    $model = sanitize((string) ($_GET['model'] ?? ''), 50);
    $trim  = sanitize((string) ($_GET['trim'] ?? ''), 100);

    if (empty($year) || empty($make) || empty($model)) {
        jsonError('Year, make, and model are required.');
    }

    $result = lookupTireFitment($year, $make, $model, $trim);

    if ($result['success']) {
        jsonSuccess($result['data']);
    } else {
        jsonError($result['error'] ?? 'Fitment lookup failed.', 422);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/tire-fitment.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
