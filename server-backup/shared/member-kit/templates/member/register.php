<?php
/**
 * Register Template — Member Kit
 *
 * Variables: $csrfToken, $ssoEnabled, $siteName
 */
?>

<div class="member-page">
    <div class="member-card">
        <nav class="member-nav-tabs" aria-label="Account navigation">
            <a href="/member/login" class="member-nav-tab">Sign In</a>
            <a href="/member/register" class="member-nav-tab active" aria-current="page">Create Account</a>
            <a href="/member/forgot-password" class="member-nav-tab">Reset Password</a>
        </nav>

        <div class="member-header">
            <h1>Create Account</h1>
            <p>Join <?= htmlspecialchars($siteName ?? 'us') ?></p>
        </div>

        <?php if (!empty($ssoEnabled)):
            $ssoBrand = MemberAuth::getSSOBranding();
        ?>
            <button type="button" class="member-sso-btn" aria-label="Sign up with <?= htmlspecialchars($ssoBrand['name']) ?>">
                <img class="member-sso-icon" src="<?= htmlspecialchars($ssoBrand['logo']) ?>" alt="" loading="lazy">
                Sign up with <?= htmlspecialchars($ssoBrand['name']) ?>
            </button>
            <div class="member-divider"><span>or</span></div>
        <?php endif; ?>

        <form class="member-form" data-action="/api/member/register.php" data-method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

            <div class="member-field">
                <label class="member-label member-label-required" for="reg-email">Email</label>
                <input class="member-input" type="email" id="reg-email" name="email"
                       required autocomplete="email" placeholder="you@example.com">
            </div>

            <div class="member-form-row">
                <div class="member-field">
                    <label class="member-label" for="reg-username">Username</label>
                    <input class="member-input" type="text" id="reg-username" name="username"
                           autocomplete="username" placeholder="your_username" pattern="[a-zA-Z0-9_]{3,50}">
                </div>
                <div class="member-field">
                    <label class="member-label" for="reg-display-name">Display Name</label>
                    <input class="member-input" type="text" id="reg-display-name" name="display_name"
                           autocomplete="name" placeholder="Your Name">
                </div>
            </div>

            <div class="member-field">
                <label class="member-label member-label-required" for="reg-password">Password</label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reg-password" name="password"
                           required autocomplete="new-password" placeholder="At least 8 characters" minlength="8">
                </div>
            </div>

            <div class="member-field">
                <label class="member-label member-label-required" for="reg-password-confirm">Confirm Password</label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reg-password-confirm" name="password_confirm"
                           required autocomplete="new-password" placeholder="Repeat your password" minlength="8">
                </div>
            </div>

            <button type="submit" class="member-btn">Create Account</button>
        </form>

        <div class="member-footer">
            <a href="/member/login" class="member-link">Already have an account? Sign in</a>
        </div>
    </div>
</div>
