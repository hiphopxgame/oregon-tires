<?php
/**
 * Oregon Tires — Bilingual Forgot Password Template (EN/ES)
 *
 * Local override of member-kit's forgot-password.php with translation support.
 * Variables: $csrfToken
 */
?>

<?php $langQ = (getMemberLang() !== 'en') ? '?lang=' . getMemberLang() : ''; ?>
<div class="member-page">
    <div class="member-card">
        <nav class="member-nav-tabs" aria-label="Account navigation">
            <a href="/member/login<?= $langQ ?>" class="member-nav-tab"><?= htmlspecialchars(t('sign_in') ?? 'Sign In') ?></a>
            <a href="/member/register<?= $langQ ?>" class="member-nav-tab"><?= htmlspecialchars(t('create_account') ?? 'Create Account') ?></a>
            <a href="/member/forgot-password<?= $langQ ?>" class="member-nav-tab active" aria-current="page"><?= htmlspecialchars(t('reset_password_tab') ?? 'Reset Password') ?></a>
        </nav>

        <div class="member-header">
            <h1><?= htmlspecialchars(t('reset_password_title') ?? 'Reset Password') ?></h1>
            <p><?= htmlspecialchars(t('enter_email_reset') ?? 'Enter your email to receive a reset link') ?></p>
        </div>

        <form class="member-form" data-action="/api/member/password-reset.php" data-method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

            <div class="member-field">
                <label class="member-label" for="forgot-email"><?= htmlspecialchars(t('email_address') ?? 'Email Address') ?></label>
                <input class="member-input" type="email" id="forgot-email" name="email"
                       required autocomplete="email" placeholder="you@example.com">
            </div>

            <button type="submit" class="member-btn"><?= htmlspecialchars(t('send_reset_link') ?? 'Send Reset Link') ?></button>
        </form>

        <div class="member-footer">
            <a href="/member/login<?= $langQ ?>" class="member-link"><?= htmlspecialchars(t('back_to_sign_in') ?? 'Back to Sign In') ?></a>
        </div>
    </div>
</div>
