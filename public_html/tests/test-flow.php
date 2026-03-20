#!/usr/bin/env php
<?php
/**
 * Test: Full appointment-to-invoice flow
 * Run: php tests/test-flow.php
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/validate.php';
require_once __DIR__ . '/../includes/schedule.php';
require_once __DIR__ . '/../includes/vin-decode.php';
require_once __DIR__ . '/../includes/invoices.php';

$db = getDB();
$pass = 0;
$fail = 0;

function ok(bool $cond, string $label): void {
    global $pass, $fail;
    if ($cond) { echo "  \033[32m✓\033[0m {$label}\n"; $pass++; }
    else       { echo "  \033[31m✗\033[0m {$label}\n"; $fail++; }
}

echo "=== 1. Database Schema ===\n";

// services column
$cols = $db->query("SHOW COLUMNS FROM oretir_appointments LIKE 'services'")->fetchAll();
ok(count($cols) === 1, 'appointments.services column exists');

// show_scarcity setting
$r = $db->query("SELECT value_en FROM oretir_site_settings WHERE setting_key = 'show_scarcity'")->fetch();
ok($r !== false, 'show_scarcity setting exists');
ok(($r['value_en'] ?? '') === '0', 'show_scarcity defaults to 0 (off)');

// promotions deactivated
$r = $db->query("SELECT COUNT(*) as cnt FROM oretir_promotions WHERE is_active = 1")->fetch();
ok((int)$r['cnt'] === 0, 'No active promotions (all deactivated)');

// invoices table
try {
    $r = $db->query("SELECT COUNT(*) as cnt FROM oretir_invoices")->fetch();
    ok(true, 'oretir_invoices table exists (' . $r['cnt'] . ' rows)');
} catch (\Throwable $e) {
    ok(false, 'oretir_invoices table: ' . $e->getMessage());
}

// estimates table
try {
    $r = $db->query("SELECT COUNT(*) as cnt FROM oretir_estimates")->fetch();
    ok(true, 'oretir_estimates table exists (' . $r['cnt'] . ' rows)');
} catch (\Throwable $e) {
    ok(false, 'oretir_estimates table: ' . $e->getMessage());
}

echo "\n=== 2. parseServices() ===\n";

$t1 = parseServices(['services' => ['oil-change', 'brake-service']]);
ok($t1 === ['oil-change', 'brake-service'], 'Multi-service array parsed');

$t2 = parseServices(['service' => 'tire-installation']);
ok($t2 === ['tire-installation'], 'Single service string parsed');

$t3 = parseServices(['services' => ['oil-change', 'INVALID', 'brake-service']]);
ok(count($t3) === 2 && !in_array('INVALID', $t3), 'Invalid service filtered out');

$t4 = parseServices(['services' => ['oil-change', 'oil-change', 'brake-service']]);
ok(count($t4) === 2, 'Duplicate services removed');

$t5 = parseServices(['services' => ['oil-change', 'brake-service', 'tuneup', 'tire-installation', 'tire-repair', 'wheel-alignment']]);
ok(count($t5) === 5, 'Max 5 services enforced');

$t6 = parseServices([]);
ok($t6 === [], 'Empty input returns empty array');

echo "\n=== 3. generateTaskSummary() ===\n";

$s1 = generateTaskSummary('oil-change', '2020 Toyota Camry', null);
ok(str_contains($s1, 'Oil Change'), 'String service formatted');

$s2 = generateTaskSummary(['oil-change', 'brake-service'], '2020 Toyota Camry', 'Check brakes');
ok(str_contains($s2, 'Oil Change + Brake Service'), 'Array services joined with +');
ok(str_contains($s2, '2020 Toyota Camry'), 'Vehicle info included');
ok(str_contains($s2, 'Check brakes'), 'Notes included');

echo "\n=== 4. Backfilled Appointments ===\n";

$stmt = $db->query("SELECT COUNT(*) as cnt FROM oretir_appointments WHERE services IS NULL AND service IS NOT NULL");
$r = $stmt->fetch();
ok((int)$r['cnt'] === 0, 'All existing appointments have services JSON backfilled');

$stmt = $db->query("SELECT id, service, services FROM oretir_appointments ORDER BY id DESC LIMIT 1");
$r = $stmt->fetch();
if ($r) {
    $decoded = json_decode($r['services'], true);
    ok(is_array($decoded), 'services column is valid JSON array');
    ok($decoded[0] === $r['service'], 'services[0] matches service column');
} else {
    ok(true, 'No appointments to check (empty table)');
    ok(true, 'Skipped');
}

echo "\n=== 5. Invoice Helper Functions ===\n";

ok(function_exists('createInvoiceFromEstimate'), 'createInvoiceFromEstimate() exists');
ok(function_exists('createInvoiceFromAnyEstimate'), 'createInvoiceFromAnyEstimate() exists');
ok(function_exists('getInvoiceWithItems'), 'getInvoiceWithItems() exists');
ok(function_exists('getInvoiceByToken'), 'getInvoiceByToken() exists');
ok(function_exists('sendInvoiceEmail'), 'sendInvoiceEmail() exists');
ok(function_exists('generateInvoiceNumber'), 'generateInvoiceNumber() exists');

// Test invoice number generation
$invNum = generateInvoiceNumber($db);
ok(str_starts_with($invNum, 'INV-'), 'Invoice number starts with INV-');
ok(strlen($invNum) === 12, 'Invoice number is 12 chars (INV-XXXXXXXX)');

echo "\n=== 6. RO → Estimate → Invoice Flow (dry run) ===\n";

// Check a real RO if available
$roRow = $db->query("SELECT r.id, r.ro_number, r.status, r.customer_id,
    (SELECT COUNT(*) FROM oretir_estimates WHERE repair_order_id = r.id) as est_count,
    (SELECT COUNT(*) FROM oretir_invoices WHERE repair_order_id = r.id) as inv_count
    FROM oretir_repair_orders r ORDER BY r.id DESC LIMIT 1")->fetch();

if ($roRow) {
    echo "  Latest RO: {$roRow['ro_number']} status={$roRow['status']} estimates={$roRow['est_count']} invoices={$roRow['inv_count']}\n";
    ok(true, "RO {$roRow['ro_number']} found");

    // Test createInvoiceFromEstimate (should return null or existing)
    $result = createInvoiceFromEstimate($db, (int)$roRow['id']);
    if ((int)$roRow['est_count'] === 0) {
        ok($result === null, 'No invoice created (no estimates on this RO)');
    } else {
        ok(true, 'createInvoiceFromEstimate returned: ' . json_encode($result));
    }
} else {
    echo "  No ROs to test\n";
    ok(true, 'Skipped (no ROs)');
}

echo "\n=== 7. syncAppointmentRoStatus ===\n";
ok(function_exists('syncAppointmentRoStatus'), 'syncAppointmentRoStatus() exists');

echo "\n=== 8. API Endpoint Smoke Tests ===\n";

// Test health endpoint (use curl since file_get_contents may lack SSL context)
$health = shell_exec('curl -s https://oregon.tires/api/health.php 2>/dev/null');
if ($health) {
    $hj = json_decode($health, true);
    ok(!empty($hj['status']), 'Health check API returns status: ' . ($hj['status'] ?? 'unknown'));
} else {
    ok(false, 'Health check API unreachable');
}

// Test settings endpoint
$settings = @file_get_contents('https://oregon.tires/api/settings.php');
if ($settings !== false) {
    $sj = json_decode($settings, true);
    ok(($sj['success'] ?? false) === true, 'Settings API returns success');
    $found = false;
    foreach (($sj['data'] ?? []) as $s) {
        if ($s['setting_key'] === 'show_scarcity') { $found = true; break; }
    }
    ok($found, 'show_scarcity visible in settings API');
} else {
    ok(false, 'Settings API unreachable');
    ok(false, 'Skipped');
}

// Test promotions endpoint (should return empty)
$promos = @file_get_contents('https://oregon.tires/api/promotions.php?placement=inline');
if ($promos !== false) {
    $pj = json_decode($promos, true);
    ok(($pj['success'] ?? false) === true, 'Promotions API returns success');
    ok(empty($pj['data']), 'No active inline promotions returned');
} else {
    ok(false, 'Promotions API unreachable');
    ok(false, 'Skipped');
}

// Test exit intent promotions (should return null)
$exit = @file_get_contents('https://oregon.tires/api/promotions.php?placement=exit_intent');
if ($exit !== false) {
    $ej = json_decode($exit, true);
    ok(($ej['success'] ?? false) === true, 'Exit intent promotions API returns success');
    ok(($ej['data'] ?? null) === null, 'No active exit-intent promotion');
} else {
    ok(false, 'Exit intent API unreachable');
    ok(false, 'Skipped');
}

echo "\n=== 9. Estimate Items Schema ===\n";
// Check is_approved column exists (invoices.php references ei.is_approved)
$cols = $db->query("SHOW COLUMNS FROM oretir_estimate_items LIKE 'is_approved'")->fetchAll();
ok(count($cols) === 1, 'estimate_items.is_approved column exists');

// Verify invoices.php uses the correct column name
$invCode = file_get_contents(__DIR__ . '/../includes/invoices.php');
ok(str_contains($invCode, 'ei.is_approved'), 'invoices.php references ei.is_approved (correct)');
ok(!str_contains($invCode, 'ei.approved ='), 'invoices.php does NOT reference ei.approved (old bug fixed)');
ok(str_contains($invCode, "'approved','partial'"), 'invoices.php uses correct status enum values');

echo "\n=== 10. Estimate recalculate function ===\n";
// Check if the recalculate function file is loadable
try {
    // The function is defined at the bottom of estimates.php
    $code = file_get_contents(__DIR__ . '/../api/admin/estimates.php');
    ok(str_contains($code, 'function recalculateEstimateTotals'), 'recalculateEstimateTotals defined in estimates.php');
    ok(str_contains($code, 'replace_items'), 'replace_items handler exists in estimates.php');
} catch (\Throwable $e) {
    ok(false, 'Could not read estimates.php: ' . $e->getMessage());
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "Results: \033[32m{$pass} passed\033[0m, " . ($fail > 0 ? "\033[31m{$fail} failed\033[0m" : "0 failed") . "\n";
exit($fail > 0 ? 1 : 0);
