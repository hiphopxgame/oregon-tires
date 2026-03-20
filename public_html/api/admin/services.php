<?php
/**
 * Oregon Tires — Admin Services CRUD
 * GET    /api/admin/services.php              — list all services (including inactive)
 * POST   /api/admin/services.php              — create service
 * PUT    /api/admin/services.php              — update service
 * DELETE /api/admin/services.php              — delete service (checks appointment refs)
 *
 * Sub-resources via ?action=:
 *   GET    ?action=faqs&service_id=N          — list FAQs for a service
 *   POST   ?action=faqs                       — create FAQ
 *   PUT    ?action=faqs                       — update FAQ
 *   DELETE ?action=faqs                       — delete FAQ
 *   GET    ?action=related&service_id=N       — list related for a service
 *   PUT    ?action=related                    — set related services
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requirePermission('shop_ops');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = trim($_GET['action'] ?? '');

    // ═══════════════════════════════════════════════════════════
    // FAQ sub-resource
    // ═══════════════════════════════════════════════════════════
    if ($action === 'faqs') {
        if ($method === 'GET') {
            $serviceId = (int) ($_GET['service_id'] ?? 0);
            if ($serviceId <= 0) jsonError('Missing service_id', 400);

            $stmt = $db->prepare(
                'SELECT * FROM oretir_service_faqs WHERE service_id = ? ORDER BY sort_order ASC, id ASC'
            );
            $stmt->execute([$serviceId]);
            jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }

        verifyCsrf();
        $body = getJsonBody();

        if ($method === 'POST') {
            $serviceId = (int) ($body['service_id'] ?? 0);
            $questionEn = trim((string) ($body['question_en'] ?? ''));
            if ($serviceId <= 0 || $questionEn === '') jsonError('service_id and question_en required', 400);

            $stmt = $db->prepare(
                'INSERT INTO oretir_service_faqs (service_id, question_en, question_es, answer_en, answer_es, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $serviceId,
                sanitize($questionEn, 500),
                sanitize(trim((string) ($body['question_es'] ?? '')), 500),
                $body['answer_en'] ?? '',
                $body['answer_es'] ?? '',
                (int) ($body['sort_order'] ?? 0),
            ]);
            jsonSuccess(['id' => (int) $db->lastInsertId()]);
        }

        if ($method === 'PUT') {
            $id = (int) ($body['id'] ?? 0);
            if ($id <= 0) jsonError('Missing FAQ id', 400);

            $stmt = $db->prepare(
                'UPDATE oretir_service_faqs SET
                    question_en = ?, question_es = ?, answer_en = ?, answer_es = ?, sort_order = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                sanitize(trim((string) ($body['question_en'] ?? '')), 500),
                sanitize(trim((string) ($body['question_es'] ?? '')), 500),
                $body['answer_en'] ?? '',
                $body['answer_es'] ?? '',
                (int) ($body['sort_order'] ?? 0),
                $id,
            ]);
            jsonSuccess(['updated' => $stmt->rowCount()]);
        }

        if ($method === 'DELETE') {
            $id = (int) ($body['id'] ?? 0);
            if ($id <= 0) jsonError('Missing FAQ id', 400);
            $db->prepare('DELETE FROM oretir_service_faqs WHERE id = ?')->execute([$id]);
            jsonSuccess(['deleted' => true]);
        }

        jsonError('Invalid method for faqs', 405);
    }

    // ═══════════════════════════════════════════════════════════
    // Related services sub-resource
    // ═══════════════════════════════════════════════════════════
    if ($action === 'related') {
        if ($method === 'GET') {
            $serviceId = (int) ($_GET['service_id'] ?? 0);
            if ($serviceId <= 0) jsonError('Missing service_id', 400);

            $stmt = $db->prepare(
                'SELECT r.related_service_id, s.slug, s.name_en, s.name_es
                 FROM oretir_service_related r
                 JOIN oretir_services s ON s.id = r.related_service_id
                 WHERE r.service_id = ?
                 ORDER BY r.sort_order ASC'
            );
            $stmt->execute([$serviceId]);
            jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }

        if ($method === 'PUT') {
            verifyCsrf();
            $body = getJsonBody();
            $serviceId = (int) ($body['service_id'] ?? 0);
            $relatedIds = $body['related_ids'] ?? [];
            if ($serviceId <= 0) jsonError('Missing service_id', 400);

            $db->beginTransaction();
            $db->prepare('DELETE FROM oretir_service_related WHERE service_id = ?')->execute([$serviceId]);

            if (!empty($relatedIds) && is_array($relatedIds)) {
                $ins = $db->prepare(
                    'INSERT INTO oretir_service_related (service_id, related_service_id, sort_order) VALUES (?, ?, ?)'
                );
                $order = 0;
                foreach ($relatedIds as $rid) {
                    $rid = (int) $rid;
                    if ($rid > 0 && $rid !== $serviceId) {
                        $ins->execute([$serviceId, $rid, $order++]);
                    }
                }
            }
            $db->commit();
            jsonSuccess(['updated' => true]);
        }

        jsonError('Invalid method for related', 405);
    }

    // ═══════════════════════════════════════════════════════════
    // Main services CRUD
    // ═══════════════════════════════════════════════════════════

    // ─── GET: List all services ────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT s.*, (SELECT COUNT(*) FROM oretir_service_faqs f WHERE f.service_id = s.id) AS faq_count
             FROM oretir_services s
             ORDER BY s.sort_order ASC, s.id ASC'
        );
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    // ─── Mutating requests require CSRF ────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    // ─── POST: Create service ──────────────────────────────────
    if ($method === 'POST') {
        $nameEn = trim((string) ($body['name_en'] ?? ''));
        $slug = trim((string) ($body['slug'] ?? ''));
        if ($nameEn === '' || $slug === '') jsonError('name_en and slug are required', 400);

        // Validate slug format
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
            jsonError('Slug must be lowercase with hyphens only', 400);
        }

        // Check uniqueness
        $check = $db->prepare('SELECT id FROM oretir_services WHERE slug = ?');
        $check->execute([$slug]);
        if ($check->fetch()) jsonError('Slug already exists', 409);

        $stmt = $db->prepare(
            'INSERT INTO oretir_services
                (slug, name_en, name_es, description_en, description_es,
                 body_en, body_es, icon, color_hex, color_bg, color_text,
                 color_dark_bg, color_dark_text, color_dot,
                 price_display_en, price_display_es, category,
                 is_bookable, has_detail_page, is_active, sort_order,
                 image_url, custom_sections_html, custom_scripts_html,
                 custom_translations, duration_estimate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $slug,
            sanitize($nameEn, 200),
            sanitize(trim((string) ($body['name_es'] ?? '')), 200),
            $body['description_en'] ?? '',
            $body['description_es'] ?? '',
            $body['body_en'] ?? '',
            $body['body_es'] ?? '',
            sanitize(trim((string) ($body['icon'] ?? '')), 20),
            sanitize(trim((string) ($body['color_hex'] ?? '#10B981')), 7),
            sanitize(trim((string) ($body['color_bg'] ?? 'bg-green-100')), 50),
            sanitize(trim((string) ($body['color_text'] ?? 'text-green-800')), 50),
            sanitize(trim((string) ($body['color_dark_bg'] ?? 'dark:bg-green-900/30')), 50),
            sanitize(trim((string) ($body['color_dark_text'] ?? 'dark:text-green-300')), 50),
            sanitize(trim((string) ($body['color_dot'] ?? 'bg-green-500')), 50),
            sanitize(trim((string) ($body['price_display_en'] ?? '')), 100),
            sanitize(trim((string) ($body['price_display_es'] ?? '')), 100),
            $body['category'] ?? 'maintenance',
            (int) ($body['is_bookable'] ?? 1),
            (int) ($body['has_detail_page'] ?? 1),
            (int) ($body['is_active'] ?? 1),
            (int) ($body['sort_order'] ?? 0),
            sanitize(trim((string) ($body['image_url'] ?? '')), 500),
            $body['custom_sections_html'] ?? null,
            $body['custom_scripts_html'] ?? null,
            $body['custom_translations'] ?? null,
            sanitize(trim((string) ($body['duration_estimate'] ?? '')), 20),
        ]);

        jsonSuccess(['id' => (int) $db->lastInsertId()]);
    }

    // ─── PUT: Update service ───────────────────────────────────
    if ($method === 'PUT') {
        $id = (int) ($body['id'] ?? 0);
        if ($id <= 0) jsonError('Missing service id', 400);

        $slug = trim((string) ($body['slug'] ?? ''));
        if ($slug !== '' && !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
            jsonError('Slug must be lowercase with hyphens only', 400);
        }

        // Check slug uniqueness if changing
        if ($slug !== '') {
            $check = $db->prepare('SELECT id FROM oretir_services WHERE slug = ? AND id != ?');
            $check->execute([$slug, $id]);
            if ($check->fetch()) jsonError('Slug already exists', 409);
        }

        $stmt = $db->prepare(
            'UPDATE oretir_services SET
                slug = ?, name_en = ?, name_es = ?,
                description_en = ?, description_es = ?,
                body_en = ?, body_es = ?,
                icon = ?, color_hex = ?, color_bg = ?, color_text = ?,
                color_dark_bg = ?, color_dark_text = ?, color_dot = ?,
                price_display_en = ?, price_display_es = ?,
                category = ?, is_bookable = ?, has_detail_page = ?,
                is_active = ?, sort_order = ?, image_url = ?,
                custom_sections_html = ?, custom_scripts_html = ?,
                custom_translations = ?, duration_estimate = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $slug,
            sanitize(trim((string) ($body['name_en'] ?? '')), 200),
            sanitize(trim((string) ($body['name_es'] ?? '')), 200),
            $body['description_en'] ?? '',
            $body['description_es'] ?? '',
            $body['body_en'] ?? '',
            $body['body_es'] ?? '',
            sanitize(trim((string) ($body['icon'] ?? '')), 20),
            sanitize(trim((string) ($body['color_hex'] ?? '#10B981')), 7),
            sanitize(trim((string) ($body['color_bg'] ?? 'bg-green-100')), 50),
            sanitize(trim((string) ($body['color_text'] ?? 'text-green-800')), 50),
            sanitize(trim((string) ($body['color_dark_bg'] ?? 'dark:bg-green-900/30')), 50),
            sanitize(trim((string) ($body['color_dark_text'] ?? 'dark:text-green-300')), 50),
            sanitize(trim((string) ($body['color_dot'] ?? 'bg-green-500')), 50),
            sanitize(trim((string) ($body['price_display_en'] ?? '')), 100),
            sanitize(trim((string) ($body['price_display_es'] ?? '')), 100),
            $body['category'] ?? 'maintenance',
            (int) ($body['is_bookable'] ?? 1),
            (int) ($body['has_detail_page'] ?? 1),
            (int) ($body['is_active'] ?? 1),
            (int) ($body['sort_order'] ?? 0),
            sanitize(trim((string) ($body['image_url'] ?? '')), 500),
            $body['custom_sections_html'] ?? null,
            $body['custom_scripts_html'] ?? null,
            $body['custom_translations'] ?? null,
            sanitize(trim((string) ($body['duration_estimate'] ?? '')), 20),
            $id,
        ]);

        jsonSuccess(['updated' => $stmt->rowCount()]);
    }

    // ─── DELETE: Remove service ────────────────────────────────
    if ($method === 'DELETE') {
        $id = (int) ($body['id'] ?? 0);
        if ($id <= 0) jsonError('Missing service id', 400);

        // Check for appointment references
        $svc = $db->prepare('SELECT slug FROM oretir_services WHERE id = ?');
        $svc->execute([$id]);
        $row = $svc->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $ref = $db->prepare(
                'SELECT COUNT(*) FROM oretir_appointments WHERE service = ? OR services_json LIKE ?'
            );
            $ref->execute([$row['slug'], '%' . $row['slug'] . '%']);
            $count = (int) $ref->fetchColumn();
            if ($count > 0) {
                jsonError("Cannot delete: {$count} appointment(s) reference this service. Deactivate instead.", 409);
            }
        }

        $db->prepare('DELETE FROM oretir_services WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log('admin/services.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
