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
    $staff = requireStaff();
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

        // Fetch existing entry
        $stmt = $db->prepare('SELECT * FROM oretir_waitlist WHERE id = ? LIMIT 1');
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

        // If notifying, send email
        if ($newStatus === 'notified' && !empty($entry['email'])) {
            try {
                require_once __DIR__ . '/../../includes/mail.php';

                $language = $entry['language'] === 'spanish' ? 'es' : 'both';
                $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=Oregon+Tires+Auto+Care+Portland+OR';
                $waitTime = estimateWaitTime($db);

                sendBrandedTemplateEmail(
                    $entry['email'],
                    'waitlist_ready',
                    [
                        'name'      => htmlspecialchars($entry['first_name'], ENT_QUOTES, 'UTF-8'),
                        'service'   => htmlspecialchars($entry['service'] ?: 'auto care', ENT_QUOTES, 'UTF-8'),
                        'wait_time' => (string) $waitTime,
                    ],
                    $language,
                    $directionsUrl
                );

                logEmail('waitlist_notify', "Waitlist notification sent to {$entry['email']} (entry #{$id})");
            } catch (\Throwable $e) {
                error_log('admin/waitlist.php notification error: ' . $e->getMessage());
            }
        }

        // If completed or cancelled, try to advance the queue
        if (in_array($newStatus, ['completed', 'cancelled'], true)) {
            advanceQueue($db);
        }

        jsonSuccess(['id' => $id, 'status' => $newStatus]);
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
