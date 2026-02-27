<?php
/**
 * Oregon Tires — Admin Promotions CRUD
 * GET    /api/admin/promotions.php       — list all promotions
 * POST   /api/admin/promotions.php       — create promotion (JSON body)
 * PUT    /api/admin/promotions.php       — update promotion (JSON body, requires id)
 * DELETE /api/admin/promotions.php       — delete promotion (JSON body, requires id)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    requireAdmin();

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List all promotions ─────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT * FROM oretir_promotions ORDER BY sort_order ASC, id DESC'
        );
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    // ─── Mutating requests require CSRF ───────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    // ─── POST: Create new promotion ───────────────────────────────
    if ($method === 'POST') {
        $stmt = $db->prepare(
            'INSERT INTO oretir_promotions
                (title_en, title_es, body_en, body_es, cta_text_en, cta_text_es,
                 cta_url, bg_color, text_color, badge_text, is_active,
                 starts_at, ends_at, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            sanitize((string)($body['title_en'] ?? ''), 255),
            sanitize((string)($body['title_es'] ?? ''), 255),
            $body['body_en'] ?? '',
            $body['body_es'] ?? '',
            sanitize((string)($body['cta_text_en'] ?? 'Book Now'), 100),
            sanitize((string)($body['cta_text_es'] ?? 'Reserve Ahora'), 100),
            sanitize((string)($body['cta_url'] ?? '/book-appointment/'), 500),
            sanitize((string)($body['bg_color'] ?? '#f59e0b'), 20),
            sanitize((string)($body['text_color'] ?? '#000000'), 20),
            sanitize((string)($body['badge_text'] ?? ''), 50) ?: null,
            (int)($body['is_active'] ?? 1),
            $body['starts_at'] ?? null,
            $body['ends_at'] ?? null,
            (int)($body['sort_order'] ?? 0),
        ]);

        jsonSuccess(['id' => (int)$db->lastInsertId()]);
    }

    // ─── PUT: Update existing promotion ───────────────────────────
    if ($method === 'PUT') {
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing promotion id', 400);
        }

        $stmt = $db->prepare(
            'UPDATE oretir_promotions SET
                title_en = ?, title_es = ?, body_en = ?, body_es = ?,
                cta_text_en = ?, cta_text_es = ?, cta_url = ?,
                bg_color = ?, text_color = ?, badge_text = ?,
                is_active = ?, starts_at = ?, ends_at = ?, sort_order = ?,
                updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            sanitize((string)($body['title_en'] ?? ''), 255),
            sanitize((string)($body['title_es'] ?? ''), 255),
            $body['body_en'] ?? '',
            $body['body_es'] ?? '',
            sanitize((string)($body['cta_text_en'] ?? 'Book Now'), 100),
            sanitize((string)($body['cta_text_es'] ?? 'Reserve Ahora'), 100),
            sanitize((string)($body['cta_url'] ?? '/book-appointment/'), 500),
            sanitize((string)($body['bg_color'] ?? '#f59e0b'), 20),
            sanitize((string)($body['text_color'] ?? '#000000'), 20),
            sanitize((string)($body['badge_text'] ?? ''), 50) ?: null,
            (int)($body['is_active'] ?? 1),
            $body['starts_at'] ?? null,
            $body['ends_at'] ?? null,
            (int)($body['sort_order'] ?? 0),
            $id,
        ]);

        jsonSuccess(['updated' => $stmt->rowCount()]);
    }

    // ─── DELETE: Remove promotion ─────────────────────────────────
    if ($method === 'DELETE') {
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing promotion id', 400);
        }

        $db->prepare('DELETE FROM oretir_promotions WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log('admin/promotions.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
