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
    $staff = requireStaff();
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

        $allowedStatuses = ['intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced','cancelled'];

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
                    (SELECT COUNT(*) FROM oretir_estimates WHERE repair_order_id = r.id) as estimate_count
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
            $allowedStatuses = ['intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','on_hold','waiting_parts','ready','completed','invoiced','cancelled'];
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

        // ─── Sync status to linked appointment ────────────────────────────
        if (isset($data['status'])) {
            try {
                syncAppointmentRoStatus('ro', $id, $data['status'], $db);
            } catch (\Throwable $syncErr) {
                error_log("repair-orders.php: appointment sync failed for RO #{$id}: " . $syncErr->getMessage());
            }
        }

        // ─── Auto-create invoice when RO status changes to 'completed' or 'invoiced' ────
        if (isset($data['status']) && in_array($data['status'], ['completed', 'invoiced'], true)) {
            try {
                require_once __DIR__ . '/../../includes/invoices.php';
                require_once __DIR__ . '/../../includes/mail.php';
                $invoiceResult = createInvoiceFromEstimate($db, $id);

                // Fallback: if no approved estimate, create invoice from any estimate (even draft)
                if (!$invoiceResult) {
                    $invoiceResult = createInvoiceFromAnyEstimate($db, $id);
                }

                if ($invoiceResult) {
                    // Send invoice email
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
                        // Update invoice status to 'sent'
                        $db->prepare('UPDATE oretir_invoices SET status = ? WHERE id = ?')->execute(['sent', $invoiceResult['invoice_id']]);
                    }

                    // Auto-advance RO to 'invoiced' if it was set to 'completed'
                    if ($data['status'] === 'completed') {
                        $db->prepare("UPDATE oretir_repair_orders SET status = 'invoiced', updated_at = NOW() WHERE id = ?")->execute([$id]);
                        try {
                            syncAppointmentRoStatus('ro', $id, 'invoiced', $db);
                        } catch (\Throwable $syncErr2) {
                            error_log("repair-orders.php: sync after auto-invoice failed for RO #{$id}: " . $syncErr2->getMessage());
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: Auto-invoice creation failed for RO #{$id}: " . $e->getMessage());
            }
        }

        // ─── Send "Job Finished" notification when status changes to 'ready' ────
        if (isset($data['status']) && $data['status'] === 'ready') {
            try {
                require_once __DIR__ . '/../../includes/mail.php';
                require_once __DIR__ . '/../../includes/sms.php';

                $notifResult = sendJobFinishedEmail($ro, $db);
                if (!$notifResult['success']) {
                    error_log("repair-orders.php: Job finished email failed for RO #{$id}: " . ($notifResult['error'] ?? 'unknown'));
                }

                // SMS if customer has opted in
                if (!empty($ro['customer_id'])) {
                    $custStmt = $db->prepare('SELECT first_name, last_name, phone, language FROM oretir_customers WHERE id = ?');
                    $custStmt->execute([$ro['customer_id']]);
                    $cust = $custStmt->fetch(PDO::FETCH_ASSOC);
                    if ($cust && !empty($cust['phone'])) {
                        $custName = trim($cust['first_name'] . ' ' . $cust['last_name']);
                        $lang = ($cust['language'] ?? 'english');
                        sendJobFinishedSms($cust['phone'], $custName, $ro['ro_number'], $lang);
                    }
                }
            } catch (\Throwable $e) {
                error_log("repair-orders.php: Job finished notification error for RO #{$id}: " . $e->getMessage());
            }
        }

        jsonSuccess(['message' => 'Repair order updated.']);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/repair-orders.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
