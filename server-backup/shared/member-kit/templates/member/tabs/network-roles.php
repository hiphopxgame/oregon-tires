<?php
/**
 * Network Roles Tab -- Super admin network-wide role overview
 *
 * Shows all sites and their role assignments across the network.
 * Only visible to super admins (users.is_admin = 1).
 *
 * Queries engine_sites for the list of network sites and
 * user_site_roles for role assignments per site.
 */

declare(strict_types=1);

if (!MemberAuth::isSuperAdmin()) {
    echo '<div class="member-alert member-alert--error">Access denied. Super admin only.</div>';
    return;
}

$pdo = MemberAuth::getPdo();
$csrfToken = MemberAuth::getCsrfToken();

// Load all active network sites
$sites = [];
try {
    $stmt = $pdo->query(
        "SELECT site_key, name, domain, site_type, status
         FROM engine_sites
         WHERE status IN ('active', 'development')
         ORDER BY site_type, name"
    );
    $sites = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    echo '<div class="member-alert member-alert--error">Failed to load sites.</div>';
    return;
}

// Load role counts per site
$roleCounts = [];
try {
    $stmt = $pdo->query(
        "SELECT site_key, role, COUNT(*) as cnt
         FROM user_site_roles
         GROUP BY site_key, role"
    );
    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $roleCounts[$row['site_key']][$row['role']] = (int) $row['cnt'];
    }
} catch (\Throwable) {
    // Table may not exist yet
}
?>
<div class="member-page">
    <div class="member-card member-card--wide">
        <div class="member-header">
            <h1>Network Role Overview</h1>
            <p>All sites and their role assignments across the 1vsM network</p>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--member-border);">
                    <th style="padding: 0.75rem 0.5rem;">Site</th>
                    <th style="padding: 0.75rem 0.5rem;">Domain</th>
                    <th style="padding: 0.75rem 0.5rem;">Type</th>
                    <th style="padding: 0.75rem 0.5rem;">Status</th>
                    <th style="padding: 0.75rem 0.5rem;">Admins</th>
                    <th style="padding: 0.75rem 0.5rem;">Managers</th>
                    <th style="padding: 0.75rem 0.5rem;">Support</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site): ?>
                <?php
                    $sk = $site['site_key'];
                    $admins = $roleCounts[$sk]['admin'] ?? 0;
                    $managers = $roleCounts[$sk]['manager'] ?? 0;
                    $support = $roleCounts[$sk]['support'] ?? 0;
                ?>
                <tr style="border-bottom: 1px solid var(--member-border);">
                    <td style="padding: 0.5rem;">
                        <strong><?= htmlspecialchars($site['name']) ?></strong>
                        <div style="font-size: 0.75rem; color: var(--member-text-muted);"><?= htmlspecialchars($sk) ?></div>
                    </td>
                    <td style="padding: 0.5rem; color: var(--member-text-muted);">
                        <?= htmlspecialchars($site['domain']) ?>
                    </td>
                    <td style="padding: 0.5rem;">
                        <span style="background: <?= $site['site_type'] === 'hiphop' ? '#f59e0b33' : '#3b82f633' ?>; color: <?= $site['site_type'] === 'hiphop' ? '#f59e0b' : '#3b82f6' ?>; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                            <?= htmlspecialchars($site['site_type']) ?>
                        </span>
                    </td>
                    <td style="padding: 0.5rem;">
                        <span style="background: <?= $site['status'] === 'active' ? '#22c55e33' : '#94a3b833' ?>; color: <?= $site['status'] === 'active' ? '#22c55e' : '#94a3b8' ?>; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                            <?= htmlspecialchars($site['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 0.5rem; text-align: center;"><?= $admins ?></td>
                    <td style="padding: 0.5rem; text-align: center;"><?= $managers ?></td>
                    <td style="padding: 0.5rem; text-align: center;"><?= $support ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($sites)): ?>
        <p style="color: var(--member-text-muted); text-align: center; padding: 2rem;">
            No active network sites found.
        </p>
        <?php endif; ?>
    </div>
</div>
