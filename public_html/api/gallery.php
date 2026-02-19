<?php
/**
 * Oregon Tires — Gallery Images Endpoint
 * GET /api/gallery.php
 *
 * Returns all active gallery images ordered by display_order.
 * Public endpoint, no authentication required.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();
    $stmt = $db->query(
        'SELECT id, image_url, title, description, display_order, created_at
         FROM oretir_gallery_images
         WHERE is_active = 1
         ORDER BY display_order ASC'
    );
    $images = $stmt->fetchAll();

    jsonSuccess($images);

} catch (\Throwable $e) {
    error_log("Oregon Tires gallery.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
