<?php
/**
 * GET /api/member/my-estimates.php
 *
 * Returns estimates and inspection reports for dashboard tab.
 * Bilingual EN/ES support via member-translations.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
require_once __DIR__ . '/../../includes/member-translations.php';

startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

$lang = getMemberLang();

try {
    requireMethod('GET');

    if (!MemberAuth::isMemberLoggedIn()) {
        http_response_code(401);
        echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('sign_in_required', $lang)) . '</div>';
        exit;
    }

    $memberId = (int) $_SESSION['member_id'];

    // Get estimates for this customer's repair orders
    $stmt = $pdo->prepare(
        'SELECT e.id, e.estimate_number, e.total, e.status, e.created_at, ro.ro_number
         FROM oretir_estimates e
         JOIN oretir_repair_orders ro ON e.repair_order_id = ro.id
         JOIN oretir_customers c ON ro.customer_id = c.id
         WHERE c.member_id = ?
         ORDER BY e.created_at DESC'
    );
    $stmt->execute([$memberId]);
    $estimates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1><?= htmlspecialchars(memberT('estimates_reports', $lang)) ?></h1>
                <p><?= htmlspecialchars(memberT('estimates_subtitle', $lang)) ?></p>
            </div>

            <?php if (empty($estimates)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    <?= htmlspecialchars(memberT('no_estimates', $lang)) ?>
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($estimates as $est): ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 3px solid var(--member-accent);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem; font-size: 0.95rem;">
                                        <?= htmlspecialchars(memberT('estimate', $lang)) ?> <?= htmlspecialchars($est['estimate_number']) ?>
                                    </h3>
                                    <p style="margin: 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                        RO: <?= htmlspecialchars($est['ro_number']) ?>
                                    </p>
                                </div>
                                <span style="padding: 0.25rem 0.75rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.25rem; font-size: 0.75rem;">
                                    <?= htmlspecialchars(ucfirst($est['status'])) ?>
                                </span>
                            </div>
                            <div style="margin-top: 0.5rem;">
                                <p style="margin: 0; font-size: 0.875rem;">
                                    <?= htmlspecialchars(memberT('total', $lang)) ?>: <strong>$<?= number_format((float) ($est['total'] ?? 0), 2) ?></strong>
                                </p>
                                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($est['created_at']))) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (\Throwable $e) {
    error_log("Oregon Tires customer/my-estimates error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
