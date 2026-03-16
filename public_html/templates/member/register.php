<?php
/**
 * Oregon Tires — Bilingual Register Template (EN/ES)
 *
 * Local override of member-kit's register.php with translation support.
 * Variables: $csrfToken, $ssoEnabled, $siteName
 */
?>

<?php $langQ = (getMemberLang() !== 'en') ? '?lang=' . getMemberLang() : ''; ?>
<div class="member-page">
    <div class="member-card">
        <nav class="member-nav-tabs" aria-label="Account navigation">
            <a href="/member/login<?= $langQ ?>" class="member-nav-tab"><?= htmlspecialchars(t('sign_in') ?? 'Sign In') ?></a>
            <a href="/member/register<?= $langQ ?>" class="member-nav-tab active" aria-current="page"><?= htmlspecialchars(t('create_account') ?? 'Create Account') ?></a>
            <a href="/member/forgot-password<?= $langQ ?>" class="member-nav-tab"><?= htmlspecialchars(t('reset_password_tab') ?? 'Reset Password') ?></a>
        </nav>

        <div class="member-header">
            <h1><?= htmlspecialchars(t('create_account') ?? 'Create Account') ?></h1>
            <p><?= htmlspecialchars(t('join_site') ?? 'Join') ?> <?= htmlspecialchars($siteName ?? 'us') ?></p>
        </div>

        <?php if (!empty($ssoEnabled)):
            $ssoBrand = MemberAuth::getSSOBranding();
        ?>
            <button type="button" class="member-sso-btn" aria-label="Sign up with <?= htmlspecialchars($ssoBrand['name']) ?>">
                <img class="member-sso-icon" src="<?= htmlspecialchars($ssoBrand['logo']) ?>" alt="" loading="lazy">
                <?= htmlspecialchars(t('sign_up_with') ?? 'Sign up with') ?> <?= htmlspecialchars($ssoBrand['name']) ?>
            </button>
            <div class="member-divider"><span><?= htmlspecialchars(t('or_divider') ?? 'or') ?></span></div>
        <?php endif; ?>

        <form class="member-form" data-action="/api/member/register.php" data-method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

            <div class="member-field">
                <label class="member-label member-label-required" for="reg-email"><?= htmlspecialchars(t('email_label') ?? 'Email') ?></label>
                <input class="member-input" type="email" id="reg-email" name="email"
                       required autocomplete="email" placeholder="you@example.com">
            </div>

            <div class="member-form-row">
                <div class="member-field">
                    <label class="member-label" for="reg-username"><?= htmlspecialchars(t('username_label') ?? 'Username') ?></label>
                    <input class="member-input" type="text" id="reg-username" name="username"
                           autocomplete="username" placeholder="your_username" pattern="[a-zA-Z0-9_]{3,50}">
                </div>
                <div class="member-field">
                    <label class="member-label" for="reg-display-name"><?= htmlspecialchars(t('display_name_label') ?? 'Display Name') ?></label>
                    <input class="member-input" type="text" id="reg-display-name" name="display_name"
                           autocomplete="name" placeholder="Your Name">
                </div>
            </div>

            <div class="member-field">
                <label class="member-label member-label-required" for="reg-password"><?= htmlspecialchars(t('password_label') ?? 'Password') ?></label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reg-password" name="password"
                           required autocomplete="new-password" placeholder="<?= htmlspecialchars(t('password_placeholder') ?? 'At least 8 characters') ?>" minlength="8">
                </div>
            </div>

            <div class="member-field">
                <label class="member-label member-label-required" for="reg-password-confirm"><?= htmlspecialchars(t('confirm_password') ?? 'Confirm Password') ?></label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reg-password-confirm" name="password_confirm"
                           required autocomplete="new-password" placeholder="<?= htmlspecialchars(t('repeat_password') ?? 'Repeat your password') ?>" minlength="8">
                </div>
            </div>

            <button type="submit" class="member-btn"><?= htmlspecialchars(t('create_account_btn') ?? 'Create Account') ?></button>
        </form>

        <div class="member-footer">
            <a href="/member/login<?= $langQ ?>" class="member-link"><?= htmlspecialchars(t('already_have_account') ?? 'Already have an account? Sign in') ?></a>
        </div>
    </div>
</div>
