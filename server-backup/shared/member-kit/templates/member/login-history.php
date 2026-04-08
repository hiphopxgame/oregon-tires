<?php
$member = $member ?? MemberAuth::getCurrentMember();
$csrfToken = $csrfToken ?? MemberAuth::getCsrfToken();
?>
<div class="member-page">
    <div class="member-card member-card--wide">
        <div class="member-header">
            <h1>Login History</h1>
            <p>Recent sign-in activity for your account</p>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
            <button type="button" class="member-btn member-btn--danger member-btn--sm" id="sign-out-all" data-csrf="<?php echo htmlspecialchars($csrfToken); ?>">
                Sign out all devices
            </button>
        </div>
        <div class="member-field" style="margin-bottom:1rem;">
            <select class="member-input" id="activity-filter" style="max-width:200px;">
                <option value="all">All activity</option>
                <option value="success">Successful logins</option>
                <option value="failed">Failed attempts</option>
            </select>
        </div>
        <div id="activity-timeline" class="member-activity-timeline"></div>
        <div class="member-footer">
            <a href="/member/settings" class="member-link">Account Settings</a>
            <a href="/member/devices" class="member-link">Trusted Devices</a>
        </div>
    </div>
</div>
