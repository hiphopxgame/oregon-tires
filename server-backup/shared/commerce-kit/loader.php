<?php
declare(strict_types=1);

/**
 * Commerce Kit — Shared Loader
 *
 * Single entry point for all sites using the commerce-kit.
 * Defines COMMERCE_KIT_PATH and requires all core classes.
 *
 * Usage in any site:
 *   require_once COMMERCE_KIT_PATH . '/loader.php';
 *   // Then use CommerceOrder::create($pdo, ...), new CommerceManual($pdo), etc.
 */

if (defined('COMMERCE_KIT_PATH')) {
    return; // Already loaded
}

define('COMMERCE_KIT_PATH', __DIR__);
define('COMMERCE_KIT_CLASSES', __DIR__ . '/classes');
define('COMMERCE_KIT_TEMPLATES', __DIR__ . '/templates');
define('COMMERCE_KIT_VERSION', '1.1.0');

require_once COMMERCE_KIT_CLASSES . '/CommerceOrder.php';
require_once COMMERCE_KIT_CLASSES . '/CommerceProvider.php';
require_once COMMERCE_KIT_CLASSES . '/CommerceManual.php';
require_once COMMERCE_KIT_CLASSES . '/CommercePayPal.php';
require_once COMMERCE_KIT_CLASSES . '/CommerceCrypto.php';
require_once COMMERCE_KIT_CLASSES . '/CommerceNotifications.php';
require_once COMMERCE_KIT_CLASSES . '/CommerceStripe.php';
require_once COMMERCE_KIT_CLASSES . '/CommerceBootstrap.php';
