<?php
/**
 * Unified Account Page — Member Kit
 *
 * Single vertically-stacked page with 5 collapsible sections:
 *   1. Profile — avatar, display name, username, bio, email
 *   2. Security — password change
 *   3. Connected Accounts — Google + SSO connect/disconnect
 *   4. Email & Preferences — email change + marketing/digest prefs
 *   5. Activity — last 10 activity items (collapsed by default)
 *
 * Variables: $member, $csrfToken, $ssoEnabled (from dashboard.php context)
 */

$member = $member ?? MemberAuth::getCurrentMember();
$avatarUrl = $member['avatar_url'] ?? null;
$displayName = $member['display_name'] ?? $member['username'] ?? 'Member';
$hasPassword = !empty($member['password_hash']);
$hasHwLink = !empty($member['hw_user_id']);
$_csrf = htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken());

// Bilingual
$_aLang = $_GET['lang'] ?? $_SESSION['member_lang'] ?? $_COOKIE['lang'] ?? 'en';
if (!in_array($_aLang, ['en', 'es'], true)) $_aLang = 'en';
$_es = ($_aLang === 'es');

// Activity data (graceful fallback if table schema is outdated)
try {
    $activities = MemberProfile::getActivity((int) $member['id']);
} catch (\Throwable $e) {
    error_log('MemberProfile::getActivity error: ' . $e->getMessage());
    $activities = [];
}
$recentActivities = array_slice($activities, 0, 10);

// formatRelativeTime helper
if (!function_exists('formatRelativeTime')) {
    function formatRelativeTime(string $datetime): string
    {
        if ($datetime === '') return '';
        $ts = strtotime($datetime);
        if ($ts === false) return $datetime;
        $diff = time() - $ts;
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j, Y', $ts);
    }
}

$actionLabels = [
    'login'                    => $_es ? 'Inici\u{00f3} sesi\u{00f3}n' : 'Signed in',
    'sso_login'                => $_es ? 'Inici\u{00f3} sesi\u{00f3}n via SSO' : 'Signed in via SSO',
    'logout'                   => $_es ? 'Cerr\u{00f3} sesi\u{00f3}n' : 'Signed out',
    'register'                 => $_es ? 'Cuenta creada' : 'Account created',
    'sso_register'             => $_es ? 'Cuenta creada via SSO' : 'Account created via SSO',
    'profile_updated'          => $_es ? 'Perfil actualizado' : 'Profile updated',
    'avatar_uploaded'          => $_es ? 'Avatar cambiado' : 'Avatar changed',
    'password_changed'         => $_es ? "Contrase\u{00f1}a cambiada" : 'Password changed',
    'password_reset_requested' => $_es ? "Restablecimiento de contrase\u{00f1}a solicitado" : 'Password reset requested',
    'password_reset_completed' => $_es ? "Contrase\u{00f1}a restablecida" : 'Password reset completed',
    'email_verified'           => $_es ? 'Correo verificado' : 'Email verified',
    'email_change_requested'   => $_es ? 'Cambio de correo solicitado' : 'Email change requested',
    'sso_unlinked'             => $_es ? 'Cuenta SSO desvinculada' : 'SSO account unlinked',
    'google_linked'            => $_es ? 'Google conectado' : 'Google connected',
    'google_unlinked'          => $_es ? 'Google desconectado' : 'Google disconnected',
];

// Chevron SVG used in section titles
$_chevron = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
?>

