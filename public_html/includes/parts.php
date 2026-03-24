<?php
/**
 * Oregon Tires — Parts Ordering Helpers
 */

declare(strict_types=1);

/**
 * Generate a unique parts order number (PO-XXXXXXXX).
 */
function generatePartOrderNumber(PDO $db): string
{
    $maxAttempts = 10;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $number = 'PO-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = $db->prepare('SELECT COUNT(*) FROM oretir_parts_orders WHERE order_number = ?');
        $stmt->execute([$number]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $number;
        }
    }
    throw new \RuntimeException('Failed to generate unique part order number');
}

/**
 * Recalculate and update the total on a parts order.
 */
function updatePartsOrderTotal(PDO $db, int $orderId): void
{
    $stmt = $db->prepare(
        'SELECT COALESCE(SUM(quantity * unit_cost), 0) FROM oretir_parts_order_items WHERE order_id = ?'
    );
    $stmt->execute([$orderId]);
    $total = (float) $stmt->fetchColumn();

    $db->prepare('UPDATE oretir_parts_orders SET total = ?, updated_at = NOW() WHERE id = ?')
       ->execute([$total, $orderId]);
}
