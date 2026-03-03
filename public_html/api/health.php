<?php
/**
 * GET /api/health.php — Oregon Tires Health Check
 *
 * Returns JSON status for database, environment, and security checks.
 * Access: X-Health-Key header or admin session.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// ── Access Control ────────────────────────────────────────────────────────
$authorized = false;

$healthKey = $_ENV['HEALTH_CHECK_KEY'] ?? '';
if (!empty($healthKey)) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    foreach ($headers as $name => $value) {
        if (strcasecmp($name, 'X-Health-Key') === 0 && hash_equals($healthKey, $value)) {
            $authorized = true;
            break;
        }
    }
}

if (!$authorized) {
    startSecureSession();
    if (!empty($_SESSION['admin_logged_in'])) {
        $authorized = true;
    }
}

if (!$authorized) {
    // Basic health check for unauthenticated monitoring (uptime, load balancers)
    try {
        $pdo = getDB();
        $pdo->query('SELECT 1');
        http_response_code(200);
        echo json_encode(['status' => 'healthy', 'timestamp' => gmdate('Y-m-d\TH:i:s\Z')]);
    } catch (\Throwable $e) {
        http_response_code(503);
        echo json_encode(['status' => 'unhealthy', 'timestamp' => gmdate('Y-m-d\TH:i:s\Z')]);
    }
    exit;
}

// ── Checks ────────────────────────────────────────────────────────────────
$checks = [];

// 1. Database
try {
    $pdo = getDB();
    $start = microtime(true);
    $pdo->query('SELECT 1');
    $latency = round((microtime(true) - $start) * 1000, 2);
    $checks['database'] = ['status' => 'ok', 'latency_ms' => $latency];
} catch (\Throwable $e) {
    $checks['database'] = ['status' => 'fail', 'details' => $e->getMessage()];
}

// 2. Environment
$required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'APP_URL'];
$missing = array_filter($required, fn($v) => empty($_ENV[$v]));
$checks['environment'] = empty($missing)
    ? ['status' => 'ok', 'details' => 'All required vars present']
    : ['status' => 'fail', 'details' => 'Missing: ' . implode(', ', $missing)];

// 3. .env security — must NOT be inside web root
$webroot = dirname(__DIR__);
$checks['env_security'] = file_exists($webroot . '/.env')
    ? ['status' => 'fail', 'details' => 'CRITICAL: .env file exists inside web root']
    : ['status' => 'ok', 'details' => '.env outside web root'];

// 4. PHP
$checks['php'] = [
    'status' => version_compare(PHP_VERSION, '8.1.0', '>=') ? 'ok' : 'warn',
    'details' => 'PHP ' . PHP_VERSION,
];

// ── Response ──────────────────────────────────────────────────────────────
$fail = count(array_filter($checks, fn($c) => ($c['status'] ?? '') === 'fail'));
$warn = count(array_filter($checks, fn($c) => ($c['status'] ?? '') === 'warn'));
$status = $fail > 0 ? 'unhealthy' : ($warn > 0 ? 'degraded' : 'healthy');

header('Cache-Control: no-cache, no-store, must-revalidate');
echo json_encode([
    'status'    => $status,
    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    'checks'    => $checks,
], JSON_PRETTY_PRINT);
