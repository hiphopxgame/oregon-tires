<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $staff = requirePermission('team');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET — list all groups (any staff with team permission)
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT g.*, COUNT(e.id) AS employee_count
             FROM oretir_employee_groups g
             LEFT JOIN oretir_employees e ON e.group_id = g.id AND e.is_active = 1
             GROUP BY g.id
             ORDER BY g.is_default DESC, g.name_en ASC'
        );
        jsonSuccess($stmt->fetchAll());
    }

    // POST/PUT/DELETE require admin
    if ($staff['type'] !== 'admin') {
        jsonError('Admin access required.', 403);
    }
    verifyCsrf();

    // DELETE — remove a custom group
    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id < 1) jsonError('Missing group id.', 400);

        // Cannot delete system groups
        $stmt = $db->prepare('SELECT is_system FROM oretir_employee_groups WHERE id = ?');
        $stmt->execute([$id]);
        $group = $stmt->fetch();
        if (!$group) jsonError('Group not found.', 404);
        if ($group['is_system']) jsonError('Cannot delete system groups.', 400);

        // Reassign employees to default group
        $defStmt = $db->query('SELECT id FROM oretir_employee_groups WHERE is_default = 1 LIMIT 1');
        $defaultId = $defStmt->fetchColumn();
        if ($defaultId) {
            $db->prepare('UPDATE oretir_employees SET group_id = ? WHERE group_id = ?')
               ->execute([$defaultId, $id]);
        }

        $db->prepare('DELETE FROM oretir_employee_groups WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => $id]);
    }

    $body = getJsonBody();

    // POST — create new group
    if ($method === 'POST') {
        $missing = requireFields($body, ['name_en', 'permissions']);
        if (!empty($missing)) jsonError('Missing required fields: ' . implode(', ', $missing), 400);

        $nameEn = sanitize($body['name_en'], 100);
        $nameEs = sanitize($body['name_es'] ?? '', 100);
        $descEn = sanitize($body['description_en'] ?? '', 255);
        $descEs = sanitize($body['description_es'] ?? '', 255);
        $perms  = $body['permissions'];

        if (!is_array($perms) || empty($perms)) {
            jsonError('Permissions must be a non-empty array.', 400);
        }

        // Validate permission bundle names
        $validBundles = ['my_work', 'shop_ops', 'customers', 'messaging', 'team', 'marketing', 'settings'];
        foreach ($perms as $p) {
            if (!in_array($p, $validBundles, true)) {
                jsonError("Invalid permission bundle: $p", 400);
            }
        }

        // Always include my_work
        if (!in_array('my_work', $perms, true)) {
            array_unshift($perms, 'my_work');
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_employee_groups (name_en, name_es, description_en, description_es, permissions, is_default, is_system)
             VALUES (?, ?, ?, ?, ?, 0, 0)'
        );
        $stmt->execute([$nameEn, $nameEs, $descEn, $descEs, json_encode($perms)]);
        jsonSuccess(['id' => (int) $db->lastInsertId()], 201);
    }

    // PUT — update group
    $id = (int) ($body['id'] ?? 0);
    if ($id < 1) jsonError('Missing group id.', 400);

    // Check group exists
    $stmt = $db->prepare('SELECT is_system FROM oretir_employee_groups WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) jsonError('Group not found.', 404);

    $fields = [];
    $params = [];

    if (isset($body['name_en'])) {
        $fields[] = 'name_en = ?';
        $params[] = sanitize($body['name_en'], 100);
    }
    if (isset($body['name_es'])) {
        $fields[] = 'name_es = ?';
        $params[] = sanitize($body['name_es'], 100);
    }
    if (isset($body['description_en'])) {
        $fields[] = 'description_en = ?';
        $params[] = sanitize($body['description_en'], 255);
    }
    if (isset($body['description_es'])) {
        $fields[] = 'description_es = ?';
        $params[] = sanitize($body['description_es'], 255);
    }
    if (isset($body['permissions'])) {
        $perms = $body['permissions'];
        if (!is_array($perms) || empty($perms)) {
            jsonError('Permissions must be a non-empty array.', 400);
        }
        $validBundles = ['my_work', 'shop_ops', 'customers', 'messaging', 'team', 'marketing', 'settings'];
        foreach ($perms as $p) {
            if (!in_array($p, $validBundles, true)) {
                jsonError("Invalid permission bundle: $p", 400);
            }
        }
        if (!in_array('my_work', $perms, true)) {
            array_unshift($perms, 'my_work');
        }
        $fields[] = 'permissions = ?';
        $params[] = json_encode($perms);
    }

    if (empty($fields)) jsonError('No fields to update.', 400);

    $fields[] = 'updated_at = NOW()';
    $params[] = $id;

    $sql = 'UPDATE oretir_employee_groups SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $db->prepare($sql)->execute($params);
    jsonSuccess(['updated' => $id]);

} catch (\Throwable $e) {
    error_log('employee-groups.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
