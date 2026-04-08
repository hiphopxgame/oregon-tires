<?php
/**
 * RateLimiter — Unified rate limiting for the 1vsM network.
 *
 * Uses the shared `engine_rate_limits` table with per-site scoping.
 * Supports IP-based and user-based identifiers.
 *
 * Usage:
 *   require_once __DIR__ . '/RateLimiter.php';
 *
 *   // Check + hit + send headers + auto-429 if exceeded:
 *   EngineRateLimiter::enforce($pdo, 'oregon_tires', 'contact', 5, 3600);
 *
 *   // Manual check (returns state without blocking):
 *   $result = EngineRateLimiter::check($pdo, 'hiphop_world', 'login', 10, 300);
 *   if (!$result['allowed']) { ... }
 *
 *   // Record a hit separately:
 *   EngineRateLimiter::hit($pdo, 'hiphop_world', 'login');
 */

require_once __DIR__ . '/ip-helpers.php';

class EngineRateLimiter
{
    /**
     * Check if the current request is within the rate limit.
     * Does NOT record a hit — use hit() or enforce() for that.
     *
     * @param PDO    $pdo        Database connection
     * @param string $siteKey    Site identifier (e.g. 'hiphop_world', 'oregon_tires')
     * @param string $action     Action identifier (e.g. 'login', 'contact', 'booking')
     * @param int    $max        Maximum attempts allowed in the window
     * @param int    $window     Time window in seconds
     * @param string|null $identifier  Custom identifier (default: client IP)
     * @return array ['allowed' => bool, 'remaining' => int, 'count' => int, 'reset_at' => int]
     */
    public static function check(PDO $pdo, string $siteKey, string $action, int $max, int $window, ?string $identifier = null): array
    {
        $identifier = $identifier ?? self::getClientIp();

        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM engine_rate_limits
                 WHERE site_key = ? AND action = ? AND identifier = ?
                   AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)"
            );
            $stmt->execute([$siteKey, $action, $identifier, $window]);
            $count = (int) $stmt->fetchColumn();

            $remaining = max(0, $max - $count);

            return [
                'allowed'   => $count < $max,
                'remaining' => $remaining,
                'count'     => $count,
                'reset_at'  => time() + $window,
            ];
        } catch (\Throwable $e) {
            error_log('RateLimiter::check error: ' . $e->getMessage());
            // Fail open — never block requests due to DB issues
            return [
                'allowed'   => true,
                'remaining' => -1,
                'count'     => 0,
                'reset_at'  => time() + $window,
            ];
        }
    }

    /**
     * Record a rate-limit hit for the current request.
     *
     * @param PDO    $pdo        Database connection
     * @param string $siteKey    Site identifier
     * @param string $action     Action identifier
     * @param string|null $identifier  Custom identifier (default: client IP)
     * @return bool Success
     */
    public static function hit(PDO $pdo, string $siteKey, string $action, ?string $identifier = null): bool
    {
        $identifier = $identifier ?? self::getClientIp();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO engine_rate_limits (site_key, action, identifier) VALUES (?, ?, ?)"
            );
            $stmt->execute([$siteKey, $action, $identifier]);
            return true;
        } catch (\Throwable $e) {
            error_log('RateLimiter::hit error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * All-in-one: check + hit + send rate-limit headers + 429 if exceeded.
     *
     * @param PDO    $pdo        Database connection
     * @param string $siteKey    Site identifier
     * @param string $action     Action identifier
     * @param int    $max        Maximum attempts allowed in the window
     * @param int    $window     Time window in seconds (default: 3600)
     * @param string|null $identifier  Custom identifier (default: client IP)
     * @return array The check result (for callers that need it)
     */
    public static function enforce(PDO $pdo, string $siteKey, string $action, int $max, int $window = 3600, ?string $identifier = null): array
    {
        $result = self::check($pdo, $siteKey, $action, $max, $window, $identifier);

        // Always send rate-limit headers
        self::sendHeaders($max, $result['remaining'], $result['reset_at']);

        if (!$result['allowed']) {
            header('Retry-After: ' . $window);
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            exit;
        }

        // Record the hit
        self::hit($pdo, $siteKey, $action, $identifier);

        return $result;
    }

    /**
     * Send standard X-RateLimit-* response headers.
     */
    private static function sendHeaders(int $limit, int $remaining, int $resetAt): void
    {
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . max(0, $remaining));
        header('X-RateLimit-Reset: ' . $resetAt);
    }

    /**
     * Get the client IP address (delegates to ip-helpers.php).
     */
    public static function getClientIp(): string
    {
        return getClientIp();
    }

    /**
     * Clean up expired rate-limit entries.
     *
     * @param PDO $pdo           Database connection
     * @param int $maxAgeHours   Delete entries older than this (default: 24)
     * @return int Number of rows deleted
     */
    public static function cleanup(PDO $pdo, int $maxAgeHours = 24): int
    {
        try {
            $stmt = $pdo->prepare(
                "DELETE FROM engine_rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)"
            );
            $stmt->execute([$maxAgeHours]);
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            error_log('RateLimiter::cleanup error: ' . $e->getMessage());
            return 0;
        }
    }
}
