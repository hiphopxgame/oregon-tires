<?php
/**
 * POST /api/member/ro-status.php
 *
 * Worker-side RO status transition. Restricts which transitions an
 * employee may perform and verifies the RO is assigned to them
 * (admins bypass the assignment check).
 *
 * Body: { "ro_number": "RO-XXXXXXXX", "new_status": "in_progress" }
 * Returns: { "success": true, "data": { "status": "in_progress" } }
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/validate.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

header('Content-Type: application/json');

try {
    requireMethod('POST');

    if (!MemberAuth::isMemberLoggedIn()) {
        jsonError('Authentication required.', 401);
    }

    $role = $_SESSION['dashboard_role'] ?? 'member';
    if (!in_array($role, ['employee', 'admin'], true)) {
        jsonError('Employee or admin access required.', 403);
    }

    $data = getJsonBody();
    $roNumber = sanitize((string) ($data['ro_number'] ?? ''), 30);
    $newStatus = sanitize((string) ($data['new_status'] ?? ''), 30);
    if ($roNumber === '' || $newStatus === '') {
        jsonError('ro_number and new_status are required.', 400);
    }

    // Allowed worker transitions: from => [allowed to]
    $allowed = [
        'intake'           => ['diagnosis', 'in_progress'],
        'check_in'         => ['diagnosis', 'in_progress'],
        'diagnosis'        => ['estimate_pending', 'in_progress'],
        'approved'         => ['in_progress'],
        'in_progress'      => ['ready', 'on_hold', 'waiting_parts'],
        'on_hold'          => ['in_progress'],
        'waiting_parts'    => ['in_progress'],
    ];

    // Resolve employee id
    $employeeId = $_SESSION['employee_id'] ?? null;
    if (!$employeeId) {
        $email = $_SESSION['member_email'] ?? '';
        $stmt = $pdo->prepare('SELECT id FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $emp = $stmt->fetch();
        $employeeId = $emp ? (int) $emp['id'] : null;
    }

    // Load RO
    $stmt = $pdo->prepare('SELECT id, status, assigned_employee_id FROM oretir_repair_orders WHERE ro_number = ? LIMIT 1');
    $stmt->execute([$roNumber]);
    $ro = $stmt->fetch();
    if (!$ro) {
        jsonError('Repair order not found.', 404);
    }

    // Assignment check (admin bypasses)
    if ($role !== 'admin') {
        if (!$employeeId || (int) $ro['assigned_employee_id'] !== (int) $employeeId) {
            jsonError('This repair order is not assigned to you.', 403);
        }
    }

    $current = (string) $ro['status'];
    if (!isset($allowed[$current]) || !in_array($newStatus, $allowed[$current], true)) {
        jsonError('Transition not permitted.', 400);
    }

    $upd = $pdo->prepare('UPDATE oretir_repair_orders SET status = ?, updated_at = NOW() WHERE id = ?');
    $upd->execute([$newStatus, (int) $ro['id']]);

    // Mirror minimal side effects from admin endpoint
    if ($newStatus === 'in_progress') {
        $pdo->prepare('UPDATE oretir_repair_orders SET service_started_at = COALESCE(service_started_at, NOW()) WHERE id = ?')
            ->execute([(int) $ro['id']]);
    } elseif ($newStatus === 'ready') {
        $pdo->prepare('UPDATE oretir_repair_orders SET service_ended_at = NOW() WHERE id = ?')
            ->execute([(int) $ro['id']]);
    }

    jsonSuccess(['status' => $newStatus]);

} catch (\Throwable $e) {
    error_log('ro-status.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error.']);
}
