<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $staff = requireStaff();
    requireMethod('GET', 'PUT');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // ─── Pagination params ────────────────────────────────────────────
        $limit  = max(1, min(500, (int) ($_GET['limit'] ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));

        // ─── Sort params (whitelist validated) ────────────────────────────
        $sortWhitelist = [
            'id', 'service_type', 'last_service_date', 'next_due_date',
            'status', 'reminder_sent_at', 'created_at',
        ];
        $sortBy    = in_array($_GET['sort_by'] ?? '', $sortWhitelist, true)
                     ? $_GET['sort_by']
                     : 'next_due_date';
        $sortOrder = strtolower($_GET['sort_order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        // ─── Build WHERE clauses ──────────────────────────────────────────
        $where  = [];
        $params = [];

        if (!empty($_GET['status'])) {
            $validStatuses = ['pending', 'sent', 'booked', 'dismissed'];
            if (in_array($_GET['status'], $validStatuses, true)) {
                $where[]  = 'sr.status = ?';
                $params[] = $_GET['status'];
            }
        }

        if (!empty($_GET['customer_id'])) {
            $where[]  = 'sr.customer_id = ?';
            $params[] = (int) $_GET['customer_id'];
        }

        if (!empty($_GET['service_type'])) {
            $where[]  = 'sr.service_type = ?';
            $params[] = $_GET['service_type'];
        }

        if (!empty($_GET['search'])) {
            $search   = '%' . $_GET['search'] . '%';
            $where[]  = '(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($_GET['due_from'])) {
            $where[]  = 'sr.next_due_date >= ?';
            $params[] = $_GET['due_from'];
        }

        if (!empty($_GET['due_to'])) {
            $where[]  = 'sr.next_due_date <= ?';
            $params[] = $_GET['due_to'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // ─── Count total matching rows ────────────────────────────────────
        $countSQL  = "SELECT COUNT(*)
                      FROM oretir_service_reminders sr
                      JOIN oretir_customers c ON sr.customer_id = c.id
                      {$whereSQL}";
        $countStmt = $db->prepare($countSQL);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // ─── Fetch paginated rows ─────────────────────────────────────────
        $dataSQL = "SELECT sr.*,
                           c.first_name, c.last_name, c.email, c.phone, c.language,
                           v.year AS vehicle_year, v.make AS vehicle_make,
                           v.model AS vehicle_model, v.license_plate
                    FROM oretir_service_reminders sr
                    JOIN oretir_customers c ON sr.customer_id = c.id
                    LEFT JOIN oretir_vehicles v ON sr.vehicle_id = v.id
                    {$whereSQL}
                    ORDER BY sr.{$sortBy} {$sortOrder}
                    LIMIT ? OFFSET ?";
        $dataParams = array_merge($params, [$limit, $offset]);
        $dataStmt   = $db->prepare($dataSQL);
        $dataStmt->execute($dataParams);
        $reminders = $dataStmt->fetchAll();

        $page  = (int) floor($offset / $limit) + 1;
        $pages = $total > 0 ? (int) ceil($total / $limit) : 1;

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data'    => $reminders,
            'meta'    => [
                'total'  => $total,
                'limit'  => $limit,
                'offset' => $offset,
                'page'   => $page,
                'pages'  => $pages,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── PUT — update reminder status ─────────────────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    $id = (int) ($body['id'] ?? 0);
    if ($id < 1) {
        jsonError('Missing reminder id.', 400);
    }

    $validStatuses = ['pending', 'sent', 'booked', 'dismissed'];
    $newStatus = $body['status'] ?? '';

    if (!in_array($newStatus, $validStatuses, true)) {
        jsonError('Invalid status. Must be one of: pending, sent, booked, dismissed.', 400);
    }

    // Verify reminder exists
    $checkStmt = $db->prepare('SELECT id, status FROM oretir_service_reminders WHERE id = ?');
    $checkStmt->execute([$id]);
    $existing = $checkStmt->fetch();

    if (!$existing) {
        jsonError('Reminder not found.', 404);
    }

    $db->prepare(
        'UPDATE oretir_service_reminders SET status = ?, updated_at = NOW() WHERE id = ?'
    )->execute([$newStatus, $id]);

    jsonSuccess(['updated' => $id, 'status' => $newStatus]);

} catch (\Throwable $e) {
    error_log('service-reminders.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
