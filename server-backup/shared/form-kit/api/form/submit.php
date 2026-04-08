<?php
declare(strict_types=1);

/**
 * POST /api/form/submit.php
 * Universal form submission endpoint.
 *
 * Accepts JSON body with form data. The site_key can come from
 * the request body or from FormManager config (set by site wrapper).
 *
 * Rate-limited via FormRateLimiter.
 */

// Bootstrap guard — skip if site wrapper already loaded
if (!function_exists('getDatabase')) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!defined('FORM_KIT_PATH')) {
    require_once __DIR__ . '/../../loader.php';
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Start session for CSRF validation
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

try {
    // Init if not already initialized by site wrapper
    if (!FormManager::getConfig('site_key')) {
        FormManager::init(getDatabase());
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Validate CSRF token (if session has one, it must match; if no session token, skip)
    $csrfToken = $input['_csrf_token'] ?? '';
    if (!empty($_SESSION['form_kit_csrf'])) {
        if (!FormRenderer::validateCsrf($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired form token. Please refresh and try again.']);
            exit;
        }
    }

    $siteKey = $input['site_key'] ?? FormManager::getConfig('site_key') ?? '';

    if (empty($siteKey)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'site_key is required']);
        exit;
    }

    // Rate limiting is handled inside FormSubmission::create()
    $result = FormSubmission::create($siteKey, $input);

    if (!$result['success']) {
        http_response_code(422);
    }

    echo json_encode($result);
} catch (\Throwable $e) {
    error_log('Form submit error: ' . $e->getMessage());

    // Rate limit exceeded gets a 429
    if (str_contains($e->getMessage(), 'Rate limit') || str_contains($e->getMessage(), 'rate limit') || str_contains($e->getMessage(), 'Too many')) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'Too many submissions. Please try again later.']);
        exit;
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
