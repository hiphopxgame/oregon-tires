<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/loyalty.php';
require_once __DIR__ . '/../../includes/push.php';

try {
    requireStaff();
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET: List visits for a date, or active visits
    if ($method === 'GET') {
        $filter = sanitize((string) ($_GET['filter'] ?? 'today'), 20);
        $date = sanitize((string) ($_GET['date'] ?? date('Y-m-d')), 10);

        if ($filter === 'active') {
            // Currently checked-in (no check_out_at)
            $stmt = $db->prepare(
                "SELECT v.*, c.first_name, c.last_name, c.phone,
                        r.ro_number, r.status AS ro_status
                 FROM oretir_visit_log v
                 JOIN oretir_customers c ON c.id = v.customer_id
                 LEFT JOIN oretir_repair_orders r ON r.id = v.repair_order_id
                 WHERE v.check_in_at IS NOT NULL AND v.check_out_at IS NULL
                 ORDER BY v.check_in_at ASC"
            );
            $stmt->execute();
        } else {
            // By date
            $stmt = $db->prepare(
                "SELECT v.*, c.first_name, c.last_name, c.phone,
                        r.ro_number, r.status AS ro_status
                 FROM oretir_visit_log v
                 JOIN oretir_customers c ON c.id = v.customer_id
                 LEFT JOIN oretir_repair_orders r ON r.id = v.repair_order_id
                 WHERE DATE(v.check_in_at) = ?
                 ORDER BY v.check_in_at DESC"
            );
            $stmt->execute([$date]);
        }

        $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Also get bay utilization
        $bayStmt = $db->query(
            "SELECT bay_number, COUNT(*) as count
             FROM oretir_visit_log
             WHERE check_in_at IS NOT NULL AND check_out_at IS NULL AND bay_number IS NOT NULL
             GROUP BY bay_number"
        );
        $baysInUse = $bayStmt->fetchAll(PDO::FETCH_ASSOC);

        // Pending appointments for today (for check-in linking)
        $pendingAppts = [];
        if ($filter === 'active' || $filter === 'today') {
            $apptStmt = $db->prepare(
                "SELECT a.id, a.customer_id, a.service, a.preferred_time,
                        a.first_name, a.last_name, a.vehicle_year, a.vehicle_make, a.vehicle_model,
                        c.id AS cust_id
                 FROM oretir_appointments a
                 LEFT JOIN oretir_customers c ON a.customer_id = c.id
                 WHERE a.preferred_date = CURDATE()
                   AND a.status IN ('confirmed', 'new', 'pending')
                   AND a.id NOT IN (SELECT appointment_id FROM oretir_visit_log WHERE appointment_id IS NOT NULL AND DATE(check_in_at) = CURDATE())
                 ORDER BY a.preferred_time ASC"
            );
            $apptStmt->execute();
            $pendingAppts = $apptStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        jsonSuccess([
            'visits' => $visits,
            'bays_in_use' => $baysInUse,
            'active_count' => count(array_filter($visits, fn($v) => empty($v['check_out_at']))),
            'pending_appointments' => $pendingAppts,
        ]);
    }

    verifyCsrf();
    $data = getJsonBody();

    // POST: Check in a customer
    if ($method === 'POST') {
        $customerId = (int) ($data['customer_id'] ?? 0);
        if ($customerId < 1) jsonError('customer_id is required.', 400);

        // Verify customer exists
        $cStmt = $db->prepare('SELECT id FROM oretir_customers WHERE id = ?');
        $cStmt->execute([$customerId]);
        if (!$cStmt->fetch()) jsonError('Customer not found.', 404);

        $appointmentId = !empty($data['appointment_id']) ? (int) $data['appointment_id'] : null;
        $repairOrderId = !empty($data['repair_order_id']) ? (int) $data['repair_order_id'] : null;
        $bayNumber = !empty($data['bay_number']) ? max(1, min(20, (int) $data['bay_number'])) : null;
        $notes = sanitize((string) ($data['notes'] ?? ''), 1000);

        $stmt = $db->prepare(
            'INSERT INTO oretir_visit_log
                (customer_id, appointment_id, repair_order_id, check_in_at, bay_number, notes)
             VALUES (?, ?, ?, NOW(), ?, ?)'
        );
        $stmt->execute([$customerId, $appointmentId, $repairOrderId, $bayNumber, $notes ?: null]);

        $visitId = (int) $db->lastInsertId();
        jsonSuccess(['id' => $visitId, 'checked_in' => true]);
    }

    // PUT: Update visit timestamps
    if ($method === 'PUT') {
        $id = (int) ($data['id'] ?? 0);
        if ($id < 1) jsonError('Visit id is required.', 400);

        // Verify visit exists
        $vStmt = $db->prepare('SELECT id FROM oretir_visit_log WHERE id = ?');
        $vStmt->execute([$id]);
        if (!$vStmt->fetch()) jsonError('Visit not found.', 404);

        $fields = [];
        $params = [];

        // Timestamp updates
        $tsFields = ['service_start_at', 'service_end_at', 'check_out_at'];
        foreach ($tsFields as $f) {
            if (isset($data[$f])) {
                if ($data[$f] === 'now' || $data[$f] === true) {
                    $fields[] = "{$f} = NOW()";
                } elseif ($data[$f] === null || $data[$f] === '') {
                    $fields[] = "{$f} = NULL";
                } else {
                    $fields[] = "{$f} = ?";
                    $params[] = $data[$f];
                }
            }
        }

        if (isset($data['bay_number'])) {
            $fields[] = 'bay_number = ?';
            $params[] = $data['bay_number'] ? max(1, min(20, (int) $data['bay_number'])) : null;
        }

        if (isset($data['notes'])) {
            $fields[] = 'notes = ?';
            $params[] = sanitize((string) $data['notes'], 1000) ?: null;
        }

        if (empty($fields)) jsonError('No fields to update.', 400);

        $params[] = $id;
        $db->prepare('UPDATE oretir_visit_log SET ' . implode(', ', $fields) . ' WHERE id = ?')
           ->execute($params);

        // ─── Auto-actions on check-out ──────────────────────────────
        $autoActions = [];
        if (isset($data['check_out_at']) && ($data['check_out_at'] === 'now' || $data['check_out_at'] === true)) {
            // Fetch visit details for customer_id
            $visitStmt = $db->prepare('SELECT customer_id FROM oretir_visit_log WHERE id = ?');
            $visitStmt->execute([$id]);
            $visitRow = $visitStmt->fetch(PDO::FETCH_ASSOC);
            $custId = (int) ($visitRow['customer_id'] ?? 0);

            if ($custId > 0) {
                // 1. Auto-award loyalty points for completed visit
                try {
                    $awarded = awardLoyaltyPoints($db, $custId, 10, 'earn_visit', 'Visit completed', 'visit', $id);
                    if ($awarded) {
                        // Increment visit count
                        $db->prepare('UPDATE oretir_customers SET visit_count = visit_count + 1, last_visit_at = NOW() WHERE id = ?')
                           ->execute([$custId]);
                        $autoActions[] = 'loyalty_awarded';
                    }
                } catch (\Throwable $e) {
                    error_log('visit-log: loyalty award error: ' . $e->getMessage());
                }

                // 2. Push notification: service complete
                try {
                    queueNotificationForCustomer(
                        $custId,
                        'service_complete',
                        'Your vehicle is ready!',
                        '¡Su vehículo está listo!',
                        'Your service at Oregon Tires is complete. Your vehicle is ready for pickup.',
                        'Su servicio en Oregon Tires está completo. Su vehículo está listo para recoger.',
                        '/members?tab=appointments'
                    );
                    $autoActions[] = 'notification_queued';
                } catch (\Throwable $e) {
                    error_log('visit-log: push notification error: ' . $e->getMessage());
                }
            }
        }

        jsonSuccess(['updated' => $id, 'auto_actions' => $autoActions]);
    }

} catch (\Throwable $e) {
    error_log('visit-log.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
