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
    $allowedPlacements = ['banner', 'exit_intent'];
    $placement = $_GET['placement'] ?? 'banner';
    if (!in_array($placement, $allowedPlacements, true)) {
        $placement = 'banner';
    }

    if ($placement === 'exit_intent') {
        $stmt = $db->prepare(
            'SELECT id, title_en, title_es, body_en, body_es,
                    subtitle_en, subtitle_es,
                    cta_text_en, cta_text_es,
                    placeholder_en, placeholder_es,
                    success_msg_en, success_msg_es,
                    error_msg_en, error_msg_es,
                    nospam_en, nospam_es,
                    popup_icon, starts_at, ends_at
             FROM oretir_promotions
             WHERE placement = ?
               AND is_active = 1
               AND (starts_at IS NULL OR starts_at <= ?)
               AND (ends_at IS NULL OR ends_at >= ?)
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([$placement, $now, $now]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        jsonSuccess($row ?: null);
    } else {
        $stmt = $db->prepare(
            'SELECT id, title_en, title_es, body_en, body_es, cta_text_en, cta_text_es,
                    cta_url, bg_color, text_color, badge_text, image_url, starts_at, ends_at
             FROM oretir_promotions
             WHERE placement = ?
               AND is_active = 1
               AND (starts_at IS NULL OR starts_at <= ?)
               AND (ends_at IS NULL OR ends_at >= ?)
             ORDER BY sort_order ASC, id DESC
             LIMIT 5'
        );
        $stmt->execute([$placement, $now, $now]);
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

} catch (\Throwable $e) {
    error_log('promotions.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
