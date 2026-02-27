<?php
/**
 * Oregon Tires — Care Plan PayPal Webhook
 * POST /api/care-plan-webhook.php
 *
 * Handles PayPal IPN/webhook callbacks for care plan payment events.
 * Activates pending plans on successful payment, cancels on subscription end.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('POST');

    $payload = file_get_contents('php://input');
    $event   = json_decode($payload, true);

    if (!$event) {
        http_response_code(400);
        exit;
    }

    $eventType = $event['event_type'] ?? '';
    $resource  = $event['resource'] ?? [];

    $db = getDB();

    // Log all webhook events for debugging
    error_log('PayPal care-plan webhook: ' . $eventType . ' — ' . substr($payload, 0, 500));

    switch ($eventType) {
        case 'CHECKOUT.ORDER.APPROVED':
        case 'PAYMENT.CAPTURE.COMPLETED':
            $orderId = $resource['id']
                ?? ($resource['supplementary_data']['related_ids']['order_id'] ?? '');
            if ($orderId) {
                $stmt = $db->prepare(
                    'UPDATE oretir_care_plans
                     SET status = ?, period_start = CURDATE(), period_end = DATE_ADD(CURDATE(), INTERVAL 1 MONTH), updated_at = NOW()
                     WHERE paypal_subscription_id = ? AND status = ?'
                );
                $stmt->execute(['active', $orderId, 'pending']);
            }
            break;

        case 'BILLING.SUBSCRIPTION.CANCELLED':
        case 'BILLING.SUBSCRIPTION.SUSPENDED':
            $subId = $resource['id'] ?? '';
            if ($subId) {
                $db->prepare(
                    'UPDATE oretir_care_plans SET status = ?, cancelled_at = NOW(), updated_at = NOW() WHERE paypal_subscription_id = ?'
                )->execute(['cancelled', $subId]);
            }
            break;

        case 'BILLING.SUBSCRIPTION.ACTIVATED':
            $subId = $resource['id'] ?? '';
            if ($subId) {
                $db->prepare(
                    'UPDATE oretir_care_plans
                     SET status = ?, period_start = CURDATE(), period_end = DATE_ADD(CURDATE(), INTERVAL 1 MONTH), updated_at = NOW()
                     WHERE paypal_subscription_id = ? AND status IN (?, ?)'
                )->execute(['active', $subId, 'pending', 'paused']);
            }
            break;

        case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
            $subId = $resource['id'] ?? '';
            if ($subId) {
                $db->prepare(
                    'UPDATE oretir_care_plans SET status = ?, updated_at = NOW() WHERE paypal_subscription_id = ?'
                )->execute(['paused', $subId]);
            }
            break;
    }

    http_response_code(200);
    echo json_encode(['received' => true]);

} catch (\Throwable $e) {
    error_log('care-plan-webhook error: ' . $e->getMessage());
    http_response_code(500);
}
