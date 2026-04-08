<?php
/**
 * Universal Dashboard Template — Member Kit
 *
 * This is the core template for all network sites' /members page.
 * It handles both logged-out (login form) and logged-in (dashboard) states.
 *
 * Expected context variables from caller:
 *   $siteKey (string) — e.g. 'oregontires', '1vsm', 'absurdlywell'
 *   $memberDashboardTabs (array, optional) — site-specific tab definitions
 *   $memberDashboardConfig (array, optional) — overrides (logo, name, nav_links)
 *
 * Tab format: ['id' => 'string', 'label' => 'string', 'icon' => 'string', 'api_endpoint' => 'string', 'template' => 'path' (optional)]
 */

declare(strict_types=1);

// ═══════════════════════════════════════════════════════════════════════════
// INITIALIZATION
// ═══════════════════════════════════════════════════════════════════════════

$siteKey = $siteKey ?? null;
$memberDashboardTabs = $memberDashboardTabs ?? [];
$memberDashboardConfig = $memberDashboardConfig ?? [];

// Auth state
$isLoggedIn = MemberAuth::isMemberLoggedIn();
$member = $isLoggedIn ? MemberAuth::getCurrentMember() : null;

// Honor ?return= when already logged in (redirect to intended page)
if ($isLoggedIn) {
    $returnTarget = $_GET['return'] ?? '';
    $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    if ($returnTarget !== ''
        && $returnTarget !== $currentPath  // Prevent self-redirect loop
        && str_starts_with($returnTarget, '/')
        && !str_starts_with($returnTarget, '//')) {
        header('Location: ' . $returnTarget);
        exit;
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// IF NOT LOGGED IN — RENDER LOGIN FORM
// ═══════════════════════════════════════════════════════════════════════════

if (!$isLoggedIn) {
    // Site-level nav/footer/stylesheets for login page
    $_siteStylesheets = $memberDashboardConfig['stylesheets'] ?? [];
    $_siteNavInclude = $memberDashboardConfig['nav_include'] ?? null;
    $_siteFooterInclude = $memberDashboardConfig['footer_include'] ?? null;
    $_siteScripts = $memberDashboardConfig['scripts'] ?? [];
    $_siteTitle = ($memberDashboardConfig['name'] ?? '') ? 'Sign In — ' . $memberDashboardConfig['name'] : 'Sign In';
    $_siteFavicon = $memberDashboardConfig['favicon'] ?? null;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="color-scheme" content="light dark">
        <meta name="description" content="Sign in to your account">
        <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
        <title><?= htmlspecialchars($_siteTitle) ?></title>
        <?php if ($_siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($_siteFavicon) ?>">
        <?php endif; ?>
        <link rel="stylesheet" href="<?= htmlspecialchars((defined('MEMBER_KIT_URL') && MEMBER_KIT_URL !== '' ? MEMBER_KIT_URL : MEMBER_KIT_PATH)) ?>/css/member.css?v=<?= htmlspecialchars(MEMBER_KIT_VERSION) ?>">
        <?php if (!empty($memberDashboardConfig['head_extra'])): ?>
        <?= $memberDashboardConfig['head_extra'] ?>
        <?php endif; ?>
        <?php $_themeVars = $memberDashboardConfig['theme'] ?? []; ?>
        <style>
            :root {
                --member-bg: <?= htmlspecialchars($_themeVars['--member-bg'] ?? '#0f172a') ?>;
                --member-surface: <?= htmlspecialchars($_themeVars['--member-surface'] ?? '#1e293b') ?>;
                --member-surface-hover: <?= htmlspecialchars($_themeVars['--member-surface-hover'] ?? '#334155') ?>;
                --member-border: <?= htmlspecialchars($_themeVars['--member-border'] ?? '#334155') ?>;
                --member-text: <?= htmlspecialchars($_themeVars['--member-text'] ?? '#f1f5f9') ?>;
                --member-text-muted: <?= htmlspecialchars($_themeVars['--member-text-muted'] ?? '#94a3b8') ?>;
                --member-accent: <?= htmlspecialchars($_themeVars['--member-accent'] ?? '#3b82f6') ?>;
                --member-accent-hover: <?= htmlspecialchars($_themeVars['--member-accent-hover'] ?? '#2563eb') ?>;
                --member-accent-text: <?= htmlspecialchars($_themeVars['--member-accent-text'] ?? '#ffffff') ?>;
                --member-error: <?= htmlspecialchars($_themeVars['--member-error'] ?? '#ef4444') ?>;
                --member-success: <?= htmlspecialchars($_themeVars['--member-success'] ?? '#22c55e') ?>;
                --member-warning: <?= htmlspecialchars($_themeVars['--member-warning'] ?? '#f59e0b') ?>;
                --member-info: <?= htmlspecialchars($_themeVars['--member-info'] ?? '#3b82f6') ?>;
                --member-radius: <?= htmlspecialchars($_themeVars['--member-radius'] ?? '0.5rem') ?>;
                --member-font: <?= htmlspecialchars($_themeVars['--member-font'] ?? "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif") ?>;
            }
            <?php if (empty($_themeVars)): ?>
            @media (prefers-color-scheme: light) {
                :root {
                    --member-bg: #f8fafc;
                    --member-surface: #ffffff;
                    --member-surface-hover: #f1f5f9;
                    --member-border: #e2e8f0;
                    --member-text: #1e293b;
                    --member-text-muted: #64748b;
                }
            }
            <?php endif; ?>
        </style>
        <?php foreach ($_siteStylesheets as $_ss): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($_ss) ?>">
        <?php endforeach; ?>
    </head>
    <body>
        <?php
        // Include site navigation if provided
        if ($_siteNavInclude && file_exists($_siteNavInclude)) {
            include $_siteNavInclude;
        }

        // Get auth.php for the return URL
        $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/members', '?');
        $returnUrl = htmlspecialchars($_GET['return'] ?? $currentPath);
        $csrfToken = MemberAuth::getCsrfToken();

        // Include auth form template based on view
        // Supports: login (default), register, forgot-password, reset-password
        $_authView = $memberDashboardConfig['auth_view'] ?? ($_GET['view'] ?? 'login');
        $_authViewMap = [
            'login'                 => 'login.php',
            'register'              => 'register.php',
            'forgot-password'       => 'forgot-password.php',
            'reset-password'        => 'reset-password.php',
            'verify-email'          => 'verify-email.php',
            'resend-verification'   => 'verify-email.php',
        ];
        $_authFile = $_authViewMap[$_authView] ?? 'login.php';

        if (!empty($memberDashboardConfig['login_template']) && $_authView === 'login' && file_exists($memberDashboardConfig['login_template'])) {
            $_loginTpl = $memberDashboardConfig['login_template'];
        } elseif (MemberAuth::isNetworkMode() && $_authView === 'login') {
            $_loginTpl = __DIR__ . '/login-network.php';
        } else {
            // Check for site-local override first, then fall back to member-kit default
            $_loginTpl = defined('MEMBER_KIT_SITE_TEMPLATES')
                ? (MEMBER_KIT_SITE_TEMPLATES . '/member/' . $_authFile)
                : (__DIR__ . '/' . $_authFile);
            if (!file_exists($_loginTpl)) {
                $_loginTpl = __DIR__ . '/' . $_authFile;
            }
            if (!file_exists($_loginTpl)) {
                $_loginTpl = __DIR__ . '/login.php'; // ultimate fallback
            }
        }
        include $_loginTpl;

        // Include site footer if provided
        if ($_siteFooterInclude && file_exists($_siteFooterInclude)) {
            include $_siteFooterInclude;
        }
        ?>
        <?php foreach ($_siteScripts as $_sScript): ?>
        <script src="<?= htmlspecialchars($_sScript) ?>"></script>
        <?php endforeach; ?>
        <script>window.MEMBER_LOGIN_URL = <?= json_encode(method_exists('MemberAuth', 'getLoginUrl') ? MemberAuth::getLoginUrl() : '/member/login') ?>;</script>
        <script src="<?= htmlspecialchars((defined('MEMBER_KIT_URL') && MEMBER_KIT_URL !== '' ? MEMBER_KIT_URL : MEMBER_KIT_PATH)) ?>/js/member.js?v=<?= htmlspecialchars(MEMBER_KIT_VERSION) ?>"></script>
    </body>
    </html>
    <?php
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// IF LOGGED IN — RENDER DASHBOARD
// ═══════════════════════════════════════════════════════════════════════════

// Load branding from engine-kit if available
$brandingVars = [];
try {
    if (function_exists('engineBranding') && $siteKey) {
        $brandingVars = engineBranding($siteKey);
    }
} catch (\Throwable $e) {
    // Fallback: no branding applied
}

// Get site config from database for name/logo
$siteName = $memberDashboardConfig['name'] ?? 'Member Account';
$siteLogo = $memberDashboardConfig['logo'] ?? null;
$navLinks = $memberDashboardConfig['nav_links'] ?? [];
$_dashStylesheets = $memberDashboardConfig['stylesheets'] ?? [];
$_dashNavInclude = $memberDashboardConfig['nav_include'] ?? null;
$_dashFooterInclude = $memberDashboardConfig['footer_include'] ?? null;
$_dashScripts = $memberDashboardConfig['scripts'] ?? [];

try {
    if (!$siteName || !$siteLogo) {
        $pdo = getDatabase() ?? null;
        if ($pdo && $siteKey) {
            $stmt = $pdo->prepare("SELECT name, branding FROM engine_sites WHERE site_key = ? LIMIT 1");
            $stmt->execute([$siteKey]);
            $site = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($site) {
                if ($site['name']) $siteName = $site['name'];
                if ($site['branding']) {
                    $branding = json_decode($site['branding'], true);
                    if (!empty($branding['logo_url'])) $siteLogo = $branding['logo_url'];
                }
            }
        }
    }
} catch (\Throwable $e) {
    // Fallback to defaults
}

// Get current tab from URL — honor config default_tab if no ?tab= param
$_configDefaultTab = $memberDashboardConfig['default_tab'] ?? 'profile';
$currentTab = $_GET['tab'] ?? $_configDefaultTab;
$csrfToken = MemberAuth::getCsrfToken();

// Build tab list
// Universal tabs get 'Account' group only when the site uses tab groups
$_siteUsesGroups = false;
foreach ($memberDashboardTabs as $_chk) {
    if (!empty($_chk['group'])) { $_siteUsesGroups = true; break; }
}
$_accountGroup = $_siteUsesGroups ? 'Account' : null;

$_utLabels = $memberDashboardConfig['universal_tab_labels'] ?? [];
$universalTabs = [
    [
        'id' => 'account',
        'label' => $_utLabels['account'] ?? $_utLabels['profile'] ?? 'Account',
        'icon' => '👤',
        'group' => $_accountGroup,
        'api_endpoint' => null,
    ],
];

// Allow sites to hide specific universal tabs (e.g. when consolidated into a custom tab)
$_hiddenTabs = $memberDashboardConfig['hidden_tabs'] ?? [];

// Backward compat: if all 4 original account tabs are hidden, also hide the new unified 'account' tab
$_originalAccountTabs = ['profile', 'settings', 'activity'];
if (count(array_intersect($_originalAccountTabs, $_hiddenTabs)) === 4) {
    $_hiddenTabs[] = 'account';
}

$universalTabs = array_filter($universalTabs, fn($t) => !in_array($t['id'], $_hiddenTabs, true));

// Add role-based tabs for network mode
$roleTabs = [];
if (MemberAuth::isNetworkMode() || MemberAuth::isHwMode()) {
    if (MemberAuth::isSiteAdmin()) {
        $roleTabs[] = [
            'id' => 'manage-roles',
            'label' => 'Manage Roles',
            'icon' => '&#x1F511;',
            'group' => $_siteUsesGroups ? 'Admin' : null,
            'template' => __DIR__ . '/tabs/manage-roles.php',
        ];
    }
    if (MemberAuth::isSuperAdmin()) {
        $roleTabs[] = [
            'id' => 'network-roles',
            'label' => 'Network',
            'icon' => '&#x1F310;',
            'group' => $_siteUsesGroups ? 'Admin' : null,
            'template' => __DIR__ . '/tabs/network-roles.php',
        ];
    }
}

$allTabs = array_merge($memberDashboardTabs, $universalTabs, $roleTabs);

// Legacy URL support: map old tab IDs to unified 'account' tab
$_legacyAccountTabs = ['profile', 'settings', 'activity'];
$validTabIds = array_map(fn($t) => $t['id'], $allTabs);
if (in_array($currentTab, $_legacyAccountTabs, true) && !in_array($currentTab, $validTabIds, true)) {
    $currentTab = 'account';
}

// Validate current tab
if (!in_array($currentTab, $validTabIds, true)) {
    $currentTab = 'account';
}

// Display name for user
$displayName = $member['display_name'] ?? $member['username'] ?? $member['email'] ?? 'Member';
$avatarUrl = $member['avatar_url'] ?? $memberDashboardConfig['default_avatar'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your account dashboard">
    <title>Account — <?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars((defined('MEMBER_KIT_URL') && MEMBER_KIT_URL !== '' ? MEMBER_KIT_URL : MEMBER_KIT_PATH)) ?>/css/member.css?v=<?= htmlspecialchars(MEMBER_KIT_VERSION) ?>">
    <?php $_themeVars = $memberDashboardConfig['theme'] ?? []; ?>
    <style>
        :root {
            --member-bg: <?= htmlspecialchars($_themeVars['--member-bg'] ?? '#0f172a') ?>;
            --member-surface: <?= htmlspecialchars($_themeVars['--member-surface'] ?? '#1e293b') ?>;
            --member-surface-hover: <?= htmlspecialchars($_themeVars['--member-surface-hover'] ?? '#334155') ?>;
            --member-border: <?= htmlspecialchars($_themeVars['--member-border'] ?? '#334155') ?>;
            --member-text: <?= htmlspecialchars($_themeVars['--member-text'] ?? '#f1f5f9') ?>;
            --member-text-muted: <?= htmlspecialchars($_themeVars['--member-text-muted'] ?? '#94a3b8') ?>;
            --member-accent: <?= htmlspecialchars($_themeVars['--member-accent'] ?? '#3b82f6') ?>;
            --member-accent-hover: <?= htmlspecialchars($_themeVars['--member-accent-hover'] ?? '#2563eb') ?>;
            --member-accent-text: <?= htmlspecialchars($_themeVars['--member-accent-text'] ?? '#ffffff') ?>;
            --member-error: <?= htmlspecialchars($_themeVars['--member-error'] ?? '#ef4444') ?>;
            --member-success: <?= htmlspecialchars($_themeVars['--member-success'] ?? '#22c55e') ?>;
            --member-warning: <?= htmlspecialchars($_themeVars['--member-warning'] ?? '#f59e0b') ?>;
            --member-info: <?= htmlspecialchars($_themeVars['--member-info'] ?? '#3b82f6') ?>;
            --member-radius: <?= htmlspecialchars($_themeVars['--member-radius'] ?? '0.5rem') ?>;
            --member-font: <?= htmlspecialchars($_themeVars['--member-font'] ?? "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif") ?>;
        }
        <?php if (empty($_themeVars)): ?>
        @media (prefers-color-scheme: light) {
            :root {
                --member-bg: #f8fafc;
                --member-surface: #ffffff;
                --member-surface-hover: #f1f5f9;
                --member-border: #e2e8f0;
                --member-text: #1e293b;
                --member-text-muted: #64748b;
            }
        }
        <?php endif; ?>

        /* Apply engine-kit branding CSS variables */
        <?php foreach ($brandingVars as $varName => $varValue): ?>
            :root { <?= htmlspecialchars($varName) ?>: <?= htmlspecialchars($varValue) ?>; }
        <?php endforeach; ?>

        /* Dashboard-specific layout */
        .member-dashboard {
            display: flex;
            min-height: 100vh;
            background: var(--member-bg);
            color: var(--member-text);
            font-family: var(--member-font);
        }

        .member-dashboard-sidebar {
            width: 250px;
            background: var(--member-surface);
            border-right: 1px solid var(--member-border);
            padding: 1.5rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            overflow-y: auto;
        }

        .member-dashboard-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--member-border);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .member-dashboard-logo {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--member-accent);
            color: var(--member-accent-text);
            border-radius: 0.5rem;
            font-weight: 700;
            overflow: hidden;
        }

        .member-dashboard-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-dashboard-site-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--member-text);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .member-dashboard-nav-item {
            padding: 0.75rem 0.75rem;
            border-radius: var(--member-radius);
            cursor: pointer;
            text-decoration: none;
            color: var(--member-text-muted);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .member-dashboard-nav-item:hover {
            background: var(--member-surface-hover);
            color: var(--member-text);
        }

        .member-dashboard-nav-item.active {
            background: var(--member-accent);
            color: var(--member-accent-text);
        }

        .member-dashboard-nav-group {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--member-text-muted);
            padding: 0.75rem 0.75rem 0.25rem;
            margin-top: 0.5rem;
        }

        .member-dashboard-nav-group:first-child {
            margin-top: 0;
        }

        .member-dashboard-nav-icon {
            display: inline-flex;
            align-items: center;
            font-size: 1.125rem;
            min-width: 1.5rem;
        }

        .member-dashboard-user {
            padding: 1rem 1rem;
            margin-top: auto;
            border-top: 1px solid var(--member-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .member-dashboard-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--member-accent);
            color: var(--member-accent-text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        .member-dashboard-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-dashboard-user-info {
            flex: 1;
            min-width: 0;
        }

        .member-dashboard-user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--member-text);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .member-dashboard-user-email {
            font-size: 0.75rem;
            color: var(--member-text-muted);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .member-dashboard-logout {
            background: none;
            border: none;
            color: var(--member-text-muted);
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0.25rem;
            transition: color 0.2s ease;
            flex-shrink: 0;
            line-height: 0;
        }

        .member-dashboard-logout:hover {
            color: var(--member-error);
        }

        .member-dashboard-logout:disabled {
            cursor: default;
            opacity: 0.6;
        }

        @keyframes mk-spin {
            to { transform: rotate(360deg); }
        }

        .mk-logout-spinner {
            animation: mk-spin 0.7s linear infinite;
        }

        .member-dashboard-main {
            margin-left: 250px;
            flex: 1;
            padding: 2rem;
        }

        .member-dashboard-content {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .member-dashboard {
                flex-direction: column;
            }

            .member-dashboard-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid var(--member-border);
                padding: 1rem;
                flex-direction: row;
                align-items: center;
                gap: 1.5rem;
            }

            .member-dashboard-header {
                flex: 0;
                padding: 0;
                border: none;
                margin: 0;
            }

            .member-dashboard-nav-item {
                white-space: nowrap;
                padding: 0.5rem 0.75rem;
            }

            .member-dashboard-user {
                order: -1;
                flex: 1;
                padding: 0;
                border: none;
                margin: 0;
            }

            .member-dashboard-main {
                margin-left: 0;
                padding: 1rem;
            }

            .member-dashboard-nav-item span {
                display: none;
            }

            .member-dashboard-nav-group {
                display: none;
            }

            .member-dashboard-user-info {
                display: none;
            }
        }

        /* Accessibility */
        .member-dashboard-nav-item:focus,
        .member-dashboard-logout:focus {
            outline: 2px solid var(--member-accent);
            outline-offset: 2px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }

        /* When site navigation is present above the dashboard */
        .member-dashboard.has-site-nav .member-dashboard-sidebar {
            position: sticky;
            top: 0;
            height: auto;
            min-height: calc(100vh - 70px);
            align-self: flex-start;
        }

        .member-dashboard.has-site-nav .member-dashboard-main {
            margin-left: 0;
        }

        /* Hide sidebar branding when site header already shows it */
        .member-dashboard.has-site-nav .member-dashboard-header {
            display: none;
        }
    </style>
    <?php foreach ($_dashStylesheets as $_ds): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($_ds) ?>">
    <?php endforeach; ?>
</head>
<body>
    <?php
    $_hasSiteNav = $_dashNavInclude && file_exists($_dashNavInclude);
    if ($_hasSiteNav) { include $_dashNavInclude; }
    $_showSiteNav = ($memberDashboardConfig['show_site_nav'] ?? true) && $_hasSiteNav;
    ?>
    <div class="member-dashboard<?= $_showSiteNav ? ' has-site-nav' : '' ?>">
        <!-- Sidebar Navigation -->
        <aside class="member-dashboard-sidebar" role="navigation" aria-label="Account navigation">
            <!-- Site Header (compact: logo + name + avatar) -->
            <div class="member-dashboard-header">
                <div class="member-dashboard-logo">
                    <?php if ($siteLogo): ?>
                        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="<?= htmlspecialchars($siteName) ?>">
                    <?php else: ?>
                        <?= htmlspecialchars(substr($siteName, 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="member-dashboard-site-name" style="flex: 1;"><?= htmlspecialchars($siteName) ?></div>
                <div class="member-dashboard-user-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Profile">
                    <?php else: ?>
                        <?= htmlspecialchars(mb_substr($displayName, 0, 1)) ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Navigation -->
                <?php
                // Check if any tab has a 'group' key — enables grouped rendering
                $_hasGroups = false;
                foreach ($allTabs as $_gt) {
                    if (!empty($_gt['group'])) { $_hasGroups = true; break; }
                }
                $_badgeCounts = $memberDashboardConfig['badge_counts'] ?? [];

                // Account tab IDs — always pinned to bottom, never in accordion
                $_accountTabIds = ['account'];

                if ($_hasGroups):
                    // ── Accordion rendering for grouped tabs ──
                    // Build groups: ordered array of [groupName => [tabs]]
                    $_groups = [];
                    $_ungrouped = [];
                    $_accountTabs = [];
                    foreach ($allTabs as $_t) {
                        if (in_array($_t['id'], $_accountTabIds, true)) {
                            $_accountTabs[] = $_t;
                        } elseif (!empty($_t['group'])) {
                            $_groups[$_t['group']][] = $_t;
                        } else {
                            $_ungrouped[] = $_t;
                        }
                    }

                    // Find which group contains the active tab
                    $_activeGroup = null;
                    foreach ($_groups as $_gName => $_gTabs) {
                        foreach ($_gTabs as $_gt) {
                            if ($_gt['id'] === $currentTab) { $_activeGroup = $_gName; break 2; }
                        }
                    }

                    // Render ungrouped tabs first (e.g., Overview)
                    foreach ($_ungrouped as $tab): ?>
                        <a href="?tab=<?= htmlspecialchars($tab['id']) ?>"
                           class="member-dashboard-nav-item <?= $tab['id'] === $currentTab ? 'active' : '' ?>" data-tab="<?= htmlspecialchars($tab['id']) ?>"
                           <?php if ($tab['id'] === $currentTab): ?>aria-current="page"<?php endif; ?>>
                            <span class="member-dashboard-nav-icon"><?= $tab['icon'] ?></span>
                            <span><?= htmlspecialchars($tab['label']) ?></span>
                            <?php if (!empty($_badgeCounts[$tab['id']]) && $_badgeCounts[$tab['id']] > 0): ?>
                            <span class="tab-badge"><?= (int) $_badgeCounts[$tab['id']] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach;

                    // Render accordion sections
                    foreach ($_groups as $_groupName => $_groupTabs):
                        $_groupBadgeTotal = 0;
                        $_groupHasActive = ($_activeGroup === $_groupName);
                        foreach ($_groupTabs as $_gt) {
                            if (!empty($_badgeCounts[$_gt['id']])) $_groupBadgeTotal += (int)$_badgeCounts[$_gt['id']];
                        }
                    ?>
                    <div class="vdash-nav-section" data-group="<?= htmlspecialchars($_groupName) ?>">
                        <button type="button" class="vdash-nav-section-header" aria-expanded="<?= $_groupHasActive ? 'true' : 'false' ?>">
                            <span class="vdash-nav-section-title"><?= htmlspecialchars($_groupName) ?></span>
                            <span class="vdash-nav-section-count"><?= count($_groupTabs) ?></span>
                            <?php if ($_groupBadgeTotal > 0): ?>
                            <span class="vdash-nav-section-badge"><?= $_groupBadgeTotal ?></span>
                            <?php endif; ?>
                            <svg class="vdash-nav-section-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="vdash-nav-section-items" <?php if (!$_groupHasActive): ?>style="display:none"<?php endif; ?>>
                            <?php foreach ($_groupTabs as $tab): ?>
                            <a href="?tab=<?= htmlspecialchars($tab['id']) ?>"
                               class="member-dashboard-nav-item <?= $tab['id'] === $currentTab ? 'active' : '' ?>" data-tab="<?= htmlspecialchars($tab['id']) ?>"
                               <?php if ($tab['id'] === $currentTab): ?>aria-current="page"<?php endif; ?>>
                                <span class="member-dashboard-nav-icon"><?= $tab['icon'] ?></span>
                                <span><?= htmlspecialchars($tab['label']) ?></span>
                                <?php if (!empty($_badgeCounts[$tab['id']]) && $_badgeCounts[$tab['id']] > 0): ?>
                                <span class="tab-badge"><?= (int) $_badgeCounts[$tab['id']] ?></span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach;

                    // Render account tabs pinned at bottom
                    if (!empty($_accountTabs)): ?>
                    <div class="vdash-nav-pinned">
                        <?php foreach ($_accountTabs as $tab): ?>
                        <a href="?tab=<?= htmlspecialchars($tab['id']) ?>"
                           class="member-dashboard-nav-item <?= $tab['id'] === $currentTab ? 'active' : '' ?>" data-tab="<?= htmlspecialchars($tab['id']) ?>"
                           <?php if ($tab['id'] === $currentTab): ?>aria-current="page"<?php endif; ?>>
                            <span class="member-dashboard-nav-icon"><?= $tab['icon'] ?></span>
                            <span><?= htmlspecialchars($tab['label']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif;

                else:
                    // ── Flat rendering for ungrouped sites ──
                    // Separate account tabs from site tabs
                    $_siteTabs = [];
                    $_accountTabs = [];
                    foreach ($allTabs as $_t) {
                        if (in_array($_t['id'], $_accountTabIds, true)) {
                            $_accountTabs[] = $_t;
                        } else {
                            $_siteTabs[] = $_t;
                        }
                    }
                    foreach ($_siteTabs as $tab): ?>
                        <a href="?tab=<?= htmlspecialchars($tab['id']) ?>"
                           class="member-dashboard-nav-item <?= $tab['id'] === $currentTab ? 'active' : '' ?>" data-tab="<?= htmlspecialchars($tab['id']) ?>"
                           <?php if ($tab['id'] === $currentTab): ?>aria-current="page"<?php endif; ?>>
                            <span class="member-dashboard-nav-icon"><?= $tab['icon'] ?></span>
                            <span><?= htmlspecialchars($tab['label']) ?></span>
                            <?php if (!empty($_badgeCounts[$tab['id']]) && $_badgeCounts[$tab['id']] > 0): ?>
                            <span class="tab-badge"><?= (int) $_badgeCounts[$tab['id']] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach;
                    if (!empty($_accountTabs)): ?>
                    <div class="vdash-nav-divider"></div>
                    <?php foreach ($_accountTabs as $tab): ?>
                        <a href="?tab=<?= htmlspecialchars($tab['id']) ?>"
                           class="member-dashboard-nav-item <?= $tab['id'] === $currentTab ? 'active' : '' ?>" data-tab="<?= htmlspecialchars($tab['id']) ?>"
                           <?php if ($tab['id'] === $currentTab): ?>aria-current="page"<?php endif; ?>>
                            <span class="member-dashboard-nav-icon"><?= $tab['icon'] ?></span>
                            <span><?= htmlspecialchars($tab['label']) ?></span>
                        </a>
                    <?php endforeach;
                    endif;
                endif; ?>
            <?php if ($_hasGroups): ?>
            <script>
            // Restore accordion collapsed state synchronously to prevent layout shift
            (function(){
                try {
                    var c = JSON.parse(localStorage.getItem('vdash_sections_collapsed') || '{}');
                    document.querySelectorAll('.vdash-nav-section').forEach(function(s) {
                        var g = s.getAttribute('data-group');
                        var h = s.querySelector('.vdash-nav-section-header');
                        var items = s.querySelector('.vdash-nav-section-items');
                        if (!h || !items) return;
                        var hasActive = items.querySelector('.member-dashboard-nav-item.active');
                        if (!hasActive && c[g] === true) {
                            h.setAttribute('aria-expanded', 'false');
                            items.style.display = 'none';
                        }
                    });
                } catch(e) {}
            })();
            </script>
            <?php endif; ?>

            <!-- Simplified User Card — primary identity lives in top bar dropdown -->
            <div class="member-dashboard-user">
                <div class="member-dashboard-user-avatar">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Profile">
                    <?php else: ?>
                        <?= htmlspecialchars(mb_substr($displayName, 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="member-dashboard-user-info">
                    <div class="member-dashboard-user-name"><?= htmlspecialchars($displayName) ?></div>
                    <?php $_vpText = (($_GET['lang'] ?? $_SESSION['member_lang'] ?? $_COOKIE['lang'] ?? 'en') === 'es') ? 'Mi Cuenta' : 'My Account'; ?>
                    <a href="?tab=account" class="member-dashboard-user-link"><?= $_vpText ?></a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="member-dashboard-main" role="main">
            <div class="member-dashboard-content">
                <?php
                // Render the current tab
                $currentTabConfig = array_filter($allTabs, fn($t) => $t['id'] === $currentTab);
                $currentTabConfig = array_pop($currentTabConfig);

                if ($currentTabConfig) {
                    // Check if there's a custom template
                    if (!empty($currentTabConfig['template'])) {
                        if (file_exists($currentTabConfig['template'])) {
                            include $currentTabConfig['template'];
                        } else {
                            echo '<div class="member-alert member-alert--error">Template not found.</div>';
                        }
                    } else {
                        // Load built-in templates for universal tabs
                        switch ($currentTabConfig['id']) {
                            case 'account':
                                include __DIR__ . '/account.php';
                                break;
                            default:
                                // Load from API endpoint if available
                                if (!empty($currentTabConfig['api_endpoint'])) {
                                    ?>
                                    <div id="tab-content-loader" data-endpoint="<?= htmlspecialchars($currentTabConfig['api_endpoint']) ?>">
                                        <p style="color: var(--member-text-muted);">Loading...</p>
                                    </div>
                                    <?php
                                } else {
                                    echo '<div class="member-alert member-alert--error">Tab not found.</div>';
                                }
                        }
                    }
                }
                ?>
            </div>
        </main>
    </div>

    <script>window.MEMBER_LOGIN_URL = <?= json_encode(method_exists('MemberAuth', 'getLoginUrl') ? MemberAuth::getLoginUrl() : '/member/login') ?>;</script>
    <script src="<?= htmlspecialchars((defined('MEMBER_KIT_URL') && MEMBER_KIT_URL !== '' ? MEMBER_KIT_URL : MEMBER_KIT_PATH)) ?>/js/member.js?v=<?= htmlspecialchars(MEMBER_KIT_VERSION) ?>"></script>
    <script>
        function dashboardLogout(btn) {
            if (btn) {
                btn.disabled = true;
                btn.title = 'Signing out\u2026';
                var icon = btn.querySelector('.mk-logout-icon');
                var spinner = btn.querySelector('.mk-logout-spinner');
                if (icon) icon.style.display = 'none';
                if (spinner) spinner.style.display = '';
            }
            var meta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = meta ? meta.content : '';
            fetch('/api/member/logout.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: csrfToken })
            })
                .then(function() { window.location.href = '/'; })
                .catch(function() { window.location.href = '/'; });
        }

        // Load dynamic tab content from API
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('tab-content-loader');
            if (loader && loader.dataset.endpoint) {
                const endpoint = loader.dataset.endpoint;
                fetch(endpoint, { credentials: 'include' })
                    .then(r => r.text())
                    .then(html => {
                        const el = document.createElement('div');
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        while (doc.body.firstChild) { el.appendChild(doc.body.firstChild); }
                        loader.replaceWith(el);
                    })
                    .catch(err => {
                        const el = document.createElement('div');
                        el.className = 'member-alert member-alert--error';
                        el.textContent = 'Error loading content.';
                        loader.replaceWith(el);
                        console.error('Tab load error:', err);
                    });
            }
        });
    </script>
    <?php foreach ($_dashScripts as $_dScript): ?>
    <script src="<?= htmlspecialchars($_dScript) ?>"></script>
    <?php endforeach; ?>
    <?php if ($_dashFooterInclude && file_exists($_dashFooterInclude)) { include $_dashFooterInclude; } ?>
</body>
</html>
