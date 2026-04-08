<?php
/**
 * Settings Template — Member Kit
 *
 * Variables: $member, $csrfToken, $ssoEnabled
 */
$member = $member ?? MemberAuth::getCurrentMember();
$hasPassword = !empty($member['password_hash']);
$hasHwLink = !empty($member['hw_user_id']);

// Bilingual support
$_stLang = $_GET['lang'] ?? $_SESSION['member_lang'] ?? $_COOKIE['lang'] ?? 'en';
if (!in_array($_stLang, ['en', 'es'], true)) $_stLang = 'en';
?>

<div class="member-page">
    <div class="member-card member-card--wide">
        <div class="member-header">
            <h1><?= $_stLang === 'es' ? "Configuraci\u{00f3}n de Cuenta" : 'Account Settings' ?></h1>
        </div>

        <div class="member-tabs">
            <button class="member-tab active" data-tab="tab-password"><?= $_stLang === 'es' ? "Contrase\u{00f1}a" : 'Password' ?></button>
            <button class="member-tab" data-tab="tab-email"><?= $_stLang === 'es' ? 'Correo' : 'Email' ?></button>
            <button class="member-tab" data-tab="tab-preferences"><?= $_stLang === 'es' ? 'Preferencias' : 'Preferences' ?></button>
            <?php if (!empty($ssoEnabled)): ?>
                <button class="member-tab" data-tab="tab-sso">SSO</button>
            <?php endif; ?>
            <?php if (MemberGoogle::isEnabled()): ?>
                <button class="member-tab" data-tab="tab-google">Google</button>
            <?php endif; ?>
        </div>

        <!-- Password Tab -->
        <div class="member-tab-content active" id="tab-password">
            <?php if ($hasPassword): ?>
                <form class="member-form" data-action="/api/member/password.php" data-method="PUT">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

                    <div class="member-field">
                        <label class="member-label" for="settings-current-password"><?= $_stLang === 'es' ? "Contrase\u{00f1}a Actual" : 'Current Password' ?></label>
                        <div class="member-password-wrap">
                            <input class="member-input" type="password" id="settings-current-password"
                                   name="current_password" required autocomplete="current-password">
                        </div>
                    </div>

                    <div class="member-field">
                        <label class="member-label" for="settings-new-password"><?= $_stLang === 'es' ? "Nueva Contrase\u{00f1}a" : 'New Password' ?></label>
                        <div class="member-password-wrap">
                            <input class="member-input" type="password" id="settings-new-password"
                                   name="new_password" required autocomplete="new-password"
                                   minlength="8" placeholder="<?= $_stLang === 'es' ? 'Al menos 8 caracteres' : 'At least 8 characters' ?>">
                        </div>
                    </div>

                    <div class="member-field">
                        <label class="member-label" for="settings-confirm-password"><?= $_stLang === 'es' ? "Confirmar Nueva Contrase\u{00f1}a" : 'Confirm New Password' ?></label>
                        <div class="member-password-wrap">
                            <input class="member-input" type="password" id="settings-confirm-password"
                                   name="password_confirm" required autocomplete="new-password" minlength="8">
                        </div>
                    </div>

                    <button type="submit" class="member-btn"><?= $_stLang === 'es' ? "Actualizar Contrase\u{00f1}a" : 'Update Password' ?></button>
                </form>
            <?php else: ?>
                <div class="member-alert member-alert--info">
                    <?= $_stLang === 'es' ? "Su cuenta usa inicio de sesi\u{00f3}n SSO. Para establecer una contrase\u{00f1}a para inicio directo, use el flujo de restablecimiento." : 'Your account uses SSO login. To set a password for direct login, use the password reset flow.' ?>
                </div>
                <a href="/member/forgot-password" class="member-btn-secondary"><?= $_stLang === 'es' ? "Establecer Contrase\u{00f1}a" : 'Set a Password' ?></a>
            <?php endif; ?>
        </div>

        <!-- Email Tab -->
        <div class="member-tab-content" id="tab-email">
            <p class="member-text-muted" style="margin-bottom:16px;">
                <?= $_stLang === 'es' ? 'Correo actual:' : 'Current email:' ?> <strong><?= htmlspecialchars($member['email'] ?? '') ?></strong>
            </p>

            <form class="member-form" data-action="/api/member/profile.php" data-method="PUT">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

                <div class="member-field">
                    <label class="member-label" for="settings-new-email"><?= $_stLang === 'es' ? "Nueva Direcci\u{00f3}n de Correo" : 'New Email Address' ?></label>
                    <input class="member-input" type="email" id="settings-new-email" name="new_email"
                           required placeholder="newemail@example.com">
                </div>

                <button type="submit" class="member-btn"><?= $_stLang === 'es' ? 'Cambiar Correo' : 'Change Email' ?></button>
                <p class="member-text-muted" style="margin-top:8px;">
                    <?= $_stLang === 'es' ? "Se enviar\u{00e1} un enlace de verificaci\u{00f3}n al nuevo correo." : 'A verification link will be sent to the new email address.' ?>
                </p>
            </form>
        </div>

        <!-- Preferences Tab -->
        <div class="member-tab-content" id="tab-preferences">
            <form class="member-form" data-action="/api/member/preferences.php" data-method="PUT">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

                <div class="member-field">
                    <label class="member-label">
                        <input type="checkbox" name="marketing_emails" value="1"
                               class="member-checkbox" <?= !empty($member['marketing_emails']) ? 'checked' : '' ?>>
                        <?= $_stLang === 'es' ? 'Recibir correos de marketing y anuncios' : 'Receive marketing emails and announcements' ?>
                    </label>
                </div>

                <div class="member-field">
                    <label class="member-label" for="pref-digest"><?= $_stLang === 'es' ? 'Frecuencia del resumen por correo' : 'Email digest frequency' ?></label>
                    <select class="member-input" id="pref-digest" name="digest_frequency">
                        <option value="never" <?= ($member['digest_frequency'] ?? 'never') === 'never' ? 'selected' : '' ?>><?= $_stLang === 'es' ? 'Nunca' : 'Never' ?></option>
                        <option value="daily" <?= ($member['digest_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>><?= $_stLang === 'es' ? 'Diario' : 'Daily' ?></option>
                        <option value="weekly" <?= ($member['digest_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>><?= $_stLang === 'es' ? 'Semanal' : 'Weekly' ?></option>
                    </select>
                </div>

                <button type="submit" class="member-btn"><?= $_stLang === 'es' ? 'Guardar Preferencias' : 'Save Preferences' ?></button>
            </form>
        </div>

        <?php if (!empty($ssoEnabled)):
            $ssoBrand = MemberAuth::getSSOBranding();
        ?>
            <!-- SSO Tab -->
            <div class="member-tab-content" id="tab-sso">
                <?php if ($hasHwLink): ?>
                    <div class="member-alert member-alert--success">
                        <?= $_stLang === 'es' ? "Su cuenta est\u{00e1} vinculada v\u{00ed}a SSO" : 'Your account is linked via SSO' ?>
                    </div>
                    <?php if ($hasPassword): ?>
                        <form class="member-form" data-action="/api/member/sso-unlink.php" data-method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
                            <button type="submit" class="member-btn-danger"><?= $_stLang === 'es' ? 'Desvincular Cuenta SSO' : 'Unlink SSO Account' ?></button>
                            <p class="member-text-muted" style="margin-top:8px;">
                                <?= $_stLang === 'es' ? "A\u{00fa}n puede iniciar sesi\u{00f3}n con su correo y contrase\u{00f1}a." : 'You can still log in with your email and password.' ?>
                            </p>
                        </form>
                    <?php else: ?>
                        <p class="member-text-muted">
                            <?= $_stLang === 'es' ? "Establezca una contrase\u{00f1}a antes de desvincular su cuenta SSO." : 'Set a password before unlinking your SSO account.' ?>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="member-text-muted" style="margin-bottom:16px;">
                        <?= $_stLang === 'es' ? "Vincule su cuenta para inicio de sesi\u{00f3}n \u{00fa}nico." : 'Link your account for single sign-on.' ?>
                    </p>
                    <button type="button" class="member-sso-btn">
                        <img class="member-sso-icon" src="<?= htmlspecialchars($ssoBrand['logo']) ?>" alt="" loading="lazy">
                        <?= $_stLang === 'es' ? 'Vincular' : 'Link' ?> <?= htmlspecialchars($ssoBrand['name']) ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (MemberGoogle::isEnabled()):
            $googleLinked = MemberGoogle::isLinked((int)$member['id']);
            $googleInfo = $googleLinked ? MemberGoogle::getLinkedInfo((int)$member['id']) : null;
        ?>
            <div class="member-tab-content" id="tab-google">
                <?php if ($googleLinked): ?>
                    <div class="member-alert member-alert--success">
                        <?= $_stLang === 'es' ? 'Conectado a Google' : 'Connected to Google' ?><?= $googleInfo && $googleInfo['google_email'] ? ' (' . htmlspecialchars($googleInfo['google_email']) . ')' : '' ?>
                    </div>
                    <?php if ($hasPassword): ?>
                        <form class="member-form" data-action="/api/member/google-unlink.php" data-method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
                            <button type="submit" class="member-btn-danger"><?= $_stLang === 'es' ? 'Desconectar Google' : 'Disconnect Google' ?></button>
                            <p class="member-text-muted" style="margin-top:8px;">
                                <?= $_stLang === 'es' ? "A\u{00fa}n puede iniciar sesi\u{00f3}n con su correo y contrase\u{00f1}a." : 'You can still log in with your email and password.' ?>
                            </p>
                        </form>
                    <?php else: ?>
                        <p class="member-text-muted">
                            <?= $_stLang === 'es' ? "Establezca una contrase\u{00f1}a antes de desconectar su cuenta de Google." : 'Set a password before disconnecting your Google account.' ?>
                        </p>
                        <a href="/member/forgot-password" class="member-btn-secondary" style="margin-top:8px;"><?= $_stLang === 'es' ? "Establecer Contrase\u{00f1}a" : 'Set a Password' ?></a>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="member-text-muted" style="margin-bottom:16px;">
                        <?= $_stLang === 'es' ? "Conecte su cuenta de Google para iniciar sesi\u{00f3}n r\u{00e1}pidamente." : 'Connect your Google account for quick sign-in.' ?>
                    </p>
                    <a href="/api/member/google.php?mode=connect&return=/members?tab=settings" class="member-btn" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                        <svg width="18" height="18" viewBox="0 0 18 18" style="flex-shrink:0;">
                            <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/>
                            <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z"/>
                            <path fill="#FBBC05" d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.997 8.997 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z"/>
                            <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
                        </svg>
                        <?= $_stLang === 'es' ? 'Conectar Cuenta de Google' : 'Connect Google Account' ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="member-footer">
            <a href="/member/profile" class="member-link"><?= $_stLang === 'es' ? 'Volver al Perfil' : 'Back to Profile' ?></a>
        </div>
    </div>
</div>
