<?php
/**
 * Oregon Tires — Admin Testimonials CRUD
 * GET    /api/admin/testimonials.php       — list all testimonials
 * POST   /api/admin/testimonials.php       — create testimonial (JSON body)
 * PUT    /api/admin/testimonials.php       — update testimonial (JSON body, requires id)
 * DELETE /api/admin/testimonials.php       — delete testimonial (JSON body, requires id)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    requireAdmin();

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List all testimonials ─────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT * FROM oretir_testimonials ORDER BY sort_order ASC, id DESC'
        );
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    // ─── Mutating requests require CSRF ─────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    // ─── POST: Create new testimonial ───────────────────────────
    if ($method === 'POST') {
        $name = trim((string)($body['customer_name'] ?? ''));
        if ($name === '') {
            jsonError('Customer name is required', 400);
        }
        $rating = (int)($body['rating'] ?? 5);
        if ($rating < 1 || $rating > 5) {
            jsonError('Rating must be between 1 and 5', 400);
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_testimonials
                (customer_name, rating, review_text_en, review_text_es, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            sanitize($name, 200),
            $rating,
            $body['review_text_en'] ?? '',
            $body['review_text_es'] ?? '',
            (int)($body['is_active'] ?? 1),
            (int)($body['sort_order'] ?? 0),
        ]);

        jsonSuccess(['id' => (int)$db->lastInsertId()]);
    }

    // ─── PUT: Update existing testimonial ───────────────────────
    if ($method === 'PUT') {
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing testimonial id', 400);
        }
        $rating = (int)($body['rating'] ?? 5);
        if ($rating < 1 || $rating > 5) {
            jsonError('Rating must be between 1 and 5', 400);
        }

        $stmt = $db->prepare(
            'UPDATE oretir_testimonials SET
                customer_name = ?, rating = ?,
                review_text_en = ?, review_text_es = ?,
                is_active = ?, sort_order = ?,
                updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            sanitize(trim((string)($body['customer_name'] ?? '')), 200),
            $rating,
            $body['review_text_en'] ?? '',
            $body['review_text_es'] ?? '',
            (int)($body['is_active'] ?? 1),
            (int)($body['sort_order'] ?? 0),
            $id,
        ]);

        jsonSuccess(['updated' => $stmt->rowCount()]);
    }

    // ─── DELETE: Remove testimonial ─────────────────────────────
    if ($method === 'DELETE') {
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing testimonial id', 400);
        }

        $db->prepare('DELETE FROM oretir_testimonials WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log('admin/testimonials.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
