<?php
/**
 * Oregon Tires — Forgot Password Page
 *
 * Thin wrapper around member-kit's forgot-password template with Oregon Tires branding.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/member-translations.php';

// Define t() BEFORE member-kit loads so templates get translations
if (!function_exists('t')) {
    function t(string $key): ?string {
        $val = memberT($key);
        return $val !== $key ? $val : null;
    }
}

require_once __DIR__ . '/../includes/member-kit-init.php';
require_once __DIR__ . '/../includes/engine-kit-init.php';

header('Content-Type: text/html; charset=utf-8');

if (!defined('MEMBER_KIT_URL')) {
    define('MEMBER_KIT_URL', '/shared/member-kit');
}

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);
initEngineKit();

// Already logged in → redirect to dashboard
if (MemberAuth::isMemberLoggedIn()) {
    header('Location: /members');
    exit;
}

$csrfToken = MemberAuth::getCsrfToken();
$ssoEnabled = !empty($_ENV['SSO_CLIENT_ID'] ?? null);
$siteName = 'Oregon Tires Auto Care';

$_siteStylesheets = ['/assets/styles.css'];
$_siteFavicon = '/assets/favicon.ico';
$_siteTitle = 'Reset Password — Oregon Tires Auto Care';
$_siteNavInclude = __DIR__ . '/../templates/header.php';
$_siteFooterInclude = __DIR__ . '/../templates/footer.php';

$memberDashboardConfig = $memberDashboardConfig ?? [];
$_themeVars = $memberDashboardConfig['theme'] ?? [];
?>
<!DOCTYPE html>
<html lang="<?= getMemberLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="Reset your Oregon Tires Auto Care account password">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <title><?= htmlspecialchars($_siteTitle) ?></title>
    <link rel="icon" href="<?= htmlspecialchars($_siteFavicon) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(MEMBER_KIT_URL) ?>/css/member.css?v=<?= htmlspecialchars(MEMBER_KIT_VERSION) ?>">
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
    if ($_siteNavInclude && file_exists($_siteNavInclude)) {
        include $_siteNavInclude;
    }

    include __DIR__ . '/../templates/member/forgot-password.php';

    if ($_siteFooterInclude && file_exists($_siteFooterInclude)) {
        include $_siteFooterInclude;
    }
    ?>
    <script>window.MEMBER_LOGIN_URL = <?= json_encode(method_exists('MemberAuth', 'getLoginUrl') ? MemberAuth::getLoginUrl() : '/member/login') ?>;</script>
    <script src="<?= htmlspecialchars(MEMBER_KIT_URL) ?>/js/member.js?v=<?= htmlspecialchars(MEMBER_KIT_VERSION) ?>"></script>
</body>
</html>
