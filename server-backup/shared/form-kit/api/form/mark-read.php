<?php
declare(strict_types=1);

/**
 * POST /api/form/mark-read.php
 * Mark submissions as read.
 *
 * Body options:
 *   {"id": 123}                                — Mark single submission read
 *   {"site_key": "oregon.tires", "mark_all": true} — Mark all as read for site
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

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

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Option 1: Mark all for a site
    if (!empty($input['mark_all'])) {
        $siteKey = $input['site_key'] ?? FormManager::getConfig('site_key') ?? '';

        if (empty($siteKey)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'site_key is required when using mark_all']);
            exit;
        }

        $count = FormSubmission::markAllRead($siteKey);
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }

    // Option 2: Mark single submission
    $id = (int) ($input['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id is required (or use mark_all with site_key)']);
        exit;
    }

    $result = ['success' => FormSubmission::markRead($id)];

    if (!$result['success']) {
        http_response_code(422);
    }

    echo json_encode($result);
} catch (\Throwable $e) {
    error_log('Form mark-read error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
