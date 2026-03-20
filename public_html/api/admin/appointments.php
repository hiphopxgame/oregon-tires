<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mail.php';
require_once __DIR__ . '/../../includes/schedule.php';
require_once __DIR__ . '/../../includes/vin-decode.php';

try {
    $staff = requireStaff();
    requireMethod('GET', 'PUT', 'POST');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // ─── Pagination params ────────────────────────────────────────────
        $limit  = max(1, min(500, (int) ($_GET['limit'] ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));

        // ─── Sort params (whitelist validated) ────────────────────────────
        $sortWhitelist = [
            'id', 'service', 'preferred_date', 'preferred_time', 'status',
            'first_name', 'last_name', 'email', 'phone', 'created_at',
            'updated_at', 'assigned_employee_id',
        ];
        $sortBy    = in_array($_GET['sort_by'] ?? '', $sortWhitelist, true)
                     ? $_GET['sort_by']
                     : 'created_at';
        $sortOrder = strtolower($_GET['sort_order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        // ─── Build WHERE clauses ──────────────────────────────────────────
        $where  = [];
        $params = [];

        if (!empty($_GET['status'])) {
            $validStatuses = ['new', 'pending', 'confirmed', 'completed', 'cancelled'];
            if (in_array($_GET['status'], $validStatuses, true)) {
                $where[]  = 'a.status = ?';
                $params[] = $_GET['status'];
            }
        }

        if (!empty($_GET['service'])) {
            $where[]  = 'a.service LIKE ?';
            $params[] = '%' . $_GET['service'] . '%';
        }

        if (!empty($_GET['search'])) {
            $search   = '%' . $_GET['search'] . '%';
            $where[]  = '(a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($_GET['date_from'])) {
            $where[]  = 'a.preferred_date >= ?';
            $params[] = $_GET['date_from'];
        }

        if (!empty($_GET['date_to'])) {
            $where[]  = 'a.preferred_date <= ?';
            $params[] = $_GET['date_to'];
        }

        if (!empty($_GET['employee_id'])) {
            $where[]  = 'a.assigned_employee_id = ?';
            $params[] = (int) $_GET['employee_id'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // ─── Count total matching rows ────────────────────────────────────
        $countSQL  = "SELECT COUNT(*) FROM oretir_appointments a {$whereSQL}";
        $countStmt = $db->prepare($countSQL);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // ─── Fetch paginated rows ─────────────────────────────────────────
        // Sort prefix: columns on joined table need alias, rest use 'a.'
        $sortPrefix = $sortBy === 'employee_name' ? '' : 'a.';
        $dataSQL = "SELECT a.*, e.name AS employee_name
                    FROM oretir_appointments a
                    LEFT JOIN oretir_employees e ON a.assigned_employee_id = e.id
                    {$whereSQL}
                    ORDER BY {$sortPrefix}{$sortBy} {$sortOrder}
                    LIMIT ? OFFSET ?";
        $dataParams   = array_merge($params, [$limit, $offset]);
        $dataStmt     = $db->prepare($dataSQL);
        $dataStmt->execute($dataParams);
        $appointments = $dataStmt->fetchAll();

        $page    = (int) floor($offset / $limit) + 1;
        $pages   = $total > 0 ? (int) ceil($total / $limit) : 1;

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data'    => $appointments,
            'meta'    => [
                'total'    => $total,
                'limit'    => $limit,
                'offset'   => $offset,
                'page'     => $page,
                'pages'    => $pages,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
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
            if ($body['assigned_employee_id']) {
                $empId = (int) $body['assigned_employee_id'];
                try {
                    $svcStmt = $db->prepare('SELECT service, preferred_date, preferred_time FROM oretir_appointments WHERE id = ?');
                    $svcStmt->execute([$id]);
                    $svcRow = $svcStmt->fetch();
                    if ($svcRow) {
                        // Skill check
                        $anyStmt = $db->prepare('SELECT COUNT(*) FROM oretir_employee_skills WHERE employee_id = ?');
                        $anyStmt->execute([$empId]);
                        if ((int) $anyStmt->fetchColumn() > 0) {
                            $certStmt = $db->prepare('SELECT COUNT(*) FROM oretir_employee_skills WHERE employee_id = ? AND service_type = ?');
                            $certStmt->execute([$empId, $svcRow['service']]);
                            if ((int) $certStmt->fetchColumn() === 0) {
                                $nameStmt = $db->prepare('SELECT name FROM oretir_employees WHERE id = ?');
                                $nameStmt->execute([$empId]);
                                $empName = ($nameStmt->fetch()['name'] ?? 'Employee');
                                jsonError($empName . ' is not certified for ' . ucwords(str_replace('-', ' ', $svcRow['service'])) . '.', 422);
                            }
                        }

                        // Schedule validation
                        try {
                            $avail = isEmployeeAvailable($db, $empId, $svcRow['preferred_date'], $svcRow['preferred_time']);
                            if (!$avail['available']) {
                                if (empty($body['force_assign'])) {
                                    http_response_code(409);
                                    echo json_encode([
                                        'success' => false,
                                        'error'   => $avail['reason'],
                                        'conflict' => true,
                                        'hours'   => $avail['hours'],
                                    ], JSON_UNESCAPED_UNICODE);
                                    exit;
                                }
                                // force_assign is truthy — allow but note warning
                            }
                        } catch (\Throwable $schedErr) {
                            error_log("appointments.php: schedule check skipped: " . $schedErr->getMessage());
                        }
                    }
                } catch (\Throwable $e) {
                    error_log("appointments.php: skill check skipped: " . $e->getMessage());
                }
                $fields[] = 'assigned_employee_id = ?';
                $params[] = $empId;
            } else {
                $fields[] = 'assigned_employee_id = ?';
                $params[] = null;
            }
        }

        if (isset($body['admin_notes'])) {
            $fields[] = 'admin_notes = ?';
            $params[] = sanitize($body['admin_notes'], 2000);
        }

        if (isset($body['task_summary'])) {
            $fields[] = 'task_summary = ?';
            $params[] = sanitize($body['task_summary'], 500);
        }

        if (empty($fields)) {
            jsonError('No fields to update.', 400);
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $sql = 'UPDATE oretir_appointments SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $db->prepare($sql)->execute($params);

        // ─── Send confirmation email when status changes to "confirmed" ────
        if (isset($body['status']) && $body['status'] === 'confirmed') {
            try {
                $confirmStmt = $db->prepare(
                    'SELECT id, reference_number, service, preferred_date, preferred_time,
                            first_name, last_name, email, language,
                            vehicle_year, vehicle_make, vehicle_model,
                            cancel_token, cancel_token_expires
                     FROM oretir_appointments WHERE id = ?'
                );
                $confirmStmt->execute([$id]);
                $row = $confirmStmt->fetch();

                if ($row && !empty($row['email'])) {
                    $custName    = trim($row['first_name'] . ' ' . $row['last_name']);
                    $svcDisplay  = ucwords(str_replace('-', ' ', $row['service']));
                    $custLang    = ($row['language'] ?? 'english') === 'spanish' ? 'es' : 'en';

                    $dateObj     = new \DateTime($row['preferred_date']);
                    $displayDate = $custLang === 'es' ? $dateObj->format('d/m/Y') : $dateObj->format('m/d/Y');

                    $tp   = explode(':', $row['preferred_time']);
                    $hr   = (int) $tp[0];
                    $ampm = $hr >= 12 ? 'PM' : 'AM';
                    $dh   = $hr > 12 ? $hr - 12 : ($hr === 0 ? 12 : $hr);
                    $displayTime = $dh . ':00 ' . $ampm;

                    $vParts      = array_filter([$row['vehicle_year'], $row['vehicle_make'], $row['vehicle_model']]);
                    $vehicleInfo = implode(' ', $vParts);

                    // Ensure cancel token exists
                    $cancelToken = $row['cancel_token'] ?? '';
                    if (empty($cancelToken) || (!empty($row['cancel_token_expires']) && strtotime($row['cancel_token_expires']) < time())) {
                        $cancelToken   = bin2hex(random_bytes(32));
                        $cancelExpires = date('Y-m-d H:i:s', strtotime('+30 days'));
                        $db->prepare('UPDATE oretir_appointments SET cancel_token = ?, cancel_token_expires = ? WHERE id = ?')
                           ->execute([$cancelToken, $cancelExpires, $id]);
                    }

                    sendBookingConfirmationEmail(
                        $row['email'], $custName, $svcDisplay, $displayDate, $displayTime,
                        $vehicleInfo, $custLang, $row['reference_number'],
                        $row['service'], $row['preferred_date'], $row['preferred_time'],
                        $cancelToken
                    );

                    logEmail('appointment_confirmed', "Confirmation email sent to {$row['email']} for {$row['reference_number']}");
                }
            } catch (\Throwable $e) {
                error_log("appointments.php: Confirmation email error for #{$id}: " . $e->getMessage());
            }
        }

        // ─── Send assignment notification when employee is assigned ────
        if (array_key_exists('assigned_employee_id', $body) && $body['assigned_employee_id']) {
            try {
                $assignStmt = $db->prepare(
                    'SELECT id, reference_number, service, preferred_date, preferred_time,
                            first_name, last_name, email, language, task_summary,
                            vehicle_year, vehicle_make, vehicle_model,
                            assigned_employee_id
                     FROM oretir_appointments WHERE id = ?'
                );
                $assignStmt->execute([$id]);
                $assignRow = $assignStmt->fetch();
                if ($assignRow && !empty($assignRow['email']) && $assignRow['assigned_employee_id']) {
                    sendAssignmentNotificationEmail($assignRow);
                }
            } catch (\Throwable $e) {
                error_log("appointments.php: Assignment email error for #{$id}: " . $e->getMessage());
            }
        }

        // ─── Sync status to linked RO ──────────────────────────────────
        if (isset($body['status'])) {
            try {
                syncAppointmentRoStatus('appointment', $id, $body['status'], $db);
            } catch (\Throwable $syncErr) {
                error_log("appointments.php: RO sync failed for #{$id}: " . $syncErr->getMessage());
            }
        }

        jsonSuccess(['updated' => $id]);
    }

    // POST — bulk operations
    $action = $body['action'] ?? '';

    // ─── Create new appointment (admin walk-in / phone) ────────────────
    if ($action === 'create') {
        $service       = sanitize((string) ($body['service'] ?? ''), 50);
        $preferredDate = sanitize((string) ($body['preferred_date'] ?? ''), 10);
        $preferredTime = sanitize((string) ($body['preferred_time'] ?? ''), 20);
        $firstName     = sanitize((string) ($body['first_name'] ?? ''), 100);
        $lastName      = sanitize((string) ($body['last_name'] ?? ''), 100);
        $phone         = sanitize((string) ($body['phone'] ?? ''), 30);
        $email         = sanitize((string) ($body['email'] ?? ''), 254);
        $vehicleYear   = sanitize((string) ($body['vehicle_year'] ?? ''), 4);
        $vehicleMake   = sanitize((string) ($body['vehicle_make'] ?? ''), 50);
        $vehicleModel  = sanitize((string) ($body['vehicle_model'] ?? ''), 50);
        $notes         = sanitize((string) ($body['notes'] ?? ''), 2000);
        $tirePreference = sanitize((string) ($body['tire_preference'] ?? ''), 10);
        $tireCount      = !empty($body['tire_count']) ? max(1, min(10, (int) $body['tire_count'])) : null;
        if ($tirePreference !== '' && !in_array($tirePreference, ['new', 'used', 'either'], true)) $tirePreference = '';
        $language      = sanitize((string) ($body['language'] ?? 'english'), 20);
        $source        = sanitize((string) ($body['source'] ?? 'walk-in'), 20);
        $employeeId    = isset($body['assigned_employee_id']) && $body['assigned_employee_id'] !== ''
                         ? (int) $body['assigned_employee_id'] : null;

        $taskSummary = isset($body['task_summary']) ? sanitize((string) $body['task_summary'], 500) : null;

        // Validate required fields
        if (!$firstName || !$lastName || !$email || !$phone || !$service || !$preferredDate || !$preferredTime) {
            jsonError('Missing required fields.', 400);
        }
        if (!isValidService($service)) jsonError('Invalid service type.', 400);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $preferredDate)) jsonError('Invalid date format.', 400);
        if (!isValidTimeSlot($preferredTime)) jsonError('Invalid time slot.', 400);
        if (!isValidEmail($email)) jsonError('Invalid email address.', 400);
        if (!isValidPhone($phone)) jsonError('Invalid phone number.', 400);
        if (!in_array($language, ['english', 'spanish'], true)) $language = 'english';
        if (!in_array($source, ['walk-in', 'phone', 'admin'], true)) $source = 'walk-in';

        // Auto-generate task summary if not provided
        if (!$taskSummary) {
            $vParts = array_filter([$vehicleYear, $vehicleMake, $vehicleModel]);
            $taskSummary = generateTaskSummary($service, implode(' ', $vParts) ?: null, $notes ?: null);
        }

        // Schedule validation for assigned employee
        if ($employeeId) {
            try {
                $avail = isEmployeeAvailable($db, $employeeId, $preferredDate, $preferredTime);
                if (!$avail['available']) {
                    if (empty($body['force_assign'])) {
                        http_response_code(409);
                        echo json_encode([
                            'success'  => false,
                            'error'    => $avail['reason'],
                            'conflict' => true,
                            'hours'    => $avail['hours'],
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
            } catch (\Throwable $schedErr) {
                error_log("appointments.php: schedule check on create skipped: " . $schedErr->getMessage());
            }
        }

        // Generate unique reference number
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $referenceNumber = '';
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = '';
            $bytes = random_bytes(8);
            for ($i = 0; $i < 8; $i++) $code .= $chars[ord($bytes[$i]) % strlen($chars)];
            $candidate = 'OT-' . $code;
            $checkStmt = $db->prepare('SELECT COUNT(*) FROM oretir_appointments WHERE reference_number = ?');
            $checkStmt->execute([$candidate]);
            if ((int) $checkStmt->fetchColumn() === 0) { $referenceNumber = $candidate; break; }
        }
        if (!$referenceNumber) jsonError('Could not generate reference number.', 500);

        $adminNotes = '[' . strtoupper($source) . '] Created by admin';

        $stmt = $db->prepare(
            'INSERT INTO oretir_appointments
                (reference_number, service, preferred_date, preferred_time,
                 vehicle_year, vehicle_make, vehicle_model,
                 first_name, last_name, phone, email, notes,
                 tire_preference, tire_count,
                 status, language, assigned_employee_id, admin_notes, task_summary,
                 created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            $referenceNumber, $service, $preferredDate, $preferredTime,
            $vehicleYear ?: null, $vehicleMake ?: null, $vehicleModel ?: null,
            $firstName, $lastName, $phone, $email, $notes ?: null,
            $tirePreference ?: null, $tireCount,
            'confirmed', $language, $employeeId, $adminNotes, $taskSummary
        ]);
        $appointmentId = (int) $db->lastInsertId();

        // Generate cancel token
        $cancelToken = bin2hex(random_bytes(32));
        $db->prepare('UPDATE oretir_appointments SET cancel_token = ?, cancel_token_expires = ? WHERE id = ?')
           ->execute([$cancelToken, date('Y-m-d H:i:s', strtotime('+30 days')), $appointmentId]);

        // Send confirmation email to customer
        try {
            $custName    = "{$firstName} {$lastName}";
            $svcDisplay  = ucwords(str_replace('-', ' ', $service));
            $custLang    = $language === 'spanish' ? 'es' : 'en';
            $dateObj     = new \DateTime($preferredDate);
            $displayDate = $custLang === 'es' ? $dateObj->format('d/m/Y') : $dateObj->format('m/d/Y');
            $displayTime = formatTimeDisplay($preferredTime);
            $vParts      = array_filter([$vehicleYear, $vehicleMake, $vehicleModel]);
            $vehicleInfo = implode(' ', $vParts);

            sendBookingConfirmationEmail(
                $email, $custName, $svcDisplay, $displayDate, $displayTime,
                $vehicleInfo, $custLang, $referenceNumber,
                $service, $preferredDate, $preferredTime, $cancelToken
            );
            logEmail('appointment_confirmed', "Confirmation email sent to {$email} for {$referenceNumber} (admin-created)");
        } catch (\Throwable $e) {
            error_log("appointments.php: Confirmation email error for admin-created #{$appointmentId}: " . $e->getMessage());
        }

        // Send assignment notification if employee was assigned
        if ($employeeId) {
            try {
                $assignStmt = $db->prepare(
                    'SELECT id, reference_number, service, preferred_date, preferred_time,
                            first_name, last_name, email, language, task_summary,
                            vehicle_year, vehicle_make, vehicle_model,
                            assigned_employee_id
                     FROM oretir_appointments WHERE id = ?'
                );
                $assignStmt->execute([$appointmentId]);
                $assignRow = $assignStmt->fetch();
                if ($assignRow && $assignRow['assigned_employee_id']) {
                    sendAssignmentNotificationEmail($assignRow);
                }
            } catch (\Throwable $e) {
                error_log("appointments.php: Assignment email error for new #{$appointmentId}: " . $e->getMessage());
            }
        }

        // ─── Auto-create Repair Order ──────────────────────────────────
        $roData = null;
        try {
            // Auto-create customer/vehicle records first
            $custId = findOrCreateCustomer($email, $firstName, $lastName, $phone, $language, $db);
            if ($custId) {
                $vehId = findOrCreateVehicle($custId, $vehicleYear ?: null, $vehicleMake ?: null, $vehicleModel ?: null, null, $db);
                $db->prepare('UPDATE oretir_appointments SET customer_id = ?, vehicle_id = ? WHERE id = ?')
                   ->execute([$custId, $vehId, $appointmentId]);
                $roData = createRoForAppointment($appointmentId, $db);
            }
        } catch (\Throwable $roErr) {
            error_log("appointments.php: auto-RO failed for admin-created #{$appointmentId}: " . $roErr->getMessage());
        }

        $result = ['appointment_id' => $appointmentId, 'reference_number' => $referenceNumber];
        if ($roData) {
            $result['ro_id']     = $roData['id'];
            $result['ro_number'] = $roData['ro_number'];
        }
        jsonSuccess($result);
    }

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
        $updated = 0; $skipped = 0;
        $stmt = $db->prepare('UPDATE oretir_appointments SET assigned_employee_id = ?, updated_at = NOW() WHERE id = ?');
        foreach ($ids as $apptId) {
            if ($employeeId) {
                try {
                    $svcStmt = $db->prepare('SELECT service, preferred_date, preferred_time FROM oretir_appointments WHERE id = ?');
                    $svcStmt->execute([$apptId]);
                    $svcRow = $svcStmt->fetch();
                    if (!$svcRow) { $skipped++; continue; }

                    // Skill check
                    $anyStmt = $db->prepare('SELECT COUNT(*) FROM oretir_employee_skills WHERE employee_id = ?');
                    $anyStmt->execute([$employeeId]);
                    if ((int) $anyStmt->fetchColumn() > 0) {
                        $certStmt = $db->prepare('SELECT COUNT(*) FROM oretir_employee_skills WHERE employee_id = ? AND service_type = ?');
                        $certStmt->execute([$employeeId, $svcRow['service']]);
                        if ((int) $certStmt->fetchColumn() === 0) { $skipped++; continue; }
                    }

                    // Schedule check
                    $avail = isEmployeeAvailable($db, $employeeId, $svcRow['preferred_date'], $svcRow['preferred_time']);
                    if (!$avail['available']) { $skipped++; continue; }
                } catch (\Throwable $e) { /* allow assignment if check fails */ }
            }
            $stmt->execute([$employeeId, $apptId]);
            $updated++;
        }
        jsonSuccess(['updated' => $updated, 'skipped' => $skipped]);
    }

    jsonError('Invalid action.', 400);

} catch (\Throwable $e) {
    error_log('appointments.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
