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

    // Optional category filter
    $category = sanitize((string) ($_GET['category'] ?? ''), 20);
    $validCategories = ['general', 'completed-work', 'facility', 'promotional'];

    $whereClause = 'WHERE is_active = 1';
    $params = [];
    if ($category && in_array($category, $validCategories, true)) {
        $whereClause .= ' AND category = ?';
        $params[] = $category;
    }

    $sql = "SELECT id, image_url, media_type, video_url, category, title_en, title_es, description_en, description_es, display_order, created_at
            FROM oretir_gallery_images
            {$whereClause}
            ORDER BY display_order ASC";

    if (empty($params) && function_exists('cachedQuery')) {
        $images = cachedQuery(
            getDB(), 'gallery_images', 600,
            $sql, [], 'oregon_tires'
        );
    } else {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $images = $stmt->fetchAll();
    }

    jsonSuccess($images);

} catch (\Throwable $e) {
    error_log("Oregon Tires gallery.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
