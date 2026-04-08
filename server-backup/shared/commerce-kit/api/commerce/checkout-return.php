<?php
declare(strict_types=1);

/**
 * Commerce Kit — Checkout Return Endpoint
 *
 * GET: Handles return redirects after PayPal/Stripe payment completion.
 * Confirms payment with the appropriate provider and returns order status.
 *
 * Site wrapper must set: $pdo, $providers (array keyed by provider name).
 */

// Expect $pdo and $providers to be set by the site wrapper
if (!isset($pdo) || !isset($providers)) {
    http_response_code(500);
    echo json_encode(['error' => 'Checkout return not configured']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $orderRef = $_GET['order_ref'] ?? '';

    if (empty($orderRef)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'order_ref is required']);
        exit;
    }

    // Look up the order to determine the provider
    $order = CommerceOrder::get($pdo, $orderRef);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Determine provider: explicit query param takes priority, then order's payment_provider
    $providerName = $_GET['provider'] ?? ($order['payment_provider'] ?? '');

    if (empty($providerName) || !isset($providers[$providerName])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unable to determine payment provider']);
        exit;
    }

    // If already completed, return success without re-confirming
    if ($order['status'] === 'completed') {
        echo json_encode([
            'success'   => true,
            'status'    => 'completed',
            'order_ref' => $orderRef,
            'total'     => $order['total'],
            'message'   => 'Payment already confirmed',
        ]);
        exit;
    }

    /** @var CommerceProvider $provider */
    $provider = $providers[$providerName];

    // Build confirmation data from query params
    $confirmData = [];
    if (!empty($_GET['token'])) {
        $confirmData['paypal_order_id'] = $_GET['token'];
    }
    if (!empty($_GET['session_id'])) {
        $confirmData['session_id'] = $_GET['session_id'];
    }

    $result = $provider->confirm($orderRef, $confirmData);

    if (!($result['success'] ?? false)) {
        http_response_code(400);
    }

    // Include order_ref and total in response for frontend convenience
    $result['order_ref'] = $orderRef;
    $result['total'] = $order['total'];

    echo json_encode($result);

} catch (\Throwable $e) {
    error_log("[Commerce API] checkout-return.php error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
