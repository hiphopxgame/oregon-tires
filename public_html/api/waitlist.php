<?php
/**
 * Oregon Tires — Public Waitlist Endpoint
 * POST /api/waitlist.php — join the walk-in queue
 * GET  /api/waitlist.php?id=N&email=EMAIL — check position
 *
 * Rate limited: 10/hr per IP
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/waitlist.php';

try {
    requireMethod('GET', 'POST');
    $db = getDB();

    // ─── GET: Check position ────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        checkRateLimit('waitlist_check', 30, 3600);

        $id = (int) ($_GET['id'] ?? 0);
        $email = sanitize((string) ($_GET['email'] ?? ''), 254);

        if ($id <= 0 || $email === '') {
            jsonError('Both id and email are required.');
        }

        $stmt = $db->prepare(
            "SELECT id, position, status, estimated_wait_minutes, created_at
             FROM oretir_waitlist
             WHERE id = ? AND email = ?
             LIMIT 1"
        );
        $stmt->execute([$id, $email]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            jsonError('Waitlist entry not found.', 404);
        }

        // Count how many people are ahead
        $aheadStmt = $db->prepare(
            "SELECT COUNT(*) FROM oretir_waitlist
             WHERE position < ? AND status IN ('waiting', 'notified')
               AND DATE(created_at) = CURDATE()"
        );
        $aheadStmt->execute([$entry['position']]);
        $ahead = (int) $aheadStmt->fetchColumn();

        // Recalculate current estimated wait
        $currentWait = estimateWaitTime($db);

        jsonSuccess([
            'id'                    => (int) $entry['id'],
            'position'              => (int) $entry['position'],
            'people_ahead'          => $ahead,
            'status'                => $entry['status'],
            'estimated_wait_minutes' => $currentWait,
            'created_at'            => $entry['created_at'],
        ]);
    }

    // ─── POST: Join waitlist ────────────────────────────────────────────
    checkRateLimit('waitlist_join', 10, 3600);

    $data = getJsonBody();

    // Validate required fields
    $missing = requireFields($data, ['first_name']);
    if (!empty($missing)) {
        jsonError('First name is required.');
    }

    // Sanitize inputs
    $firstName   = sanitize((string) $data['first_name'], 100);
    $lastName    = sanitize((string) ($data['last_name'] ?? ''), 100);
    $email       = sanitize((string) ($data['email'] ?? ''), 254);
    $phone       = sanitize((string) ($data['phone'] ?? ''), 30);
    $service     = sanitize((string) ($data['service'] ?? ''), 100);
    $vehicleInfo = sanitize((string) ($data['vehicle_info'] ?? ''), 200);
    $language    = sanitize((string) ($data['language'] ?? 'english'), 20);

    if (mb_strlen($firstName) < 1) {
        jsonError('First name is required.');
    }

    if ($email !== '' && !isValidEmail($email)) {
        jsonError('Please provide a valid email address.');
    }

    if ($phone !== '' && !isValidPhone($phone)) {
        jsonError('Please provide a valid phone number.');
    }

    if (!in_array($language, ['english', 'spanish'], true)) {
        $language = 'english';
    }

    // Try to find existing customer
    $customerId = null;
    if ($email !== '') {
        $custStmt = $db->prepare('SELECT id FROM oretir_customers WHERE email = ? LIMIT 1');
        $custStmt->execute([$email]);
        $custId = $custStmt->fetchColumn();
        if ($custId) {
            $customerId = (int) $custId;
        }
    }

    // Get position and estimated wait
    $position = getNextPosition($db);
    $estimatedWait = estimateWaitTime($db);

    // Insert into waitlist
    $stmt = $db->prepare(
        'INSERT INTO oretir_waitlist
            (customer_id, first_name, last_name, email, phone, service, vehicle_info, position, estimated_wait_minutes, status, language)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $customerId,
        $firstName,
        $lastName,
        $email ?: null,
        $phone ?: null,
        $service,
        $vehicleInfo ?: null,
        $position,
        $estimatedWait,
        'waiting',
        $language,
    ]);

    $entryId = (int) $db->lastInsertId();

    jsonSuccess([
        'id'                     => $entryId,
        'position'               => $position,
        'estimated_wait_minutes' => $estimatedWait,
    ]);

} catch (\Throwable $e) {
    error_log('waitlist.php error: ' . $e->getMessage());
    jsonError('Server error', 500);
}
