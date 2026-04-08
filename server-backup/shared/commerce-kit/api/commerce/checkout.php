<?php
declare(strict_types=1);

/**
 * Commerce Kit — Checkout Endpoint
 *
 * POST: Initiate a payment with any configured provider (stripe, paypal, crypto).
 * Creates an order and returns provider-specific payment data.
 *
 * Site wrapper must set: $pdo, $siteKey, $providers (array keyed by provider name).
 */

// Expect $pdo, $siteKey, and $providers to be set by the site wrapper
if (!isset($pdo) || !isset($siteKey) || !isset($providers)) {
    http_response_code(500);
    echo json_encode(['error' => 'Checkout not configured']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
        exit;
    }

    $providerName = $input['provider'] ?? '';
    $items = $input['items'] ?? [];

    if (empty($providerName)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'provider is required']);
        exit;
    }

    if (empty($items)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'items are required']);
        exit;
    }

    $validProviders = ['stripe', 'paypal', 'crypto'];
    if (!in_array($providerName, $validProviders, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Invalid provider: {$providerName}"]);
        exit;
    }

    if (!isset($providers[$providerName])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Provider not available: {$providerName}"]);
        exit;
    }

    /** @var CommerceProvider $provider */
    $provider = $providers[$providerName];

    // Build provider-specific data from the request
    $data = [
        'items'          => $items,
        'customer_name'  => $input['customer_name'] ?? null,
        'customer_email' => $input['customer_email'] ?? null,
        'customer_phone' => $input['customer_phone'] ?? null,
        'metadata'       => $input['metadata'] ?? null,
    ];

    // Stripe/PayPal: pass return/cancel URLs
    if ($providerName === 'stripe') {
        if (!empty($input['return_url'])) {
            $data['success_url'] = $input['return_url'];
        }
        if (!empty($input['cancel_url'])) {
            $data['cancel_url'] = $input['cancel_url'];
        }
    } elseif ($providerName === 'paypal') {
        if (!empty($input['return_url'])) {
            $data['return_url'] = $input['return_url'];
        }
        if (!empty($input['cancel_url'])) {
            $data['cancel_url'] = $input['cancel_url'];
        }
    } elseif ($providerName === 'crypto') {
        // Crypto-specific fields
        if (!empty($input['crypto_currency'])) {
            $data['crypto_currency'] = $input['crypto_currency'];
        }
        if (!empty($input['crypto_amount'])) {
            $data['crypto_amount'] = $input['crypto_amount'];
        }
    }

    $result = $provider->initiate($siteKey, $data);

    if (!($result['success'] ?? false)) {
        http_response_code(400);
    }

    echo json_encode($result);

} catch (\Throwable $e) {
    error_log("[Commerce API] checkout.php error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
