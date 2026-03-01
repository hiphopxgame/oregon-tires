<?php
/**
 * Oregon Tires — Server-side Rate Limiting
 *
 * Delegates to engine-kit's shared RateLimiter when available,
 * falls back to local oretir_rate_limits table.
 */

declare(strict_types=1);

// Load shared RateLimiter from engine-kit (includes ip-helpers.php)
$engineKitPath = $_ENV['ENGINE_KIT_PATH'] ?? dirname(__DIR__, 3) . '/---engine-kit';
$_otUseSharedRateLimiter = false;
if (file_exists($engineKitPath . '/includes/RateLimiter.php')) {
    require_once $engineKitPath . '/includes/RateLimiter.php';
    $_otUseSharedRateLimiter = true;
}

/**
 * Check rate limit for an action.
 * @param string $action   Action identifier (e.g. 'contact', 'booking', 'login')
 * @param int    $maxHits  Maximum allowed hits in the window
 * @param int    $windowSeconds  Time window in seconds
 */
function checkRateLimit(string $action, int $maxHits = 5, int $windowSeconds = 3600): void
{
    global $_otUseSharedRateLimiter;

    // Delegate to shared EngineRateLimiter if available
    if ($_otUseSharedRateLimiter && class_exists('EngineRateLimiter')) {
        try {
            $db = getDB();
            EngineRateLimiter::enforce($db, 'oregon_tires', $action, $maxHits, $windowSeconds);
            return;
        } catch (\Throwable $e) {
            // If shared table doesn't exist yet, fall through to local
            error_log('Shared RateLimiter fallback: ' . $e->getMessage());
        }
    }

    // Local fallback using oretir_rate_limits table
    $db = getDB();
    $ip = getClientIp();

    $db->beginTransaction();

    try {
        $db->prepare('DELETE FROM oretir_rate_limits WHERE action = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)')
           ->execute([$action, $windowSeconds]);

        $db->prepare('INSERT INTO oretir_rate_limits (ip_address, action) VALUES (?, ?)')
           ->execute([$ip, $action]);

        $stmt = $db->prepare('SELECT COUNT(*) FROM oretir_rate_limits WHERE ip_address = ? AND action = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)');
        $stmt->execute([$ip, $action, $windowSeconds]);
        $count = (int) $stmt->fetchColumn();

        $db->commit();

        if ($count > $maxHits) {
            jsonError('Too many requests. Please try again later.', 429);
        }
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Get client IP address.
 * Delegates to engine-kit's getClientIp() if available, otherwise uses local logic.
 */
if (!function_exists('getClientIp')) {
    function getClientIp(): string
    {
        $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $trustedProxy = filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;

        if ($trustedProxy) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['HTTP_X_REAL_IP']
                ?? $remote;

            if (str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
        } else {
            $ip = $remote;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }

        return $ip;
    }
}
