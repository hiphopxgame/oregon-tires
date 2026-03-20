<?php
/**
 * Oregon Tires — Admin Loyalty Rewards Catalog CRUD
 * GET    /api/admin/loyalty-rewards.php           — list all rewards
 * POST   /api/admin/loyalty-rewards.php           — create reward
 * PUT    /api/admin/loyalty-rewards.php           — update reward
 * DELETE /api/admin/loyalty-rewards.php           — deactivate reward (soft delete)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    requirePermission('marketing');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List all rewards ──────────────────────────────────────
    if ($method === 'GET') {
        $includeInactive = (int) ($_GET['include_inactive'] ?? 0);

        if ($includeInactive) {
            $stmt = $db->query(
                'SELECT * FROM oretir_loyalty_rewards ORDER BY is_active DESC, points_cost ASC'
            );
        } else {
            $stmt = $db->query(
                'SELECT * FROM oretir_loyalty_rewards WHERE is_active = 1 ORDER BY points_cost ASC'
            );
        }

        jsonSuccess($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ─── Mutating requests require CSRF ─────────────────────────────
    verifyCsrf();
    $body = getJsonBody();

    // ─── POST: Create new reward ────────────────────────────────────
    if ($method === 'POST') {
        $nameEn = trim((string) ($body['name_en'] ?? ''));
        if ($nameEn === '') {
            jsonError('English name is required.', 400);
        }

        $pointsCost = (int) ($body['points_cost'] ?? 0);
        if ($pointsCost <= 0) {
            jsonError('Points cost must be greater than zero.', 400);
        }

        $rewardType = (string) ($body['reward_type'] ?? 'discount_flat');
        $validTypes = ['discount_pct', 'discount_flat', 'free_service', 'custom'];
        if (!in_array($rewardType, $validTypes, true)) {
            jsonError('Invalid reward type. Must be one of: ' . implode(', ', $validTypes), 400);
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_loyalty_rewards
                (name_en, name_es, description_en, description_es, points_cost, reward_type, reward_value)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            sanitize($nameEn, 100),
            sanitize(trim((string) ($body['name_es'] ?? '')), 100),
            sanitize(trim((string) ($body['description_en'] ?? '')), 500),
            sanitize(trim((string) ($body['description_es'] ?? '')), 500),
            $pointsCost,
            $rewardType,
            round((float) ($body['reward_value'] ?? 0), 2),
        ]);

        jsonSuccess(['id' => (int) $db->lastInsertId()]);
    }

    // ─── PUT: Update existing reward ────────────────────────────────
    if ($method === 'PUT') {
        $id = (int) ($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing reward id.', 400);
        }

        // Verify reward exists
        $checkStmt = $db->prepare('SELECT id FROM oretir_loyalty_rewards WHERE id = ? LIMIT 1');
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            jsonError('Reward not found.', 404);
        }

        $fields = [];
        $params = [];

        if (isset($body['name_en'])) {
            $fields[] = 'name_en = ?';
            $params[] = sanitize(trim((string) $body['name_en']), 100);
        }
        if (isset($body['name_es'])) {
            $fields[] = 'name_es = ?';
            $params[] = sanitize(trim((string) $body['name_es']), 100);
        }
        if (isset($body['description_en'])) {
            $fields[] = 'description_en = ?';
            $params[] = sanitize(trim((string) $body['description_en']), 500);
        }
        if (isset($body['description_es'])) {
            $fields[] = 'description_es = ?';
            $params[] = sanitize(trim((string) $body['description_es']), 500);
        }
        if (isset($body['points_cost'])) {
            $pointsCost = (int) $body['points_cost'];
            if ($pointsCost <= 0) {
                jsonError('Points cost must be greater than zero.', 400);
            }
            $fields[] = 'points_cost = ?';
            $params[] = $pointsCost;
        }
        if (isset($body['reward_type'])) {
            $rewardType = (string) $body['reward_type'];
            $validTypes = ['discount_pct', 'discount_flat', 'free_service', 'custom'];
            if (!in_array($rewardType, $validTypes, true)) {
                jsonError('Invalid reward type.', 400);
            }
            $fields[] = 'reward_type = ?';
            $params[] = $rewardType;
        }
        if (isset($body['reward_value'])) {
            $fields[] = 'reward_value = ?';
            $params[] = round((float) $body['reward_value'], 2);
        }
        if (isset($body['is_active'])) {
            $fields[] = 'is_active = ?';
            $params[] = (int) (bool) $body['is_active'];
        }

        if (empty($fields)) {
            jsonError('No fields to update.', 400);
        }

        $params[] = $id;
        $db->prepare(
            'UPDATE oretir_loyalty_rewards SET ' . implode(', ', $fields) . ' WHERE id = ?'
        )->execute($params);

        jsonSuccess(['updated' => true]);
    }

    // ─── DELETE: Soft-delete (deactivate) reward ────────────────────
    if ($method === 'DELETE') {
        $id = (int) ($body['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Missing reward id.', 400);
        }

        $stmt = $db->prepare(
            'UPDATE oretir_loyalty_rewards SET is_active = 0 WHERE id = ?'
        );
        $stmt->execute([$id]);

        jsonSuccess(['deactivated' => true]);
    }

} catch (\Throwable $e) {
    error_log('admin/loyalty-rewards.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
