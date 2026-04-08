<?php
/**
 * Auth Configuration — Site-specific member-kit setup
 *
 * Copy this file to your site's includes/auth.php and configure the values below.
 * This file initializes the member-kit with your site's branding and settings.
 *
 * MEMBER_MODE must be 'independent' for all non-HipHop sites.
 */

// ── Kit Path ─────────────────────────────────────────────────────────────────
$kitPath = $_ENV['MEMBER_KIT_PATH'] ?? dirname(__DIR__, 2) . '/---member-kit';
require_once $kitPath . '/loader.php';

// ── Site Identity ────────────────────────────────────────────────────────────
// These values control branding on the login/dashboard pages.
define('MEMBER_KIT_SITE_KEY', $_ENV['SITE_KEY'] ?? 'mysite');
define('MEMBER_KIT_URL', $_ENV['MEMBER_KIT_URL'] ?? '/shared/member-kit');

// ── Auth Mode ────────────────────────────────────────────────────────────────
// 'independent' = own members table, own branding, no HipHop.World SSO
// 'hw'          = shared users table, HipHop.World branding + SSO (HipHop sites only)
$_ENV['MEMBER_MODE'] = $_ENV['MEMBER_MODE'] ?? 'independent';

// ── Database Table ───────────────────────────────────────────────────────────
// Prefix for the members table. Default: site-specific prefix.
// Table will be: {prefix}members (e.g., mysite_members)
$_ENV['MEMBER_TABLE_PREFIX'] = $_ENV['MEMBER_TABLE_PREFIX'] ?? '';
$_ENV['MEMBER_TABLE'] = $_ENV['MEMBER_TABLE'] ?? 'members';

// ── Session Configuration ────────────────────────────────────────────────────
$_ENV['MEMBER_SESSION_KEY'] = $_ENV['MEMBER_SESSION_KEY'] ?? 'member_id';

// ── Branding ─────────────────────────────────────────────────────────────────
// Used on login forms, dashboard headers, and email templates.
$_ENV['SITE_NAME'] = $_ENV['SITE_NAME'] ?? 'My Site';
$_ENV['SITE_LOGO'] = $_ENV['SITE_LOGO'] ?? '/assets/img/logo.png';
$_ENV['SITE_PRIMARY_COLOR'] = $_ENV['SITE_PRIMARY_COLOR'] ?? '#3B82F6';

// ── Google OAuth (optional) ──────────────────────────────────────────────────
// Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in .env to enable Google SSO.
// Leave empty to use email/password only.

// ── Default Return URL ───────────────────────────────────────────────────────
// Where to redirect after login if no ?return= parameter is set.
$_GET['return'] = $_GET['return'] ?? '/members';

// ── Translation Stub ─────────────────────────────────────────────────────────
// member-kit templates may call t() for i18n. Provide a stub if not defined.
if (!function_exists('t')) {
    function t(string $key): ?string { return null; }
}

// ── Initialize ───────────────────────────────────────────────────────────────
initMemberAuth($pdo ?? null);

// Ensure CSRF token exists in session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
