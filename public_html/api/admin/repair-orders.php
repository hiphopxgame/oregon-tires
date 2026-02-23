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
    $admin = requireAdmin();
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
                    v.trim_level, v.engine, v.tire_size_front, v.tire_size_rear, v.color as vehicle_color,
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

        $allowedSorts = ['id', 'ro_number', 'status', 'created_at', 'updated_at', 'promised_date'];
        if (!in_array($sortBy, $allowedSorts, true)) $sortBy = 'created_at';
        if (!in_array($sortOrder, ['ASC', 'DESC'], true)) $sortOrder = 'DESC';

        $allowedStatuses = ['intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','waiting_parts','ready','completed','invoiced','cancelled'];

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

        $sql = "SELECT r.*, c.first_name, c.last_name, c.email as customer_email,
                    v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model, v.vin,
                    (SELECT COUNT(*) FROM oretir_inspections WHERE repair_order_id = r.id) as inspection_count,
                    (SELECT COUNT(*) FROM oretir_estimates WHERE repair_order_id = r.id) as estimate_count
                FROM oretir_repair_orders r
                JOIN oretir_customers c ON c.id = r.customer_id
                LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                {$where}
                ORDER BY r.{$sortBy} {$sortOrder}
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
                jsonError('A repair order already exists for this appointment (RO ' . $existingRo['ro_number'] . ').', 409);
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

            $stmt = $db->prepare(
                'INSERT INTO oretir_repair_orders
                    (ro_number, customer_id, vehicle_id, appointment_id, status,
                     customer_concern, mileage_in, promised_date, promised_time, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([
                $roNumber,
                $customerId,
                $vehicleId,
                $appointmentId,
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
            $allowedStatuses = ['intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','waiting_parts','ready','completed','invoiced','cancelled'];
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

        // String fields
        $strFields = [
            'customer_concern' => 2000,
            'technician_notes' => 2000,
            'admin_notes'      => 2000,
            'promised_date'    => 10,
            'promised_time'    => 10,
        ];
        foreach ($strFields as $f => $maxLen) {
            if (isset($data[$f])) {
                $fields[] = "{$f} = ?";
                $params[] = sanitize((string) $data[$f], $maxLen) ?: null;
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
        jsonSuccess(['message' => 'Repair order updated.']);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/repair-orders.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
