<?php
/**
 * Oregon Tires — Admin Google Business Profile Sync
 * GET  /api/admin/google-business-sync.php — get last sync status
 * POST /api/admin/google-business-sync.php — trigger manual sync
 *
 * Requires admin session + CSRF for POST.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $admin = requireAdmin();
    requireMethod('GET', 'POST');

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: Return last sync status ───────────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->prepare(
            "SELECT value_en FROM oretir_site_settings WHERE setting_key = 'google_business_last_sync' LIMIT 1"
        );
        $stmt->execute();
        $raw = $stmt->fetchColumn();

        $lastSync = $raw ? json_decode($raw, true) : null;

        $accountId = $_ENV['GOOGLE_BUSINESS_ACCOUNT_ID'] ?? '';
        $locationId = $_ENV['GOOGLE_BUSINESS_LOCATION_ID'] ?? '';

        jsonSuccess([
            'configured' => !empty($accountId) && !empty($locationId),
            'last_sync'  => $lastSync,
        ]);
    }

    // ─── POST: Trigger manual sync ──────────────────────────────────────
    verifyCsrf();

    $accountId = $_ENV['GOOGLE_BUSINESS_ACCOUNT_ID'] ?? '';
    $locationId = $_ENV['GOOGLE_BUSINESS_LOCATION_ID'] ?? '';

    if (empty($accountId) || empty($locationId)) {
        // Not configured — just update the timestamp so admin knows they tried
        $syncResult = [
            'status'    => 'not_configured',
            'timestamp' => date('c'),
            'message'   => 'Google Business API not configured. Set GOOGLE_BUSINESS_ACCOUNT_ID and GOOGLE_BUSINESS_LOCATION_ID in .env.',
        ];

        $syncJson = json_encode($syncResult);
        $db->prepare(
            "INSERT INTO oretir_site_settings (setting_key, value_en, value_es)
             VALUES ('google_business_last_sync', ?, ?)
             ON DUPLICATE KEY UPDATE value_en = ?, value_es = ?"
        )->execute([$syncJson, $syncJson, $syncJson, $syncJson]);

        jsonSuccess([
            'configured' => false,
            'last_sync'  => $syncResult,
            'message'    => 'Google Business Profile sync is not configured. Set GOOGLE_BUSINESS_ACCOUNT_ID and GOOGLE_BUSINESS_LOCATION_ID environment variables.',
        ]);
    }

    // ─── Fetch business hours ───────────────────────────────────────────
    $hoursStmt = $db->query(
        "SELECT setting_key, value_en FROM oretir_site_settings
         WHERE setting_key LIKE 'business_hours_%'
         ORDER BY setting_key ASC"
    );
    $hoursSettings = [];
    while ($row = $hoursStmt->fetch(PDO::FETCH_ASSOC)) {
        $hoursSettings[$row['setting_key']] = $row['value_en'];
    }

    $dayMap = [
        'monday' => 'MONDAY', 'tuesday' => 'TUESDAY', 'wednesday' => 'WEDNESDAY',
        'thursday' => 'THURSDAY', 'friday' => 'FRIDAY', 'saturday' => 'SATURDAY', 'sunday' => 'SUNDAY',
    ];

    $regularHours = [];
    foreach ($dayMap as $dayKey => $googleDay) {
        $open = $hoursSettings["business_hours_{$dayKey}_open"] ?? null;
        $close = $hoursSettings["business_hours_{$dayKey}_close"] ?? null;
        if ($open && $close && $open !== 'closed') {
            $regularHours[] = ['day' => $googleDay, 'open' => $open, 'close' => $close];
        }
    }

    // ─── Fetch holidays ─────────────────────────────────────────────────
    $holidayStmt = $db->query(
        "SELECT value_en FROM oretir_site_settings WHERE setting_key LIKE 'holiday_%_date' ORDER BY value_en ASC"
    );
    $holidays = [];
    while ($row = $holidayStmt->fetch(PDO::FETCH_ASSOC)) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['value_en'])) {
            $holidays[] = $row['value_en'];
        }
    }

    // TODO: Make actual Google Business Profile API call when OAuth credentials are available
    // PATCH https://mybusinessbusinessinformation.googleapis.com/v1/locations/{locationId}
    // Authorization: Bearer {access_token}
    // Body: { regularHours: ..., specialHours: ... }

    $syncResult = [
        'status'      => 'dry_run',
        'timestamp'   => date('c'),
        'hours_count' => count($regularHours),
        'holidays'    => count($holidays),
        'triggered_by' => $admin['email'],
        'message'     => 'Sync payload prepared. Actual API call will be made once OAuth credentials are configured.',
    ];

    $syncJson = json_encode($syncResult);
    $db->prepare(
        "INSERT INTO oretir_site_settings (setting_key, value_en, value_es)
         VALUES ('google_business_last_sync', ?, ?)
         ON DUPLICATE KEY UPDATE value_en = ?, value_es = ?"
    )->execute([$syncJson, $syncJson, $syncJson, $syncJson]);

    jsonSuccess([
        'configured'   => true,
        'last_sync'    => $syncResult,
        'hours_synced' => $regularHours,
        'holidays'     => $holidays,
    ]);

} catch (\Throwable $e) {
    error_log('admin/google-business-sync.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
