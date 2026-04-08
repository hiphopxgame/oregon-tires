<?php
/**
 * Oregon Tires — Admin Tire Quote Management
 * GET /api/admin/tire-quotes.php         — paginated list (filterable by status)
 * GET /api/admin/tire-quotes.php?id=N    — single quote by ID
 * PUT /api/admin/tire-quotes.php         — update quote (status, admin_notes, quote_amount)
 *
 * Requires staff session + CSRF for mutations.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $staff = requirePermission('shop_ops');
    requireMethod('GET', 'PUT', 'DELETE');

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List or single ────────────────────────────────────────────
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $stmt = $db->prepare(
                'SELECT tq.*, c.first_name AS cust_first, c.last_name AS cust_last
                 FROM oretir_tire_quotes tq
                 LEFT JOIN oretir_customers c ON c.id = tq.customer_id
                 WHERE tq.id = ?
                 LIMIT 1'
            );
            $stmt->execute([$id]);
            $quote = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$quote) {
                jsonError('Tire quote not found.', 404);
            }

            jsonSuccess($quote);
        }

        // Paginated list
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $status = sanitize((string) ($_GET['status'] ?? ''), 20);
        $search = sanitize((string) ($_GET['search'] ?? ''), 200);

        $where = [];
        $params = [];

        $validStatuses = ['new', 'quoted', 'accepted', 'ordered', 'installed', 'cancelled'];
        if ($status !== '' && in_array($status, $validStatuses, true)) {
            $where[] = 'tq.status = ?';
            $params[] = $status;
        }

        if ($search !== '') {
            $where[] = '(tq.first_name LIKE ? OR tq.last_name LIKE ? OR tq.email LIKE ? OR tq.tire_size LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $db->prepare("SELECT COUNT(*) FROM oretir_tire_quotes tq {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare(
            "SELECT tq.id, tq.first_name, tq.last_name, tq.email, tq.phone,
                    tq.vehicle_year, tq.vehicle_make, tq.vehicle_model,
                    tq.tire_size, tq.tire_count, tq.tire_preference, tq.budget_range,
                    tq.include_installation, tq.preferred_date,
                    tq.status, tq.quote_amount, tq.language,
                    tq.created_at, tq.updated_at
             FROM oretir_tire_quotes tq
             {$whereClause}
             ORDER BY tq.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonList($quotes, $total, $page, $limit);
    }

    // ─── DELETE: Remove tire quote(s) ──────────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();
        $data = getJsonBody();
        $action = $data['action'] ?? '';

        // ── Bulk delete ──
        if ($action === 'bulk_delete') {
            requireSuperAdmin();
            $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn(int $v) => $v > 0);
            if (empty($ids)) jsonError('No valid IDs.', 400);
            if (count($ids) > 100) jsonError('Maximum 100 items per batch.', 400);

            $db->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("DELETE FROM oretir_tire_quotes WHERE id IN ($placeholders)")->execute($ids);
            $db->commit();
            jsonSuccess(['deleted' => count($ids)]);
        }

        // ── Single delete ──
        requireAdmin();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Quote ID is required.', 400);

        $db->prepare('DELETE FROM oretir_tire_quotes WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => 1]);
    }

    // ─── PUT: Update quote ──────────────────────────────────────────────
    verifyCsrf();

    $data = getJsonBody();
    $id = (int) ($data['id'] ?? 0);

    if ($id <= 0) {
        jsonError('Quote ID is required.');
    }

    // Fetch existing
    $stmt = $db->prepare('SELECT * FROM oretir_tire_quotes WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        jsonError('Tire quote not found.', 404);
    }

    // Build update
    $updates = [];
    $params = [];

    if (isset($data['status'])) {
        $newStatus = sanitize((string) $data['status'], 20);
        $validStatuses = ['new', 'quoted', 'accepted', 'ordered', 'installed', 'cancelled'];
        if (in_array($newStatus, $validStatuses, true)) {
            $updates[] = 'status = ?';
            $params[] = $newStatus;
        }
    }

    if (isset($data['admin_notes'])) {
        $updates[] = 'admin_notes = ?';
        $params[] = sanitize((string) $data['admin_notes'], 2000);
    }

    if (isset($data['quote_amount'])) {
        $amount = $data['quote_amount'];
        if ($amount === null || $amount === '') {
            $updates[] = 'quote_amount = NULL';
        } else {
            $updates[] = 'quote_amount = ?';
            $params[] = round((float) $amount, 2);
        }
    }

    if (empty($updates)) {
        jsonError('No fields to update.');
    }

    $params[] = $id;

    $db->prepare(
        'UPDATE oretir_tire_quotes SET ' . implode(', ', $updates) . ' WHERE id = ?'
    )->execute($params);

    // Fetch updated record
    $stmt = $db->prepare('SELECT * FROM oretir_tire_quotes WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);

    jsonSuccess($updated);

} catch (\Throwable $e) {
    error_log('admin/tire-quotes.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
