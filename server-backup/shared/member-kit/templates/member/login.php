<?php
/**
 * Login Template — Member Kit
 *
 * Three-tier login hierarchy:
 * 1. PRIMARY — Email + Password (always visible)
 * 2. SECONDARY — Social connections (HHW SSO, Google, Discord, etc.)
 * 3. TERTIARY — Wallet connections (MetaMask, WalletConnect, Coinbase)
 *
 * Variables available from parent layout:
 *   $csrfToken — from MemberAuth::getCsrfToken()
 *   $error — optional error message from URL params
 *   $verified — optional success flag (email just verified)
 */

$error = $_GET['error'] ?? null;
$verified = isset($_GET['verified']);
$returnUrl = htmlspecialchars($_GET['return'] ?? '/member/profile');
$registered = isset($_GET['registered']);
$maskedEmail = htmlspecialchars($_GET['email'] ?? '');

// Evaluate enabled auth methods
$ssoEnabled = !empty($_ENV['SSO_CLIENT_ID'] ?? null);
$googleEnabled = !empty($_ENV['GOOGLE_CLIENT_ID'] ?? null);
$metamaskEnabled = !empty($_ENV['METAMASK_ENABLED'] ?? null);
$walletConnectEnabled = !empty($_ENV['WALLETCONNECT_PROJECT_ID'] ?? null);
$coinbaseEnabled = !empty($_ENV['COINBASE_WALLET_ENABLED'] ?? null);

// Determine if groups should render
$hasSocial = $ssoEnabled || $googleEnabled;
$hasWallets = $metamaskEnabled || $walletConnectEnabled || $coinbaseEnabled;

// Get SSO branding if enabled
$ssoBrand = $ssoEnabled ? MemberAuth::getSSOBranding() : null;

// Determine conditional features
$showDeviceVerification = isset($_GET['device_verify']);
?>

<!-- Color scheme meta tag for dark mode support -->
<meta name="color-scheme" content="light dark">

