<?php
/**
 * Oregon Tires — Push Notification Utility
 * Manages Web Push subscriptions, notification queuing, and sending.
 * Follows patterns from includes/mail.php.
 */

declare(strict_types=1);

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

/**
 * Generate VAPID key pair and store in site_settings.
 */
function generateVapidKeys(): array
{
    $keys = VAPID::createVapidKeys();
    $db = getDB();

    $db->prepare(
        "UPDATE oretir_site_settings SET value_en = ?, updated_at = NOW() WHERE setting_key = 'vapid_public_key'"
    )->execute([$keys['publicKey']]);

    $db->prepare(
        "UPDATE oretir_site_settings SET value_en = ?, updated_at = NOW() WHERE setting_key = 'vapid_private_key'"
    )->execute([$keys['privateKey']]);

    return $keys;
}

/**
 * Get VAPID public key from site_settings (cached).
 */
function getVapidPublicKey(): string
{
    static $key = null;
    if ($key !== null) return $key;

    $db = getDB();
    $stmt = $db->prepare("SELECT value_en FROM oretir_site_settings WHERE setting_key = 'vapid_public_key' LIMIT 1");
    $stmt->execute();
    $key = (string) $stmt->fetchColumn();
    return $key;
}

/**
 * Get VAPID private key from site_settings.
 */
function getVapidPrivateKey(): string
{
    static $key = null;
    if ($key !== null) return $key;

    $db = getDB();
    $stmt = $db->prepare("SELECT value_en FROM oretir_site_settings WHERE setting_key = 'vapid_private_key' LIMIT 1");
    $stmt->execute();
    $key = (string) $stmt->fetchColumn();
    return $key;
}

/**
 * Save or update a push subscription.
 */
