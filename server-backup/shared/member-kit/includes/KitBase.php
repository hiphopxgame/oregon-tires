<?php
declare(strict_types=1);

/**
 * KitBase — Stub fallback for form-kit (no ENGINE_KIT_PATH dependency)
 *
 * Loaded by loader.php when ENGINE_KIT_PATH is not set or the engine-kit
 * KitBase is not available. Provides the same interface as the engine-kit
 * KitBase but loadBranding() always returns [] — kits function normally,
 * branding methods just return their defaults.
 *
 * When ENGINE_KIT_PATH is set and engine-kit/includes/KitBase.php exists,
 * the loader preferentially loads that file instead of this stub.
 */
if (!class_exists('KitBase')) {
    abstract class KitBase
    {
        // ── Abstract methods each subclass must implement ──────────────────

        abstract protected static function &staticPdo(): ?\PDO;
        abstract protected static function &staticConfig(): array;
        abstract protected static function &staticInitialized(): bool;
        abstract protected static function kitName(): string;
        abstract protected static function defaultConfig(): array;

        // ── Base initialization ────────────────────────────────────────────

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
            // No branding loading — stub has no engine dependency

            $initialized = true;
        }

        // ── Config & PDO accessors ─────────────────────────────────────────

        public static function getConfig(?string $key = null): mixed
        {
            $config = static::staticConfig();
            if ($key === null) {
                return $config;
            }
            return $config[$key] ?? null;
        }

        public static function getPdo(): \PDO
        {
            $pdo = static::staticPdo();
            if (!$pdo) {
                $name = static::kitName();
                throw new \RuntimeException("{$name} not initialized. Call {$name}::init() first.");
            }
            return $pdo;
        }

        // ── Mode helper ────────────────────────────────────────────────────

        public static function isHwMode(): bool
        {
            $config = static::staticConfig();
            return ($config['mode'] ?? 'independent') === 'hw';
        }

        // ── Branding bridge (stubs — always returns defaults) ──────────────

        public static function getBranding(string $key, string $default = ''): string
        {
            return $default;
        }

        public static function hasBranding(): bool
        {
            return false;
        }

        // ── Table name helper ──────────────────────────────────────────────

        public static function prefixedTable(string $table): string
        {
            $config = static::staticConfig();
            $prefix = $config['table_prefix'] ?? '';
            if ($prefix !== '' && !str_ends_with($prefix, '_')) {
                $prefix .= '_';
            }
            return $prefix . $table;
        }

        // ── Reset (for testing) ────────────────────────────────────────────

        public static function baseReset(): void
        {
            $pdo = &static::staticPdo();
            $pdo = null;

            $config = &static::staticConfig();
            $config = [];

            $initialized = &static::staticInitialized();
            $initialized = false;
        }
    }
}
