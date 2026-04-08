<?php
/**
 * Profile Template — Member Kit
 *
 * Variables: $member (from MemberAuth::getCurrentMember()), $csrfToken
 */
$member = $member ?? MemberAuth::getCurrentMember();
$avatarUrl = $member['avatar_url'] ?? null;
$displayName = $member['display_name'] ?? $member['username'] ?? 'Member';

// Bilingual support
$_prLang = $_GET['lang'] ?? $_SESSION['member_lang'] ?? $_COOKIE['lang'] ?? 'en';
if (!in_array($_prLang, ['en', 'es'], true)) $_prLang = 'en';
?>

<div class="member-page">
    <div class="member-card member-card--wide">
        <div class="member-header">
            <div class="member-avatar member-avatar--lg">
                <?php if ($avatarUrl): ?>
                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar">
                <?php else: ?>
                    <span><?= htmlspecialchars(mb_strtoupper(mb_substr($displayName, 0, 1))) ?></span>
                <?php endif; ?>
            </div>
            <h1><?= htmlspecialchars($displayName) ?></h1>
            <?php if (!empty($member['username'])): ?>
                <p>@<?= htmlspecialchars($member['username']) ?></p>
            <?php endif; ?>
        </div>

        <form class="member-form" data-action="/api/member/profile.php" data-method="PUT">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">

            <div class="member-field">
                <label class="member-label"><?= $_prLang === 'es' ? 'Avatar' : 'Avatar' ?></label>
                <div class="member-avatar-upload" data-member-id="<?= (int) $member['id'] ?>">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar preview">
                    <?php endif; ?>
                    <p><?= $_prLang === 'es' ? 'Arrastra una imagen o haz clic para subir' : 'Drop an image here or click to upload' ?></p>
                    <small><?= $_prLang === 'es' ? "JPG, PNG o WebP. M\u{00e1}x 2MB." : 'JPG, PNG, or WebP. Max 2MB.' ?></small>
                </div>
            </div>

            <div class="member-form-row">
                <div class="member-field">
                    <label class="member-label" for="profile-display-name"><?= $_prLang === 'es' ? 'Nombre para Mostrar' : 'Display Name' ?></label>
                    <input class="member-input" type="text" id="profile-display-name" name="display_name"
                           value="<?= htmlspecialchars($member['display_name'] ?? '') ?>" placeholder="<?= $_prLang === 'es' ? 'Tu Nombre' : 'Your Name' ?>">
                </div>
                <div class="member-field">
                    <label class="member-label" for="profile-username"><?= $_prLang === 'es' ? 'Nombre de Usuario' : 'Username' ?></label>
                    <input class="member-input" type="text" id="profile-username" name="username"
                           value="<?= htmlspecialchars($member['username'] ?? '') ?>" placeholder="<?= $_prLang === 'es' ? 'tu_usuario' : 'your_username' ?>"
                           pattern="[a-zA-Z0-9_]{3,50}">
                </div>
            </div>

            <div class="member-field">
                <label class="member-label" for="profile-bio"><?= $_prLang === 'es' ? "Biograf\u{00ed}a" : 'Bio' ?></label>
                <textarea class="member-textarea" id="profile-bio" name="bio" rows="3"
                          placeholder="<?= $_prLang === 'es' ? "Cu\u{00e9}ntanos sobre ti" : 'Tell us about yourself' ?>"><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
            </div>

            <div class="member-field">
                <label class="member-label"><?= $_prLang === 'es' ? 'Correo' : 'Email' ?></label>
                <input class="member-input" type="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>" disabled>
                <small class="member-text-muted"><?= $_prLang === 'es' ? "Para cambiar su correo, vaya a <a href=\"/member/settings\" class=\"member-link\">Configuraci\u{00f3}n</a>" : 'To change your email, go to <a href="/member/settings" class="member-link">Settings</a>' ?></small>
            </div>

            <button type="submit" class="member-btn"><?= $_prLang === 'es' ? 'Guardar Perfil' : 'Save Profile' ?></button>
        </form>

        <div class="member-footer">
            <a href="/member/settings" class="member-link"><?= $_prLang === 'es' ? "Configuraci\u{00f3}n de Cuenta" : 'Account Settings' ?></a>
            <a href="/member/activity" class="member-link"><?= $_prLang === 'es' ? 'Historial de Actividad' : 'Activity History' ?></a>
        </div>
    </div>
</div>
