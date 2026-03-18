<?php
/**
 * Oregon Tires — Customer Referral Program
 *
 * Functions for managing referral codes and processing referral rewards.
 */

declare(strict_types=1);

/**
 * Generate a unique 6-character uppercase alphanumeric referral code.
 */
function generateReferralCode(PDO $db): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no I, O, 0, 1 to avoid confusion
    $maxAttempts = 20;

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Check uniqueness across both tables
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM oretir_customers WHERE referral_code = ?
             UNION ALL
             SELECT COUNT(*) FROM oretir_referrals WHERE referral_code = ?'
        );
        $stmt->execute([$code, $code]);
        $counts = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (array_sum($counts) === 0) {
            return $code;
        }
    }

    // Fallback: append random suffix
    return strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/**
 * Get existing referral code for a customer, or generate and store a new one.
 */
function getOrCreateReferralCode(PDO $db, int $customerId): string
{
    // Check if customer already has a code
    $stmt = $db->prepare('SELECT referral_code FROM oretir_customers WHERE id = ? LIMIT 1');
    $stmt->execute([$customerId]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        return $existing;
    }

    // Generate new code and save to customer record
    $code = generateReferralCode($db);

    $db->prepare('UPDATE oretir_customers SET referral_code = ? WHERE id = ?')
       ->execute([$code, $customerId]);

    return $code;
}

/**
 * Validate a referral code and return referrer info.
 * Returns associative array with referrer details, or null if invalid.
 */
function validateReferralCode(PDO $db, string $code): ?array
{
    $code = strtoupper(trim($code));

    if (strlen($code) < 4 || strlen($code) > 10) {
        return null;
    }

    // Look up customer by referral_code
    $stmt = $db->prepare(
        'SELECT id, first_name, last_name, email
         FROM oretir_customers
         WHERE referral_code = ?
         LIMIT 1'
    );
    $stmt->execute([$code]);
    $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

    return $referrer ?: null;
}

/**
 * Process a referral: award points to both referrer and referred customer.
 * Updates the referral record status to 'rewarded'.
 *
 * @return array ['success' => bool, 'error' => ?string]
 */
function processReferral(PDO $db, string $referralCode, int $referredCustomerId): array
{
    require_once __DIR__ . '/loyalty.php';

    $referralCode = strtoupper(trim($referralCode));

    // Find the referrer by code
    $referrer = validateReferralCode($db, $referralCode);
    if (!$referrer) {
        return ['success' => false, 'error' => 'Invalid referral code.'];
    }

    $referrerId = (int) $referrer['id'];

    // Don't allow self-referral
    if ($referrerId === $referredCustomerId) {
        return ['success' => false, 'error' => 'Cannot use your own referral code.'];
    }

    // Check if this referred customer was already rewarded for this referrer
    $dupeStmt = $db->prepare(
        'SELECT id FROM oretir_referrals
         WHERE referrer_customer_id = ? AND referred_customer_id = ? AND status = ?
         LIMIT 1'
    );
    $dupeStmt->execute([$referrerId, $referredCustomerId, 'rewarded']);
    if ($dupeStmt->fetch()) {
        return ['success' => false, 'error' => 'Referral already rewarded.'];
    }

    $referrerPoints = 100;
    $referredPoints = 50;

    $db->beginTransaction();
    try {
        // Create or update referral record
        $existingStmt = $db->prepare(
            'SELECT id FROM oretir_referrals
             WHERE referral_code = ? AND referred_customer_id = ?
             LIMIT 1'
        );
        $existingStmt->execute([$referralCode, $referredCustomerId]);
        $existingReferral = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingReferral) {
            $db->prepare(
                'UPDATE oretir_referrals SET status = ?, referrer_points = ?, referred_points = ? WHERE id = ?'
            )->execute(['rewarded', $referrerPoints, $referredPoints, $existingReferral['id']]);
        } else {
            $db->prepare(
                'INSERT INTO oretir_referrals
                    (referrer_customer_id, referral_code, referred_customer_id, status, referrer_points, referred_points)
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([$referrerId, $referralCode, $referredCustomerId, 'rewarded', $referrerPoints, $referredPoints]);
        }

        $db->commit();
    } catch (\Throwable $e) {
        $db->rollBack();
        error_log('processReferral DB error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Server error processing referral.'];
    }

    // Award points to referrer
    $referrerName = trim($referrer['first_name'] . ' ' . $referrer['last_name']);
    awardLoyaltyPoints(
        $db,
        $referrerId,
        $referrerPoints,
        'earn_referral',
        "Referral bonus: referred a new customer",
        'referral',
        $referredCustomerId
    );

    // Award points to referred customer
    awardLoyaltyPoints(
        $db,
        $referredCustomerId,
        $referredPoints,
        'earn_referral',
        "Welcome bonus: referred by {$referrerName}",
        'referral',
        $referrerId
    );

    return ['success' => true, 'error' => null];
}
