<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'POST', 'PUT');
    $admin = requireAdmin();
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $db->query('SELECT * FROM oretir_employees ORDER BY name ASC');
        jsonSuccess($stmt->fetchAll());
    }

    verifyCsrf();
    $body = getJsonBody();

    if ($method === 'POST') {
        $missing = requireFields($body, ['name', 'role']);
        if (!empty($missing)) {
            jsonError('Missing required fields: ' . implode(', ', $missing), 400);
        }

        $name = sanitize($body['name'], 100);
        $email = isset($body['email']) ? sanitize($body['email'], 255) : null;
        $phone = isset($body['phone']) ? sanitize($body['phone'], 30) : null;
        $role = $body['role'];

        if (!in_array($role, ['Employee', 'Manager'], true)) {
            jsonError('Invalid role. Must be Employee or Manager.', 400);
        }

        if ($email && !isValidEmail($email)) {
            jsonError('Invalid email address.', 400);
        }

        $stmt = $db->prepare('INSERT INTO oretir_employees (name, email, phone, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())');
        $stmt->execute([$name, $email, $phone, $role]);

        jsonSuccess(['id' => (int) $db->lastInsertId()], 201);
    }

    // PUT
    $id = (int) ($body['id'] ?? 0);
    if ($id < 1) {
        jsonError('Missing employee id.', 400);
    }

    $fields = [];
    $params = [];

    if (isset($body['name'])) {
        $fields[] = 'name = ?';
        $params[] = sanitize($body['name'], 100);
    }

    if (array_key_exists('email', $body)) {
        $email = $body['email'] ? sanitize($body['email'], 255) : null;
        if ($email && !isValidEmail($email)) {
            jsonError('Invalid email address.', 400);
        }
        $fields[] = 'email = ?';
        $params[] = $email;
    }

    if (array_key_exists('phone', $body)) {
        $fields[] = 'phone = ?';
        $params[] = $body['phone'] ? sanitize($body['phone'], 30) : null;
    }

    if (isset($body['role'])) {
        if (!in_array($body['role'], ['Employee', 'Manager'], true)) {
            jsonError('Invalid role. Must be Employee or Manager.', 400);
        }
        $fields[] = 'role = ?';
        $params[] = $body['role'];
    }

    if (array_key_exists('is_active', $body)) {
        $fields[] = 'is_active = ?';
        $params[] = $body['is_active'] ? 1 : 0;
    }

    if (empty($fields)) {
        jsonError('No fields to update.', 400);
    }

    $fields[] = 'updated_at = NOW()';
    $params[] = $id;

    $sql = 'UPDATE oretir_employees SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $db->prepare($sql)->execute($params);

    jsonSuccess(['updated' => $id]);

} catch (\Throwable $e) {
    error_log('employees.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
