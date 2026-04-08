<?php
$member = $member ?? (MemberAuth::isLoggedIn() ? MemberAuth::getCurrentMember() : null);
$memberId = $memberId ?? ($member ? ($member['id'] ?? 0) : 0);
$email = $member['email'] ?? '';
$status = $_GET['status'] ?? '';
?>
<div class="member-page">
    <div class="member-card">
        <div class="member-header">
            <h1>Verify Your Email</h1>
            <p>We need to confirm your email address before you can continue.</p>
        </div>
        <?php if ($status === 'sent'): ?>
            <div class="member-alert member-alert--success">Verification email sent! Please check your inbox and click the link.</div>
        <?php elseif ($status === 'already_verified'): ?>
            <div class="member-alert member-alert--success">Your email is already verified. You can continue using your account.</div>
        <?php elseif ($status === 'error'): ?>
            <div class="member-alert member-alert--error">Something went wrong. Please try again or contact support.</div>
        <?php else: ?>
            <div class="member-alert member-alert--info">A verification email was sent to <?php if ($email !== ''): ?><strong><?= htmlspecialchars($email) ?></strong>.<?php endif; ?> Please click the link in the email to activate your account.</div>
        <?php endif; ?>
        <?php if ($status !== 'already_verified'): ?>
            <p class="member-text-muted" style="margin-bottom:16px;">Didn't receive the email? Check your spam folder or request a new one below. <br><small>You can request one resend per hour.</small></p>
            <form class="member-form" id="resend-verification-form" data-action="/api/member/resend-verification.php" data-method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
                <input type="hidden" name="member_id" value="<?= htmlspecialchars((string) $memberId) ?>">
                <button type="submit" class="member-btn">Resend Verification Email</button>
            </form>
        <?php endif; ?>
        <div class="member-footer"><?php if (MemberAuth::isLoggedIn()): ?><a href="/member/settings" class="member-link">Back to Settings</a><?php else: ?><a href="/member/login" class="member-link">Back to Sign In</a><?php endif; ?></div>
    </div>
</div>
