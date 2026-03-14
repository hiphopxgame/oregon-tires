<?php
/**
 * Oregon Tires — Admin FAQ CRUD
 * GET    /api/admin/faq.php       — list all FAQs
 * POST   /api/admin/faq.php       — create FAQ (JSON body)
 * PUT    /api/admin/faq.php       — update FAQ (JSON body, requires id)
 * DELETE /api/admin/faq.php       — delete FAQ (JSON body, requires id)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireAdmin();
    requireMethod('GET', 'POST', 'PUT', 'DELETE');

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List all FAQs ─────────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT * FROM oretir_faq ORDER BY sort_order ASC, id DESC'
        );
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    // ─── Mutating requests require CSRF ─────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    // ─── POST: Create new FAQ ───────────────────────────────────
    if ($method === 'POST') {
        $questionEn = trim((string)($body['question_en'] ?? ''));
        if ($questionEn === '') {
            jsonError('Question (EN) is required', 400);
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_faq
                (question_en, question_es, answer_en, answer_es, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            sanitize($questionEn, 500),
            sanitize(trim((string)($body['question_es'] ?? '')), 500),
            $body['answer_en'] ?? '',
            $body['answer_es'] ?? '',
            (int)($body['is_active'] ?? 1),
            (int)($body['sort_order'] ?? 0),
        ]);

        jsonSuccess(['id' => (int)$db->lastInsertId()]);
    }

    // ─── PUT: Update existing FAQ ───────────────────────────────
    if ($method === 'PUT') {
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing FAQ id', 400);
        }

        $stmt = $db->prepare(
            'UPDATE oretir_faq SET
                question_en = ?, question_es = ?,
                answer_en = ?, answer_es = ?,
                is_active = ?, sort_order = ?,
                updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            sanitize(trim((string)($body['question_en'] ?? '')), 500),
            sanitize(trim((string)($body['question_es'] ?? '')), 500),
            $body['answer_en'] ?? '',
            $body['answer_es'] ?? '',
            (int)($body['is_active'] ?? 1),
            (int)($body['sort_order'] ?? 0),
            $id,
        ]);

        jsonSuccess(['updated' => $stmt->rowCount()]);
    }

    // ─── DELETE: Remove FAQ ─────────────────────────────────────
    if ($method === 'DELETE') {
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing FAQ id', 400);
        }

        $db->prepare('DELETE FROM oretir_faq WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log('admin/faq.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
