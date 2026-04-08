<?php
$member = $member ?? (MemberAuth::isLoggedIn() ? MemberAuth::getCurrentMember() : null);
?>
<div class="member-page">
    <div class="member-card">
        <div class="member-header">
            <h1>Security Activity</h1>
            <p>Review suspicious login attempts and manage your account security.</p>
        </div>
        <div class="member-anomaly-list">
            <!-- Populated by JavaScript -->
        </div>
    </div>
</div>
