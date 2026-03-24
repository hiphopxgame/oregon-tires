<?php
/**
 * Oregon Tires — Admin Health Dashboard API
 * GET /api/admin/health-dashboard.php           — full dashboard summary
 * GET /api/admin/health-dashboard.php?days=N    — uptime chart for N days
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    $staff = requirePermission('settings');
    requireMethod('GET');
    $db = getDB();

    $days = max(1, min(90, (int) ($_GET['days'] ?? 30)));

    // ── Uptime percentages ──────────────────────────────────────────────
    $uptimeQuery = function (string $interval) use ($db): float {
        return (float) $db->query(
            "SELECT COALESCE(
                COUNT(CASE WHEN status = 'ok' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 0
            ) FROM oretir_health_checks
            WHERE check_type = 'uptime' AND checked_at >= DATE_SUB(NOW(), INTERVAL {$interval})"
        )->fetchColumn();
    };
    $uptime24h = round($uptimeQuery('24 HOUR'), 1);
    $uptime7d  = round($uptimeQuery('7 DAY'), 1);
    $uptime30d = round($uptimeQuery('30 DAY'), 1);

    // ── Overall status ──────────────────────────────────────────────────
    $recentFails = (int) $db->query(
        "SELECT COUNT(*) FROM oretir_health_checks
         WHERE status = 'fail' AND checked_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    )->fetchColumn();
    $recentWarns = (int) $db->query(
        "SELECT COUNT(*) FROM oretir_health_checks
         WHERE status = 'warn' AND checked_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    )->fetchColumn();
    $overallStatus = $recentFails > 0 ? 'critical' : ($recentWarns > 0 ? 'degraded' : 'healthy');

    // ── SSL info (latest) ───────────────────────────────────────────────
    $sslRow = $db->query(
        "SELECT status, details, checked_at FROM oretir_health_checks
         WHERE check_type = 'ssl' ORDER BY checked_at DESC LIMIT 1"
    )->fetch(\PDO::FETCH_ASSOC);
    $ssl = null;
    if ($sslRow) {
        $ssl = json_decode($sslRow['details'] ?: '{}', true);
        $ssl['status']     = $sslRow['status'];
        $ssl['checked_at'] = $sslRow['checked_at'];
    }

    // ── Last backup ─────────────────────────────────────────────────────
    $backupRow = $db->query(
        "SELECT status, details, checked_at FROM oretir_health_checks
         WHERE check_type = 'backup' ORDER BY checked_at DESC LIMIT 1"
    )->fetch(\PDO::FETCH_ASSOC);
    $lastBackup = null;
    if ($backupRow) {
        $lastBackup = json_decode($backupRow['details'] ?: '{}', true);
        $lastBackup['status']     = $backupRow['status'];
        $lastBackup['checked_at'] = $backupRow['checked_at'];
    }

    // ── Backup history (last 7) ─────────────────────────────────────────
    $backupHistory = $db->query(
        "SELECT status, details, checked_at FROM oretir_health_checks
         WHERE check_type = 'backup' ORDER BY checked_at DESC LIMIT 7"
    )->fetchAll(\PDO::FETCH_ASSOC);

    // ── Disk usage (latest) ─────────────────────────────────────────────
    $diskRow = $db->query(
        "SELECT status, details FROM oretir_health_checks
         WHERE check_type = 'disk' ORDER BY checked_at DESC LIMIT 1"
    )->fetch(\PDO::FETCH_ASSOC);
    $disk = $diskRow ? json_decode($diskRow['details'] ?: '{}', true) : null;
    if ($disk && $diskRow) $disk['status'] = $diskRow['status'];

    // ── Feature tests (latest run) ──────────────────────────────────────
    $latestTestTime = $db->query(
        "SELECT MAX(checked_at) FROM oretir_health_checks WHERE check_type = 'feature_test'"
    )->fetchColumn();
    $featureTests = [];
    if ($latestTestTime) {
        $stmt = $db->prepare(
            "SELECT label, status, details, response_time_ms, checked_at FROM oretir_health_checks
             WHERE check_type = 'feature_test' AND checked_at = ? ORDER BY label"
        );
        $stmt->execute([$latestTestTime]);
        $featureTests = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    $testsPassed = count(array_filter($featureTests, fn($t) => $t['status'] === 'ok'));

    // ── Cron status ─────────────────────────────────────────────────────
    $cronStmt = $db->query(
        "SELECT h.label, h.status, h.details, h.checked_at
         FROM oretir_health_checks h
         INNER JOIN (
             SELECT label, MAX(checked_at) as max_at
             FROM oretir_health_checks WHERE check_type = 'cron_freshness'
             GROUP BY label
         ) latest ON h.label = latest.label AND h.checked_at = latest.max_at
         WHERE h.check_type = 'cron_freshness'
         ORDER BY h.label"
    );
    $cronStatus = $cronStmt->fetchAll(\PDO::FETCH_ASSOC);

    // ── Recent incidents (7 days) ───────────────────────────────────────
    $incidents = $db->query(
        "SELECT label, check_type, status, details, response_time_ms, checked_at
         FROM oretir_health_checks
         WHERE status IN ('warn','fail') AND checked_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY checked_at DESC LIMIT 20"
    )->fetchAll(\PDO::FETCH_ASSOC);

    // ── Response times (24h avg per label) ──────────────────────────────
    $responseTimes = $db->query(
        "SELECT label, ROUND(AVG(response_time_ms)) AS avg_ms, COUNT(*) AS checks
         FROM oretir_health_checks
         WHERE check_type = 'uptime' AND response_time_ms IS NOT NULL
           AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
         GROUP BY label ORDER BY avg_ms DESC"
    )->fetchAll(\PDO::FETCH_ASSOC);

    // ── Uptime chart (daily) ────────────────────────────────────────────
    $chartStmt = $db->prepare(
        "SELECT DATE(checked_at) AS date,
                COUNT(CASE WHEN status = 'ok' THEN 1 END) * 100.0 / COUNT(*) AS uptime_pct,
                COUNT(*) AS checks
         FROM oretir_health_checks
         WHERE check_type = 'uptime' AND checked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         GROUP BY DATE(checked_at) ORDER BY date"
    );
    $chartStmt->execute([$days]);
    $uptimeChart = $chartStmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($uptimeChart as &$row) {
        $row['uptime_pct'] = round((float) $row['uptime_pct'], 1);
        $row['checks']     = (int) $row['checks'];
    }
    unset($row);

    // ── Last check time ─────────────────────────────────────────────────
    $lastCheck = $db->query("SELECT MAX(checked_at) FROM oretir_health_checks")->fetchColumn();

    jsonSuccess([
        'overall_status' => $overallStatus,
        'last_check'     => $lastCheck,
        'uptime_24h'     => $uptime24h,
        'uptime_7d'      => $uptime7d,
        'uptime_30d'     => $uptime30d,
        'ssl'            => $ssl,
        'last_backup'    => $lastBackup,
        'backup_history' => $backupHistory,
        'disk'           => $disk,
        'feature_tests'  => [
            'passed'   => $testsPassed,
            'failed'   => count($featureTests) - $testsPassed,
            'last_run' => $latestTestTime,
            'tests'    => $featureTests,
        ],
        'cron_status'    => $cronStatus,
        'incidents'      => $incidents,
        'response_times' => $responseTimes,
        'uptime_chart'   => $uptimeChart,
    ]);

} catch (\Throwable $e) {
    error_log('health-dashboard.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
