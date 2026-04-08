<?php
declare(strict_types=1);

/**
 * GET /api/member/login-activity.php
 * Retrieve login history for the authenticated member
 *
 * Query parameters:
 *   limit=20 — Results per page (max 100)
 *   offset=0 — Pagination offset
 *   filter=all|success|failed — Filter by status
 *
 * Response:
 *   { "success": true, "data": [...], "total": 42 }
 */

// Bootstrap: skip if already loaded by a site wrapper
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('MEMBER_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}
initSession();
MemberAuth::init(getDatabase());

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Require authentication
    $member = MemberAuth::requireAuth();
    $memberId = (int) $member[MemberAuth::getMemberIdColumn()];

    // Parse pagination parameters
    $limit = min((int) ($_GET['limit'] ?? 20), 100);
    $offset = max((int) ($_GET['offset'] ?? 0), 0);
    $filter = trim($_GET['filter'] ?? 'all');

    if ($limit < 1) {
        $limit = 20;
    }
    if ($offset < 0) {
        $offset = 0;
    }

    $pdo = getDatabase();
    $prefix = MemberAuth::prefixedTable('');

    // Build query
    $whereClause = 'WHERE member_id = ?';
    $params = [$memberId];

    if ($filter === 'success') {
        $whereClause .= ' AND success = TRUE';
    } elseif ($filter === 'failed') {
        $whereClause .= ' AND success = FALSE';
    }

    // Get total count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM {$prefix}login_activity
        {$whereClause}
    ");
    $stmt->execute($params);
    $countResult = $stmt->fetch();
    $total = (int) ($countResult['total'] ?? 0);

    // Get activity records
    $stmt = $pdo->prepare("
        SELECT
            id,
            login_method,
            ip_address,
            user_agent,
            device_fingerprint,
            success,
            failure_reason,
            created_at,
            updated_at
        FROM {$prefix}login_activity
        {$whereClause}
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$limit, $offset]));
    $activities = $stmt->fetchAll();

    // Format response
    $formattedActivities = [];
    foreach ($activities as $activity) {
        $formattedActivities[] = [
            'id' => (int) $activity['id'],
            'login_method' => $activity['login_method'] ?? 'unknown',
            'ip_address' => $activity['ip_address'] ?? 'unknown',
            'user_agent' => $activity['user_agent'] ?? null,
            'device_fingerprint' => $activity['device_fingerprint'] ?? null,
            'success' => (bool) $activity['success'],
            'failure_reason' => $activity['failure_reason'] ?? null,
            'timestamp' => $activity['created_at'] ?? null, 'geo_location' => $activity['geo_location'] ?? null,
        ];
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $formattedActivities,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'pages' => (int) ceil($total / $limit),
        ],
    ]);

} catch (\RuntimeException $e) {
    // Authentication failed
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log('Login activity error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not retrieve activity']);
}
