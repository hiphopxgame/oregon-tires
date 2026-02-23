<?php
/**
 * Oregon Tires — Public Inspection View API
 * GET /api/inspection-view.php?token=XXXXX — Returns inspection data for customer view
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

try {
    requireMethod('GET');

    $token = sanitize((string) ($_GET['token'] ?? ''), 64);
    if (empty($token)) jsonError('Token is required.');

    $db = getDB();

    // Find inspection by token
    $stmt = $db->prepare(
        'SELECT i.*, r.ro_number, r.customer_id, r.vehicle_id,
            c.first_name, c.last_name, c.language,
            v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model,
            v.vin, v.color as vehicle_color, v.license_plate
         FROM oretir_inspections i
         JOIN oretir_repair_orders r ON r.id = i.repair_order_id
         JOIN oretir_customers c ON c.id = r.customer_id
         LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
         WHERE i.customer_view_token = ?'
    );
    $stmt->execute([$token]);
    $insp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$insp) jsonError('Inspection not found or link expired.', 404);

    // Mark as viewed (first view only)
    if (empty($insp['customer_viewed_at'])) {
        $db->prepare('UPDATE oretir_inspections SET customer_viewed_at = NOW() WHERE id = ?')->execute([$insp['id']]);
    }

    // Get items with photos
    $itemStmt = $db->prepare(
        'SELECT id, category, label, position, condition_rating, measurement, notes, sort_order
         FROM oretir_inspection_items WHERE inspection_id = ? ORDER BY sort_order ASC, id ASC'
    );
    $itemStmt->execute([$insp['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch photos
    $itemIds = array_column($items, 'id');
    $photoMap = [];
    if (!empty($itemIds)) {
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $photoStmt = $db->prepare(
            "SELECT id, inspection_item_id, image_url, caption
             FROM oretir_inspection_photos WHERE inspection_item_id IN ({$placeholders}) ORDER BY id ASC"
        );
        $photoStmt->execute($itemIds);
        foreach ($photoStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $photoMap[$p['inspection_item_id']][] = $p;
        }
    }

    foreach ($items as &$item) {
        $item['photos'] = $photoMap[$item['id']] ?? [];
    }
    unset($item);

    // Count ratings
    $greenCount = count(array_filter($items, fn($i) => $i['condition_rating'] === 'green'));
    $yellowCount = count(array_filter($items, fn($i) => $i['condition_rating'] === 'yellow'));
    $redCount = count(array_filter($items, fn($i) => $i['condition_rating'] === 'red'));

    // Check for linked estimate
    $estStmt = $db->prepare(
        "SELECT e.approval_token FROM oretir_estimates e
         WHERE e.repair_order_id = ? AND e.status NOT IN ('superseded','expired')
         ORDER BY e.version DESC LIMIT 1"
    );
    $estStmt->execute([$insp['repair_order_id']]);
    $estRow = $estStmt->fetch(PDO::FETCH_ASSOC);

    $result = [
        'customer_name'     => trim($insp['first_name'] . ' ' . $insp['last_name']),
        'customer_language' => $insp['language'],
        'ro_number'         => $insp['ro_number'],
        'vehicle'           => trim(($insp['vehicle_year'] ?? '') . ' ' . ($insp['vehicle_make'] ?? '') . ' ' . ($insp['vehicle_model'] ?? '')),
        'vehicle_color'     => $insp['vehicle_color'] ?? null,
        'license_plate'     => $insp['license_plate'] ?? null,
        'vin'               => $insp['vin'] ?? null,
        'overall_condition'  => $insp['overall_condition'],
        'notes'             => $insp['notes'],
        'status'            => $insp['status'],
        'created_at'        => $insp['created_at'],
        'green_count'       => $greenCount,
        'yellow_count'      => $yellowCount,
        'red_count'         => $redCount,
        'items'             => $items,
        'estimate_token'    => $estRow['approval_token'] ?? null,
    ];

    jsonSuccess($result);

} catch (\Throwable $e) {
    error_log("Oregon Tires api/inspection-view.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
