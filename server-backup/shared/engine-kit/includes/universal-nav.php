<?php
/**
 * Universal Navigation — Shared header/footer for all 1vsM network sites
 *
 * Renders the HipHop.World universal nav bar that sits above each site's
 * own navigation. Works on hiphop.* addon domains AND non-hiphop sites
 * (1vsm.com, oregon.tires, 1oh6.events).
 *
 * Usage:
 *   require_once $engineKitPath . '/includes/universal-nav.php';
 *   echo engineUniversalHeader([
 *       'site_key'  => 'oregontires',
 *       'site_name' => 'Oregon Tires',
 *       'logged_in' => !empty($_SESSION['user_id']),
 *       'username'  => $_SESSION['username'] ?? '',
 *   ]);
 */

require_once __DIR__ . '/network-constants.php';

/**
 * Render the universal header bar HTML.
 *
 * @param array $config {
 *   @type string $site_key      Domain key for active-state detection
 *   @type string $site_name     Current site display name
 *   @type bool   $show_site_nav Show site-specific nav below universal bar (default: true)
 *   @type bool   $logged_in     Auth state
 *   @type string $username      For avatar initial
 *   @type int|null $user_id     For credits API
 *   @type string $hub_url       Hub URL (default: HH_HUB)
 *   @type string $login_url     Login URL (auto-generated if empty)
 *   @type string $theme         'dark' (default) or 'light'
 *   @type string $logo_url      Custom logo URL (default: hub logo)
 * }
 * @return string HTML
 */
