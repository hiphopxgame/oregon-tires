<?php
declare(strict_types=1);

/**
 * CommerceStripe — Stripe Checkout Sessions payment provider.
 *
 * Flow: initiate() -> Stripe Checkout redirect -> webhook -> completed
 *
 * Supports:
 *   - Checkout Session creation (redirect flow)
 *   - Webhook handling (checkout.session.completed, charge.refunded)
 *   - Refund via Stripe API
 *
 * Required env vars:
 *   STRIPE_SECRET_KEY     — sk_test_... or sk_live_...
 *   STRIPE_WEBHOOK_SECRET — whsec_...
 *   STRIPE_MODE           — test|live (for URL selection)
 */
class CommerceStripe extends CommerceProvider
{
    protected string $providerName = 'stripe';
    private string $secretKey;
    private string $webhookSecret;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct(PDO $pdo, array $config = [])
    {
        parent::__construct($pdo);
        $this->secretKey = $config['secret_key'] ?? ($_ENV['STRIPE_SECRET_KEY'] ?? '');
        $this->webhookSecret = $config['webhook_secret'] ?? ($_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');
    }

    /**
     * Create a Stripe Checkout Session and return the checkout URL.
     */
    public function initiate(string $siteKey, array $data): array
    {
        if (empty($this->secretKey)) {
            return ['success' => false, 'error' => 'Stripe is not configured.'];
        }

        if (empty($data['items'])) {
            return ['success' => false, 'error' => 'Items are required.'];
        }

        // Create order in our system first
        $result = CommerceOrder::create($this->pdo, $siteKey, $data['items'], array_merge($data, [
            'payment_provider' => 'stripe',
            'payment_method'   => 'card',
        ]));

        if (!$result['success']) {
            return $result;
        }

        $orderRef = $result['order_ref'];

        // Build Stripe line items
        $lineItems = [];
        foreach ($data['items'] as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => strtolower($data['currency'] ?? 'usd'),
                    'unit_amount'  => (int) round((float) $item['unit_price'] * 100),
                    'product_data' => [
                        'name' => $item['description'],
                    ],
                ],
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
            ];
        }

        $successUrl = $data['success_url'] ?? ($data['base_url'] ?? '') . '/order-success?ref=' . $orderRef;
        $cancelUrl = $data['cancel_url'] ?? ($data['base_url'] ?? '') . '/order-cancelled?ref=' . $orderRef;

        // Create Checkout Session
        $sessionData = [
            'mode'                => 'payment',
            'success_url'         => $successUrl,
            'cancel_url'          => $cancelUrl,
            'client_reference_id' => $orderRef,
            'customer_email'      => $data['customer_email'] ?? null,
            'metadata'            => ['order_ref' => $orderRef, 'site_key' => $siteKey],
        ];

