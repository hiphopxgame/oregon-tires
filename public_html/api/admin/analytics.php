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
    $admin = requireAdmin();
    requireMethod('GET');
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

    // ─── Employee productivity (last 30 days) ──────────────────────────
    $stmt = $db->query(
        "SELECT e.name AS employee_name, e.id AS employee_id,
                COUNT(a.id) AS completed_count
         FROM oretir_employees e
         LEFT JOIN oretir_appointments a ON a.assigned_employee_id = e.id
              AND a.status = 'completed'
              AND a.updated_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         WHERE e.is_active = 1
         GROUP BY e.id, e.name
         ORDER BY completed_count DESC"
    );
    $employeeProductivity = $stmt->fetchAll();

    // ─── Revenue estimate by month (last 6 months) ─────────────────────
    $stmt = $db->query(
        "SELECT DATE_FORMAT(e.updated_at, '%Y-%m') AS month,
                SUM(e.total) AS revenue,
                COUNT(e.id) AS estimate_count
         FROM oretir_estimates e
         WHERE e.status = 'approved'
           AND e.updated_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY month
         ORDER BY month ASC"
    );
    $revenueByMonth = $stmt->fetchAll();

    // ─── Conversion funnel ─────────────────────────────────────────────
    $totalBookings = (int) $db->query(
        "SELECT COUNT(*) FROM oretir_appointments WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    )->fetchColumn();
    $totalROs = (int) $db->query(
        "SELECT COUNT(*) FROM oretir_repair_orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    )->fetchColumn();
    $completedROs = (int) $db->query(
        "SELECT COUNT(*) FROM oretir_repair_orders WHERE status = 'completed' AND updated_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    )->fetchColumn();
    $conversionFunnel = [
        ['stage' => 'Bookings', 'count' => $totalBookings],
        ['stage' => 'Repair Orders', 'count' => $totalROs],
        ['stage' => 'Completed', 'count' => $completedROs],
    ];

    // ─── Average service duration by service type ──────────────────────
    $stmt = $db->query(
        "SELECT a.service,
                ROUND(AVG(DATEDIFF(r.updated_at, r.created_at)), 1) AS avg_days,
                COUNT(r.id) AS sample_size
         FROM oretir_repair_orders r
         JOIN oretir_appointments a ON a.id = r.appointment_id
         WHERE r.status = 'completed'
         GROUP BY a.service
         HAVING sample_size >= 1
         ORDER BY avg_days ASC"
    );
    $serviceDuration = $stmt->fetchAll();

    // ─── Customer retention (repeat vs new) ────────────────────────────
    $totalCustomers = (int) $db->query('SELECT COUNT(*) FROM oretir_customers')->fetchColumn();
    $repeatCustomers = (int) $db->query('SELECT COUNT(*) FROM oretir_customers WHERE visit_count > 1')->fetchColumn();
    $customerRetention = [
        'total' => $totalCustomers,
        'repeat' => $repeatCustomers,
        'repeat_pct' => $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 1) : 0,
    ];

    jsonSuccess([
        'appointments_by_service' => $appointmentsByService,
        'appointments_by_status'  => $appointmentsByStatus,
        'bookings_trend'          => $bookingsTrend,
        'peak_times'              => $peakTimes,
        'messages_by_status'      => $messagesByStatus,
        'total_appointments'      => $totalAppointments,
        'total_messages'          => $totalMessages,
        'total_employees'         => $totalEmployees,
        'employee_productivity'   => $employeeProductivity,
        'revenue_by_month'        => $revenueByMonth,
        'conversion_funnel'       => $conversionFunnel,
        'service_duration'        => $serviceDuration,
        'customer_retention'      => $customerRetention,
    ]);

} catch (\Throwable $e) {
    error_log('analytics.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
