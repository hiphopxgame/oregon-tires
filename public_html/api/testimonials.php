<?php
/**
 * Oregon Tires — Testimonials Endpoint
 * GET /api/testimonials.php
 *
 * Returns all active testimonials ordered by sort_order.
 * Public endpoint, no authentication required.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();
    $stmt = $db->query(
        'SELECT id, customer_name, rating, review_text_en, review_text_es, sort_order, created_at
         FROM oretir_testimonials
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC'
    );

    jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));

} catch (\Throwable $e) {
    error_log('testimonials.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
