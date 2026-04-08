<?php
declare(strict_types=1);

/**
 * CommerceManual — Manual/booking payment provider.
 *
 * For services that don't require online payment (appointments, bookings, quotes).
 * Orders are created as "pending" and confirmed manually by the business owner.
 *
 * Flow: initiate() → pending → confirm() → completed
 *   or: initiate() → pending → cancel() → cancelled
 */
class CommerceManual extends CommerceProvider
{
    protected string $providerName = 'manual';

    /**
     * Create a booking/order without payment.
     *
     * @param string $siteKey
     * @param array  $data  Required: items. Optional: customer_name, customer_email, customer_phone, metadata, notes
     */
    public function initiate(string $siteKey, array $data): array
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            return ['success' => false, 'error' => 'At least one item is required.'];
        }

        $result = CommerceOrder::create($this->pdo, $siteKey, $items, [
            'payment_provider' => $this->providerName,
            'payment_method'   => 'manual',
            'customer_name'    => $data['customer_name'] ?? null,
            'customer_email'   => $data['customer_email'] ?? null,
            'customer_phone'   => $data['customer_phone'] ?? null,
            'metadata'         => $data['metadata'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'user_id'          => $data['user_id'] ?? null,
        ]);

        if (!$result['success']) return $result;

        // Record a "manual" transaction (amount = 0 until confirmed)
        CommerceOrder::addTransaction($this->pdo, $result['order_ref'], [
            'type'     => 'payment',
            'provider' => $this->providerName,
            'amount'   => 0.00,
            'status'   => 'pending',
        ]);

        return $result;
    }

    /**
     * Confirm a manual order (marks as completed, records payment).
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
        if ($order['status'] === 'cancelled') {
            return ['success' => false, 'error' => 'Cannot confirm a cancelled order.'];
        }

        // Update status
        $result = CommerceOrder::updateStatus($this->pdo, $orderRef, 'completed');
        if (!$result['success']) return $result;

        // Record the confirmed payment transaction
        CommerceOrder::addTransaction($this->pdo, $orderRef, [
            'type'     => 'payment',
            'provider' => $this->providerName,
            'amount'   => (float)$order['total'],
            'status'   => 'completed',
            'provider_metadata' => [
                'confirmed_by' => $data['confirmed_by'] ?? 'admin',
                'confirmed_at' => date('c'),
            ],
        ]);

        return ['success' => true, 'status' => 'completed'];
    }

    /**
     * Cancel a manual order.
     */
    public function cancel(string $orderRef, array $data = []): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }
        if ($order['status'] === 'cancelled') {
            return ['success' => true, 'message' => 'Order already cancelled.'];
        }
        if ($order['status'] === 'completed') {
            return ['success' => false, 'error' => 'Cannot cancel a completed order. Use refund instead.'];
        }

        return CommerceOrder::updateStatus($this->pdo, $orderRef, 'cancelled');
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
            'success'    => true,
            'status'     => $order['status'],
            'order_ref'  => $order['order_ref'],
            'total'      => $order['total'],
            'created_at' => $order['created_at'],
            'paid_at'    => $order['paid_at'],
        ];
    }
}
