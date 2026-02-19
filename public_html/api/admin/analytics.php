<?php
/**
 * Oregon Tires — Admin Analytics Endpoint
 * GET /api/admin/analytics.php
 *
 * Returns aggregated statistics for the admin dashboard:
 * - appointments_by_service, appointments_by_status
 * - bookings_trend (daily for last 30 days)
 * - peak_times (count per hour slot)
 * - messages_by_status
 * - total_appointments, total_messages, total_employees
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    requireMethod('GET');
    $admin = requireAdmin();
    $db = getDB();

    // ─── Appointments by service ──────────────────────────────────────────
    $stmt = $db->query(
        'SELECT service, COUNT(*) AS count
         FROM oretir_appointments
         GROUP BY service
         ORDER BY count DESC'
    );
    $appointmentsByService = $stmt->fetchAll();

    // ─── Appointments by status ───────────────────────────────────────────
    $stmt = $db->query(
        'SELECT status, COUNT(*) AS count
         FROM oretir_appointments
         GROUP BY status
         ORDER BY FIELD(status, "new", "pending", "confirmed", "completed", "cancelled")'
    );
    $appointmentsByStatus = $stmt->fetchAll();

    // ─── Bookings trend (daily count for last 30 days) ────────────────────
    $stmt = $db->query(
        'SELECT DATE(created_at) AS date, COUNT(*) AS count
         FROM oretir_appointments
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY DATE(created_at)
         ORDER BY date ASC'
    );
    $bookingsTrend = $stmt->fetchAll();

    // ─── Peak times (count per hour slot) ─────────────────────────────────
    $stmt = $db->query(
        'SELECT CAST(SUBSTRING(preferred_time, 1, 2) AS UNSIGNED) AS hour_slot, COUNT(*) AS count
         FROM oretir_appointments
         GROUP BY hour_slot
         ORDER BY hour_slot ASC'
    );
    $peakTimes = $stmt->fetchAll();

    // ─── Messages by status ───────────────────────────────────────────────
    $stmt = $db->query(
        'SELECT status, COUNT(*) AS count
         FROM oretir_contact_messages
         GROUP BY status
         ORDER BY FIELD(status, "new", "priority", "completed")'
    );
    $messagesByStatus = $stmt->fetchAll();

    // ─── Totals ───────────────────────────────────────────────────────────
    $totalAppointments = (int) $db->query('SELECT COUNT(*) FROM oretir_appointments')->fetchColumn();
    $totalMessages     = (int) $db->query('SELECT COUNT(*) FROM oretir_contact_messages')->fetchColumn();
    $totalEmployees    = (int) $db->query('SELECT COUNT(*) FROM oretir_employees WHERE is_active = 1')->fetchColumn();

    jsonSuccess([
        'appointments_by_service' => $appointmentsByService,
        'appointments_by_status'  => $appointmentsByStatus,
        'bookings_trend'          => $bookingsTrend,
        'peak_times'              => $peakTimes,
        'messages_by_status'      => $messagesByStatus,
        'total_appointments'      => $totalAppointments,
        'total_messages'          => $totalMessages,
        'total_employees'         => $totalEmployees,
    ]);

} catch (\Throwable $e) {
    error_log('analytics.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
