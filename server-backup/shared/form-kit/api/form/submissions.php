<?php
declare(strict_types=1);

/**
 * GET|POST /api/form/submissions.php
 * Admin endpoint for managing form submissions.
 *
 * GET  — List submissions (requires auth)
 *   Query params: site_key (required), status, form_type, search, limit, offset
 *
 * POST — Update submission (requires auth)
 *   Body: {"id": 123, "action": "mark_read"} or {"id": 123, "action": "delete"}
 */

// Bootstrap guard — skip if site wrapper already loaded
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('FORM_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}
initSession();

header('Content-Type: application/json');

// Auth check — require admin session (supports multiple naming conventions)
$isAuthed = !empty($_SESSION['admin_id'])
         || !empty($_SESSION['admin'])
         || !empty($_SESSION['is_admin']);

if (!$isAuthed) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

try {
    // Init if not already initialized by site wrapper
    if (!FormManager::getConfig('site_key')) {
        FormManager::init(getDatabase());
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        handleList();
    } elseif ($method === 'POST') {
        handleAction();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (\Throwable $e) {
    error_log('Form submissions error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * GET — List submissions with filters
 */
function handleList(): void
{
    $siteKey = $_GET['site_key'] ?? FormManager::getConfig('site_key') ?? '';

    if (empty($siteKey)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'site_key is required']);
        return;
    }

    $filters = [];

    if (!empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (!empty($_GET['form_type'])) {
        $filters['form_type'] = $_GET['form_type'];
    }
    if (!empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }

    $filters['limit']  = max(1, min(100, (int) ($_GET['limit'] ?? 50)));
    $filters['offset'] = max(0, (int) ($_GET['offset'] ?? 0));

    $submissions = FormSubmission::list($siteKey, $filters);
    $total = FormSubmission::count($siteKey, $filters);

    echo json_encode([
        'success'     => true,
        'submissions' => $submissions,
        'total'       => $total,
        'limit'       => $filters['limit'],
        'offset'      => $filters['offset'],
    ]);
}

/**
 * POST — Perform action on a submission
 */
function handleAction(): void
{
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $id     = (int) ($input['id'] ?? 0);
    $action = $input['action'] ?? '';

    if ($id <= 0 || empty($action)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id and action are required']);
        return;
    }

    $allowedActions = ['mark_read', 'delete'];
    if (!in_array($action, $allowedActions, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action. Allowed: ' . implode(', ', $allowedActions)]);
        return;
    }

    switch ($action) {
        case 'mark_read':
            $result = ['success' => FormSubmission::markRead($id)];
            break;

        case 'delete':
            $result = ['success' => FormSubmission::delete($id)];
            break;

        default:
            $result = ['success' => false, 'error' => 'Unknown action'];
    }

    if (!$result['success']) {
        http_response_code(422);
    }

    echo json_encode($result);
}
