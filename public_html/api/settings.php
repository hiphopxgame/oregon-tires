<?php
/**
 * Oregon Tires — Site Settings Endpoint
 * GET /api/settings.php
 *
 * Returns all site settings (bilingual key/value pairs).
 * Public endpoint, no authentication required.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();
    $stmt = $db->query('SELECT setting_key, value_en, value_es FROM oretir_site_settings ORDER BY setting_key ASC');
    $settings = $stmt->fetchAll();

    jsonSuccess($settings);

} catch (\Throwable $e) {
    error_log("Oregon Tires settings.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
