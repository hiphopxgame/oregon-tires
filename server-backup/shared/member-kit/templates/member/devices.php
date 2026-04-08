<?php
/**
 * Devices Management Template — Phase 5.1
 * Displays trusted devices with skeleton loading placeholders
 */
$member = $member ?? MemberAuth::getCurrentMember();
$csrfToken = $csrfToken ?? MemberAuth::getCsrfToken();
?>
<div class="member-page">
    <div class="member-card member-card--wide">
        <div class="member-header">
            <h1>Trusted Devices</h1>
            <p>Manage your active sessions and trusted devices</p>
        </div>

        <!-- Skeleton loading placeholders (Phase 5.4) -->
        <div id="devices-loading" aria-busy="true" aria-label="Loading devices" role="status">
            <div class="member-skeleton-grid">
                <div class="member-skeleton member-device-card" aria-hidden="true"></div>
                <div class="member-skeleton member-device-card" aria-hidden="true"></div>
                <div class="member-skeleton member-device-card" aria-hidden="true"></div>
            </div>
        </div>

        <!-- Populated by API -->
        <div id="devices-content" aria-busy="false" style="display:none;"></div>

        <div class="member-footer">
            <a href="/member/settings" class="member-link">Account Settings</a>
            <a href="/member/login-history" class="member-link">Login History</a>
        </div>
    </div>
</div>
