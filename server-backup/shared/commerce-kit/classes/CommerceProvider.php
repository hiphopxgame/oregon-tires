<?php
declare(strict_types=1);

/**
 * CommerceProvider — Abstract base for payment providers.
 *
 * Each provider implements:
 *   initiate()  — Create an order and begin payment flow
 *   confirm()   — Confirm/complete a payment
 *   cancel()    — Cancel an order
 *   getStatus() — Check current order status
 *   refund()    — Refund a completed order (optional)
 *
 * All methods return ['success' => bool, ...data] or ['success' => false, 'error' => string].
 */
abstract class CommerceProvider
{
    protected PDO $pdo;
    protected string $providerName;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Initiate a new order/payment.
     *
     * @param string $siteKey  Site identifier
     * @param array  $data     Provider-specific data (items, customer info, etc.)
     * @return array {success, order_ref, ...provider-specific data}
     */
    abstract public function initiate(string $siteKey, array $data): array;

    /**
     * Confirm/complete a payment for an order.
     *
     * @param string $orderRef  Order reference
     * @param array  $data      Provider-specific confirmation data
     * @return array {success, ...}
     */
    abstract public function confirm(string $orderRef, array $data = []): array;

    /**
     * Cancel an order.
     *
     * @param string $orderRef  Order reference
     * @param array  $data      Provider-specific cancellation data
     * @return array {success, ...}
     */
    abstract public function cancel(string $orderRef, array $data = []): array;

    /**
     * Get the current status of an order.
     *
     * @param string $orderRef  Order reference
     * @return array {success, status, ...}
     */
    abstract public function getStatus(string $orderRef): array;

    /**
     * Refund a completed order (optional — default returns error).
     */
    public function refund(string $orderRef, float $amount = 0.0, array $data = []): array
    {
        return ['success' => false, 'error' => "Refund not supported by {$this->providerName} provider."];
    }

    /**
     * Handle an incoming webhook (optional — default no-op).
     */
    public function handleWebhook(string $rawBody, array $headers = []): array
    {
        return ['success' => true, 'message' => 'No webhook handling for this provider.'];
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }
}
