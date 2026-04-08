<?php
/**
 * Engine Kit — Shared PHP Library Loader
 *
 * Single entry point for satellite sites to access the HipHop World
 * component engine. Follows the member-kit loader pattern.
 *
 * Usage:
 *   $engineKitPath = $_ENV['ENGINE_KIT_PATH'] ?? '/home/hiphopwo/shared/engine-kit';
 *   require_once $engineKitPath . '/loader.php';
 *
 *   // Then use helpers:
 *   echo engineHead('oregontires');
 *   echo engineFooter('oregontires');
 *   echo engineContactForm('oregontires');
 */

// Guard: don't re-initialize
if (defined('ENGINE_KIT_PATH')) {
    return;
}

define('ENGINE_KIT_PATH', __DIR__);
define('ENGINE_KIT_VERSION', '1.0.0');

// The engine lives at hiphop.world's public_html on the same server.
// ENGINE_HUB_ROOT points to the engine directory (same server, direct require).
// Note: getenv() returns false (not null) when unset, so use ?: not ??
$hubRoot = ($_ENV['ENGINE_HUB_ROOT'] ?? null)
    ?: (getenv('ENGINE_HUB_ROOT') ?: '/home/hiphopwo/public_html/engine');

if (!defined('ENGINE_HUB_ROOT')) {
    define('ENGINE_HUB_ROOT', $hubRoot);
}

// Load the engine bootstrap (registers autoloader + provides getEngine())
$bootstrapPath = ENGINE_HUB_ROOT . '/bootstrap.php';
if (!file_exists($bootstrapPath)) {
    error_log("[engine-kit] Engine bootstrap not found at: {$bootstrapPath}");
    return;
}

require_once $bootstrapPath;

// Load shared Composer dependencies if available
$vendorPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
}

// Load the database config from hiphop.world to connect to hiphopwo_rld_system.
// Skip if host site already defines getDatabase() (e.g., 1OH6, Oregon Tires).
// NOTE: Even when skipped, getEnginePdo() below creates a dedicated connection
// to the engine database so satellite sites don't query engine_* tables on their own DB.
$dbConfigPath = dirname(ENGINE_HUB_ROOT) . '/config/database.php';
if (file_exists($dbConfigPath) && !function_exists('getDatabase')) {
    require_once $dbConfigPath;
}

// Load helper functions
require_once __DIR__ . '/helpers.php';

/**
 * Get a PDO connection specifically for the engine database (hiphopwo_rld_system).
 *
 * When the engine-kit is loaded by a satellite site (1OH6, Oregon Tires, etc.),
 * the site's own getDatabase() returns a PDO connected to the site's DB
 * (e.g., hiphopwo_1oh6). The engine tables (engine_sites, engine_components, etc.)
 * only exist in hiphopwo_rld_system. This function ensures the engine always
 * connects to the correct database.
 *
 * Resolution order:
 *   1. ENGINE_DB_NAME env var (explicit override)
 *   2. HHW's config/database.php .env (reads DB_NAME from hiphop.world's .env)
 *   3. Falls back to hiphopwo_rld_system with default credentials
 *
 * If the site's own getDatabase() already points to hiphopwo_rld_system,
 * it is reused to avoid creating a duplicate connection.
 *
 * @return PDO
 */
if (!function_exists('getEnginePdo')) {
    function getEnginePdo(): PDO
    {
        static $enginePdo = null;
        if ($enginePdo !== null) {
            return $enginePdo;
        }

        // Check if ENGINE_DB_NAME is explicitly set (preferred for satellite sites)
        $engineDbName = $_ENV['ENGINE_DB_NAME'] ?? (getenv('ENGINE_DB_NAME') ?: null);

        if ($engineDbName) {
            // Explicit engine DB config — create a dedicated connection
            $host    = $_ENV['ENGINE_DB_HOST'] ?? ($_ENV['DB_HOST'] ?? 'localhost');
            $port    = $_ENV['ENGINE_DB_PORT'] ?? ($_ENV['DB_PORT'] ?? '3306');
            $user    = $_ENV['ENGINE_DB_USER'] ?? ($_ENV['DB_USER'] ?? '');
            $pass    = $_ENV['ENGINE_DB_PASSWORD'] ?? ($_ENV['DB_PASSWORD'] ?? '');
            $charset = $_ENV['ENGINE_DB_CHARSET'] ?? ($_ENV['DB_CHARSET'] ?? 'utf8mb4');

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $engineDbName, $charset);
            $enginePdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return $enginePdo;
        }

        // No explicit ENGINE_DB_NAME — try to read HHW's .env to discover
        // the correct DB name AND credentials (satellite users may lack cross-DB access)
        $hwEnvPath = dirname(ENGINE_HUB_ROOT) . '/.env';
        $hwDb = ['name' => null, 'host' => null, 'port' => null, 'user' => null, 'pass' => null, 'charset' => null];

        if (file_exists($hwEnvPath)) {
            $lines = file($hwEnvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                if (strpos($line, '=') === false) continue;
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\"'");
                match ($k) {
                    'DB_NAME'     => $hwDb['name']    = $v,
                    'DB_HOST'     => $hwDb['host']    = $v,
                    'DB_PORT'     => $hwDb['port']    = $v,
                    'DB_USER'     => $hwDb['user']    = $v,
                    'DB_PASSWORD' => $hwDb['pass']    = $v,
                    'DB_CHARSET'  => $hwDb['charset'] = $v,
                    default       => null,
                };
            }
        }

        // If the site's own DB matches HHW's DB (or no HHW .env found), reuse getDatabase()
        $siteDbName = $_ENV['DB_NAME'] ?? (defined('DB_NAME') ? DB_NAME : null);
        $targetDb   = $hwDb['name'] ?: 'hiphopwo_rld_system';

        if ($siteDbName === $targetDb && function_exists('getDatabase')) {
            $enginePdo = getDatabase();
            return $enginePdo;
        }

        // Satellite site DB differs from engine DB — create a dedicated connection
        // using HHW's credentials (since the satellite DB user may not have access)
        $host    = $hwDb['host']    ?: ($_ENV['DB_HOST'] ?? 'localhost');
        $port    = $hwDb['port']    ?: ($_ENV['DB_PORT'] ?? '3306');
        $user    = $hwDb['user']    ?: ($_ENV['DB_USER'] ?? (defined('DB_USER') ? DB_USER : ''));
        $pass    = $hwDb['pass']    ?: ($_ENV['DB_PASSWORD'] ?? (defined('DB_PASS') ? DB_PASS : ''));
        $charset = $hwDb['charset'] ?: ($_ENV['DB_CHARSET'] ?? 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $targetDb, $charset);
        $enginePdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $enginePdo;
    }
}

/**
 * Get or create the engine context for a site.
 *
 * Uses getEnginePdo() to ensure the engine always queries the correct
 * database (hiphopwo_rld_system) regardless of which site loads the kit.
 *
 * @param string $siteKey The site key to configure the engine for
 * @return \Engine\EngineContext|null
 */
if (!function_exists('getEngineKit')) {
    function getEngineKit(string $siteKey): ?\Engine\EngineContext
    {
        static $engine = null;

        if ($engine === null) {
            try {
                $pdo = getEnginePdo();
                $engine = \Engine\getEngine($pdo);
            } catch (\Throwable $e) {
                error_log("[engine-kit] Failed to initialize: {$e->getMessage()}");
                return null;
            }
        }

        return $engine;
    }
}
