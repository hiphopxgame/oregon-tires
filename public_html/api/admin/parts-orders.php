<?php
/**
 * Oregon Tires — Admin Parts Orders CRUD + Status Transitions
 * GET    — list orders (with filters)
 * POST   — create order / add items / transition status
 * PUT    — update order
 * DELETE — cancel order
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/parts.php';

try {
    $staff = requirePermission('shop_ops');
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        session_write_close();

        $status = sanitize((string) ($_GET['status'] ?? ''), 20);
        $vendorId = (int) ($_GET['vendor_id'] ?? 0);
        $roId = (int) ($_GET['ro_id'] ?? 0);
        $orderId = (int) ($_GET['id'] ?? 0);

        // Single order detail
        if ($orderId > 0) {
            $stmt = $db->prepare(
                'SELECT po.*, v.name AS vendor_name
                 FROM oretir_parts_orders po
                 LEFT JOIN oretir_vendors v ON po.vendor_id = v.id
                 WHERE po.id = ?'
            );
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$order) jsonError('Order not found', 404);

            // Fetch items
            $itemStmt = $db->prepare(
                'SELECT poi.*, pc.part_number, pc.name AS part_name
                 FROM oretir_parts_order_items poi
                 LEFT JOIN oretir_parts_catalog pc ON poi.part_id = pc.id
                 WHERE poi.order_id = ?
                 ORDER BY poi.id ASC'
            );
            $itemStmt->execute([$orderId]);
            $order['items'] = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);

            jsonSuccess($order);
        }

        // List orders
        $where = [];
        $params = [];

        if ($status !== '') {
            $validStatuses = ['draft', 'ordered', 'shipped', 'partial', 'received', 'cancelled'];
            if (in_array($status, $validStatuses, true)) {
                $where[] = 'po.status = ?';
                $params[] = $status;
            }
        }

        if ($vendorId > 0) {
            $where[] = 'po.vendor_id = ?';
            $params[] = $vendorId;
        }

        if ($roId > 0) {
            $where[] = 'po.ro_id = ?';
            $params[] = $roId;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare(
            "SELECT po.*, v.name AS vendor_name,
                    (SELECT COUNT(*) FROM oretir_parts_order_items WHERE order_id = po.id) AS item_count
             FROM oretir_parts_orders po
             LEFT JOIN oretir_vendors v ON po.vendor_id = v.id
             {$whereStr}
             ORDER BY po.created_at DESC
             LIMIT 200"
        );
        $stmt->execute($params);
        jsonSuccess($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    verifyCsrf();
    $data = getJsonBody();
    $action = $data['action'] ?? '';

    if ($method === 'POST') {
        if ($action === 'create') {
            $vendorId = (int) ($data['vendor_id'] ?? 0);
            if ($vendorId <= 0) jsonError('Vendor is required.');

            $orderNumber = generatePartOrderNumber($db);
            $roId = !empty($data['ro_id']) ? (int) $data['ro_id'] : null;

            $stmt = $db->prepare(
                'INSERT INTO oretir_parts_orders (order_number, vendor_id, ro_id, notes, status)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderNumber,
                $vendorId,
                $roId,
                sanitize((string) ($data['notes'] ?? ''), 2000),
                'draft',
            ]);

            $id = (int) $db->lastInsertId();

            // Add items if provided
            if (!empty($data['items']) && is_array($data['items'])) {
                $itemStmt = $db->prepare(
                    'INSERT INTO oretir_parts_order_items (order_id, part_id, description, quantity, unit_cost, ro_id, estimate_item_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                foreach ($data['items'] as $item) {
                    $itemStmt->execute([
                        $id,
                        !empty($item['part_id']) ? (int) $item['part_id'] : null,
                        sanitize((string) ($item['description'] ?? ''), 500),
                        max(1, (int) ($item['quantity'] ?? 1)),
                        (float) ($item['unit_cost'] ?? 0),
                        !empty($item['ro_id']) ? (int) $item['ro_id'] : $roId,
                        !empty($item['estimate_item_id']) ? (int) $item['estimate_item_id'] : null,
                    ]);
                }
                updatePartsOrderTotal($db, $id);
            }

            $newStmt = $db->prepare('SELECT po.*, v.name AS vendor_name FROM oretir_parts_orders po LEFT JOIN oretir_vendors v ON po.vendor_id = v.id WHERE po.id = ?');
            $newStmt->execute([$id]);
            jsonSuccess($newStmt->fetch(\PDO::FETCH_ASSOC), 201);
        }

        if ($action === 'add_item') {
            $orderId = (int) ($data['order_id'] ?? 0);
            if ($orderId <= 0) jsonError('Missing order_id.');

            $stmt = $db->prepare(
                'INSERT INTO oretir_parts_order_items (order_id, part_id, description, quantity, unit_cost, ro_id, estimate_item_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderId,
                !empty($data['part_id']) ? (int) $data['part_id'] : null,
                sanitize((string) ($data['description'] ?? ''), 500),
                max(1, (int) ($data['quantity'] ?? 1)),
                (float) ($data['unit_cost'] ?? 0),
                !empty($data['ro_id']) ? (int) $data['ro_id'] : null,
                !empty($data['estimate_item_id']) ? (int) $data['estimate_item_id'] : null,
            ]);

            updatePartsOrderTotal($db, $orderId);
            jsonSuccess(['item_id' => (int) $db->lastInsertId()], 201);
        }

        if ($action === 'transition') {
            $orderId = (int) ($data['order_id'] ?? 0);
            $newStatus = sanitize((string) ($data['status'] ?? ''), 20);
            if ($orderId <= 0) jsonError('Missing order_id.');

            $validTransitions = [
                'draft' => ['ordered', 'cancelled'],
                'ordered' => ['shipped', 'partial', 'received', 'cancelled'],
                'shipped' => ['partial', 'received'],
                'partial' => ['received'],
            ];

            $currentStmt = $db->prepare('SELECT status FROM oretir_parts_orders WHERE id = ?');
            $currentStmt->execute([$orderId]);
            $current = $currentStmt->fetchColumn();

            if (!$current) jsonError('Order not found', 404);

            $allowed = $validTransitions[$current] ?? [];
            if (!in_array($newStatus, $allowed, true)) {
                jsonError("Cannot transition from '{$current}' to '{$newStatus}'");
            }

            $updates = ['status = ?', 'updated_at = NOW()'];
            $params = [$newStatus];

            if ($newStatus === 'ordered') {
                $updates[] = 'ordered_at = NOW()';
            }
            if ($newStatus === 'received') {
                $updates[] = 'received_at = NOW()';
            }

            if (!empty($data['tracking_number'])) {
                $updates[] = 'tracking_number = ?';
                $params[] = sanitize((string) $data['tracking_number'], 200);
            }
            if (!empty($data['expected_at'])) {
                $updates[] = 'expected_at = ?';
                $params[] = sanitize((string) $data['expected_at'], 10);
            }

            $params[] = $orderId;
            $db->prepare("UPDATE oretir_parts_orders SET " . implode(', ', $updates) . " WHERE id = ?")->execute($params);

            // If received, update received_qty on items
            if ($newStatus === 'received') {
                $db->prepare('UPDATE oretir_parts_order_items SET received_qty = quantity WHERE order_id = ?')->execute([$orderId]);
            }

            jsonSuccess(['status' => $newStatus]);
        }

        if ($action === 'receive_item') {
            $itemId = (int) ($data['item_id'] ?? 0);
            $receivedQty = max(0, (int) ($data['received_qty'] ?? 0));
            if ($itemId <= 0) jsonError('Missing item_id.');

            $db->prepare('UPDATE oretir_parts_order_items SET received_qty = ? WHERE id = ?')->execute([$receivedQty, $itemId]);

            // Check if all items received → mark order as received
            $orderIdStmt = $db->prepare('SELECT order_id FROM oretir_parts_order_items WHERE id = ?');
            $orderIdStmt->execute([$itemId]);
            $orderId = (int) $orderIdStmt->fetchColumn();

            $checkStmt = $db->prepare(
                'SELECT COUNT(*) FROM oretir_parts_order_items WHERE order_id = ? AND received_qty < quantity'
            );
            $checkStmt->execute([$orderId]);
            $unreceivedCount = (int) $checkStmt->fetchColumn();

            if ($unreceivedCount === 0) {
                $db->prepare("UPDATE oretir_parts_orders SET status = 'received', received_at = NOW() WHERE id = ? AND status IN ('ordered','shipped','partial')")
                   ->execute([$orderId]);
            } else {
                // Partial receive
                $db->prepare("UPDATE oretir_parts_orders SET status = 'partial' WHERE id = ? AND status IN ('ordered','shipped')")
                   ->execute([$orderId]);
            }

            jsonSuccess(['received_qty' => $receivedQty]);
        }

        jsonError('Invalid action', 400);
    }

    if ($method === 'PUT') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing order id.');

        $stmt = $db->prepare(
            'UPDATE oretir_parts_orders SET notes = ?, tracking_number = ?, expected_at = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([
            sanitize((string) ($data['notes'] ?? ''), 2000),
            sanitize((string) ($data['tracking_number'] ?? ''), 200),
            !empty($data['expected_at']) ? sanitize((string) $data['expected_at'], 10) : null,
            $id,
        ]);
        jsonSuccess(['updated' => true]);
    }

    if ($method === 'DELETE') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Missing order id.');

        $db->prepare("UPDATE oretir_parts_orders SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND status = 'draft'")
           ->execute([$id]);
        jsonSuccess(['cancelled' => true]);
    }

} catch (\Throwable $e) {
    error_log("Admin parts-orders error: " . $e->getMessage());
    jsonError('Server error', 500);
}
