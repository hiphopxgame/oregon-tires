<?php
/**
 * GET /api/member/my-bookings-ui.php
 *
 * Returns appointment list as HTML for dashboard tab.
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
    $status = sanitize((string) ($_GET['status'] ?? ''), 20);

    // Fetch member email for matching guest bookings
    $memberStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $memberStmt->execute([$memberId]);
    $memberEmail = $memberStmt->fetchColumn();

    // Get customer visit_count for loyalty badge
    $visitCount = 0;
    if ($memberEmail) {
        $vcStmt = $pdo->prepare('SELECT visit_count FROM oretir_customers WHERE email = ? LIMIT 1');
        $vcStmt->execute([$memberEmail]);
        $vc = $vcStmt->fetchColumn();
        if ($vc !== false) {
            $visitCount = (int) $vc;
        }
    }

    // RO status step map (10 steps)
    $roStepMap = [
        'intake'           => 1,
        'check_in'         => 2,
        'diagnosis'        => 3,
        'estimate_pending' => 4,
        'pending_approval' => 5,
        'approved'         => 6,
        'in_progress'      => 7,
        'on_hold'          => 7,
        'waiting_parts'    => 7,
        'ready'            => 8,
        'completed'        => 9,
        'invoiced'         => 10,
    ];
    $roStepLabels = [
        'en' => [
            'intake'           => 'Intake',
            'check_in'         => 'Checked in',
            'diagnosis'        => 'Diagnosis',
            'estimate_pending' => 'Estimate ready',
            'pending_approval' => 'Awaiting approval',
            'approved'         => 'Approved',
            'in_progress'      => 'Work in progress',
            'on_hold'          => 'On hold',
            'waiting_parts'    => 'Waiting for parts',
            'ready'            => 'Ready for pickup',
            'completed'        => 'Completed',
            'invoiced'         => 'Invoiced',
        ],
        'es' => [
            'intake'           => 'Recepción',
            'check_in'         => 'Registrado',
            'diagnosis'        => 'Diagnóstico',
            'estimate_pending' => 'Estimado listo',
            'pending_approval' => 'Esperando aprobación',
            'approved'         => 'Aprobado',
            'in_progress'      => 'En progreso',
            'on_hold'          => 'En pausa',
            'waiting_parts'    => 'Esperando piezas',
            'ready'            => 'Listo para recoger',
            'completed'        => 'Completado',
            'invoiced'         => 'Facturado',
        ],
    ];

    // Enhanced query: full RO journey data
    $sql = 'SELECT DISTINCT a.id, a.reference_number, a.service, a.preferred_date, a.preferred_time,
                   a.vehicle_year, a.vehicle_make, a.vehicle_model, a.status, a.language,
                   a.created_at, e.name as employee_name,
                   insp.customer_view_token,
                   ro.id as ro_id, ro.status as ro_status, ro.ro_number,
                   ro.customer_concern, ro.promised_date, ro.checked_in_at, ro.service_started_at,
                   ro.service_ended_at, ro.checked_out_at,
                   est.id as estimate_id, est.estimate_number, est.status as estimate_status,
                   est.total as estimate_total, est.approval_token,
                   inv.invoice_number, inv.status as invoice_status, inv.total as invoice_total,
                   inv.customer_view_token as invoice_token,
                   (SELECT COUNT(*) FROM oretir_inspection_photos ip
                    JOIN oretir_inspection_items ii ON ip.inspection_item_id = ii.id
                    WHERE ii.inspection_id = insp.id) as photo_count
            FROM oretir_appointments a
            LEFT JOIN oretir_customers c ON a.customer_id = c.id
            LEFT JOIN oretir_employees e ON a.assigned_employee_id = e.id
            LEFT JOIN oretir_repair_orders ro ON ro.appointment_id = a.id
            LEFT JOIN oretir_inspections insp ON insp.repair_order_id = ro.id
            LEFT JOIN oretir_estimates est ON est.repair_order_id = ro.id
            LEFT JOIN oretir_invoices inv ON inv.repair_order_id = ro.id
            WHERE (a.member_id = :mid';

    $params = [':mid' => $memberId];

    if ($memberEmail) {
        $sql .= ' OR (c.email = :email AND a.member_id IS NULL)';
        $params[':email'] = $memberEmail;
    }

    $sql .= ')';

    if ($status && in_array($status, ['new', 'confirmed', 'completed', 'cancelled'], true)) {
        $sql .= ' AND a.status = :status';
        $params[':status'] = $status;
    }

    $sql .= ' ORDER BY a.preferred_date DESC, a.preferred_time DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Backfill: claim orphaned appointments so future queries are fast (direct member_id match)
    if ($memberEmail) {
        try {
            $backfill = $pdo->prepare(
                'UPDATE oretir_appointments a
                 JOIN oretir_customers c ON a.customer_id = c.id
                 SET a.member_id = :mid
                 WHERE c.email = :email AND a.member_id IS NULL'
            );
            $backfill->execute([':mid' => $memberId, ':email' => $memberEmail]);
        } catch (\Throwable $e) {
            error_log("Oregon Tires backfill orphaned appointments error: " . $e->getMessage());
        }
    }

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1><?= htmlspecialchars(memberT('my_appointments', $lang)) ?></h1>
                <p><?= htmlspecialchars(memberT('appt_subtitle', $lang)) ?></p>
            </div>

            <?php if ($visitCount >= 3):
                if ($visitCount >= 10) {
                    $loyaltyLabel = memberT('loyal_customer', $lang);
                } elseif ($visitCount >= 5) {
                    $loyaltyLabel = memberT('regular_customer', $lang);
                } else {
                    $loyaltyLabel = memberT('valued_customer', $lang);
                }
                $visitsLabel = sprintf(memberT('visits_count', $lang), $visitCount);
            ?>
            <div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:var(--member-radius);padding:0.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                <span style="font-size:1.25rem;">⭐</span>
                <span style="color:#92400e;font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($loyaltyLabel) ?> — <?= htmlspecialchars($visitsLabel) ?></span>
            </div>
            <?php endif; ?>

            <?php if (empty($bookings)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    <?= htmlspecialchars(memberT('no_appointments', $lang)) ?>
                    <a href="/book-appointment/" style="color: var(--member-accent); text-decoration: none;">
                        <?= htmlspecialchars(memberT('book_now', $lang)) ?>
                    </a>
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($bookings as $b):
                        $vehicle = trim(($b['vehicle_year'] ?? '') . ' ' . ($b['vehicle_make'] ?? '') . ' ' . ($b['vehicle_model'] ?? ''));
                        $hasRo = !empty($b['ro_status']);
                        $roStatus = $b['ro_status'] ?? '';
                        $currentStep = $roStepMap[$roStatus] ?? 0;
                        $totalSteps = 10;
                        $stepLabel = $roStepLabels[$lang][$roStatus] ?? $roStepLabels['en'][$roStatus] ?? $roStatus;
                        $isActive = $hasRo && !in_array($roStatus, ['completed', 'invoiced', 'cancelled'], true);
                        $isDone = in_array($roStatus, ['completed', 'invoiced'], true);
                        $isCancelled = $b['status'] === 'cancelled' || $roStatus === 'cancelled';

                        // Colors
                        if ($isDone) { $borderColor = '#22c55e'; $barColor = '#22c55e'; $barBg = '#dcfce7'; $textColor = '#166534'; }
                        elseif ($isCancelled) { $borderColor = '#ef4444'; $barColor = '#ef4444'; $barBg = '#fef2f2'; $textColor = '#991b1b'; }
                        elseif ($isActive) { $borderColor = '#3b82f6'; $barColor = '#3b82f6'; $barBg = '#dbeafe'; $textColor = '#1e40af'; }
                        else { $borderColor = '#f59e0b'; $barColor = '#f59e0b'; $barBg = '#fef3c7'; $textColor = '#92400e'; }

                        $canApprove = in_array($b['estimate_status'] ?? '', ['sent', 'viewed'], true) && !empty($b['approval_token']);
                    ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 4px solid <?= $borderColor ?>;">
                            <!-- Header: Vehicle + Status -->
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem; font-size: 1rem; font-weight: 700;">
                                        <?= htmlspecialchars($vehicle ?: memberT('no_vehicle', $lang)) ?>
                                    </h3>
                                    <p style="margin: 0; color: var(--member-text-muted); font-size: 0.8rem;">
                                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $b['service'] ?? ''))) ?>
                                        · <?= htmlspecialchars(date('M d, Y', strtotime($b['preferred_date']))) ?>
                                        · <?= htmlspecialchars(date('g:i A', strtotime($b['preferred_time']))) ?>
                                    </p>
                                    <?php if (!empty($b['employee_name'])): ?>
                                    <p style="margin: 0.125rem 0 0; color: var(--member-text-muted); font-size: 0.8rem;">
                                        🔧 <?= htmlspecialchars($b['employee_name']) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <span style="display: inline-block; padding: 0.2rem 0.6rem; background: <?= $borderColor ?>; color: white; border-radius: 0.25rem; font-size: 0.7rem; font-weight: 700;">
                                        <?= htmlspecialchars($hasRo ? $stepLabel : ucfirst($b['status'])) ?>
                                    </span>
                                    <p style="margin: 0.25rem 0 0; font-size: 0.65rem; color: var(--member-text-muted);">
                                        <?= htmlspecialchars($b['reference_number']) ?>
                                        <?= !empty($b['ro_number']) ? ' · ' . htmlspecialchars($b['ro_number']) : '' ?>
                                    </p>
                                </div>
                            </div>

                            <?php if ($hasRo && !$isCancelled): ?>
                            <!-- RO Progress Bar -->
                            <div style="margin: 0.5rem 0; padding: 0.5rem 0.75rem; background: <?= $barBg ?>; border-radius: 0.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.7rem; font-weight: 700; color: <?= $textColor ?>;">
                                        <?= $lang === 'es' ? 'Paso' : 'Step' ?> <?= $currentStep ?>/<?= $totalSteps ?> — <?= htmlspecialchars($stepLabel) ?>
                                    </span>
                                </div>
                                <div style="background: rgba(0,0,0,0.08); border-radius: 0.25rem; height: 6px; overflow: hidden;">
                                    <div style="background: <?= $barColor ?>; height: 100%; width: <?= round(($currentStep / $totalSteps) * 100) ?>%; border-radius: 0.25rem;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                                    <?php for ($s = 1; $s <= $totalSteps; $s++): ?>
                                    <div style="width: 5px; height: 5px; border-radius: 50%; background: <?= $s <= $currentStep ? $barColor : 'rgba(0,0,0,0.12)' ?>;"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <?php if ($canApprove): ?>
                                <a href="/approve/<?= htmlspecialchars($b['approval_token']) ?>" style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.4rem 0.75rem; background: #f59e0b; color: #000; text-decoration: none; font-size: 0.8rem; font-weight: 700; border-radius: 0.375rem;">
                                    ✅ <?= $lang === 'es' ? 'Revisar Presupuesto' : 'Review Estimate' ?>
                                    ($<?= number_format((float) ($b['estimate_total'] ?? 0), 2) ?>)
                                </a>
                                <?php endif; ?>

                                <?php if (!empty($b['customer_view_token'])): ?>
                                <a href="/inspection/<?= htmlspecialchars($b['customer_view_token']) ?>" style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.4rem 0.75rem; background: var(--member-accent); color: var(--member-accent-text); text-decoration: none; font-size: 0.8rem; font-weight: 600; border-radius: 0.375rem;">
                                    🔍 <?= htmlspecialchars(memberT('view_inspection', $lang)) ?>
                                    <?php if (!empty($b['photo_count'])): ?>
                                    (<?= (int) $b['photo_count'] ?> <?= htmlspecialchars(memberT('photos', $lang)) ?>)
                                    <?php endif; ?>
                                </a>
                                <?php endif; ?>

                                <?php if (!empty($b['invoice_token'])): ?>
                                <a href="/invoice/<?= htmlspecialchars($b['invoice_token']) ?>" style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.4rem 0.75rem; background: #16a34a; color: white; text-decoration: none; font-size: 0.8rem; font-weight: 600; border-radius: 0.375rem;">
                                    🧾 <?= $lang === 'es' ? 'Ver Factura' : 'View Invoice' ?>
                                    ($<?= number_format((float) ($b['invoice_total'] ?? 0), 2) ?>)
                                </a>
                                <?php endif; ?>

                                <?php if ($b['status'] !== 'cancelled' && !$isDone): ?>
                                <a href="/book-appointment/" style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.4rem 0.75rem; color: var(--member-text-muted); text-decoration: none; font-size: 0.75rem; border: 1px solid var(--member-border); border-radius: 0.375rem;">
                                    <?= $lang === 'es' ? 'Reagendar' : 'Reschedule' ?>
                                </a>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($b['customer_concern'])): ?>
                            <p style="margin: 0.5rem 0 0; font-size: 0.75rem; color: var(--member-text-muted); font-style: italic;">
                                <?= htmlspecialchars($b['customer_concern']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (\Throwable $e) {
    error_log("Oregon Tires customer/my-bookings-ui error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
