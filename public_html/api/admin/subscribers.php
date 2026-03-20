<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $admin = requirePermission('marketing');
    requireMethod('GET', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List subscribers (paginated) or export CSV ──────────
    if ($method === 'GET') {
        $search = sanitize((string)($_GET['search'] ?? ''), 254);
        $export = $_GET['export'] ?? '';

        // Base query
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = ' WHERE email LIKE ?';
            $params[] = "%{$search}%";
        }

        // CSV export
        if ($export === 'csv') {
            $stmt = $db->prepare(
                "SELECT email, language, source, subscribed_at, unsubscribed_at
                 FROM oretir_subscribers{$where}
                 ORDER BY subscribed_at DESC"
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Email', 'Language', 'Source', 'Subscribed At', 'Unsubscribed At']);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
            exit;
        }

        // Paginated list
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $countStmt = $db->prepare("SELECT COUNT(*) FROM oretir_subscribers{$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Active count
        $activeStmt = $db->query("SELECT COUNT(*) FROM oretir_subscribers WHERE unsubscribed_at IS NULL");
        $activeCount = (int)$activeStmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT id, email, language, source, subscribed_at, unsubscribed_at
             FROM oretir_subscribers{$where}
             ORDER BY subscribed_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute(array_merge($params, [$limit, $offset]));

        jsonSuccess([
            'subscribers' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'active_count' => $activeCount,
            'page' => $page,
            'pages' => (int)ceil($total / $limit),
        ]);
    }

    // ─── DELETE: Soft-unsubscribe a subscriber ────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();
        $body = getJsonBody();
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing subscriber id', 400);
        }

        $db->prepare(
            'UPDATE oretir_subscribers SET unsubscribed_at = NOW() WHERE id = ? AND unsubscribed_at IS NULL'
        )->execute([$id]);

        jsonSuccess(['unsubscribed' => true]);
    }

} catch (\Throwable $e) {
    error_log('admin/subscribers.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
