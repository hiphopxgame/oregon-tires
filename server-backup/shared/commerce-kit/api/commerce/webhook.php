<?php
declare(strict_types=1);

/**
 * Commerce Kit — Stripe Webhook Endpoint
 *
 * Receives Stripe webhooks. No session auth — uses Stripe signature verification.
 * Site bootstrap must load before this to set up $pdo and $stripeProvider.
 */

// Expect $pdo and $stripeProvider to be set by the site wrapper
if (!isset($pdo) || !isset($stripeProvider)) {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook not configured']);
    exit;
}

header('Content-Type: application/json');

$rawBody = file_get_contents('php://input');
$headers = getallheaders();

try {
    $result = $stripeProvider->handleWebhook($rawBody, $headers);
    echo json_encode(['success' => true, 'result' => $result]);
} catch (\Throwable $e) {
    error_log('[Commerce Webhook] Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Webhook processing failed']);
}
