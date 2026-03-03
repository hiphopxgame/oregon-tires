<?php
/**
 * Oregon Tires — Bootstrap
 * Loads environment, database, and session configuration.
 * Include this file at the top of every API endpoint.
 */

declare(strict_types=1);

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'bootstrap.php') {
    http_response_code(403);
    exit;
}

// ─── Autoloader ─────────────────────────────────────────────────────────────
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Dependencies not installed. Run: composer install']);
    exit;
}
require_once $vendorAutoload;

// ─── Environment ────────────────────────────────────────────────────────────
// Load .env — prefer parent of web root (outside public_html), fall back to project root (local dev)
$envDir = dirname(__DIR__, 3); // Server: /home/hiphopwo/ (above public_html)
$envFile = '.env.oregon-tires';
if (!file_exists($envDir . '/' . $envFile)) {
    $envDir = __DIR__ . '/..'; // Local dev fallback: public_html/
    $envFile = '.env';
}
$dotenv = Dotenv\Dotenv::createImmutable($envDir, $envFile);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'])->notEmpty();

// ─── Database ───────────────────────────────────────────────────────────────
require_once __DIR__ . '/db.php';

// ─── Helpers ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/rate-limit.php';

// ─── Session Config (only started when needed) ──────────────────────────────
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

// Alias expected by shared member-kit endpoints
if (!function_exists('initSession')) {
    function initSession(): void { startSecureSession(); }
}

// ─── CORS (allow APP_URL and HipHop World network) ──────────────────────────
$appUrl = $_ENV['APP_URL'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = array_filter([$appUrl, 'https://hiphop.world', 'https://www.hiphop.world']);

if ($origin && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
}

// Handle preflight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Content Type (API endpoints only) ──────────────────────────────────────
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
if (str_contains($scriptName, '/api/') || str_contains($scriptName, '/cli/')) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-API-Version: v1');
}
header('X-Content-Type-Options: nosniff');

// ─── Engine Kit (optional, for network integration) ─────────────────────────
$engineKitPath = $_ENV['ENGINE_KIT_PATH'] ?? null;
if ($engineKitPath && file_exists($engineKitPath . '/loader.php')) {
    require_once $engineKitPath . '/loader.php';
}

// ─── Shared Kit Path ──────────────────────────────────────────────────────
$etKitPath = $engineKitPath ?? dirname(__DIR__, 3) . '/---engine-kit';

// ─── Cache (APCu + file fallback from engine-kit) ──────────────────────────
if (file_exists($etKitPath . '/includes/simple-cache.php')) {
    require_once $etKitPath . '/includes/simple-cache.php';
}

// ─── Error Tracking (Sentry + DB) ──────────────────────────────────────────
if (file_exists($f = $etKitPath . '/includes/error-tracking.php')) {
    require_once $f;
    initErrorTracking(getDB(), [
        'dsn'      => $_ENV['SENTRY_DSN'] ?? '',
        'site_key' => 'oregon_tires',
        'env'      => $_ENV['APP_ENV'] ?? 'production',
    ]);
}
