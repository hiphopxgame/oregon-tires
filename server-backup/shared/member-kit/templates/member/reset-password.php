<?php
/**
 * Reset Password Template — Member Kit
 * User arrives here from the reset email link.
 *
 * Variables: $csrfToken, $token (from URL)
 */
$token = $_GET['token'] ?? '';

if ($token === '') {
    header('Location: /member/forgot-password');
    exit;
}
?>

<div class="member-page">
    <div class="member-card">
        <div class="member-header">
            <h1>Set New Password</h1>
            <p>Choose a strong password</p>
        </div>

        <form class="member-form" data-action="/api/member/reset-password.php" data-method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="member-field">
                <label class="member-label" for="reset-password">New Password</label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reset-password" name="password"
                           required autocomplete="new-password" placeholder="At least 8 characters" minlength="8">
                </div>
            </div>

            <div class="member-field">
                <label class="member-label" for="reset-password-confirm">Confirm Password</label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reset-password-confirm" name="password_confirm"
                           required autocomplete="new-password" minlength="8">
                </div>
            </div>

            <button type="submit" class="member-btn">Reset Password</button>
        </form>

        <div class="member-footer">
            <a href="/member/login" class="member-link">Back to Sign In</a>
        </div>
    </div>
</div>