function engineUniversalHeader(array $config = []): string
{
    $siteKey    = $config['site_key'] ?? 'world';
    $siteName   = $config['site_name'] ?? '';
    $loggedIn   = $config['logged_in'] ?? false;
    $username   = $config['username'] ?? '';
    $userId     = $config['user_id'] ?? null;
    $hubUrl     = $config['hub_url'] ?? HH_HUB;
    $theme      = $config['theme'] ?? 'dark';
    $logoUrl    = $config['logo_url'] ?? ($hubUrl . '/assets/img/logo.png');

    // Build login URL with return-to-current-site
    $currentHost = $_SERVER['HTTP_HOST'] ?? 'hiphop.world';
    $currentScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $loginUrl = $config['login_url'] ?? ($hubUrl . '/login?return=' . urlencode($currentScheme . '://' . $currentHost));

    // Nav items
    $primaryNav = HH_NAV_SITES;
    $networkNav = HH_NAV_NETWORK;

    // Detect active state — check if current site matches any nav item
    $headerActive = $siteKey;

    // CSS class: use hub's asset URL for the CSS file
    $cssUrl = $hubUrl . '/engine-kit/assets/css/universal-nav.css';
    $jsUrl  = $hubUrl . '/engine-kit/assets/js/universal-nav.js';

    ob_start();
    ?>
<!-- Universal Nav — HipHop.World Network -->
<link rel="stylesheet" href="<?= htmlspecialchars($cssUrl) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
<div id="hhw-universal-nav" class="hhw-nav" data-theme="<?= htmlspecialchars($theme) ?>" data-hub="<?= htmlspecialchars($hubUrl) ?>" data-logged-in="<?= $loggedIn ? '1' : '0' ?>">
    <div class="hhw-nav-inner">
        <!-- Logo + Brand -->
        <div class="hhw-nav-left">
            <a href="<?= htmlspecialchars($hubUrl) ?>" class="hhw-nav-logo" aria-label="HipHop.World">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="HipHop.World" width="28" height="28">
            </a>
            <a href="<?= htmlspecialchars($hubUrl) ?>" class="hhw-nav-brand">HIPHOP<span class="hhw-gold">.WORLD</span></a>
            <span class="hhw-nav-beta">BETA</span>
        </div>

        <!-- Desktop nav: Audio, Directory, Chat, Community -->
        <nav class="hhw-nav-center" aria-label="HipHop.World Network">
            <?php foreach ($primaryNav as $key => $site):
                $isActive = ($headerActive === $key);
                $url = 'https://' . $site['domain'];
            ?>
            <a href="<?= htmlspecialchars($url) ?>" class="hhw-nav-link<?= $isActive ? ' hhw-active' : '' ?>">
                <svg aria-hidden="true" class="hhw-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $site['icon'] ?>"/>
                </svg>
                <?= htmlspecialchars($site['label']) ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Right side: Network + Credits + Avatar/Join -->
        <div class="hhw-nav-right">
            <!-- Network link (desktop) -->
            <a href="https://<?= htmlspecialchars($networkNav['domain']) ?>" class="hhw-nav-link hhw-nav-desktop<?= ($headerActive === 'network') ? ' hhw-active' : '' ?>">
                <svg aria-hidden="true" class="hhw-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $networkNav['icon'] ?>"/>
                </svg>
                Network
            </a>

            <?php if ($loggedIn): ?>
            <!-- Credits meter -->
            <a href="<?= htmlspecialchars($hubUrl) ?>/credits" class="hhw-nav-credits" title="Credits">
                <span class="hhw-credits-dot"></span>
                <span id="hhw-credits-amount">--</span>
            </a>

            <!-- Avatar dropdown -->
            <div class="hhw-nav-avatar-wrap">
                <button id="hhw-avatar-btn" class="hhw-nav-avatar-btn" aria-label="Account menu" aria-expanded="false" aria-haspopup="true">
                    <div class="hhw-nav-avatar-circle">
                        <?= htmlspecialchars(strtoupper(substr($username ?: 'U', 0, 1))) ?>
                    </div>
                    <svg aria-hidden="true" class="hhw-nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="hhw-avatar-dropdown" class="hhw-nav-dropdown" role="menu" hidden>
                    <div class="hhw-dropdown-header">
                        <p class="hhw-dropdown-user"><?= htmlspecialchars($username) ?></p>
                    </div>
                    <a href="<?= htmlspecialchars($hubUrl) ?>/members" class="hhw-dropdown-item" role="menuitem">Dashboard</a>
                    <a href="<?= htmlspecialchars($hubUrl) ?>/credits" class="hhw-dropdown-item" role="menuitem">Billing</a>
                    <a href="<?= htmlspecialchars($hubUrl) ?>/settings" class="hhw-dropdown-item" role="menuitem">Settings</a>
                    <div class="hhw-dropdown-divider"></div>
                    <a href="<?= htmlspecialchars($hubUrl) ?>/logout" class="hhw-dropdown-item hhw-dropdown-logout" role="menuitem">Logout</a>
                </div>
            </div>
            <?php else: ?>
            <!-- Guest: Join CTA -->
            <a href="<?= htmlspecialchars($loginUrl) ?>" class="hhw-nav-join">
                Join Free
                <svg aria-hidden="true" class="hhw-nav-join-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="<?= htmlspecialchars($loginUrl) ?>" class="hhw-nav-join-mobile">Join</a>
            <?php endif; ?>

            <!-- Mobile menu button -->
            <button id="hhw-mobile-btn" class="hhw-nav-mobile-btn" aria-label="Menu" aria-expanded="false" aria-controls="hhw-mobile-menu">
                <svg id="hhw-menu-open" class="hhw-mobile-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="hhw-menu-close" class="hhw-mobile-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" hidden><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Mobile dropdown -->
    <div id="hhw-mobile-menu" class="hhw-mobile-menu" hidden>
        <div class="hhw-mobile-inner">
            <?php
            $allNavSites = $primaryNav;
            $allNavSites['network'] = $networkNav;
            foreach ($allNavSites as $key => $site):
                $isActive = ($headerActive === $key);
                $url = 'https://' . $site['domain'];
            ?>
            <a href="<?= htmlspecialchars($url) ?>" class="hhw-mobile-link<?= $isActive ? ' hhw-active' : '' ?>">
                <svg aria-hidden="true" class="hhw-mobile-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $site['icon'] ?>"/>
                </svg>
                <?= htmlspecialchars($site['label']) ?>
            </a>
            <?php endforeach; ?>

            <?php if (!$loggedIn): ?>
            <div class="hhw-mobile-cta">
                <a href="<?= htmlspecialchars($loginUrl) ?>" class="hhw-mobile-join">
                    Join Free
                    <svg aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
            <?php else: ?>
            <div class="hhw-mobile-user">
                <a href="<?= htmlspecialchars($hubUrl) ?>/credits" class="hhw-mobile-credits">
                    <span class="hhw-credits-dot"></span>
                    <span id="hhw-mobile-credits-amount">--</span> Credits
                </a>
                <a href="<?= htmlspecialchars($hubUrl) ?>/members" class="hhw-mobile-link">Dashboard</a>
                <a href="<?= htmlspecialchars($hubUrl) ?>/logout" class="hhw-mobile-logout">Logout</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="hhw-nav-spacer"></div>
<script src="<?= htmlspecialchars($jsUrl) ?>"></script>
<?php
    return ob_get_clean();
}

