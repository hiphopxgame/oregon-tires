<?php
/**
 * Oregon Tires — Admin Push Broadcast
 * POST /api/admin/push-broadcast.php
 * Sends a promotional push notification to all opted-in subscribers.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/push.php';

try {
    requireMethod('POST');
    $staff = requirePermission('marketing');

    verifyCsrf();
    $staffKey = $staff['type'] === 'admin' ? ($_SESSION['admin_id'] ?? $staff['id']) : ($_SESSION['employee_id'] ?? $staff['id']);
    checkRateLimit('push_broadcast_' . $staffKey, 5, 86400); // 5 per day

    $data = getJsonBody();

    $titleEn = sanitize((string) ($data['title_en'] ?? ''), 255);
    $titleEs = sanitize((string) ($data['title_es'] ?? ''), 255);
    $bodyEn  = sanitize((string) ($data['body_en'] ?? ''), 1000);
    $bodyEs  = sanitize((string) ($data['body_es'] ?? ''), 1000);
    $url     = sanitize((string) ($data['url'] ?? '/'), 500);

    if (empty($titleEn) || empty($bodyEn)) {
        jsonError('Title and body (English) are required');
    }

    // Use English as fallback for Spanish
    if (empty($titleEs)) $titleEs = $titleEn;
    if (empty($bodyEs)) $bodyEs = $bodyEn;

    // Queue as broadcast
    $notifId = queueNotification(
        'promotion',
        $titleEn, $titleEs,
        $bodyEn, $bodyEs,
        $url,
        'broadcast',
        null, null, null,
        null
    );

    jsonSuccess([
        'notification_id' => $notifId,
        'message' => 'Broadcast queued for all promotion-opted subscribers',
    ]);

} catch (\Throwable $e) {
    error_log("Oregon Tires push-broadcast.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
