<?php
/**
 * Oregon Tires — Member Referral Info
 * GET /api/member/my-referral.php
 *
 * Returns the logged-in member's referral code, successful referral count,
 * and total points earned from referrals.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
require_once __DIR__ . '/../../includes/referrals.php';

try {
    requireMethod('GET');
    startSecureSession();
    $pdo = getDB();
    initMemberKit($pdo);

    if (empty($_SESSION['member_id'])) {
        jsonError('Authentication required', 401);
    }

    $memberId = (int) $_SESSION['member_id'];

    // Find the customer record linked to this member
    $stmt = $pdo->prepare(
        'SELECT id FROM oretir_customers WHERE member_id = ? LIMIT 1'
    );
    $stmt->execute([$memberId]);
    $customerId = $stmt->fetchColumn();

    if (!$customerId) {
        // Try by email
        $emailStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
        $emailStmt->execute([$memberId]);
        $email = $emailStmt->fetchColumn();

        if ($email) {
            $custStmt = $pdo->prepare('SELECT id FROM oretir_customers WHERE email = ? LIMIT 1');
            $custStmt->execute([$email]);
            $customerId = $custStmt->fetchColumn();
        }
    }

    if (!$customerId) {
        jsonSuccess([
            'referral_code'      => null,
            'successful_referrals' => 0,
            'total_points_earned'  => 0,
        ]);
    }

    $customerId = (int) $customerId;

    // Get or create the referral code
    $referralCode = getOrCreateReferralCode($pdo, $customerId);

    // Count successful referrals
    $countStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM oretir_referrals
         WHERE referrer_customer_id = ? AND status = 'rewarded'"
    );
    $countStmt->execute([$customerId]);
    $successfulReferrals = (int) $countStmt->fetchColumn();

    // Total points earned from referrals
    $pointsStmt = $pdo->prepare(
        "SELECT COALESCE(SUM(points), 0) FROM oretir_loyalty_points
         WHERE customer_id = ? AND type = 'earn_referral' AND points > 0"
    );
    $pointsStmt->execute([$customerId]);
    $totalPointsEarned = (int) $pointsStmt->fetchColumn();

    jsonSuccess([
        'referral_code'        => $referralCode,
        'successful_referrals' => $successfulReferrals,
        'total_points_earned'  => $totalPointsEarned,
    ]);

} catch (\Throwable $e) {
    error_log('my-referral.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
