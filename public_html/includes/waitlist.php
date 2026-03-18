<?php
/**
 * Oregon Tires — Waitlist & Walk-In Queue Helpers
 *
 * Functions for managing the walk-in customer queue.
 */

declare(strict_types=1);

/**
 * Estimate the current wait time in minutes for a new walk-in.
 * Calculation: (active entries * avg_service_minutes) / max_bays
 */
function estimateWaitTime(PDO $db): int
{
    // Count active entries (waiting, notified, checked_in, serving)
    $countStmt = $db->query(
        "SELECT COUNT(*) FROM oretir_waitlist
         WHERE status IN ('waiting', 'notified', 'checked_in', 'serving')
           AND DATE(created_at) = CURDATE()"
    );
    $activeCount = (int) $countStmt->fetchColumn();

    // Load settings
    $settingsStmt = $db->query(
        "SELECT setting_key, value_en FROM oretir_site_settings
         WHERE setting_key IN ('waitlist_avg_service_minutes', 'waitlist_max_bays')"
    );
    $settings = [];
    while ($row = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = (int) $row['value_en'];
    }

    $avgMinutes = $settings['waitlist_avg_service_minutes'] ?? 60;
    $maxBays = max(1, $settings['waitlist_max_bays'] ?? 4);

    if ($activeCount === 0) {
        return 0;
    }

    return (int) ceil(($activeCount * $avgMinutes) / $maxBays);
}

/**
 * Get the next position number for today's waitlist.
 */
function getNextPosition(PDO $db): int
{
    $stmt = $db->query(
        "SELECT MAX(position) FROM oretir_waitlist
         WHERE DATE(created_at) = CURDATE()
           AND status NOT IN ('cancelled', 'expired')"
    );
    $maxPosition = $stmt->fetchColumn();

    return $maxPosition ? (int) $maxPosition + 1 : 1;
}

/**
 * Advance the queue: find the next 'waiting' entry and notify if estimated wait <= 15 min.
 * Called after a status change (e.g., when an entry is completed or cancelled).
 */
function advanceQueue(PDO $db): void
{
    // Find next waiting entry by position
    $stmt = $db->query(
        "SELECT id, email, first_name, service, language
         FROM oretir_waitlist
         WHERE status = 'waiting'
           AND DATE(created_at) = CURDATE()
         ORDER BY position ASC
         LIMIT 1"
    );
    $next = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$next) {
        return;
    }

    $waitTime = estimateWaitTime($db);

    // Only notify if wait is 15 minutes or less
    if ($waitTime > 15) {
        return;
    }

    // Update status to notified
    $db->prepare(
        "UPDATE oretir_waitlist SET status = 'notified', notified_at = NOW() WHERE id = ?"
    )->execute([$next['id']]);

    // Send notification email if email is available
    if (!empty($next['email'])) {
        try {
            require_once __DIR__ . '/mail.php';

            $language = $next['language'] === 'spanish' ? 'spanish' : 'english';
            $name = htmlspecialchars($next['first_name'], ENT_QUOTES, 'UTF-8');
            $service = htmlspecialchars($next['service'] ?: 'auto care', ENT_QUOTES, 'UTF-8');

            $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=Oregon+Tires+Auto+Care+Portland+OR';

            sendBrandedTemplateEmail(
                $next['email'],
                'waitlist_ready',
                [
                    'name'      => $name,
                    'service'   => $service,
                    'wait_time' => (string) $waitTime,
                ],
                $language === 'spanish' ? 'es' : 'both',
                $directionsUrl
            );

            logEmail('waitlist_notify', "Waitlist notification sent to {$next['email']} (entry #{$next['id']})");
        } catch (\Throwable $e) {
            error_log('advanceQueue email error: ' . $e->getMessage());
        }
    }
}
