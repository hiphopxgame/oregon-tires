<?php
/**
 * Commerce Kit — Orders REST API
 *
 * GET  /api/commerce/orders.php           — List orders (requires site_key, optional status filter)
 * GET  /api/commerce/orders.php?ref=X     — Get single order
 * POST /api/commerce/orders.php           — Update order status (requires ref, status)
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

// Simple session auth check (sites should wrap this with their own auth)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['user_id']) && empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $ref = $_GET['ref'] ?? '';
        if ($ref) {
            $order = CommerceOrder::get($pdo, $ref);
            echo json_encode(['success' => (bool) $order, 'order' => $order]);
        } else {
            $siteKey = $_GET['site_key'] ?? '';
            if (!$siteKey) {
                echo json_encode(['success' => false, 'error' => 'site_key required']);
                exit;
            }
            $filters = [];
            if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
            if (!empty($_GET['limit'])) $filters['limit'] = (int) $_GET['limit'];
            if (!empty($_GET['offset'])) $filters['offset'] = (int) $_GET['offset'];
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

            $orders = CommerceOrder::list($pdo, $siteKey, $filters);
            echo json_encode(['success' => true, 'orders' => $orders, 'count' => count($orders)]);
        }
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $ref = $input['ref'] ?? '';
        $status = $input['status'] ?? '';

        if (!$ref || !$status) {
            echo json_encode(['success' => false, 'error' => 'ref and status required']);
            exit;
        }

        $result = CommerceOrder::updateStatus($pdo, $ref, $status);
        echo json_encode($result);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (\Throwable $e) {
    error_log("[Commerce API] orders.php error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
