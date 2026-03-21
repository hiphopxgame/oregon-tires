<?php
/**
 * Oregon Tires — Work Order Page Test Suite
 * Verifies work-order.php exists with correct structure and the Print button is in the RO modal.
 * Run: php tests/test-work-order.php
 */

$passed = 0;
$failed = 0;
$total  = 0;

function assert_test(bool $condition, string $label): void {
    global $passed, $failed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  PASS: {$label}\n";
    } else {
        $failed++;
        echo "  FAIL: {$label}\n";
    }
}

$base = __DIR__ . '/../public_html';

// ─── TEST 1: work-order.php file exists ─────────────────────────────────────
echo "\nTEST 1: Work order page exists\n";

$woPath = $base . '/work-order.php';
assert_test(file_exists($woPath), 'work-order.php exists in public_html');

$woContent = file_get_contents($woPath);

// ─── TEST 2: Auth requirement ───────────────────────────────────────────────
echo "\nTEST 2: Authentication\n";

assert_test(str_contains($woContent, "require_once __DIR__ . '/includes/auth.php'"), 'Includes auth.php');
assert_test(str_contains($woContent, 'requireStaff()'), 'Calls requireStaff() for session auth');
assert_test(str_contains($woContent, 'startSecureSession()'), 'Starts secure session');

// ─── TEST 3: Shop header info ───────────────────────────────────────────────
echo "\nTEST 3: Shop header\n";

assert_test(str_contains($woContent, 'Oregon Tires Auto Care'), 'Shop name present');
assert_test(str_contains($woContent, '5630 SE 82nd Ave'), 'Address present');
assert_test(str_contains($woContent, '(503) 788-4680'), 'Phone number present');

// ─── TEST 4: RO data displayed ──────────────────────────────────────────────
echo "\nTEST 4: RO data fields\n";

assert_test(str_contains($woContent, "ro['ro_number']"), 'RO number displayed');
assert_test(str_contains($woContent, "ro['created_at']"), 'RO date displayed');
assert_test(str_contains($woContent, "ro['promised_date']"), 'Promised date displayed');
assert_test(str_contains($woContent, '$customer'), 'Customer name displayed');
assert_test(str_contains($woContent, "ro['customer_phone']"), 'Customer phone displayed');
assert_test(str_contains($woContent, "ro['customer_email']"), 'Customer email displayed');

// ─── TEST 5: Vehicle info ───────────────────────────────────────────────────
echo "\nTEST 5: Vehicle info\n";

assert_test(str_contains($woContent, '$vehicle'), 'Vehicle YMM displayed');
assert_test(str_contains($woContent, "ro['vin']"), 'VIN displayed');
assert_test(str_contains($woContent, "ro['license_plate']"), 'License plate displayed');
assert_test(str_contains($woContent, "ro['mileage_in']"), 'Mileage displayed');

// ─── TEST 6: Inspection items ───────────────────────────────────────────────
echo "\nTEST 6: Inspection items\n";

assert_test(str_contains($woContent, 'oretir_inspection_items'), 'Queries inspection items');
assert_test(str_contains($woContent, 'condition_rating'), 'Condition rating column');
assert_test(str_contains($woContent, 'rating-green'), 'Green traffic light style');
assert_test(str_contains($woContent, 'rating-yellow'), 'Yellow traffic light style');
assert_test(str_contains($woContent, 'rating-red'), 'Red traffic light style');

// ─── TEST 7: Estimate items ────────────────────────────────────────────────
echo "\nTEST 7: Estimate items\n";

assert_test(str_contains($woContent, 'oretir_estimate_items'), 'Queries estimate items');
assert_test(str_contains($woContent, 'unit_price'), 'Unit price column');
assert_test(str_contains($woContent, 'Subtotal'), 'Subtotal row');
assert_test(str_contains($woContent, 'Total:'), 'Total row');

// ─── TEST 8: Technician notes area ─────────────────────────────────────────
echo "\nTEST 8: Notes area\n";

assert_test(str_contains($woContent, 'Technician Notes'), 'Technician notes section');
assert_test(str_contains($woContent, 'note-line'), 'Empty note lines for writing');
assert_test(str_contains($woContent, "ro['technician_notes']"), 'Existing tech notes shown');

// ─── TEST 9: Footer with terms ─────────────────────────────────────────────
echo "\nTEST 9: Footer\n";

assert_test(str_contains($woContent, 'terms and conditions'), 'Footer terms text');
assert_test(str_contains($woContent, 'Estimates are valid for 30 days'), 'Estimate validity terms');

// ─── TEST 10: Print functionality ───────────────────────────────────────────
echo "\nTEST 10: Print functionality\n";

assert_test(str_contains($woContent, 'window.print()'), 'Auto-triggers print on load');
assert_test(str_contains($woContent, '@media print'), 'Has @media print CSS');
assert_test(str_contains($woContent, 'print-btn'), 'Has manual print button');
assert_test(str_contains($woContent, "display: none !important"), 'Print button hidden in print');

// ─── TEST 11: Signature areas ───────────────────────────────────────────────
echo "\nTEST 11: Signature areas\n";

assert_test(str_contains($woContent, 'Technician Signature'), 'Technician signature line');
assert_test(str_contains($woContent, 'Customer Signature'), 'Customer signature line');

// ─── TEST 12: Print button in RO modal ─────────────────────────────────────
echo "\nTEST 12: Print button in admin RO modal\n";

$roJs = file_get_contents($base . '/admin/js/repair-orders.js');
assert_test(str_contains($roJs, "'/work-order?ro_id='"), 'Print button links to work-order page');
assert_test(str_contains($roJs, 'roPrintWorkOrder'), 'Print button has translation key');
assert_test(str_contains($roJs, "window.open('/work-order?ro_id=' + ro.id, '_blank')"), 'Opens in new tab');

// ─── TEST 13: No index meta ────────────────────────────────────────────────
echo "\nTEST 13: SEO protection\n";

assert_test(str_contains($woContent, 'noindex, nofollow'), 'Page has noindex meta tag');

// ─── SUMMARY ──────────────────────────────────────────────────────────────────
echo "\n══════════════════════════════════\n";
echo "Results: {$passed}/{$total} passed, {$failed} failed\n";
echo "══════════════════════════════════\n\n";

exit($failed > 0 ? 1 : 0);
