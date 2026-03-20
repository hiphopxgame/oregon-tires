<?php
/**
 * Oregon Tires — Admin Email Logs
 * GET /api/admin/email-logs.php          — list all email logs (paginated, excludes body)
 * GET /api/admin/email-logs.php?id=N     — single log with full body
 * GET /api/admin/email-logs.php?type=X   — filter by log_type
 * GET /api/admin/email-logs.php?q=X      — search description/subject/recipient
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireAdmin();
    requireMethod('GET');

    $db = getDB();

    // ─── Single record with full body ─────────────────────────
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare('SELECT * FROM oretir_email_logs WHERE id = ?');
        $stmt->execute([$id]);
        $log = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$log) jsonError('Log not found', 404);
        jsonSuccess($log);
    }

    // ─── List (excludes body for performance) ─────────────────
    $where = [];
    $params = [];

    $type = trim($_GET['type'] ?? '');
    if ($type !== '') {
        $where[] = 'log_type = ?';
        $params[] = $type;
    }

    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $where[] = '(description LIKE ? OR subject LIKE ? OR recipient_email LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }

    $limit = min((int) ($_GET['limit'] ?? 100), 500);
    $offset = max((int) ($_GET['offset'] ?? 0), 0);

    $cols = 'id, log_type, description, admin_email, recipient_email, subject, created_at,
             (body IS NOT NULL AND body != \'\') AS has_body';
    $sql = "SELECT {$cols} FROM oretir_email_logs";
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Total count
    $countSql = 'SELECT COUNT(*) FROM oretir_email_logs';
    if ($where) {
        $countSql .= ' WHERE ' . implode(' AND ', $where);
        $countStmt = $db->prepare($countSql);
        $countStmt->execute(array_slice($params, 0, count($params) - 2));
    } else {
        $countStmt = $db->query($countSql);
    }
    $total = (int) $countStmt->fetchColumn();

    // Available log types
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
