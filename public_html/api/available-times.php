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
    $maxPerSlot = 2;

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

    // All possible time slots (07:00 - 18:00 on the hour)
    $allSlots = [];
    for ($h = 7; $h <= 18; $h++) {
        $time = sprintf('%02d:00', $h);
        $count = $slotCounts[$time] ?? 0;
        $allSlots[$time] = [
            'booked' => $count,
            'available' => $count < $maxPerSlot,
        ];
    }

    jsonSuccess([
        'date' => $date,
        'max_per_slot' => $maxPerSlot,
        'slots' => $allSlots,
    ]);

} catch (\Throwable $e) {
    error_log("Oregon Tires available-times.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
