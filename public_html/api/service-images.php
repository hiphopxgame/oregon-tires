<?php
/**
 * Oregon Tires — Service Images Endpoint
 * GET /api/service-images.php
 *
 * Returns current service images with positioning data.
 * Public endpoint, no authentication required.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();
    $stmt = $db->query(
        'SELECT service_key, image_url, position_x, position_y, scale
         FROM oretir_service_images
         WHERE is_current = 1
         ORDER BY service_key ASC'
    );
    $images = $stmt->fetchAll();

    jsonSuccess($images);

} catch (\Throwable $e) {
    error_log("Oregon Tires service-images.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
