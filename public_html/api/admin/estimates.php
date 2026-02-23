<?php
/**
 * Oregon Tires — Admin Estimate Management
 * GET    /api/admin/estimates.php?ro_id=N     — List estimates for RO
 * GET    /api/admin/estimates.php?id=N        — Get single estimate with items
 * POST   /api/admin/estimates.php             — Create estimate (manual or from inspection)
 * PUT    /api/admin/estimates.php             — Update estimate (items, totals, send, etc.)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/vin-decode.php';

try {
    startSecureSession();
    $admin = requireAdmin();
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET ─────────────────────────────────────────────────────────────
    if ($method === 'GET') {

        // Single estimate with items
        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $db->prepare(
                'SELECT e.*, r.ro_number, r.customer_id,
                    c.first_name, c.last_name, c.email as customer_email, c.language as customer_language,
                    v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model, v.vin
                 FROM oretir_estimates e
                 JOIN oretir_repair_orders r ON r.id = e.repair_order_id
                 JOIN oretir_customers c ON c.id = r.customer_id
                 LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                 WHERE e.id = ?'
            );
            $stmt->execute([$id]);
            $est = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$est) jsonError('Estimate not found.', 404);

            $itemStmt = $db->prepare('SELECT * FROM oretir_estimate_items WHERE estimate_id = ? ORDER BY sort_order ASC, id ASC');
            $itemStmt->execute([$id]);
            $est['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            jsonSuccess($est);
        }

        // List estimates for RO
        $roId = (int) ($_GET['ro_id'] ?? 0);
        if ($roId <= 0) jsonError('ro_id is required.');

        $stmt = $db->prepare(
            'SELECT e.*,
                (SELECT COUNT(*) FROM oretir_estimate_items WHERE estimate_id = e.id) as item_count,
                (SELECT COUNT(*) FROM oretir_estimate_items WHERE estimate_id = e.id AND is_approved = 1) as approved_count
             FROM oretir_estimates e
             WHERE e.repair_order_id = ?
             ORDER BY e.version DESC'
        );
        $stmt->execute([$roId]);
        jsonSuccess($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ─── POST: Create estimate ───────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $roId = (int) ($data['repair_order_id'] ?? 0);
        if ($roId <= 0) jsonError('repair_order_id is required.');

        // Verify RO exists
        $roStmt = $db->prepare('SELECT id FROM oretir_repair_orders WHERE id = ?');
        $roStmt->execute([$roId]);
        if (!$roStmt->fetch()) jsonError('Repair order not found.', 404);

        // Supersede any existing draft/sent estimates for this RO
        $existingStmt = $db->prepare("SELECT id, version FROM oretir_estimates WHERE repair_order_id = ? AND status NOT IN ('superseded','expired') ORDER BY version DESC LIMIT 1");
        $existingStmt->execute([$roId]);
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

        $version = 1;
        if ($existing) {
            $version = (int) $existing['version'] + 1;
            $db->prepare("UPDATE oretir_estimates SET status = 'superseded', updated_at = NOW() WHERE id = ?")->execute([$existing['id']]);
        }

        $estNumber = generateEstimateNumber($db);
        $approvalToken = bin2hex(random_bytes(32));
        $taxRate = (float) ($data['tax_rate'] ?? 0.0000);
        $notes = sanitize((string) ($data['notes'] ?? ''), 2000);
        $validUntil = sanitize((string) ($data['valid_until'] ?? ''), 10);

        // Default valid_until to 30 days from now
        if (empty($validUntil)) {
            $validUntil = date('Y-m-d', strtotime('+30 days'));
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_estimates
                (repair_order_id, estimate_number, version, status, approval_token,
                 subtotal, tax_rate, tax_amount, total, notes, valid_until, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 0.00, ?, 0.00, 0.00, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$roId, $estNumber, $version, 'draft', $approvalToken, $taxRate, $notes ?: null, $validUntil]);
        $estId = (int) $db->lastInsertId();

        // ── Auto-generate items from inspection (red + yellow items) ──
        if (!empty($data['from_inspection_id'])) {
            $inspId = (int) $data['from_inspection_id'];
            $inspItems = $db->prepare(
                "SELECT * FROM oretir_inspection_items
                 WHERE inspection_id = ? AND condition_rating IN ('red', 'yellow')
                 ORDER BY sort_order ASC"
            );
            $inspItems->execute([$inspId]);
            $redYellowItems = $inspItems->fetchAll(PDO::FETCH_ASSOC);

            $insertItem = $db->prepare(
                'INSERT INTO oretir_estimate_items
                    (estimate_id, inspection_item_id, item_type, description, quantity, unit_price, total, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );

            $order = 0;
            foreach ($redYellowItems as $ii) {
                $desc = $ii['label'];
                if ($ii['condition_rating'] === 'red') {
                    $desc .= ' [URGENT]';
                }
                if ($ii['measurement']) {
                    $desc .= ' — ' . $ii['measurement'];
                }
                if ($ii['notes']) {
                    $desc .= ': ' . $ii['notes'];
                }
                $type = ($ii['category'] === 'tires') ? 'tire' : 'labor';
                $insertItem->execute([$estId, $ii['id'], $type, $desc, 1.00, 0.00, 0.00, $order++]);
            }
        }

        // ── Manual items (passed in request body) ──
        if (!empty($data['items']) && is_array($data['items'])) {
            $insertItem = $db->prepare(
                'INSERT INTO oretir_estimate_items
                    (estimate_id, item_type, description, quantity, unit_price, total, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
            );

            // Get max sort_order so far
            $maxOrd = $db->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM oretir_estimate_items WHERE estimate_id = ?');
            $maxOrd->execute([$estId]);
            $order = (int) $maxOrd->fetchColumn();

            foreach ($data['items'] as $item) {
                $type = sanitize((string) ($item['item_type'] ?? 'labor'), 20);
                $allowedTypes = ['labor', 'parts', 'tire', 'fee', 'discount', 'sublet'];
                if (!in_array($type, $allowedTypes, true)) $type = 'labor';

                $desc     = sanitize((string) ($item['description'] ?? ''), 500);
                $qty      = max(0.01, (float) ($item['quantity'] ?? 1));
                $price    = (float) ($item['unit_price'] ?? 0);
                $lineTotal = round($qty * $price, 2);

                if ($type === 'discount') {
                    $lineTotal = -abs($lineTotal);
                }

                $insertItem->execute([$estId, $type, $desc, $qty, $price, $lineTotal, $order++]);
            }
        }

        // Recalculate totals
        recalculateEstimateTotals($estId, $db);

        jsonSuccess([
            'id'              => $estId,
            'estimate_number' => $estNumber,
            'version'         => $version,
            'approval_token'  => $approvalToken,
            'message'         => 'Estimate created.',
        ]);
    }

    // ─── PUT: Update estimate ────────────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Estimate ID is required.');

        $estStmt = $db->prepare('SELECT * FROM oretir_estimates WHERE id = ?');
        $estStmt->execute([$id]);
        $est = $estStmt->fetch(PDO::FETCH_ASSOC);
        if (!$est) jsonError('Estimate not found.', 404);

        // ── Update items (bulk) ──
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $itemId = (int) ($item['id'] ?? 0);
                if ($itemId <= 0) continue;

                $updateFields = [];
                $updateParams = [];

                if (isset($item['description'])) {
                    $updateFields[] = 'description = ?';
                    $updateParams[] = sanitize((string) $item['description'], 500);
                }
                if (isset($item['quantity'])) {
                    $updateFields[] = 'quantity = ?';
                    $updateParams[] = max(0.01, (float) $item['quantity']);
                }
                if (isset($item['unit_price'])) {
                    $updateFields[] = 'unit_price = ?';
                    $updateParams[] = (float) $item['unit_price'];
                }
                if (isset($item['item_type'])) {
                    $type = sanitize((string) $item['item_type'], 20);
                    $allowedTypes = ['labor', 'parts', 'tire', 'fee', 'discount', 'sublet'];
                    if (in_array($type, $allowedTypes, true)) {
                        $updateFields[] = 'item_type = ?';
                        $updateParams[] = $type;
                    }
                }

                // Recalculate line total
                if (isset($item['quantity']) || isset($item['unit_price'])) {
                    $qty   = (float) ($item['quantity'] ?? 1);
                    $price = (float) ($item['unit_price'] ?? 0);
                    $lineTotal = round($qty * $price, 2);
                    $curType = $item['item_type'] ?? '';
                    if ($curType === 'discount') $lineTotal = -abs($lineTotal);
                    $updateFields[] = 'total = ?';
                    $updateParams[] = $lineTotal;
                }

                if (!empty($updateFields)) {
                    $updateParams[] = $itemId;
                    $updateParams[] = $id;
                    $db->prepare(
                        'UPDATE oretir_estimate_items SET ' . implode(', ', $updateFields) . ' WHERE id = ? AND estimate_id = ?'
                    )->execute($updateParams);
                }
            }
        }

        // ── Add item ──
        if (!empty($data['add_item'])) {
            $ai = $data['add_item'];
            $type = sanitize((string) ($ai['item_type'] ?? 'labor'), 20);
            $allowedTypes = ['labor', 'parts', 'tire', 'fee', 'discount', 'sublet'];
            if (!in_array($type, $allowedTypes, true)) $type = 'labor';

            $desc     = sanitize((string) ($ai['description'] ?? ''), 500);
            $qty      = max(0.01, (float) ($ai['quantity'] ?? 1));
            $price    = (float) ($ai['unit_price'] ?? 0);
            $lineTotal = round($qty * $price, 2);
            if ($type === 'discount') $lineTotal = -abs($lineTotal);

            $maxOrd = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM oretir_estimate_items WHERE estimate_id = ?');
            $maxOrd->execute([$id]);
            $order = (int) $maxOrd->fetchColumn();

            $db->prepare(
                'INSERT INTO oretir_estimate_items
                    (estimate_id, item_type, description, quantity, unit_price, total, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
            )->execute([$id, $type, $desc, $qty, $price, $lineTotal, $order]);
        }

        // ── Remove item ──
        if (!empty($data['remove_item_id'])) {
            $removeId = (int) $data['remove_item_id'];
            $db->prepare('DELETE FROM oretir_estimate_items WHERE id = ? AND estimate_id = ?')->execute([$removeId, $id]);
        }

        // ── Update estimate-level fields ──
        $fields = [];
        $params = [];

        if (isset($data['notes'])) {
            $fields[] = 'notes = ?';
            $params[] = sanitize((string) $data['notes'], 2000) ?: null;
        }
        if (isset($data['tax_rate'])) {
            $fields[] = 'tax_rate = ?';
            $params[] = (float) $data['tax_rate'];
        }
        if (isset($data['valid_until'])) {
            $fields[] = 'valid_until = ?';
            $params[] = sanitize((string) $data['valid_until'], 10) ?: null;
        }

        // ── Action: send ──
        if (!empty($data['action']) && $data['action'] === 'send') {
            $fields[] = 'status = ?';
            $params[] = 'sent';

            // Send email
            $roStmt = $db->prepare(
                'SELECT r.ro_number, r.id as ro_id, c.first_name, c.last_name, c.email, c.phone, c.language,
                        v.year, v.make, v.model
                 FROM oretir_repair_orders r
                 JOIN oretir_customers c ON c.id = r.customer_id
                 LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                 WHERE r.id = ?'
            );
            $roStmt->execute([$est['repair_order_id']]);
            $roData = $roStmt->fetch(PDO::FETCH_ASSOC);

            if ($roData) {
                $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
                $approveUrl = $baseUrl . '/approve.php?token=' . urlencode($est['approval_token']);
                $vehicleStr = trim(($roData['year'] ?? '') . ' ' . ($roData['make'] ?? '') . ' ' . ($roData['model'] ?? ''));
                $custName = trim($roData['first_name'] . ' ' . $roData['last_name']);
                $lang = ($roData['language'] === 'spanish') ? 'es' : 'en';

                // Recalculate totals before sending
                recalculateEstimateTotals($id, $db);
                $freshEst = $db->prepare('SELECT total FROM oretir_estimates WHERE id = ?');
                $freshEst->execute([$id]);
                $currentTotal = $freshEst->fetchColumn();

                require_once __DIR__ . '/../../includes/mail.php';
                sendBrandedTemplateEmail(
                    $roData['email'],
                    'estimate',
                    [
                        'name'      => $custName,
                        'ro_number' => $roData['ro_number'],
                        'vehicle'   => $vehicleStr,
                        'total'     => '$' . number_format((float) $currentTotal, 2),
                    ],
                    $lang,
                    $approveUrl
                );

                // Try SMS
                require_once __DIR__ . '/../../includes/sms.php';
                if (function_exists('sendEstimateSms')) {
                    sendEstimateSms(
                        $roData['phone'] ?? '',
                        $custName,
                        '$' . number_format((float) $currentTotal, 2),
                        $approveUrl,
                        $lang
                    );
                }

                // Update RO status
                $db->prepare("UPDATE oretir_repair_orders SET status = 'pending_approval', updated_at = NOW() WHERE id = ? AND status IN ('estimate_pending', 'diagnosis')")->execute([$est['repair_order_id']]);
            }
        }

        // ── Manual status change ──
        if (isset($data['status']) && empty($data['action'])) {
            $newStatus = sanitize((string) $data['status'], 20);
            $allowedStatuses = ['draft', 'sent', 'viewed', 'approved', 'partial', 'declined', 'expired', 'superseded'];
            if (in_array($newStatus, $allowedStatuses, true)) {
                $fields[] = 'status = ?';
                $params[] = $newStatus;
            }
        }

        if (!empty($fields)) {
            $fields[] = 'updated_at = NOW()';
            $params[] = $id;
            $db->prepare('UPDATE oretir_estimates SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
        }

        // Recalculate totals after any item changes
        recalculateEstimateTotals($id, $db);

        jsonSuccess(['message' => 'Estimate updated.']);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/estimates.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}

// ─── Helper: Recalculate estimate totals ─────────────────────────────────────

function recalculateEstimateTotals(int $estimateId, PDO $db): void
{
    $stmt = $db->prepare('SELECT COALESCE(SUM(total), 0) FROM oretir_estimate_items WHERE estimate_id = ?');
    $stmt->execute([$estimateId]);
    $subtotal = (float) $stmt->fetchColumn();

    $taxStmt = $db->prepare('SELECT tax_rate FROM oretir_estimates WHERE id = ?');
    $taxStmt->execute([$estimateId]);
    $taxRate = (float) $taxStmt->fetchColumn();

    $taxAmount = round($subtotal * $taxRate, 2);
    $total = round($subtotal + $taxAmount, 2);

    $db->prepare(
        'UPDATE oretir_estimates SET subtotal = ?, tax_amount = ?, total = ?, updated_at = NOW() WHERE id = ?'
    )->execute([$subtotal, $taxAmount, $total, $estimateId]);
}
