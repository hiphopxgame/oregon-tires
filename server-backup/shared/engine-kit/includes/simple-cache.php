<?php
/**
 * simple-cache.php — Shared APCu + file-based cache for the 1vsM network.
 *
 * Promoted from HHW's includes/simple-cache.php with site-key scoping
 * for multi-site isolation.
 *
 * Usage:
 *   require_once __DIR__ . '/simple-cache.php';
 *
 *   // Basic get/set:
 *   $data = cacheGet('settings', 300);
 *   if ($data === null) {
 *       $data = expensiveQuery();
 *       cacheSet('settings', $data, 300);
 *   }
 *
 *   // Convenience: cached DB query:
 *   $rows = cachedQuery($pdo, 'gallery_images', 600,
 *       'SELECT * FROM gallery_images WHERE is_active = 1 ORDER BY display_order',
 *       []
 *   );
 */

/**
 * Detect the active cache driver.
 *
 * @return string 'apcu' or 'file'
 */
function cacheDriver(): string
{
    static $driver = null;
    if ($driver !== null) return $driver;
    $driver = (function_exists('apcu_enabled') && apcu_enabled()) ? 'apcu' : 'file';
    return $driver;
}

/**
 * Resolve the cache directory for a given site key.
 * Creates the directory if it doesn't exist.
 *
 * @param string $siteKey  Site identifier for scoping (e.g. 'oregon_tires')
 * @param string $baseDir  Base cache directory (override for custom paths)
 * @return string Resolved cache directory path
 */
function cacheDir(string $siteKey = '', string $baseDir = ''): string
{
    if ($baseDir === '') {
        // Default: /tmp/1vsm-cache/{site_key}/
        $baseDir = sys_get_temp_dir() . '/1vsm-cache';
    }

    $dir = $siteKey !== '' ? $baseDir . '/' . $siteKey : $baseDir;

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return $dir;
}

/**
 * Get a cached value by key.
 *
 * @param string $key       Cache key
 * @param int    $ttl       Max age in seconds before stale
 * @param string $siteKey   Site key for scoping (file driver only)
 * @return mixed|null       Decoded data, or null if missing/expired/corrupt
 */
function cacheGet(string $key, int $ttl = 300, string $siteKey = ''): mixed
{
    // APCu: prefix key with site key for isolation
    if (cacheDriver() === 'apcu') {
        $prefixedKey = $siteKey !== '' ? "{$siteKey}:{$key}" : $key;
        $success = false;
        $data = apcu_fetch($prefixedKey, $success);
        return $success ? $data : null;
    }

    // File driver
    $dir = cacheDir($siteKey);
    $file = $dir . '/' . md5($key) . '.json';

    if (!file_exists($file)) {
        return null;
    }

    $age = time() - filemtime($file);
    if ($age > $ttl) {
        return null;
    }

    $raw = file_get_contents($file);
    if ($raw === false) {
        return null;
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $data;
}

/**
 * Set a cache value by key.
 *
 * @param string $key       Cache key
 * @param mixed  $data      Data to cache (must be JSON-serializable for file driver)
 * @param int    $ttl       Time-to-live in seconds (0 = no expiry for APCu)
 * @param string $siteKey   Site key for scoping
 * @return bool             True on success
 */
function cacheSet(string $key, mixed $data, int $ttl = 0, string $siteKey = ''): bool
{
    // APCu
    if (cacheDriver() === 'apcu') {
        $prefixedKey = $siteKey !== '' ? "{$siteKey}:{$key}" : $key;
        return apcu_store($prefixedKey, $data, $ttl);
    }

    // File driver
    $dir = cacheDir($siteKey);
    $file = $dir . '/' . md5($key) . '.json';
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($json === false) {
        return false;
    }

    // Atomic write: temp file + rename
    $tmpFile = $file . '.tmp.' . getmypid();
    $written = file_put_contents($tmpFile, $json, LOCK_EX);

    if ($written === false) {
        @unlink($tmpFile);
        return false;
    }

    return rename($tmpFile, $file);
}

/**
 * Delete a cached value by key.
 *
 * @param string $key       Cache key
 * @param string $siteKey   Site key for scoping
 * @return bool
 */
function cacheDelete(string $key, string $siteKey = ''): bool
{
    if (cacheDriver() === 'apcu') {
        $prefixedKey = $siteKey !== '' ? "{$siteKey}:{$key}" : $key;
        return apcu_delete($prefixedKey);
    }

    $dir = cacheDir($siteKey);
    $file = $dir . '/' . md5($key) . '.json';

    if (file_exists($file)) {
        return unlink($file);
    }

    return true;
}

/**
 * Delete all cache entries matching a key prefix.
 *
 * @param string $prefix   Key prefix to match
 * @param string $siteKey  Site key for scoping
 * @return int Number of entries deleted
 */
function cacheDeletePrefix(string $prefix, string $siteKey = ''): int
{
    if (cacheDriver() === 'apcu') {
        $fullPrefix = $siteKey !== '' ? "{$siteKey}:{$prefix}" : $prefix;
        $iterator = new \APCuIterator('#^' . preg_quote($fullPrefix, '#') . '#', APC_ITER_KEY);
        $count = 0;
        foreach ($iterator as $item) {
            apcu_delete($item['key']);
            $count++;
        }
        return $count;
    }

    // File fallback: delete all cache files for this site key
    $dir = cacheDir($siteKey);
    if (!is_dir($dir)) return 0;
    $count = 0;
    foreach (glob($dir . '/*.json') as $file) {
        @unlink($file);
        $count++;
    }
    return $count;
}

/**
 * Convenience: Execute a cached database query.
 *
 * @param PDO    $pdo      Database connection
 * @param string $key      Cache key for this query
 * @param int    $ttl      Cache TTL in seconds
 * @param string $sql      SQL query
 * @param array  $params   Bound parameters
 * @param string $siteKey  Site key for cache scoping
 * @return array Query results
 */
function cachedQuery(PDO $pdo, string $key, int $ttl, string $sql, array $params = [], string $siteKey = ''): array
{
    $cached = cacheGet($key, $ttl, $siteKey);
    if ($cached !== null) {
        // Signal cache hit for callers that check headers
        if (!headers_sent()) {
            header('X-Cache: HIT');
        }
        return $cached;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll();

    cacheSet($key, $result, $ttl, $siteKey);

    if (!headers_sent()) {
        header('X-Cache: MISS');
    }

    return $result;
}
