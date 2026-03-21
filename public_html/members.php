<?php
/**
 * Oregon Tires — Unified Dashboard
 *
 * Role-based dashboard for members, employees, and admins.
 * - Members:   appointments, vehicles, estimates, messages, care plan, invoices, loyalty, referrals
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

// Point to local bilingual template overrides for auth pages
if (!defined('MEMBER_KIT_SITE_TEMPLATES')) {
    define('MEMBER_KIT_SITE_TEMPLATES', __DIR__ . '/templates');
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

// If admin or employee and no explicit tab requested, redirect to admin panel
if (($isAdmin || $isEmployee) && !isset($_GET['tab'])) {
    // Ensure admin session vars are set before redirecting — the onLogin callback
    // only fires during MemberAuth::login(), so returning members need this fallback
    if ($isAdmin && empty($_SESSION['admin_id'])) {
        $email = $_SESSION['member_email'] ?? '';
        if ($email !== '') {
            try {
                $stmt = $pdo->prepare('SELECT id, role, display_name, language FROM oretir_admins WHERE email = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$email]);
                $localAdmin = $stmt->fetch();
                if ($localAdmin) {
                    $_SESSION['admin_id']       = $localAdmin['id'];
                    $_SESSION['admin_email']    = $email;
                    $_SESSION['admin_role']     = $localAdmin['role'] ?? 'admin';
                    $_SESSION['admin_name']     = $localAdmin['display_name'] ?? $email;
                    $_SESSION['admin_language'] = $localAdmin['language'] ?? 'both';
                    $_SESSION['login_time']     = $_SESSION['login_time'] ?? time();
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                }
            } catch (\Throwable $e) {
                error_log('Admin session recovery failed: ' . $e->getMessage());
            }
        }
        // If recovery failed for ANY reason, don't redirect — show member dashboard
        if (empty($_SESSION['admin_id'])) {
            $isAdmin = false;
            $isEmployee = false;
            $dashboardRole = 'member';
            $_SESSION['dashboard_role'] = 'member';
        }
    }
    if ($isAdmin || $isEmployee) {
        header('Location: /admin/');
        exit;
    }
}

// Auth view routing: /members?view=register|forgot-password|reset-password
$authView = $_GET['view'] ?? 'login';
$validViews = ['login', 'register', 'forgot-password', 'reset-password', 'verify-email', 'resend-verification'];
if (!in_array($authView, $validViews, true)) $authView = 'login';

// Site key for branding
$siteKey = 'oregon_tires';

// Oregon Tires branding config for member-kit dashboard
$memberDashboardConfig = [
    'name'           => 'Oregon Tires Auto Care',
    'logo'           => '/assets/logo.png',
    'favicon'        => '/assets/favicon.ico',
    'default_avatar' => '/assets/logo.png',
    'stylesheets'    => ['/assets/styles.css'],
    'scripts'        => [],
    'nav_include'    => __DIR__ . '/templates/header.php',
    'footer_include' => __DIR__ . '/templates/footer.php',
    'universal_tab_labels' => [
        'profile'  => memberT('profile', $lang),
        'settings' => memberT('settings', $lang),
        'activity' => memberT('activity', $lang),
    ],
    'hide_register_link'       => true,
    'hide_login_activity_link' => true,
    'auth_view'                => $authView,
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
    [
        'id'           => 'invoices',
        'label'        => memberT('my_invoices', $lang),
        'icon'         => '🧾',
        'api_endpoint' => '/api/member/my-invoices.php',
    ],
    [
        'id'           => 'loyalty',
        'label'        => memberT('my_loyalty', $lang),
        'icon'         => '⭐',
        'api_endpoint' => '/api/member/my-loyalty.php',
    ],
    [
        'id'           => 'referrals',
        'label'        => memberT('my_referrals', $lang),
        'icon'         => '🤝',
        'api_endpoint' => '/api/member/my-referral-ui.php',
    ],
];

// Staff banner: if staff explicitly visits /members?tab=X, show link to /admin/
$showStaffBanner = ($isAdmin || $isEmployee) && isset($_GET['tab']);

// Disable wallet connections — not relevant for auto shop
unset($_ENV['METAMASK_ENABLED'], $_ENV['WALLETCONNECT_PROJECT_ID'], $_ENV['COINBASE_WALLET_ENABLED']);

// Staff banner (rendered before dashboard if staff visits /members?tab=X)
if (!empty($showStaffBanner)): ?>
<div style="max-width:900px;margin:1rem auto;padding:0 1rem;">
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-size:14px;color:#166534;"><?= htmlspecialchars(memberT('go_to_admin', $lang)) ?></span>
    <a href="/admin/" style="font-size:14px;font-weight:500;color:#15803d;text-decoration:none;"><?= htmlspecialchars(memberT('admin_panel', $lang)) ?> &rarr;</a>
  </div>
</div>
<?php endif;

// Load universal dashboard template
require MEMBER_KIT_PATH . '/templates/member/dashboard.php';
