<?php
/**
 * Oregon Tires — Employee CRUD Tests
 *
 * Tests full CRUD for employees (oretir_employees table) including
 * cascade behavior for schedules and skills.
 * Run via CLI: php tests/test-employee-crud.php
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

// ─── Setup ──────────────────────────────────────────────────────────────────
$rand = bin2hex(random_bytes(4));
$testName  = "EmpTest_{$rand}";
$testEmail = "emptest_{$rand}@example.com";
$empId = null;

echo "\n=== Employee CRUD Tests ===\n";
echo "--- Setup: using test name {$testName} ---\n\n";

try {
    // ─── Test 1: Create employee with name and role ─────────────────────
    $db->prepare(
        "INSERT INTO oretir_employees (name, email, phone, role, is_active, created_at, updated_at)
         VALUES (?, ?, '555-0099', 'Employee', 1, NOW(), NOW())"
    )->execute([$testName, $testEmail]);
    $empId = (int) $db->lastInsertId();
    test('Create employee with name and role', $empId > 0);

    // ─── Test 2: Read employee with group and skills ────────────────────
    $stmt = $db->prepare(
        "SELECT e.*, g.name_en AS group_name
         FROM oretir_employees e
         LEFT JOIN oretir_employee_groups g ON e.group_id = g.id
         WHERE e.id = ?"
    );
    $stmt->execute([$empId]);
    $emp = $stmt->fetch(PDO::FETCH_ASSOC);
    test('Read employee with group and skills', $emp !== false
        && $emp['name'] === $testName
        && $emp['email'] === $testEmail
        && $emp['role'] === 'Employee'
        && (int) $emp['is_active'] === 1
    );

    // ─── Test 3: Update employee name ───────────────────────────────────
    $newName = "EmpUpdated_{$rand}";
    $db->prepare("UPDATE oretir_employees SET name = ? WHERE id = ?")->execute([$newName, $empId]);
    $stmt = $db->prepare("SELECT name FROM oretir_employees WHERE id = ?");
    $stmt->execute([$empId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    test('Update employee name', $row['name'] === $newName);

    // ─── Test 4: Update employee email ──────────────────────────────────
    $newEmail = "empupdated_{$rand}@example.com";
    $db->prepare("UPDATE oretir_employees SET email = ? WHERE id = ?")->execute([$newEmail, $empId]);
    $stmt = $db->prepare("SELECT email FROM oretir_employees WHERE id = ?");
    $stmt->execute([$empId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    test('Update employee email', $row['email'] === $newEmail);

    // ─── Test 5: Update employee role to Manager ────────────────────────
    $db->prepare("UPDATE oretir_employees SET role = 'Manager' WHERE id = ?")->execute([$empId]);
    $stmt = $db->prepare("SELECT role FROM oretir_employees WHERE id = ?");
    $stmt->execute([$empId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    test('Update employee role to Manager', $row['role'] === 'Manager');

    // ─── Test 6: Toggle employee active status (deactivate) ─────────────
    $db->prepare("UPDATE oretir_employees SET is_active = 0 WHERE id = ?")->execute([$empId]);
    $stmt = $db->prepare("SELECT is_active FROM oretir_employees WHERE id = ?");
    $stmt->execute([$empId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    test('Toggle employee active status (deactivate)', (int) $row['is_active'] === 0);

    // ─── Test 7: Inactive employee excluded from schedule queries ───────
    // First create a schedule entry for this employee
    $db->prepare(
        "INSERT INTO oretir_schedules (employee_id, day_of_week, start_time, end_time, is_available, created_at, updated_at)
         VALUES (?, 1, '08:00:00', '17:00:00', 1, NOW(), NOW())"
    )->execute([$empId]);

    // Run weekly schedule query with is_active filter (mirrors available-times.php)
    $stmt = $db->prepare(
        "SELECT s.employee_id
         FROM oretir_schedules s
         JOIN oretir_employees e ON s.employee_id = e.id
         WHERE s.day_of_week = 1
           AND s.is_available = 1
           AND e.is_active = 1"
    );
    $stmt->execute();
    $schedEmpIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'employee_id');
    test('Inactive employee excluded from schedule queries', !in_array((string) $empId, $schedEmpIds));

    // ─── Test 8: Toggle employee active status (reactivate) ─────────────
    $db->prepare("UPDATE oretir_employees SET is_active = 1 WHERE id = ?")->execute([$empId]);
    $stmt = $db->prepare("SELECT is_active FROM oretir_employees WHERE id = ?");
    $stmt->execute([$empId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    test('Toggle employee active status (reactivate)', (int) $row['is_active'] === 1);

    // ─── Test 9: Add skills to employee ─────────────────────────────────
    $skillStmt = $db->prepare(
        "INSERT INTO oretir_employee_skills (employee_id, service_type, certified_at)
         VALUES (?, ?, NOW())"
    );
    $skillStmt->execute([$empId, 'tire-installation']);
    $skillStmt->execute([$empId, 'oil-change']);
    $skillStmt->execute([$empId, 'brake-service']);

    $stmt = $db->prepare("SELECT service_type FROM oretir_employee_skills WHERE employee_id = ? ORDER BY service_type ASC");
    $stmt->execute([$empId]);
    $skills = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'service_type');
    test('Add skills to employee', count($skills) === 3
        && in_array('tire-installation', $skills)
        && in_array('oil-change', $skills)
        && in_array('brake-service', $skills)
    );

    // ─── Test 10: Update skills (replace) ───────────────────────────────
    // Delete old skills and add new ones
    $db->prepare("DELETE FROM oretir_employee_skills WHERE employee_id = ?")->execute([$empId]);
    $skillStmt->execute([$empId, 'wheel-alignment']);
    $skillStmt->execute([$empId, 'mechanical-inspection']);

    $stmt = $db->prepare("SELECT service_type FROM oretir_employee_skills WHERE employee_id = ? ORDER BY service_type ASC");
    $stmt->execute([$empId]);
    $skills = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'service_type');
    test('Update skills (replace)', count($skills) === 2
        && in_array('wheel-alignment', $skills)
        && in_array('mechanical-inspection', $skills)
    );

    // ─── Test 11: Delete employee cascades schedules ────────────────────
    // Verify schedule rows exist before delete
    $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM oretir_schedules WHERE employee_id = ?");
    $stmt->execute([$empId]);
    $preDeleteSchedules = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Delete the employee
    $db->prepare("DELETE FROM oretir_employees WHERE id = ?")->execute([$empId]);

    // Check schedules are gone (CASCADE)
    $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM oretir_schedules WHERE employee_id = ?");
    $stmt->execute([$empId]);
    $postDeleteSchedules = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    test('Delete employee cascades schedules', $preDeleteSchedules > 0 && $postDeleteSchedules === 0);

    // ─── Test 12: Delete employee cascades skills ───────────────────────
    $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM oretir_employee_skills WHERE employee_id = ?");
    $stmt->execute([$empId]);
    $postDeleteSkills = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    test('Delete employee cascades skills', $postDeleteSkills === 0);

    // Mark as already cleaned up
    $empId = null;

} finally {
    // ─── Cleanup ────────────────────────────────────────────────────────────
    echo "\n--- Cleanup: removing test data ---\n";
    if ($empId !== null) {
        // If tests failed before the DELETE test, clean up manually
        $db->prepare("DELETE FROM oretir_employees WHERE id = ?")->execute([$empId]);
    }
    // Safety net: clean up any leftover test employees by name pattern
    $db->prepare("DELETE FROM oretir_employees WHERE name LIKE 'EmpTest_%' OR name LIKE 'EmpUpdated_%'")->execute();
}

// ─── Summary ────────────────────────────────────────────────────────────────
echo "\n=== Results: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
