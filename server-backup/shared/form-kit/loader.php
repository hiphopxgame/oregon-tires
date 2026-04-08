<?php
declare(strict_types=1);

/**
 * Form Kit — Shared Loader
 *
 * Single entry point for all sites using the form-kit.
 * Defines FORM_KIT_PATH and requires all core classes.
 *
 * Usage in any site:
 *   require_once $_ENV['FORM_KIT_PATH'] . '/loader.php';
 *   FormManager::init($pdo, [ ...site config... ]);
 */

if (defined('FORM_KIT_PATH')) {
    return; // Already loaded
}

define('FORM_KIT_PATH', __DIR__);
define('FORM_KIT_CLASSES', __DIR__ . '/classes');
define('FORM_KIT_TEMPLATES', __DIR__ . '/templates');
define('FORM_KIT_VERSION', '1.0.0');

// Load KitBase: prefer engine-kit version (has branding bridge), fall back to stub
$_kitBaseEngineFile = ($_ENV['ENGINE_KIT_PATH'] ?? null)
    ? ($_ENV['ENGINE_KIT_PATH'] . '/includes/KitBase.php')
    : null;
if ($_kitBaseEngineFile && file_exists($_kitBaseEngineFile)) {
    require_once $_kitBaseEngineFile;
} else {
    require_once __DIR__ . '/includes/KitBase.php';
}
unset($_kitBaseEngineFile);

require_once FORM_KIT_CLASSES . '/FormManager.php';
require_once FORM_KIT_CLASSES . '/FormSubmission.php';
require_once FORM_KIT_CLASSES . '/FormNotifier.php';
require_once FORM_KIT_CLASSES . '/FormRenderer.php';
require_once FORM_KIT_CLASSES . '/FormRateLimiter.php';
