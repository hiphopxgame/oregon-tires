<?php
/**
 * Oregon Tires — Public Estimate Approval API
 * GET  /api/estimate-approve.php?token=XXXXX       — Get estimate for customer view
 * POST /api/estimate-approve.php                    — Submit customer approval/decline
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET', 'POST');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: View estimate ──────────────────────────────────────────────
    if ($method === 'GET') {
        $token = sanitize((string) ($_GET['token'] ?? ''), 64);
        if (empty($token)) jsonError('Token is required.');

        $stmt = $db->prepare(
            'SELECT e.*, r.ro_number,
                c.first_name, c.last_name, c.email as customer_email, c.language,
                v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model,
                v.vin, v.color as vehicle_color
             FROM oretir_estimates e
             JOIN oretir_repair_orders r ON r.id = e.repair_order_id
             JOIN oretir_customers c ON c.id = r.customer_id
             LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
             WHERE e.approval_token = ?'
        );
        $stmt->execute([$token]);
        $est = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$est) jsonError('Estimate not found or link expired.', 404);

        // Check expiry
        if ($est['valid_until'] && strtotime($est['valid_until']) < time()) {
            $db->prepare("UPDATE oretir_estimates SET status = 'expired', updated_at = NOW() WHERE id = ? AND status NOT IN ('expired','superseded')")->execute([$est['id']]);
            jsonError('This estimate has expired. Please contact us for a new estimate.', 410);
        }

        // Mark as viewed (first view only)
        if (empty($est['customer_viewed_at'])) {
            $db->prepare("UPDATE oretir_estimates SET customer_viewed_at = NOW(), status = 'viewed', updated_at = NOW() WHERE id = ? AND status = 'sent'")->execute([$est['id']]);
        }

        // Get items
        $itemStmt = $db->prepare(
            'SELECT id, item_type, description, quantity, unit_price, total, is_approved, sort_order
             FROM oretir_estimate_items WHERE estimate_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $itemStmt->execute([$est['id']]);

        $result = [
            'customer_name'    => trim($est['first_name'] . ' ' . $est['last_name']),
            'customer_language' => $est['language'],
            'ro_number'        => $est['ro_number'],
            'estimate_number'  => $est['estimate_number'],
            'vehicle'          => trim(($est['vehicle_year'] ?? '') . ' ' . ($est['vehicle_make'] ?? '') . ' ' . ($est['vehicle_model'] ?? '')),
            'vehicle_color'    => $est['vehicle_color'] ?? null,
            'status'           => $est['status'],
            'subtotal'         => $est['subtotal'],
            'tax_rate'         => $est['tax_rate'],
            'tax_amount'       => $est['tax_amount'],
            'total'            => $est['total'],
            'notes'            => $est['notes'],
            'valid_until'      => $est['valid_until'],
            'created_at'       => $est['created_at'],
            'items'            => $itemStmt->fetchAll(PDO::FETCH_ASSOC),
            'can_respond'      => in_array($est['status'], ['sent', 'viewed'], true),
        ];

        jsonSuccess($result);
    }

    // ─── POST: Submit approval ───────────────────────────────────────────
    if ($method === 'POST') {
        $data = getJsonBody();

        $token = sanitize((string) ($data['token'] ?? ''), 64);
        if (empty($token)) jsonError('Token is required.');

        $stmt = $db->prepare(
            'SELECT e.*, r.id as ro_id, r.ro_number,
                c.first_name, c.last_name, c.email, c.phone, c.language,
                v.year, v.make, v.model
             FROM oretir_estimates e
             JOIN oretir_repair_orders r ON r.id = e.repair_order_id
             JOIN oretir_customers c ON c.id = r.customer_id
             LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
             WHERE e.approval_token = ?'
        );
        $stmt->execute([$token]);
        $est = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$est) jsonError('Estimate not found.', 404);

        // Only allow response on sent/viewed estimates
        if (!in_array($est['status'], ['sent', 'viewed'], true)) {
            jsonError('This estimate has already been responded to.', 409);
        }

        // Check expiry
        if ($est['valid_until'] && strtotime($est['valid_until']) < time()) {
            jsonError('This estimate has expired.', 410);
        }

        // Process per-item approvals
        $approvals = $data['approvals'] ?? [];
        if (!is_array($approvals) || empty($approvals)) {
            jsonError('Please approve or decline at least one item.');
        }

        $approvedCount = 0;
        $declinedCount = 0;

        $updateItem = $db->prepare('UPDATE oretir_estimate_items SET is_approved = ? WHERE id = ? AND estimate_id = ?');

        foreach ($approvals as $itemId => $approved) {
            $itemId = (int) $itemId;
            $isApproved = $approved ? 1 : 0;
            $updateItem->execute([$isApproved, $itemId, $est['id']]);

            if ($isApproved) {
                $approvedCount++;
            } else {
                $declinedCount++;
            }
        }

        // Determine overall status
        $totalItems = $approvedCount + $declinedCount;
        if ($approvedCount === $totalItems) {
            $newStatus = 'approved';
        } elseif ($approvedCount > 0) {
            $newStatus = 'partial';
        } else {
            $newStatus = 'declined';
        }

        // Recalculate approved total
        $approvedTotal = $db->prepare(
            'SELECT COALESCE(SUM(total), 0) FROM oretir_estimate_items WHERE estimate_id = ? AND is_approved = 1'
        );
        $approvedTotal->execute([$est['id']]);
        $approvedSubtotal = (float) $approvedTotal->fetchColumn();

        $taxAmount = round($approvedSubtotal * (float) $est['tax_rate'], 2);
        $total = round($approvedSubtotal + $taxAmount, 2);

        $db->prepare(
            "UPDATE oretir_estimates SET status = ?, customer_responded_at = NOW(), subtotal = ?, tax_amount = ?, total = ?, updated_at = NOW() WHERE id = ?"
        )->execute([$newStatus, $approvedSubtotal, $taxAmount, $total, $est['id']]);

        // Update RO status
        if ($newStatus === 'approved' || $newStatus === 'partial') {
            $db->prepare("UPDATE oretir_repair_orders SET status = 'approved', updated_at = NOW() WHERE id = ? AND status = 'pending_approval'")->execute([$est['ro_id']]);
        }

        // Send confirmation email + SMS
        $vehicleStr = trim(($est['year'] ?? '') . ' ' . ($est['make'] ?? '') . ' ' . ($est['model'] ?? ''));
        $custName = trim($est['first_name'] . ' ' . $est['last_name']);
        $lang = ($est['language'] === 'spanish') ? 'es' : 'en';
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
        $viewUrl = $baseUrl . '/approve.php?token=' . urlencode($token);

        require_once __DIR__ . '/../includes/mail.php';
        sendBrandedTemplateEmail(
            $est['email'],
            'approval',
            [
                'name'      => $custName,
                'ro_number' => $est['ro_number'],
                'vehicle'   => $vehicleStr,
                'total'     => '$' . number_format($total, 2),
                'date'      => date('m/d/Y', strtotime('+2 days')),
            ],
            $lang,
            $viewUrl
        );

        require_once __DIR__ . '/../includes/sms.php';
        if (function_exists('sendApprovalConfirmationSms')) {
            sendApprovalConfirmationSms($est['phone'] ?? '', $custName, $est['ro_number'], $lang);
        }

        // Notify shop owner
        notifyOwner(
            "Estimate {$newStatus}: {$est['ro_number']} — {$custName}",
            "<p><strong>{$custName}</strong> has {$newStatus} estimate <strong>{$est['estimate_number']}</strong> for RO <strong>{$est['ro_number']}</strong>.</p>"
            . "<p>Approved: {$approvedCount} items | Declined: {$declinedCount} items | Total: \${$total}</p>"
        );

        jsonSuccess([
            'status'         => $newStatus,
            'approved_count' => $approvedCount,
            'declined_count' => $declinedCount,
            'approved_total' => number_format($total, 2),
            'message'        => $newStatus === 'approved'
                ? 'All services approved. We will begin work shortly.'
                : ($newStatus === 'partial'
                    ? 'Selected services approved. We will begin on the approved items.'
                    : 'Services declined. Contact us if you change your mind.'),
        ]);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/estimate-approve.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
