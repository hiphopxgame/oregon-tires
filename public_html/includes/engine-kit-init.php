<?php
/**
 * Oregon Tires — Engine Kit Initializer
 * Lazy-loads the Engine Kit for 1vsM network integration (GA, SEO, branding).
 */

declare(strict_types=1);

function initEngineKit(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $engineKitPath = $_ENV['ENGINE_KIT_PATH'] ?? null;

    if (!$engineKitPath || !file_exists($engineKitPath . '/loader.php')) {
        return;
    }

    require_once $engineKitPath . '/loader.php';
    $initialized = true;
}
