<?php
/**
 * Oregon Tires — Admin Inspection Management
 * GET    /api/admin/inspections.php?ro_id=N     — List inspections for RO
 * GET    /api/admin/inspections.php?id=N        — Get single inspection with items + photos
 * POST   /api/admin/inspections.php             — Create inspection (auto-populates template items)
 * PUT    /api/admin/inspections.php             — Update inspection (items, status, complete, send)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    $admin = requireAdmin();
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET ─────────────────────────────────────────────────────────────
    if ($method === 'GET') {

        // Single inspection with items + photos
        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $db->prepare(
                'SELECT i.*, r.ro_number, r.customer_id, r.vehicle_id,
                    c.first_name, c.last_name, c.email as customer_email,
                    v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model, v.vin
                 FROM oretir_inspections i
                 JOIN oretir_repair_orders r ON r.id = i.repair_order_id
                 JOIN oretir_customers c ON c.id = r.customer_id
                 LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                 WHERE i.id = ?'
            );
            $stmt->execute([$id]);
            $insp = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$insp) jsonError('Inspection not found.', 404);

            // Items with photos
            $itemStmt = $db->prepare(
                'SELECT * FROM oretir_inspection_items WHERE inspection_id = ? ORDER BY sort_order ASC, id ASC'
            );
            $itemStmt->execute([$id]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch photos for all items
            $itemIds = array_column($items, 'id');
            if (!empty($itemIds)) {
                $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
                $photoStmt = $db->prepare(
                    "SELECT * FROM oretir_inspection_photos WHERE inspection_item_id IN ({$placeholders}) ORDER BY id ASC"
                );
                $photoStmt->execute($itemIds);
                $photos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);

                // Group photos by item
                $photoMap = [];
                foreach ($photos as $p) {
                    $photoMap[$p['inspection_item_id']][] = $p;
                }
                foreach ($items as &$item) {
                    $item['photos'] = $photoMap[$item['id']] ?? [];
                }
                unset($item);
            }

            $insp['items'] = $items;
            jsonSuccess($insp);
        }

        // List inspections for RO
        $roId = (int) ($_GET['ro_id'] ?? 0);
        if ($roId <= 0) jsonError('ro_id is required.');

        $stmt = $db->prepare(
            'SELECT i.*,
                (SELECT COUNT(*) FROM oretir_inspection_items WHERE inspection_id = i.id) as item_count,
                (SELECT COUNT(*) FROM oretir_inspection_items WHERE inspection_id = i.id AND condition_rating = \'red\') as red_count,
                (SELECT COUNT(*) FROM oretir_inspection_items WHERE inspection_id = i.id AND condition_rating = \'yellow\') as yellow_count
             FROM oretir_inspections i
             WHERE i.repair_order_id = ?
             ORDER BY i.created_at DESC'
        );
        $stmt->execute([$roId]);
        jsonSuccess($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ─── POST: Create inspection ─────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $roId = (int) ($data['repair_order_id'] ?? 0);
        if ($roId <= 0) jsonError('repair_order_id is required.');

        // Verify RO exists
        $roStmt = $db->prepare('SELECT id, vehicle_id FROM oretir_repair_orders WHERE id = ?');
        $roStmt->execute([$roId]);
        $ro = $roStmt->fetch(PDO::FETCH_ASSOC);
        if (!$ro) jsonError('Repair order not found.', 404);

        $inspectorId = !empty($data['inspector_employee_id']) ? (int) $data['inspector_employee_id'] : null;
        $notes = sanitize((string) ($data['notes'] ?? ''), 2000);

        // Generate customer view token
        $viewToken = bin2hex(random_bytes(32));

        $stmt = $db->prepare(
            'INSERT INTO oretir_inspections
                (repair_order_id, inspector_employee_id, status, customer_view_token, notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$roId, $inspectorId, 'draft', $viewToken, $notes ?: null]);
        $inspId = (int) $db->lastInsertId();

        // Auto-populate template items (standard DVI categories)
        $templateItems = [
            ['tires', 'Left Front Tire', 'LF', 0],
            ['tires', 'Right Front Tire', 'RF', 1],
            ['tires', 'Left Rear Tire', 'LR', 2],
            ['tires', 'Right Rear Tire', 'RR', 3],
            ['tires', 'Spare Tire', 'spare', 4],
            ['brakes', 'Front Brake Pads', 'front', 10],
            ['brakes', 'Rear Brake Pads', 'rear', 11],
            ['brakes', 'Front Rotors', 'front', 12],
            ['brakes', 'Rear Rotors', 'rear', 13],
            ['suspension', 'Front Struts/Shocks', 'front', 20],
            ['suspension', 'Rear Struts/Shocks', 'rear', 21],
            ['suspension', 'Tie Rods', null, 22],
            ['suspension', 'Ball Joints', null, 23],
            ['fluids', 'Engine Oil', null, 30],
            ['fluids', 'Coolant', null, 31],
            ['fluids', 'Brake Fluid', null, 32],
            ['fluids', 'Transmission Fluid', null, 33],
            ['fluids', 'Power Steering Fluid', null, 34],
            ['lights', 'Headlights', null, 40],
            ['lights', 'Tail Lights', null, 41],
            ['lights', 'Brake Lights', null, 42],
            ['lights', 'Turn Signals', null, 43],
            ['engine', 'Air Filter', null, 50],
            ['engine', 'Cabin Air Filter', null, 51],
            ['engine', 'Spark Plugs', null, 52],
            ['exhaust', 'Exhaust System', null, 60],
            ['exhaust', 'Catalytic Converter', null, 61],
            ['hoses', 'Coolant Hoses', null, 70],
            ['hoses', 'Heater Hoses', null, 71],
            ['belts', 'Serpentine Belt', null, 80],
            ['belts', 'Timing Belt/Chain', null, 81],
            ['battery', 'Battery', null, 90],
            ['battery', 'Battery Terminals', null, 91],
            ['wipers', 'Front Wipers', 'front', 100],
            ['wipers', 'Rear Wiper', 'rear', 101],
        ];

        $itemStmt = $db->prepare(
            'INSERT INTO oretir_inspection_items
                (inspection_id, category, label, position, condition_rating, sort_order, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        foreach ($templateItems as [$cat, $label, $pos, $order]) {
            $itemStmt->execute([$inspId, $cat, $label, $pos, 'green', $order]);
        }

        // Update RO status to diagnosis if currently at intake
        $db->prepare("UPDATE oretir_repair_orders SET status = 'diagnosis', updated_at = NOW() WHERE id = ? AND status = 'intake'")->execute([$roId]);

        jsonSuccess([
            'id'                 => $inspId,
            'customer_view_token' => $viewToken,
            'item_count'         => count($templateItems),
            'message'            => 'Inspection created with template items.',
        ]);
    }

    // ─── PUT: Update inspection ──────────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Inspection ID is required.');

        $inspStmt = $db->prepare('SELECT * FROM oretir_inspections WHERE id = ?');
        $inspStmt->execute([$id]);
        $insp = $inspStmt->fetch(PDO::FETCH_ASSOC);
        if (!$insp) jsonError('Inspection not found.', 404);

        // ── Action: update_items (bulk update item ratings/measurements/notes) ──
        if (!empty($data['items']) && is_array($data['items'])) {
            $updateItem = $db->prepare(
                'UPDATE oretir_inspection_items
                 SET condition_rating = ?, measurement = ?, notes = ?
                 WHERE id = ? AND inspection_id = ?'
            );
            foreach ($data['items'] as $item) {
                $itemId = (int) ($item['id'] ?? 0);
                if ($itemId <= 0) continue;
                $rating = sanitize((string) ($item['condition_rating'] ?? 'green'), 10);
                if (!in_array($rating, ['green', 'yellow', 'red'], true)) $rating = 'green';
                $measurement = sanitize((string) ($item['measurement'] ?? ''), 50);
                $notes = sanitize((string) ($item['notes'] ?? ''), 2000);
                $updateItem->execute([$rating, $measurement ?: null, $notes ?: null, $itemId, $id]);
            }
        }

        // ── Action: add_item (add a custom inspection item) ──
        if (!empty($data['add_item'])) {
            $ai = $data['add_item'];
            $allowedCats = ['tires','brakes','suspension','fluids','lights','engine','exhaust','hoses','belts','battery','wipers','other'];
            $cat = sanitize((string) ($ai['category'] ?? 'other'), 20);
            if (!in_array($cat, $allowedCats, true)) $cat = 'other';

            $label = sanitize((string) ($ai['label'] ?? ''), 200);
            if (empty($label)) jsonError('Item label is required.');

            $pos = sanitize((string) ($ai['position'] ?? ''), 20);
            $rating = sanitize((string) ($ai['condition_rating'] ?? 'green'), 10);
            if (!in_array($rating, ['green', 'yellow', 'red'], true)) $rating = 'green';

            // Get max sort_order
            $maxStmt = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM oretir_inspection_items WHERE inspection_id = ?');
            $maxStmt->execute([$id]);
            $nextOrder = (int) $maxStmt->fetchColumn();

            $db->prepare(
                'INSERT INTO oretir_inspection_items
                    (inspection_id, category, label, position, condition_rating, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())'
            )->execute([$id, $cat, $label, $pos ?: null, $rating, $nextOrder]);
        }

        // ── Action: remove_item ──
        if (!empty($data['remove_item_id'])) {
            $removeId = (int) $data['remove_item_id'];
            $db->prepare('DELETE FROM oretir_inspection_items WHERE id = ? AND inspection_id = ?')->execute([$removeId, $id]);
        }

        // ── Update inspection-level fields ──
        $fields = [];
        $params = [];

        if (isset($data['notes'])) {
            $fields[] = 'notes = ?';
            $params[] = sanitize((string) $data['notes'], 2000) ?: null;
        }
        if (isset($data['overall_condition'])) {
            $oc = sanitize((string) $data['overall_condition'], 10);
            if (in_array($oc, ['green', 'yellow', 'red'], true)) {
                $fields[] = 'overall_condition = ?';
                $params[] = $oc;
            }
        }
        if (isset($data['inspector_employee_id'])) {
            $fields[] = 'inspector_employee_id = ?';
            $params[] = $data['inspector_employee_id'] !== '' && $data['inspector_employee_id'] !== null ? (int) $data['inspector_employee_id'] : null;
        }

        // ── Action: complete ──
        if (!empty($data['action']) && $data['action'] === 'complete') {
            // Auto-calculate overall condition from items
            $ratingStmt = $db->prepare(
                'SELECT condition_rating, COUNT(*) as cnt FROM oretir_inspection_items WHERE inspection_id = ? GROUP BY condition_rating'
            );
            $ratingStmt->execute([$id]);
            $ratings = $ratingStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $redCount = (int) ($ratings['red'] ?? 0);
            $yellowCount = (int) ($ratings['yellow'] ?? 0);

            if ($redCount > 0) {
                $overall = 'red';
            } elseif ($yellowCount > 0) {
                $overall = 'yellow';
            } else {
                $overall = 'green';
            }

            $fields[] = 'status = ?';
            $params[] = 'completed';
            $fields[] = 'overall_condition = ?';
            $params[] = $overall;

            // Update RO status
            $db->prepare("UPDATE oretir_repair_orders SET status = 'estimate_pending', updated_at = NOW() WHERE id = ? AND status = 'diagnosis'")->execute([$insp['repair_order_id']]);
        }

        // ── Action: send (to customer) ──
        if (!empty($data['action']) && $data['action'] === 'send') {
            $fields[] = 'status = ?';
            $params[] = 'sent';

            // Send email notification
            if (function_exists('sendBrandedTemplateEmail')) {
                $roStmt = $db->prepare(
                    'SELECT r.ro_number, c.first_name, c.last_name, c.email, c.language,
                            v.year, v.make, v.model
                     FROM oretir_repair_orders r
                     JOIN oretir_customers c ON c.id = r.customer_id
                     LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                     WHERE r.id = ?'
                );
                $roStmt->execute([$insp['repair_order_id']]);
                $roData = $roStmt->fetch(PDO::FETCH_ASSOC);

                if ($roData) {
                    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
                    $viewUrl = $baseUrl . '/inspection.php?token=' . urlencode($insp['customer_view_token']);
                    $vehicleStr = trim(($roData['year'] ?? '') . ' ' . ($roData['make'] ?? '') . ' ' . ($roData['model'] ?? ''));
                    $custName = trim($roData['first_name'] . ' ' . $roData['last_name']);
                    $lang = ($roData['language'] === 'spanish') ? 'es' : 'en';

                    require_once __DIR__ . '/../../includes/mail.php';
                    sendBrandedTemplateEmail(
                        $roData['email'],
                        'inspection',
                        ['name' => $custName, 'ro_number' => $roData['ro_number'], 'vehicle' => $vehicleStr],
                        $lang,
                        $viewUrl
                    );

                    // Try SMS
                    if (function_exists('sendInspectionSms')) {
                        require_once __DIR__ . '/../../includes/sms.php';
                        sendInspectionSms($roData['phone'] ?? '', $custName, $viewUrl, $lang);
                    }
                }
            }
        }

        // Status change (manual)
        if (isset($data['status']) && empty($data['action'])) {
            $newStatus = sanitize((string) $data['status'], 20);
            if (in_array($newStatus, ['draft', 'in_progress', 'completed', 'sent'], true)) {
                $fields[] = 'status = ?';
                $params[] = $newStatus;
            }
        }

        if (!empty($fields)) {
            $fields[] = 'updated_at = NOW()';
            $params[] = $id;
            $db->prepare('UPDATE oretir_inspections SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
        }

        jsonSuccess(['message' => 'Inspection updated.']);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/inspections.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
