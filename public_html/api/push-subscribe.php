<?php
/**
 * Oregon Tires — Push Subscription CRUD
 * POST   /api/push-subscribe.php — create/reactivate subscription
 * PUT    /api/push-subscribe.php — update notification preferences
 * DELETE /api/push-subscribe.php — unsubscribe (soft delete)
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/push.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    checkRateLimit('push_subscribe', 10, 3600);

    if ($method === 'POST') {
        $data = getJsonBody();

        $endpoint = trim((string) ($data['endpoint'] ?? ''));
        $p256dh = trim((string) ($data['keys']['p256dh'] ?? ''));
        $auth = trim((string) ($data['keys']['auth'] ?? ''));
        $language = in_array($data['language'] ?? '', ['english', 'spanish'], true)
            ? $data['language']
            : 'english';

        if (empty($endpoint) || empty($p256dh) || empty($auth)) {
            jsonError('Missing subscription data (endpoint, keys.p256dh, keys.auth)');
        }

        // Link to logged-in member if available
        startSecureSession();
        $memberId = !empty($_SESSION['member_id']) ? (int) $_SESSION['member_id'] : null;

        // Try to find customer by member
        $customerId = null;
        if ($memberId) {
            $db = getDB();
            $cStmt = $db->prepare("SELECT id FROM oretir_customers WHERE member_id = ? LIMIT 1");
            $cStmt->execute([$memberId]);
            $customerId = $cStmt->fetchColumn() ?: null;
            if ($customerId) $customerId = (int) $customerId;
        }

        $userAgent = sanitize((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 255);

        $subscriptionId = savePushSubscription(
            $endpoint, $p256dh, $auth,
            $customerId, $memberId, $language, $userAgent
        );

        jsonSuccess(['subscription_id' => $subscriptionId]);

    } elseif ($method === 'PUT') {
        $data = getJsonBody();

        $subscriptionId = (int) ($data['subscription_id'] ?? 0);
        if ($subscriptionId <= 0) {
            jsonError('Missing subscription_id');
        }

        $prefs = [];
        foreach (['notify_booking_confirm', 'notify_reminders', 'notify_status_updates', 'notify_promotions'] as $key) {
            if (isset($data[$key])) {
                $prefs[$key] = (bool) $data[$key];
            }
        }

        if (empty($prefs)) {
            jsonError('No preferences to update');
        }

        updatePushPreferences($subscriptionId, $prefs);
        jsonSuccess(['updated' => true]);

    } elseif ($method === 'DELETE') {
        $data = getJsonBody();
        $endpoint = trim((string) ($data['endpoint'] ?? ''));

        if (empty($endpoint)) {
            jsonError('Missing endpoint');
        }

        removePushSubscription($endpoint);
        jsonSuccess(['unsubscribed' => true]);

    } else {
        jsonError('Method not allowed', 405);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires push-subscribe.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