        // Add line items in Stripe's form-encoded format
        foreach ($lineItems as $i => $li) {
            foreach ($li as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k2 => $v2) {
                        if (is_array($v2)) {
                            foreach ($v2 as $k3 => $v3) {
                                $sessionData["line_items[{$i}][{$key}][{$k2}][{$k3}]"] = $v3;
                            }
                        } else {
                            $sessionData["line_items[{$i}][{$key}][{$k2}]"] = $v2;
                        }
                    }
                } else {
                    $sessionData["line_items[{$i}][{$key}]"] = $val;
                }
            }
        }

        // Add metadata in form-encoded format
        if (isset($sessionData['metadata']) && is_array($sessionData['metadata'])) {
            $meta = $sessionData['metadata'];
            unset($sessionData['metadata']);
            foreach ($meta as $mk => $mv) {
                $sessionData["metadata[{$mk}]"] = $mv;
            }
        }

        // Remove null values
        $sessionData = array_filter($sessionData, fn($v) => $v !== null);

        $session = $this->apiCall('POST', '/checkout/sessions', $sessionData);

        if (!$session || !isset($session['id'])) {
            CommerceOrder::updateStatus($this->pdo, $orderRef, 'failed');
            return ['success' => false, 'error' => 'Failed to create Stripe checkout session.'];
        }

        // Store Stripe session ID in order metadata
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if ($order) {
            $meta = $order['metadata'] ?? [];
            $meta['stripe_session_id'] = $session['id'];
            $this->pdo->prepare("UPDATE commerce_orders SET metadata = ? WHERE order_ref = ?")
                ->execute([json_encode($meta), $orderRef]);
        }

        // Record pending transaction
        CommerceOrder::addTransaction($this->pdo, $orderRef, [
            'type'     => 'payment',
            'provider' => 'stripe',
            'provider_transaction_id' => $session['id'],
            'amount'   => $result['total'],
            'currency' => $data['currency'] ?? 'USD',
            'status'   => 'pending',
        ]);

        return [
            'success'      => true,
            'order_ref'    => $orderRef,
            'checkout_url' => $session['url'] ?? '',
            'session_id'   => $session['id'],
            'total'        => $result['total'],
        ];
    }

    /**
     * Confirm a payment (typically called after webhook or success redirect).
     */
    public function confirm(string $orderRef, array $data = []): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order['status'] === 'completed') {
            return ['success' => true, 'message' => 'Order already completed.'];
        }

        // Retrieve the Stripe session to verify payment
        $sessionId = $data['session_id'] ?? ($order['metadata']['stripe_session_id'] ?? '');
        if ($sessionId) {
            $session = $this->apiCall('GET', '/checkout/sessions/' . $sessionId);
            if ($session && ($session['payment_status'] ?? '') === 'paid') {
                CommerceOrder::updateStatus($this->pdo, $orderRef, 'completed');

                // Update transaction to completed
                $paymentIntent = $session['payment_intent'] ?? '';
                if ($paymentIntent) {
                    $this->pdo->prepare("
                        UPDATE commerce_transactions
                        SET status = 'completed', provider_transaction_id = ?
                        WHERE order_id = ? AND provider = 'stripe' AND status = 'pending'
                        ORDER BY created_at DESC LIMIT 1
                    ")->execute([$paymentIntent, $order['id']]);
                }

                return ['success' => true, 'status' => 'completed'];
            }
        }

        return ['success' => false, 'error' => 'Payment not confirmed.'];
    }

    /**
     * Cancel an order.
     */
    public function cancel(string $orderRef, array $data = []): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order['status'] === 'completed') {
            return ['success' => false, 'error' => 'Cannot cancel a completed order. Use refund instead.'];
        }

        CommerceOrder::updateStatus($this->pdo, $orderRef, 'cancelled');
        return ['success' => true, 'status' => 'cancelled'];
    }

    /**
     * Get order status.
     */
    public function getStatus(string $orderRef): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        return [
            'success' => true,
            'status'  => $order['status'],
            'total'   => $order['total'],
            'paid_at' => $order['paid_at'],
        ];
    }

    /**
     * Refund a completed order via Stripe API.
     */
    public function refund(string $orderRef, float $amount = 0.0, array $data = []): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Can only refund completed orders.'];
        }

        // Find the payment intent from transactions
        $stmt = $this->pdo->prepare("
            SELECT provider_transaction_id FROM commerce_transactions
            WHERE order_id = ? AND provider = 'stripe' AND type = 'payment' AND status = 'completed'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$order['id']]);
        $tx = $stmt->fetch();

        if (!$tx || empty($tx['provider_transaction_id'])) {
            return ['success' => false, 'error' => 'No Stripe payment found to refund.'];
        }

        $refundData = ['payment_intent' => $tx['provider_transaction_id']];
        if ($amount > 0) {
            $refundData['amount'] = (int) round($amount * 100);
        }

        $refund = $this->apiCall('POST', '/refunds', $refundData);

        if (!$refund || ($refund['status'] ?? '') !== 'succeeded') {
            return ['success' => false, 'error' => 'Stripe refund failed.'];
        }

        $refundAmount = $amount > 0 ? $amount : (float) $order['total'];

        CommerceOrder::addTransaction($this->pdo, $orderRef, [
            'type'     => 'refund',
            'provider' => 'stripe',
            'provider_transaction_id' => $refund['id'],
            'amount'   => $refundAmount,
            'currency' => $order['currency'],
            'status'   => 'completed',
            'provider_metadata' => $refund,
        ]);

        CommerceOrder::updateStatus($this->pdo, $orderRef, 'refunded');

        return ['success' => true, 'refund_id' => $refund['id'], 'amount' => $refundAmount];
    }

    /**
     * Handle Stripe webhook events.
     */
    public function handleWebhook(string $rawBody, array $headers = []): array
    {
        // Log webhook
        try {
            $payload = json_decode($rawBody, true) ?: [];
            $eventType = $payload['type'] ?? 'unknown';

            $this->pdo->prepare("
                INSERT INTO commerce_webhooks (provider, event_type, payload) VALUES ('stripe', ?, ?)
            ")->execute([$eventType, $rawBody]);
        } catch (\Throwable $e) {
            error_log("[CommerceStripe] Webhook log failed: {$e->getMessage()}");
        }

        // Verify webhook signature if secret is configured
        if ($this->webhookSecret) {
            $sigHeader = $headers['stripe-signature'] ?? $headers['Stripe-Signature'] ?? '';
            if (!$this->verifyWebhookSignature($rawBody, $sigHeader)) {
                return ['success' => false, 'error' => 'Invalid webhook signature.'];
            }
        }

        $event = json_decode($rawBody, true);
        if (!$event || !isset($event['type'])) {
            return ['success' => false, 'error' => 'Invalid webhook payload.'];
        }

        switch ($event['type']) {
            case 'checkout.session.completed':
                return $this->handleCheckoutCompleted($event['data']['object'] ?? []);

            case 'charge.refunded':
                return $this->handleChargeRefunded($event['data']['object'] ?? []);

            default:
                return ['success' => true, 'message' => "Unhandled event: {$event['type']}"];
        }
    }

    private function handleCheckoutCompleted(array $session): array
    {
        $orderRef = $session['client_reference_id'] ?? ($session['metadata']['order_ref'] ?? '');
        if (!$orderRef) {
            return ['success' => false, 'error' => 'No order reference in session.'];
        }

        return $this->confirm($orderRef, ['session_id' => $session['id'] ?? '']);
    }

    private function handleChargeRefunded(array $charge): array
    {
        $paymentIntent = $charge['payment_intent'] ?? '';
        if (!$paymentIntent) {
            return ['success' => true, 'message' => 'No payment intent in charge.'];
        }

        // Find order by payment intent
        $stmt = $this->pdo->prepare("
            SELECT o.order_ref FROM commerce_orders o
            JOIN commerce_transactions t ON t.order_id = o.id
            WHERE t.provider_transaction_id = ? AND t.provider = 'stripe'
            LIMIT 1
        ");
        $stmt->execute([$paymentIntent]);
        $row = $stmt->fetch();

        if ($row) {
            $refundAmount = ($charge['amount_refunded'] ?? 0) / 100;
            CommerceOrder::addTransaction($this->pdo, $row['order_ref'], [
                'type'     => 'refund',
                'provider' => 'stripe',
                'provider_transaction_id' => $charge['id'] ?? '',
                'amount'   => $refundAmount,
                'status'   => 'completed',
            ]);
            CommerceOrder::updateStatus($this->pdo, $row['order_ref'], 'refunded');
        }

        return ['success' => true];
    }

    private function verifyWebhookSignature(string $payload, string $sigHeader): bool
    {
        if (empty($sigHeader) || empty($this->webhookSecret)) return false;

        $parts = [];
        foreach (explode(',', $sigHeader) as $item) {
            $pair = explode('=', $item, 2);
            if (count($pair) === 2) {
                $parts[trim($pair[0])] = trim($pair[1]);
            }
        }

        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';

        if (!$timestamp || !$signature) return false;

        // Tolerance: 5 minutes
        if (abs(time() - (int) $timestamp) > 300) return false;

        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Make a Stripe API call.
     */
    private function apiCall(string $method, string $path, array $data = []): ?array
    {
        $url = $this->apiBase . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || $response === false) {
            error_log("[CommerceStripe] API {$method} {$path} failed ({$httpCode}): " . ($response ?: 'no response'));
            return null;
        }

        return json_decode($response, true);
    }
}
