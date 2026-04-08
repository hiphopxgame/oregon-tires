<?php
declare(strict_types=1);

/**
 * GET /api/form/stats.php?site_key=X
 * Returns submission stats for admin dashboard.
 */

// Bootstrap guard
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('FORM_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}

initSession();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Auth check — require admin session (supports multiple naming conventions)
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin']) && empty($_SESSION['is_admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

try {
    if (!FormManager::getConfig('site_key')) {
        FormManager::init(getDatabase());
    }

    $siteKey = $_GET['site_key'] ?? FormManager::getConfig('site_key') ?? '';

    if (empty($siteKey)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'site_key is required']);
        exit;
    }

    $total = FormSubmission::count($siteKey);
    $unread = FormSubmission::count($siteKey, ['status' => 'new']);
    $thisWeek = 0;

    // Count this week's submissions
    $pdo = FormManager::getPdo();
    $table = FormManager::submissionsTable();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM `{$table}`
        WHERE site_key = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$siteKey]);
    $thisWeek = (int) $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats'   => [
            'total'  => $total,
            'unread' => $unread,
            'week'   => $thisWeek,
        ],
    ]);
} catch (\Throwable $e) {
    error_log('Form stats error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
