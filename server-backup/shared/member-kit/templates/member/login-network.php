<?php
/**
 * Network Login Template -- Unified login form for all network sites.
 *
 * Clean, minimal login with HipHop.World branding.
 * Supports optional Google OAuth when GOOGLE_CLIENT_ID is configured.
 *
 * Expected context from dashboard.php:
 *   $csrfToken (string) -- CSRF token
 *   $returnUrl (string) -- redirect after login
 *   $_siteTitle (string) -- page title
 */

declare(strict_types=1);

$siteName = MemberAuth::getConfig('site_name') ?: 'Site';
$loginUrl = MemberAuth::getConfig('login_url') ?: '/members';
$siteUrl = MemberAuth::getConfig('site_url') ?: '';
$error = htmlspecialchars($_GET['error'] ?? '', ENT_QUOTES, 'UTF-8');
$registered = !empty($_GET['registered']);
$verified = !empty($_GET['verified']);
$googleEnabled = !empty($_ENV['GOOGLE_CLIENT_ID'] ?? null);
$_siteLogo = $memberDashboardConfig['logo'] ?? 'https://hiphop.world/assets/logos/HipHop.World.svg';
$_siteLogoAlt = $siteName;
?>
<div class="member-page">
    <div class="member-card" style="max-width: 400px; margin: 2rem auto;">
        <!-- Logo -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <img src="<?= htmlspecialchars($_siteLogo) ?>"
                 alt="<?= htmlspecialchars($_siteLogoAlt) ?>"
                 style="width: 48px; height: 48px; margin-bottom: 1rem; border-radius: 8px; object-fit: contain;">
            <h1 style="font-size: 1.25rem; font-weight: 600; color: var(--member-text); margin: 0;">
                Sign In
            </h1>
            <p style="font-size: 0.875rem; color: var(--member-text-muted); margin-top: 0.25rem;">
                Access <?= htmlspecialchars($siteName) ?>
            </p>
        </div>

        <?php if ($error): ?>
        <div class="member-alert member-alert--error" role="alert">
            <?= $error ?>
        </div>
        <?php endif; ?>

        <?php if ($verified): ?>
        <div class="member-alert member-alert--success" role="status">
            Email verified successfully. You can now sign in.
        </div>
        <?php endif; ?>

        <?php if ($registered): ?>
        <div class="member-alert member-alert--info" role="status">
            Account created. Please check your email for a verification link.
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="network-login-form" class="member-form" method="POST" action="/api/member/login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl ?? '/') ?>">

            <div class="member-field">
                <label class="member-label" for="login-email">Email</label>
                <input class="member-input"
                       type="email"
                       id="login-email"
                       name="email"
                       required
                       autocomplete="email"
                       placeholder="you@example.com"
                       aria-describedby="email-help">
                <small id="email-help" style="color: var(--member-text-muted); font-size: 0.75rem;">
                    Your <?= htmlspecialchars($siteName) ?> email
                </small>
            </div>

            <div class="member-field">
                <label class="member-label" for="login-password">Password</label>
                <input class="member-input"
                       type="password"
                       id="login-password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Enter your password"
                       minlength="8">
            </div>

            <div id="login-error" class="member-alert member-alert--error" role="alert" style="display: none;"></div>

            <button type="submit" class="member-button" id="login-submit" style="width: 100%;">
                Sign In
            </button>
        </form>

        <?php if ($googleEnabled): ?>
        <!-- Google OAuth -->
        <div style="margin-top: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; color: var(--member-text-muted); font-size: 0.8rem;">
                <div style="flex: 1; height: 1px; background: var(--member-border);"></div>
                or continue with
                <div style="flex: 1; height: 1px; background: var(--member-border);"></div>
            </div>
            <a href="/api/auth/google.php?return=<?= htmlspecialchars(urlencode($returnUrl ?? '/members')) ?>"
               class="member-button" id="google-login-btn"
               style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; text-decoration: none; background: var(--member-surface, #f8f9fa); color: var(--member-text); border: 1px solid var(--member-border);"
               onclick="this.style.opacity='0.6';this.style.pointerEvents='none';var s=this.querySelector('span');if(s)s.textContent='Connecting...';">
                <svg width="18" height="18" viewBox="0 0 48 48" style="flex-shrink:0;">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                <span>Google</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Footer Links -->
        <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--member-border);">
            <a href="<?= htmlspecialchars($loginUrl) ?>?view=forgot"
               style="color: var(--member-accent); text-decoration: none; font-size: 0.875rem;">
                Forgot password?
            </a>
            <span style="color: var(--member-text-muted); margin: 0 0.5rem;">|</span>
            <a href="<?= htmlspecialchars($loginUrl) ?>?view=register"
               style="color: var(--member-accent); text-decoration: none; font-size: 0.875rem;">
                Create account
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    var form = document.getElementById('network-login-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('login-submit');
        var errDiv = document.getElementById('login-error');
        var email = form.querySelector('[name="email"]').value;
        var password = form.querySelector('[name="password"]').value;
        var csrf = form.querySelector('[name="csrf_token"]').value;
        var returnUrl = form.querySelector('[name="return"]').value;

        btn.disabled = true;
        btn.textContent = 'Signing in...';
        errDiv.style.display = 'none';

        fetch('/api/member/login.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: email,
                password: password,
                csrf_token: csrf,
                return: returnUrl
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = data.return || returnUrl || '/members';
            } else {
                errDiv.textContent = data.error || 'Invalid email or password.';
                errDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Sign In';
            }
        })
        .catch(function() {
            errDiv.textContent = 'Connection error. Please try again.';
            errDiv.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Sign In';
        });
    });
})();
</script>
