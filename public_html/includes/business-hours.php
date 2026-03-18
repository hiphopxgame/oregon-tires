<?php
declare(strict_types=1);

/**
 * Oregon Tires — Business Hours Helper
 * Provides DB-backed business hours and holiday lookups with graceful fallback.
 */

/**
 * Get business hours for a specific date.
 * Returns ['is_open' => bool, 'open_time' => 'HH:MM', 'close_time' => 'HH:MM', 'max_concurrent' => int]
 */
function getBusinessHoursForDate(PDO $pdo, string $date): array
{
    // Default fallback
    $defaults = [
        'is_open' => true,
        'open_time' => '07:00',
        'close_time' => '18:00',
        'max_concurrent' => 2,
        'source' => 'default',
    ];

    try {
        $dayOfWeek = (int) (new DateTime($date))->format('w');

        // Check holiday first
        if (isHoliday($pdo, $date)) {
            return [
                'is_open' => false,
                'open_time' => '07:00',
                'close_time' => '18:00',
                'max_concurrent' => 0,
                'source' => 'holiday',
                'holiday' => getHolidayName($pdo, $date),
            ];
        }

        // Look up configured hours for this day of week
        $stmt = $pdo->prepare(
            'SELECT is_open, open_time, close_time, max_concurrent
             FROM oretir_business_hours WHERE day_of_week = ? LIMIT 1'
        );
        $stmt->execute([$dayOfWeek]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'is_open' => (bool) $row['is_open'],
                'open_time' => substr($row['open_time'], 0, 5),
                'close_time' => substr($row['close_time'], 0, 5),
                'max_concurrent' => (int) $row['max_concurrent'],
                'source' => 'configured',
            ];
        }
    } catch (\Throwable $e) {
        error_log("business-hours.php: getBusinessHoursForDate error: " . $e->getMessage());
    }

    return $defaults;
}

/**
 * Check if a given date is a holiday.
 */
function isHoliday(PDO $pdo, string $date): bool
{
    try {
        // Exact date match
        $stmt = $pdo->prepare('SELECT id FROM oretir_holidays WHERE holiday_date = ? LIMIT 1');
        $stmt->execute([$date]);
        if ($stmt->fetch()) {
            return true;
        }

        // Recurring holiday: match month-day regardless of year
        $monthDay = substr($date, 5); // MM-DD
        $stmt = $pdo->prepare(
            "SELECT id FROM oretir_holidays
             WHERE is_recurring = 1 AND DATE_FORMAT(holiday_date, '%m-%d') = ? LIMIT 1"
        );
        $stmt->execute([$monthDay]);
        return (bool) $stmt->fetch();
    } catch (\Throwable $e) {
        error_log("business-hours.php: isHoliday error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get holiday name for a date (returns array with en/es or null).
 */
function getHolidayName(PDO $pdo, string $date): ?array
{
    try {
        $stmt = $pdo->prepare('SELECT name_en, name_es FROM oretir_holidays WHERE holiday_date = ? LIMIT 1');
        $stmt->execute([$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row;

        $monthDay = substr($date, 5);
        $stmt = $pdo->prepare(
            "SELECT name_en, name_es FROM oretir_holidays
             WHERE is_recurring = 1 AND DATE_FORMAT(holiday_date, '%m-%d') = ? LIMIT 1"
        );
        $stmt->execute([$monthDay]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (\Throwable $e) {
        return null;
    }
}

/**
 * Get all holidays for a given year.
 */
function getHolidays(PDO $pdo, int $year): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT id, holiday_date, name_en, name_es, is_recurring, created_at
             FROM oretir_holidays
             WHERE YEAR(holiday_date) = ? OR is_recurring = 1
             ORDER BY DATE_FORMAT(holiday_date, '%m-%d') ASC"
        );
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        error_log("business-hours.php: getHolidays error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all 7 days of business hours config.
 */
function getAllBusinessHours(PDO $pdo): array
{
    try {
        $stmt = $pdo->query(
            'SELECT id, day_of_week, open_time, close_time, is_open, max_concurrent
             FROM oretir_business_hours ORDER BY day_of_week ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        error_log("business-hours.php: getAllBusinessHours error: " . $e->getMessage());
        return [];
    }
}
