<?php
/**
 * Oregon Tires — Public Tire Fitment Endpoint
 * GET /api/tire-fitment.php?year=2022&make=Toyota&model=Camry&trim=SE
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/tire-fitment.php';

try {
    requireMethod('GET');
    checkRateLimit('tire_fitment', 10, 3600);

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
    error_log("Oregon Tires api/tire-fitment.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
