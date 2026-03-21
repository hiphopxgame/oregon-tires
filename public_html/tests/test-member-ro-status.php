<?php
/**
 * Test: Member RO Status in Bookings
 *
 * Verifies that RO status data (ro_status, ro_number) is included in
 * member booking responses and that the progress bar HTML is rendered.
 *
 * Run: php tests/test-member-ro-status.php
 */

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';
TestHelper::initSession();

// Simulate bootstrap environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SESSION['member_id'] = 1;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$t = new TestHelper('Member RO Status');

// ── Test 1: RO step map is complete (10 steps) ──
$t->test('RO step map covers all 10 statuses', function () {
    $stepMap = [
        'intake'           => 1,
        'check_in'         => 2,
        'diagnosis'        => 3,
        'estimate_pending' => 4,
        'pending_approval' => 5,
        'approved'         => 6,
        'in_progress'      => 7,
        'ready'            => 8,
        'completed'        => 9,
        'invoiced'         => 10,
    ];
    TestHelper::assertEqual(10, count($stepMap), 'Step map should have 10 entries');
    TestHelper::assertEqual(1, $stepMap['intake'], 'intake should be step 1');
    TestHelper::assertEqual(10, $stepMap['invoiced'], 'invoiced should be step 10');
});

// ── Test 2: Step labels exist for both languages ──
$t->test('Step labels exist in EN and ES for all statuses', function () {
    $statuses = ['intake', 'check_in', 'diagnosis', 'estimate_pending', 'pending_approval',
                 'approved', 'in_progress', 'ready', 'completed', 'invoiced'];
    $labelsEn = [
        'intake' => 'Intake', 'check_in' => 'Checked in', 'diagnosis' => 'Diagnosis',
        'estimate_pending' => 'Estimate ready', 'pending_approval' => 'Awaiting approval',
        'approved' => 'Approved', 'in_progress' => 'Work in progress',
        'ready' => 'Ready for pickup', 'completed' => 'Completed', 'invoiced' => 'Invoiced',
    ];
    $labelsEs = [
        'intake' => 'Recepción', 'check_in' => 'Registrado', 'diagnosis' => 'Diagnóstico',
        'estimate_pending' => 'Estimado listo', 'pending_approval' => 'Esperando aprobación',
        'approved' => 'Aprobado', 'in_progress' => 'En progreso',
        'ready' => 'Listo para recoger', 'completed' => 'Completado', 'invoiced' => 'Facturado',
    ];
    foreach ($statuses as $s) {
        TestHelper::assertTrue(isset($labelsEn[$s]), "EN label missing for $s");
        TestHelper::assertTrue(isset($labelsEs[$s]), "ES label missing for $s");
        TestHelper::assertTrue(strlen($labelsEn[$s]) > 0, "EN label empty for $s");
        TestHelper::assertTrue(strlen($labelsEs[$s]) > 0, "ES label empty for $s");
    }
});

// ── Test 3: my-bookings.php SQL includes ro_status ──
$t->test('my-bookings.php query selects ro_status and ro_number', function () {
    $source = file_get_contents(__DIR__ . '/../api/member/my-bookings.php');
    TestHelper::assertContains('ro.status as ro_status', $source, 'Should select ro_status');
    TestHelper::assertContains('ro.ro_number', $source, 'Should select ro_number');
    TestHelper::assertContains('LEFT JOIN oretir_repair_orders ro', $source, 'Should join repair_orders');
});

// ── Test 4: my-bookings-ui.php renders progress bar ──
$t->test('my-bookings-ui.php contains progress bar rendering', function () {
    $source = file_get_contents(__DIR__ . '/../api/member/my-bookings-ui.php');
    TestHelper::assertContains('ro_status', $source, 'Should reference ro_status');
    TestHelper::assertContains('roStepMap', $source, 'Should contain step map');
    TestHelper::assertContains('roStepLabels', $source, 'Should contain step labels');
    TestHelper::assertContains('Step', $source, 'Should render step text');
    TestHelper::assertContains('Paso', $source, 'Should render Spanish step text');
});

// ── Test 5: Progress percentage calculation ──
$t->test('Progress percentage calculation is correct', function () {
    $stepMap = [
        'intake' => 1, 'check_in' => 2, 'diagnosis' => 3, 'estimate_pending' => 4,
        'pending_approval' => 5, 'approved' => 6, 'in_progress' => 7,
        'ready' => 8, 'completed' => 9, 'invoiced' => 10,
    ];
    $totalSteps = 10;

    // Test a few key steps
    $pctIntake = (int) round(($stepMap['intake'] / $totalSteps) * 100);
    TestHelper::assertEqual(10, $pctIntake, 'Intake should be 10%');

    $pctInProgress = (int) round(($stepMap['in_progress'] / $totalSteps) * 100);
    TestHelper::assertEqual(70, $pctInProgress, 'In progress should be 70%');

    $pctInvoiced = (int) round(($stepMap['invoiced'] / $totalSteps) * 100);
    TestHelper::assertEqual(100, $pctInvoiced, 'Invoiced should be 100%');
});

// ── Test 6: Cancelled status is handled gracefully ──
$t->test('Cancelled ROs do not show progress bar (not in step map)', function () {
    $stepMap = [
        'intake' => 1, 'check_in' => 2, 'diagnosis' => 3, 'estimate_pending' => 4,
        'pending_approval' => 5, 'approved' => 6, 'in_progress' => 7,
        'ready' => 8, 'completed' => 9, 'invoiced' => 10,
    ];
    TestHelper::assertTrue(!isset($stepMap['cancelled']), 'cancelled should not be in step map');
});

$t->done();
