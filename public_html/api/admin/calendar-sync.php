<?php
/**
 * Oregon Tires — Admin Calendar Sync Management
 * GET  — sync status overview, failed syncs
 * POST — manual sync trigger, retry failed, enable/disable toggle
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/google-calendar.php';

try {
    $staff = requirePermission('shop_ops');
    requireMethod('GET', 'POST');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        session_write_close();

        $enabled = isCalendarSyncEnabled();

        // Sync status counts
        $stmt = $db->query(
            "SELECT calendar_sync_status, COUNT(*) AS cnt
             FROM oretir_appointments
             WHERE calendar_sync_status IS NOT NULL
             GROUP BY calendar_sync_status"
        );
        $statusCounts = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $statusCounts[$row['calendar_sync_status']] = (int) $row['cnt'];
        }

        // Failed syncs detail
        $failedStmt = $db->query(
            "SELECT id, reference_number, first_name, last_name, preferred_date, preferred_time,
                    calendar_sync_error, calendar_sync_attempts
             FROM oretir_appointments
             WHERE calendar_sync_status = 'failed'
             ORDER BY updated_at DESC
             LIMIT 20"
        );
        $failedSyncs = $failedStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Last successful sync
        $lastStmt = $db->query(
            "SELECT calendar_synced_at FROM oretir_appointments
             WHERE calendar_sync_status = 'success'
             ORDER BY calendar_synced_at DESC LIMIT 1"
        );
        $lastSync = $lastStmt->fetchColumn();

        jsonSuccess([
            'enabled' => $enabled,
            'calendar_id' => $_ENV['GOOGLE_CALENDAR_ID'] ?? '',
            'status_counts' => $statusCounts,
            'failed_syncs' => $failedSyncs,
            'last_sync' => $lastSync,
        ]);
    }

    verifyCsrf();
    $data = getJsonBody();
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'sync_appointment':
            $apptId = (int) ($data['appointment_id'] ?? 0);
            if ($apptId <= 0) jsonError('Missing appointment_id.');

            $result = createCalendarEvent($db, $apptId);
            jsonSuccess($result);
            break;

        case 'retry_failed':
            // Retry all failed syncs (max 3 attempts)
            $stmt = $db->query(
                "SELECT id FROM oretir_appointments
                 WHERE calendar_sync_status = 'failed' AND calendar_sync_attempts < 3 AND status != 'cancelled'
                 LIMIT 10"
            );
            $failed = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            $results = [];
            foreach ($failed as $id) {
                $results[$id] = createCalendarEvent($db, (int) $id);
            }
            jsonSuccess(['retried' => count($failed), 'results' => $results]);
            break;

        case 'toggle':
            // This updates the .env-backed setting — for shared hosting, use site_settings
            $enabled = !empty($data['enabled']) ? '1' : '0';
            $db->prepare(
                "INSERT INTO oretir_site_settings (setting_key, value_en) VALUES ('google_calendar_sync_enabled', ?)
                 ON DUPLICATE KEY UPDATE value_en = VALUES(value_en)"
            )->execute([$enabled]);
            jsonSuccess(['enabled' => $enabled === '1']);
            break;

        default:
            jsonError('Invalid action', 400);
    }

} catch (\Throwable $e) {
    error_log("Admin calendar-sync error: " . $e->getMessage());
    jsonError('Server error', 500);
}
