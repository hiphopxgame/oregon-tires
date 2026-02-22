<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET');
    $admin = requireAdmin();
    $db = getDB();

    // ─── Build WHERE clauses ──────────────────────────────────────────
    $where  = ["a.status != 'cancelled'"];
    $params = [];

    if (!empty($_GET['search'])) {
        $search   = '%' . $_GET['search'] . '%';
        $where[]  = '(a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    // ─── Customer list grouped by email ──────────────────────────────
    $sql = "SELECT
                a.email,
                MAX(a.first_name) AS first_name,
                MAX(a.last_name) AS last_name,
                MAX(a.phone) AS phone,
                COUNT(*) AS total_visits,
                MAX(a.preferred_date) AS last_visit,
                MIN(a.preferred_date) AS first_visit,
                GROUP_CONCAT(DISTINCT a.service ORDER BY a.service ASC SEPARATOR ', ') AS services,
                MAX(a.vehicle_year) AS vehicle_year,
                MAX(a.vehicle_make) AS vehicle_make,
                MAX(a.vehicle_model) AS vehicle_model
            FROM oretir_appointments a
            {$whereSQL}
            GROUP BY a.email
            ORDER BY total_visits DESC, last_visit DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

    $total = count($customers);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $customers,
        'meta'    => [
            'total' => $total,
        ],
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    error_log('customers.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
