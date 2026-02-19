<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'PUT', 'POST');
    $admin = requireAdmin();
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $db->query('SELECT a.*, e.name AS employee_name FROM oretir_appointments a LEFT JOIN oretir_employees e ON a.assigned_employee_id = e.id ORDER BY a.created_at DESC LIMIT 1000');
        jsonSuccess($stmt->fetchAll());
    }

    verifyCsrf();
    $body = getJsonBody();

    if ($method === 'PUT') {
        $id = (int) ($body['id'] ?? 0);
        if ($id < 1) {
            jsonError('Missing appointment id.', 400);
        }

        $validStatuses = ['new', 'pending', 'confirmed', 'completed', 'cancelled'];
        $fields = [];
        $params = [];

        if (isset($body['status'])) {
            if (!in_array($body['status'], $validStatuses, true)) {
                jsonError('Invalid status value.', 400);
            }
            $fields[] = 'status = ?';
            $params[] = $body['status'];
        }

        if (array_key_exists('assigned_employee_id', $body)) {
            $fields[] = 'assigned_employee_id = ?';
            $params[] = $body['assigned_employee_id'] ? (int) $body['assigned_employee_id'] : null;
        }

        if (isset($body['admin_notes'])) {
            $fields[] = 'admin_notes = ?';
            $params[] = sanitize($body['admin_notes'], 2000);
        }

        if (empty($fields)) {
            jsonError('No fields to update.', 400);
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $sql = 'UPDATE oretir_appointments SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $db->prepare($sql)->execute($params);

        jsonSuccess(['updated' => $id]);
    }

    // POST — bulk operations
    $action = $body['action'] ?? '';
    $ids = $body['ids'] ?? [];

    if (!is_array($ids) || empty($ids)) {
        jsonError('Missing or empty ids array.', 400);
    }

    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, fn(int $v) => $v > 0);

    if (empty($ids)) {
        jsonError('No valid ids provided.', 400);
    }

    if ($action === 'bulk_status') {
        $validStatuses = ['new', 'pending', 'confirmed', 'completed', 'cancelled'];
        $status = $body['status'] ?? '';
        if (!in_array($status, $validStatuses, true)) {
            jsonError('Invalid status value.', 400);
        }
        $stmt = $db->prepare('UPDATE oretir_appointments SET status = ?, updated_at = NOW() WHERE id = ?');
        foreach ($ids as $id) {
            $stmt->execute([$status, $id]);
        }
        jsonSuccess(['updated' => count($ids)]);
    }

    if ($action === 'bulk_assign') {
        $employeeId = isset($body['employee_id']) ? (int) $body['employee_id'] : null;
        $stmt = $db->prepare('UPDATE oretir_appointments SET assigned_employee_id = ?, updated_at = NOW() WHERE id = ?');
        foreach ($ids as $id) {
            $stmt->execute([$employeeId, $id]);
        }
        jsonSuccess(['updated' => count($ids)]);
    }

    jsonError('Invalid action.', 400);

} catch (\Throwable $e) {
    error_log('appointments.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
