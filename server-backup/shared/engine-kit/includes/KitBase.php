<?php
declare(strict_types=1);

/**
 * KitBase — Shared abstract base for all Kit static manager classes
 *
 * Consolidates the common ~60 lines of static-manager boilerplate:
 * init guard, config merge, PDO/config accessors, isHwMode(), prefixedTable().
 * Also provides an optional branding bridge: if ENGINE_KIT_PATH is set and
 * 'site_key' is provided in config at init time, raw branding JSON is loaded
 * from engine_sites once and cached in static config as '_branding'.
 *
 * Usage (in each Kit manager):
 *
 *   class FormManager extends KitBase
 *   {
 *       private static ?\PDO $pdo = null;
 *       private static array $config = [];
 *       private static bool $initialized = false;
 *
 *       protected static function &staticPdo(): ?\PDO      { return self::$pdo; }
 *       protected static function &staticConfig(): array   { return self::$config; }
 *       protected static function &staticInitialized(): bool { return self::$initialized; }
 *       protected static function kitName(): string        { return 'FormManager'; }
 *       protected static function defaultConfig(): array   { return [...]; }
 *
 *       public static function init(\PDO $pdo, array $config = []): void
 *       {
 *           if (self::$initialized) return;
 *           parent::baseInit($pdo, $config);
 *       }
 *   }
 *
 * IMPORTANT: PHP static properties declared on a parent class are shared across
 * ALL subclasses. Each subclass MUST declare its own private static $pdo/config/
 * initialized and implement the abstract reference-returning methods so KitBase
 * can mutate the correct subclass storage via late static binding.
 */
abstract class KitBase
{
    // ── Abstract methods each subclass must implement ──────────────────────

    /**
     * Return a reference to the subclass's own private static ?PDO $pdo.
     * Implementation: `protected static function &staticPdo(): ?\PDO { return self::$pdo; }`
     */
    abstract protected static function &staticPdo(): ?\PDO;

    /**
     * Return a reference to the subclass's own private static array $config.
     * Implementation: `protected static function &staticConfig(): array { return self::$config; }`
     */
    abstract protected static function &staticConfig(): array;

    /**
     * Return a reference to the subclass's own private static bool $initialized.
     * Implementation: `protected static function &staticInitialized(): bool { return self::$initialized; }`
     */
    abstract protected static function &staticInitialized(): bool;

    /**
     * Return the manager class name for use in error messages (e.g. 'FormManager').
     */
    abstract protected static function kitName(): string;

    /**
     * Return the kit's default config array (merged with caller-supplied overrides).
     * Must include at minimum: 'mode', 'table_prefix', 'site_key'.
     */
    abstract protected static function defaultConfig(): array;

    // ── Base initialization ────────────────────────────────────────────────

    /**
     * Initialize: sets PDO, merges defaultConfig() with $config overrides,
     * and optionally loads branding from engine-kit if 'site_key' is non-empty.
     *
     * Call from the subclass's init() AFTER the initialized guard:
     *   public static function init(\PDO $pdo, array $config = []): void
     *   {
     *       if (self::$initialized) return;
     *       parent::baseInit($pdo, $config);
     *   }
     */
    protected static function baseInit(\PDO $pdo, array $config): void
    {
        $initialized = &static::staticInitialized();
        if ($initialized) {
            return;
        }

        $pdoRef = &static::staticPdo();
        $pdoRef = $pdo;

        $configRef = &static::staticConfig();
        $configRef = array_merge(static::defaultConfig(), $config);

        // Branding bridge: auto-load from engine-kit if site_key is set
        $siteKey = $configRef['site_key'] ?? '';
        if ($siteKey !== '') {
            $configRef['_branding'] = self::loadBranding($siteKey);
        }

        $initialized = true;
    }

    // ── Config & PDO accessors ─────────────────────────────────────────────

