<?php
/**
 * Commerce Kit — Dashboard Stats API
 *
 * GET /api/commerce/stats.php?site_key=X — Revenue, order count, conversion stats
 */
declare(strict_types=1);

require_once __DIR__ . '/../../loader.php';

// If $pdo is not yet set (standalone mode), load test bootstrap
if (!isset($pdo)) {
    $testBootstrap = __DIR__ . '/../../tests/test-bootstrap.php';
    if (file_exists($testBootstrap)) {
        require_once $testBootstrap;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database not configured']);
        exit;
    }
}

header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['user_id']) && empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$siteKey = $_GET['site_key'] ?? '';
if (!$siteKey) {
    echo json_encode(['success' => false, 'error' => 'site_key required']);
    exit;
}

try {
    // Total revenue (completed orders)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_orders,
               COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as total_revenue,
               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
               SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
               SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) as refunded_orders
        FROM commerce_orders WHERE site_key = ?
    ");
    $stmt->execute([$siteKey]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Revenue last 30 days
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total), 0) as revenue_30d,
               COUNT(*) as orders_30d
        FROM commerce_orders
        WHERE site_key = ? AND status = 'completed' AND paid_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$siteKey]);
    $recent = $stmt->fetch(PDO::FETCH_ASSOC);

    // Revenue last 7 days
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total), 0) as revenue_7d,
               COUNT(*) as orders_7d
        FROM commerce_orders
        WHERE site_key = ? AND status = 'completed' AND paid_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$siteKey]);
    $week = $stmt->fetch(PDO::FETCH_ASSOC);

    // Conversion rate
    $totalOrders = (int) $totals['total_orders'];
    $completedOrders = (int) $totals['completed_orders'];
    $conversionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_revenue'    => round((float) $totals['total_revenue'], 2),
            'total_orders'     => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders'   => (int) $totals['pending_orders'],
            'cancelled_orders' => (int) $totals['cancelled_orders'],
            'refunded_orders'  => (int) $totals['refunded_orders'],
            'conversion_rate'  => $conversionRate,
            'revenue_30d'      => round((float) $recent['revenue_30d'], 2),
            'orders_30d'       => (int) $recent['orders_30d'],
            'revenue_7d'       => round((float) $week['revenue_7d'], 2),
            'orders_7d'        => (int) $week['orders_7d'],
        ],
    ]);
} catch (\Throwable $e) {
    error_log("[Commerce API] stats.php error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
