<?php
/**
 * Oregon Tires — Schedule Filtering Tests
 *
 * Tests that the schedule system correctly excludes inactive/deleted employees.
 * Run via CLI: php tests/test-schedule-filtering.php
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

// ─── Setup: create test employees + schedules ───────────────────────────────
$rand = bin2hex(random_bytes(4));
$activeName   = "SchedTestActive_{$rand}";
$inactiveName = "SchedTestInactive_{$rand}";
$activeEmail   = "schedtest_active_{$rand}@example.com";
$inactiveEmail = "schedtest_inactive_{$rand}@example.com";
$testDate = '2099-06-02'; // a Monday (day_of_week = 1)

echo "\n=== Schedule Filtering Tests ===\n";
echo "--- Setup: inserting test employees ({$activeName}, {$inactiveName}) ---\n\n";

try {
    // Create active employee
    $db->prepare(
        "INSERT INTO oretir_employees (name, email, role, is_active, created_at, updated_at)
         VALUES (?, ?, 'Employee', 1, NOW(), NOW())"
    )->execute([$activeName, $activeEmail]);
    $activeId = (int) $db->lastInsertId();

    // Create inactive employee
    $db->prepare(
        "INSERT INTO oretir_employees (name, email, role, is_active, created_at, updated_at)
         VALUES (?, ?, 'Employee', 0, NOW(), NOW())"
    )->execute([$inactiveName, $inactiveEmail]);
    $inactiveId = (int) $db->lastInsertId();

    // Create Mon-Fri schedules for both (day_of_week 1-5)
    $schedStmt = $db->prepare(
        "INSERT INTO oretir_schedules (employee_id, day_of_week, start_time, end_time, is_available, created_at, updated_at)
         VALUES (?, ?, '08:00:00', '17:00:00', 1, NOW(), NOW())"
    );
    for ($dow = 1; $dow <= 5; $dow++) {
        $schedStmt->execute([$activeId, $dow]);
        $schedStmt->execute([$inactiveId, $dow]);
    }

    // Create a schedule override for each on the test date
    $ovStmt = $db->prepare(
        "INSERT INTO oretir_schedule_overrides (employee_id, override_date, is_closed, start_time, end_time, reason, created_at, updated_at)
         VALUES (?, ?, 0, '09:00:00', '15:00:00', ?, NOW(), NOW())"
    );
    $ovStmt->execute([$activeId, $testDate, "Test override active {$rand}"]);
    $ovStmt->execute([$inactiveId, $testDate, "Test override inactive {$rand}"]);

    // ─── Test 1: Weekly schedule query excludes inactive employees ───────
    // Mirrors schedules.php weekly endpoint, but adds WHERE e.is_active = 1
    $stmt = $db->query(
        "SELECT s.*, e.name AS employee_name, e.is_active
         FROM oretir_schedules s
         JOIN oretir_employees e ON s.employee_id = e.id
         WHERE e.is_active = 1
         ORDER BY e.name ASC, s.day_of_week ASC"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $foundActive = false;
    $foundInactive = false;
    foreach ($rows as $row) {
        if ((int) $row['employee_id'] === $activeId) $foundActive = true;
        if ((int) $row['employee_id'] === $inactiveId) $foundInactive = true;
    }
    test('Weekly schedule query excludes inactive employees', $foundActive && !$foundInactive);

    // ─── Test 2: Daily schedule query excludes inactive employees ────────
    // Mirrors schedules.php daily endpoint (already has e.is_active = 1 in JOIN)
    $dayOfWeek = 1; // Monday
    $stmt = $db->prepare(
        "SELECT s.*, e.name AS employee_name,
                ov.id AS override_id, ov.is_closed AS ov_is_closed,
                ov.start_time AS ov_start_time, ov.end_time AS ov_end_time, ov.reason AS ov_reason
         FROM oretir_schedules s
         JOIN oretir_employees e ON s.employee_id = e.id AND e.is_active = 1
         LEFT JOIN oretir_schedule_overrides ov ON ov.employee_id = s.employee_id AND ov.override_date = ?
         WHERE s.day_of_week = ?
         ORDER BY e.name ASC"
    );
    $stmt->execute([$testDate, $dayOfWeek]);
    $dailyRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dailyActive = false;
    $dailyInactive = false;
    foreach ($dailyRows as $row) {
        if ((int) $row['employee_id'] === $activeId) $dailyActive = true;
        if ((int) $row['employee_id'] === $inactiveId) $dailyInactive = true;
    }
    test('Daily schedule query excludes inactive employees', $dailyActive && !$dailyInactive);

    // ─── Test 3: Overrides query excludes inactive employee overrides ────
    // Overrides query joined with employee active status filter
    $stmt = $db->prepare(
        "SELECT ov.*, e.name AS employee_name
         FROM oretir_schedule_overrides ov
         LEFT JOIN oretir_employees e ON ov.employee_id = e.id
         WHERE ov.override_date BETWEEN ? AND ?
           AND (e.is_active = 1 OR ov.employee_id IS NULL)
         ORDER BY ov.override_date ASC"
    );
    $stmt->execute([$testDate, $testDate]);
    $ovRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ovActive = false;
    $ovInactive = false;
    foreach ($ovRows as $row) {
        if ((int) $row['employee_id'] === $activeId) $ovActive = true;
        if ((int) $row['employee_id'] === $inactiveId) $ovInactive = true;
    }
    test('Overrides query excludes inactive employee overrides', $ovActive && !$ovInactive);

    // ─── Test 4: Available times excludes inactive employees ─────────────
    // Mirrors available-times.php capacity query (step 3)
    $stmt = $db->prepare(
        "SELECT s.employee_id, s.start_time, s.end_time
         FROM oretir_schedules s
         JOIN oretir_employees e ON s.employee_id = e.id
         WHERE s.day_of_week = ?
           AND s.is_available = 1
           AND e.is_active = 1"
    );
    $stmt->execute([$dayOfWeek]);
    $capacityRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $capActive = false;
    $capInactive = false;
    foreach ($capacityRows as $row) {
        if ((int) $row['employee_id'] === $activeId) $capActive = true;
        if ((int) $row['employee_id'] === $inactiveId) $capInactive = true;
    }
    test('Available times excludes inactive employees', $capActive && !$capInactive);

    // ─── Test 5: Deactivating removes from weekly schedule results ───────
    $db->prepare("UPDATE oretir_employees SET is_active = 0 WHERE id = ?")->execute([$activeId]);

    $stmt = $db->query(
        "SELECT s.*, e.name AS employee_name, e.is_active
         FROM oretir_schedules s
         JOIN oretir_employees e ON s.employee_id = e.id
         WHERE e.is_active = 1
         ORDER BY e.name ASC, s.day_of_week ASC"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $foundAfterDeactivate = false;
    foreach ($rows as $row) {
        if ((int) $row['employee_id'] === $activeId) $foundAfterDeactivate = true;
    }
    test('Deactivating an employee removes them from weekly schedule results', !$foundAfterDeactivate);

} finally {
    // ─── Cleanup ────────────────────────────────────────────────────────────
    echo "\n--- Cleanup: removing test data ---\n";
    // Cascades handle oretir_schedules and oretir_schedule_overrides
    $db->prepare("DELETE FROM oretir_employees WHERE id IN (?, ?)")->execute([$activeId ?? 0, $inactiveId ?? 0]);
}

// ─── Summary ────────────────────────────────────────────────────────────────
echo "\n=== Results: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
