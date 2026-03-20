<?php
/**
 * Oregon Tires — Bilingual Reset Password Template (EN/ES)
 *
 * Local override of member-kit's reset-password.php with translation support.
 * Variables: $csrfToken, $token (from URL)
 */
$token = $_GET['token'] ?? '';

if ($token === '') {
    header('Location: /members?view=forgot-password');
    exit;
}
?>

<div class="member-page">
    <div class="member-card">
        <div class="member-header">
            <h1><?= htmlspecialchars(t('set_new_password') ?? 'Set New Password') ?></h1>
            <p><?= htmlspecialchars(t('choose_strong_password') ?? 'Choose a strong password') ?></p>
        </div>

        <form class="member-form" data-action="/api/member/reset-password.php" data-method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="member-field">
                <label class="member-label" for="reset-password"><?= htmlspecialchars(t('new_password') ?? 'New Password') ?></label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reset-password" name="password"
                           required autocomplete="new-password" placeholder="<?= htmlspecialchars(t('password_placeholder') ?? 'At least 8 characters') ?>" minlength="8">
                </div>
            </div>

            <div class="member-field">
                <label class="member-label" for="reset-password-confirm"><?= htmlspecialchars(t('confirm_password') ?? 'Confirm Password') ?></label>
                <div class="member-password-wrap">
                    <input class="member-input" type="password" id="reset-password-confirm" name="password_confirm"
                           required autocomplete="new-password" minlength="8">
                </div>
            </div>

            <button type="submit" class="member-btn"><?= htmlspecialchars(t('reset_password_btn') ?? 'Reset Password') ?></button>
        </form>

        <?php $langQ = (getMemberLang() !== 'en') ? '?lang=' . getMemberLang() : ''; ?>
        <div class="member-footer">
            <a href="/member/login<?= $langQ ?>" class="member-link"><?= htmlspecialchars(t('back_to_sign_in') ?? 'Back to Sign In') ?></a>
        </div>
    </div>
</div>
