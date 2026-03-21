<?php
/**
 * Oregon Tires — Admin Waitlist Management
 * GET    /api/admin/waitlist.php       — list active waitlist entries
 * PUT    /api/admin/waitlist.php       — update entry status
 * DELETE /api/admin/waitlist.php?id=N  — remove from queue
 *
 * Requires admin/staff session + CSRF for mutations.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/waitlist.php';

try {
    $staff = requirePermission('shop_ops');
    requireMethod('GET', 'PUT', 'DELETE');

    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List active waitlist entries ───────────────────────────────
    if ($method === 'GET') {
        $stmt = $db->query(
            "SELECT w.*, c.email AS customer_email_linked
             FROM oretir_waitlist w
             LEFT JOIN oretir_customers c ON c.id = w.customer_id
             WHERE w.status IN ('waiting', 'notified', 'checked_in', 'serving')
             ORDER BY w.position ASC"
        );
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Include current estimated wait time
        $estimatedWait = estimateWaitTime($db);

        jsonSuccess([
            'entries'                => $entries,
            'estimated_wait_minutes' => $estimatedWait,
            'total_active'           => count($entries),
        ]);
    }

    // ─── PUT: Update entry status ───────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();

        $data = getJsonBody();
        $id = (int) ($data['id'] ?? 0);
        $newStatus = sanitize((string) ($data['status'] ?? ''), 20);

        if ($id <= 0) {
            jsonError('Entry ID is required.');
        }

        $validStatuses = ['waiting', 'notified', 'checked_in', 'serving', 'completed', 'cancelled', 'expired'];
        if (!in_array($newStatus, $validStatuses, true)) {
            jsonError('Invalid status: ' . $newStatus);
        }

        // Fetch existing entry with customer email fallback
        $stmt = $db->prepare(
            'SELECT w.*, c.email AS customer_email_linked
             FROM oretir_waitlist w
             LEFT JOIN oretir_customers c ON c.id = w.customer_id
             WHERE w.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            jsonError('Waitlist entry not found.', 404);
        }

        // Build update fields
        $updates = ['status = ?'];
        $params = [$newStatus];

        if ($newStatus === 'notified' && !$entry['notified_at']) {
            $updates[] = 'notified_at = NOW()';
        }
        if ($newStatus === 'checked_in' && !$entry['checked_in_at']) {
            $updates[] = 'checked_in_at = NOW()';
        }

        $params[] = $id;

        $db->prepare(
            'UPDATE oretir_waitlist SET ' . implode(', ', $updates) . ' WHERE id = ?'
        )->execute($params);

        // If notifying, send email (fallback to linked customer email)
        $notifyEmail = $entry['email'] ?: ($entry['customer_email_linked'] ?? '');
        if ($newStatus === 'notified' && !empty($notifyEmail)) {
            try {
                require_once __DIR__ . '/../../includes/mail.php';

                $language = $entry['language'] === 'spanish' ? 'es' : 'both';
                $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=Oregon+Tires+Auto+Care+Portland+OR';
                $waitTime = estimateWaitTime($db);

                sendBrandedTemplateEmail(
                    $notifyEmail,
                    'waitlist_ready',
                    [
                        'name'      => htmlspecialchars($entry['first_name'], ENT_QUOTES, 'UTF-8'),
                        'service'   => htmlspecialchars($entry['service'] ?: 'auto care', ENT_QUOTES, 'UTF-8'),
                        'wait_time' => (string) $waitTime,
                    ],
                    $language,
                    $directionsUrl
                );

                logEmail('waitlist_notify', "Waitlist notification sent to {$notifyEmail} (entry #{$id})");
            } catch (\Throwable $e) {
                error_log('admin/waitlist.php notification error: ' . $e->getMessage());
            }
        }

        // If "serving", auto-create a visit check-in linked to waitlist
        $visitId = null;
        if ($newStatus === 'serving' && !empty($entry['customer_id'])) {
            try {
                // Use checked_in_at as the real arrival time if available, otherwise NOW()
                $checkInTime = $entry['checked_in_at'] ?? date('Y-m-d H:i:s');
                $db->prepare(
                    'INSERT INTO oretir_visit_log
                       (customer_id, waitlist_id, check_in_at, service_start_at, service, notes)
                     VALUES (?, ?, ?, NOW(), ?, ?)'
                )->execute([
                    (int) $entry['customer_id'],
                    $id,
                    $checkInTime,
                    $entry['service'] ?: null,
                    'Walk-in from waitlist #' . $id,
                ]);
                $visitId = (int) $db->lastInsertId();

                // Calculate wait_minutes (time from check-in to service start)
                $db->prepare(
                    'UPDATE oretir_visit_log
                     SET wait_minutes = TIMESTAMPDIFF(MINUTE, check_in_at, service_start_at)
                     WHERE id = ?'
                )->execute([$visitId]);
            } catch (\Throwable $e) {
                error_log('waitlist: auto check-in error: ' . $e->getMessage());
            }
        }

        // If completed, finalize the linked visit
        if ($newStatus === 'completed') {
            try {
                $db->prepare(
                    'UPDATE oretir_visit_log
                     SET service_end_at = COALESCE(service_end_at, NOW()),
                         check_out_at   = COALESCE(check_out_at, NOW()),
                         service_minutes = TIMESTAMPDIFF(MINUTE, service_start_at, COALESCE(service_end_at, NOW())),
                         total_minutes   = TIMESTAMPDIFF(MINUTE, check_in_at, NOW())
                     WHERE waitlist_id = ? AND check_out_at IS NULL'
                )->execute([$id]);
            } catch (\Throwable $e) {
                error_log('waitlist: visit finalize error: ' . $e->getMessage());
            }
            advanceQueue($db);
        }

        // If cancelled, try to advance the queue
        if ($newStatus === 'cancelled') {
            advanceQueue($db);
        }

        jsonSuccess(['id' => $id, 'status' => $newStatus, 'visit_id' => $visitId]);
    }

    // ─── DELETE: Remove from queue ──────────────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Entry ID is required.');
        }

        $stmt = $db->prepare('SELECT id FROM oretir_waitlist WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            jsonError('Waitlist entry not found.', 404);
        }

        $db->prepare('DELETE FROM oretir_waitlist WHERE id = ?')->execute([$id]);

        // Advance queue after removal
        advanceQueue($db);

        jsonSuccess(['deleted' => $id]);
    }

} catch (\Throwable $e) {
    error_log('admin/waitlist.php error: ' . $e->getMessage());
    jsonError('Server error.', 500);
}
