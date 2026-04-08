<?php
declare(strict_types=1);

/**
 * CommercePayPal — PayPal REST API payment provider.
 *
 * Supports one-time payments via PayPal Checkout.
 * Flow: initiate() -> redirect to PayPal -> webhook/return -> confirm()
 *
 * Requires env vars: PAYPAL_CLIENT_ID, PAYPAL_SECRET, PAYPAL_MODE (sandbox|live)
 */
class CommercePayPal extends CommerceProvider
{
    protected string $providerName = 'paypal';
    private string $clientId;
    private string $secret;
    private string $baseUrl;

    public function __construct(PDO $pdo, array $config = [])
    {
        parent::__construct($pdo);
        $this->clientId = $config['client_id'] ?? ($_ENV['PAYPAL_CLIENT_ID'] ?? '');
        $this->secret = $config['secret'] ?? ($_ENV['PAYPAL_SECRET'] ?? '');
        $mode = $config['mode'] ?? ($_ENV['PAYPAL_MODE'] ?? 'sandbox');
        $this->baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function initiate(string $siteKey, array $data): array
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            return ['success' => false, 'error' => 'Items required.'];
        }

        $order = CommerceOrder::create($this->pdo, $siteKey, $items, [
            'payment_provider' => $this->providerName,
            'payment_method'   => 'paypal',
            'customer_name'    => $data['customer_name'] ?? null,
            'customer_email'   => $data['customer_email'] ?? null,
            'user_id'          => $data['user_id'] ?? null,
            'metadata'         => $data['metadata'] ?? null,
        ]);

