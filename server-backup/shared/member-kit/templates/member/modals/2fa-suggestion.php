<?php
$reason = $suggested_reason ?? 'Enable two-factor authentication to protect your account.';
$setupUrl = $setup_url ?? '/member/settings#2fa';
?>
<div id="member-2fa-suggestion-modal" class="member-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="2fa-modal-title" aria-describedby="2fa-modal-body" tabindex="-1">
    <div class="member-modal member-modal--suggestion">
        <div class="member-modal-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                <path d="M8 11V7a4 4 0 018 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="12" cy="16" r="1.5" fill="currentColor"/>
            </svg>
        </div>
        <h2 id="2fa-modal-title" class="member-modal-title">Secure Your Account</h2>
        <p id="2fa-modal-body" class="member-modal-body"><?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="member-modal-actions">
            <a href="<?php echo htmlspecialchars($setupUrl, ENT_QUOTES, 'UTF-8'); ?>" class="member-btn member-btn--primary">Enable 2FA now</a>
            <button type="button" class="member-btn-secondary member-modal-dismiss" data-modal="member-2fa-suggestion-modal">Remind me later</button>
        </div>
    </div>
</div>
