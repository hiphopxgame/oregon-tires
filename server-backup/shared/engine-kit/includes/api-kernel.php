<?php
/**
 * API Kernel — Lightweight middleware replacement for the 1vsM network.
 *
 * Reduces per-endpoint boilerplate from 10+ lines to a single call.
 *
 * Usage (in any API endpoint):
 *
 *   // Before (typical 10-line bootstrap):
 *   require_once __DIR__ . '/../../includes/bootstrap.php';
 *   handleCors();
 *   if ($_SERVER['REQUEST_METHOD'] !== 'GET') { jsonResponse(['error' => 'Method not allowed'], 405); }
 *   $user = requireApiAuth($pdo);
 *   requireCsrf();
 *
 *   // After (2 lines):
 *   require_once __DIR__ . '/../../includes/bootstrap.php';
 *   ['pdo' => $pdo, 'user' => $user] = apiBootstrap(['auth' => 'required', 'method' => 'GET']);
 *
 * Options:
 *   'auth'       => 'required' | 'optional' | 'admin' | 'none' (default: 'none')
 *   'csrf'       => true | false (default: true for mutating methods)
 *   'cors'       => true | false (default: true)
 *   'method'     => string | array (e.g. 'GET', ['GET', 'POST'], default: null = any)
 *   'rate_limit' => ['action' => string, 'max' => int, 'window' => int] (default: null)
 *   'site_key'   => string (for rate limiting, default: auto-detect)
 */

/**
 * Bootstrap an API endpoint with declarative middleware.
 *
 * @param array $options Middleware configuration
 * @return array ['pdo' => PDO, 'user' => ?array]
 */
function apiBootstrap(array $options = []): array
{
    $auth      = $options['auth'] ?? 'none';
    $csrf      = $options['csrf'] ?? null; // null = auto (true for POST/PUT/DELETE/PATCH)
    $cors      = $options['cors'] ?? true;
    $method    = $options['method'] ?? null;
    $rateLimit = $options['rate_limit'] ?? null;
    $siteKey   = $options['site_key'] ?? null;

    // 1. CORS
    if ($cors && function_exists('handleCors')) {
        handleCors();
    }

    // 2. Method check
    if ($method !== null) {
        $allowed = is_array($method) ? $method : [$method];
        $current = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Always allow OPTIONS for CORS preflight
        if ($current === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        if (!in_array($current, $allowed, true)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $allowed));
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
    }

    // 3. Get PDO
    $pdo = null;
    if (function_exists('getDatabase')) {
        $pdo = getDatabase();
    } elseif (function_exists('getDB')) {
        $pdo = getDB();
    }

    // 4. Rate limiting
    if ($rateLimit !== null && $pdo !== null) {
        $rlAction = $rateLimit['action'] ?? 'api';
        $rlMax    = $rateLimit['max'] ?? 60;
        $rlWindow = $rateLimit['window'] ?? 3600;

        if (class_exists('EngineRateLimiter')) {
            $site = $siteKey ?? $_ENV['SITE_KEY'] ?? 'unknown';
            EngineRateLimiter::enforce($pdo, $site, $rlAction, $rlMax, $rlWindow);
        } elseif (function_exists('checkRateLimit')) {
            checkRateLimit($pdo, $rlAction, $rlMax, $rlWindow);
        }
    }

    // 5. Authentication
    $user = null;
    switch ($auth) {
        case 'required':
            if (function_exists('requireApiAuth')) {
                $user = requireApiAuth($pdo);
            }
            break;

        case 'admin':
            if (function_exists('requireApiAuth')) {
                $user = requireApiAuth($pdo);
                if (empty($user['is_admin'])) {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Admin access required']);
                    exit;
                }
            }
            break;

        case 'optional':
            if (function_exists('isLoggedIn') && isLoggedIn() && function_exists('getCurrentUser') && $pdo) {
                $user = getCurrentUser($pdo);
            }
            break;

        case 'none':
        default:
            break;
    }

    // 6. CSRF (auto-detect: required for mutating methods unless explicitly disabled)
    $currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $isMutating = in_array($currentMethod, ['POST', 'PUT', 'DELETE', 'PATCH'], true);

    if ($csrf === null) {
        $csrf = $isMutating && $auth !== 'none';
    }

    if ($csrf && function_exists('requireCsrf')) {
        requireCsrf();
    }

    return ['pdo' => $pdo, 'user' => $user];
}
