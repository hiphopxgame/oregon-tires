<?php
/**
 * Oregon Tires — Admin GBP Post CRUD
 * GET    — list posts
 * POST   — create post / publish post
 * PUT    — update post
 * DELETE — delete post
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/google-business.php';

try {
    $staff = requirePermission('marketing');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        session_write_close();

        $status = sanitize((string) ($_GET['status'] ?? ''), 20);

        $where = [];
        $params = [];
        if ($status !== '') {
            $validStatuses = ['draft', 'published', 'failed', 'expired', 'deleted'];
            if (in_array($status, $validStatuses, true)) {
                $where[] = 'status = ?';
                $params[] = $status;
            }
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("SELECT * FROM oretir_gbp_posts {$whereStr} ORDER BY created_at DESC LIMIT 100");
        $stmt->execute($params);
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    verifyCsrf();
    $data = getJsonBody();

    if ($method === 'POST') {
        $action = $data['action'] ?? 'create';

        if ($action === 'publish') {
            $id = (int) ($data['id'] ?? 0);
            if ($id <= 0) jsonError('Missing post id.');
            $result = publishGbpPost($db, $id);
            if ($result['success']) {
                jsonSuccess($result);
            } else {
                jsonError($result['error'] ?? 'Publish failed');
            }
        }

        // Create
        $postType = sanitize((string) ($data['post_type'] ?? 'update'), 20);
        $validTypes = ['update', 'offer', 'event'];
        if (!in_array($postType, $validTypes, true)) $postType = 'update';

        $stmt = $db->prepare(
            'INSERT INTO oretir_gbp_posts (post_type, title_en, title_es, body_en, body_es, image_url, cta_type, cta_url, offer_start, offer_end, event_start, event_end, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $postType,
            sanitize((string) ($data['title_en'] ?? ''), 300),
            sanitize((string) ($data['title_es'] ?? ''), 300),
            sanitize((string) ($data['body_en'] ?? ''), 5000),
            sanitize((string) ($data['body_es'] ?? ''), 5000),
            sanitize((string) ($data['image_url'] ?? ''), 500),
            sanitize((string) ($data['cta_type'] ?? ''), 50),
            sanitize((string) ($data['cta_url'] ?? ''), 500),
            !empty($data['offer_start']) ? sanitize((string) $data['offer_start'], 10) : null,
            !empty($data['offer_end']) ? sanitize((string) $data['offer_end'], 10) : null,
            !empty($data['event_start']) ? sanitize((string) $data['event_start'], 19) : null,
            !empty($data['event_end']) ? sanitize((string) $data['event_end'], 19) : null,
            'draft',
        ]);

        $id = (int) $db->lastInsertId();
        $newStmt = $db->prepare('SELECT * FROM oretir_gbp_posts WHERE id = ?');
        $newStmt->execute([$id]);
        jsonSuccess($newStmt->fetch(\PDO::FETCH_ASSOC), 201);
    }

    if ($method === 'PUT') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing post id.');

        $stmt = $db->prepare(
            'UPDATE oretir_gbp_posts SET title_en = ?, title_es = ?, body_en = ?, body_es = ?, image_url = ?, cta_type = ?, cta_url = ?, offer_start = ?, offer_end = ?, event_start = ?, event_end = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([
            sanitize((string) ($data['title_en'] ?? ''), 300),
            sanitize((string) ($data['title_es'] ?? ''), 300),
            sanitize((string) ($data['body_en'] ?? ''), 5000),
            sanitize((string) ($data['body_es'] ?? ''), 5000),
            sanitize((string) ($data['image_url'] ?? ''), 500),
            sanitize((string) ($data['cta_type'] ?? ''), 50),
            sanitize((string) ($data['cta_url'] ?? ''), 500),
            !empty($data['offer_start']) ? sanitize((string) $data['offer_start'], 10) : null,
            !empty($data['offer_end']) ? sanitize((string) $data['offer_end'], 10) : null,
            !empty($data['event_start']) ? sanitize((string) $data['event_start'], 19) : null,
            !empty($data['event_end']) ? sanitize((string) $data['event_end'], 19) : null,
            $id,
        ]);
        jsonSuccess(['updated' => true]);
    }

    if ($method === 'DELETE') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing post id.');
        $db->prepare("UPDATE oretir_gbp_posts SET status = 'deleted', updated_at = NOW() WHERE id = ?")->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log("Admin gbp-posts error: " . $e->getMessage());
    jsonError('Server error', 500);
}
