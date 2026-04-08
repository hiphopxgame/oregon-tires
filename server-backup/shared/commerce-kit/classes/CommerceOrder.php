<?php
declare(strict_types=1);

/**
 * CommerceOrder — Core order CRUD, reference generation, status tracking.
 *
 * All methods are static for simplicity (no instance state needed).
 * Every method returns ['success' => bool, ...data] or ['success' => false, 'error' => string].
 */
class CommerceOrder
{
    private const VALID_STATUSES = ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'];

    /**
     * Create a new order with line items.
     *
     * @param PDO    $pdo      Database connection
     * @param string $siteKey  Site identifier
     * @param array  $items    Line items: [['description', 'quantity', 'unit_price', ?'sku', ?'metadata']]
     * @param array  $options  Optional: user_id, customer_name, customer_email, customer_phone, currency, metadata, notes, expires_at
     * @return array {success, order_ref, status, total} or {success: false, error}
     */
    public static function create(PDO $pdo, string $siteKey, array $items, array $options = []): array
    {
        if (empty($items)) {
            return ['success' => false, 'error' => 'At least one line item is required.'];
        }

        // Validate line items
        foreach ($items as $i => $item) {
            if (empty($item['description'])) {
                return ['success' => false, 'error' => "Line item {$i}: description is required."];
            }
            if (!isset($item['unit_price']) || !is_numeric($item['unit_price'])) {
                return ['success' => false, 'error' => "Line item {$i}: unit_price is required."];
            }
        }

        // Calculate totals
        $subtotal = 0.0;
        foreach ($items as &$item) {
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $price = round((float)$item['unit_price'], 2);
            $amount = round($qty * $price, 2);
            $item['_qty'] = $qty;
            $item['_price'] = $price;
            $item['_amount'] = $amount;
            $subtotal += $amount;
        }
        unset($item);

        $tax = round((float)($options['tax'] ?? 0.0), 2);
        $total = round($subtotal + $tax, 2);

        $orderRef = self::generateRef($pdo);
        $currency = strtoupper($options['currency'] ?? 'USD');
        $metadata = isset($options['metadata']) ? json_encode($options['metadata']) : null;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO commerce_orders
                    (order_ref, site_key, user_id, status, payment_provider, currency,
                     subtotal, tax, total, customer_name, customer_email, customer_phone,
                     metadata, notes, expires_at)
                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderRef,
                $siteKey,
                $options['user_id'] ?? null,
                $options['payment_provider'] ?? null,
                $currency,
                $subtotal,
                $tax,
                $total,
                $options['customer_name'] ?? null,
                $options['customer_email'] ?? null,
                $options['customer_phone'] ?? null,
                $metadata,
                $options['notes'] ?? null,
                $options['expires_at'] ?? null,
            ]);

            $orderId = (int)$pdo->lastInsertId();

            // Insert line items
            $itemStmt = $pdo->prepare("
                INSERT INTO commerce_line_items (order_id, sku, description, quantity, unit_price, amount, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($items as $item) {
                $itemMeta = isset($item['metadata']) ? json_encode($item['metadata']) : null;
                $itemStmt->execute([
                    $orderId,
                    $item['sku'] ?? null,
                    $item['description'],
                    $item['_qty'],
                    $item['_price'],
                    $item['_amount'],
                    $itemMeta,
                ]);
            }

            $pdo->commit();

            return [
                'success'   => true,
                'order_ref' => $orderRef,
                'order_id'  => $orderId,
                'status'    => 'pending',
                'subtotal'  => $subtotal,
                'tax'       => $tax,
                'total'     => $total,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("[CommerceOrder] create failed: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Failed to create order.'];
        }
    }

    /**
     * Get a full order by reference, including line items and transactions.
     */
    public static function get(PDO $pdo, string $orderRef): ?array
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM commerce_orders WHERE order_ref = ?");
            $stmt->execute([$orderRef]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) return null;

            // Attach line items
            $itemStmt = $pdo->prepare("SELECT * FROM commerce_line_items WHERE order_id = ? ORDER BY id");
            $itemStmt->execute([$order['id']]);
            $order['line_items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            // Attach transactions
            $txStmt = $pdo->prepare("SELECT * FROM commerce_transactions WHERE order_id = ? ORDER BY created_at");
            $txStmt->execute([$order['id']]);
            $order['transactions'] = $txStmt->fetchAll(PDO::FETCH_ASSOC);

            // Decode JSON fields
            if (is_string($order['metadata'])) {
                $order['metadata'] = json_decode($order['metadata'], true);
            }

            return $order;
        } catch (\Throwable $e) {
            error_log("[CommerceOrder] get failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Update order status with validation.
     */
    public static function updateStatus(PDO $pdo, string $orderRef, string $newStatus): array
    {
        if (!in_array($newStatus, self::VALID_STATUSES, true)) {
            return ['success' => false, 'error' => "Invalid status: {$newStatus}"];
        }

        try {
            $extra = '';
            $params = [$newStatus, $orderRef];

            // Set paid_at when completing
            if ($newStatus === 'completed') {
                $extra = ', paid_at = UTC_TIMESTAMP()';
            }

            $stmt = $pdo->prepare("UPDATE commerce_orders SET status = ?{$extra} WHERE order_ref = ?");
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Order not found.'];
            }

            // Trigger notifications on status transitions
            try {
                $order = self::get($pdo, $orderRef);
                if ($order && !empty($order['customer_email'])) {
                    $notifyConfig = $order['metadata']['notification_config'] ?? [];
                    switch ($newStatus) {
                        case 'completed':
                            CommerceNotifications::sendOrderConfirmation($pdo, $orderRef, $notifyConfig);
                            if (!empty($notifyConfig['owner_email'])) {
                                CommerceNotifications::sendOwnerNotification($pdo, $orderRef, $notifyConfig['owner_email'], $notifyConfig);
                            }
                            break;
                        case 'cancelled':
                            CommerceNotifications::sendStatusUpdate($pdo, $orderRef, 'cancelled', $notifyConfig);
                            break;
                        case 'refunded':
                            CommerceNotifications::sendStatusUpdate($pdo, $orderRef, 'refunded', $notifyConfig);
                            break;
                    }
                }
            } catch (\Throwable $e) {
                error_log("[CommerceOrder] Notification failed for {$orderRef}: {$e->getMessage()}");
                // Don't fail the status update — it already succeeded
            }

            return ['success' => true, 'status' => $newStatus];
        } catch (\Throwable $e) {
            error_log("[CommerceOrder] updateStatus failed: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Failed to update status.'];
        }
    }

    /**
     * List orders for a site with optional filters.
     *
     * @param array $filters  Optional: status, user_id, limit (default 50), offset (default 0)
     */
    public static function list(PDO $pdo, string $siteKey, array $filters = []): array
    {
        try {
            $where = ['site_key = ?'];
            $params = [$siteKey];

            if (!empty($filters['status'])) {
                $where[] = 'status = ?';
                $params[] = $filters['status'];
            }
            if (!empty($filters['user_id'])) {
                $where[] = 'user_id = ?';
                $params[] = $filters['user_id'];
            }
            if (!empty($filters['search'])) {
                $searchParam = '%' . $filters['search'] . '%';
                $where[] = '(customer_name LIKE ? OR customer_email LIKE ? OR order_ref LIKE ?)';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            $limit = min((int)($filters['limit'] ?? 50), 200);
            $offset = max((int)($filters['offset'] ?? 0), 0);

            $whereStr = implode(' AND ', $where);
            $stmt = $pdo->prepare("
                SELECT * FROM commerce_orders
                WHERE {$whereStr}
                ORDER BY created_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("[CommerceOrder] list failed: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Generate a unique order reference: ORD-XXXXXXXX (8 hex chars).
     */
    public static function generateRef(PDO $pdo): string
    {
        for ($i = 0; $i < 10; $i++) {
            $ref = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $pdo->prepare("SELECT 1 FROM commerce_orders WHERE order_ref = ?");
            $stmt->execute([$ref]);
            if (!$stmt->fetch()) return $ref;
        }
        // Fallback: include timestamp for guaranteed uniqueness
        return 'ORD-' . strtoupper(dechex(time())) . strtoupper(bin2hex(random_bytes(2)));
    }

    /**
     * Record a transaction (payment, refund, adjustment) against an order.
     */
    public static function addTransaction(PDO $pdo, string $orderRef, array $data): array
    {
        try {
            $order = self::get($pdo, $orderRef);
            if (!$order) {
                return ['success' => false, 'error' => 'Order not found.'];
            }

            $providerMeta = isset($data['provider_metadata']) ? json_encode($data['provider_metadata']) : null;

            $stmt = $pdo->prepare("
                INSERT INTO commerce_transactions
                    (order_id, type, provider, provider_transaction_id, amount, currency, status, provider_metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $order['id'],
                $data['type'] ?? 'payment',
                $data['provider'] ?? 'unknown',
                $data['provider_transaction_id'] ?? null,
                round((float)($data['amount'] ?? 0), 2),
                $data['currency'] ?? $order['currency'],
                $data['status'] ?? 'pending',
                $providerMeta,
            ]);

            return [
                'success'        => true,
                'transaction_id' => (int)$pdo->lastInsertId(),
            ];
        } catch (\Throwable $e) {
            error_log("[CommerceOrder] addTransaction failed: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Failed to record transaction.'];
        }
    }
}
