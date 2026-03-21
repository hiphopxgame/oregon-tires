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
    session_write_close(); // release session lock for read-only request

    // Fetch member email for fallback matching
    $memberStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $memberStmt->execute([$memberId]);
    $memberEmail = $memberStmt->fetchColumn();

    // Get estimates for this customer's repair orders (member_id OR email fallback)
    $stmt = $pdo->prepare(
        'SELECT e.id, e.estimate_number, e.total, e.status, e.created_at, e.approval_token,
                ro.ro_number, ro.status AS ro_status,
                insp.customer_view_token
         FROM oretir_estimates e
         JOIN oretir_repair_orders ro ON e.repair_order_id = ro.id
         JOIN oretir_customers c ON ro.customer_id = c.id
         LEFT JOIN oretir_inspections insp ON insp.repair_order_id = ro.id
         WHERE (c.member_id = ? OR c.email = ?)
         ORDER BY e.created_at DESC'
    );
    $stmt->execute([$memberId, $memberEmail]);
    $estimates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // RO status → step mapping for progress bar
    $roStepMap = [
        'intake' => 1, 'check_in' => 2, 'diagnosis' => 3, 'estimate_pending' => 4,
        'pending_approval' => 5, 'approved' => 6, 'in_progress' => 7, 'on_hold' => 7,
        'waiting_parts' => 7, 'ready' => 8, 'completed' => 9, 'invoiced' => 10,
    ];
    $roStepLabels = [
        'en' => ['', 'Intake', 'Checked In', 'Diagnosis', 'Estimate', 'Approval', 'Approved', 'Working', 'Ready', 'Complete', 'Invoiced'],
        'es' => ['', 'Recepción', 'Registrado', 'Diagnóstico', 'Presupuesto', 'Aprobación', 'Aprobado', 'Trabajando', 'Listo', 'Completo', 'Facturado'],
    ];

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
                    <?php foreach ($estimates as $est):
                        $roStep = $roStepMap[$est['ro_status'] ?? ''] ?? 0;
                        $roLabel = ($roStepLabels[$lang] ?? $roStepLabels['en'])[$roStep] ?? '';
                        $canApprove = in_array($est['status'], ['sent', 'viewed'], true) && !empty($est['approval_token']);
                        $statusColors = ['sent' => '#3b82f6', 'viewed' => '#3b82f6', 'approved' => '#16a34a', 'partial' => '#f59e0b', 'declined' => '#ef4444', 'draft' => '#9ca3af'];
                        $statusColor = $statusColors[$est['status']] ?? '#6b7280';
                    ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 3px solid <?= $canApprove ? '#f59e0b' : 'var(--member-accent)' ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem; font-size: 0.95rem;">
                                        <?= htmlspecialchars(memberT('estimate', $lang)) ?> <?= htmlspecialchars($est['estimate_number']) ?>
                                    </h3>
                                    <p style="margin: 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                        RO: <?= htmlspecialchars($est['ro_number']) ?>
                                    </p>
                                </div>
                                <span style="padding: 0.25rem 0.75rem; background: <?= $statusColor ?>; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                    <?= htmlspecialchars(ucfirst($est['status'])) ?>
                                </span>
                            </div>

                            <!-- RO Progress Bar -->
                            <?php if ($roStep > 0): ?>
                            <div style="margin: 0.5rem 0; padding: 0.5rem; background: rgba(0,0,0,0.05); border-radius: 0.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--member-text-muted);">
                                        <?= $lang === 'es' ? 'Paso' : 'Step' ?> <?= $roStep ?>/10 — <?= htmlspecialchars($roLabel) ?>
                                    </span>
                                </div>
                                <div style="display: flex; gap: 2px;">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <div style="flex: 1; height: 4px; border-radius: 2px; background: <?= $i <= $roStep ? '#16a34a' : 'rgba(0,0,0,0.1)' ?>;"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div style="margin-top: 0.5rem;">
                                <p style="margin: 0; font-size: 0.875rem;">
                                    <?= htmlspecialchars(memberT('total', $lang)) ?>: <strong>$<?= number_format((float) ($est['total'] ?? 0), 2) ?></strong>
                                </p>
                                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($est['created_at']))) ?>
                                </p>

                                <?php if ($canApprove): ?>
                                <p style="margin: 0.75rem 0 0;">
                                    <a href="/approve/<?= htmlspecialchars($est['approval_token']) ?>" style="display: inline-block; padding: 0.5rem 1.25rem; background: #f59e0b; color: #000; text-decoration: none; font-size: 0.85rem; font-weight: 700; border-radius: 0.5rem;">
                                        <?= $lang === 'es' ? '✅ Revisar y Aprobar' : '✅ Review & Approve' ?>
                                    </a>
                                </p>
                                <?php endif; ?>

                                <?php if (!empty($est['customer_view_token'])): ?>
                                <p style="margin: 0.5rem 0 0;">
                                    <a href="/inspection/<?= htmlspecialchars($est['customer_view_token']) ?>" style="color: var(--member-accent); text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                                        <?= htmlspecialchars(memberT('view_inspection', $lang)) ?> →
                                    </a>
                                </p>
                                <?php endif; ?>
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
