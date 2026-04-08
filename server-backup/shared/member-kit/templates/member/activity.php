<?php
/**
 * Activity History Template — Member Kit
 *
 * Variables: $member, $activities (from MemberProfile::getActivity())
 */
$member = $member ?? MemberAuth::getCurrentMember();
$activities = $activities ?? MemberProfile::getActivity((int) $member['id']);

$actionLabels = [
    'login'                   => 'Signed in',
    'sso_login'               => 'Signed in via SSO',
    'logout'                  => 'Signed out',
    'register'                => 'Account created',
    'sso_register'            => 'Account created via SSO',
    'profile_updated'         => 'Profile updated',
    'avatar_uploaded'         => 'Avatar changed',
    'password_changed'        => 'Password changed',
    'password_reset_requested' => 'Password reset requested',
    'password_reset_completed' => 'Password reset completed',
    'email_verified'          => 'Email verified',
    'email_change_requested'  => 'Email change requested',
    'sso_unlinked'            => 'SSO account unlinked',
];
?>

<div class="member-page">
    <div class="member-card member-card--wide">
        <div class="member-header">
            <h1>Activity History</h1>
            <p>Your recent account activity</p>
        </div>

        <?php if (empty($activities)): ?>
            <p class="member-text-muted" style="text-align:center;padding:32px 0;">No activity recorded yet.</p>
        <?php else: ?>
            <div class="member-activity-list">
                <?php foreach ($activities as $activity): ?>
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
        <?php endif; ?>

        <div class="member-footer">
            <a href="/member/profile" class="member-link">Back to Profile</a>
        </div>
    </div>
</div>

<?php
// Helper included inline since templates shouldn't require additional includes
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
?>
