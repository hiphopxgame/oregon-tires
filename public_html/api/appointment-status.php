<?php
/**
 * Oregon Tires — Appointment Status Lookup
 * GET /api/appointment-status.php?ref=OT-XXXXXXXX&email=customer@example.com
 *
 * Returns appointment details for the given reference number + email combo.
 * Rate limited to prevent enumeration attacks.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    // Rate limit: 10 lookups per hour per IP
    checkRateLimit('appointment_status', 10, 3600);

    $ref   = sanitize((string) ($_GET['ref'] ?? ''), 20);
    $email = sanitize((string) ($_GET['email'] ?? ''), 255);

    if (empty($ref) || empty($email)) {
        jsonError('Reference number and email are required.', 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonError('Invalid email format.', 400);
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT a.id, a.reference_number, a.first_name, a.last_name, a.service,
                a.preferred_date, a.preferred_time, a.status, a.vehicle_year,
                a.vehicle_make, a.vehicle_model, a.created_at,
                e.name AS employee_name
         FROM oretir_appointments a
         LEFT JOIN oretir_employees e ON a.assigned_employee_id = e.id
         WHERE a.reference_number = ?
           AND a.email = ?
         LIMIT 1'
    );
    $stmt->execute([$ref, $email]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        jsonError('No appointment found with that reference number and email.', 404);
    }

    // Build vehicle string (trim empty parts)
    $vehicleParts = array_filter([
        $appointment['vehicle_year'] ?? '',
        $appointment['vehicle_make'] ?? '',
        $appointment['vehicle_model'] ?? '',
    ], fn(string $v) => $v !== '');

    jsonSuccess([
        'reference'  => $appointment['reference_number'] ?: str_pad((string) $appointment['id'], 5, '0', STR_PAD_LEFT),
        'name'       => $appointment['first_name'] . ' ' . $appointment['last_name'],
        'service'    => $appointment['service'],
        'date'       => $appointment['preferred_date'],
        'time'       => $appointment['preferred_time'],
        'status'     => $appointment['status'],
        'vehicle'    => implode(' ', $vehicleParts),
        'technician' => $appointment['employee_name'] ?? null,
        'booked_on'  => $appointment['created_at'],
    ]);
} catch (\Throwable $e) {
    error_log('Oregon Tires appointment-status error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
