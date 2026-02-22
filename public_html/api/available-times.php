<?php
/**
 * Oregon Tires — Available Time Slots Endpoint
 * GET /api/available-times.php?date=YYYY-MM-DD
 *
 * Returns booking counts per time slot for a given date,
 * so the frontend can grey out fully-booked slots.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $date = sanitize((string) ($_GET['date'] ?? ''), 10);

    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        jsonError('Invalid date format. Use YYYY-MM-DD.');
    }

    if (!isValidAppointmentDate($date)) {
        jsonError('Invalid date. Must be a future weekday (Mon-Sat).');
    }

    $db = getDB();
    $legacyCapacity = 2;

    // Count active bookings per time slot for this date
    $stmt = $db->prepare(
        "SELECT preferred_time, COUNT(*) AS cnt
         FROM oretir_appointments
         WHERE preferred_date = ?
           AND status NOT IN ('cancelled', 'completed')
         GROUP BY preferred_time"
    );
    $stmt->execute([$date]);
    $rows = $stmt->fetchAll();

    // Build a map of time -> count
    $slotCounts = [];
    foreach ($rows as $row) {
        $slotCounts[$row['preferred_time']] = (int) $row['cnt'];
    }

    // ─── Employee schedule-based capacity ─────────────────────────────
    // Wrapped in try/catch: if schedule tables don't exist yet, fall
    // back to the original legacy behaviour (all slots open, capacity 2).
    $useSchedules = false;
    $shopClosed   = false;
    $shopStart    = 7;  // default window
    $shopEnd      = 18; // default window
    $slotCapacity = []; // hour => number of available employees

    try {
        $dayOfWeek = (int) (new DateTime($date))->format('w'); // 0=Sun … 6=Sat

        // 1) Shop-wide closure override for this date
        $stmt = $db->prepare(
            "SELECT id FROM oretir_schedule_overrides
             WHERE override_date = ? AND employee_id IS NULL AND is_closed = 1
             LIMIT 1"
        );
        $stmt->execute([$date]);
        if ($stmt->fetch()) {
            $shopClosed = true;
        }

        if (!$shopClosed) {
            // 2) Shop-wide special hours override for this date
            $stmt = $db->prepare(
                "SELECT start_time, end_time FROM oretir_schedule_overrides
                 WHERE override_date = ? AND employee_id IS NULL AND is_closed = 0
                 LIMIT 1"
            );
            $stmt->execute([$date]);
            $shopOverride = $stmt->fetch();
            if ($shopOverride) {
                $shopStart = (int) substr($shopOverride['start_time'], 0, 2);
                $shopEnd   = (int) substr($shopOverride['end_time'], 0, 2);
            }

            // 3) Load employee schedules for this day of week
            $stmt = $db->prepare(
                "SELECT s.employee_id, s.start_time, s.end_time
                 FROM oretir_schedules s
                 JOIN oretir_employees e ON s.employee_id = e.id
                 WHERE s.day_of_week = ?
                   AND s.is_available = 1
                   AND e.is_active = 1"
            );
            $stmt->execute([$dayOfWeek]);
            $schedules = $stmt->fetchAll();

            if (count($schedules) > 0) {
                $useSchedules = true;

                // Pre-load all employee-specific overrides for this date
                $stmt = $db->prepare(
                    "SELECT employee_id, is_closed, start_time, end_time
                     FROM oretir_schedule_overrides
                     WHERE override_date = ? AND employee_id IS NOT NULL"
                );
                $stmt->execute([$date]);
                $empOverrides = [];
                foreach ($stmt->fetchAll() as $ov) {
                    $empOverrides[(int) $ov['employee_id']] = $ov;
                }

                // 4) Calculate capacity per hourly slot
                for ($h = $shopStart; $h <= $shopEnd; $h++) {
                    $slotCapacity[$h] = 0;
                }

                foreach ($schedules as $sched) {
                    $empId    = (int) $sched['employee_id'];
                    $empStart = (int) substr($sched['start_time'], 0, 2);
                    $empEnd   = (int) substr($sched['end_time'], 0, 2);

                    // Apply employee-specific override if one exists
                    if (isset($empOverrides[$empId])) {
                        $ov = $empOverrides[$empId];
                        if ((int) $ov['is_closed']) {
                            continue; // employee off this day
                        }
                        // Special hours for this employee
                        $empStart = (int) substr($ov['start_time'], 0, 2);
                        $empEnd   = (int) substr($ov['end_time'], 0, 2);
                    }

                    // Add this employee's availability to each hourly slot
                    for ($h = $shopStart; $h <= $shopEnd; $h++) {
                        if ($h >= $empStart && $h < $empEnd) {
                            $slotCapacity[$h]++;
                        }
                    }
                }
            }
        }
    } catch (\Throwable $schedErr) {
        // Tables likely don't exist yet — fall back to legacy behaviour
        $useSchedules = false;
        $shopClosed   = false;
        error_log("available-times.php schedule check skipped: " . $schedErr->getMessage());
    }

    // ─── Build response slots ─────────────────────────────────────────
    if ($shopClosed) {
        // Return all slots as unavailable
        $allSlots = [];
        for ($h = 7; $h <= 18; $h++) {
            $time = sprintf('%02d:00', $h);
            $count = $slotCounts[$time] ?? 0;
            $allSlots[$time] = [
                'booked'    => $count,
                'capacity'  => 0,
                'available' => false,
            ];
        }

        jsonSuccess([
            'date'   => $date,
            'closed' => true,
            'slots'  => $allSlots,
        ]);
    }

    $allSlots = [];
    for ($h = $shopStart; $h <= $shopEnd; $h++) {
        $time     = sprintf('%02d:00', $h);
        $count    = $slotCounts[$time] ?? 0;
        $capacity = $useSchedules ? ($slotCapacity[$h] ?? 0) : $legacyCapacity;

        $allSlots[$time] = [
            'booked'    => $count,
            'capacity'  => $capacity,
            'available' => $capacity > 0 && $count < $capacity,
        ];
    }

    jsonSuccess([
        'date'         => $date,
        'max_per_slot' => $useSchedules ? null : $legacyCapacity,
        'slots'        => $allSlots,
    ]);

} catch (\Throwable $e) {
    error_log("Oregon Tires available-times.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
