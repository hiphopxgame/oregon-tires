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

// ── Bridge admin session to member-kit session ──────────────────���───────────
// Admin logged in at /admin/ has admin_id but not member_id. Bridge it so the
// member dashboard works when admins click "My Account".
if (!MemberAuth::isMemberLoggedIn() && !empty($_SESSION['admin_id'])) {
    $adminEmail = $_SESSION['admin_email'] ?? '';
    if ($adminEmail !== '') {
        try {
            // Look up existing member record by email
            $stmt = $pdo->prepare('SELECT * FROM members WHERE email = ? LIMIT 1');
            $stmt->execute([$adminEmail]);
            $memberRow = $stmt->fetch();

            if (!$memberRow) {
                // Create a member record for this admin
                $adminName = $_SESSION['admin_name'] ?? explode('@', $adminEmail)[0];
                $username  = preg_replace('/[^a-zA-Z0-9]/', '', $adminName) ?: 'admin';
                // Ensure unique username
                $baseUsername = strtolower($username);
                $username = $baseUsername;
                $counter = 0;
                while ($counter < 100) {
                    $check = $pdo->prepare('SELECT id FROM members WHERE username = ? LIMIT 1');
                    $check->execute([$username]);
                    if (!$check->fetch()) break;
                    $counter++;
                    $username = $baseUsername . $counter;
                }

                $pdo->prepare(
                    'INSERT INTO members (email, username, display_name, role, is_active, is_admin, email_verified_at, created_at)
                     VALUES (?, ?, ?, ?, 1, 1, NOW(), NOW())'
                )->execute([$adminEmail, $username, $_SESSION['admin_name'] ?? $adminName, 'admin']);
                $newId = (int) $pdo->lastInsertId();
                $stmt = $pdo->prepare('SELECT * FROM members WHERE id = ? LIMIT 1');
                $stmt->execute([$newId]);
                $memberRow = $stmt->fetch();
            }

            if ($memberRow) {
                // Bridge: set member-kit session vars directly (don't call
                // startAuthenticatedSession which regenerates session ID and
                // would wipe admin session vars)
                $_SESSION['member_id']         = (int) $memberRow['id'];
                $_SESSION['member_email']      = $memberRow['email'];
                $_SESSION['member_username']   = $memberRow['username'] ?? '';
                $_SESSION['is_super_admin']    = !empty($memberRow['is_admin']);
                $_SESSION['sso_session_start'] = $_SESSION['login_time'] ?? time();
                $_SESSION['dashboard_role']    = 'admin';
            }
        } catch (\Throwable $e) {
            error_log('Admin→member session bridge failed: ' . $e->getMessage());
        }
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
    'head_extra'     => '<!-- Google tag (gtag.js) --><script async src="https://www.googletagmanager.com/gtag/js?id=G-PCK6ZYFHQ0"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","G-PCK6ZYFHQ0");</script>',
    'nav_include'    => __DIR__ . '/templates/header.php',
    'footer_include' => __DIR__ . '/templates/footer.php',
    'universal_tab_labels' => [
        'account'  => $lang === 'es' ? 'Mi Cuenta' : 'Account Settings',
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

// Load universal dashboard template — buffer output via callback to inject a11y
// landmarks (skip-to-content link + <main id="main"> wrapper) even when the
// shared kit calls exit() mid-render (login view does this).
ob_start(function (string $html): string {
    if (stripos($html, '<body') === false) return $html;
    $skip = '<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 bg-white text-black px-4 py-2 rounded shadow">Skip to content</a><main id="main">';
    $html = preg_replace('/(<body\b[^>]*>)/i', '$1' . $skip, $html, 1);
    $html = preg_replace('/(<\/body>)/i', '</main>$1', $html, 1);
    return $html;
});
require MEMBER_KIT_PATH . '/templates/member/dashboard.php';
ob_end_flush();
