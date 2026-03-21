<?php
/**
 * Oregon Tires — Service Reminders Test Suite
 * Verifies that service reminders are created correctly when ROs are invoiced.
 * Run: php tests/test-service-reminders.php
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

// ─── TEST 1: createServiceReminderFromRo function exists ─────────────────────
echo "\nTEST 1: Service reminder function exists in repair-orders.php\n";
$roFile = file_get_contents($base . '/api/admin/repair-orders.php');

assert_test(str_contains($roFile, 'function createServiceReminderFromRo'), 'createServiceReminderFromRo function defined');
assert_test(str_contains($roFile, "createServiceReminderFromRo(\$db, \$ro)"), 'Function called in invoiced case');

// ─── TEST 2: Service type intervals are correct ─────────────────────────────
echo "\nTEST 2: Service interval mapping\n";

assert_test(str_contains($roFile, "'oil_change'"), 'Oil change service type defined');
assert_test(str_contains($roFile, "'tire_rotation'"), 'Tire rotation service type defined');
assert_test(str_contains($roFile, "'brake_service'"), 'Brake service type defined');

// Verify the interval values
assert_test(str_contains($roFile, "'oil_change'        => ['days' => 90,  'miles' => 5000]"), 'Oil change: 90 days / 5000 miles');
assert_test(str_contains($roFile, "'tire_rotation'     => ['days' => 180, 'miles' => 7500]"), 'Tire rotation: 180 days / 7500 miles');
assert_test(str_contains($roFile, "'brake_service'     => ['days' => 365, 'miles' => 30000]"), 'Brake service: 365 days / 30000 miles');

// ─── TEST 3: Duplicate prevention ───────────────────────────────────────────
echo "\nTEST 3: Duplicate prevention\n";

assert_test(str_contains($roFile, "status = ?") && str_contains($roFile, "'pending'"), 'Checks for existing pending reminder');
assert_test(str_contains($roFile, 'return; // Already has a pending reminder'), 'Returns early if duplicate exists');

// ─── TEST 4: Reminder inserted with correct fields ──────────────────────────
echo "\nTEST 4: Correct insert fields\n";

assert_test(str_contains($roFile, 'customer_id, vehicle_id, service_type, last_service_date, next_due_date, due_mileage, mileage_at_service, status'), 'All required fields in INSERT');
assert_test(str_contains($roFile, "'pending',"), 'Pending status value in insert');

// ─── TEST 5: Function is called in invoiced transition ──────────────────────
echo "\nTEST 5: Called during invoiced status transition\n";

// The call should be inside the 'invoiced' case block
$invoicedPos = strpos($roFile, "case 'invoiced':");
$cancelledPos = strpos($roFile, "case 'cancelled':");
$callPos = strpos($roFile, 'createServiceReminderFromRo($db, $ro)');
assert_test($callPos > $invoicedPos && $callPos < $cancelledPos, 'Function call is within invoiced case block');
assert_test(str_contains($roFile, 'service reminder creation failed'), 'Error logging for failed reminder creation');

// ─── TEST 6: Migration file exists ──────────────────────────────────────────
echo "\nTEST 6: Migration file\n";

$migrationFile = __DIR__ . '/../sql/migrate-064-service-reminder-mileage.sql';
assert_test(file_exists($migrationFile), 'Migration 064 file exists');
$migrationSql = file_get_contents($migrationFile);
assert_test(str_contains($migrationSql, 'due_mileage'), 'Migration adds due_mileage column');
assert_test(str_contains($migrationSql, 'oretir_service_reminders'), 'Migration targets service_reminders table');

// ─── TEST 7: Default interval for unknown services ─────────────────────────
echo "\nTEST 7: Default interval for unknown services\n";

assert_test(str_contains($roFile, "['days' => 365, 'miles' => null]"), 'Default interval: 365 days, no mileage');

// ─── SUMMARY ──────────────────────────────────────────────────────────────────
echo "\n══════════════════════════════════\n";
echo "Results: {$passed}/{$total} passed, {$failed} failed\n";
echo "══════════════════════════════════\n\n";

exit($failed > 0 ? 1 : 0);
