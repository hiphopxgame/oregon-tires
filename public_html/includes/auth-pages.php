<?php
/**
 * auth-pages.php — Auth page renderer for Oregon Tires
 *
 * Renders login, register, forgot-password, reset-password, account pages
 * by delegating to member-kit templates with site-appropriate branding.
 *
 * Usage (3-line page entry point):
 *   <?php
 *   require_once __DIR__ . '/includes/auth-pages.php';
 *   renderAuthPage('login');
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/member-translations.php';

// Define t() BEFORE member-kit loads so templates get translations
if (!function_exists('t')) {
    function t(string $key): ?string {
        $val = memberT($key);
        return $val !== $key ? $val : null;
    }
}

require_once __DIR__ . '/member-kit-init.php';
require_once __DIR__ . '/engine-kit-init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Override bootstrap's application/json — this is an HTML page
header('Content-Type: text/html; charset=utf-8');

/**
 * Render an authentication page via member-kit.
 *
 * @param string $page One of: 'login', 'forgot-password', 'reset-password', 'account'
 */
function renderAuthPage(string $page): void {
    if (!MEMBER_KIT_PATH || !class_exists('MemberAuth')) {
        http_response_code(500);
        die('Member authentication is not configured');
    }

    $templateMap = [
        'login' => 'login.php',
        'forgot-password' => 'forgot-password.php',
        'reset-password' => 'reset-password.php',
        'account' => 'settings.php',
    ];

    $templateFile = $templateMap[$page] ?? null;
    if (!$templateFile) {
        http_response_code(404);
        die('Page not found');
    }

    $templatePath = MEMBER_KIT_PATH . '/templates/member/' . $templateFile;
    if (!file_exists($templatePath)) {
        http_response_code(500);
        error_log("Member-kit template not found: {$templatePath}");
        die('Authentication page unavailable');
    }

    // Set variables member-kit templates expect
    $siteKey = 'oregon_tires';
    $siteName = 'Oregon Tires Auto Care';
    $pdo = getDB();

    // For account page, require authentication
    if ($page === 'account') {
        $user = MemberAuth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }
    }

    // For login/forgot/reset, redirect if already logged in
    if (in_array($page, ['login', 'forgot-password', 'reset-password'])) {
        if (MemberAuth::user()) {
            header('Location: /members');
            exit;
        }
    }

    require $templatePath;
}
