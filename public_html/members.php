<?php
/**
 * Oregon Tires — Unified Dashboard
 *
 * Role-based dashboard for members, employees, and admins.
 * - Members:   appointments, vehicles, estimates, messages, care plan
 * - Employees: + my schedule, assigned work
 * - Admins:    + admin panel access, all employee tabs
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/member-translations.php';

// Define t() BEFORE member-kit loads so templates get translations
if (!function_exists('t')) {
    function t(string $key): ?string {
        $val = memberT($key);
        return $val !== $key ? $val : null;
    }
}

require_once __DIR__ . '/includes/member-kit-init.php';
require_once __DIR__ . '/includes/engine-kit-init.php';

// Override bootstrap's application/json — this is an HTML page
header('Content-Type: text/html; charset=utf-8');

// Web URL for member-kit CSS/JS assets (must be before initMemberKit → loader.php)
if (!defined('MEMBER_KIT_URL')) {
    define('MEMBER_KIT_URL', '/shared/member-kit');
}

// Start session and init member-kit
startSecureSession();
$pdo = getDB();
initMemberKit($pdo);
initEngineKit();

$lang = getMemberLang();

// Set default return URL for login form
if (!MemberAuth::isMemberLoggedIn() && !isset($_GET['return'])) {
    $_GET['return'] = '/members';
}

// Detect role from session (set by onLogin callback)
$dashboardRole = $_SESSION['dashboard_role'] ?? 'member';
// Fallback: check DB if session doesn't have role yet
if ($dashboardRole === 'member' && MemberAuth::isMemberLoggedIn()) {
    $memberId = $_SESSION['member_id'] ?? null;
    if ($memberId) {
        try {
            $stmt = $pdo->prepare('SELECT role FROM members WHERE id = ? LIMIT 1');
            $stmt->execute([$memberId]);
            $row = $stmt->fetch();
            if ($row && $row['role'] !== 'member') {
                $dashboardRole = $row['role'];
                $_SESSION['dashboard_role'] = $dashboardRole;
            }
        } catch (\Throwable $e) {
            // role column may not exist pre-migration
        }
    }
}

$isEmployee = in_array($dashboardRole, ['employee', 'admin'], true);
$isAdmin    = $dashboardRole === 'admin';

// Site key for branding
$siteKey = 'oregon_tires';

// Oregon Tires branding config for member-kit dashboard
$memberDashboardConfig = [
    'name'           => 'Oregon Tires Auto Care',
    'logo'           => '/assets/logo.png',
    'favicon'        => '/assets/favicon.ico',
    'stylesheets'    => ['/assets/styles.css'],
    'scripts'        => [],
    'nav_include'    => __DIR__ . '/templates/header.php',
    'footer_include' => __DIR__ . '/templates/footer.php',
    'universal_tab_labels' => [
        'profile'  => memberT('profile', $lang),
        'settings' => memberT('settings', $lang),
        'activity' => memberT('activity', $lang),
        'security' => memberT('security', $lang),
    ],
    'hide_register_link'       => true,
    'hide_login_activity_link' => true,
];

// ── Build tabs based on role ─────────────────────────────────────────────

// Base tabs — all roles see these
$memberDashboardTabs = [
    [
        'id'           => 'appointments',
        'label'        => memberT('my_appointments', $lang),
        'icon'         => '📅',
        'api_endpoint' => '/api/member/my-bookings-ui.php',
    ],
    [
        'id'           => 'vehicles',
        'label'        => memberT('my_vehicles', $lang),
        'icon'         => '🚗',
        'api_endpoint' => '/api/member/my-vehicles.php',
    ],
    [
        'id'           => 'estimates',
        'label'        => memberT('estimates_reports', $lang),
        'icon'         => '📋',
        'api_endpoint' => '/api/member/my-estimates.php',
    ],
    [
        'id'           => 'messages',
        'label'        => memberT('messages', $lang),
        'icon'         => '💬',
        'api_endpoint' => '/api/member/my-messages.php',
    ],
    [
        'id'           => 'care-plan',
        'label'        => memberT('care_plan', $lang),
        'icon'         => '🛡️',
        'api_endpoint' => '/api/member/my-care-plan.php',
    ],
];

// Employee + Admin tabs
if ($isEmployee) {
    $memberDashboardTabs[] = [
        'id'           => 'my-schedule',
        'label'        => memberT('my_schedule', $lang),
        'icon'         => '🕐',
        'api_endpoint' => '/api/member/my-schedule.php',
    ];
    $memberDashboardTabs[] = [
        'id'           => 'assigned-work',
        'label'        => memberT('assigned_work', $lang),
        'icon'         => '🔧',
        'api_endpoint' => '/api/member/my-assigned-work.php',
    ];
}

// Admin-only tabs
if ($isAdmin) {
    $memberDashboardTabs[] = [
        'id'       => 'admin-panel',
        'label'    => memberT('admin_panel', $lang),
        'icon'     => '⚙️',
        'template' => __DIR__ . '/templates/dashboard-admin-tab.php',
    ];
}

// Disable wallet connections — not relevant for auto shop
unset($_ENV['METAMASK_ENABLED'], $_ENV['WALLETCONNECT_PROJECT_ID'], $_ENV['COINBASE_WALLET_ENABLED']);

// Load universal dashboard template
require MEMBER_KIT_PATH . '/templates/member/dashboard.php';
