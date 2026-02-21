<?php
/**
 * Oregon Tires — Duplicate Booking Prevention Tests
 *
 * Tests that the duplicate check query logic works correctly.
 * Run via CLI: php tests/test-duplicate-booking.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../public_html/includes/bootstrap.php';

$db = getDB();
$passed = 0;
$failed = 0;

function test(string $name, bool $result): void
{
    global $passed, $failed;
    if ($result) {
        echo "PASS: {$name}\n";
        $passed++;
    } else {
        echo "FAIL: {$name}\n";
        $failed++;
    }
}

// ─── Setup: insert test appointments ─────────────────────────────────────────
$testEmail    = 'dupetest_' . bin2hex(random_bytes(4)) . '@example.com';
$testDate     = '2099-12-31';
$testTime     = '10:00';
$testRef1     = 'OT-DTEST001';
$testRef2     = 'OT-DTEST002';
$testRef3     = 'OT-DTEST003';

echo "\n--- Setup: inserting test data (email: {$testEmail}) ---\n\n";

// Active appointment (status = 'new')
$db->prepare(
    'INSERT INTO oretir_appointments
        (reference_number, service, preferred_date, preferred_time,
         first_name, last_name, phone, email, status, language, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
)->execute([$testRef1, 'oil-change', $testDate, $testTime,
            'Test', 'User', '555-0001', $testEmail, 'new', 'english']);

// Cancelled appointment (same email/date/time — should NOT block)
$db->prepare(
    'INSERT INTO oretir_appointments
        (reference_number, service, preferred_date, preferred_time,
         first_name, last_name, phone, email, status, language, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
)->execute([$testRef2, 'oil-change', $testDate, '11:00',
            'Test', 'User', '555-0001', $testEmail, 'cancelled', 'english']);

// Different email, same date/time (should NOT block)
$diffEmail = 'other_' . bin2hex(random_bytes(4)) . '@example.com';
$db->prepare(
    'INSERT INTO oretir_appointments
        (reference_number, service, preferred_date, preferred_time,
         first_name, last_name, phone, email, status, language, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
)->execute([$testRef3, 'oil-change', $testDate, $testTime,
            'Other', 'Person', '555-0002', $diffEmail, 'new', 'english']);

// ─── Duplicate check query (mirrors book.php logic) ──────────────────────────
$dupeQuery = 'SELECT id FROM oretir_appointments
              WHERE email = ? AND preferred_date = ? AND preferred_time = ? AND status != ?
              LIMIT 1';

// ─── Test 1: Duplicate detected (same email + date + time, active status) ────
$stmt = $db->prepare($dupeQuery);
$stmt->execute([$testEmail, $testDate, $testTime, 'cancelled']);
$row = $stmt->fetch();
test('Duplicate detected for same email/date/time with active status', $row !== false);

// ─── Test 2: Different email — no duplicate ──────────────────────────────────
$stmt = $db->prepare($dupeQuery);
$stmt->execute(['nobody@example.com', $testDate, $testTime, 'cancelled']);
$row = $stmt->fetch();
test('No duplicate for different email', $row === false);

// ─── Test 3: Different date — no duplicate ───────────────────────────────────
$stmt = $db->prepare($dupeQuery);
$stmt->execute([$testEmail, '2099-12-30', $testTime, 'cancelled']);
$row = $stmt->fetch();
test('No duplicate for different date', $row === false);

// ─── Test 4: Different time — no duplicate ───────────────────────────────────
$stmt = $db->prepare($dupeQuery);
$stmt->execute([$testEmail, $testDate, '14:00', 'cancelled']);
$row = $stmt->fetch();
test('No duplicate for different time', $row === false);

// ─── Test 5: Cancelled appointment does NOT block rebooking ──────────────────
$stmt = $db->prepare($dupeQuery);
$stmt->execute([$testEmail, $testDate, '11:00', 'cancelled']);
$row = $stmt->fetch();
test('Cancelled appointment does not block rebooking', $row === false);

// ─── Test 6: Different email at same slot is allowed ─────────────────────────
$stmt = $db->prepare($dupeQuery);
$stmt->execute([$diffEmail, $testDate, $testTime, 'cancelled']);
$row = $stmt->fetch();
test('Different customer can book same slot', $row !== false); // row exists = they already have one
// Clarification: this confirms diffEmail DOES have an active booking at that slot,
// but the dupe check is per-customer, so a NEW customer wouldn't match.
$stmt2 = $db->prepare($dupeQuery);
$stmt2->execute(['brand_new@example.com', $testDate, $testTime, 'cancelled']);
$row2 = $stmt2->fetch();
test('Brand new customer is not blocked by others at same slot', $row2 === false);

// ─── Cleanup ─────────────────────────────────────────────────────────────────
echo "\n--- Cleanup: removing test data ---\n";
$db->prepare('DELETE FROM oretir_appointments WHERE reference_number IN (?, ?, ?)')
   ->execute([$testRef1, $testRef2, $testRef3]);

// ─── Summary ─────────────────────────────────────────────────────────────────
echo "\n=== Results: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