function savePushSubscription(
    string $endpoint,
    string $p256dh,
    string $auth,
    ?int $customerId,
    ?int $memberId,
    string $language,
    ?string $userAgent
): int {
    $db = getDB();

    // Upsert: reactivate if endpoint exists
    $stmt = $db->prepare(
        "INSERT INTO oretir_push_subscriptions
            (endpoint, p256dh_key, auth_key, customer_id, member_id, language, user_agent, is_active, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
         ON DUPLICATE KEY UPDATE
            p256dh_key = VALUES(p256dh_key),
            auth_key = VALUES(auth_key),
            customer_id = COALESCE(VALUES(customer_id), customer_id),
            member_id = COALESCE(VALUES(member_id), member_id),
            language = VALUES(language),
            user_agent = VALUES(user_agent),
            is_active = 1,
            updated_at = NOW()"
    );
    $stmt->execute([$endpoint, $p256dh, $auth, $customerId, $memberId, $language, $userAgent]);

    // Return subscription ID
    $idStmt = $db->prepare("SELECT id FROM oretir_push_subscriptions WHERE LEFT(endpoint, 500) = LEFT(?, 500) LIMIT 1");
    $idStmt->execute([$endpoint]);
    return (int) $idStmt->fetchColumn();
}

/**
 * Soft-delete a push subscription by endpoint.
 */
function removePushSubscription(string $endpoint): void
{
    $db = getDB();
    $db->prepare(
        "UPDATE oretir_push_subscriptions SET is_active = 0, updated_at = NOW() WHERE endpoint = ?"
    )->execute([$endpoint]);
}

/**
 * Update notification preferences for a subscription.
 */
function updatePushPreferences(int $subscriptionId, array $prefs): void
{
    $db = getDB();
    $allowed = ['notify_booking_confirm', 'notify_reminders', 'notify_status_updates', 'notify_promotions'];
    $sets = [];
    $params = [];

    foreach ($allowed as $col) {
        if (isset($prefs[$col])) {
            $sets[] = "{$col} = ?";
            $params[] = (int) (bool) $prefs[$col];
        }
    }

    if (empty($sets)) return;

    $sets[] = "updated_at = NOW()";
    $params[] = $subscriptionId;
    $db->prepare("UPDATE oretir_push_subscriptions SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
}

/**
 * Queue a notification for sending.
 */
function queueNotification(
    string $type,
    string $titleEn,
    string $titleEs,
    string $bodyEn,
    string $bodyEs,
    ?string $url = null,
    string $targetType = 'subscription',
    ?int $targetId = null,
    ?int $subscriptionId = null,
    ?string $scheduledAt = null,
    ?array $extraData = null
): int {
    $db = getDB();
    $stmt = $db->prepare(
        "INSERT INTO oretir_notification_queue
            (subscription_id, target_type, target_id, notification_type,
             title_en, title_es, body_en, body_es, url, data_json,
             status, scheduled_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())"
    );
    $stmt->execute([
        $subscriptionId,
        $targetType,
        $targetId,
        $type,
        $titleEn,
        $titleEs,
        $bodyEn,
        $bodyEs,
        $url,
        $extraData ? json_encode($extraData) : null,
        $scheduledAt,
    ]);
    return (int) $db->lastInsertId();
}

/**
 * Queue a notification targeting a customer (all their active subscriptions).
 */
function queueNotificationForCustomer(
    int $customerId,
    string $type,
    string $titleEn,
    string $titleEs,
    string $bodyEn,
    string $bodyEs,
    ?string $url = null
): void {
    queueNotification($type, $titleEn, $titleEs, $bodyEn, $bodyEs, $url, 'customer', $customerId);
}

/**
 * Queue a notification targeting a member.
 */
function queueNotificationForMember(
    int $memberId,
    string $type,
    string $titleEn,
    string $titleEs,
    string $bodyEn,
    string $bodyEs,
    ?string $url = null
): void {
    queueNotification($type, $titleEn, $titleEs, $bodyEn, $bodyEs, $url, 'member', $memberId);
}

/**
 * Send a push notification to a single subscription.
 * Returns true on success, false on failure. Auto-deactivates on 410 Gone.
 */
function sendPushToSubscription(array $sub, string $title, string $body, ?string $url, string $icon, string $badge, ?array $data = null): bool
{
    $publicKey = getVapidPublicKey();
    $privateKey = getVapidPrivateKey();

    if (empty($publicKey) || empty($privateKey)) {
        error_log('Oregon Tires push: VAPID keys not configured');
        return false;
    }

    $vapidSubject = $_ENV['VAPID_SUBJECT'] ?? 'mailto:info@oregon.tires';

    $auth = [
        'VAPID' => [
            'subject' => $vapidSubject,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
        ],
    ];

    $webPush = new WebPush($auth);

    $payload = json_encode([
        'title' => $title,
        'body' => $body,
        'url' => $url ?? '/',
        'icon' => $icon,
        'badge' => $badge,
        'data' => $data,
        'tag' => $data['tag'] ?? 'oregon-tires',
    ]);

    $subscription = Subscription::create([
        'endpoint' => $sub['endpoint'],
        'publicKey' => $sub['p256dh_key'],
        'authToken' => $sub['auth_key'],
    ]);

    $report = $webPush->sendOneNotification($subscription, $payload);

    if ($report->isSuccess()) {
        return true;
    }

    // Auto-deactivate on 410 Gone (browser unsubscribed)
    if ($report->getResponse() && $report->getResponse()->getStatusCode() === 410) {
        $db = getDB();
        $db->prepare("UPDATE oretir_push_subscriptions SET is_active = 0, updated_at = NOW() WHERE id = ?")
           ->execute([$sub['id']]);
    }

    error_log('Oregon Tires push failed: ' . $report->getReason() . ' [endpoint: ' . substr($sub['endpoint'], 0, 80) . ']');
    return false;
}

/**
 * Process the notification queue. Called by cli/send-push-notifications.php.
 */
function processNotificationQueue(int $batchSize = 50): array
{
    $db = getDB();
    $stats = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

    // Fetch pending notifications that are due
    $stmt = $db->prepare(
        "SELECT * FROM oretir_notification_queue
         WHERE status = 'pending'
           AND attempts < max_attempts
           AND (scheduled_at IS NULL OR scheduled_at <= NOW())
         ORDER BY created_at ASC
         LIMIT ?"
    );
    $stmt->execute([$batchSize]);
    $queue = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($queue as $notif) {
        $subscriptions = resolveNotificationTargets($notif);

        if (empty($subscriptions)) {
            $db->prepare("UPDATE oretir_notification_queue SET status = 'failed', error_message = 'No active subscriptions', attempts = attempts + 1 WHERE id = ?")
               ->execute([$notif['id']]);
            $stats['skipped']++;
            continue;
        }

        $allSent = true;
        foreach ($subscriptions as $sub) {
            $lang = $sub['language'] ?? 'english';
            $title = $lang === 'spanish' ? $notif['title_es'] : $notif['title_en'];
            $body = $lang === 'spanish' ? $notif['body_es'] : $notif['body_en'];

            // Use English if translation is empty
            if (empty($title)) $title = $notif['title_en'];
            if (empty($body)) $body = $notif['body_en'];

            // Check preference
            if (!shouldSendToSubscription($sub, $notif['notification_type'])) {
                continue;
            }

            $extraData = $notif['data_json'] ? json_decode($notif['data_json'], true) : [];
            $extraData['type'] = $notif['notification_type'];
            $extraData['tag'] = $notif['notification_type'] . '-' . $notif['id'];

            $sent = sendPushToSubscription(
                $sub,
                $title,
                $body,
                $notif['url'],
                $notif['icon'],
                $notif['badge'],
                $extraData
            );

            if (!$sent) $allSent = false;
        }

        if ($allSent) {
            $db->prepare("UPDATE oretir_notification_queue SET status = 'sent', sent_at = NOW(), attempts = attempts + 1 WHERE id = ?")
               ->execute([$notif['id']]);
            $stats['sent']++;
        } else {
            $attempts = (int) $notif['attempts'] + 1;
            $newStatus = $attempts >= (int) $notif['max_attempts'] ? 'failed' : 'pending';
            $db->prepare("UPDATE oretir_notification_queue SET status = ?, attempts = ?, error_message = 'Partial send failure' WHERE id = ?")
               ->execute([$newStatus, $attempts, $notif['id']]);
            $stats['failed']++;
        }
    }

    return $stats;
}

/**
 * Resolve which subscriptions a notification should be sent to.
 */
function resolveNotificationTargets(array $notif): array
{
    $db = getDB();

    switch ($notif['target_type']) {
        case 'subscription':
            if (!$notif['subscription_id']) return [];
            $stmt = $db->prepare("SELECT * FROM oretir_push_subscriptions WHERE id = ? AND is_active = 1");
            $stmt->execute([$notif['subscription_id']]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        case 'customer':
            $stmt = $db->prepare("SELECT * FROM oretir_push_subscriptions WHERE customer_id = ? AND is_active = 1");
            $stmt->execute([$notif['target_id']]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        case 'member':
            $stmt = $db->prepare("SELECT * FROM oretir_push_subscriptions WHERE member_id = ? AND is_active = 1");
            $stmt->execute([$notif['target_id']]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        case 'broadcast':
            $stmt = $db->prepare("SELECT * FROM oretir_push_subscriptions WHERE is_active = 1");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        default:
            return [];
    }
}

/**
 * Check if a notification type should be sent to a subscription based on preferences.
 */
function shouldSendToSubscription(array $sub, string $notificationType): bool
{
    $map = [
        'booking_confirmed' => 'notify_booking_confirm',
        'appointment_reminder' => 'notify_reminders',
        'status_update' => 'notify_status_updates',
        'vehicle_ready' => 'notify_status_updates',
        'estimate_ready' => 'notify_status_updates',
        'promotion' => 'notify_promotions',
    ];

    $prefCol = $map[$notificationType] ?? null;
    if ($prefCol === null) return true; // unknown type = always send
    return !empty($sub[$prefCol]);
}

/**
 * Find active subscriptions by customer email.
 */
function findPushSubscriptionsByEmail(string $email): array
{
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT ps.* FROM oretir_push_subscriptions ps
         JOIN oretir_customers c ON ps.customer_id = c.id
         WHERE c.email = ? AND ps.is_active = 1"
    );
    $stmt->execute([$email]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
