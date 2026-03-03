<?php
/**
 * Oregon Tires — JSON Response Helpers
 */

declare(strict_types=1);

// Set standard API headers on API responses only
$_scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
if (!headers_sent() && (str_contains($_scriptName, '/api/') || str_contains($_scriptName, '/cli/'))) {
    header('X-API-Version: v1');
    header('X-Request-ID: ' . bin2hex(random_bytes(8)));
}

/**
 * Check if the current request was made by HTMX.
 */
function isHtmxRequest(): bool
{
    return !empty($_SERVER['HTTP_HX_REQUEST']);
}

/**
 * Render an HTMX error alert and exit.
 */
function htmxError(string $message, int $code = 400): never
{
    http_response_code($code);
    header('Content-Type: text/html; charset=utf-8');
    echo '<div role="alert" class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm font-medium">'
       . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
       . '</div>';
    exit;
}

/**
 * Send a JSON success response and exit.
 */
function jsonSuccess(mixed $data = null, int $code = 200): never
{
    http_response_code($code);
    $response = ['success' => true];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send a JSON error response and exit.
 */
function jsonError(string $message, int $code = 400): never
{
    if (isHtmxRequest()) {
        htmxError($message, $code);
    }
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error'   => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send a JSON list response with pagination metadata.
 */
function jsonList(array $items, int $total, int $page = 1, int $perPage = 20): never
{
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $items,
        'meta'    => [
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / $perPage),
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Require specific HTTP method(s).
 */
function requireMethod(string ...$methods): void
{
    $current = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($current, $methods, true)) {
        http_response_code(405);
        header('Allow: ' . implode(', ', $methods));
        echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
        exit;
    }
}
