<?php
/**
 * Oregon Tires — Public Services API
 * GET /api/services.php                  — all active services
 * GET /api/services.php?bookable=1       — only bookable services
 * GET /api/services.php?slug=tire-installation — single service with FAQs + related
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();
    $slug = trim($_GET['slug'] ?? '');
    $bookable = isset($_GET['bookable']) ? (int) $_GET['bookable'] : null;

    // ─── Single service by slug ────────────────────────────────
    if ($slug !== '') {
        $stmt = $db->prepare(
            'SELECT * FROM oretir_services WHERE slug = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$slug]);
        $service = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$service) {
            jsonError('Service not found', 404);
        }

        // Fetch FAQs
        $faqStmt = $db->prepare(
            'SELECT id, question_en, question_es, answer_en, answer_es, sort_order
             FROM oretir_service_faqs
             WHERE service_id = ?
             ORDER BY sort_order ASC, id ASC'
        );
        $faqStmt->execute([$service['id']]);
        $service['faqs'] = $faqStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch related services
        $relStmt = $db->prepare(
            'SELECT s.id, s.slug, s.name_en, s.name_es, s.icon
             FROM oretir_service_related r
             JOIN oretir_services s ON s.id = r.related_service_id AND s.is_active = 1
             WHERE r.service_id = ?
             ORDER BY r.sort_order ASC'
        );
        $relStmt->execute([$service['id']]);
        $service['related'] = $relStmt->fetchAll(\PDO::FETCH_ASSOC);

        jsonSuccess($service);
    }

    // ─── List services ─────────────────────────────────────────
    $sql = 'SELECT id, slug, name_en, name_es, description_en, description_es,
                   icon, color_hex, color_bg, color_text, color_dark_bg, color_dark_text, color_dot,
                   price_display_en, price_display_es, category,
                   is_bookable, has_detail_page, sort_order, image_url, duration_estimate
            FROM oretir_services
            WHERE is_active = 1';
    $params = [];

    if ($bookable === 1) {
        $sql .= ' AND is_bookable = 1';
    }

    $sql .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));

} catch (\Throwable $e) {
    error_log('services.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
