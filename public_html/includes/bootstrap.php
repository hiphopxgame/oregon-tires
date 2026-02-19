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
// Prefer .env above public_html (safer), fall back to public_html root
$envDir = file_exists(__DIR__ . '/../../.env') ? __DIR__ . '/../..' : __DIR__ . '/..';
$dotenv = Dotenv\Dotenv::createImmutable($envDir);
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
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

// ─── CORS (same-origin only, or allow APP_URL) ─────────────────────────────
$appUrl = $_ENV['APP_URL'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($appUrl && $origin === $appUrl) {
    header("Access-Control-Allow-Origin: {$appUrl}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
}

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Content Type ───────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
