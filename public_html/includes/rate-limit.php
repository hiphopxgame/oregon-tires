<?php
/**
 * Oregon Tires — Server-side Rate Limiting
 * Uses oretir_rate_limits table to track requests per IP.
 */

declare(strict_types=1);

/**
 * Check rate limit for an action.
 * @param string $action   Action identifier (e.g. 'contact', 'booking', 'login')
 * @param int    $maxHits  Maximum allowed hits in the window
 * @param int    $windowSeconds  Time window in seconds
 */
function checkRateLimit(string $action, int $maxHits = 5, int $windowSeconds = 3600): void
{
    $db = getDB();
    $ip = getClientIp();

    $db->beginTransaction();

    try {
        // Clean old entries (older than window)
        $db->prepare('DELETE FROM oretir_rate_limits WHERE action = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)')
           ->execute([$action, $windowSeconds]);

        // Insert first, then count — eliminates race condition
        $db->prepare('INSERT INTO oretir_rate_limits (ip_address, action) VALUES (?, ?)')
           ->execute([$ip, $action]);

        // Count all hits including the one we just inserted
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
 * Only trusts proxy headers when REMOTE_ADDR is a known proxy (loopback / private).
 */
function getClientIp(): string
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Only trust forwarded headers if the direct connection is from a trusted proxy
    $trustedProxy = filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;

    if ($trustedProxy) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $remote;

        // X-Forwarded-For may contain multiple IPs — use the first (client)
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
    } else {
        $ip = $remote;
    }

    // Validate IP format
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '0.0.0.0';
    }

    return $ip;
}