        if (!$order['success']) return $order;

        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'PayPal authentication failed.'];
        }

        $returnUrl = $data['return_url'] ?? "https://{$siteKey}/checkout/success";
        $cancelUrl = $data['cancel_url'] ?? "https://{$siteKey}/checkout/cancel";

        $ppItems = [];
        foreach ($items as $item) {
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $price = number_format((float)$item['unit_price'], 2, '.', '');
            $ppItems[] = [
                'name'        => substr($item['description'], 0, 127),
                'quantity'    => (string)$qty,
                'unit_amount' => ['currency_code' => 'USD', 'value' => $price],
            ];
        }

        $total = number_format((float)$order['total'], 2, '.', '');

        $ppOrder = $this->apiCall('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order['order_ref'],
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $total,
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => $total],
                    ],
                ],
                'items' => $ppItems,
            ]],
            'application_context' => [
                'return_url' => $returnUrl . '?order_ref=' . $order['order_ref'],
                'cancel_url' => $cancelUrl . '?order_ref=' . $order['order_ref'],
                'brand_name' => $data['brand_name'] ?? $siteKey,
                'user_action' => 'PAY_NOW',
            ],
        ], $token);

        if (!$ppOrder || empty($ppOrder['id'])) {
            return ['success' => false, 'error' => 'Failed to create PayPal order.'];
        }

        // Store PayPal order ID in metadata
        $metaUpdate = json_encode(['paypal_order_id' => $ppOrder['id']]);
        $this->pdo->prepare("UPDATE commerce_orders SET metadata = ? WHERE order_ref = ?")
            ->execute([$metaUpdate, $order['order_ref']]);

        $approvalUrl = '';
        foreach ($ppOrder['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') {
                $approvalUrl = $link['href'];
                break;
            }
        }

        return [
            'success'         => true,
            'order_ref'       => $order['order_ref'],
            'total'           => $order['total'],
            'approval_url'    => $approvalUrl,
            'paypal_order_id' => $ppOrder['id'],
        ];
    }

    public function confirm(string $orderRef, array $data = []): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) return ['success' => false, 'error' => 'Order not found.'];

        $meta = is_array($order['metadata']) ? $order['metadata'] : [];
        $ppOrderId = $meta['paypal_order_id'] ?? ($data['paypal_order_id'] ?? '');
        if (empty($ppOrderId)) {
            return ['success' => false, 'error' => 'PayPal order ID not found.'];
        }

        $token = $this->getAccessToken();
        if (!$token) return ['success' => false, 'error' => 'PayPal auth failed.'];

        $capture = $this->apiCall('POST', "/v2/checkout/orders/{$ppOrderId}/capture", [], $token);

        if (!$capture || ($capture['status'] ?? '') !== 'COMPLETED') {
            return ['success' => false, 'error' => 'PayPal capture failed.', 'details' => $capture];
        }

        $captureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? 'unknown';
        CommerceOrder::addTransaction($this->pdo, $orderRef, [
            'type'                    => 'payment',
            'provider'                => $this->providerName,
            'provider_transaction_id' => $captureId,
            'amount'                  => (float)$order['total'],
            'status'                  => 'completed',
            'provider_metadata'       => $capture,
        ]);

        CommerceOrder::updateStatus($this->pdo, $orderRef, 'completed');

        return ['success' => true, 'status' => 'completed', 'capture_id' => $captureId];
    }

    public function cancel(string $orderRef, array $data = []): array
    {
        return CommerceOrder::updateStatus($this->pdo, $orderRef, 'cancelled');
    }

    public function getStatus(string $orderRef): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) return ['success' => false, 'error' => 'Order not found.'];

        return [
            'success'   => true,
            'status'    => $order['status'],
            'order_ref' => $orderRef,
            'total'     => $order['total'],
            'paid_at'   => $order['paid_at'],
        ];
    }

    public function handleWebhook(string $rawBody, array $headers = []): array
    {
        $event = json_decode($rawBody, true);
        if (!$event || empty($event['event_type'])) {
            return ['success' => false, 'error' => 'Invalid webhook payload.'];
        }

        try {
            $this->pdo->prepare("INSERT INTO commerce_webhooks (provider, event_type, payload) VALUES (?, ?, ?)")
                ->execute([$this->providerName, $event['event_type'], $rawBody]);
        } catch (\Throwable $e) {
            error_log("[CommercePayPal] webhook log failed: " . $e->getMessage());
        }

        switch ($event['event_type']) {
            case 'CHECKOUT.ORDER.APPROVED':
                $ppOrderId = $event['resource']['id'] ?? '';
                $stmt = $this->pdo->prepare("SELECT order_ref FROM commerce_orders WHERE metadata LIKE ?");
                $stmt->execute(['%' . $ppOrderId . '%']);
                $ref = $stmt->fetchColumn();
                if ($ref) {
                    CommerceOrder::updateStatus($this->pdo, $ref, 'processing');
                }
                break;

            case 'PAYMENT.CAPTURE.COMPLETED':
                $ppOrderId = $event['resource']['supplementary_data']['related_ids']['order_id'] ?? '';
                if ($ppOrderId) {
                    $stmt = $this->pdo->prepare("SELECT order_ref FROM commerce_orders WHERE metadata LIKE ?");
                    $stmt->execute(['%' . $ppOrderId . '%']);
                    $ref = $stmt->fetchColumn();
                    if ($ref) {
                        $this->confirm($ref, ['paypal_order_id' => $ppOrderId]);
                    }
                }
                break;

            default:
                error_log("[CommercePayPal] Unhandled webhook: " . $event['event_type']);
        }

        return ['success' => true, 'event_type' => $event['event_type']];
    }

    public function refund(string $orderRef, float $amount = 0.0, array $data = []): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) return ['success' => false, 'error' => 'Order not found.'];

        $captureId = '';
        foreach ($order['transactions'] as $tx) {
            if ($tx['type'] === 'payment' && $tx['status'] === 'completed' && !empty($tx['provider_transaction_id'])) {
                $captureId = $tx['provider_transaction_id'];
                break;
            }
        }

        if (empty($captureId)) {
            return ['success' => false, 'error' => 'No capture transaction found.'];
        }

        $token = $this->getAccessToken();
        if (!$token) return ['success' => false, 'error' => 'PayPal auth failed.'];

        $refundAmount = $amount > 0 ? $amount : (float)$order['total'];
        $refundData = [
            'amount' => [
                'currency_code' => $order['currency'],
                'value' => number_format($refundAmount, 2, '.', ''),
            ],
        ];

        $result = $this->apiCall('POST', "/v2/payments/captures/{$captureId}/refund", $refundData, $token);

        if (!$result || ($result['status'] ?? '') !== 'COMPLETED') {
            return ['success' => false, 'error' => 'PayPal refund failed.', 'details' => $result];
        }

        CommerceOrder::addTransaction($this->pdo, $orderRef, [
            'type'                    => 'refund',
            'provider'                => $this->providerName,
            'provider_transaction_id' => $result['id'] ?? null,
            'amount'                  => $refundAmount,
            'status'                  => 'completed',
            'provider_metadata'       => $result,
        ]);

        CommerceOrder::updateStatus($this->pdo, $orderRef, 'refunded');

        return ['success' => true, 'refund_id' => $result['id'] ?? null];
    }

    private function getAccessToken(): ?string
    {
        static $cached = null;
        if ($cached) return $cached;

        $ch = curl_init($this->baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_USERPWD        => $this->clientId . ':' . $this->secret,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            error_log("[CommercePayPal] Token failed: HTTP {$httpCode}");
            return null;
        }

        $data = json_decode($response, true);
        $cached = $data['access_token'] ?? null;
        return $cached;
    }

    private function apiCall(string $method, string $path, array $body, string $token): ?array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_TIMEOUT => 30,
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = !empty($body) ? json_encode($body) : '{}';
        }

        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response) {
            error_log("[CommercePayPal] API: {$method} {$path} HTTP {$httpCode}");
            return null;
        }

        return json_decode($response, true);
    }
}
