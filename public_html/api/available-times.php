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
require_once __DIR__ . '/../includes/business-hours.php';

try {
    requireMethod('GET');

    $date = sanitize((string) ($_GET['date'] ?? ''), 10);

    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        jsonError('Invalid date format. Use YYYY-MM-DD.');
    }

    if (!isValidAppointmentDate($date)) {
        jsonError('Invalid date. Must be a future weekday (Mon-Sat).');
    }

    // Check cache first (60s TTL, keyed by date)
    if (function_exists('cacheGet')) {
        $cacheKey = "available_times:{$date}";
        $cached = cacheGet($cacheKey, 60, 'oregon_tires');
        if ($cached !== null) {
            if (!headers_sent()) { header('X-Cache: HIT'); }
            jsonSuccess($cached);
        }
    }

    $db = getDB();
    $maxBays = 2;
    $legacyCapacity = $maxBays;

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

    // ─── Business hours from config ─────────────────────────────────
    try {
        $bizHours = getBusinessHoursForDate($db, $date);
        if (!$bizHours['is_open']) {
            $shopClosed = true;
        } else {
            $shopStart = (int) substr($bizHours['open_time'], 0, 2);
            $shopEnd   = (int) substr($bizHours['close_time'], 0, 2);
        }
    } catch (\Throwable $bhErr) {
        error_log("available-times.php: business hours lookup skipped: " . $bhErr->getMessage());
    }

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

                // 4) Calculate capacity per 15-min slot
                for ($h = $shopStart; $h <= $shopEnd; $h++) {
                    foreach ([0, 15, 30, 45] as $m) {
                        if ($h === $shopEnd && $m > 0) break;
                        $key = sprintf('%02d:%02d', $h, $m);
                        $slotCapacity[$key] = 0;
                    }
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

                    // Add this employee's availability to each 15-min slot
                    for ($h = $shopStart; $h <= $shopEnd; $h++) {
                        foreach ([0, 15, 30, 45] as $m) {
                            if ($h === $shopEnd && $m > 0) break;
                            $key = sprintf('%02d:%02d', $h, $m);
                            if ($h >= $empStart && $h < $empEnd) {
                                $slotCapacity[$key]++;
                            }
                        }
                    }
                }

                // 5) Factor in max_daily_appointments per employee
                try {
                    $dailyCountStmt = $db->prepare(
                        "SELECT e.id, e.max_daily_appointments,
                                COUNT(a.id) AS daily_count
                         FROM oretir_employees e
                         LEFT JOIN oretir_appointments a ON a.assigned_employee_id = e.id
                              AND a.preferred_date = ?
                              AND a.status NOT IN ('cancelled')
                         WHERE e.is_active = 1
                         GROUP BY e.id"
                    );
                    $dailyCountStmt->execute([$date]);
                    $empDailyCounts = [];
                    foreach ($dailyCountStmt->fetchAll() as $dc) {
                        if ((int) $dc['daily_count'] >= (int) $dc['max_daily_appointments']) {
                            $empDailyCounts[(int) $dc['id']] = true; // at capacity
                        }
                    }

                    // Reduce slot capacity for employees at daily limit
                    if (!empty($empDailyCounts)) {
                        foreach ($schedules as $sched) {
                            $empId = (int) $sched['employee_id'];
                            if (isset($empDailyCounts[$empId])) {
                                // This employee is at daily capacity — reduce all their slots
                                $empStart = (int) substr($sched['start_time'], 0, 2);
                                $empEnd   = (int) substr($sched['end_time'], 0, 2);
                                for ($h = $shopStart; $h <= $shopEnd; $h++) {
                                    foreach ([0, 15, 30, 45] as $m) {
                                        if ($h === $shopEnd && $m > 0) break;
                                        $key = sprintf('%02d:%02d', $h, $m);
                                        if ($h >= $empStart && $h < $empEnd && isset($slotCapacity[$key]) && $slotCapacity[$key] > 0) {
                                            $slotCapacity[$key]--;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (\Throwable $capErr) {
                    error_log("available-times.php: daily capacity check skipped: " . $capErr->getMessage());
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
            foreach ([0, 15, 30, 45] as $m) {
                if ($h === 18 && $m > 0) break;
                $time = sprintf('%02d:%02d', $h, $m);
                $count = $slotCounts[$time] ?? 0;
                $allSlots[$time] = [
                    'booked'    => $count,
                    'capacity'  => 0,
                    'available' => false,
                    'reason'    => 'closed',
                ];
            }
        }

        if (isHtmxRequest()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Vary: HX-Request');
            $lang = sanitize((string) ($_GET['lang'] ?? 'en'), 2);
            $shopClosed = true;
            require __DIR__ . '/../templates/partials/booking-time-slots.php';
            exit;
        }

        jsonSuccess([
            'date'   => $date,
            'closed' => true,
            'slots'  => $allSlots,
        ]);
    }

    // ─── Filter past time slots when booking for today ─────────────
    $isToday = $date === (new DateTime('now', new DateTimeZone('America/Los_Angeles')))->format('Y-m-d');
    $nowMinutes = null;
    if ($isToday) {
        $now = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
        $nowMinutes = (int) $now->format('H') * 60 + (int) $now->format('i');
    }

    $allSlots = [];
    for ($h = $shopStart; $h <= $shopEnd; $h++) {
        foreach ([0, 15, 30, 45] as $m) {
            if ($h === $shopEnd && $m > 0) break;
            $time     = sprintf('%02d:%02d', $h, $m);
            $count    = $slotCounts[$time] ?? 0;
            $capacity = $useSchedules ? ($slotCapacity[$time] ?? 0) : $legacyCapacity;
            $available = $capacity > 0 && $count < $capacity;

            // Block slots in the past (with 15-min buffer so nobody books "right now")
            if ($isToday && ($h * 60 + $m) <= $nowMinutes + 15) {
                $available = false;
                $allSlots[$time] = [
                    'booked'    => $count,
                    'capacity'  => $capacity,
                    'available' => false,
                    'reason'    => 'past',
                ];
                continue;
            }

            $allSlots[$time] = [
                'booked'    => $count,
                'capacity'  => $capacity,
                'available' => $available,
                'reason'    => $available ? null : ($capacity === 0 && !$useSchedules ? null : 'full'),
            ];
        }
    }

    if (isHtmxRequest()) {
        header('Content-Type: text/html; charset=utf-8');
        header('Vary: HX-Request');
        $lang = sanitize((string) ($_GET['lang'] ?? 'en'), 2);
        $shopClosed = false;
        require __DIR__ . '/../templates/partials/booking-time-slots.php';
        exit;
    }

    $responseData = [
        'date'         => $date,
        'max_per_slot' => $useSchedules ? max(array_values($slotCapacity) ?: [0]) : $legacyCapacity,
        'slots'        => $allSlots,
    ];

    // Cache the result (60s TTL)
    if (function_exists('cacheSet') && isset($cacheKey)) {
        cacheSet($cacheKey, $responseData, 60, 'oregon_tires');
        if (!headers_sent()) { header('X-Cache: MISS'); }
    }

    jsonSuccess($responseData);

} catch (\Throwable $e) {
    error_log("Oregon Tires available-times.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
