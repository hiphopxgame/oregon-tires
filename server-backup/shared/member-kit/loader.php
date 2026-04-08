<?php
declare(strict_types=1);

/**
 * Member Kit — Shared Loader
 *
 * Single entry point for all sites using the member-kit.
 * Defines MEMBER_KIT_PATH and requires all core classes.
 *
 * Usage in any site:
 *   require_once $_ENV['MEMBER_KIT_PATH'] . '/loader.php';
 *   MemberAuth::init($pdo, [ ...site config... ]);
 */

if (defined('MEMBER_KIT_LOADED')) {
    return; // Already loaded
}
define('MEMBER_KIT_LOADED', true);

if (!function_exists('t')) {
    function t(string $key): ?string { return null; }
}

if (!defined('MEMBER_KIT_PATH')) {
    define('MEMBER_KIT_PATH', __DIR__);
}
define('MEMBER_KIT_INCLUDES', __DIR__ . '/includes/member-kit');
define('MEMBER_KIT_TEMPLATES', __DIR__ . '/templates');
define('MEMBER_KIT_VERSION', '1.0.0');

// Web-accessible base URL for member-kit assets (optional — defaults to MEMBER_KIT_PATH)
// Sites can override by setting this env var to a different web path
if (!defined('MEMBER_KIT_URL')) {
    define('MEMBER_KIT_URL', $_ENV['MEMBER_KIT_URL'] ?? '');
}

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

require_once MEMBER_KIT_INCLUDES . '/MemberAuth.php';
require_once MEMBER_KIT_INCLUDES . '/MemberProfile.php';
require_once MEMBER_KIT_INCLUDES . '/MemberSSO.php';
require_once MEMBER_KIT_INCLUDES . '/MemberGoogle.php';
require_once MEMBER_KIT_INCLUDES . '/MemberDiscord.php';
require_once MEMBER_KIT_INCLUDES . '/MemberLinkedIn.php';
require_once MEMBER_KIT_INCLUDES . '/MemberSync.php';
require_once MEMBER_KIT_INCLUDES . '/MemberMail.php';
require_once MEMBER_KIT_INCLUDES . '/MemberFiles.php';
require_once MEMBER_KIT_INCLUDES . '/ConnectionLedger.php';
