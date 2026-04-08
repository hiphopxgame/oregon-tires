<?php
/**
 * POST /api/member/ro-note.php
 *
 * Append a timestamped technician note to a repair order. Worker-only.
 *
 * Body: { "ro_number": "RO-XXXXXXXX", "note": "..." }
 * Returns: { "success": true }
 *
 * Append format matches admin/repair-orders.php (note_append branch):
 *   "[Author Name — Mon j, Y g:ia]\n{note}"  with newest entry first,
 *   separated from previous notes by "\n\n".
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
    $note = sanitize((string) ($data['note'] ?? ''), 2000);
    if ($roNumber === '' || $note === '') {
        jsonError('ro_number and note are required.', 400);
    }

    $employeeId = $_SESSION['employee_id'] ?? null;
    $email = $_SESSION['member_email'] ?? '';
    if (!$employeeId && $email) {
        $stmt = $pdo->prepare('SELECT id, name FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $emp = $stmt->fetch();
        $employeeId = $emp ? (int) $emp['id'] : null;
        $authorName = $emp['name'] ?? $email;
    } else {
        $authorName = $email ?: 'Worker';
    }

    $stmt = $pdo->prepare('SELECT id, technician_notes, assigned_employee_id FROM oretir_repair_orders WHERE ro_number = ? LIMIT 1');
    $stmt->execute([$roNumber]);
    $ro = $stmt->fetch();
    if (!$ro) {
        jsonError('Repair order not found.', 404);
    }

    if ($role !== 'admin') {
        if (!$employeeId || (int) $ro['assigned_employee_id'] !== (int) $employeeId) {
            jsonError('This repair order is not assigned to you.', 403);
        }
    }

    $timestamp = date('M j, Y g:ia');
    $entry = "[{$authorName} — {$timestamp}]\n{$note}";
    $existing = (string) ($ro['technician_notes'] ?? '');
    $combined = $existing ? $entry . "\n\n" . $existing : $entry;

    $upd = $pdo->prepare('UPDATE oretir_repair_orders SET technician_notes = ?, updated_at = NOW() WHERE id = ?');
    $upd->execute([$combined, (int) $ro['id']]);

    jsonSuccess(['appended' => true]);

} catch (\Throwable $e) {
    error_log('ro-note.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error.']);
}
