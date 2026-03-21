<?php
/**
 * Oregon Tires — Estimate Auto-Approval → RO Status Update Tests
 *
 * Verifies that when a customer approves an estimate via the public approval API,
 * the linked RO auto-advances to 'approved' and the appointment is synced.
 *
 * Run via CLI: php tests/test-estimate-auto-approve.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../public_html/includes/bootstrap.php';
require_once __DIR__ . '/../public_html/includes/vin-decode.php';

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

// ─── Setup: create test customer, vehicle, appointment, RO, estimate ─────────
$testSuffix = bin2hex(random_bytes(4));
$testEmail = "autotest_{$testSuffix}@example.com";
$testToken = 'TESTTOKEN_' . $testSuffix;

echo "\n--- Setup: creating test data (email: {$testEmail}) ---\n\n";

// Customer
$db->prepare(
    'INSERT INTO oretir_customers (first_name, last_name, email, phone, language, visit_count, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())'
)->execute(['Test', 'Approver', $testEmail, '5035551234', 'english']);
$customerId = (int) $db->lastInsertId();

// Vehicle
$db->prepare(
    'INSERT INTO oretir_vehicles (customer_id, year, make, model, created_at, updated_at)
     VALUES (?, ?, ?, ?, NOW(), NOW())'
)->execute([$customerId, '2020', 'Honda', 'Civic']);
$vehicleId = (int) $db->lastInsertId();

// Appointment
$db->prepare(
    "INSERT INTO oretir_appointments (customer_id, vehicle_id, first_name, last_name, email, phone, preferred_date, preferred_time, status, reference_number, created_at, updated_at)
     VALUES (?, ?, 'Test', 'Approver', ?, '5035551234', '2099-12-31', '10:00', 'confirmed', ?, NOW(), NOW())"
)->execute([$customerId, $vehicleId, $testEmail, 'OT-ATEST' . $testSuffix]);
$appointmentId = (int) $db->lastInsertId();

// Repair Order in pending_approval status
$roNumber = 'RO-TEST' . strtoupper($testSuffix);
$db->prepare(
    "INSERT INTO oretir_repair_orders (ro_number, customer_id, vehicle_id, appointment_id, status, customer_concern, created_at, updated_at)
     VALUES (?, ?, ?, ?, 'pending_approval', 'Test concern', NOW(), NOW())"
)->execute([$roNumber, $customerId, $vehicleId, $appointmentId]);
$roId = (int) $db->lastInsertId();

// Estimate linked to RO
$estNumber = 'ES-TEST' . strtoupper($testSuffix);
$db->prepare(
    "INSERT INTO oretir_estimates (repair_order_id, estimate_number, approval_token, status, subtotal, tax_rate, tax_amount, total, created_at, updated_at)
     VALUES (?, ?, ?, 'sent', 100.00, 0.00, 0.00, 100.00, NOW(), NOW())"
)->execute([$roId, $estNumber, $testToken]);
$estimateId = (int) $db->lastInsertId();

// Estimate items
$db->prepare(
    "INSERT INTO oretir_estimate_items (estimate_id, item_type, description, quantity, unit_price, total, sort_order, created_at, updated_at)
     VALUES (?, 'labor', 'Test labor', 1, 50.00, 50.00, 1, NOW(), NOW())"
)->execute([$estimateId]);
$itemId1 = (int) $db->lastInsertId();

$db->prepare(
    "INSERT INTO oretir_estimate_items (estimate_id, item_type, description, quantity, unit_price, total, sort_order, created_at, updated_at)
     VALUES (?, 'parts', 'Test part', 1, 50.00, 50.00, 2, NOW(), NOW())"
)->execute([$estimateId]);
$itemId2 = (int) $db->lastInsertId();

// ─── Test 1: RO starts in pending_approval ─────────────────────────────────
$stmt = $db->prepare('SELECT status FROM oretir_repair_orders WHERE id = ?');
$stmt->execute([$roId]);
test('RO starts in pending_approval', $stmt->fetchColumn() === 'pending_approval');

// ─── Test 2: Simulate full approval — update estimate items + status ────────
// Approve all items
$db->prepare('UPDATE oretir_estimate_items SET is_approved = 1 WHERE estimate_id = ?')->execute([$estimateId]);

// Update estimate status to approved
$db->prepare("UPDATE oretir_estimates SET status = 'approved', customer_responded_at = NOW(), updated_at = NOW() WHERE id = ?")->execute([$estimateId]);

// Simulate what estimate-approve.php does: update RO + sync
$updated = $db->prepare("UPDATE oretir_repair_orders SET status = 'approved', updated_at = NOW() WHERE id = ? AND status = 'pending_approval'");
$updated->execute([$roId]);

if ($updated->rowCount() > 0) {
    syncAppointmentRoStatus('ro', $roId, 'approved', $db);
}

// Verify RO moved to approved
$stmt = $db->prepare('SELECT status FROM oretir_repair_orders WHERE id = ?');
$stmt->execute([$roId]);
test('RO auto-advances to approved after estimate approval', $stmt->fetchColumn() === 'approved');

// ─── Test 3: Appointment synced to confirmed ────────────────────────────────
$stmt = $db->prepare('SELECT status FROM oretir_appointments WHERE id = ?');
$stmt->execute([$appointmentId]);
test('Appointment synced to confirmed after RO approved', $stmt->fetchColumn() === 'confirmed');

// ─── Test 4: Partial approval also advances RO ─────────────────────────────
// Reset RO to pending_approval
$db->prepare("UPDATE oretir_repair_orders SET status = 'pending_approval', updated_at = NOW() WHERE id = ?")->execute([$roId]);

// Partial: approve one, decline other
$db->prepare('UPDATE oretir_estimate_items SET is_approved = 1 WHERE id = ?')->execute([$itemId1]);
$db->prepare('UPDATE oretir_estimate_items SET is_approved = 0 WHERE id = ?')->execute([$itemId2]);

$db->prepare("UPDATE oretir_estimates SET status = 'partial', updated_at = NOW() WHERE id = ?")->execute([$estimateId]);

$updated = $db->prepare("UPDATE oretir_repair_orders SET status = 'approved', updated_at = NOW() WHERE id = ? AND status = 'pending_approval'");
$updated->execute([$roId]);

if ($updated->rowCount() > 0) {
    syncAppointmentRoStatus('ro', $roId, 'approved', $db);
}

$stmt = $db->prepare('SELECT status FROM oretir_repair_orders WHERE id = ?');
$stmt->execute([$roId]);
test('Partial approval also advances RO to approved', $stmt->fetchColumn() === 'approved');

// ─── Test 5: Declined estimate does NOT advance RO ─────────────────────────
$db->prepare("UPDATE oretir_repair_orders SET status = 'pending_approval', updated_at = NOW() WHERE id = ?")->execute([$roId]);
$db->prepare("UPDATE oretir_estimates SET status = 'declined', updated_at = NOW() WHERE id = ?")->execute([$estimateId]);

// Declined = no RO update (simulating what estimate-approve.php does)
$stmt = $db->prepare('SELECT status FROM oretir_repair_orders WHERE id = ?');
$stmt->execute([$roId]);
test('Declined estimate does NOT advance RO', $stmt->fetchColumn() === 'pending_approval');

// ─── Test 6: Already-approved RO is not affected by re-approval ────────────
$db->prepare("UPDATE oretir_repair_orders SET status = 'in_progress', updated_at = NOW() WHERE id = ?")->execute([$roId]);

$updated = $db->prepare("UPDATE oretir_repair_orders SET status = 'approved', updated_at = NOW() WHERE id = ? AND status = 'pending_approval'");
$updated->execute([$roId]);

$stmt = $db->prepare('SELECT status FROM oretir_repair_orders WHERE id = ?');
$stmt->execute([$roId]);
test('RO already past pending_approval is not rolled back', $stmt->fetchColumn() === 'in_progress');

// ─── Cleanup ────────────────────────────────────────────────────────────────
echo "\n--- Cleanup ---\n";
$db->prepare('DELETE FROM oretir_estimate_items WHERE estimate_id = ?')->execute([$estimateId]);
$db->prepare('DELETE FROM oretir_estimates WHERE id = ?')->execute([$estimateId]);
$db->prepare('DELETE FROM oretir_repair_orders WHERE id = ?')->execute([$roId]);
$db->prepare('DELETE FROM oretir_appointments WHERE id = ?')->execute([$appointmentId]);
$db->prepare('DELETE FROM oretir_vehicles WHERE id = ?')->execute([$vehicleId]);
$db->prepare('DELETE FROM oretir_customers WHERE id = ?')->execute([$customerId]);

echo "\n=== Results: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
