<?php
/**
 * Oregon Tires — Admin Email Logs
 * GET /api/admin/email-logs.php          — list all email logs (paginated)
 * GET /api/admin/email-logs.php?type=X   — filter by log_type
 * GET /api/admin/email-logs.php?q=X      — search description
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireAdmin();
    requireMethod('GET');

    $db = getDB();

    $where = [];
    $params = [];

    // Filter by log_type
    $type = trim($_GET['type'] ?? '');
    if ($type !== '') {
        $where[] = 'log_type = ?';
        $params[] = $type;
    }

    // Search description
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $where[] = 'description LIKE ?';
        $params[] = '%' . $q . '%';
    }

    $limit = min((int) ($_GET['limit'] ?? 100), 500);
    $offset = max((int) ($_GET['offset'] ?? 0), 0);

    $sql = 'SELECT * FROM oretir_email_logs';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Get total count
    $countSql = 'SELECT COUNT(*) FROM oretir_email_logs';
    if ($where) {
        $countSql .= ' WHERE ' . implode(' AND ', array_slice($where, 0));
        $countStmt = $db->prepare($countSql);
        $countStmt->execute(array_slice($params, 0, count($params) - 2));
    } else {
        $countStmt = $db->query($countSql);
    }
    $total = (int) $countStmt->fetchColumn();

    // Get available log types for filter dropdown
    $types = $db->query('SELECT DISTINCT log_type FROM oretir_email_logs ORDER BY log_type')
                ->fetchAll(\PDO::FETCH_COLUMN);

    jsonSuccess([
        'logs' => $logs,
        'total' => $total,
        'types' => $types,
    ]);

} catch (\Throwable $e) {
    error_log('email-logs.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
