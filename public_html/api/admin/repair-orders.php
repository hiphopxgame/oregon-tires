<?php
/**
 * Oregon Tires — Admin Repair Order Management
 * GET    /api/admin/repair-orders.php              — List ROs (paginated, filterable)
 * GET    /api/admin/repair-orders.php?id=N         — Get single RO with full details
 * POST   /api/admin/repair-orders.php              — Create RO (from appointment or walk-in)
 * PUT    /api/admin/repair-orders.php              — Update RO (status, fields, etc.)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/vin-decode.php';

try {
    startSecureSession();
    $staff = requirePermission('shop_ops');
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List or single RO ──────────────────────────────────────────
    if ($method === 'GET') {

        // Single RO detail
        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $db->prepare(
                'SELECT r.*,
                    c.first_name, c.last_name, c.email as customer_email, c.phone as customer_phone, c.language as customer_language,
                    v.vin, v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model,
                    v.trim_level, v.engine, v.transmission, v.drive_type, v.body_class, v.fuel_type, v.doors,
                    v.tire_size_front, v.tire_size_rear, v.color as vehicle_color,
                    v.license_plate
                 FROM oretir_repair_orders r
                 JOIN oretir_customers c ON c.id = r.customer_id
                 LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                 WHERE r.id = ?'
            );
            $stmt->execute([$id]);
            $ro = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ro) jsonError('Repair order not found.', 404);

            // Inspections
            $iStmt = $db->prepare('SELECT * FROM oretir_inspections WHERE repair_order_id = ? ORDER BY created_at DESC');
            $iStmt->execute([$id]);
            $ro['inspections'] = $iStmt->fetchAll(PDO::FETCH_ASSOC);

            // Estimates
            $eStmt = $db->prepare('SELECT * FROM oretir_estimates WHERE repair_order_id = ? ORDER BY version DESC');
            $eStmt->execute([$id]);
            $ro['estimates'] = $eStmt->fetchAll(PDO::FETCH_ASSOC);

            // Invoices
            $invStmt = $db->prepare('SELECT id, invoice_number, status, total, customer_view_token, created_at FROM oretir_invoices WHERE repair_order_id = ? ORDER BY created_at DESC');
            $invStmt->execute([$id]);
            $ro['invoices'] = $invStmt->fetchAll(PDO::FETCH_ASSOC);

            // Active labor entries with employee names
            $laborStmt = $db->prepare(
                'SELECT l.id, l.employee_id, l.clock_in_at, l.task_description, l.is_billable,
                        e.name AS employee_name
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 WHERE l.repair_order_id = ? AND l.clock_out_at IS NULL
                 ORDER BY l.clock_in_at ASC'
            );
            $laborStmt->execute([$id]);
            $ro['active_labor'] = $laborStmt->fetchAll(PDO::FETCH_ASSOC);

            // All labor entries (for history display)
            $allLaborStmt = $db->prepare(
                'SELECT l.id, l.employee_id, l.clock_in_at, l.clock_out_at, l.duration_minutes,
                        l.task_description, l.is_billable, e.name AS employee_name
                 FROM oretir_labor_entries l
                 JOIN oretir_employees e ON e.id = l.employee_id
                 WHERE l.repair_order_id = ?
                 ORDER BY l.clock_in_at ASC'
            );
            $allLaborStmt->execute([$id]);
            $ro['labor_entries'] = $allLaborStmt->fetchAll(PDO::FETCH_ASSOC);

            // Labor totals
            $totalMins = 0; $billableMins = 0;
            foreach ($ro['labor_entries'] as $le) {
                $m = $le['duration_minutes'] !== null ? (int) $le['duration_minutes'] : 0;
                $totalMins += $m;
                if ($le['is_billable']) $billableMins += $m;
            }
            $ro['labor_total_hours'] = round($totalMins / 60, 2);
            $ro['labor_billable_hours'] = round($billableMins / 60, 2);

            // Visit log (if linked)
            try {
                $visitStmt = $db->prepare(
                    'SELECT v.*, e.name AS employee_name
                     FROM oretir_visit_log v
                     LEFT JOIN oretir_employees e ON e.id = v.assigned_employee_id
                     WHERE v.repair_order_id = ?
                     ORDER BY v.check_in_at DESC LIMIT 1'
                );
                $visitStmt->execute([$id]);
                $ro['visit'] = $visitStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (\Throwable $e) {
                $ro['visit'] = null;
            }

            // Linked appointment
            if ($ro['appointment_id']) {
                $aStmt = $db->prepare('SELECT id, reference_number, service, preferred_date, preferred_time, status FROM oretir_appointments WHERE id = ?');
                $aStmt->execute([$ro['appointment_id']]);
                $ro['appointment'] = $aStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            jsonSuccess($ro);
        }

        // List ROs (paginated, filterable)
        $limit  = max(1, min(500, (int) ($_GET['limit'] ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $status = sanitize((string) ($_GET['status'] ?? ''), 30);
        $search = sanitize((string) ($_GET['search'] ?? ''), 200);
        $customerId = (int) ($_GET['customer_id'] ?? 0);
        $sortBy = sanitize((string) ($_GET['sort_by'] ?? 'created_at'), 30);
        $sortOrder = strtoupper(sanitize((string) ($_GET['sort_order'] ?? 'DESC'), 4));

        $sortMapping = [
            'id' => 'r.id', 'ro_number' => 'r.ro_number', 'status' => 'r.status',
            'created_at' => 'r.created_at', 'updated_at' => 'r.updated_at', 'promised_date' => 'r.promised_date',
        ];
        $sortColumn = $sortMapping[$sortBy] ?? 'r.created_at';
        if (!in_array($sortOrder, ['ASC', 'DESC'], true)) $sortOrder = 'DESC';

        $allowedStatuses = ['intake','check_in','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced','cancelled'];

        $where = 'WHERE 1=1';
        $params = [];

        if ($status !== '' && in_array($status, $allowedStatuses, true)) {
            $where .= ' AND r.status = ?';
            $params[] = $status;
        }

        if ($customerId > 0) {
            $where .= ' AND r.customer_id = ?';
            $params[] = $customerId;
        }

        if (!empty($search)) {
            $where .= ' AND (r.ro_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR v.vin LIKE ?)';
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        $countSql = "SELECT COUNT(*) FROM oretir_repair_orders r
                     JOIN oretir_customers c ON c.id = r.customer_id
                     LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                     {$where}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT r.*, c.first_name, c.last_name, c.email as customer_email, c.phone as customer_phone,
                    v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model, v.vin,
                    v.trim_level, v.engine, v.transmission, v.drive_type, v.fuel_type, v.license_plate,
                    (SELECT COUNT(*) FROM oretir_inspections WHERE repair_order_id = r.id) as inspection_count,
                    (SELECT COUNT(*) FROM oretir_estimates WHERE repair_order_id = r.id) as estimate_count,
                    (SELECT COUNT(*) FROM oretir_labor_entries WHERE repair_order_id = r.id AND clock_out_at IS NULL) as active_labor_count
                FROM oretir_repair_orders r
                JOIN oretir_customers c ON c.id = r.customer_id
                LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                {$where}
                ORDER BY {$sortColumn} {$sortOrder}
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $ros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page = (int) floor($offset / $limit) + 1;
        jsonList($ros, $total, $page, $limit);
    }

    // ─── POST: Create RO ─────────────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $appointmentId = (int) ($data['appointment_id'] ?? 0);

        // Route A: Create from appointment
        if ($appointmentId > 0) {
            $aStmt = $db->prepare(
                'SELECT * FROM oretir_appointments WHERE id = ?'
            );
            $aStmt->execute([$appointmentId]);
            $appt = $aStmt->fetch(PDO::FETCH_ASSOC);
            if (!$appt) jsonError('Appointment not found.', 404);

            // Check if RO already exists for this appointment
            $roCheck = $db->prepare('SELECT id, ro_number FROM oretir_repair_orders WHERE appointment_id = ?');
            $roCheck->execute([$appointmentId]);
            $existingRo = $roCheck->fetch(PDO::FETCH_ASSOC);
            if ($existingRo) {
                jsonSuccess([
                    'id'        => (int) $existingRo['id'],
                    'ro_number' => $existingRo['ro_number'],
                    'existing'  => true,
                    'message'   => 'Repair order already exists for this appointment.',
                ]);
            }

            // Find or create customer
            $customerId = findOrCreateCustomer(
                $appt['email'],
                $appt['first_name'],
                $appt['last_name'],
                $appt['phone'] ?? '',
                $appt['language'] ?? 'english',
                $db
            );
            if (!$customerId) jsonError('Failed to create customer record.');

            // Find or create vehicle
            $vehicleId = findOrCreateVehicle(
                $customerId,
                $appt['vehicle_year'] ?? null,
                $appt['vehicle_make'] ?? null,
                $appt['vehicle_model'] ?? null,
                $appt['vehicle_vin'] ?? null,
                $db
            );

            // Update appointment with customer/vehicle IDs
            $db->prepare(
                'UPDATE oretir_appointments SET customer_id = ?, vehicle_id = ? WHERE id = ?'
            )->execute([$customerId, $vehicleId, $appointmentId]);

            $roNumber = generateRoNumber($db);

            // Auto-assign employee from appointment if present
            $assignedEmpFromAppt = !empty($appt['assigned_employee_id']) ? (int) $appt['assigned_employee_id'] : null;

            $stmt = $db->prepare(
                'INSERT INTO oretir_repair_orders
                    (ro_number, customer_id, vehicle_id, appointment_id, assigned_employee_id, status,
                     customer_concern, mileage_in, promised_date, promised_time, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([
                $roNumber,
                $customerId,
                $vehicleId,
                $appointmentId,
                $assignedEmpFromAppt,
                'intake',
                $appt['notes'] ?? null,
                !empty($data['mileage_in']) ? (int) $data['mileage_in'] : null,
                $appt['preferred_date'] ?? null,
                $appt['preferred_time'] ?? null,
            ]);

            $roId = (int) $db->lastInsertId();

            // Update appointment status to confirmed
            $db->prepare("UPDATE oretir_appointments SET status = 'confirmed' WHERE id = ? AND status = 'pending'")->execute([$appointmentId]);

            jsonSuccess([
                'id'        => $roId,
                'ro_number' => $roNumber,
                'message'   => 'Repair order created from appointment.',
            ]);
        }

        // Route B: Walk-in (no appointment)
        $customerId = (int) ($data['customer_id'] ?? 0);
        if ($customerId <= 0) jsonError('customer_id or appointment_id is required.');

        // Verify customer exists
        $cStmt = $db->prepare('SELECT id FROM oretir_customers WHERE id = ?');
        $cStmt->execute([$customerId]);
        if (!$cStmt->fetch()) jsonError('Customer not found.', 404);

        $vehicleId       = !empty($data['vehicle_id']) ? (int) $data['vehicle_id'] : null;
        $customerConcern = sanitize((string) ($data['customer_concern'] ?? ''), 2000);
        $adminNotes      = sanitize((string) ($data['admin_notes'] ?? ''), 2000);
        $mileageIn       = !empty($data['mileage_in']) ? (int) $data['mileage_in'] : null;
        $promisedDate    = sanitize((string) ($data['promised_date'] ?? ''), 10);
        $promisedTime    = sanitize((string) ($data['promised_time'] ?? ''), 10);
        $assignedEmpId   = !empty($data['assigned_employee_id']) ? (int) $data['assigned_employee_id'] : null;

        $roNumber = generateRoNumber($db);

        $stmt = $db->prepare(
            'INSERT INTO oretir_repair_orders
                (ro_number, customer_id, vehicle_id, assigned_employee_id, status,
                 customer_concern, admin_notes, mileage_in, promised_date, promised_time, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $roNumber,
            $customerId,
            $vehicleId,
            $assignedEmpId,
            'intake',
            $customerConcern ?: null,
            $adminNotes ?: null,
            $mileageIn,
            $promisedDate ?: null,
            $promisedTime ?: null,
        ]);

        jsonSuccess([
            'id'        => (int) $db->lastInsertId(),
            'ro_number' => $roNumber,
            'message'   => 'Repair order created.',
        ]);
    }

    // ─── PUT: Update RO ──────────────────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Repair order ID is required.');

        // Verify RO exists
        $roStmt = $db->prepare('SELECT * FROM oretir_repair_orders WHERE id = ?');
        $roStmt->execute([$id]);
        $ro = $roStmt->fetch(PDO::FETCH_ASSOC);
        if (!$ro) jsonError('Repair order not found.', 404);

        $fields = [];
        $params = [];

        // Status transition
        if (isset($data['status'])) {
            $newStatus = sanitize((string) $data['status'], 30);
            $allowedStatuses = ['intake','check_in','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced','cancelled'];
            if (!in_array($newStatus, $allowedStatuses, true)) {
                jsonError('Invalid status value.');
            }
            $fields[] = 'status = ?';
            $params[] = $newStatus;

            // If marking as completed, update vehicle mileage
            if ($newStatus === 'completed' && !empty($data['mileage_out']) && $ro['vehicle_id']) {
                $mileageOut = (int) $data['mileage_out'];
                $db->prepare('UPDATE oretir_vehicles SET mileage = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$mileageOut, $ro['vehicle_id']]);
            }
        }

        // String fields (simple replace)
        $strFields = [
            'customer_concern' => 2000,
            'promised_date'    => 10,
            'promised_time'    => 10,
        ];
        foreach ($strFields as $f => $maxLen) {
            if (isset($data[$f])) {
                $fields[] = "{$f} = ?";
                $params[] = sanitize((string) $data[$f], $maxLen) ?: null;
            }
        }

        // Notes fields — append-only when note_append flag is set
        $noteFields = ['technician_notes' => 2000, 'admin_notes' => 2000];
        foreach ($noteFields as $nf => $maxLen) {
            if (!isset($data[$nf])) continue;
            if (!empty($data['note_append'])) {
                $newNote = sanitize((string) $data[$nf], $maxLen);
                if ($newNote !== '') {
                    $authorName = $staff['name'] ?? $staff['email'] ?? 'Staff';
                    $timestamp = date('M j, Y g:ia');
                    $entry = "[{$authorName} — {$timestamp}]\n{$newNote}";
                    $existCol = $db->prepare("SELECT {$nf} FROM oretir_repair_orders WHERE id = ?");
                    $existCol->execute([$id]);
                    $existing = (string) ($existCol->fetchColumn() ?: '');
                    $fields[] = "{$nf} = ?";
                    $params[] = $existing ? $entry . "\n\n" . $existing : $entry;
                }
            } else {
                $fields[] = "{$nf} = ?";
                $params[] = sanitize((string) $data[$nf], $maxLen) ?: null;
            }
        }

        // Int fields
        $intFields = ['mileage_in', 'mileage_out', 'vehicle_id', 'assigned_employee_id'];
        foreach ($intFields as $f) {
            if (isset($data[$f])) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f] !== '' && $data[$f] !== null ? (int) $data[$f] : null;
            }
        }

        if (empty($fields)) jsonError('No fields to update.');

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $db->prepare('UPDATE oretir_repair_orders SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);

        // ─── Handle status transition side effects ────────────────────────
        $transitionResult = [];
        if (isset($data['status'])) {
            $transitionResult = handleStatusTransition($db, $id, $ro, $data['status'], $staff, $data);
        }

        $resp = ['message' => 'Repair order updated.'];
        if (!empty($transitionResult['auto_clock_out'])) {
            $resp['auto_clock_out'] = $transitionResult['auto_clock_out'];
        }
        if (!empty($transitionResult['invoice_created'])) {
            $resp['invoice_created'] = true;
        }
        jsonSuccess($resp);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/repair-orders.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}

// ─── handleStatusTransition — centralized side effects for every status change ──
function handleStatusTransition(PDO $db, int $roId, array $ro, string $newStatus, array $staff, array $body): array
{
    $result = ['auto_clock_out' => 0];
    $oldStatus = $ro['status'];

    // ── Sync status to linked appointment ──
    try {
        syncAppointmentRoStatus('ro', $roId, $newStatus, $db);
    } catch (\Throwable $e) {
        error_log("repair-orders.php: appointment sync failed for RO #{$roId}: " . $e->getMessage());
    }

    // ── Transition-specific side effects ──
    switch ($newStatus) {

        case 'check_in':
            // Create/update visit_log, set RO checked_in_at, sync appointment check_in_at
            try {
                $bayNumber = !empty($body['bay_number']) ? (int) $body['bay_number'] : null;

                // Create visit_log entry if table exists
                try {
                    $vlStmt = $db->prepare(
                        'INSERT INTO oretir_visit_log (repair_order_id, appointment_id, customer_id, check_in_at, bay_number, created_at)
                         VALUES (?, ?, ?, NOW(), ?, NOW())'
                    );
                    $vlStmt->execute([$roId, $ro['appointment_id'] ?: null, $ro['customer_id'], $bayNumber]);
                    $visitLogId = (int) $db->lastInsertId();
                    $db->prepare('UPDATE oretir_repair_orders SET checked_in_at = NOW(), visit_log_id = ? WHERE id = ?')
                       ->execute([$visitLogId, $roId]);
                } catch (\Throwable $vlErr) {
                    // visit_log table may not exist yet — just set the RO timestamp
                    $db->prepare('UPDATE oretir_repair_orders SET checked_in_at = NOW() WHERE id = ?')
                       ->execute([$roId]);
                }

                // Sync check_in_at to appointment
                if ($ro['appointment_id']) {
                    $db->prepare('UPDATE oretir_appointments SET check_in_at = NOW() WHERE id = ?')
                       ->execute([$ro['appointment_id']]);
                }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: check_in side effects failed for RO #{$roId}: " . $e->getMessage());
            }
            break;

        case 'diagnosis':
            // Auto clock-in assigned employee (starts REPAIR timer)
            try {
                $db->prepare('UPDATE oretir_repair_orders SET service_started_at = COALESCE(service_started_at, NOW()) WHERE id = ?')
                   ->execute([$roId]);

                // Update visit_log service_start_at
                if ($ro['appointment_id']) {
                    $db->prepare('UPDATE oretir_appointments SET service_start_at = COALESCE(service_start_at, NOW()) WHERE id = ?')
                       ->execute([$ro['appointment_id']]);
                }

                // Auto clock-in assigned employee if no active labor
                $empId = $ro['assigned_employee_id'] ?? null;
                if ($empId) {
                    $openCheck = $db->prepare('SELECT id FROM oretir_labor_entries WHERE repair_order_id = ? AND clock_out_at IS NULL LIMIT 1');
                    $openCheck->execute([$roId]);
                    if (!$openCheck->fetch()) {
                        $empCheck = $db->prepare('SELECT id FROM oretir_employees WHERE id = ? AND is_active = 1');
                        $empCheck->execute([$empId]);
                        if ($empCheck->fetch()) {
                            $db->prepare(
                                'INSERT INTO oretir_labor_entries (repair_order_id, employee_id, clock_in_at, is_billable, task_description, created_at, updated_at)
                                 VALUES (?, ?, NOW(), 1, ?, NOW(), NOW())'
                            )->execute([$roId, $empId, 'Diagnosis']);
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: diagnosis side effects failed for RO #{$roId}: " . $e->getMessage());
            }
            break;

        case 'in_progress':
            // Auto clock-in assigned employee if no active timer (coming from approved, waiting_parts, or on_hold)
            try {
                $empId = $ro['assigned_employee_id'] ?? null;
                if ($empId) {
                    $openCheck = $db->prepare('SELECT id FROM oretir_labor_entries WHERE repair_order_id = ? AND clock_out_at IS NULL LIMIT 1');
                    $openCheck->execute([$roId]);
                    if (!$openCheck->fetch()) {
                        $empCheck = $db->prepare('SELECT id FROM oretir_employees WHERE id = ? AND is_active = 1');
                        $empCheck->execute([$empId]);
                        if ($empCheck->fetch()) {
                            $task = in_array($oldStatus, ['waiting_parts', 'on_hold'], true) ? 'Resumed' : 'Repairs';
                            $db->prepare(
                                'INSERT INTO oretir_labor_entries (repair_order_id, employee_id, clock_in_at, is_billable, task_description, created_at, updated_at)
                                 VALUES (?, ?, NOW(), 1, ?, NOW(), NOW())'
                            )->execute([$roId, $empId, $task]);
                        }
                    }
                }

                // Set service_started_at if not set (first time hitting in_progress)
                $db->prepare('UPDATE oretir_repair_orders SET service_started_at = COALESCE(service_started_at, NOW()) WHERE id = ?')
                   ->execute([$roId]);
            } catch (\Throwable $e) {
                error_log("repair-orders.php: in_progress side effects failed for RO #{$roId}: " . $e->getMessage());
            }
            break;

        case 'waiting_parts':
        case 'on_hold':
            // Auto clock-out ALL active labor entries
            try {
                $clockOutStmt = $db->prepare('UPDATE oretir_labor_entries SET clock_out_at = NOW() WHERE repair_order_id = ? AND clock_out_at IS NULL');
                $clockOutStmt->execute([$roId]);
                $result['auto_clock_out'] = $clockOutStmt->rowCount();
            } catch (\Throwable $e) {
                error_log("repair-orders.php: auto-clock-out failed for RO #{$roId}: " . $e->getMessage());
            }
            break;

        case 'ready':
            // Auto clock-out all labor, set service_ended_at, send "job finished" notification
            try {
                $clockOutStmt = $db->prepare('UPDATE oretir_labor_entries SET clock_out_at = NOW() WHERE repair_order_id = ? AND clock_out_at IS NULL');
                $clockOutStmt->execute([$roId]);
                $result['auto_clock_out'] = $clockOutStmt->rowCount();

                $db->prepare('UPDATE oretir_repair_orders SET service_ended_at = NOW() WHERE id = ?')->execute([$roId]);

                // Update visit_log + appointment
                if ($ro['appointment_id']) {
                    $db->prepare('UPDATE oretir_appointments SET service_end_at = NOW() WHERE id = ?')
                       ->execute([$ro['appointment_id']]);
                }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: ready clock-out failed for RO #{$roId}: " . $e->getMessage());
            }

            // Send "job finished" email + SMS
            try {
                require_once __DIR__ . '/../../includes/mail.php';
                require_once __DIR__ . '/../../includes/sms.php';

                $notifResult = sendJobFinishedEmail($ro, $db);
                if (!$notifResult['success']) {
                    error_log("repair-orders.php: Job finished email failed for RO #{$roId}: " . ($notifResult['error'] ?? 'unknown'));
                }

                if (!empty($ro['customer_id'])) {
                    $custStmt = $db->prepare('SELECT first_name, last_name, phone, language FROM oretir_customers WHERE id = ?');
                    $custStmt->execute([$ro['customer_id']]);
                    $cust = $custStmt->fetch(PDO::FETCH_ASSOC);
                    if ($cust && !empty($cust['phone'])) {
                        $custName = trim($cust['first_name'] . ' ' . $cust['last_name']);
                        sendJobFinishedSms($cust['phone'], $custName, $ro['ro_number'], $cust['language'] ?? 'english');
                    }
                }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: Job finished notification error for RO #{$roId}: " . $e->getMessage());
            }
            break;

        case 'completed':
            // Manager gate — NO auto-invoice. Just clock out any remaining labor.
            try {
                $clockOutStmt = $db->prepare('UPDATE oretir_labor_entries SET clock_out_at = NOW() WHERE repair_order_id = ? AND clock_out_at IS NULL');
                $clockOutStmt->execute([$roId]);
                $result['auto_clock_out'] = $clockOutStmt->rowCount();
            } catch (\Throwable $e) {
                error_log("repair-orders.php: completed clock-out failed for RO #{$roId}: " . $e->getMessage());
            }
            break;

        case 'invoiced':
            // Auto-create invoice, send invoice email, set checked_out_at, end visit
            try {
                $clockOutStmt = $db->prepare('UPDATE oretir_labor_entries SET clock_out_at = NOW() WHERE repair_order_id = ? AND clock_out_at IS NULL');
                $clockOutStmt->execute([$roId]);
                $result['auto_clock_out'] = $clockOutStmt->rowCount();

                $db->prepare('UPDATE oretir_repair_orders SET checked_out_at = NOW(), service_ended_at = COALESCE(service_ended_at, NOW()) WHERE id = ?')
                   ->execute([$roId]);

                require_once __DIR__ . '/../../includes/invoices.php';
                require_once __DIR__ . '/../../includes/mail.php';
                $invoiceResult = createInvoiceFromEstimate($db, $roId);
                if (!$invoiceResult) {
                    $invoiceResult = createInvoiceFromAnyEstimate($db, $roId);
                }

                if ($invoiceResult) {
                    $result['invoice_created'] = true;
                    $custStmt2 = $db->prepare('SELECT first_name, last_name, email, language FROM oretir_customers WHERE id = ?');
                    $custStmt2->execute([$ro['customer_id']]);
                    $cust2 = $custStmt2->fetch(PDO::FETCH_ASSOC);
                    if ($cust2 && !empty($cust2['email'])) {
                        $vStmt2 = $db->prepare('SELECT year, make, model FROM oretir_vehicles WHERE id = ?');
                        $vStmt2->execute([$ro['vehicle_id']]);
                        $v2 = $vStmt2->fetch(PDO::FETCH_ASSOC);
                        $vehicle2 = $v2 ? trim(implode(' ', array_filter([$v2['year'], $v2['make'], $v2['model']]))) : '';
                        $custName2 = trim($cust2['first_name'] . ' ' . $cust2['last_name']);
                        $lang2 = ($cust2['language'] ?? 'english') === 'spanish' ? 'es' : 'en';
                        $baseUrl2 = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
                        $inv2 = getInvoiceWithItems($db, $invoiceResult['invoice_id']);
                        $viewUrl2 = $baseUrl2 . '/invoice/' . $inv2['customer_view_token'];
                        sendInvoiceEmail($cust2['email'], $custName2, $ro['ro_number'], $vehicle2, '$' . number_format((float)$inv2['total'], 2), $invoiceResult['invoice_number'], $viewUrl2, $lang2);
                        $db->prepare('UPDATE oretir_invoices SET status = ? WHERE id = ?')->execute(['sent', $invoiceResult['invoice_id']]);
                    }
                }

                // End visit log
                try {
                    $db->prepare('UPDATE oretir_visit_log SET check_out_at = NOW() WHERE repair_order_id = ? AND check_out_at IS NULL')
                       ->execute([$roId]);
                } catch (\Throwable $vlErr) { /* visit_log may not exist */ }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: invoiced side effects failed for RO #{$roId}: " . $e->getMessage());
            }
            break;

        case 'cancelled':
            // Clock out all labor, end visit timer
            try {
                $clockOutStmt = $db->prepare('UPDATE oretir_labor_entries SET clock_out_at = NOW() WHERE repair_order_id = ? AND clock_out_at IS NULL');
                $clockOutStmt->execute([$roId]);
                $result['auto_clock_out'] = $clockOutStmt->rowCount();

                $db->prepare('UPDATE oretir_repair_orders SET checked_out_at = COALESCE(checked_out_at, NOW()), service_ended_at = COALESCE(service_ended_at, NOW()) WHERE id = ?')
                   ->execute([$roId]);

                try {
                    $db->prepare('UPDATE oretir_visit_log SET check_out_at = NOW() WHERE repair_order_id = ? AND check_out_at IS NULL')
                       ->execute([$roId]);
                } catch (\Throwable $vlErr) { /* visit_log may not exist */ }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: cancelled side effects failed for RO #{$roId}: " . $e->getMessage());
            }
            break;
    }

    return $result;
}
