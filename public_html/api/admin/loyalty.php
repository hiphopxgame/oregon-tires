<?php
/**
 * Oregon Tires — Admin Loyalty Points Management
 * GET  /api/admin/loyalty.php?customer_id=N  — list ledger entries for a customer
 * POST /api/admin/loyalty.php                — manual point adjustment
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/loyalty.php';

try {
    startSecureSession();
    $admin = requireAdmin();
    requireMethod('GET', 'POST');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List loyalty ledger for a customer ────────────────────
    if ($method === 'GET') {
        $customerId = (int) ($_GET['customer_id'] ?? 0);
        if ($customerId <= 0) {
            jsonError('customer_id is required.', 400);
        }

        // Verify customer exists and get current balance
        $custStmt = $db->prepare(
            'SELECT id, first_name, last_name, email, loyalty_balance
             FROM oretir_customers WHERE id = ? LIMIT 1'
        );
        $custStmt->execute([$customerId]);
        $customer = $custStmt->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            jsonError('Customer not found.', 404);
        }

        $limit  = max(1, min(500, (int) ($_GET['limit'] ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));

        // Get total count
        $countStmt = $db->prepare(
            'SELECT COUNT(*) FROM oretir_loyalty_points WHERE customer_id = ?'
        );
        $countStmt->execute([$customerId]);
        $total = (int) $countStmt->fetchColumn();

        // Get ledger entries
        $entries = getLoyaltyHistory($db, $customerId, $limit, $offset);

        $page = (int) floor($offset / $limit) + 1;

        jsonSuccess([
            'customer' => $customer,
            'balance'  => (int) $customer['loyalty_balance'],
            'entries'  => $entries,
            'meta'     => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $limit,
                'pages'    => (int) ceil($total / max(1, $limit)),
            ],
        ]);
    }

    // ─── POST: Manual point adjustment ──────────────────────────────
    verifyCsrf();
    $data = getJsonBody();

    $customerId = (int) ($data['customer_id'] ?? 0);
    if ($customerId <= 0) {
        jsonError('customer_id is required.', 400);
    }

    $points = (int) ($data['points'] ?? 0);
    if ($points === 0) {
        jsonError('Points must be a non-zero integer.', 400);
    }

    $description = sanitize((string) ($data['description'] ?? ''), 255);
    if ($description === '') {
        jsonError('Description is required for manual adjustments.', 400);
    }

    // Verify customer exists
    $custStmt = $db->prepare('SELECT id FROM oretir_customers WHERE id = ? LIMIT 1');
    $custStmt->execute([$customerId]);
    if (!$custStmt->fetch()) {
        jsonError('Customer not found.', 404);
    }

    if ($points > 0) {
        // Positive adjustment — award points
        $success = awardLoyaltyPoints(
            $db,
            $customerId,
            $points,
            'adjust',
            $description . ' (by ' . ($admin['name'] ?? 'admin') . ')'
        );
        if (!$success) {
            jsonError('Failed to award points.', 500);
        }
    } else {
        // Negative adjustment — redeem/deduct points
        $result = redeemLoyaltyPoints(
            $db,
            $customerId,
            abs($points),
            $description . ' (by ' . ($admin['name'] ?? 'admin') . ')'
        );
        if (!$result['success']) {
            jsonError($result['error'] ?? 'Failed to deduct points.', 400);
        }
    }

    $newBalance = getLoyaltyBalance($db, $customerId);
    jsonSuccess([
        'message'     => 'Points adjusted successfully.',
        'new_balance' => $newBalance,
    ]);

} catch (\Throwable $e) {
    error_log('admin/loyalty.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
