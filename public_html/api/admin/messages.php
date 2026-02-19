<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET', 'PUT');
    $admin = requireAdmin();
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // ─── Pagination params ────────────────────────────────────────────
        $limit  = max(1, min(500, (int) ($_GET['limit'] ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));

        // ─── Sort params (whitelist validated) ────────────────────────────
        $sortWhitelist = [
            'id', 'first_name', 'last_name', 'email', 'phone',
            'status', 'language', 'created_at',
        ];
        $sortBy    = in_array($_GET['sort_by'] ?? '', $sortWhitelist, true)
                     ? $_GET['sort_by']
                     : 'created_at';
        $sortOrder = strtolower($_GET['sort_order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        // ─── Build WHERE clauses ──────────────────────────────────────────
        $where  = [];
        $params = [];

        if (!empty($_GET['status'])) {
            $validStatuses = ['new', 'priority', 'completed'];
            if (in_array($_GET['status'], $validStatuses, true)) {
                $where[]  = 'status = ?';
                $params[] = $_GET['status'];
            }
        }

        if (!empty($_GET['search'])) {
            $search   = '%' . $_GET['search'] . '%';
            $where[]  = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($_GET['date_from'])) {
            $where[]  = 'created_at >= ?';
            $params[] = $_GET['date_from'] . ' 00:00:00';
        }

        if (!empty($_GET['date_to'])) {
            $where[]  = 'created_at <= ?';
            $params[] = $_GET['date_to'] . ' 23:59:59';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // ─── Count total matching rows ────────────────────────────────────
        $countSQL  = "SELECT COUNT(*) FROM oretir_contact_messages {$whereSQL}";
        $countStmt = $db->prepare($countSQL);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // ─── Fetch paginated rows ─────────────────────────────────────────
        $dataSQL  = "SELECT * FROM oretir_contact_messages {$whereSQL}
                     ORDER BY {$sortBy} {$sortOrder}
                     LIMIT ? OFFSET ?";
        $dataParams = array_merge($params, [$limit, $offset]);
        $dataStmt   = $db->prepare($dataSQL);
        $dataStmt->execute($dataParams);
        $messages = $dataStmt->fetchAll();

        $page  = (int) floor($offset / $limit) + 1;
        $pages = $total > 0 ? (int) ceil($total / $limit) : 1;

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data'    => $messages,
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

    // PUT
    verifyCsrf();
    $body = getJsonBody();

    $id = (int) ($body['id'] ?? 0);
    $status = $body['status'] ?? '';

    if ($id < 1) {
        jsonError('Missing message id.', 400);
    }

    $validStatuses = ['new', 'priority', 'completed'];
    if (!in_array($status, $validStatuses, true)) {
        jsonError('Invalid status. Must be: new, priority, or completed.', 400);
    }

    $stmt = $db->prepare('UPDATE oretir_contact_messages SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);

    jsonSuccess(['updated' => $id]);

} catch (\Throwable $e) {
    error_log('messages.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
