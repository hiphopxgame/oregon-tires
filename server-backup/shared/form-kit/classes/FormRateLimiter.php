<?php
declare(strict_types=1);

/**
 * FormRateLimiter — Database-backed rate limiting for Form Kit
 *
 * Provides simple rate limiting using a database table. Tracks
 * submissions by site, action type, and hashed identifier (typically
 * the SHA-256 of the client IP). The table is auto-created on first use.
 */
class FormRateLimiter
{
    /** @var bool Whether the rate limit table has been verified to exist */
    private static bool $tableVerified = false;

    /**
     * Check if an identifier is within the rate limit.
     *
     * Counts entries in the rate limit table for the given identifier
     * within the time window. Returns true if under the limit.
     *
     * @param string $siteKey       Site identifier
     * @param string $action        Action type (e.g., 'form_submit')
     * @param string $identifier    Hashed IP or other identifier
     * @param int    $max           Maximum allowed attempts in the window
     * @param int    $windowSeconds Time window in seconds
     * @return bool True if under the limit, false if exceeded
     */
    public static function check(string $siteKey, string $action, string $identifier, int $max, int $windowSeconds): bool
    {
        self::ensureTable();

        $pdo = FormManager::getPdo();
        $table = self::rateLimitTable();
        $since = date('Y-m-d H:i:s', time() - $windowSeconds);

        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM `{$table}`
                WHERE site_key = ? AND action = ? AND identifier = ? AND created_at >= ?
            ");
            $stmt->execute([$siteKey, $action, $identifier, $since]);
            $count = (int) $stmt->fetchColumn();

            return $count < $max;
        } catch (\Throwable $e) {
            error_log('[FormRateLimiter] check: ' . $e->getMessage());
            // On error, allow the request (fail open)
            return true;
        }
    }

    /**
     * Record a rate limit entry.
     *
     * Inserts a new row into the rate limit table for tracking.
     *
     * @param string $siteKey    Site identifier
     * @param string $action     Action type
     * @param string $identifier Hashed IP or other identifier
     */
    public static function record(string $siteKey, string $action, string $identifier): void
    {
        self::ensureTable();

        $pdo = FormManager::getPdo();
        $table = self::rateLimitTable();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `{$table}` (site_key, action, identifier, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$siteKey, $action, $identifier]);
        } catch (\Throwable $e) {
            error_log('[FormRateLimiter] record: ' . $e->getMessage());
        }
    }

    /**
     * Clean up expired rate limit entries.
     *
     * Deletes entries older than the specified maximum window (default 24 hours).
     * Intended to be called periodically (e.g., via cron) to keep the table small.
     *
     * @param int $maxWindowSeconds Maximum window to retain (default 86400 = 24 hours)
     * @return int Number of deleted rows
     */
    public static function cleanup(int $maxWindowSeconds = 86400): int
    {
        self::ensureTable();

        $pdo = FormManager::getPdo();
        $table = self::rateLimitTable();
        $cutoff = date('Y-m-d H:i:s', time() - $maxWindowSeconds);

        try {
            $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE created_at < ?");
            $stmt->execute([$cutoff]);
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            error_log('[FormRateLimiter] cleanup: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get the rate limit table name with prefix.
     *
     * @return string
     */
    public static function rateLimitTable(): string
    {
        return FormManager::prefixedTable('form_rate_limits');
    }

    /**
     * Check rate limit and throw RuntimeException if exceeded.
     *
     * Convenience method for API endpoints — check the limit and throw
     * if exceeded, so the caller can catch and return an error response.
     *
     * @param string $siteKey       Site identifier
     * @param string $action        Action type
     * @param string $identifier    Hashed IP or other identifier
     * @param int    $max           Maximum allowed attempts
     * @param int    $windowSeconds Time window in seconds
     * @throws \RuntimeException If rate limit is exceeded
     */
    public static function checkOrFail(string $siteKey, string $action, string $identifier, int $max, int $windowSeconds): void
    {
        if (!self::check($siteKey, $action, $identifier, $max, $windowSeconds)) {
            throw new \RuntimeException('Rate limit exceeded. Please try again later.');
        }
    }

    // ── Private Helpers ─────────────────────────────────────────────────

    /**
     * Ensure the rate limit table exists (auto-create if needed).
     *
     * Uses a static flag to avoid repeated SHOW TABLES queries.
     * The table is created with InnoDB engine for transactional safety.
     */
    private static function ensureTable(): void
    {
        if (self::$tableVerified) {
            return;
        }

        $pdo = FormManager::getPdo();
        $table = self::rateLimitTable();

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    site_key VARCHAR(100) NOT NULL DEFAULT '',
                    action VARCHAR(50) NOT NULL DEFAULT 'form_submit',
                    identifier VARCHAR(64) NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_rate_lookup (site_key, action, identifier, created_at),
                    INDEX idx_rate_cleanup (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            self::$tableVerified = true;
        } catch (\Throwable $e) {
            error_log('[FormRateLimiter] ensureTable: ' . $e->getMessage());
            // Set verified anyway to avoid retry loops
            self::$tableVerified = true;
        }
    }

    /**
     * Reset table verification state (for testing purposes).
     */
    public static function resetTableVerification(): void
    {
        self::$tableVerified = false;
    }
}