<div class="member-page">
    <div class="member-card">
        <nav class="member-nav-tabs" aria-label="Account navigation">
            <a href="/member/login" class="member-nav-tab active" aria-current="page">Sign In</a>
            <a href="/member/register" class="member-nav-tab">Create Account</a>
            <a href="/member/forgot-password" class="member-nav-tab">Reset Password</a>
        </nav>

        <div class="member-header">
            <h1>Sign In</h1>
            <p>Welcome back</p>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- SIGN UP CTA (above fold for non-logged-in visitors)              -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <div class="member-signup-cta" role="complementary" aria-label="Create account">
            <span class="member-signup-cta__text">New here?</span>
            <a href="/member/register<?= !empty($_GET['return']) ? '?return=' . urlencode($_GET['return']) : '' ?>"
               class="member-signup-cta__link" data-track="signup-cta">
                Create your free account
            </a>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- SESSION TIMEOUT WARNING                                           -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <div id="session-timeout-warning" class="member-alert member-alert--warning"
             style="display:none;margin-bottom:1rem;">
            <strong><?php echo htmlspecialchars(t('session_timeout_warning') ?? 'Session expiring soon'); ?></strong>
            Your session will expire in <span id="countdown">5:00</span>.
            <button type="button" class="member-link" id="extend-session" style="margin-left:0.5rem;text-decoration:underline;">
                <?php echo htmlspecialchars(t('extend_session') ?? 'Extend session'); ?>
            </button>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- ALERTS                                                            -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <?php if ($verified): ?>
            <div class="member-alert member-alert--success">
                Email verified successfully. You can now sign in.
            </div>
        <?php endif; ?>

        <?php if ($registered): ?>
            <div class="member-alert member-alert--info">
                Account created! Please check your email<?= $maskedEmail ? " at <strong>{$maskedEmail}</strong>" : '' ?> to verify your address before signing in.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="member-alert member-alert--error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($showDeviceVerification): ?>
            <div class="member-alert member-alert--info">
                <strong><?= htmlspecialchars(t('new_device_detected') ?? 'New device detected'); ?></strong>
                <?= htmlspecialchars(t('check_email_verify') ?? 'Check your email to verify this device.'); ?>
            </div>
        <?php endif; ?>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- ACCESSIBILITY LANDMARKS & LIVE REGION                            -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <div id="auth-status" role="status" aria-live="polite" aria-label="Authentication method status"
             style="position:absolute;left:-9999px;"></div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- GROUP 1: EMAIL + PASSWORD (PRIMARY)                              -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <form class="member-form" data-action="/api/member/login.php" data-method="POST"
              role="region" aria-label="Email login form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
            <input type="hidden" name="session_lifetime" value="<?= htmlspecialchars((string)($_ENV['SESSION_LIFETIME'] ?? '3600')) ?>" id="session-lifetime">
            <input type="hidden" name="return_url" id="login-return-url"
                   value="<?= htmlspecialchars($_GET['return'] ?? '') ?>">

            <div class="member-field">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
                    <label class="member-label" for="login-email" style="margin-bottom:0;">Email</label>
                    <span class="member-helper-icon" title="<?= htmlspecialchars(t('email_privacy_info') ?? 'We protect your privacy. Your email is never shared.'); ?>"
                          style="cursor:help;font-size:0.875rem;">ℹ️</span>
                </div>
                <div class="member-input-wrap">
                    <input class="member-input" type="email" id="login-email" name="email"
                           required autocomplete="email" placeholder="you@example.com"
                           aria-describedby="field-helper-email" data-track="email-input" autofocus>
                    <span class="member-input-indicator" id="email-indicator" aria-hidden="true"></span>
                </div>
                <div id="field-helper-email" class="member-form-helper" style="font-size:0.75rem;color:#666;margin-top:0.25rem;">
                    <?= htmlspecialchars(t('email_helper_text') ?? 'We\'ll never share your email address.'); ?>
                </div>
            </div>

            <div class="member-field">
                <div class="member-label-row">
                    <label class="member-label" for="login-password">Password</label>
                    <a href="/member/forgot-password" class="member-link member-forgot-link" data-track="forgot-password">Forgot?</a>
                </div>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="login-password" name="password"
                           required autocomplete="current-password" placeholder="Your password" minlength="8"
                           data-track="password-input">
                </div>
            </div>

            <div class="member-field" style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                <input type="checkbox" id="trust-device" name="trust_device" value="1"
                       style="width:auto;cursor:pointer;" data-track="trust-device-toggle">
                <label for="trust-device" style="margin:0;font-size:0.875rem;cursor:pointer;">
                    <?= htmlspecialchars(t('remember_device_30_days') ?? 'Remember this device for 30 days'); ?>
                </label>
            </div>

            <button type="submit" class="member-btn" data-track="email-submit">Sign In</button>

            <div class="member-trust-badge" aria-label="Security information">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span>256-bit encrypted &middot; Your data stays private</span>
            </div>
        </form>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- GROUP 2: SOCIAL CONNECTIONS (SECONDARY)                          -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <?php if ($hasSocial): ?>
        <div class="member-group" data-group="social">
            <div class="member-group-label">or continue with</div>

            <div class="member-social-btns">
                <?php if ($ssoEnabled && $ssoBrand): ?>
                    <button type="button" class="member-sso-btn" data-return="<?= $returnUrl ?>" data-track="sso-click"
                            aria-label="Sign in with <?= htmlspecialchars($ssoBrand['name']) ?>">
                        <img class="member-sso-icon" src="<?= htmlspecialchars($ssoBrand['logo']) ?>" alt="" loading="lazy">
                        <?= htmlspecialchars($ssoBrand['name']) ?>
                    </button>
                <?php endif; ?>

                <?php if ($googleEnabled): ?>
                    <a href="/api/member/google.php?return=<?= urlencode($returnUrl) ?>" class="member-social-btn member-google-btn" data-track="google-click"
                       aria-label="Sign in with Google"
                       onclick="this.style.opacity='0.6';this.style.pointerEvents='none';var s=this.querySelector('span');if(s)s.textContent='Connecting...';">
                        <svg width="18" height="18" viewBox="0 0 48 48" style="flex-shrink:0;" aria-hidden="true">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        </svg>
                        <span>Google</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- GROUP 3: WALLET CONNECTIONS (TERTIARY)                           -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <?php if ($hasWallets): ?>
        <div class="member-group" data-group="wallets">
            <div class="member-group-label">or connect wallet</div>

            <div class="member-wallet-btns">
                <?php if ($metamaskEnabled): ?>
                    <button type="button" class="member-wallet-btn" data-wallet="metamask" data-track="wallet-click" aria-label="Sign in with MetaMask">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;">
                            <path d="M21.53 17.25l-6.56-5.09-4.6 4.27.7 7.97 10.46-7.15zm-9.86-6.27L8.68 4.5 4.5 10.67l.14 6.64 7.33-6.33z"/>
                        </svg>
                        MetaMask
                    </button>
                <?php endif; ?>

                <?php if ($walletConnectEnabled): ?>
                    <button type="button" class="member-wallet-btn" data-wallet="walletconnect" data-track="wallet-click" aria-label="Sign in with WalletConnect">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;">
                            <path d="M5.5 8.5c3.87-3.87 10.13-3.87 14 0m-1.4 1.4c-3.09-3.09-8.11-3.09-11.2 0"/>
                        </svg>
                        WalletConnect
                    </button>
                <?php endif; ?>

                <?php if ($coinbaseEnabled): ?>
                    <button type="button" class="member-wallet-btn" data-wallet="coinbase" data-track="wallet-click" aria-label="Sign in with Coinbase Wallet">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;">
                            <circle cx="12" cy="12" r="8"/>
                        </svg>
                        Coinbase
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- FOOTER LINKS                                                     -->
        <!-- ═════════════════════════════════════════════════════════════════ -->

        <?php $hideRegLink = !empty($memberDashboardConfig['hide_register_link']); $hideActivityLink = !empty($memberDashboardConfig['hide_login_activity_link']); ?>
        <?php if (!$hideRegLink || !$hideActivityLink): ?>
        <div class="member-footer">
            <?php if (!$hideRegLink): ?>
            <a href="/member/register" class="member-link" data-track="create-account">Create an account</a>
            <?php endif; ?>
            <?php if (!$hideActivityLink): ?>
            <a href="/member/login-activity" class="member-link" data-track="login-activity" style="margin-left:auto;">
                <?= htmlspecialchars(t('view_login_activity') ?? 'View login activity'); ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Rate Limit Alert (Phase 5.3) -->
<div id="rate-limit-message" class="member-alert member-alert--rate-limit" role="alert" aria-live="polite" style="display:none;">
    <strong>Too many requests.</strong>
    <span id="rate-limit-text">Please try again later.</span>
    <div class="member-rate-limit-countdown"></div>
</div>
