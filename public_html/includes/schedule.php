<?php
/**
 * Oregon Tires — Employee Schedule Helpers
 */

declare(strict_types=1);

/**
 * Check if an employee is available on a given date and time.
 *
 * @return array{available: bool, reason: ?string, hours: ?string}
 */
function isEmployeeAvailable(PDO $db, int $employeeId, string $date, string $time): array
{
    // 1. Check employee is active
    $stmt = $db->prepare('SELECT id, name, is_active FROM oretir_employees WHERE id = ?');
    $stmt->execute([$employeeId]);
    $emp = $stmt->fetch();

    if (!$emp) {
        return ['available' => false, 'reason' => 'Employee not found', 'hours' => null];
    }
    if (!$emp['is_active']) {
        return ['available' => false, 'reason' => $emp['name'] . ' is inactive', 'hours' => null];
    }

    $dayOfWeek = (int) (new DateTime($date))->format('w'); // 0=Sun … 6=Sat

    // 2. Check shop-wide closure override
    $stmt = $db->prepare(
        "SELECT id FROM oretir_schedule_overrides
         WHERE override_date = ? AND employee_id IS NULL AND is_closed = 1
         LIMIT 1"
    );
    $stmt->execute([$date]);
    if ($stmt->fetch()) {
        return ['available' => false, 'reason' => 'Shop is closed on ' . $date, 'hours' => null];
    }

    // 3. Check employee-specific override for this date
    $stmt = $db->prepare(
        "SELECT is_closed, start_time, end_time FROM oretir_schedule_overrides
         WHERE override_date = ? AND employee_id = ?
         LIMIT 1"
    );
    $stmt->execute([$date, $employeeId]);
    $empOverride = $stmt->fetch();

    if ($empOverride) {
        if ((int) $empOverride['is_closed']) {
            return ['available' => false, 'reason' => $emp['name'] . ' is off on ' . $date, 'hours' => null];
        }
        // Employee has special hours this day
        $startTime = $empOverride['start_time'];
        $endTime = $empOverride['end_time'];
        $hours = substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5);

        $timeHour = (int) substr($time, 0, 2);
        $empStart = (int) substr($startTime, 0, 2);
        $empEnd = (int) substr($endTime, 0, 2);

        if ($timeHour < $empStart || $timeHour >= $empEnd) {
            return ['available' => false, 'reason' => $emp['name'] . ' works ' . $hours . ' on ' . $date, 'hours' => $hours];
        }
        return ['available' => true, 'reason' => null, 'hours' => $hours];
    }

    // 4. Check regular schedule for this day of week
    $stmt = $db->prepare(
        "SELECT start_time, end_time, is_available FROM oretir_schedules
         WHERE employee_id = ? AND day_of_week = ?
         LIMIT 1"
    );
    $stmt->execute([$employeeId, $dayOfWeek]);
    $sched = $stmt->fetch();

    if (!$sched || !$sched['is_available']) {
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return ['available' => false, 'reason' => $emp['name'] . ' does not work on ' . $dayNames[$dayOfWeek] . 's', 'hours' => null];
    }

    $startTime = $sched['start_time'];
    $endTime = $sched['end_time'];
    $hours = substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5);

    $timeHour = (int) substr($time, 0, 2);
    $empStart = (int) substr($startTime, 0, 2);
    $empEnd = (int) substr($endTime, 0, 2);

    if ($timeHour < $empStart || $timeHour >= $empEnd) {
        return ['available' => false, 'reason' => $emp['name'] . ' works ' . $hours . ' on this day', 'hours' => $hours];
    }

    return ['available' => true, 'reason' => null, 'hours' => $hours];
}

/**
 * Generate a human-readable task summary from appointment data.
 */
function generateTaskSummary(string|array $service, ?string $vehicleInfo, ?string $notes): string
{
    if (is_array($service)) {
        $summary = implode(' + ', array_map(fn(string $s) => ucwords(str_replace('-', ' ', $s)), $service));
    } else {
        $summary = ucwords(str_replace('-', ' ', $service));
    }

    if ($vehicleInfo) {
        $summary .= ' — ' . trim($vehicleInfo);
    }

    if ($notes) {
        $truncated = mb_strlen($notes) > 200 ? mb_substr($notes, 0, 197) . '...' : $notes;
        $summary .= ' | ' . $truncated;
    }

    return mb_substr($summary, 0, 500);
}