<div class="member-page">
    <div class="member-card member-card--wide" style="max-width: 640px;">

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- SECTION 1: Profile                                            -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="member-account-section" data-section="profile">
            <div class="member-account-section-title" role="button" tabindex="0" aria-expanded="true">
                <span><?= $_es ? 'Perfil' : 'Profile' ?></span>
                <?= $_chevron ?>
            </div>
            <div class="member-account-section-body">
                <div class="member-header" style="margin-bottom: 1rem;">
                    <div class="member-avatar member-avatar--lg">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar">
                        <?php else: ?>
                            <span><?= htmlspecialchars(mb_strtoupper(mb_substr($displayName, 0, 1))) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <form class="member-form" data-action="/api/member/profile.php" data-method="PUT">
                    <input type="hidden" name="csrf_token" value="<?= $_csrf ?>">

                    <div class="member-field">
                        <label class="member-label"><?= $_es ? 'Avatar' : 'Avatar' ?></label>
                        <div class="member-avatar-upload" data-member-id="<?= (int) $member['id'] ?>">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar preview">
                            <?php endif; ?>
                            <p><?= $_es ? 'Arrastra una imagen o haz clic para subir' : 'Drop an image here or click to upload' ?></p>
                            <small><?= $_es ? "JPG, PNG o WebP. M\u{00e1}x 2MB." : 'JPG, PNG, or WebP. Max 2MB.' ?></small>
                        </div>
                    </div>

                    <div class="member-form-row">
                        <div class="member-field">
                            <label class="member-label" for="acct-display-name"><?= $_es ? 'Nombre para Mostrar' : 'Display Name' ?></label>
                            <input class="member-input" type="text" id="acct-display-name" name="display_name"
                                   value="<?= htmlspecialchars($member['display_name'] ?? '') ?>" placeholder="<?= $_es ? 'Tu Nombre' : 'Your Name' ?>">
                        </div>
                        <div class="member-field">
                            <label class="member-label" for="acct-username"><?= $_es ? 'Nombre de Usuario' : 'Username' ?></label>
                            <input class="member-input" type="text" id="acct-username" name="username"
                                   value="<?= htmlspecialchars($member['username'] ?? '') ?>" placeholder="<?= $_es ? 'tu_usuario' : 'your_username' ?>"
                                   pattern="[a-zA-Z0-9_]{3,50}">
                        </div>
                    </div>

                    <div class="member-field">
                        <label class="member-label" for="acct-bio"><?= $_es ? "Biograf\u{00ed}a" : 'Bio' ?></label>
                        <textarea class="member-textarea" id="acct-bio" name="bio" rows="3"
                                  placeholder="<?= $_es ? "Cu\u{00e9}ntanos sobre ti" : 'Tell us about yourself' ?>"><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="member-field">
                        <label class="member-label"><?= $_es ? 'Correo' : 'Email' ?></label>
                        <input class="member-input" type="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>" disabled>
                    </div>

                    <button type="submit" class="member-btn"><?= $_es ? 'Guardar Perfil' : 'Save Profile' ?></button>
                </form>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- SECTION 2: Security                                           -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="member-account-section" data-section="security">
            <div class="member-account-section-title" role="button" tabindex="0" aria-expanded="true">
                <span><?= $_es ? 'Seguridad' : 'Security' ?></span>
                <?= $_chevron ?>
            </div>
            <div class="member-account-section-body">
                <h3 style="margin-top: 0; font-size: 1rem;"><?= $_es ? "Cambiar Contrase\u{00f1}a" : 'Change Password' ?></h3>

                <?php if ($hasPassword): ?>
                    <form class="member-form" data-action="/api/member/password.php" data-method="PUT">
                        <input type="hidden" name="csrf_token" value="<?= $_csrf ?>">

                        <div class="member-field">
                            <label class="member-label" for="acct-current-pw"><?= $_es ? "Contrase\u{00f1}a Actual" : 'Current Password' ?></label>
                            <div class="member-password-wrap">
                                <input class="member-input" type="password" id="acct-current-pw" name="current_password" required autocomplete="current-password">
                            </div>
                        </div>

                        <div class="member-field">
                            <label class="member-label" for="acct-new-pw"><?= $_es ? "Nueva Contrase\u{00f1}a" : 'New Password' ?></label>
                            <div class="member-password-wrap">
                                <input class="member-input" type="password" id="acct-new-pw" name="new_password" required autocomplete="new-password"
                                       minlength="8" placeholder="<?= $_es ? 'Al menos 8 caracteres' : 'At least 8 characters' ?>">
                            </div>
                        </div>

                        <div class="member-field">
                            <label class="member-label" for="acct-confirm-pw"><?= $_es ? "Confirmar Nueva Contrase\u{00f1}a" : 'Confirm New Password' ?></label>
                            <div class="member-password-wrap">
                                <input class="member-input" type="password" id="acct-confirm-pw" name="password_confirm" required autocomplete="new-password" minlength="8">
                            </div>
                        </div>

                        <button type="submit" class="member-btn"><?= $_es ? "Actualizar Contrase\u{00f1}a" : 'Update Password' ?></button>
                    </form>
                <?php else: ?>
                    <div class="member-alert member-alert--info">
                        <?= $_es ? "Su cuenta usa inicio de sesi\u{00f3}n social. Para establecer una contrase\u{00f1}a, use el flujo de restablecimiento." : 'Your account uses social login. To set a password for direct login, use the password reset flow.' ?>
                    </div>
                    <a href="/members?view=forgot-password" class="member-btn-secondary"><?= $_es ? "Establecer Contrase\u{00f1}a" : 'Set a Password' ?></a>
                <?php endif; ?>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- SECTION 3: Connected Accounts                                 -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <?php
        $showConnected = (!empty($ssoEnabled) || MemberGoogle::isEnabled());
        if ($showConnected):
        ?>
        <div class="member-account-section" data-section="connected">
            <div class="member-account-section-title" role="button" tabindex="0" aria-expanded="true">
                <span><?= $_es ? 'Cuentas Conectadas' : 'Connected Accounts' ?></span>
                <?= $_chevron ?>
            </div>
            <div class="member-account-section-body">

                <?php if (MemberGoogle::isEnabled()):
                    $googleLinked = MemberGoogle::isLinked((int)$member['id']);
                    $googleInfo = $googleLinked ? MemberGoogle::getLinkedInfo((int)$member['id']) : null;
                ?>
                    <div style="margin-bottom: 1.25rem;">
                        <h3 style="margin-top: 0; font-size: 1rem;">Google</h3>
                        <?php if ($googleLinked): ?>
                            <div class="member-alert member-alert--success" style="margin-bottom: 0.75rem;">
                                <?= $_es ? 'Conectado a Google' : 'Connected to Google' ?><?= $googleInfo && $googleInfo['google_email'] ? ' (' . htmlspecialchars($googleInfo['google_email']) . ')' : '' ?>
                            </div>
                            <?php if ($hasPassword): ?>
                                <form class="member-form" data-action="/api/member/google-unlink.php" data-method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_csrf ?>">
                                    <button type="submit" class="member-btn-danger"><?= $_es ? 'Desconectar Google' : 'Disconnect Google' ?></button>
                                    <p class="member-text-muted" style="margin-top:8px;">
                                        <?= $_es ? "A\u{00fa}n puede iniciar sesi\u{00f3}n con su correo y contrase\u{00f1}a." : 'You can still log in with your email and password.' ?>
                                    </p>
                                </form>
                            <?php else: ?>
                                <p class="member-text-muted">
                                    <?= $_es ? "Establezca una contrase\u{00f1}a antes de desconectar su cuenta de Google." : 'Set a password before disconnecting your Google account.' ?>
                                </p>
                                <a href="/members?view=forgot-password" class="member-btn-secondary" style="margin-top:8px;"><?= $_es ? "Establecer Contrase\u{00f1}a" : 'Set a Password' ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="member-text-muted" style="margin-bottom: 0.75rem;">
                                <?= $_es ? "Conecte su cuenta de Google para iniciar sesi\u{00f3}n r\u{00e1}pidamente." : 'Connect your Google account for quick sign-in.' ?>
                            </p>
                            <a href="/api/member/google.php?mode=connect&return=/members?tab=account" class="member-btn" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                                <svg width="18" height="18" viewBox="0 0 18 18" style="flex-shrink:0;">
                                    <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/>
                                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z"/>
                                    <path fill="#FBBC05" d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.997 8.997 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z"/>
                                    <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
                                </svg>
                                <?= $_es ? 'Conectar Cuenta de Google' : 'Connect Google Account' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($ssoEnabled)):
                    $ssoBrand = MemberAuth::getSSOBranding();
                ?>
                    <div>
                        <h3 style="margin-top: 0; font-size: 1rem;">SSO</h3>
                        <?php if ($hasHwLink): ?>
                            <div class="member-alert member-alert--success" style="margin-bottom: 0.75rem;">
                                <?= $_es ? "Su cuenta est\u{00e1} vinculada v\u{00ed}a SSO" : 'Your account is linked via SSO' ?>
                            </div>
                            <?php if ($hasPassword): ?>
                                <form class="member-form" data-action="/api/member/sso-unlink.php" data-method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_csrf ?>">
                                    <button type="submit" class="member-btn-danger"><?= $_es ? 'Desvincular Cuenta SSO' : 'Unlink SSO Account' ?></button>
                                    <p class="member-text-muted" style="margin-top:8px;">
                                        <?= $_es ? "A\u{00fa}n puede iniciar sesi\u{00f3}n con su correo y contrase\u{00f1}a." : 'You can still log in with your email and password.' ?>
                                    </p>
                                </form>
                            <?php else: ?>
                                <p class="member-text-muted">
                                    <?= $_es ? "Establezca una contrase\u{00f1}a antes de desvincular su cuenta SSO." : 'Set a password before unlinking your SSO account.' ?>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="member-text-muted" style="margin-bottom: 0.75rem;">
                                <?= $_es ? "Vincule su cuenta para inicio de sesi\u{00f3}n \u{00fa}nico." : 'Link your account for single sign-on.' ?>
                            </p>
                            <button type="button" class="member-sso-btn">
                                <img class="member-sso-icon" src="<?= htmlspecialchars($ssoBrand['logo']) ?>" alt="" loading="lazy">
                                <?= $_es ? 'Vincular' : 'Link' ?> <?= htmlspecialchars($ssoBrand['name']) ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- SECTION 4: Email & Preferences                                -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="member-account-section" data-section="email-prefs">
            <div class="member-account-section-title" role="button" tabindex="0" aria-expanded="true">
                <span><?= $_es ? 'Correo y Preferencias' : 'Email & Preferences' ?></span>
                <?= $_chevron ?>
            </div>
            <div class="member-account-section-body">
                <h3 style="margin-top: 0; font-size: 1rem;"><?= $_es ? 'Cambiar Correo' : 'Change Email' ?></h3>
                <p class="member-text-muted" style="margin-bottom: 0.75rem;">
                    <?= $_es ? 'Correo actual:' : 'Current email:' ?> <strong><?= htmlspecialchars($member['email'] ?? '') ?></strong>
                </p>

                <form class="member-form" data-action="/api/member/profile.php" data-method="PUT" style="margin-bottom: 1.5rem;">
                    <input type="hidden" name="csrf_token" value="<?= $_csrf ?>">

                    <div class="member-field">
                        <label class="member-label" for="acct-new-email"><?= $_es ? "Nueva Direcci\u{00f3}n de Correo" : 'New Email Address' ?></label>
                        <input class="member-input" type="email" id="acct-new-email" name="new_email"
                               required placeholder="newemail@example.com">
                    </div>

                    <button type="submit" class="member-btn"><?= $_es ? 'Cambiar Correo' : 'Change Email' ?></button>
                    <p class="member-text-muted" style="margin-top:8px;">
                        <?= $_es ? "Se enviar\u{00e1} un enlace de verificaci\u{00f3}n al nuevo correo." : 'A verification link will be sent to the new email address.' ?>
                    </p>
                </form>

                <div style="padding-top: 1rem; border-top: 1px solid var(--member-border);">
                    <h3 style="margin-top: 0; font-size: 1rem;"><?= $_es ? 'Preferencias' : 'Preferences' ?></h3>
                    <form class="member-form" data-action="/api/member/preferences.php" data-method="PUT">
                        <input type="hidden" name="csrf_token" value="<?= $_csrf ?>">

                        <div class="member-field">
                            <label class="member-label">
                                <input type="checkbox" name="marketing_emails" value="1"
                                       class="member-checkbox" <?= !empty($member['marketing_emails']) ? 'checked' : '' ?>>
                                <?= $_es ? 'Recibir correos de marketing y anuncios' : 'Receive marketing emails and announcements' ?>
                            </label>
                        </div>

                        <div class="member-field">
                            <label class="member-label" for="acct-digest"><?= $_es ? 'Frecuencia del resumen por correo' : 'Email digest frequency' ?></label>
                            <select class="member-input" id="acct-digest" name="digest_frequency">
                                <option value="never" <?= ($member['digest_frequency'] ?? 'never') === 'never' ? 'selected' : '' ?>><?= $_es ? 'Nunca' : 'Never' ?></option>
                                <option value="daily" <?= ($member['digest_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>><?= $_es ? 'Diario' : 'Daily' ?></option>
                                <option value="weekly" <?= ($member['digest_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>><?= $_es ? 'Semanal' : 'Weekly' ?></option>
                            </select>
                        </div>

                        <button type="submit" class="member-btn"><?= $_es ? 'Guardar Preferencias' : 'Save Preferences' ?></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- SECTION 5: Activity (collapsed by default)                    -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="member-account-section collapsed" data-section="activity">
            <div class="member-account-section-title" role="button" tabindex="0" aria-expanded="false">
                <span><?= $_es ? 'Actividad Reciente' : 'Recent Activity' ?></span>
                <?= $_chevron ?>
            </div>
            <div class="member-account-section-body">
                <?php if (empty($recentActivities)): ?>
                    <p class="member-text-muted" style="text-align:center;padding:16px 0;"><?= $_es ? 'Sin actividad registrada.' : 'No activity recorded yet.' ?></p>
                <?php else: ?>
                    <div class="member-activity-list">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="member-activity-item">
                                <div class="member-activity-icon">
                                    <?php
                                    $action = $activity['action'] ?? '';
                                    $iconClass = match (true) {
                                        str_contains($action, 'login') => 'login',
                                        str_contains($action, 'logout') => 'logout',
                                        str_contains($action, 'password') => 'security',
                                        str_contains($action, 'email') => 'email',
                                        str_contains($action, 'profile') || str_contains($action, 'avatar') => 'profile',
                                        str_contains($action, 'register') => 'register',
                                        str_contains($action, 'google') => 'default',
                                        default => 'default',
                                    };
                                    ?>
                                    <span class="member-activity-icon--<?= $iconClass ?>"></span>
                                </div>
                                <div class="member-activity-content">
                                    <span class="member-activity-action">
                                        <?= htmlspecialchars($actionLabels[$action] ?? $action) ?>
                                    </span>
                                    <?php if (!empty($activity['details']) && is_array($activity['details'])): ?>
                                        <span class="member-activity-detail">
                                            <?php
                                            $detail = $activity['details'];
                                            if (!empty($detail['ip'])) {
                                                echo 'from ' . htmlspecialchars($detail['ip']);
                                            } elseif (!empty($detail['fields'])) {
                                                echo htmlspecialchars(implode(', ', $detail['fields']));
                                            } elseif (!empty($detail['new_email'])) {
                                                echo 'to ' . htmlspecialchars($detail['new_email']);
                                            }
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <time class="member-activity-time" datetime="<?= htmlspecialchars($activity['created_at'] ?? '') ?>">
                                    <?= htmlspecialchars(formatRelativeTime($activity['created_at'] ?? '')) ?>
                                </time>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($activities) > 10): ?>
                        <div style="text-align: center; margin-top: 1rem;">
                            <button type="button" class="member-btn-secondary" id="acct-show-all-activity"
                                    data-member-id="<?= (int) $member['id'] ?>">
                                <?= $_es ? 'Mostrar todo' : 'Show all' ?> (<?= count($activities) ?>)
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
