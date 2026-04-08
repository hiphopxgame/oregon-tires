<?php
declare(strict_types=1);

/**
 * CommerceCrypto — Cryptocurrency payment provider.
 *
 * Multi-chain support (ETH, SOL, BTC, USDT, USDC).
 * Flow: initiate() -> user sends TX -> confirm(tx_hash) -> verify on-chain -> completed
 *
 * Orders expire after a configurable timeout (default 30 minutes).
 */
class CommerceCrypto extends CommerceProvider
{
    protected string $providerName = 'crypto';
    private int $expiryMinutes;
    private array $walletAddresses;

    public function __construct(PDO $pdo, array $config = [])
    {
        parent::__construct($pdo);
        $this->expiryMinutes = (int)($config['expiry_minutes'] ?? 30);
        $this->walletAddresses = $config['wallet_addresses'] ?? [
            'ETH'  => $_ENV['CRYPTO_WALLET_ETH'] ?? '',
            'SOL'  => $_ENV['CRYPTO_WALLET_SOL'] ?? '',
            'BTC'  => $_ENV['CRYPTO_WALLET_BTC'] ?? '',
            'USDT' => $_ENV['CRYPTO_WALLET_USDT'] ?? '',
            'USDC' => $_ENV['CRYPTO_WALLET_USDC'] ?? '',
        ];
    }

    /**
     * Create a crypto payment order.
     *
     * $data expects: items, currency (ETH/SOL/BTC/USDT/USDC), crypto_amount
     */
    public function initiate(string $siteKey, array $data): array
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            return ['success' => false, 'error' => 'Items required.'];
        }

        $cryptoCurrency = strtoupper($data['crypto_currency'] ?? 'ETH');
        $walletAddress = $this->walletAddresses[$cryptoCurrency] ?? '';

        if (empty($walletAddress)) {
            return ['success' => false, 'error' => "No wallet configured for {$cryptoCurrency}."];
        }

        $expiresAt = date('Y-m-d H:i:s', time() + ($this->expiryMinutes * 60));

        $order = CommerceOrder::create($this->pdo, $siteKey, $items, [
            'payment_provider' => $this->providerName,
            'payment_method'   => $cryptoCurrency,
            'customer_name'    => $data['customer_name'] ?? null,
            'customer_email'   => $data['customer_email'] ?? null,
            'user_id'          => $data['user_id'] ?? null,
            'expires_at'       => $expiresAt,
            'metadata'         => [
                'crypto_currency'  => $cryptoCurrency,
                'crypto_amount'    => $data['crypto_amount'] ?? null,
                'wallet_address'   => $walletAddress,
                'exchange_rate'    => $data['exchange_rate'] ?? null,
            ],
        ]);

        if (!$order['success']) return $order;

        return array_merge($order, [
            'wallet_address'  => $walletAddress,
            'crypto_currency' => $cryptoCurrency,
            'crypto_amount'   => $data['crypto_amount'] ?? null,
            'expires_at'      => $expiresAt,
        ]);
    }

    /**
     * Submit a transaction hash for verification.
     */
    public function confirm(string $orderRef, array $data = []): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) return ['success' => false, 'error' => 'Order not found.'];

        if ($order['status'] === 'completed') {
            return ['success' => true, 'message' => 'Already completed.'];
        }

        // Check expiry
        if (!empty($order['expires_at']) && strtotime($order['expires_at']) < time()) {
            CommerceOrder::updateStatus($this->pdo, $orderRef, 'failed');
            return ['success' => false, 'error' => 'Order expired.'];
        }

        $txHash = $data['tx_hash'] ?? '';
        if (empty($txHash)) {
            return ['success' => false, 'error' => 'Transaction hash required.'];
        }

        // Check for duplicate TX hash
        $dupStmt = $this->pdo->prepare("SELECT 1 FROM commerce_transactions WHERE provider_transaction_id = ? AND provider = 'crypto'");
        $dupStmt->execute([$txHash]);
        if ($dupStmt->fetch()) {
            return ['success' => false, 'error' => 'Transaction hash already used.'];
        }

        // Mark as processing (awaiting on-chain confirmation)
        CommerceOrder::updateStatus($this->pdo, $orderRef, 'processing');

        // Record the pending transaction
        CommerceOrder::addTransaction($this->pdo, $orderRef, [
            'type'                    => 'payment',
            'provider'                => $this->providerName,
            'provider_transaction_id' => $txHash,
            'amount'                  => (float)$order['total'],
            'status'                  => 'pending',
            'provider_metadata'       => [
                'tx_hash'         => $txHash,
                'submitted_at'    => date('c'),
                'crypto_currency' => $order['metadata']['crypto_currency'] ?? 'unknown',
            ],
        ]);

        return [
            'success'   => true,
            'status'    => 'processing',
            'message'   => 'Transaction submitted. Awaiting on-chain confirmation.',
            'order_ref' => $orderRef,
        ];
    }

    /**
     * Admin confirms on-chain verification.
     */
    public function verifyOnChain(string $orderRef): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) return ['success' => false, 'error' => 'Order not found.'];

        if ($order['status'] !== 'processing') {
            return ['success' => false, 'error' => 'Order not in processing state.'];
        }

        // Update pending transaction to completed
        $this->pdo->prepare("
            UPDATE commerce_transactions SET status = 'completed'
            WHERE order_id = ? AND provider = 'crypto' AND status = 'pending'
        ")->execute([$order['id']]);

        CommerceOrder::updateStatus($this->pdo, $orderRef, 'completed');

        return ['success' => true, 'status' => 'completed'];
    }

    public function cancel(string $orderRef, array $data = []): array
    {
        return CommerceOrder::updateStatus($this->pdo, $orderRef, 'cancelled');
    }

    public function getStatus(string $orderRef): array
    {
        $order = CommerceOrder::get($this->pdo, $orderRef);
        if (!$order) return ['success' => false, 'error' => 'Order not found.'];

        // Auto-expire
        if ($order['status'] === 'pending' && !empty($order['expires_at']) && strtotime($order['expires_at']) < time()) {
            CommerceOrder::updateStatus($this->pdo, $orderRef, 'failed');
            $order['status'] = 'failed';
        }

        return [
            'success'    => true,
            'status'     => $order['status'],
            'order_ref'  => $orderRef,
            'total'      => $order['total'],
            'expires_at' => $order['expires_at'],
            'paid_at'    => $order['paid_at'],
        ];
    }

    /**
     * Expire all pending crypto orders past their expiry time.
     * Run via cron: php -r "require 'loader.php'; CommerceCrypto::expireStale($pdo);"
     */
    public static function expireStale(PDO $pdo): int
    {
        $stmt = $pdo->prepare("
            UPDATE commerce_orders SET status = 'failed'
            WHERE payment_provider = 'crypto'
              AND status = 'pending'
              AND expires_at IS NOT NULL
              AND expires_at < NOW()
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
