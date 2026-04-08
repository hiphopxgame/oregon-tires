<?php
declare(strict_types=1);

/**
 * Commerce Kit — PayPal Webhook Endpoint
 *
 * Receives PayPal webhooks. No session auth — uses PayPal verification.
 * Site bootstrap must load before this to set up $pdo and $paypalProvider.
 */

// Expect $pdo and $paypalProvider to be set by the site wrapper
if (!isset($pdo) || !isset($paypalProvider)) {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook not configured']);
    exit;
}

header('Content-Type: application/json');

$rawBody = file_get_contents('php://input');
$headers = getallheaders();

try {
    $result = $paypalProvider->handleWebhook($rawBody, $headers);
    echo json_encode(['success' => true, 'result' => $result]);
} catch (\Throwable $e) {
    error_log('[Commerce PayPal Webhook] Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Webhook processing failed']);
}
