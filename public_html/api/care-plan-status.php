<?php
/**
 * Oregon Tires — Care Plan Status Check
 * GET /api/care-plan-status.php?email=...
 *
 * Returns the current care plan status for a given email address.
 * Used by the customer dashboard and care-plan page.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $email = sanitize((string)($_GET['email'] ?? ''), 254);
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonError('Valid email required', 400);
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, plan_type, status, monthly_price, period_start, period_end, created_at
         FROM oretir_care_plans
         WHERE customer_email = ? AND status NOT IN (?)
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $stmt->execute([$email, 'cancelled']);
    $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$plan) {
        jsonSuccess(['enrolled' => false]);
    }

    jsonSuccess([
        'enrolled' => true,
        'plan'     => $plan,
    ]);
} catch (\Throwable $e) {
    error_log('care-plan-status error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
