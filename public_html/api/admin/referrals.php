<?php
/**
 * Oregon Tires — Admin Referrals Management
 * GET  /api/admin/referrals.php?status=  — list referrals with stats
 * PUT  /api/admin/referrals.php          — mark referral complete
 * POST /api/admin/referrals.php          — award points
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/referrals.php';
require_once __DIR__ . '/../../includes/loyalty.php';

try {
    startSecureSession();
    $admin = requirePermission('marketing');
    requireMethod('GET', 'PUT', 'POST', 'DELETE');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List referrals with stats ───────────────────────────
    if ($method === 'GET') {
        $allowedStatuses = ['pending', 'booked', 'completed', 'rewarded', 'expired'];
        $statusFilter = $_GET['status'] ?? '';

        $where = '';
        $params = [];
        if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses, true)) {
            $where = ' WHERE r.status = ?';
            $params[] = $statusFilter;
        }

        // Fetch referrals with customer names
        $sql = "SELECT r.id, r.referral_code, r.status,
                       (r.referrer_points + r.referred_points) AS bonus_points,
                       r.created_at,
                       CONCAT(rc.first_name, ' ', rc.last_name) AS referrer_name,
                       CASE WHEN rd.id IS NOT NULL
                            THEN CONCAT(rd.first_name, ' ', rd.last_name)
                            ELSE r.referred_email END AS referred_name
                FROM oretir_referrals r
                JOIN oretir_customers rc ON rc.id = r.referrer_customer_id
                LEFT JOIN oretir_customers rd ON rd.id = r.referred_customer_id
                {$where}
                ORDER BY r.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compute stats (over all referrals, ignoring filter)
        $statsStmt = $db->query(
            "SELECT COUNT(*) AS total,
                    SUM(status IN ('completed','rewarded')) AS completed,
                    SUM(CASE WHEN status = 'rewarded' THEN referrer_points + referred_points ELSE 0 END) AS points_awarded
             FROM oretir_referrals"
        );
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data'    => $referrals,
            'stats'   => [
                'total'          => (int) ($stats['total'] ?? 0),
                'completed'      => (int) ($stats['completed'] ?? 0),
                'points_awarded' => (int) ($stats['points_awarded'] ?? 0),
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── DELETE: Remove referral(s) ────────────────────────────────
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
            $db->prepare("DELETE FROM oretir_referrals WHERE id IN ($placeholders)")->execute($ids);
            $db->commit();
            jsonSuccess(['deleted' => count($ids)]);
        }

        // ── Single delete ──
        requireAdmin();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Referral ID is required.', 400);

        $db->prepare('DELETE FROM oretir_referrals WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => 1]);
    }

    // ─── PUT: Mark referral complete ──────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Referral ID is required.', 400);
        }

        // Fetch current referral
        $stmt = $db->prepare('SELECT id, status FROM oretir_referrals WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $referral = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$referral) {
            jsonError('Referral not found.', 404);
        }
        if (!in_array($referral['status'], ['pending', 'booked'], true)) {
            jsonError('Referral cannot be marked complete from status: ' . $referral['status'], 400);
        }

        $db->prepare(
            'UPDATE oretir_referrals SET status = ?, updated_at = NOW() WHERE id = ?'
        )->execute(['completed', $id]);

        jsonSuccess(['message' => 'Referral marked complete.']);
    }

    // ─── POST: Award points ───────────────────────────────────────
    verifyCsrf();
    $data = getJsonBody();

    $action = $data['action'] ?? '';
    if ($action !== 'award_points') {
        jsonError('Unknown action.', 400);
    }

    $id = (int) ($data['id'] ?? 0);
    if ($id <= 0) {
        jsonError('Referral ID is required.', 400);
    }

    // Fetch referral
    $stmt = $db->prepare(
        'SELECT r.*, CONCAT(rc.first_name, \' \', rc.last_name) AS referrer_name
         FROM oretir_referrals r
         JOIN oretir_customers rc ON rc.id = r.referrer_customer_id
         WHERE r.id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $referral = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$referral) {
        jsonError('Referral not found.', 404);
    }
    if ($referral['status'] !== 'completed') {
        jsonError('Referral must be in completed status to award points.', 400);
    }
    if (empty($referral['referred_customer_id'])) {
        jsonError('Cannot award points: no referred customer linked.', 400);
    }

    $referrerId  = (int) $referral['referrer_customer_id'];
    $referredId  = (int) $referral['referred_customer_id'];
    $referrerPts = (int) $referral['referrer_points'];
    $referredPts = (int) $referral['referred_points'];

    // Award points to referrer
    awardLoyaltyPoints(
        $db,
        $referrerId,
        $referrerPts,
        'earn_referral',
        'Referral bonus: referred a new customer',
        'referral',
        $referredId
    );

    // Award points to referred customer
    awardLoyaltyPoints(
        $db,
        $referredId,
        $referredPts,
        'earn_referral',
        'Welcome bonus: referred by ' . ($referral['referrer_name'] ?? 'a friend'),
        'referral',
        $referrerId
    );

    // Update status to rewarded
    $db->prepare(
        'UPDATE oretir_referrals SET status = ?, updated_at = NOW() WHERE id = ?'
    )->execute(['rewarded', $id]);

    jsonSuccess(['message' => 'Points awarded successfully.']);

} catch (\Throwable $e) {
    error_log('admin/referrals.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
