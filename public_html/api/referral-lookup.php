<?php
/**
 * Oregon Tires — Referral Code Lookup
 * GET /api/referral-lookup.php?code=XXXXXX
 *
 * Public endpoint to validate a referral code.
 * Returns the referrer's first name for display in the booking form.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/referrals.php';

try {
    requireMethod('GET');

    // Rate limit: 20 lookups per hour per IP
    checkRateLimit('referral_lookup', 20, 3600);

    $code = strtoupper(trim(sanitize((string) ($_GET['code'] ?? ''), 10)));

    if (strlen($code) < 4) {
        jsonError('Invalid referral code.');
    }

    $db = getDB();
    $referrer = validateReferralCode($db, $code);

    if (!$referrer) {
        jsonError('Referral code not found.', 404);
    }

    jsonSuccess([
        'valid'      => true,
        'first_name' => $referrer['first_name'],
    ]);

} catch (\Throwable $e) {
    error_log('referral-lookup.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
