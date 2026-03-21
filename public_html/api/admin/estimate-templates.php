<?php
/**
 * Oregon Tires — Admin Estimate Template Management
 * GET    /api/admin/estimate-templates.php         — List active templates
 * POST   /api/admin/estimate-templates.php         — Create template
 * PUT    /api/admin/estimate-templates.php         — Update template
 * DELETE /api/admin/estimate-templates.php?id=N    — Delete template
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    $staff = requirePermission('shop_ops');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List templates ──────────────────────────────────────────────
    if ($method === 'GET') {
        $activeOnly = ($_GET['active'] ?? '1') === '1';
        $sql = 'SELECT * FROM oretir_estimate_templates';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, name_en ASC';

        $stmt = $db->query($sql);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON items for each template
        foreach ($templates as &$tpl) {
            $tpl['items'] = json_decode($tpl['items'], true) ?: [];
        }
        unset($tpl);

        jsonSuccess($templates);
    }

    // ─── POST: Create template ────────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $nameEn = sanitize((string) ($data['name_en'] ?? ''), 200);
        $nameEs = sanitize((string) ($data['name_es'] ?? ''), 200);
        $serviceType = sanitize((string) ($data['service_type'] ?? ''), 100);
        $items = $data['items'] ?? [];
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        if (empty($nameEn)) {
            jsonError('Template name (English) is required.');
        }
        if (empty($items) || !is_array($items)) {
            jsonError('At least one line item is required.');
        }

        // Validate and sanitize items
        $cleanItems = [];
        foreach ($items as $item) {
            $cleanItems[] = [
                'type'           => sanitize((string) ($item['type'] ?? 'labor'), 20),
                'description_en' => sanitize((string) ($item['description_en'] ?? ''), 500),
                'description_es' => sanitize((string) ($item['description_es'] ?? ''), 500),
                'quantity'       => max(0.01, (float) ($item['quantity'] ?? 1)),
                'unit_price'     => (float) ($item['unit_price'] ?? 0),
                'is_taxable'     => (bool) ($item['is_taxable'] ?? false),
            ];
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_estimate_templates (name_en, name_es, service_type, items, sort_order)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $nameEn,
            $nameEs ?: null,
            $serviceType ?: null,
            json_encode($cleanItems),
            $sortOrder,
        ]);

        jsonSuccess([
            'id'      => (int) $db->lastInsertId(),
            'message' => 'Template created.',
        ]);
    }

    // ─── PUT: Update template ─────────────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Template ID is required.');

        $stmt = $db->prepare('SELECT id FROM oretir_estimate_templates WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) jsonError('Template not found.', 404);

        $fields = [];
        $params = [];

        if (isset($data['name_en'])) {
            $fields[] = 'name_en = ?';
            $params[] = sanitize((string) $data['name_en'], 200);
        }
        if (isset($data['name_es'])) {
            $fields[] = 'name_es = ?';
            $params[] = sanitize((string) $data['name_es'], 200);
        }
        if (isset($data['service_type'])) {
            $fields[] = 'service_type = ?';
            $params[] = sanitize((string) $data['service_type'], 100) ?: null;
        }
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = ?';
            $params[] = (int) (bool) $data['is_active'];
        }
        if (isset($data['sort_order'])) {
            $fields[] = 'sort_order = ?';
            $params[] = (int) $data['sort_order'];
        }
        if (isset($data['items']) && is_array($data['items'])) {
            $cleanItems = [];
            foreach ($data['items'] as $item) {
                $cleanItems[] = [
                    'type'           => sanitize((string) ($item['type'] ?? 'labor'), 20),
                    'description_en' => sanitize((string) ($item['description_en'] ?? ''), 500),
                    'description_es' => sanitize((string) ($item['description_es'] ?? ''), 500),
                    'quantity'       => max(0.01, (float) ($item['quantity'] ?? 1)),
                    'unit_price'     => (float) ($item['unit_price'] ?? 0),
                    'is_taxable'     => (bool) ($item['is_taxable'] ?? false),
                ];
            }
            $fields[] = 'items = ?';
            $params[] = json_encode($cleanItems);
        }

        if (empty($fields)) {
            jsonError('No fields to update.');
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;
        $db->prepare('UPDATE oretir_estimate_templates SET ' . implode(', ', $fields) . ' WHERE id = ?')
           ->execute($params);

        jsonSuccess(['message' => 'Template updated.']);
    }

    // ─── DELETE: Remove template ──────────────────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) jsonError('Template ID is required.');

        $stmt = $db->prepare('DELETE FROM oretir_estimate_templates WHERE id = ?');
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            jsonError('Template not found.', 404);
        }

        jsonSuccess(['message' => 'Template deleted.']);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/estimate-templates.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
