<?php
/**
 * Oregon Tires — Admin Analytics Endpoint
 * GET /api/admin/analytics.php
 *
 * Returns aggregated statistics for the admin dashboard:
 * - appointments_by_service, appointments_by_status
 * - bookings_trend (daily for date range)
 * - peak_times (count per hour slot)
 * - messages_by_status
 * - total_appointments, total_messages, total_employees
 * - employee_productivity, revenue_by_month, conversion_funnel
 * - service_duration, customer_retention
 * - revenue_by_service, customer_acquisition, top_customers
 * - no_show_rate, average_ticket_value
 * - labor_hours_total, labor_hours_per_employee
 *
 * Supports ?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD (default: 30 days)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $admin = requireAdmin();
    requireMethod('GET');
    $db = getDB();

    // ─── Date range parsing ─────────────────────────────────────────────
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

    // Validate date formats
    $startDt = DateTime::createFromFormat('Y-m-d', $startDate);
    $endDt = DateTime::createFromFormat('Y-m-d', $endDate);

    if (!$startDt || $startDt->format('Y-m-d') !== $startDate) {
        $startDate = date('Y-m-d', strtotime('-30 days'));
    }
    if (!$endDt || $endDt->format('Y-m-d') !== $endDate) {
        $endDate = date('Y-m-d');
    }

    // Ensure end_date includes the full day
    $endDateEnd = $endDate . ' 23:59:59';

    // ─── Appointments by service (within date range) ────────────────────
    $stmt = $db->prepare(
        'SELECT service, COUNT(*) AS count
         FROM oretir_appointments
         WHERE created_at >= ? AND created_at <= ?
         GROUP BY service
         ORDER BY count DESC'
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $appointmentsByService = $stmt->fetchAll();

    // ─── Appointments by status (within date range) ─────────────────────
    $stmt = $db->prepare(
        'SELECT status, COUNT(*) AS count
         FROM oretir_appointments
         WHERE created_at >= ? AND created_at <= ?
         GROUP BY status
         ORDER BY FIELD(status, "new", "pending", "confirmed", "completed", "cancelled")'
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $appointmentsByStatus = $stmt->fetchAll();

    // ─── Bookings trend (daily count within date range) ─────────────────
    $stmt = $db->prepare(
        'SELECT DATE(created_at) AS date, COUNT(*) AS count
         FROM oretir_appointments
         WHERE created_at >= ? AND created_at <= ?
         GROUP BY DATE(created_at)
         ORDER BY date ASC'
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $bookingsTrend = $stmt->fetchAll();

    // ─── Peak times (count per hour slot, within date range) ────────────
    $stmt = $db->prepare(
        'SELECT CAST(SUBSTRING(preferred_time, 1, 2) AS UNSIGNED) AS hour_slot, COUNT(*) AS count
         FROM oretir_appointments
         WHERE created_at >= ? AND created_at <= ?
         GROUP BY hour_slot
         ORDER BY hour_slot ASC'
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $peakTimes = $stmt->fetchAll();

    // ─── Messages by status (within date range) ─────────────────────────
    $stmt = $db->prepare(
        'SELECT status, COUNT(*) AS count
         FROM oretir_contact_messages
         WHERE created_at >= ? AND created_at <= ?
         GROUP BY status
         ORDER BY FIELD(status, "new", "priority", "completed")'
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $messagesByStatus = $stmt->fetchAll();

    // ─── Totals (within date range) ─────────────────────────────────────
    $stmt = $db->prepare('SELECT COUNT(*) FROM oretir_appointments WHERE created_at >= ? AND created_at <= ?');
    $stmt->execute([$startDate, $endDateEnd]);
    $totalAppointments = (int) $stmt->fetchColumn();

    $stmt = $db->prepare('SELECT COUNT(*) FROM oretir_contact_messages WHERE created_at >= ? AND created_at <= ?');
    $stmt->execute([$startDate, $endDateEnd]);
    $totalMessages = (int) $stmt->fetchColumn();

    $totalEmployees = (int) $db->query('SELECT COUNT(*) FROM oretir_employees WHERE is_active = 1')->fetchColumn();

    // ─── Employee productivity (within date range) ──────────────────────
    $stmt = $db->prepare(
        "SELECT e.name AS employee_name, e.id AS employee_id,
                COUNT(a.id) AS completed_count
         FROM oretir_employees e
         LEFT JOIN oretir_appointments a ON a.assigned_employee_id = e.id
              AND a.status = 'completed'
              AND a.updated_at >= ? AND a.updated_at <= ?
         WHERE e.is_active = 1
         GROUP BY e.id, e.name
         ORDER BY completed_count DESC"
    );
    $stmt->execute([$startDate, $endDateEnd]);
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

    // ─── Conversion funnel (within date range) ──────────────────────────
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_appointments WHERE created_at >= ? AND created_at <= ?"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $totalBookings = (int) $stmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_repair_orders WHERE created_at >= ? AND created_at <= ?"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $totalROs = (int) $stmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_repair_orders WHERE status = 'completed' AND updated_at >= ? AND updated_at <= ?"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $completedROs = (int) $stmt->fetchColumn();

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

    // ─── Revenue by service type (within date range) ────────────────────
    $stmt = $db->prepare(
        "SELECT a.service, SUM(e.total) AS total_revenue, COUNT(e.id) AS estimate_count
         FROM oretir_estimates e
         JOIN oretir_repair_orders r ON r.id = e.repair_order_id
         JOIN oretir_appointments a ON a.id = r.appointment_id
         WHERE e.status = 'approved'
           AND e.updated_at >= ? AND e.updated_at <= ?
         GROUP BY a.service
         ORDER BY total_revenue DESC"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $revenueByService = $stmt->fetchAll();

    // ─── Customer acquisition (new customers per month, last 6 months) ──
    $stmt = $db->query(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS new_customers
         FROM oretir_customers
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY month
         ORDER BY month ASC"
    );
    $customerAcquisition = $stmt->fetchAll();

    // ─── Top 10 customers by approved estimate total (within date range) ─
    $stmt = $db->prepare(
        "SELECT c.id, c.first_name, c.last_name, c.email,
                SUM(e.total) AS total_spent, COUNT(e.id) AS estimate_count
         FROM oretir_customers c
         JOIN oretir_repair_orders r ON r.customer_id = c.id
         JOIN oretir_estimates e ON e.repair_order_id = r.id
         WHERE e.status = 'approved'
           AND e.updated_at >= ? AND e.updated_at <= ?
         GROUP BY c.id, c.first_name, c.last_name, c.email
         ORDER BY total_spent DESC
         LIMIT 10"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $topCustomers = $stmt->fetchAll();

    // ─── No-show rate (within date range) ───────────────────────────────
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_appointments WHERE created_at >= ? AND created_at <= ?"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $totalApptRange = (int) $stmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM oretir_appointments
         WHERE status = 'cancelled' AND created_at >= ? AND created_at <= ?"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $cancelledAppts = (int) $stmt->fetchColumn();

    $noShowRate = $totalApptRange > 0
        ? round(($cancelledAppts / $totalApptRange) * 100, 1)
        : 0;

    // ─── Average ticket value (within date range) ───────────────────────
    $stmt = $db->prepare(
        "SELECT AVG(total) FROM oretir_estimates
         WHERE status = 'approved'
           AND updated_at >= ? AND updated_at <= ?"
    );
    $stmt->execute([$startDate, $endDateEnd]);
    $avgTicket = $stmt->fetchColumn();
    $averageTicketValue = $avgTicket !== false ? round((float) $avgTicket, 2) : 0;

    // ─── Labor hours (within date range, only if table exists) ──────────
    $laborHoursTotal = null;
    $laborHoursPerEmployee = null;

    try {
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(duration_minutes), 0) AS total_minutes
             FROM oretir_labor_entries
             WHERE clock_in_at >= ? AND clock_in_at <= ?
               AND clock_out_at IS NOT NULL"
        );
        $stmt->execute([$startDate, $endDateEnd]);
        $totalMinutes = (int) $stmt->fetchColumn();
        $laborHoursTotal = round($totalMinutes / 60, 1);

        $stmt = $db->prepare(
            "SELECT e.name AS employee_name, e.id AS employee_id,
                    ROUND(SUM(l.duration_minutes) / 60, 1) AS total_hours,
                    COUNT(l.id) AS entry_count
             FROM oretir_labor_entries l
             JOIN oretir_employees e ON e.id = l.employee_id
             WHERE l.clock_in_at >= ? AND l.clock_in_at <= ?
               AND l.clock_out_at IS NOT NULL
             GROUP BY e.id, e.name
             ORDER BY total_hours DESC"
        );
        $stmt->execute([$startDate, $endDateEnd]);
        $laborHoursPerEmployee = $stmt->fetchAll();
    } catch (\Throwable $e) {
        // Table may not exist yet; silently skip
        error_log('analytics.php labor_hours query skipped: ' . $e->getMessage());
    }

    jsonSuccess([
        'date_range'              => ['start' => $startDate, 'end' => $endDate],
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
        'revenue_by_service'      => $revenueByService,
        'customer_acquisition'    => $customerAcquisition,
        'top_customers'           => $topCustomers,
        'no_show_rate'            => $noShowRate,
        'average_ticket_value'    => $averageTicketValue,
        'labor_hours_total'       => $laborHoursTotal,
        'labor_hours_per_employee' => $laborHoursPerEmployee,
    ]);

} catch (\Throwable $e) {
    error_log('analytics.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