/**
 * Render the universal footer HTML.
 *
 * @param array $config Same as header config
 * @return string HTML
 */
function engineUniversalFooter(array $config = []): string
{
    $siteKey    = $config['site_key'] ?? 'world';
    $loggedIn   = $config['logged_in'] ?? false;
    $hubUrl     = $config['hub_url'] ?? HH_HUB;

    $footerGroups = HH_FOOTER_GROUPS;
    $domainColors = HH_DOMAIN_COLORS;
    $activeSites  = HH_ACTIVE_SITES;

    ob_start();
    ?>
<!-- Universal Footer — HipHop.World Network -->
<footer class="hhw-footer">
    <div class="hhw-footer-inner">
        <!-- Network Domain Map -->
        <div class="hhw-footer-grid">
            <?php foreach ($footerGroups as $groupName => $domains): ?>
            <div class="hhw-footer-group">
                <h4 class="hhw-footer-group-title"><?= htmlspecialchars($groupName) ?></h4>
                <ul class="hhw-footer-list">
                    <?php foreach ($domains as $dKey => $dLabel):
                        $color = $domainColors[$dKey] ?? '#FFD700';
                        $isCurrent = ($dKey === $siteKey);
                        $isActive = in_array($dKey, $activeSites, true);
                        $tld = strtoupper($dKey);
                    ?>
                    <li>
                        <?php if ($isActive): ?>
                        <a href="https://hiphop.<?= htmlspecialchars($dKey) ?>"
                           class="hhw-footer-link<?= $isCurrent ? ' hhw-current' : '' ?>"
                           <?= $isCurrent ? 'aria-current="true"' : '' ?>>
                            <span class="hhw-footer-brand">HIPHOP</span><span class="hhw-footer-tld" style="color: <?= htmlspecialchars($color) ?>">.<?= htmlspecialchars($tld) ?></span>
                        </a>
                        <?php else: ?>
                        <span class="hhw-footer-link hhw-footer-soon" title="Coming Soon">
                            <span class="hhw-footer-brand">HIPHOP</span><span class="hhw-footer-tld" style="color: <?= htmlspecialchars($color) ?>">.<?= htmlspecialchars($tld) ?></span>
                            <span class="hhw-footer-badge">Soon</span>
                        </span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($loggedIn): ?>
        <div class="hhw-footer-quick">
            <a href="<?= htmlspecialchars($hubUrl) ?>/members">Dashboard</a>
            <span class="hhw-footer-sep">|</span>
            <a href="<?= htmlspecialchars($hubUrl) ?>/profile">Profile</a>
            <span class="hhw-footer-sep">|</span>
            <a href="https://hiphop.cash">Wallet</a>
            <span class="hhw-footer-sep">|</span>
            <a href="<?= htmlspecialchars($hubUrl) ?>/settings">Settings</a>
        </div>
        <?php endif; ?>

        <!-- Bottom Row -->
        <div class="hhw-footer-bottom">
            <p class="hhw-footer-copy">&copy; <?= date('Y') ?> <span class="hhw-footer-brand">HIPHOP.WORLD</span></p>
            <div class="hhw-footer-links">
                <a href="<?= htmlspecialchars($hubUrl) ?>/about">About</a>
                <a href="<?= htmlspecialchars($hubUrl) ?>/contact">Contact</a>
                <a href="<?= htmlspecialchars($hubUrl) ?>/terms">Terms</a>
                <a href="<?= htmlspecialchars($hubUrl) ?>/privacy">Privacy</a>
            </div>
        </div>

        <p class="hhw-footer-powered">Powered by <a href="https://1vsM.com" target="_blank" rel="noopener noreferrer">1vsM.com</a></p>
    </div>
</footer>
<?php
    return ob_get_clean();
}
