<?php
/**
 * Oregon Tires — Testimonials Endpoint
 * GET /api/testimonials.php              — all active (backward compat)
 * GET /api/testimonials.php?scope=homepage — only show_on_homepage=1
 * GET /api/testimonials.php?scope=all      — all active reviews
 * GET /api/testimonials.php?scope=stats    — aggregate rating + review count
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();
    $scope = $_GET['scope'] ?? '';

    // Stats endpoint
    if ($scope === 'stats') {
        require_once __DIR__ . '/../includes/google-reviews.php';
        jsonSuccess(getGoogleReviewStats($db));
    }

    // Build query based on scope
    $where = 'WHERE is_active = 1';
    if ($scope === 'homepage') {
        $where .= ' AND show_on_homepage = 1';
    }

    $stmt = $db->query(
        "SELECT id, source, customer_name, author_photo_url, google_published_at,
                rating, review_text_en, review_text_es, show_on_homepage, sort_order, created_at
         FROM oretir_testimonials
         {$where}
         ORDER BY sort_order ASC, id ASC"
    );

    jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));

} catch (\Throwable $e) {
    error_log('testimonials.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
