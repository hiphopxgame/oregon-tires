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

        // Sync status change to Google Calendar
        if (isset($body['status']) && !empty($_ENV['GOOGLE_CALENDAR_CREDENTIALS'])) {
            try {
                $apptStmt = $db->prepare('SELECT google_event_id FROM oretir_appointments WHERE id = ?');
                $apptStmt->execute([$id]);
                $apptRow = $apptStmt->fetch();
                $eventId = $apptRow['google_event_id'] ?? null;

                if ($eventId) {
                    $formKitPath = $_ENV['FORM_KIT_PATH'] ?? __DIR__ . '/../../../---form-kit';
                    require_once $formKitPath . '/loader.php';
                    require_once $formKitPath . '/actions/google-calendar.php';

                    FormManager::init($db, ['site_key' => 'oregon.tires']);
                    GoogleCalendarAction::register([
                        'credentials_path' => $_ENV['GOOGLE_CALENDAR_CREDENTIALS'],
                        'calendar_id'      => $_ENV['GOOGLE_CALENDAR_ID'] ?? 'primary',
                        'send_invites'     => true,
                        'timezone'         => 'America/Los_Angeles',
                    ]);

                    if ($body['status'] === 'cancelled') {
                        GoogleCalendarAction::deleteEvent($eventId);
                    } elseif ($body['status'] === 'confirmed') {
                        GoogleCalendarAction::updateEvent($eventId, ['colorId' => '10']); // Green
                    } elseif ($body['status'] === 'completed') {
                        GoogleCalendarAction::updateEvent($eventId, ['colorId' => '8']); // Graphite
                    }
                }
            } catch (\Throwable $e) {
                // Calendar sync failure should not break the status update
                error_log("appointments.php: Google Calendar sync error for appointment #{$id}: " . $e->getMessage());
            }
        }

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