    /**
     * Get the full config array, or a single key's value.
     *
     * @param string|null $key Config key, or null for entire array
     * @return mixed
     */
    public static function getConfig(?string $key = null): mixed
    {
        $config = static::staticConfig();
        if ($key === null) {
            return $config;
        }
        return $config[$key] ?? null;
    }

    /**
     * Get the PDO instance. Throws if the kit has not been initialized.
     *
     * @throws \RuntimeException
     */
    public static function getPdo(): \PDO
    {
        $pdo = static::staticPdo();
        if (!$pdo) {
            $name = static::kitName();
            throw new \RuntimeException("{$name} not initialized. Call {$name}::init() first.");
        }
        return $pdo;
    }

    // ── Mode helper ────────────────────────────────────────────────────────

    /**
     * True when the kit is running in HipHop.World shared-tables mode.
     */
    public static function isHwMode(): bool
    {
        $config = static::staticConfig();
        return ($config['mode'] ?? 'independent') === 'hw';
    }

    // ── Branding bridge ────────────────────────────────────────────────────

    /**
     * Get a single value from the auto-loaded engine branding data.
     *
     * Returns $default if branding was not loaded or the key is absent.
     * Keys match the raw engine_sites.branding JSON (e.g. 'primary_color', 'logo').
     */
    public static function getBranding(string $key, string $default = ''): string
    {
        $config = static::staticConfig();
        return (string) ($config['_branding'][$key] ?? $default);
    }

    /**
     * True if branding data was successfully loaded at init time.
     */
    public static function hasBranding(): bool
    {
        $config = static::staticConfig();
        return !empty($config['_branding']);
    }

    // ── Table name helper ──────────────────────────────────────────────────

    /**
     * Build a prefixed table name using the 'table_prefix' config value.
     *
     * Appends a trailing '_' to the prefix if it lacks one.
     * Example: prefix='oretir', table='events' → 'oretir_events'
     */
    public static function prefixedTable(string $table): string
    {
        $config = static::staticConfig();
        $prefix = $config['table_prefix'] ?? '';
        if ($prefix !== '' && !str_ends_with($prefix, '_')) {
            $prefix .= '_';
        }
        return $prefix . $table;
    }

    // ── Reset (for testing) ────────────────────────────────────────────────

    /**
     * Reset all base state. Call from subclass reset() before clearing
     * any subclass-specific state (e.g. registered actions, callbacks).
     */
    public static function baseReset(): void
    {
        $pdo = &static::staticPdo();
        $pdo = null;

        $config = &static::staticConfig();
        $config = [];

        $initialized = &static::staticInitialized();
        $initialized = false;
    }

    // ── Private: branding loader ───────────────────────────────────────────

    /**
     * Load raw branding JSON from engine_sites for the given site key.
     *
     * Returns an associative array of branding keys (e.g. 'primary_color',
     * 'logo', 'font') on success. Returns [] silently on any failure —
     * kits always work without the engine.
     *
     * @param string $siteKey engine_sites.site_key value
     * @return array
     */
    private static function loadBranding(string $siteKey): array
    {
        try {
            $engineKitPath = $_ENV['ENGINE_KIT_PATH'] ?? null;
            if (!$engineKitPath) {
                return [];
            }

            $loaderPath = $engineKitPath . '/loader.php';
            if (!file_exists($loaderPath)) {
                return [];
            }

            require_once $loaderPath;

            if (!function_exists('getEngineKit')) {
                return [];
            }

            $engine = getEngineKit($siteKey);
            if (!$engine) {
                return [];
            }

            $site = $engine->getSite($siteKey);
            if (!$site || empty($site['branding'])) {
                return [];
            }

            $branding = json_decode($site['branding'], true);
            return is_array($branding) ? $branding : [];
        } catch (\Throwable $e) {
            error_log('[KitBase] loadBranding failed for site_key=' . $siteKey . ': ' . $e->getMessage());
            return [];
        }
    }
}
