<?php
/**
 * Oregon Tires — Customer Dashboard
 *
 * Entry point for /members page.
 * Uses universal dashboard template with custom Oregon Tires tabs.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/member-kit-init.php';
require_once __DIR__ . '/includes/engine-kit-init.php';

// Start session and init member-kit
startSecureSession();
$pdo = getDB();
initMemberKit($pdo);
initEngineKit();

// Set default return URL for login form
if (!MemberAuth::isMemberLoggedIn() && !isset($_GET['return'])) {
    $_GET['return'] = '/members';
}

// Web URL for member-kit CSS/JS assets
if (!defined('MEMBER_KIT_URL')) {
    define('MEMBER_KIT_URL', '/shared/member-kit');
}

// Site key for branding
$siteKey = 'oregon_tires';

// Define Oregon Tires custom dashboard tabs
$memberDashboardTabs = [
    [
        'id'           => 'appointments',
        'label'        => 'My Appointments',
        'icon'         => '📅',
        'api_endpoint' => '/api/member/my-bookings-ui.php',
    ],
    [
        'id'           => 'vehicles',
        'label'        => 'My Vehicles',
        'icon'         => '🚗',
        'api_endpoint' => '/api/member/my-vehicles.php',
    ],
    [
        'id'           => 'estimates',
        'label'        => 'Estimates & Reports',
        'icon'         => '📋',
        'api_endpoint' => '/api/member/my-estimates.php',
    ],
    [
        'id'           => 'messages',
        'label'        => 'Messages',
        'icon'         => '💬',
        'api_endpoint' => '/api/member/my-messages.php',
    ],
];

// Load universal dashboard template
require MEMBER_KIT_PATH . '/templates/member/dashboard.php';
