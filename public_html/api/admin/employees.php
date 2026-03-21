<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $staff = requirePermission('team');
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT e.*, g.name_en AS group_name, g.name_es AS group_name_es
             FROM oretir_employees e
             LEFT JOIN oretir_employee_groups g ON g.id = e.group_id
             ORDER BY e.name ASC'
        );
        $employees = $stmt->fetchAll();

        // Attach skills per employee
        try {
            $skillRows = $db->query('SELECT employee_id, service_type FROM oretir_employee_skills ORDER BY employee_id, service_type')->fetchAll();
            $skillMap = [];
            foreach ($skillRows as $sr) {
                $skillMap[(int)$sr['employee_id']][] = $sr['service_type'];
            }
            foreach ($employees as &$emp) {
                $emp['skills'] = $skillMap[(int)$emp['id']] ?? [];
            }
        } catch (\Throwable $e) {
            // Table may not exist yet
            foreach ($employees as &$emp) { $emp['skills'] = []; }
        }
        jsonSuccess($employees);
    }

    // POST/PUT require admin
    if ($staff['type'] !== 'admin') {
        jsonError('Admin access required.', 403);
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

        $groupId = isset($body['group_id']) ? (int) $body['group_id'] : null;

        $stmt = $db->prepare('INSERT INTO oretir_employees (name, email, phone, role, group_id, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())');
        $stmt->execute([$name, $email, $phone, $role, $groupId]);

        $newEmpId = (int) $db->lastInsertId();

        // Auto-seed default weekly schedule (Mon-Sat 08:00-17:00, Sun off)
        try {
            $schedStmt = $db->prepare(
                "INSERT INTO oretir_schedules (employee_id, day_of_week, start_time, end_time, is_available, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE updated_at = NOW()"
            );
            for ($dow = 0; $dow <= 6; $dow++) {
                $schedStmt->execute([$newEmpId, $dow, '08:00:00', '17:00:00', $dow === 0 ? 0 : 1]);
            }
        } catch (\Throwable $schedErr) {
            // Schedule tables may not exist yet — graceful degradation
            error_log("employees.php: auto-seed schedule failed for employee #{$newEmpId}: " . $schedErr->getMessage());
        }

        // Auto-seed all service skills for new employee (from DB, not hardcoded)
        try {
            $svcStmt = $db->query('SELECT slug FROM oretir_services WHERE is_active = 1 ORDER BY sort_order ASC');
            $allServices = $svcStmt->fetchAll(\PDO::FETCH_COLUMN);
            if (empty($allServices)) {
                // Fallback if services table is empty
                $allServices = ['tire-installation','tire-repair','wheel-alignment','oil-change',
                                'brake-service','engine-diagnostics','suspension-repair','mobile-service','roadside-assistance','other'];
            }
            $skillStmt = $db->prepare(
                'INSERT INTO oretir_employee_skills (employee_id, service_type) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE certified_at = certified_at'
            );
            foreach ($allServices as $svc) { $skillStmt->execute([$newEmpId, $svc]); }
        } catch (\Throwable $e) {
            error_log("employees.php: auto-seed skills failed for #{$newEmpId}: " . $e->getMessage());
        }

        jsonSuccess(['id' => $newEmpId], 201);
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

    if (array_key_exists('group_id', $body)) {
        $fields[] = 'group_id = ?';
        $params[] = $body['group_id'] ? (int) $body['group_id'] : null;
    }

    if (isset($body['max_daily_appointments'])) {
        $fields[] = 'max_daily_appointments = ?';
        $params[] = max(1, min(30, (int) $body['max_daily_appointments']));
    }

    // Handle skill updates (separate from main field updates)
    if (isset($body['skills']) && is_array($body['skills'])) {
        try {
            $db->prepare('DELETE FROM oretir_employee_skills WHERE employee_id = ?')->execute([$id]);
            $skillStmt = $db->prepare('INSERT INTO oretir_employee_skills (employee_id, service_type) VALUES (?, ?)');
            foreach ($body['skills'] as $svc) {
                $svc = sanitize((string) $svc, 50);
                if ($svc) $skillStmt->execute([$id, $svc]);
            }
        } catch (\Throwable $e) {
            error_log("employees.php: skill update failed for #{$id}: " . $e->getMessage());
        }
    }

    if (empty($fields)) {
        // Skills may have been the only update
        if (isset($body['skills'])) {
            jsonSuccess(['updated' => $id]);
        }
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
