<?php
/**
 * Universal Nav Init Helper
 *
 * Provides a simple function for non-hiphop sites to render the universal nav.
 * Automatically detects auth state from member-kit if available.
 *
 * Usage:
 *   require_once $engineKitPath . '/includes/universal-nav-init.php';
 *   echo renderUniversalNav('oregontires', 'Oregon Tires');
 */

require_once __DIR__ . '/universal-nav.php';

/**
 * Render the universal nav with auto-detected auth state.
 *
 * @param string $siteKey  Site key for active-state detection
 * @param string $siteName Display name of the site
 * @param array  $extra    Additional config overrides
 * @return string HTML for header
 */
function renderUniversalNav(string $siteKey, string $siteName, array $extra = []): string
{
    $loggedIn = false;
    $username = '';
    $userId = null;

    // Try member-kit auth
    if (function_exists('isMemberLoggedIn') && isMemberLoggedIn()) {
        $loggedIn = true;
        $username = $_SESSION['member_username'] ?? $_SESSION['username'] ?? '';
        $userId = $_SESSION['member_id'] ?? $_SESSION['user_id'] ?? null;
    } elseif (!empty($_SESSION['user_id'])) {
        $loggedIn = true;
        $username = $_SESSION['username'] ?? '';
        $userId = $_SESSION['user_id'];
    }

    $config = array_merge([
        'site_key'  => $siteKey,
        'site_name' => $siteName,
        'logged_in' => $loggedIn,
        'username'  => $username,
        'user_id'   => $userId,
    ], $extra);

    return engineUniversalHeader($config);
}

/**
 * Render the universal footer.
 *
 * @param string $siteKey  Site key
 * @param array  $extra    Additional config overrides
 * @return string HTML for footer
 */
function renderUniversalFooter(string $siteKey, array $extra = []): string
{
    $loggedIn = false;
    if (function_exists('isMemberLoggedIn') && isMemberLoggedIn()) {
        $loggedIn = true;
    } elseif (!empty($_SESSION['user_id'])) {
        $loggedIn = true;
    }

    $config = array_merge([
        'site_key'  => $siteKey,
        'logged_in' => $loggedIn,
    ], $extra);

    return engineUniversalFooter($config);
}
