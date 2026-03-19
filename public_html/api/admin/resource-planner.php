<?php
/**
 * Oregon Tires — Resource Planner API
 * GET /api/admin/resource-planner.php?dates=2026-03-19,2026-03-20
 *
 * Returns per-date resource planning data: employees, appointments,
 * hourly breakdown by service type, skill gap analysis, and staffing recommendations.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $staff = requireStaff();
    requireMethod('GET');
    $db = getDB();

    $datesParam = sanitize((string) ($_GET['dates'] ?? ''), 200);
    if (!$datesParam) {
        // Default: today + tomorrow
        $datesParam = date('Y-m-d') . ',' . date('Y-m-d', strtotime('+1 day'));
    }

    $dates = array_filter(array_map('trim', explode(',', $datesParam)));
    $validDates = [];
    foreach ($dates as $d) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            $validDates[] = $d;
        }
    }
    if (empty($validDates)) {
        jsonError('No valid dates provided. Use YYYY-MM-DD format.', 400);
    }
    if (count($validDates) > 7) {
        jsonError('Maximum 7 dates per request.', 400);
    }

    // Load all active employees with skills
    $empStmt = $db->query(
        "SELECT id, name, email, phone, role, is_active, max_daily_appointments
         FROM oretir_employees WHERE is_active = 1 ORDER BY name ASC"
    );
    $allEmployees = $empStmt->fetchAll(PDO::FETCH_ASSOC);

    // Load all employee skills
    $skillMap = [];
    try {
        $skillRows = $db->query(
            "SELECT employee_id, service_type FROM oretir_employee_skills ORDER BY employee_id, service_type"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($skillRows as $sr) {
            $skillMap[(int)$sr['employee_id']][] = $sr['service_type'];
        }
    } catch (\Throwable $e) {
        error_log('resource-planner.php: employee_skills query failed: ' . $e->getMessage());
    }
    foreach ($allEmployees as &$emp) {
        $emp['id'] = (int)$emp['id'];
        $emp['is_active'] = (bool)$emp['is_active'];
        $emp['max_daily_appointments'] = (int)($emp['max_daily_appointments'] ?? 10);
        $emp['skills'] = $skillMap[$emp['id']] ?? [];
    }
    unset($emp);

    // Load inactive employees for skills matrix
    $inactiveStmt = $db->query(
        "SELECT id, name, role, is_active FROM oretir_employees WHERE is_active = 0 ORDER BY name ASC"
    );
    $inactiveEmployees = $inactiveStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($inactiveEmployees as &$ie) {
        $ie['id'] = (int)$ie['id'];
        $ie['is_active'] = false;
        $ie['skills'] = $skillMap[$ie['id']] ?? [];
    }
    unset($ie);

    // Build per-date results
    $results = [];
    foreach ($validDates as $date) {
        $results[$date] = buildDateData($db, $date, $skillMap);
    }

    // Off-duty employees (those not scheduled on any requested date)
    $allScheduledIds = [];
    foreach ($results as $dateData) {
        foreach ($dateData['employees'] as $emp) {
            $allScheduledIds[] = $emp['id'];
        }
    }
    $allScheduledIds = array_unique($allScheduledIds);
    $offDuty = array_values(array_filter($allEmployees, function($e) use ($allScheduledIds) {
        return !in_array($e['id'], $allScheduledIds);
    }));

    jsonSuccess([
        'dates' => $results,
        'all_employees' => $allEmployees,
        'inactive_employees' => $inactiveEmployees,
        'off_duty_employees' => $offDuty,
        'service_types' => [
            'tire-installation', 'tire-repair', 'wheel-alignment', 'oil-change',
            'brake-service', 'tuneup', 'mechanical-inspection', 'mobile-service',
            'roadside-assistance', 'other'
        ],
    ]);

} catch (\Throwable $e) {
    error_log('resource-planner.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}

/**
 * Build resource planning data for a single date
 */
