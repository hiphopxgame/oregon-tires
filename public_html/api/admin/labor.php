<?php
/**
 * Oregon Tires — Admin Labor Time Tracking
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
    $staff = requireStaff();
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // Active RO statuses (not completed/cancelled/invoiced)
    $activeStatuses = ['intake', 'diagnosis', 'estimate_pending', 'pending_approval', 'approved', 'in_progress', 'waiting_parts', 'ready'];

    // ─── GET: List labor entries ────────────────────────────────────────────
    if ($method === 'GET') {

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
                'SELECT l.*, e.name as employee_name, r.ro_number
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 JOIN oretir_repair_orders r ON r.id = l.repair_order_id
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
