<?php
/**
 * Oregon Tires — Admin Vendor CRUD
 * GET    — list vendors (with search)
 * POST   — create vendor
 * PUT    — update vendor
 * DELETE — deactivate vendor
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
        $activeOnly = ($_GET['active'] ?? '1') === '1';

        $where = [];
        $params = [];

        if ($activeOnly) {
            $where[] = 'is_active = 1';
        }

        if ($search !== '') {
            $where[] = '(name LIKE ? OR contact_name LIKE ? OR email LIKE ? OR account_number LIKE ?)';
            $like = "%{$search}%";
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("SELECT * FROM oretir_vendors {$whereStr} ORDER BY name ASC");
        $stmt->execute($params);
        $vendors = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        jsonSuccess($vendors);
    }

    verifyCsrf();

    if ($method === 'POST') {
        $data = getJsonBody();
        $name = sanitize((string) ($data['name'] ?? ''), 200);
        if ($name === '') jsonError('Vendor name is required.');

        $stmt = $db->prepare(
            'INSERT INTO oretir_vendors (name, contact_name, email, phone, website, account_number, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            sanitize((string) ($data['contact_name'] ?? ''), 200),
            sanitize((string) ($data['email'] ?? ''), 254),
            sanitize((string) ($data['phone'] ?? ''), 30),
            sanitize((string) ($data['website'] ?? ''), 500),
            sanitize((string) ($data['account_number'] ?? ''), 100),
            sanitize((string) ($data['notes'] ?? ''), 2000),
        ]);

        $id = (int) $db->lastInsertId();
        $newStmt = $db->prepare('SELECT * FROM oretir_vendors WHERE id = ?');
        $newStmt->execute([$id]);
        jsonSuccess($newStmt->fetch(\PDO::FETCH_ASSOC), 201);
    }

    if ($method === 'PUT') {
        $data = getJsonBody();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing vendor id.');

        $name = sanitize((string) ($data['name'] ?? ''), 200);
        if ($name === '') jsonError('Vendor name is required.');

        $stmt = $db->prepare(
            'UPDATE oretir_vendors SET name = ?, contact_name = ?, email = ?, phone = ?, website = ?, account_number = ?, notes = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([
            $name,
            sanitize((string) ($data['contact_name'] ?? ''), 200),
            sanitize((string) ($data['email'] ?? ''), 254),
            sanitize((string) ($data['phone'] ?? ''), 30),
            sanitize((string) ($data['website'] ?? ''), 500),
            sanitize((string) ($data['account_number'] ?? ''), 100),
            sanitize((string) ($data['notes'] ?? ''), 2000),
            $id,
        ]);

        $updStmt = $db->prepare('SELECT * FROM oretir_vendors WHERE id = ?');
        $updStmt->execute([$id]);
        jsonSuccess($updStmt->fetch(\PDO::FETCH_ASSOC));
    }

    if ($method === 'DELETE') {
        $data = getJsonBody();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing vendor id.');

        $db->prepare('UPDATE oretir_vendors SET is_active = 0, updated_at = NOW() WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => true]);
    }

} catch (\Throwable $e) {
    error_log("Admin vendors error: " . $e->getMessage());
    jsonError('Server error', 500);
}
