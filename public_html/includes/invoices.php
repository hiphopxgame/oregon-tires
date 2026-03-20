<?php
/**
 * Oregon Tires — Invoice Helpers
 * Functions for creating, querying, and emailing digital invoices.
 */

declare(strict_types=1);

/**
 * Generate a unique invoice number in INV-XXXXXXXX format.
 */
function generateInvoiceNumber(PDO $db): string
{
    $maxAttempts = 20;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $number = 'INV-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = $db->prepare('SELECT COUNT(*) FROM oretir_invoices WHERE invoice_number = ?');
        $stmt->execute([$number]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $number;
        }
    }
    throw new \RuntimeException('Failed to generate unique invoice number after ' . $maxAttempts . ' attempts.');
}

/**
 * Create an invoice from the approved estimate for a repair order.
 *
 * @return array{invoice_id: int, invoice_number: string}|null Null if no approved estimate found.
 */
function createInvoiceFromEstimate(PDO $db, int $roId): ?array
{
    // Find the approved estimate for this RO
    $estStmt = $db->prepare(
        "SELECT e.* FROM oretir_estimates e
         WHERE e.repair_order_id = ? AND e.status IN ('approved','partial')
         ORDER BY e.version DESC LIMIT 1"
    );
    $estStmt->execute([$roId]);
    $estimate = $estStmt->fetch(PDO::FETCH_ASSOC);

    if (!$estimate) {
        return null;
    }

    // Check if invoice already exists for this RO
    $existCheck = $db->prepare('SELECT id, invoice_number FROM oretir_invoices WHERE repair_order_id = ? LIMIT 1');
    $existCheck->execute([$roId]);
    $existing = $existCheck->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        return ['invoice_id' => (int) $existing['id'], 'invoice_number' => $existing['invoice_number']];
    }

    // Get RO for customer_id
    $roStmt = $db->prepare('SELECT customer_id FROM oretir_repair_orders WHERE id = ?');
    $roStmt->execute([$roId]);
    $ro = $roStmt->fetch(PDO::FETCH_ASSOC);
    if (!$ro) {
        return null;
    }

    // Get approved estimate items only
    $itemStmt = $db->prepare(
        "SELECT ei.* FROM oretir_estimate_items ei
         WHERE ei.estimate_id = ? AND (ei.is_approved = 1 OR ei.item_type = 'discount')
         ORDER BY ei.sort_order ASC, ei.id ASC"
    );
    $itemStmt->execute([$estimate['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        // Fallback: get all items if none have approved flag
        $itemStmt = $db->prepare(
            'SELECT ei.* FROM oretir_estimate_items ei
             WHERE ei.estimate_id = ?
             ORDER BY ei.sort_order ASC, ei.id ASC'
        );
        $itemStmt->execute([$estimate['id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Calculate totals
    $subtotal = 0.00;
    $discountAmount = 0.00;
    foreach ($items as $item) {
        $lineTotal = (float) ($item['total'] ?? ((float) $item['quantity'] * (float) $item['unit_price']));
        if ($item['item_type'] === 'discount') {
            $discountAmount += abs($lineTotal);
        } else {
            $subtotal += $lineTotal;
        }
    }

    $taxRate = (float) ($estimate['tax_rate'] ?? 0.0000);
    $taxableAmount = $subtotal - $discountAmount;
    $taxAmount = round($taxableAmount * $taxRate, 2);
    $total = round($taxableAmount + $taxAmount, 2);

    $invoiceNumber = generateInvoiceNumber($db);
    $token = bin2hex(random_bytes(32));

    $db->beginTransaction();
    try {
        $invStmt = $db->prepare(
            'INSERT INTO oretir_invoices
                (repair_order_id, invoice_number, estimate_id, customer_id, subtotal, tax_rate,
                 tax_amount, discount_amount, total, status, customer_view_token, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $invStmt->execute([
            $roId,
            $invoiceNumber,
            $estimate['id'],
            $ro['customer_id'],
            $subtotal,
            $taxRate,
            $taxAmount,
            $discountAmount,
            $total,
            'draft',
            $token,
        ]);
        $invoiceId = (int) $db->lastInsertId();

        // Copy items
        $insertItem = $db->prepare(
            'INSERT INTO oretir_invoice_items
                (invoice_id, estimate_item_id, item_type, description, quantity, unit_price, total, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $sortOrder = 0;
        foreach ($items as $item) {
            $lineTotal = (float) ($item['total'] ?? ((float) $item['quantity'] * (float) $item['unit_price']));
            $insertItem->execute([
                $invoiceId,
                $item['id'],
                $item['item_type'],
                $item['description'],
                $item['quantity'],
                $item['unit_price'],
                $lineTotal,
                $sortOrder++,
            ]);
        }

        $db->commit();
        return ['invoice_id' => $invoiceId, 'invoice_number' => $invoiceNumber];
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Create an invoice from any estimate (including draft) when no approved estimate exists.
 * Used as a fallback when completing an RO without going through the approval flow.
 *
 * @return array{invoice_id: int, invoice_number: string}|null
 */
function createInvoiceFromAnyEstimate(PDO $db, int $roId): ?array
{
    // Check if invoice already exists
    $existCheck = $db->prepare('SELECT id, invoice_number FROM oretir_invoices WHERE repair_order_id = ? LIMIT 1');
    $existCheck->execute([$roId]);
    $existing = $existCheck->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        return ['invoice_id' => (int) $existing['id'], 'invoice_number' => $existing['invoice_number']];
    }

    // Find the latest non-superseded estimate (any status)
    $estStmt = $db->prepare(
        "SELECT e.* FROM oretir_estimates e
         WHERE e.repair_order_id = ? AND e.status NOT IN ('superseded','expired')
         ORDER BY e.version DESC LIMIT 1"
    );
    $estStmt->execute([$roId]);
    $estimate = $estStmt->fetch(PDO::FETCH_ASSOC);

    if (!$estimate) {
        return null;
    }

    $roStmt = $db->prepare('SELECT customer_id FROM oretir_repair_orders WHERE id = ?');
    $roStmt->execute([$roId]);
    $ro = $roStmt->fetch(PDO::FETCH_ASSOC);
    if (!$ro) return null;

    // Get all items (no approval filter)
    $itemStmt = $db->prepare(
        'SELECT ei.* FROM oretir_estimate_items ei
         WHERE ei.estimate_id = ?
         ORDER BY ei.sort_order ASC, ei.id ASC'
    );
    $itemStmt->execute([$estimate['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($items)) return null;

    $subtotal = 0.00;
    $discountAmount = 0.00;
    foreach ($items as $item) {
        $lineTotal = (float) ($item['total'] ?? ((float) $item['quantity'] * (float) $item['unit_price']));
        if ($item['item_type'] === 'discount') {
            $discountAmount += abs($lineTotal);
        } else {
            $subtotal += $lineTotal;
        }
    }

    $taxRate = (float) ($estimate['tax_rate'] ?? 0.0000);
    $taxableAmount = $subtotal - $discountAmount;
    $taxAmount = round($taxableAmount * $taxRate, 2);
    $total = round($taxableAmount + $taxAmount, 2);

    $invoiceNumber = generateInvoiceNumber($db);
    $token = bin2hex(random_bytes(32));

    $db->beginTransaction();
    try {
        $invStmt = $db->prepare(
            'INSERT INTO oretir_invoices
                (repair_order_id, invoice_number, estimate_id, customer_id, subtotal, tax_rate,
                 tax_amount, discount_amount, total, status, customer_view_token, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $invStmt->execute([
            $roId, $invoiceNumber, $estimate['id'], $ro['customer_id'],
            $subtotal, $taxRate, $taxAmount, $discountAmount, $total, 'draft', $token,
        ]);
        $invoiceId = (int) $db->lastInsertId();

        $insertItem = $db->prepare(
            'INSERT INTO oretir_invoice_items
                (invoice_id, estimate_item_id, item_type, description, quantity, unit_price, total, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $sortOrder = 0;
        foreach ($items as $item) {
            $lineTotal = (float) ($item['total'] ?? ((float) $item['quantity'] * (float) $item['unit_price']));
            $insertItem->execute([
                $invoiceId, $item['id'], $item['item_type'], $item['description'],
                $item['quantity'], $item['unit_price'], $lineTotal, $sortOrder++,
            ]);
        }

        $db->commit();
        return ['invoice_id' => $invoiceId, 'invoice_number' => $invoiceNumber];
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Get an invoice with full details (customer, vehicle, RO) and line items.
 */
function getInvoiceWithItems(PDO $db, int $invoiceId): ?array
{
    $stmt = $db->prepare(
        'SELECT inv.*,
            r.ro_number, r.vehicle_id,
            c.first_name, c.last_name, c.email as customer_email, c.phone as customer_phone, c.language as customer_language,
            v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model,
            v.vin, v.color as vehicle_color, v.license_plate
         FROM oretir_invoices inv
         JOIN oretir_repair_orders r ON r.id = inv.repair_order_id
         JOIN oretir_customers c ON c.id = inv.customer_id
         LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
         WHERE inv.id = ?'
    );
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        return null;
    }

    // Get items
    $itemStmt = $db->prepare(
        'SELECT id, estimate_item_id, item_type, description, quantity, unit_price, total, sort_order
         FROM oretir_invoice_items WHERE invoice_id = ? ORDER BY sort_order ASC, id ASC'
    );
    $itemStmt->execute([$invoiceId]);
    $invoice['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    return $invoice;
}

/**
 * Get an invoice by customer view token with full details.
 */
function getInvoiceByToken(PDO $db, string $token): ?array
{
    $stmt = $db->prepare(
        'SELECT inv.*,
            r.ro_number, r.vehicle_id,
            c.first_name, c.last_name, c.email as customer_email, c.phone as customer_phone, c.language as customer_language,
            v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model,
            v.vin, v.color as vehicle_color, v.license_plate
         FROM oretir_invoices inv
         JOIN oretir_repair_orders r ON r.id = inv.repair_order_id
         JOIN oretir_customers c ON c.id = inv.customer_id
         LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
         WHERE inv.customer_view_token = ?'
    );
    $stmt->execute([$token]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        return null;
    }

    // Get items
    $itemStmt = $db->prepare(
        'SELECT id, estimate_item_id, item_type, description, quantity, unit_price, total, sort_order
         FROM oretir_invoice_items WHERE invoice_id = ? ORDER BY sort_order ASC, id ASC'
    );
    $itemStmt->execute([$invoice['id']]);
    $invoice['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    return $invoice;
}

/**
 * Send invoice email to customer using branded template system.
 */
function sendInvoiceEmail(
    string $email,
    string $name,
    string $roNumber,
    string $vehicle,
    string $total,
    string $invoiceNumber,
    string $viewUrl,
    string $language = 'both'
): array {
    $vars = [
        'name'           => $name,
        'ro_number'      => $roNumber,
        'vehicle'        => $vehicle,
        'total'          => $total,
        'invoice_number' => $invoiceNumber,
    ];

    $result = sendBrandedTemplateEmail($email, 'invoice', $vars, $language, $viewUrl);

    // Log the email
    logEmail(
        'invoice_sent',
        "Invoice {$invoiceNumber} sent to {$email} for RO {$roNumber}"
    );

    return $result;
}
