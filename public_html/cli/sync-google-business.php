<?php
/**
 * Oregon Tires — Google Business Profile Sync (CLI)
 *
 * Syncs business hours and holidays to Google Business Profile.
 * Run via cron or manually: php cli/sync-google-business.php
 *
 * Required env vars:
 *   GOOGLE_BUSINESS_ACCOUNT_ID
 *   GOOGLE_BUSINESS_LOCATION_ID
 *   GOOGLE_BUSINESS_API_KEY (or OAuth token)
 */

declare(strict_types=1);

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CLI only']);
    exit(1);
}

require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();

// ─── Check required env vars ─────────────────────────────────────────────
$accountId = $_ENV['GOOGLE_BUSINESS_ACCOUNT_ID'] ?? '';
$locationId = $_ENV['GOOGLE_BUSINESS_LOCATION_ID'] ?? '';

if (empty($accountId) || empty($locationId)) {
    echo "[" . date('Y-m-d H:i:s') . "] Google Business API not configured. Skipping sync.\n";
    echo "  Set GOOGLE_BUSINESS_ACCOUNT_ID and GOOGLE_BUSINESS_LOCATION_ID in .env\n";

    // Update last sync status
    try {
        $db->prepare(
            "INSERT INTO oretir_site_settings (setting_key, value_en, value_es)
             VALUES ('google_business_last_sync', ?, ?)
             ON DUPLICATE KEY UPDATE value_en = ?, value_es = ?"
        )->execute([
            json_encode(['status' => 'not_configured', 'timestamp' => date('c')]),
            json_encode(['status' => 'not_configured', 'timestamp' => date('c')]),
            json_encode(['status' => 'not_configured', 'timestamp' => date('c')]),
            json_encode(['status' => 'not_configured', 'timestamp' => date('c')]),
        ]);
    } catch (\Throwable $e) {
        error_log('sync-google-business.php setting update error: ' . $e->getMessage());
    }

    exit(0);
}

// ─── Fetch business hours from site settings ────────────────────────────
echo "[" . date('Y-m-d H:i:s') . "] Fetching business hours from database...\n";

$stmt = $db->query(
    "SELECT setting_key, value_en FROM oretir_site_settings
     WHERE setting_key LIKE 'business_hours_%'
     ORDER BY setting_key ASC"
);
$hoursSettings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $hoursSettings[$row['setting_key']] = $row['value_en'];
}

// Map day names to Google API format
$dayMap = [
    'monday'    => 'MONDAY',
    'tuesday'   => 'TUESDAY',
    'wednesday' => 'WEDNESDAY',
    'thursday'  => 'THURSDAY',
    'friday'    => 'FRIDAY',
    'saturday'  => 'SATURDAY',
    'sunday'    => 'SUNDAY',
];

$regularHours = [];
foreach ($dayMap as $dayKey => $googleDay) {
    $openKey = "business_hours_{$dayKey}_open";
    $closeKey = "business_hours_{$dayKey}_close";

    $openTime = $hoursSettings[$openKey] ?? null;
    $closeTime = $hoursSettings[$closeKey] ?? null;

    if ($openTime && $closeTime && $openTime !== 'closed') {
        $regularHours[] = [
            'openDay'  => $googleDay,
            'closeDay' => $googleDay,
            'openTime' => ['hours' => (int) explode(':', $openTime)[0], 'minutes' => (int) (explode(':', $openTime)[1] ?? 0)],
            'closeTime' => ['hours' => (int) explode(':', $closeTime)[0], 'minutes' => (int) (explode(':', $closeTime)[1] ?? 0)],
        ];
    }
}

echo "  Found " . count($regularHours) . " days with operating hours.\n";

// ─── Fetch holidays ─────────────────────────────────────────────────────
$stmt = $db->query(
    "SELECT setting_key, value_en FROM oretir_site_settings
     WHERE setting_key LIKE 'holiday_%_date'
     ORDER BY value_en ASC"
);
$holidays = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['value_en'];
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $holidays[] = $date;
    }
}

echo "  Found " . count($holidays) . " holidays configured.\n";

// ─── Build Google Business API payload ──────────────────────────────────
$payload = [
    'regularHours' => ['periods' => $regularHours],
    // Special hours for holidays (closed)
    'specialHours' => ['specialHourPeriods' => array_map(function (string $date) {
        $parts = explode('-', $date);
        return [
            'startDate' => ['year' => (int) $parts[0], 'month' => (int) $parts[1], 'day' => (int) $parts[2]],
            'endDate' => ['year' => (int) $parts[0], 'month' => (int) $parts[1], 'day' => (int) $parts[2]],
            'isClosed' => true,
        ];
    }, $holidays)],
];

echo "\n[" . date('Y-m-d H:i:s') . "] API Payload prepared:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// ─── TODO: Make actual API call when credentials are available ──────────
// The Google Business Profile API (v1) endpoint would be:
// PATCH https://mybusinessbusinessinformation.googleapis.com/v1/locations/{locationId}
// Headers: Authorization: Bearer {access_token}, Content-Type: application/json
// Body: $payload
// Update mask: regularHours,specialHours
//
// For now, we log the payload and record the sync attempt.

$syncResult = [
    'status'      => 'dry_run',
    'timestamp'   => date('c'),
    'hours_count' => count($regularHours),
    'holidays'    => count($holidays),
    'message'     => 'Payload prepared but API call skipped — credentials not configured.',
];

echo "[" . date('Y-m-d H:i:s') . "] Sync result: dry_run (API credentials needed)\n";
echo "  To enable: set GOOGLE_BUSINESS_ACCOUNT_ID and GOOGLE_BUSINESS_LOCATION_ID with valid OAuth credentials.\n";

// ─── Update last sync status in site settings ──────────────────────────
try {
    $syncJson = json_encode($syncResult);
    $db->prepare(
        "INSERT INTO oretir_site_settings (setting_key, value_en, value_es)
         VALUES ('google_business_last_sync', ?, ?)
         ON DUPLICATE KEY UPDATE value_en = ?, value_es = ?"
    )->execute([$syncJson, $syncJson, $syncJson, $syncJson]);

    echo "[" . date('Y-m-d H:i:s') . "] Sync status saved to database.\n";
} catch (\Throwable $e) {
    error_log('sync-google-business.php setting update error: ' . $e->getMessage());
    echo "[" . date('Y-m-d H:i:s') . "] Error saving sync status: " . $e->getMessage() . "\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Done.\n";
