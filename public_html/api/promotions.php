<?php
/**
 * Oregon Tires — Promotions Endpoint
 * GET /api/promotions.php
 *
 * Returns active promotions whose date range includes the current time.
 * Public endpoint, no authentication required.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();

    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare(
        'SELECT id, title_en, title_es, body_en, body_es, cta_text_en, cta_text_es,
                cta_url, bg_color, text_color, badge_text, image_url, starts_at, ends_at
         FROM oretir_promotions
         WHERE is_active = 1
           AND (starts_at IS NULL OR starts_at <= ?)
           AND (ends_at IS NULL OR ends_at >= ?)
         ORDER BY sort_order ASC, id DESC
         LIMIT 5'
    );
    $stmt->execute([$now, $now]);

    jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));

} catch (\Throwable $e) {
    error_log('promotions.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
