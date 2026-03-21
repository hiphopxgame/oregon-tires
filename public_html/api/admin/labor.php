<?php
/**
 * Oregon Tires — Admin Labor Time Tracking
 * GET    /api/admin/labor.php?summary=1                        — cross-RO employee labor summary
 * GET    /api/admin/labor.php?ro_id=N                         — entries for a repair order
 * GET    /api/admin/labor.php?employee_id=N&start_date=&end_date= — entries for an employee in date range
 * POST   /api/admin/labor.php                                 — clock in
 * PUT    /api/admin/labor.php                                 — clock out or update entry
 * DELETE /api/admin/labor.php?id=N                            — delete entry (admin only)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    $staff = requirePermission('team');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // Active RO statuses (not completed/cancelled/invoiced)
    $activeStatuses = ['intake', 'diagnosis', 'estimate_pending', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'waiting_parts', 'ready'];

    // ─── GET: List labor entries ────────────────────────────────────────────
    if ($method === 'GET') {

        // Cross-RO labor dashboard (for the dedicated Labor tab)
        if (!empty($_GET['summary'])) {

            // 1. Per-employee summary
            $stmt = $db->query(
                'SELECT
                    e.id   AS employee_id,
                    e.name AS employee_name,
                    ROUND(COALESCE(SUM(l.duration_minutes), 0) / 60, 2) AS total_hours,
                    ROUND(COALESCE(SUM(CASE WHEN l.is_billable = 1 THEN l.duration_minutes ELSE 0 END), 0) / 60, 2) AS billable_hours,
                    SUM(CASE WHEN l.clock_out_at IS NULL THEN 1 ELSE 0 END) AS active_count,
                    COUNT(DISTINCT l.repair_order_id) AS ro_count
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 GROUP BY e.id, e.name
                 ORDER BY e.name'
            );
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($employees as &$r) {
                $r['total_hours']    = (float) $r['total_hours'];
                $r['billable_hours'] = (float) $r['billable_hours'];
                $r['active_count']   = (int) $r['active_count'];
                $r['ro_count']       = (int) $r['ro_count'];
            }
            unset($r);

            // 2. Active clocks (currently clocked in) with full context
            $active = $db->query(
                'SELECT l.id, l.repair_order_id, l.employee_id, l.clock_in_at,
                        l.task_description, l.is_billable,
                        e.name AS employee_name, e.role AS employee_role,
                        r.ro_number, r.status AS ro_status,
                        c.first_name AS customer_first, c.last_name AS customer_last, c.phone AS customer_phone,
                        v.year AS vehicle_year, v.make AS vehicle_make, v.model AS vehicle_model, v.license_plate
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 JOIN oretir_repair_orders r ON r.id = l.repair_order_id
                 LEFT JOIN oretir_customers c ON c.id = r.customer_id
                 LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                 WHERE l.clock_out_at IS NULL
                 ORDER BY l.clock_in_at ASC'
            )->fetchAll(PDO::FETCH_ASSOC);

            // 3. Recent completed entries (last 20) with full context
            $recent = $db->query(
                'SELECT l.id, l.repair_order_id, l.employee_id, l.clock_in_at, l.clock_out_at,
                        l.duration_minutes, l.task_description, l.is_billable,
                        e.name AS employee_name, r.ro_number, r.status AS ro_status,
                        c.first_name AS customer_first, c.last_name AS customer_last,
                        v.year AS vehicle_year, v.make AS vehicle_make, v.model AS vehicle_model
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 JOIN oretir_repair_orders r ON r.id = l.repair_order_id
                 LEFT JOIN oretir_customers c ON c.id = r.customer_id
                 LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                 WHERE l.clock_out_at IS NOT NULL
                 ORDER BY l.clock_out_at DESC
                 LIMIT 20'
            )->fetchAll(PDO::FETCH_ASSOC);

            // 4. Active employees + active ROs for the clock-in form
            $availableEmployees = $db->query(
                'SELECT id, name FROM oretir_employees WHERE is_active = 1 ORDER BY name'
            )->fetchAll(PDO::FETCH_ASSOC);

            $placeholders = implode(',', array_fill(0, count($activeStatuses), '?'));
            $roStmt = $db->prepare(
                "SELECT id, ro_number, status FROM oretir_repair_orders
                 WHERE status IN ($placeholders) ORDER BY created_at DESC"
            );
            $roStmt->execute($activeStatuses);
            $availableROs = $roStmt->fetchAll(PDO::FETCH_ASSOC);

            // 5. Totals
            $totals = $db->query(
                'SELECT
                    COUNT(*) AS total_entries,
                    ROUND(COALESCE(SUM(duration_minutes), 0) / 60, 2) AS total_hours,
                    ROUND(COALESCE(SUM(CASE WHEN is_billable = 1 THEN duration_minutes ELSE 0 END), 0) / 60, 2) AS billable_hours,
                    SUM(CASE WHEN clock_out_at IS NULL THEN 1 ELSE 0 END) AS active_clocks
                 FROM oretir_labor_entries'
            )->fetch(PDO::FETCH_ASSOC);
            $totals['total_hours']    = (float) $totals['total_hours'];
            $totals['billable_hours'] = (float) $totals['billable_hours'];
            $totals['active_clocks']  = (int) $totals['active_clocks'];
            $totals['total_entries']  = (int) $totals['total_entries'];

            jsonSuccess([
                'employees'  => $employees,
                'active'     => $active,
                'recent'     => $recent,
                'available_employees' => $availableEmployees,
                'available_ros'       => $availableROs,
                'totals'     => $totals,
            ]);
        }

        // Entries for a specific repair order
        if (!empty($_GET['ro_id'])) {
            $roId = (int) $_GET['ro_id'];

            $stmt = $db->prepare(
                'SELECT l.*, e.name as employee_name
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 WHERE l.repair_order_id = ?
                 ORDER BY l.clock_in_at DESC'
            );
            $stmt->execute([$roId]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compute summaries
            $totalMinutes = 0;
            $billableMinutes = 0;
            foreach ($entries as $entry) {
                $mins = $entry['duration_minutes'] !== null ? (int) $entry['duration_minutes'] : 0;
                $totalMinutes += $mins;
                if ($entry['is_billable']) {
                    $billableMinutes += $mins;
                }
            }

            jsonSuccess([
                'entries'         => $entries,
                'total_hours'     => round($totalMinutes / 60, 2),
                'billable_hours'  => round($billableMinutes / 60, 2),
                'total_minutes'   => $totalMinutes,
                'billable_minutes'=> $billableMinutes,
            ]);
        }

        // Entries for an employee in a date range
        if (!empty($_GET['employee_id'])) {
            $employeeId = (int) $_GET['employee_id'];
            $startDate  = sanitize((string) ($_GET['start_date'] ?? ''), 10);
            $endDate    = sanitize((string) ($_GET['end_date'] ?? ''), 10);

            if (empty($startDate) || empty($endDate)) {
                // Default to current week (Monday–Sunday)
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate   = date('Y-m-d', strtotime('sunday this week'));
            }

            $stmt = $db->prepare(
                'SELECT l.*, e.name as employee_name, r.ro_number, r.status AS ro_status,
                        c.first_name AS customer_first, c.last_name AS customer_last,
                        v.year AS vehicle_year, v.make AS vehicle_make, v.model AS vehicle_model
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 JOIN oretir_repair_orders r ON r.id = l.repair_order_id
                 LEFT JOIN oretir_customers c ON c.id = r.customer_id
                 LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                 WHERE l.employee_id = ?
                   AND DATE(l.clock_in_at) >= ?
                   AND DATE(l.clock_in_at) <= ?
                 ORDER BY l.clock_in_at DESC'
            );
            $stmt->execute([$employeeId, $startDate, $endDate]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalMinutes = 0;
            $billableMinutes = 0;
            foreach ($entries as $entry) {
                $mins = $entry['duration_minutes'] !== null ? (int) $entry['duration_minutes'] : 0;
                $totalMinutes += $mins;
                if ($entry['is_billable']) {
                    $billableMinutes += $mins;
                }
            }

            jsonSuccess([
                'entries'         => $entries,
                'total_hours'     => round($totalMinutes / 60, 2),
                'billable_hours'  => round($billableMinutes / 60, 2),
                'total_minutes'   => $totalMinutes,
                'billable_minutes'=> $billableMinutes,
                'start_date'      => $startDate,
                'end_date'        => $endDate,
            ]);
        }

        jsonError('Provide ro_id or employee_id parameter.', 400);
    }

    // ─── POST: Clock in ─────────────────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $roId       = (int) ($data['repair_order_id'] ?? 0);
        $employeeId = (int) ($data['employee_id'] ?? 0);

        if ($roId <= 0) jsonError('repair_order_id is required.', 400);
        if ($employeeId <= 0) jsonError('employee_id is required.', 400);

        // Verify RO exists and is in an active status
        $roStmt = $db->prepare('SELECT id, status, ro_number FROM oretir_repair_orders WHERE id = ?');
        $roStmt->execute([$roId]);
        $ro = $roStmt->fetch(PDO::FETCH_ASSOC);
        if (!$ro) jsonError('Repair order not found.', 404);

        if (!in_array($ro['status'], $activeStatuses, true)) {
            jsonError('Cannot clock in on a ' . $ro['status'] . ' repair order.', 400);
        }

        // Verify employee exists
        $empStmt = $db->prepare('SELECT id, name FROM oretir_employees WHERE id = ? AND is_active = 1');
        $empStmt->execute([$employeeId]);
        $emp = $empStmt->fetch(PDO::FETCH_ASSOC);
        if (!$emp) jsonError('Employee not found or inactive.', 404);

        // Check no open clock-in already exists for this employee on this RO
        $openStmt = $db->prepare(
            'SELECT id FROM oretir_labor_entries
             WHERE repair_order_id = ? AND employee_id = ? AND clock_out_at IS NULL
             LIMIT 1'
        );
        $openStmt->execute([$roId, $employeeId]);
        if ($openStmt->fetch()) {
            jsonError('Employee already has an open clock-in on this repair order. Clock out first.', 409);
        }

        $taskDescription = isset($data['task_description']) ? sanitize((string) $data['task_description'], 500) : null;
        $isBillable      = (int) ($data['is_billable'] ?? 1);

        $stmt = $db->prepare(
            'INSERT INTO oretir_labor_entries (repair_order_id, employee_id, clock_in_at, is_billable, task_description, created_at, updated_at)
             VALUES (?, ?, NOW(), ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$roId, $employeeId, $isBillable, $taskDescription ?: null]);

        $entryId = (int) $db->lastInsertId();

        jsonSuccess([
            'id'          => $entryId,
            'employee'    => $emp['name'],
            'ro_number'   => $ro['ro_number'],
            'message'     => 'Clocked in successfully.',
        ], 201);
    }

    // ─── PUT: Clock out or update entry ─────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Labor entry id is required.', 400);

        // Fetch existing entry
        $entryStmt = $db->prepare('SELECT * FROM oretir_labor_entries WHERE id = ?');
        $entryStmt->execute([$id]);
        $entry = $entryStmt->fetch(PDO::FETCH_ASSOC);
        if (!$entry) jsonError('Labor entry not found.', 404);

        $fields = [];
        $params = [];

        // Clock out
        if (isset($data['clock_out']) || isset($data['clock_out_at'])) {
            if ($entry['clock_out_at'] !== null && !isset($data['clock_out_at'])) {
                jsonError('Entry is already clocked out.', 400);
            }

            if (isset($data['clock_out_at']) && $data['clock_out_at'] !== null) {
                $clockOut = sanitize((string) $data['clock_out_at'], 30);
                // Validate clock_out_at > clock_in_at
                if (strtotime($clockOut) <= strtotime($entry['clock_in_at'])) {
                    jsonError('Clock out time must be after clock in time.', 400);
                }
                $fields[] = 'clock_out_at = ?';
                $params[] = $clockOut;
            } else {
                // Set to NOW()
                $now = date('Y-m-d H:i:s');
                if (strtotime($now) <= strtotime($entry['clock_in_at'])) {
                    jsonError('Clock out time must be after clock in time.', 400);
                }
                $fields[] = 'clock_out_at = NOW()';
            }
        }

        // Update task description
        if (isset($data['task_description'])) {
            $fields[] = 'task_description = ?';
            $params[] = sanitize((string) $data['task_description'], 500) ?: null;
        }

        // Update billable flag
        if (isset($data['is_billable'])) {
            $fields[] = 'is_billable = ?';
            $params[] = (int) $data['is_billable'];
        }

        if (empty($fields)) {
            jsonError('No fields to update.', 400);
        }

        $params[] = $id;
        $sql = 'UPDATE oretir_labor_entries SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $db->prepare($sql)->execute($params);

        jsonSuccess(['message' => 'Labor entry updated.']);
    }

    // ─── DELETE: Remove entry (admin only) ──────────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();

        if ($staff['type'] !== 'admin') {
            jsonError('Only admins can delete labor entries.', 403);
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            // Also check JSON body for id
            $data = getJsonBody();
            $id = (int) ($data['id'] ?? 0);
        }
        if ($id <= 0) jsonError('Labor entry id is required.', 400);

        // Verify entry exists
        $entryStmt = $db->prepare('SELECT id FROM oretir_labor_entries WHERE id = ?');
        $entryStmt->execute([$id]);
        if (!$entryStmt->fetch()) {
            jsonError('Labor entry not found.', 404);
        }

        $db->prepare('DELETE FROM oretir_labor_entries WHERE id = ?')->execute([$id]);

        jsonSuccess(['message' => 'Labor entry deleted.']);
    }

} catch (\Throwable $e) {
    error_log('Oregon Tires api/admin/labor.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
