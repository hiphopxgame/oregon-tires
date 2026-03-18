<?php
/**
 * Oregon Tires — Admin Invoice Management
 * GET    /api/admin/invoices.php?ro_id=N   — List invoices for an RO
 * GET    /api/admin/invoices.php?id=N      — Get single invoice with items
 * POST   /api/admin/invoices.php           — Create invoice from estimate
 * PUT    /api/admin/invoices.php           — Update invoice (status, payment, notes)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/invoices.php';

try {
    startSecureSession();
    $staff = requireStaff();
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List or single invoice ────────────────────────────────────
    if ($method === 'GET') {

        // Single invoice detail
        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $invoice = getInvoiceWithItems($db, $id);
            if (!$invoice) {
                jsonError('Invoice not found.', 404);
            }
            jsonSuccess($invoice);
        }

        // List invoices for an RO
        if (!empty($_GET['ro_id'])) {
            $roId = (int) $_GET['ro_id'];
            $stmt = $db->prepare(
                'SELECT inv.*, c.first_name, c.last_name, c.email as customer_email
                 FROM oretir_invoices inv
                 JOIN oretir_customers c ON c.id = inv.customer_id
                 WHERE inv.repair_order_id = ?
                 ORDER BY inv.created_at DESC'
            );
            $stmt->execute([$roId]);
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonSuccess($invoices);
        }

        // List all invoices (paginated)
        $limit  = max(1, min(500, (int) ($_GET['limit'] ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $status = sanitize((string) ($_GET['status'] ?? ''), 20);
        $search = sanitize((string) ($_GET['search'] ?? ''), 200);

        $where = 'WHERE 1=1';
        $params = [];

        $allowedStatuses = ['draft', 'sent', 'viewed', 'paid', 'overdue', 'void'];
        if ($status !== '' && in_array($status, $allowedStatuses, true)) {
            $where .= ' AND inv.status = ?';
            $params[] = $status;
        }

        if (!empty($search)) {
            $where .= ' AND (inv.invoice_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR r.ro_number LIKE ?)';
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        $countSql = "SELECT COUNT(*) FROM oretir_invoices inv
                     JOIN oretir_customers c ON c.id = inv.customer_id
                     JOIN oretir_repair_orders r ON r.id = inv.repair_order_id
                     {$where}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT inv.*, c.first_name, c.last_name, c.email as customer_email,
                    r.ro_number,
                    v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model
                FROM oretir_invoices inv
                JOIN oretir_customers c ON c.id = inv.customer_id
                JOIN oretir_repair_orders r ON r.id = inv.repair_order_id
                LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
                {$where}
                ORDER BY inv.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page = (int) floor($offset / $limit) + 1;
        jsonList($invoices, $total, $page, $limit);
    }

    // ─── POST: Create invoice from estimate ─────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $roId = (int) ($data['ro_id'] ?? 0);
        if ($roId <= 0) {
            jsonError('ro_id is required.');
        }

        // Verify RO exists
        $roStmt = $db->prepare('SELECT * FROM oretir_repair_orders WHERE id = ?');
        $roStmt->execute([$roId]);
        $ro = $roStmt->fetch(PDO::FETCH_ASSOC);
        if (!$ro) {
            jsonError('Repair order not found.', 404);
        }

        $result = createInvoiceFromEstimate($db, $roId);
        if (!$result) {
            jsonError('No approved estimate found for this repair order. Create and approve an estimate first.');
        }

        jsonSuccess([
            'invoice_id'     => $result['invoice_id'],
            'invoice_number' => $result['invoice_number'],
            'message'        => 'Invoice created successfully.',
        ]);
    }

    // ─── PUT: Update invoice ────────────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            jsonError('Invoice ID is required.');
        }

        // Verify invoice exists
        $invStmt = $db->prepare('SELECT * FROM oretir_invoices WHERE id = ?');
        $invStmt->execute([$id]);
        $invoice = $invStmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) {
            jsonError('Invoice not found.', 404);
        }

        $fields = [];
        $params = [];

        // Status update
        if (isset($data['status'])) {
            $newStatus = sanitize((string) $data['status'], 20);
            $allowedStatuses = ['draft', 'sent', 'viewed', 'paid', 'overdue', 'void'];
            if (!in_array($newStatus, $allowedStatuses, true)) {
                jsonError('Invalid status value.');
            }
            $fields[] = 'status = ?';
            $params[] = $newStatus;

            // If marking as paid, set paid_at
            if ($newStatus === 'paid') {
                $fields[] = 'paid_at = NOW()';
            }

            // If marking as sent, send email to customer
            if ($newStatus === 'sent' && $invoice['status'] !== 'sent') {
                try {
                    require_once __DIR__ . '/../../includes/mail.php';

                    $inv = getInvoiceWithItems($db, $id);
                    if ($inv && !empty($inv['customer_email'])) {
                        $custName = trim($inv['first_name'] . ' ' . $inv['last_name']);
                        $vehicle = trim(($inv['vehicle_year'] ?? '') . ' ' . ($inv['vehicle_make'] ?? '') . ' ' . ($inv['vehicle_model'] ?? ''));
                        $lang = ($inv['customer_language'] ?? 'english') === 'spanish' ? 'es' : 'en';
                        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');
                        $viewUrl = $baseUrl . '/invoice/' . $inv['customer_view_token'];

                        sendInvoiceEmail(
                            $inv['customer_email'],
                            $custName,
                            $inv['ro_number'],
                            $vehicle,
                            '$' . number_format((float) $inv['total'], 2),
                            $inv['invoice_number'],
                            $viewUrl,
                            $lang
                        );
                    }
                } catch (\Throwable $e) {
                    error_log("admin/invoices.php: Failed to send invoice email for #{$id}: " . $e->getMessage());
                }
            }
        }

        // Payment method
        if (isset($data['payment_method'])) {
            $method = sanitize((string) $data['payment_method'], 20);
            $allowedMethods = ['cash', 'card', 'check', 'paypal', 'other', ''];
            if (!in_array($method, $allowedMethods, true)) {
                jsonError('Invalid payment method.');
            }
            $fields[] = 'payment_method = ?';
            $params[] = $method ?: null;
        }

        // Payment reference
        if (isset($data['payment_reference'])) {
            $fields[] = 'payment_reference = ?';
            $params[] = sanitize((string) $data['payment_reference'], 100) ?: null;
        }

        // Notes
        if (isset($data['notes'])) {
            $fields[] = 'notes = ?';
            $params[] = sanitize((string) $data['notes'], 2000) ?: null;
        }

        // Due date
        if (isset($data['due_date'])) {
            $fields[] = 'due_date = ?';
            $params[] = sanitize((string) $data['due_date'], 10) ?: null;
        }

        // Tax rate override
        if (isset($data['tax_rate'])) {
            $taxRate = (float) $data['tax_rate'];
            $fields[] = 'tax_rate = ?';
            $params[] = $taxRate;

            // Recalculate tax and total
            $taxableAmount = (float) $invoice['subtotal'] - (float) $invoice['discount_amount'];
            $newTaxAmount = round($taxableAmount * $taxRate, 2);
            $newTotal = round($taxableAmount + $newTaxAmount, 2);
            $fields[] = 'tax_amount = ?';
            $params[] = $newTaxAmount;
            $fields[] = 'total = ?';
            $params[] = $newTotal;
        }

        if (empty($fields)) {
            jsonError('No fields to update.');
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $db->prepare('UPDATE oretir_invoices SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);

        jsonSuccess(['message' => 'Invoice updated.']);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/invoices.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
