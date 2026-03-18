<?php
/**
 * Oregon Tires — Customer Loyalty & Rewards System
 *
 * Functions for awarding, redeeming, and querying loyalty points.
 * All point mutations are atomic (transactions) to prevent balance drift.
 */

declare(strict_types=1);

/**
 * Award loyalty points to a customer.
 * Inserts a ledger entry and updates the cached balance on oretir_customers.
 */
function awardLoyaltyPoints(
    PDO $db,
    int $customerId,
    int $points,
    string $type,
    string $description,
    ?string $referenceType = null,
    ?int $referenceId = null
): bool {
    if ($points <= 0) {
        return false;
    }

    $db->beginTransaction();
    try {
        // Lock the customer row and read current balance
        $stmt = $db->prepare(
            'SELECT loyalty_balance FROM oretir_customers WHERE id = ? FOR UPDATE'
        );
        $stmt->execute([$customerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $db->rollBack();
            return false;
        }

        $currentBalance = (int) $row['loyalty_balance'];
        $newBalance = $currentBalance + $points;

        // Insert ledger entry
        $insert = $db->prepare(
            'INSERT INTO oretir_loyalty_points
                (customer_id, points, balance_after, type, description, reference_type, reference_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $customerId,
            $points,
            $newBalance,
            $type,
            $description,
            $referenceType,
            $referenceId,
        ]);

        // Update cached balance
        $update = $db->prepare(
            'UPDATE oretir_customers SET loyalty_balance = ? WHERE id = ?'
        );
        $update->execute([$newBalance, $customerId]);

        $db->commit();
        return true;
    } catch (\Throwable $e) {
        $db->rollBack();
        error_log('awardLoyaltyPoints error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Redeem loyalty points from a customer's balance.
 * Returns an associative array with success status, optional error, and new balance.
 */
function redeemLoyaltyPoints(
    PDO $db,
    int $customerId,
    int $points,
    string $description,
    ?string $referenceType = null,
    ?int $referenceId = null
): array {
    if ($points <= 0) {
        return ['success' => false, 'error' => 'Points must be positive.', 'new_balance' => 0];
    }

    $db->beginTransaction();
    try {
        // Lock the customer row and read current balance
        $stmt = $db->prepare(
            'SELECT loyalty_balance FROM oretir_customers WHERE id = ? FOR UPDATE'
        );
        $stmt->execute([$customerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Customer not found.', 'new_balance' => 0];
        }

        $currentBalance = (int) $row['loyalty_balance'];

        if ($currentBalance < $points) {
            $db->rollBack();
            return [
                'success'     => false,
                'error'       => 'Insufficient points. Current balance: ' . $currentBalance,
                'new_balance' => $currentBalance,
            ];
        }

        $newBalance = $currentBalance - $points;

        // Insert ledger entry (negative points for redemption)
        $insert = $db->prepare(
            'INSERT INTO oretir_loyalty_points
                (customer_id, points, balance_after, type, description, reference_type, reference_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $customerId,
            -$points,
            $newBalance,
            'redeem',
            $description,
            $referenceType,
            $referenceId,
        ]);

        // Update cached balance
        $update = $db->prepare(
            'UPDATE oretir_customers SET loyalty_balance = ? WHERE id = ?'
        );
        $update->execute([$newBalance, $customerId]);

        $db->commit();
        return ['success' => true, 'error' => null, 'new_balance' => $newBalance];
    } catch (\Throwable $e) {
        $db->rollBack();
        error_log('redeemLoyaltyPoints error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Server error.', 'new_balance' => 0];
    }
}

/**
 * Get a customer's current loyalty balance.
 */
function getLoyaltyBalance(PDO $db, int $customerId): int
{
    $stmt = $db->prepare('SELECT loyalty_balance FROM oretir_customers WHERE id = ? LIMIT 1');
    $stmt->execute([$customerId]);
    $balance = $stmt->fetchColumn();
    return $balance !== false ? (int) $balance : 0;
}

/**
 * Get a customer's loyalty point history (ledger entries).
 */
function getLoyaltyHistory(PDO $db, int $customerId, int $limit = 50, int $offset = 0): array
{
    $stmt = $db->prepare(
        'SELECT id, points, balance_after, type, description, reference_type, reference_id, created_at
         FROM oretir_loyalty_points
         WHERE customer_id = ?
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([$customerId, $limit, $offset]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all active rewards from the catalog.
 */
function getAvailableRewards(PDO $db): array
{
    $stmt = $db->query(
        'SELECT id, name_en, name_es, description_en, description_es,
                points_cost, reward_type, reward_value
         FROM oretir_loyalty_rewards
         WHERE is_active = 1
         ORDER BY points_cost ASC'
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Award points for a completed visit (repair order).
 * Points = max(min_points_per_visit, floor(totalAmount * points_per_dollar)).
 * Also updates visit_count and last_visit_at on the customer record.
 */
function awardVisitPoints(PDO $db, int $customerId, float $totalAmount, int $roId): bool
{
    // Check if loyalty is enabled
    $enabledStmt = $db->prepare(
        "SELECT value_en FROM oretir_site_settings WHERE setting_key = 'loyalty_enabled' LIMIT 1"
    );
    $enabledStmt->execute();
    $enabled = $enabledStmt->fetchColumn();
    if ($enabled !== '1') {
        return false;
    }

    // Read loyalty settings
    $settingsStmt = $db->prepare(
        "SELECT setting_key, value_en FROM oretir_site_settings
         WHERE setting_key IN ('loyalty_points_per_dollar', 'loyalty_min_points_per_visit')"
    );
    $settingsStmt->execute();
    $settings = [];
    while ($row = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['value_en'];
    }

    $pointsPerDollar = (int) ($settings['loyalty_points_per_dollar'] ?? 1);
    $minPointsPerVisit = (int) ($settings['loyalty_min_points_per_visit'] ?? 50);

    // Prevent duplicate award for same RO
    $dupeStmt = $db->prepare(
        "SELECT id FROM oretir_loyalty_points
         WHERE customer_id = ? AND reference_type = 'repair_order' AND reference_id = ? AND type = 'earn_visit'
         LIMIT 1"
    );
    $dupeStmt->execute([$customerId, $roId]);
    if ($dupeStmt->fetch()) {
        return false; // Already awarded for this RO
    }

    // Calculate points
    $calculatedPoints = (int) floor($totalAmount * $pointsPerDollar);
    $points = max($minPointsPerVisit, $calculatedPoints);

    $description = 'Points earned for service visit (RO #' . $roId . ')';
    $awarded = awardLoyaltyPoints($db, $customerId, $points, 'earn_visit', $description, 'repair_order', $roId);

    if ($awarded) {
        // Update visit tracking on customer record
        try {
            $db->prepare(
                'UPDATE oretir_customers SET visit_count = visit_count + 1, last_visit_at = NOW() WHERE id = ?'
            )->execute([$customerId]);
        } catch (\Throwable $e) {
            error_log('awardVisitPoints visit tracking update error: ' . $e->getMessage());
        }
    }

    return $awarded;
}
