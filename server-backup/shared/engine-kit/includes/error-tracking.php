<?php
/**
 * Error Tracking — Shared across all 1vsM sites
 *
 * Primary: Database logging (engine_error_log table)
 * Supplemental: Sentry PHP SDK (grouping, alerts, source maps)
 *
 * Usage in site bootstrap:
 *   require_once $engineKitPath . '/includes/error-tracking.php';
 *   initErrorTracking($pdo, ['dsn' => $_ENV['SENTRY_DSN'] ?? '', 'site_key' => 'hiphop_world', 'env' => 'production']);
 */

/**
 * Initialize error tracking with Sentry + DB fallback.
 *
 * @param PDO $pdo Database connection
 * @param array $config {dsn, site_key, env, sample_rate}
 */
function initErrorTracking(PDO $pdo, array $config = []): void
{
    static $initialized = false;
    if ($initialized) return;
    $initialized = true;

    $dsn = $config['dsn'] ?? '';
    $siteKey = $config['site_key'] ?? 'unknown';
    $env = $config['env'] ?? 'production';
    $sampleRate = $config['sample_rate'] ?? 0.1;

    // Store config for captureError()
    $GLOBALS['_error_tracking'] = [
        'pdo' => $pdo,
        'site_key' => $siteKey,
        'env' => $env,
        'sentry_enabled' => false,
    ];

    // Initialize Sentry if SDK is available and DSN is set
    if ($dsn && class_exists('\Sentry\SentrySdk')) {
        try {
            \Sentry\init([
                'dsn' => $dsn,
                'environment' => $env,
                'traces_sample_rate' => $sampleRate,
                'send_default_pii' => false,
                'tags' => ['site_key' => $siteKey],
            ]);

            // Set user context if available
            if (!empty($_SESSION['user_id'])) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                    $scope->setUser(['id' => (string) $_SESSION['user_id']]);
                });
            }

            $GLOBALS['_error_tracking']['sentry_enabled'] = true;
        } catch (\Throwable $e) {
            error_log('[error-tracking] Sentry init failed: ' . $e->getMessage());
        }
    }

    // Register global error/exception handlers
    set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
        // Don't capture suppressed errors
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $level = match (true) {
            ($severity & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) => 'error',
            ($severity & (E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING)) => 'warning',
            default => 'notice',
        };

        captureError($message, $level, [
            'file' => $file,
            'line' => $line,
            'severity' => $severity,
        ]);

        return false; // Allow default error handler to run too
    });

    set_exception_handler(function (\Throwable $e): void {
        captureError($e->getMessage(), 'fatal', [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'exception_class' => get_class($e),
        ]);

        // Re-throw for default handling
        throw $e;
    });

    register_shutdown_function(function (): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            captureError($error['message'], 'fatal', [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type'],
            ]);
        }
    });
}

/**
 * Capture an error to Sentry + DB.
 *
 * @param string $message Error message
 * @param string $level   Level: fatal, error, warning, notice, info
 * @param array  $context Additional context (file, line, trace, etc.)
 */
function captureError(string $message, string $level = 'error', array $context = []): void
{
    $config = $GLOBALS['_error_tracking'] ?? null;
    if (!$config) {
        error_log("[error-tracking] Not initialized. Error: {$message}");
        return;
    }

    $requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? bin2hex(random_bytes(8));

    // Send to Sentry if available
    if ($config['sentry_enabled']) {
        try {
            if ($level === 'fatal' || $level === 'error') {
                \Sentry\captureMessage($message, \Sentry\Severity::error());
            } elseif ($level === 'warning') {
                \Sentry\captureMessage($message, \Sentry\Severity::warning());
            } else {
                \Sentry\captureMessage($message, \Sentry\Severity::info());
            }
        } catch (\Throwable $e) {
            error_log('[error-tracking] Sentry capture failed: ' . $e->getMessage());
        }
    }

    // Always log to database
    logErrorToDb($config['pdo'], [
        'site_key' => $config['site_key'],
        'request_id' => $requestId,
        'level' => $level,
        'message' => mb_substr($message, 0, 2000),
        'file' => $context['file'] ?? null,
        'line' => $context['line'] ?? null,
        'trace' => isset($context['trace']) ? mb_substr($context['trace'], 0, 10000) : null,
        'url' => ($_SERVER['REQUEST_URI'] ?? null),
        'method' => ($_SERVER['REQUEST_METHOD'] ?? null),
        'user_id' => ($_SESSION['user_id'] ?? null),
        'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        'ip_hash' => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? ''),
    ]);

    // Also log to PHP error_log as fallback
    error_log("[{$level}][{$config['site_key']}][{$requestId}] {$message}" .
        (isset($context['file']) ? " in {$context['file']}:{$context['line']}" : ''));
}

/**
 * Log error to engine_error_log table.
 */
function logErrorToDb(PDO $pdo, array $data): void
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO engine_error_log
                (site_key, request_id, level, message, file, line, trace, url, method, user_id, user_agent, ip_hash)
            VALUES
                (:site_key, :request_id, :level, :message, :file, :line, :trace, :url, :method, :user_id, :user_agent, :ip_hash)
        ");
        $stmt->execute([
            'site_key' => $data['site_key'],
            'request_id' => $data['request_id'],
            'level' => $data['level'],
            'message' => $data['message'],
            'file' => $data['file'],
            'line' => $data['line'],
            'trace' => $data['trace'],
            'url' => $data['url'],
            'method' => $data['method'],
            'user_id' => $data['user_id'],
            'user_agent' => $data['user_agent'],
            'ip_hash' => $data['ip_hash'],
        ]);
    } catch (\Throwable $e) {
        // If DB logging fails, fall through to error_log only
        error_log('[error-tracking] DB log failed: ' . $e->getMessage());
    }
}
