<?php
/**
 * Oregon Tires — Public Invoice View API
 * GET /api/invoice-view.php?token=XXXXX — Returns invoice data for customer view
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/invoices.php';

try {
    requireMethod('GET');

    $token = sanitize((string) ($_GET['token'] ?? ''), 64);
    if (empty($token)) {
        jsonError('Token is required.');
    }

    $db = getDB();

    $invoice = getInvoiceByToken($db, $token);

    if (!$invoice) {
        jsonError('Invoice not found or link expired.', 404);
    }

    // Mark as viewed on first access (only if currently 'sent')
    if ($invoice['status'] === 'sent') {
        $db->prepare("UPDATE oretir_invoices SET status = 'viewed', updated_at = NOW() WHERE id = ? AND status = 'sent'")
           ->execute([$invoice['id']]);
        $invoice['status'] = 'viewed';
    }

    // Check for linked inspection and estimate tokens
    $inspToken = null;
    $inspStmt = $db->prepare(
        'SELECT customer_view_token FROM oretir_inspections WHERE repair_order_id = ? ORDER BY created_at DESC LIMIT 1'
    );
    $inspStmt->execute([$invoice['repair_order_id']]);
    $inspRow = $inspStmt->fetch(PDO::FETCH_ASSOC);
    if ($inspRow) {
        $inspToken = $inspRow['customer_view_token'];
    }

    $estToken = null;
    if ($invoice['estimate_id']) {
        $estStmt = $db->prepare('SELECT approval_token FROM oretir_estimates WHERE id = ?');
        $estStmt->execute([$invoice['estimate_id']]);
        $estRow = $estStmt->fetch(PDO::FETCH_ASSOC);
        if ($estRow) {
            $estToken = $estRow['approval_token'];
        }
    }

    $result = [
        'invoice_number'    => $invoice['invoice_number'],
        'ro_number'         => $invoice['ro_number'],
        'customer_name'     => trim($invoice['first_name'] . ' ' . $invoice['last_name']),
        'customer_email'    => $invoice['customer_email'],
        'customer_language' => $invoice['customer_language'],
        'vehicle'           => trim(($invoice['vehicle_year'] ?? '') . ' ' . ($invoice['vehicle_make'] ?? '') . ' ' . ($invoice['vehicle_model'] ?? '')),
        'vehicle_color'     => $invoice['vehicle_color'] ?? null,
        'license_plate'     => $invoice['license_plate'] ?? null,
        'vin'               => $invoice['vin'] ?? null,
        'subtotal'          => $invoice['subtotal'],
        'tax_rate'          => $invoice['tax_rate'],
        'tax_amount'        => $invoice['tax_amount'],
        'discount_amount'   => $invoice['discount_amount'],
        'total'             => $invoice['total'],
        'status'            => $invoice['status'],
        'payment_method'    => $invoice['payment_method'],
        'paid_at'           => $invoice['paid_at'],
        'due_date'          => $invoice['due_date'],
        'notes'             => $invoice['notes'],
        'created_at'        => $invoice['created_at'],
        'items'             => $invoice['items'],
        'inspection_token'  => $inspToken,
        'estimate_token'    => $estToken,
    ];

    jsonSuccess($result);

} catch (\Throwable $e) {
    error_log("Oregon Tires api/invoice-view.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
