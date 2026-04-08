<?php
/**
 * Forgot Password Template — Member Kit
 *
 * Variables: $csrfToken
 */
?>

<div class="member-page">
    <div class="member-card">
        <nav class="member-nav-tabs" aria-label="Account navigation">
            <a href="/member/login" class="member-nav-tab">Sign In</a>
            <a href="/member/register" class="member-nav-tab">Create Account</a>
            <a href="/member/forgot-password" class="member-nav-tab active" aria-current="page">Reset Password</a>
        </nav>

        <div class="member-header">
            <h1>Reset Password</h1>
            <p>Enter your email to receive a reset link</p>
        </div>

        <form class="member-form" data-action="/api/member/password-reset.php" data-method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

            <div class="member-field">
                <label class="member-label" for="forgot-email">Email Address</label>
                <input class="member-input" type="email" id="forgot-email" name="email"
                       required autocomplete="email" placeholder="you@example.com">
            </div>

            <button type="submit" class="member-btn">Send Reset Link</button>
        </form>

        <div class="member-footer">
            <a href="/member/login" class="member-link">Back to Sign In</a>
        </div>
    </div>
</div>
