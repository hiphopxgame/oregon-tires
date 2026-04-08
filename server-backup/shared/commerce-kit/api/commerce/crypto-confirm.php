<?php
declare(strict_types=1);

/**
 * Commerce Kit — Crypto Confirmation Endpoint
 *
 * POST: Submit a cryptocurrency transaction hash for verification.
 * Marks the order as processing pending on-chain confirmation.
 *
 * Site wrapper must set: $pdo, $providers (array with 'crypto' key).
 */

// Expect $pdo and $providers to be set by the site wrapper
if (!isset($pdo) || !isset($providers)) {
    http_response_code(500);
    echo json_encode(['error' => 'Crypto confirm not configured']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    if (!isset($providers['crypto'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Crypto provider not available']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
        exit;
    }

    $orderRef = $input['order_ref'] ?? '';
    $txHash = $input['tx_hash'] ?? '';

    if (empty($orderRef)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'order_ref is required']);
        exit;
    }

    if (empty($txHash)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'tx_hash is required']);
        exit;
    }

    // Verify the order exists and belongs to crypto provider
    $order = CommerceOrder::get($pdo, $orderRef);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    if ($order['payment_provider'] !== 'crypto') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Order is not a crypto payment']);
        exit;
    }

    /** @var CommerceCrypto $cryptoProvider */
    $cryptoProvider = $providers['crypto'];

    $result = $cryptoProvider->confirm($orderRef, ['tx_hash' => $txHash]);

    if (!($result['success'] ?? false)) {
        http_response_code(400);
    }

    echo json_encode($result);

} catch (\Throwable $e) {
    error_log("[Commerce API] crypto-confirm.php error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
