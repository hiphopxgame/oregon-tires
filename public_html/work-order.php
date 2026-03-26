<?php
/**
 * Oregon Tires — Print-Ready Work Order
 * Renders a clean, printable work order for a given RO.
 * Requires admin/employee session.
 * GET /work-order?ro_id=N
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();
$staff = requireStaff();

$db = getDB();
$roId = (int) ($_GET['ro_id'] ?? 0);
if ($roId <= 0) {
    http_response_code(400);
    echo 'Missing ro_id parameter.';
    exit;
}

// Fetch RO with customer + vehicle
$stmt = $db->prepare(
    'SELECT r.*,
        c.first_name, c.last_name, c.email AS customer_email, c.phone AS customer_phone,
        v.vin, v.year AS vehicle_year, v.make AS vehicle_make, v.model AS vehicle_model,
        v.trim_level, v.engine, v.transmission, v.drive_type,
        v.license_plate, v.color AS vehicle_color, v.mileage AS vehicle_mileage
     FROM oretir_repair_orders r
     JOIN oretir_customers c ON c.id = r.customer_id
     LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
     WHERE r.id = ?'
);
$stmt->execute([$roId]);
$ro = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ro) {
    http_response_code(404);
    echo 'Repair order not found.';
    exit;
}

$customer = trim(($ro['first_name'] ?? '') . ' ' . ($ro['last_name'] ?? ''));
$vehicle = trim(implode(' ', array_filter([$ro['vehicle_year'], $ro['vehicle_make'], $ro['vehicle_model']]))) ?: 'N/A';

// Fetch inspection items (if any)
$inspectionItems = [];
$inspStmt = $db->prepare('SELECT id, status FROM oretir_inspections WHERE repair_order_id = ? ORDER BY created_at DESC LIMIT 1');
$inspStmt->execute([$roId]);
$inspection = $inspStmt->fetch(PDO::FETCH_ASSOC);
if ($inspection) {
    $iiStmt = $db->prepare('SELECT * FROM oretir_inspection_items WHERE inspection_id = ? ORDER BY sort_order, id');
    $iiStmt->execute([$inspection['id']]);
    $inspectionItems = $iiStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch estimate items (if any)
$estimateItems = [];
$estTotal = '0.00';
$estStmt = $db->prepare('SELECT id, estimate_number, subtotal, tax_amount, total FROM oretir_estimates WHERE repair_order_id = ? ORDER BY version DESC LIMIT 1');
$estStmt->execute([$roId]);
$estimate = $estStmt->fetch(PDO::FETCH_ASSOC);
if ($estimate) {
    $eiStmt = $db->prepare('SELECT * FROM oretir_estimate_items WHERE estimate_id = ? ORDER BY sort_order, id');
    $eiStmt->execute([$estimate['id']]);
    $estimateItems = $eiStmt->fetchAll(PDO::FETCH_ASSOC);
    $estTotal = $estimate['total'];
}

// Linked appointment
$appointment = null;
if (!empty($ro['appointment_id'])) {
    $aStmt = $db->prepare('SELECT service, preferred_date, preferred_time FROM oretir_appointments WHERE id = ?');
    $aStmt->execute([$ro['appointment_id']]);
    $appointment = $aStmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once __DIR__ . "/includes/gtag.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order <?= htmlspecialchars($ro['ro_number']) ?> - Oregon Tires Auto Care</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        /* Reset */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            background: #fff;
            padding: 20px;
        }

        /* Print button (hidden in print) */
        .print-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            background: #15803d;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .print-btn:hover { background: #166534; }

        /* Page layout */
        .work-order { max-width: 800px; margin: 0 auto; }

        /* Header */
        .wo-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #15803d;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .shop-name { font-size: 20px; font-weight: 700; color: #15803d; }
        .shop-info { font-size: 11px; color: #555; line-height: 1.6; }
        .ro-label { font-size: 18px; font-weight: 700; text-align: right; }
        .ro-date { font-size: 11px; color: #555; text-align: right; }

        /* Info grids */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        .info-box {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }
        .info-box h3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #15803d;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }
        .info-row { display: flex; margin-bottom: 2px; }
        .info-label { font-weight: 600; min-width: 80px; color: #555; }
        .info-value { flex: 1; }

        /* Concern */
        .concern-box {
            border: 2px solid #f59e0b;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 16px;
            background: #fffbeb;
        }
        .concern-box h3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #b45309;
            margin-bottom: 4px;
        }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
        th {
            background: #f3f4f6;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #ddd;
            color: #374151;
        }
        td { padding: 5px 8px; border: 1px solid #ddd; vertical-align: top; }

        /* Traffic light colors */
        .rating-green { background: #dcfce7; color: #166534; font-weight: 600; }
        .rating-yellow { background: #fef9c3; color: #854d0e; font-weight: 600; }
        .rating-red { background: #fecaca; color: #991b1b; font-weight: 600; }

        /* Estimate totals */
        .totals-row td { font-weight: 700; border-top: 2px solid #000; }
        .text-right { text-align: right; }

        /* Technician notes area */
        .notes-section {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 16px;
        }
        .notes-section h3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #15803d;
            margin-bottom: 8px;
        }
        .note-lines {
            border-top: 1px solid #ddd;
        }
        .note-line {
            border-bottom: 1px solid #eee;
            height: 24px;
        }
        .note-content { white-space: pre-wrap; font-size: 11px; margin-bottom: 8px; }

        /* Footer */
        .wo-footer {
            border-top: 2px solid #15803d;
            padding-top: 8px;
            margin-top: 20px;
            font-size: 9px;
            color: #666;
            text-align: center;
        }

        /* Signatures */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 24px;
            margin-bottom: 16px;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }
        .sig-label { font-size: 9px; color: #666; }

        /* Print styles */
        @media print {
            body { padding: 0; font-size: 11px; }
            .print-btn { display: none !important; }
            .work-order { max-width: none; }
            .wo-header { border-bottom-color: #000; }
            .shop-name { color: #000; }
            .info-box h3, .concern-box h3, .notes-section h3 { color: #000; }
            .wo-footer { border-top-color: #000; }
            @page {
                margin: 0.5in;
                size: letter;
            }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">Print Work Order</button>

<div class="work-order">

    <!-- Shop Header -->
    <div class="wo-header">
        <div>
            <div class="shop-name">Oregon Tires Auto Care</div>
            <div class="shop-info">
                5630 SE 82nd Ave, Portland, OR 97266<br>
                (503) 788-4680 &bull; info@oregon.tires
            </div>
        </div>
        <div>
            <div class="ro-label"><?= htmlspecialchars($ro['ro_number']) ?></div>
            <div class="ro-date">
                Date: <?= date('m/d/Y', strtotime($ro['created_at'])) ?><br>
                <?php if ($ro['promised_date']): ?>
                    Promised: <?= date('m/d/Y', strtotime($ro['promised_date'])) ?>
                    <?= $ro['promised_time'] ? htmlspecialchars($ro['promised_time']) : '' ?>
                <?php endif; ?>
            </div>
            <div class="ro-date" style="margin-top:4px;">
                Status: <?= ucwords(str_replace('_', ' ', $ro['status'])) ?>
            </div>
        </div>
    </div>

    <!-- Customer & Vehicle Info -->
    <div class="info-grid">
        <div class="info-box">
            <h3>Customer</h3>
            <div class="info-row"><span class="info-label">Name:</span><span class="info-value"><?= htmlspecialchars($customer) ?></span></div>
            <div class="info-row"><span class="info-label">Phone:</span><span class="info-value"><?= htmlspecialchars($ro['customer_phone'] ?? '-') ?></span></div>
            <div class="info-row"><span class="info-label">Email:</span><span class="info-value"><?= htmlspecialchars($ro['customer_email'] ?? '-') ?></span></div>
        </div>
        <div class="info-box">
            <h3>Vehicle</h3>
            <div class="info-row"><span class="info-label">Vehicle:</span><span class="info-value"><?= htmlspecialchars($vehicle) ?><?= $ro['trim_level'] ? ' ' . htmlspecialchars($ro['trim_level']) : '' ?></span></div>
            <div class="info-row"><span class="info-label">VIN:</span><span class="info-value"><?= htmlspecialchars($ro['vin'] ?? '-') ?></span></div>
            <div class="info-row"><span class="info-label">Plate:</span><span class="info-value"><?= htmlspecialchars($ro['license_plate'] ?? '-') ?></span></div>
            <div class="info-row"><span class="info-label">Mileage In:</span><span class="info-value"><?= $ro['mileage_in'] ? number_format((int) $ro['mileage_in']) : '-' ?></span></div>
            <?php if ($ro['vehicle_color']): ?>
            <div class="info-row"><span class="info-label">Color:</span><span class="info-value"><?= htmlspecialchars($ro['vehicle_color']) ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($appointment): ?>
    <div class="info-box" style="margin-bottom:16px;">
        <h3>Appointment</h3>
        <div class="info-row">
            <span class="info-label">Service:</span>
            <span class="info-value"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $appointment['service']))) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Scheduled:</span>
            <span class="info-value"><?= date('m/d/Y', strtotime($appointment['preferred_date'])) ?> <?= htmlspecialchars($appointment['preferred_time'] ?? '') ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Customer Concern -->
    <?php if ($ro['customer_concern']): ?>
    <div class="concern-box">
        <h3>Customer Concern</h3>
        <?= htmlspecialchars($ro['customer_concern']) ?>
    </div>
    <?php endif; ?>

    <!-- Inspection Items -->
    <?php if ($inspectionItems): ?>
    <h3 style="font-size:12px;font-weight:700;margin-bottom:6px;">Inspection Results</h3>
    <table>
        <thead>
            <tr>
                <th style="width:25%">Category</th>
                <th style="width:30%">Item</th>
                <th style="width:10%">Condition</th>
                <th style="width:15%">Measurement</th>
                <th style="width:20%">Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inspectionItems as $item): ?>
            <tr>
                <td><?= htmlspecialchars(ucwords($item['category'])) ?></td>
                <td><?= htmlspecialchars($item['label']) ?><?= $item['position'] ? ' (' . htmlspecialchars($item['position']) . ')' : '' ?></td>
                <td class="rating-<?= htmlspecialchars($item['condition_rating']) ?>"><?= ucfirst($item['condition_rating']) ?></td>
                <td><?= htmlspecialchars($item['measurement'] ?? '-') ?></td>
                <td><?= htmlspecialchars($item['notes'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Estimate Items -->
    <?php if ($estimateItems): ?>
    <h3 style="font-size:12px;font-weight:700;margin-bottom:6px;">Estimate<?= $estimate ? ' (' . htmlspecialchars($estimate['estimate_number']) . ')' : '' ?></h3>
    <table>
        <thead>
            <tr>
                <th style="width:10%">Type</th>
                <th style="width:45%">Description</th>
                <th style="width:10%">Qty</th>
                <th class="text-right" style="width:15%">Unit Price</th>
                <th class="text-right" style="width:20%">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($estimateItems as $ei): ?>
            <tr>
                <td><?= ucfirst($ei['item_type']) ?></td>
                <td><?= htmlspecialchars($ei['description']) ?></td>
                <td><?= $ei['quantity'] ?></td>
                <td class="text-right">$<?= number_format((float) $ei['unit_price'], 2) ?></td>
                <td class="text-right">$<?= number_format((float) $ei['total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="text-right" style="font-weight:600;">Subtotal:</td>
                <td class="text-right" style="font-weight:600;">$<?= number_format((float) ($estimate['subtotal'] ?? 0), 2) ?></td>
            </tr>
            <?php if ((float) ($estimate['tax_amount'] ?? 0) > 0): ?>
            <tr>
                <td colspan="4" class="text-right" style="font-weight:600;">Tax:</td>
                <td class="text-right" style="font-weight:600;">$<?= number_format((float) $estimate['tax_amount'], 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="totals-row">
                <td colspan="4" class="text-right">Total:</td>
                <td class="text-right">$<?= number_format((float) $estTotal, 2) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Technician Notes -->
    <div class="notes-section">
        <h3>Technician Notes</h3>
        <?php if ($ro['technician_notes']): ?>
        <div class="note-content"><?= htmlspecialchars($ro['technician_notes']) ?></div>
        <?php endif; ?>
        <div class="note-lines">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="note-line"></div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Admin Notes (if any) -->
    <?php if ($ro['admin_notes']): ?>
    <div class="notes-section">
        <h3>Internal Notes</h3>
        <div class="note-content"><?= htmlspecialchars($ro['admin_notes']) ?></div>
    </div>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="signature-grid">
        <div>
            <div class="sig-line"></div>
            <div class="sig-label">Technician Signature / Date</div>
        </div>
        <div>
            <div class="sig-line"></div>
            <div class="sig-label">Customer Signature / Date</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="wo-footer">
        Oregon Tires Auto Care &bull; 5630 SE 82nd Ave, Portland, OR 97266 &bull; (503) 788-4680 &bull; oregon.tires<br>
        All work is subject to our standard terms and conditions. Estimates are valid for 30 days. Customer authorization required for work exceeding estimate.
    </div>

</div>

<script>
// Auto-trigger print dialog on page load
window.addEventListener('load', function() {
    // Brief delay to ensure rendering is complete
    setTimeout(function() { window.print(); }, 500);
});
</script>

</body>
</html>
