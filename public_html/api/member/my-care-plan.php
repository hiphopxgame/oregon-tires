<?php
/**
 * GET /api/member/my-care-plan.php
 *
 * Returns care plan status as HTML for the member dashboard tab.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

try {
    requireMethod('GET');

    if (!MemberAuth::isMemberLoggedIn()) {
        http_response_code(401);
        echo '<div class="member-alert member-alert--error">Please sign in to view your care plan.</div>';
        exit;
    }

    $memberId = (int) $_SESSION['member_id'];

    // Fetch member email
    $memberStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $memberStmt->execute([$memberId]);
    $memberEmail = $memberStmt->fetchColumn();

    // Check for existing care plan by member_id or email
    $plan = null;
    if ($memberEmail) {
        $stmt = $pdo->prepare(
            'SELECT id, plan_type, status, monthly_price, period_start, period_end, created_at, updated_at
             FROM oretir_care_plans
             WHERE (member_id = ? OR customer_email = ?) AND status NOT IN (?)
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$memberId, $memberEmail, 'cancelled']);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Plan display names and features
    $planFeatures = [
        'basic' => [
            'name' => 'Basic',
            'price' => '$19/mo',
            'features' => ['1 oil change per year', '5% off all services', 'Free tire rotations', 'Priority scheduling'],
        ],
        'standard' => [
            'name' => 'Standard',
            'price' => '$29/mo',
            'features' => ['2 oil changes per year', '10% off all services', 'Free tire rotations', 'Priority scheduling', 'Free multi-point inspections'],
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => '$49/mo',
            'features' => ['Unlimited oil changes', '15% off all services', 'Free tire rotations', 'Priority scheduling', 'Free multi-point inspections', 'Roadside assistance', 'Free alignment check'],
        ],
    ];

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1>Care Plan</h1>
                <p>Your monthly auto care membership</p>
            </div>

            <?php if (!$plan): ?>
                <div style="text-align: center; padding: 2rem 0;">
                    <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--member-text);">You do not have an active Care Plan</p>
                    <p class="member-text-muted" style="margin-bottom: 1.5rem;">Save on oil changes, tire rotations, and all services with a monthly plan.</p>
                    <a href="/care-plan" style="display: inline-block; padding: 0.75rem 2rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
                        View Plans &amp; Enroll
                    </a>
                </div>
            <?php else:
                $info = $planFeatures[$plan['plan_type']] ?? $planFeatures['basic'];
                $statusColors = [
                    'active'  => '#15803d',
                    'pending' => '#d97706',
                    'paused'  => '#6b7280',
                    'expired' => '#dc2626',
                ];
                $statusColor = $statusColors[$plan['status']] ?? '#6b7280';
            ?>
                <div style="padding: 1.5rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 4px solid <?= $statusColor ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <h3 style="margin: 0 0 0.25rem; font-size: 1.1rem;">
                                <?= htmlspecialchars($info['name']) ?> Care Plan
                            </h3>
                            <p style="margin: 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                <?= htmlspecialchars($info['price']) ?>
                            </p>
                        </div>
                        <span style="padding: 0.25rem 0.75rem; background: <?= $statusColor ?>; color: #fff; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                            <?= htmlspecialchars(ucfirst($plan['status'])) ?>
                        </span>
                    </div>

                    <?php if ($plan['period_start'] && $plan['period_end']): ?>
                    <div style="margin-bottom: 1rem; font-size: 0.875rem;">
                        <p style="margin: 0;">
                            Current period: <strong><?= htmlspecialchars(date('M d, Y', strtotime($plan['period_start']))) ?></strong>
                            &mdash; <strong><?= htmlspecialchars(date('M d, Y', strtotime($plan['period_end']))) ?></strong>
                        </p>
                    </div>
                    <?php endif; ?>

                    <div style="margin-top: 1rem;">
                        <p style="font-weight: 600; margin: 0 0 0.5rem; font-size: 0.875rem;">Your Benefits:</p>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($info['features'] as $feature): ?>
                            <li style="padding: 0.25rem 0; font-size: 0.875rem; color: var(--member-text-muted);">
                                <span style="color: <?= $statusColor ?>; margin-right: 0.5rem;">&#10003;</span>
                                <?= htmlspecialchars($feature) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php if ($plan['status'] === 'active'): ?>
                    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--member-border);">
                        <a href="/book-appointment/" style="display: inline-block; padding: 0.5rem 1.5rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.375rem; text-decoration: none; font-weight: 600; font-size: 0.875rem;">
                            Book an Appointment
                        </a>
                    </div>
                    <?php elseif ($plan['status'] === 'pending'): ?>
                    <div style="margin-top: 1rem; padding: 0.75rem; background: #fef3c7; border-radius: 0.375rem; font-size: 0.875rem; color: #92400e;">
                        Your enrollment is being processed. We will contact you to complete payment setup.
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (\Throwable $e) {
    error_log("Oregon Tires member/my-care-plan error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">Error loading care plan. Please try again.</div>';
}
