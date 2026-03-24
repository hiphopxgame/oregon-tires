<?php
/**
 * Oregon Tires — Admin Parts Catalog CRUD
 * GET    — list/search parts
 * POST   — create part
 * PUT    — update part
 * DELETE — deactivate part
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $staff = requirePermission('shop_ops');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        session_write_close();

        $search = sanitize((string) ($_GET['search'] ?? ''), 200);
        $category = sanitize((string) ($_GET['category'] ?? ''), 100);
        $vendorId = (int) ($_GET['vendor_id'] ?? 0);
        $limit = max(1, min(500, (int) ($_GET['limit'] ?? 100)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $autocomplete = ($_GET['autocomplete'] ?? '') === '1';

        $where = ['p.is_active = 1'];
        $params = [];

        if ($search !== '') {
            $where[] = '(p.part_number LIKE ? OR p.name LIKE ? OR p.name_es LIKE ?)';
            $like = "%{$search}%";
            $params = array_merge($params, [$like, $like, $like]);
        }

        if ($category !== '') {
            $where[] = 'p.category = ?';
            $params[] = $category;
        }

        if ($vendorId > 0) {
            $where[] = 'p.vendor_id = ?';
            $params[] = $vendorId;
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);

        if ($autocomplete) {
            // Lightweight response for autocomplete
            $stmt = $db->prepare(
                "SELECT p.id, p.part_number, p.name, p.default_price, p.cost_price, p.in_stock, v.name AS vendor_name
                 FROM oretir_parts_catalog p
                 LEFT JOIN oretir_vendors v ON p.vendor_id = v.id
                 {$whereStr}
                 ORDER BY p.name ASC LIMIT 20"
            );
            $stmt->execute($params);
            jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }

        // Full listing with count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM oretir_parts_catalog p {$whereStr}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $dataParams = array_merge($params, [$limit, $offset]);
        $stmt = $db->prepare(
            "SELECT p.*, v.name AS vendor_name
             FROM oretir_parts_catalog p
             LEFT JOIN oretir_vendors v ON p.vendor_id = v.id
             {$whereStr}
             ORDER BY p.name ASC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($dataParams);
        $parts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get categories for filter
        $catStmt = $db->query("SELECT DISTINCT category FROM oretir_parts_catalog WHERE category IS NOT NULL AND category != '' AND is_active = 1 ORDER BY category");
        $categories = $catStmt->fetchAll(\PDO::FETCH_COLUMN);

        jsonSuccess([
            'parts' => $parts,
            'categories' => $categories,
            'total' => $total,
        ]);
    }

    verifyCsrf();

    if ($method === 'POST') {
        $data = getJsonBody();
        $partNumber = sanitize((string) ($data['part_number'] ?? ''), 100);
        $name = sanitize((string) ($data['name'] ?? ''), 300);
        if ($name === '') jsonError('Part name is required.');

        $stmt = $db->prepare(
            'INSERT INTO oretir_parts_catalog (part_number, name, name_es, category, default_price, cost_price, vendor_id, in_stock, min_stock, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $partNumber,
            $name,
            sanitize((string) ($data['name_es'] ?? ''), 300),
            sanitize((string) ($data['category'] ?? ''), 100),
            !empty($data['default_price']) ? (float) $data['default_price'] : null,
            !empty($data['cost_price']) ? (float) $data['cost_price'] : null,
            !empty($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            !empty($data['in_stock']) ? 1 : 0,
            (int) ($data['min_stock'] ?? 0),
            sanitize((string) ($data['notes'] ?? ''), 2000),
        ]);

        $id = (int) $db->lastInsertId();
        $newStmt = $db->prepare('SELECT p.*, v.name AS vendor_name FROM oretir_parts_catalog p LEFT JOIN oretir_vendors v ON p.vendor_id = v.id WHERE p.id = ?');
        $newStmt->execute([$id]);
        jsonSuccess($newStmt->fetch(\PDO::FETCH_ASSOC), 201);
    }

    if ($method === 'PUT') {
        $data = getJsonBody();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing part id.');

        $name = sanitize((string) ($data['name'] ?? ''), 300);
        if ($name === '') jsonError('Part name is required.');

        $stmt = $db->prepare(
            'UPDATE oretir_parts_catalog SET part_number = ?, name = ?, name_es = ?, category = ?, default_price = ?, cost_price = ?, vendor_id = ?, in_stock = ?, min_stock = ?, notes = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([
            sanitize((string) ($data['part_number'] ?? ''), 100),
            $name,
            sanitize((string) ($data['name_es'] ?? ''), 300),
            sanitize((string) ($data['category'] ?? ''), 100),
            !empty($data['default_price']) ? (float) $data['default_price'] : null,
            !empty($data['cost_price']) ? (float) $data['cost_price'] : null,
            !empty($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            !empty($data['in_stock']) ? 1 : 0,
            (int) ($data['min_stock'] ?? 0),
            sanitize((string) ($data['notes'] ?? ''), 2000),
            $id,
        ]);

        $updStmt = $db->prepare('SELECT p.*, v.name AS vendor_name FROM oretir_parts_catalog p LEFT JOIN oretir_vendors v ON p.vendor_id = v.id WHERE p.id = ?');
        $updStmt->execute([$id]);
        jsonSuccess($updStmt->fetch(\PDO::FETCH_ASSOC));
    }

    if ($method === 'DELETE') {
        $data = getJsonBody();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing part id.');

        $db->prepare('UPDATE oretir_parts_catalog SET is_active = 0, updated_at = NOW() WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log("Admin parts-catalog error: " . $e->getMessage());
    jsonError('Server error', 500);
}
