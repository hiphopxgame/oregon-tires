<?php
/**
 * Oregon Tires — Employee Schedule Management API
 * GET  /api/admin/schedules.php?type=weekly|daily|overrides
 * POST /api/admin/schedules.php  (action: set_weekly | set_override)
 * DELETE /api/admin/schedules.php (id: override id)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $admin = requireAdmin();
    requireMethod('GET', 'POST', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET ────────────────────────────────────────────────────────────
    if ($method === 'GET') {
        $type = sanitize((string) ($_GET['type'] ?? 'weekly'), 20);

        if ($type === 'weekly') {
            // All employee weekly schedules grouped by employee
            $stmt = $db->query(
                "SELECT s.*, e.name AS employee_name, e.is_active
                 FROM oretir_schedules s
                 JOIN oretir_employees e ON s.employee_id = e.id
                 ORDER BY e.name ASC, s.day_of_week ASC"
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grouped = [];
            foreach ($rows as $row) {
                $empId = (int) $row['employee_id'];
                if (!isset($grouped[$empId])) {
                    $grouped[$empId] = [
                        'employee_id'   => $empId,
                        'employee_name' => $row['employee_name'],
                        'is_active'     => (bool) $row['is_active'],
                        'days'          => [],
                    ];
                }
                $grouped[$empId]['days'][] = [
                    'id'           => (int) $row['id'],
                    'day_of_week'  => (int) $row['day_of_week'],
                    'start_time'   => $row['start_time'],
                    'end_time'     => $row['end_time'],
                    'is_available' => (bool) $row['is_available'],
                ];
            }

            jsonSuccess(array_values($grouped));
        }

        if ($type === 'daily') {
            $date = sanitize((string) ($_GET['date'] ?? ''), 10);
            if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                jsonError('Invalid date format. Use YYYY-MM-DD.', 400);
            }

            $dayOfWeek = (int) (new DateTime($date))->format('w');

            // Shop-wide override
            $stmt = $db->prepare(
                "SELECT * FROM oretir_schedule_overrides
                 WHERE override_date = ? AND employee_id IS NULL LIMIT 1"
            );
            $stmt->execute([$date]);
            $shopOverride = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            // Employee schedules for this day + overrides
            $stmt = $db->prepare(
                "SELECT s.*, e.name AS employee_name,
                        ov.id AS override_id, ov.is_closed AS ov_is_closed,
                        ov.start_time AS ov_start_time, ov.end_time AS ov_end_time, ov.reason AS ov_reason
                 FROM oretir_schedules s
                 JOIN oretir_employees e ON s.employee_id = e.id AND e.is_active = 1
                 LEFT JOIN oretir_schedule_overrides ov ON ov.employee_id = s.employee_id AND ov.override_date = ?
                 WHERE s.day_of_week = ?
                 ORDER BY e.name ASC"
            );
            $stmt->execute([$date, $dayOfWeek]);
            $empSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Attach skills per employee
            $empIds = array_unique(array_column($empSchedules, 'employee_id'));
            $skillMap = [];
            if ($empIds) {
                try {
                    $in = implode(',', array_map('intval', $empIds));
                    $skillRows = $db->query("SELECT employee_id, service_type FROM oretir_employee_skills WHERE employee_id IN ({$in})")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($skillRows as $sr) {
                        $skillMap[(int)$sr['employee_id']][] = $sr['service_type'];
                    }
                } catch (\Throwable $e) { /* table may not exist */ }
            }
            foreach ($empSchedules as &$es) {
                $es['skills'] = $skillMap[(int)$es['employee_id']] ?? [];
            }
            unset($es);

            // Appointments for this date
            $stmt = $db->prepare(
                "SELECT a.id, a.preferred_time, a.service, a.first_name, a.last_name, a.status,
                        a.assigned_employee_id
                 FROM oretir_appointments a
                 WHERE a.preferred_date = ? AND a.status NOT IN ('cancelled', 'completed')
                 ORDER BY a.preferred_time ASC"
            );
            $stmt->execute([$date]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate capacity per slot
            $capacity = [];
            $workingCount = 0;
            foreach ($empSchedules as $es) {
                if ($es['ov_is_closed']) continue;
                if (!(bool) $es['is_available'] && !$es['override_id']) continue;

                $workingCount++;
                $start = (int) substr($es['ov_start_time'] ?? $es['start_time'], 0, 2);
                $end   = (int) substr($es['ov_end_time'] ?? $es['end_time'], 0, 2);

                for ($h = $start; $h < $end; $h++) {
                    foreach ([0, 15, 30, 45] as $m) {
                        $key = sprintf('%02d:%02d', $h, $m);
                        $capacity[$key] = ($capacity[$key] ?? 0) + 1;
                    }
                }
            }

            jsonSuccess([
                'date'           => $date,
                'day_of_week'    => $dayOfWeek,
                'shop_override'  => $shopOverride,
                'employees'      => $empSchedules,
                'appointments'   => $appointments,
                'slot_capacity'  => $capacity,
                'working_count'  => $workingCount,
            ]);
        }

        if ($type === 'overrides') {
            $from = sanitize((string) ($_GET['from'] ?? date('Y-m-d')), 10);
            $to   = sanitize((string) ($_GET['to'] ?? date('Y-m-d', strtotime('+90 days'))), 10);

            $stmt = $db->prepare(
                "SELECT ov.*, e.name AS employee_name
                 FROM oretir_schedule_overrides ov
                 LEFT JOIN oretir_employees e ON ov.employee_id = e.id
                 WHERE ov.override_date BETWEEN ? AND ?
                 ORDER BY ov.override_date ASC"
            );
            $stmt->execute([$from, $to]);
            jsonSuccess($stmt->fetchAll(PDO::FETCH_ASSOC));
        }

        jsonError('Invalid type parameter. Use: weekly, daily, overrides.', 400);
    }

    // ─── POST / DELETE require CSRF ─────────────────────────────────────
    verifyCsrf();

    // ─── DELETE ──────────────────────────────────────────────────────────
    if ($method === 'DELETE') {
        $body = getJsonBody();
        $id = (int) ($body['id'] ?? 0);
        if ($id < 1) {
            jsonError('Missing override id.', 400);
        }

        $stmt = $db->prepare('DELETE FROM oretir_schedule_overrides WHERE id = ?');
        $stmt->execute([$id]);

        jsonSuccess(['deleted' => $id]);
    }

    // ─── POST ───────────────────────────────────────────────────────────
    $body = getJsonBody();
    $action = sanitize((string) ($body['action'] ?? ''), 30);

    if ($action === 'set_weekly') {
        $employeeId = (int) ($body['employee_id'] ?? 0);
        if ($employeeId < 1) {
            jsonError('Missing employee_id.', 400);
        }

        $days = $body['days'] ?? [];
        if (!is_array($days) || empty($days)) {
            jsonError('Missing days array.', 400);
        }

        $stmt = $db->prepare(
            "INSERT INTO oretir_schedules (employee_id, day_of_week, start_time, end_time, is_available, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time),
                                     is_available = VALUES(is_available), updated_at = NOW()"
        );

        $count = 0;
        foreach ($days as $day) {
            $dow = (int) ($day['day_of_week'] ?? -1);
            if ($dow < 0 || $dow > 6) continue;

            $startTime   = sanitize((string) ($day['start_time'] ?? '08:00'), 8);
            $endTime     = sanitize((string) ($day['end_time'] ?? '17:00'), 8);
            $isAvailable = !empty($day['is_available']) ? 1 : 0;

            // Normalize time format
            if (preg_match('/^\d{2}:\d{2}$/', $startTime)) $startTime .= ':00';
            if (preg_match('/^\d{2}:\d{2}$/', $endTime)) $endTime .= ':00';

            $stmt->execute([$employeeId, $dow, $startTime, $endTime, $isAvailable]);
            $count++;
        }

        jsonSuccess(['updated' => $count, 'employee_id' => $employeeId]);
    }

    if ($action === 'set_override') {
        $employeeId   = !empty($body['employee_id']) ? (int) $body['employee_id'] : null;
        $overrideDate = sanitize((string) ($body['override_date'] ?? ''), 10);
        $isClosed     = !empty($body['is_closed']) ? 1 : 0;
        $startTime    = isset($body['start_time']) ? sanitize((string) $body['start_time'], 8) : null;
        $endTime      = isset($body['end_time']) ? sanitize((string) $body['end_time'], 8) : null;
        $reason       = sanitize((string) ($body['reason'] ?? ''), 255);

        if (!$overrideDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $overrideDate)) {
            jsonError('Invalid override_date.', 400);
        }

        // Normalize time format
        if ($startTime && preg_match('/^\d{2}:\d{2}$/', $startTime)) $startTime .= ':00';
        if ($endTime && preg_match('/^\d{2}:\d{2}$/', $endTime)) $endTime .= ':00';

        // Upsert: use REPLACE since we have unique on (employee_id, override_date)
        // But UNIQUE treats NULL differently, so we delete first then insert for shop-wide
        if ($employeeId === null) {
            $db->prepare(
                "DELETE FROM oretir_schedule_overrides WHERE employee_id IS NULL AND override_date = ?"
            )->execute([$overrideDate]);
        }

        $stmt = $db->prepare(
            "INSERT INTO oretir_schedule_overrides (employee_id, override_date, is_closed, start_time, end_time, reason, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE is_closed = VALUES(is_closed), start_time = VALUES(start_time),
                                     end_time = VALUES(end_time), reason = VALUES(reason), updated_at = NOW()"
        );
        $stmt->execute([$employeeId, $overrideDate, $isClosed, $startTime, $endTime, $reason ?: null]);

        $id = (int) $db->lastInsertId();

        jsonSuccess(['id' => $id, 'override_date' => $overrideDate]);
    }

    jsonError('Invalid action. Use: set_weekly, set_override.', 400);

} catch (\Throwable $e) {
    error_log('schedules.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
