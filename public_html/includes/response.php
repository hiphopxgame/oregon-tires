<?php
/**
 * Oregon Tires — JSON Response Helpers
 */

declare(strict_types=1);

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
