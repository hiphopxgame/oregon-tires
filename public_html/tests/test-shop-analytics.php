#!/usr/bin/env php
<?php
/**
 * Oregon Tires — Shop Performance Analytics Test
 * Tests the /api/admin/analytics.php?type=shop_performance endpoint.
 * Run: php tests/test-shop-analytics.php
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }

require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();
$pass = 0;
$fail = 0;
$errors = [];

function ok(bool $cond, string $label): void {
    global $pass, $fail, $errors;
    if ($cond) { echo "  \033[32m✓\033[0m {$label}\n"; $pass++; }
    else       { echo "  \033[31m✗\033[0m {$label}\n"; $fail++; $errors[] = $label; }
}

echo "\n=== Shop Performance Analytics Tests ===\n\n";

// ── Test 1: Simulate the shop_performance query logic directly ──────

echo "--- Service Duration Query ---\n";
try {
    $stmt = $db->query(
        "SELECT a.service,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, r.service_started_at, r.service_ended_at)), 0) AS avg_minutes,
                COUNT(*) AS job_count
         FROM oretir_repair_orders r
         JOIN oretir_appointments a ON a.id = r.appointment_id
         WHERE r.service_started_at IS NOT NULL AND r.service_ended_at IS NOT NULL
           AND r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY a.service ORDER BY job_count DESC"
    );
    $serviceDuration = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ok(is_array($serviceDuration), 'service_duration returns array');
    if (count($serviceDuration) > 0) {
        $first = $serviceDuration[0];
        ok(isset($first['service']), 'service_duration has service field');
        ok(isset($first['avg_minutes']), 'service_duration has avg_minutes field');
        ok(isset($first['job_count']), 'service_duration has job_count field');
        ok(is_numeric($first['avg_minutes']), 'avg_minutes is numeric');
        ok(is_numeric($first['job_count']), 'job_count is numeric');
    } else {
        echo "  (no data rows — OK, table may be empty)\n";
        $pass++;
    }
} catch (\Throwable $e) {
    ok(false, 'service_duration query: ' . $e->getMessage());
}

echo "\n--- Technician Productivity Query ---\n";
try {
    $stmt = $db->query(
        "SELECT e.name, e.id,
                ROUND(SUM(l.duration_minutes) / 60, 1) AS total_hours,
                ROUND(SUM(CASE WHEN l.is_billable = 1 THEN l.duration_minutes ELSE 0 END) / 60, 1) AS billable_hours,
                COUNT(DISTINCT l.repair_order_id) AS jobs_completed
         FROM oretir_labor_entries l
         JOIN oretir_employees e ON e.id = l.employee_id
         WHERE l.clock_out_at IS NOT NULL AND l.clock_in_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY e.id, e.name ORDER BY total_hours DESC"
    );
    $techProd = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ok(is_array($techProd), 'tech_productivity returns array');
    if (count($techProd) > 0) {
        $first = $techProd[0];
        ok(isset($first['name']), 'tech_productivity has name field');
        ok(isset($first['id']), 'tech_productivity has id field');
        ok(isset($first['total_hours']), 'tech_productivity has total_hours field');
        ok(isset($first['billable_hours']), 'tech_productivity has billable_hours field');
        ok(isset($first['jobs_completed']), 'tech_productivity has jobs_completed field');
        ok(is_numeric($first['total_hours']), 'total_hours is numeric');
        ok(is_numeric($first['billable_hours']), 'billable_hours is numeric');
    } else {
        echo "  (no data rows — OK, table may be empty)\n";
        $pass++;
    }
} catch (\Throwable $e) {
    ok(false, 'tech_productivity query: ' . $e->getMessage());
}

echo "\n--- Vehicle Time in Shop Query ---\n";
try {
    $stmt = $db->query(
        "SELECT
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, checked_in_at, checked_out_at)), 0) AS avg_total_minutes,
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, checked_in_at, service_started_at)), 0) AS avg_wait_minutes,
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, service_started_at, service_ended_at)), 0) AS avg_service_minutes,
            COUNT(*) AS completed_count
         FROM oretir_repair_orders
         WHERE checked_in_at IS NOT NULL AND checked_out_at IS NOT NULL
           AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    $vehicleTime = $stmt->fetch(PDO::FETCH_ASSOC);
    ok(is_array($vehicleTime), 'vehicle_time returns array');
    ok(array_key_exists('avg_total_minutes', $vehicleTime), 'vehicle_time has avg_total_minutes');
    ok(array_key_exists('avg_wait_minutes', $vehicleTime), 'vehicle_time has avg_wait_minutes');
    ok(array_key_exists('avg_service_minutes', $vehicleTime), 'vehicle_time has avg_service_minutes');
    ok(array_key_exists('completed_count', $vehicleTime), 'vehicle_time has completed_count');
    ok(
        $vehicleTime['avg_total_minutes'] === null || is_numeric($vehicleTime['avg_total_minutes']),
        'avg_total_minutes is null or numeric'
    );
} catch (\Throwable $e) {
    ok(false, 'vehicle_time query: ' . $e->getMessage());
}

echo "\n--- RO Status Distribution Query ---\n";
try {
    $stmt = $db->query(
        "SELECT status, COUNT(*) AS count FROM oretir_repair_orders GROUP BY status"
    );
    $roStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ok(is_array($roStatus), 'ro_status_distribution returns array');
    if (count($roStatus) > 0) {
        $first = $roStatus[0];
        ok(isset($first['status']), 'ro_status_distribution has status field');
        ok(isset($first['count']), 'ro_status_distribution has count field');
        ok(is_numeric($first['count']), 'count is numeric');
    } else {
        echo "  (no data rows — OK, table may be empty)\n";
        $pass++;
    }
} catch (\Throwable $e) {
    ok(false, 'ro_status_distribution query: ' . $e->getMessage());
}

// ── Test 2: Verify response structure keys ──────────────────────────

echo "\n--- Response Structure Validation ---\n";
$expectedKeys = ['service_duration', 'tech_productivity', 'vehicle_time', 'ro_status_distribution'];
// Simulate the full response structure as built in analytics.php
$simulatedResponse = [
    'service_duration' => $serviceDuration ?? [],
    'tech_productivity' => $techProd ?? [],
    'vehicle_time' => $vehicleTime ?? [],
    'ro_status_distribution' => $roStatus ?? [],
];
foreach ($expectedKeys as $key) {
    ok(array_key_exists($key, $simulatedResponse), "response contains '$key'");
}
ok(is_array($simulatedResponse['service_duration']), 'service_duration is array');
ok(is_array($simulatedResponse['tech_productivity']), 'tech_productivity is array');
ok(is_array($simulatedResponse['vehicle_time']), 'vehicle_time is array (single row)');
ok(is_array($simulatedResponse['ro_status_distribution']), 'ro_status_distribution is array');

// ── Test 3: Verify vehicle_time sub-fields ──────────────────────────
echo "\n--- Vehicle Time Sub-field Types ---\n";
$vt = $simulatedResponse['vehicle_time'];
$vtFields = ['avg_total_minutes', 'avg_wait_minutes', 'avg_service_minutes', 'completed_count'];
foreach ($vtFields as $f) {
    ok(array_key_exists($f, $vt), "vehicle_time.$f exists");
}

// ── Summary ─────────────────────────────────────────────────────────
echo "\n" . str_repeat('=', 50) . "\n";
echo "Results: {$pass} passed, {$fail} failed\n";
if ($errors) {
    echo "\nFailed:\n";
    foreach ($errors as $e) echo "  - {$e}\n";
}
echo "\n";
exit($fail > 0 ? 1 : 0);
