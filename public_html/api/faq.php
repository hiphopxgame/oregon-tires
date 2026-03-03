<?php
/**
 * Oregon Tires — FAQ Endpoint
 * GET /api/faq.php
 *
 * Returns all active FAQs ordered by sort_order.
 * Public endpoint, no authentication required.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $db = getDB();
    $stmt = $db->query(
        'SELECT id, question_en, question_es, answer_en, answer_es, sort_order, created_at
         FROM oretir_faq
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC'
    );

    jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));

} catch (\Throwable $e) {
    error_log('faq.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