function buildDateData(PDO $db, string $date, array $skillMap): array
{
    $dayOfWeek = (int)(new DateTime($date))->format('w');

    // Shop-wide override
    $stmt = $db->prepare(
        "SELECT id, override_date, is_closed, start_time, end_time, reason
         FROM oretir_schedule_overrides
         WHERE override_date = ? AND employee_id IS NULL LIMIT 1"
    );
    $stmt->execute([$date]);
    $shopOverride = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // If shop is closed, return minimal data
    if ($shopOverride && !empty($shopOverride['is_closed'])) {
        return [
            'date' => $date,
            'shop_closed' => true,
            'employees' => [],
            'appointments' => [],
            'hourly_breakdown' => [],
            'skill_gaps' => [],
            'peak_hour' => null,
            'peak_concurrent' => 0,
            'working_count' => 0,
            'unassigned_count' => 0,
        ];
    }

    // Employee schedules for this day + overrides
    $stmt = $db->prepare(
        "SELECT s.employee_id, s.start_time, s.end_time, s.is_available,
                e.name AS employee_name, e.max_daily_appointments,
                ov.id AS override_id, ov.is_closed AS ov_is_closed,
                ov.start_time AS ov_start_time, ov.end_time AS ov_end_time
         FROM oretir_schedules s
         JOIN oretir_employees e ON s.employee_id = e.id AND e.is_active = 1
         LEFT JOIN oretir_schedule_overrides ov ON ov.employee_id = s.employee_id AND ov.override_date = ?
         WHERE s.day_of_week = ?
         ORDER BY e.name ASC"
    );
    $stmt->execute([$date, $dayOfWeek]);
    $schedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build working employees list
    $workingEmployees = [];
    foreach ($schedRows as $row) {
        $empId = (int)$row['employee_id'];
        // Skip if employee has override closing them
        if (!empty($row['ov_is_closed'])) continue;
        // Skip if not normally available and no override
        if (!(bool)$row['is_available'] && !$row['override_id']) continue;

        $startTime = $row['ov_start_time'] ?? $row['start_time'];
        $endTime = $row['ov_end_time'] ?? $row['end_time'];

        $workingEmployees[] = [
            'id' => $empId,
            'name' => $row['employee_name'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'max_daily' => (int)($row['max_daily_appointments'] ?? 10),
            'skills' => $skillMap[$empId] ?? [],
            'has_override' => (bool)$row['override_id'],
        ];
    }

    // Appointments for this date
    $stmt = $db->prepare(
        "SELECT a.id, a.preferred_time, a.service, a.first_name, a.last_name,
                a.status, a.assigned_employee_id, a.phone, a.email
         FROM oretir_appointments a
         WHERE a.preferred_date = ? AND a.status NOT IN ('cancelled', 'completed')
         ORDER BY a.preferred_time ASC"
    );
    $stmt->execute([$date]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cast types
    foreach ($appointments as &$apt) {
        $apt['id'] = (int)$apt['id'];
        $apt['assigned_employee_id'] = $apt['assigned_employee_id'] ? (int)$apt['assigned_employee_id'] : null;
    }
    unset($apt);

    // Count assigned appointments per employee
    $assignedCounts = [];
    foreach ($appointments as $apt) {
        if ($apt['assigned_employee_id']) {
            $assignedCounts[$apt['assigned_employee_id']] = ($assignedCounts[$apt['assigned_employee_id']] ?? 0) + 1;
        }
    }
    foreach ($workingEmployees as &$we) {
        $we['assigned_count'] = $assignedCounts[$we['id']] ?? 0;
    }
    unset($we);

    // Hourly breakdown by service type
    $hourlyBreakdown = [];
    for ($h = 8; $h <= 16; $h++) {
        $hourlyBreakdown[$h] = ['hour' => $h, 'total' => 0, 'services' => []];
    }

    foreach ($appointments as $apt) {
        if (!$apt['preferred_time'] || $apt['status'] === 'completed') continue;
        $hour = (int)substr($apt['preferred_time'], 0, 2);
        if ($hour < 8 || $hour > 16) continue;

        $svc = $apt['service'] ?: 'other';
        $hourlyBreakdown[$hour]['total']++;
        $hourlyBreakdown[$hour]['services'][$svc] = ($hourlyBreakdown[$hour]['services'][$svc] ?? 0) + 1;
    }

    // Calculate capacity per hour (how many employees available)
    $hourlyCapacity = [];
    for ($h = 8; $h <= 16; $h++) {
        $hourlyCapacity[$h] = 0;
    }
    foreach ($workingEmployees as $we) {
        $startH = (int)substr($we['start_time'], 0, 2);
        $endH = (int)substr($we['end_time'], 0, 2);
        for ($h = $startH; $h < $endH && $h <= 16; $h++) {
            if ($h >= 8) $hourlyCapacity[$h]++;
        }
    }

    // Attach capacity to hourly breakdown
    foreach ($hourlyBreakdown as &$hb) {
        $hb['capacity'] = $hourlyCapacity[$hb['hour']] ?? 0;
    }
    unset($hb);

    // Find peak hour
    $peakHour = null;
    $peakCount = 0;
    foreach ($hourlyBreakdown as $hb) {
        if ($hb['total'] > $peakCount) {
            $peakCount = $hb['total'];
            $peakHour = $hb['hour'];
        }
    }

    // Skill gap analysis
    $skillGaps = [];
    foreach ($hourlyBreakdown as $hb) {
        if (empty($hb['services'])) continue;
        foreach ($hb['services'] as $svc => $demand) {
            // Count employees with this skill available at this hour
            $supply = 0;
            foreach ($workingEmployees as $we) {
                $startH = (int)substr($we['start_time'], 0, 2);
                $endH = (int)substr($we['end_time'], 0, 2);
                if ($hb['hour'] >= $startH && $hb['hour'] < $endH) {
                    if (in_array($svc, $we['skills'])) {
                        $supply++;
                    }
                }
            }
            if ($demand > $supply) {
                $severity = ($supply === 0) ? 'critical' : 'warning';
                $skillGaps[] = [
                    'hour' => $hb['hour'],
                    'service' => $svc,
                    'demand' => $demand,
                    'supply' => $supply,
                    'gap' => $demand - $supply,
                    'severity' => $severity,
                ];
            }
        }
    }

    // Sort skill gaps: critical first, then by gap size
    usort($skillGaps, function($a, $b) {
        if ($a['severity'] !== $b['severity']) {
            return $a['severity'] === 'critical' ? -1 : 1;
        }
        return $b['gap'] - $a['gap'];
    });

    // Unassigned count
    $unassignedCount = count(array_filter($appointments, function($a) {
        return !$a['assigned_employee_id'] && $a['status'] !== 'completed';
    }));

    return [
        'date' => $date,
        'shop_closed' => false,
        'employees' => $workingEmployees,
        'appointments' => $appointments,
        'hourly_breakdown' => array_values($hourlyBreakdown),
        'skill_gaps' => $skillGaps,
        'peak_hour' => $peakHour,
        'peak_concurrent' => $peakCount,
        'working_count' => count($workingEmployees),
        'unassigned_count' => $unassignedCount,
    ];
}
