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
            'ready'            => 'Listo para recoger',
            'completed'        => 'Completado',
            'invoiced'         => 'Facturado',
        ],
    ];

    // Enhanced query: match by member_id OR by email on orphaned appointments
    $sql = 'SELECT DISTINCT a.id, a.reference_number, a.service, a.preferred_date, a.preferred_time,
                   a.vehicle_year, a.vehicle_make, a.vehicle_model, a.status, a.language,
                   a.created_at, e.name as employee_name,
                   insp.customer_view_token,
                   ro.status as ro_status, ro.ro_number,
                   (SELECT COUNT(*) FROM oretir_inspection_photos ip
                    JOIN oretir_inspection_items ii ON ip.inspection_item_id = ii.id
                    WHERE ii.inspection_id = insp.id) as photo_count
            FROM oretir_appointments a
            LEFT JOIN oretir_customers c ON a.customer_id = c.id
            LEFT JOIN oretir_employees e ON a.assigned_employee_id = e.id
            LEFT JOIN oretir_repair_orders ro ON ro.appointment_id = a.id
            LEFT JOIN oretir_inspections insp ON insp.repair_order_id = ro.id
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
                    <?php foreach ($bookings as $booking): ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 3px solid var(--member-accent);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem; font-size: 0.95rem;">
                                        <?= htmlspecialchars($booking['vehicle_year'] . ' ' . $booking['vehicle_make'] . ' ' . $booking['vehicle_model']) ?>
                                    </h3>
                                    <?php if (!empty($booking['employee_name'])): ?>
                                    <p style="margin: 0 0 0.25rem; color: var(--member-text-muted); font-size: 0.875rem;">
                                        <?= htmlspecialchars(memberT('your_technician', $lang)) ?>: <?= htmlspecialchars($booking['employee_name']) ?>
                                    </p>
                                    <?php endif; ?>
                                    <p style="margin: 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                        <?= htmlspecialchars(memberT('ref', $lang)) ?>: <?= htmlspecialchars($booking['reference_number']) ?>
                                    </p>
                                </div>
                                <span style="padding: 0.25rem 0.75rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.25rem; font-size: 0.75rem;">
                                    <?= htmlspecialchars(ucfirst($booking['status'])) ?>
                                </span>
                            </div>
                            <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                                <p style="margin: 0;">
                                    <?= htmlspecialchars(memberT('service', $lang)) ?>: <strong><?= htmlspecialchars($booking['service']) ?></strong>
                                </p>
                                <p style="margin: 0.25rem 0 0;">
                                    <?= htmlspecialchars(memberT('date_time', $lang)) ?>: <?= htmlspecialchars(date('M d, Y g:i A', strtotime($booking['preferred_date'] . ' ' . $booking['preferred_time']))) ?>
                                </p>
                                <?php if (!empty($booking['customer_view_token'])): ?>
                                <p style="margin: 0.5rem 0 0;">
                                    <a href="/inspection/<?= htmlspecialchars($booking['customer_view_token']) ?>" style="color: var(--member-accent); text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                                        <?= htmlspecialchars(memberT('view_inspection', $lang)) ?>
                                        <?php if (!empty($booking['photo_count'])): ?>
                                        <span style="background: var(--member-accent); color: var(--member-accent-text); padding: 0.1rem 0.4rem; border-radius: 0.25rem; font-size: 0.7rem; margin-left: 0.25rem;">
                                            <?= (int) $booking['photo_count'] ?> <?= htmlspecialchars(memberT('photos', $lang)) ?>
                                        </span>
                                        <?php endif; ?>
                                    </a>
                                </p>
                                <?php endif; ?>
                                <?php
                                // ── RO Status Progress Bar ──
                                if (!empty($booking['ro_status']) && isset($roStepMap[$booking['ro_status']])):
                                    $roStatus = $booking['ro_status'];
                                    $currentStep = $roStepMap[$roStatus];
                                    $totalSteps = 10;
                                    $stepLabel = $roStepLabels[$lang][$roStatus] ?? $roStepLabels['en'][$roStatus] ?? $roStatus;
                                    $stepText = ($lang === 'es')
                                        ? "Paso {$currentStep}/{$totalSteps} — {$stepLabel}"
                                        : "Step {$currentStep}/{$totalSteps} — {$stepLabel}";
                                    $pct = round(($currentStep / $totalSteps) * 100);
                                    // Color: green for completed/invoiced, blue for in-progress, amber for pending
                                    if ($currentStep >= 9) {
                                        $barColor = '#22c55e'; $barBg = '#dcfce7'; $textColor = '#166534';
                                    } elseif ($currentStep >= 6) {
                                        $barColor = '#3b82f6'; $barBg = '#dbeafe'; $textColor = '#1e40af';
                                    } else {
                                        $barColor = '#f59e0b'; $barBg = '#fef3c7'; $textColor = '#92400e';
                                    }
                                ?>
                                <div style="margin-top: 0.75rem; padding: 0.625rem 0.75rem; background: <?= $barBg ?>; border-radius: 0.5rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                                        <span style="font-size: 0.75rem; font-weight: 600; color: <?= $textColor ?>;">
                                            <?= htmlspecialchars($stepText) ?>
                                        </span>
                                        <?php if (!empty($booking['ro_number'])): ?>
                                        <span style="font-size: 0.7rem; color: <?= $textColor ?>; opacity: 0.7;">
                                            <?= htmlspecialchars($booking['ro_number']) ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="background: rgba(0,0,0,0.1); border-radius: 0.25rem; height: 6px; overflow: hidden;">
                                        <div style="background: <?= $barColor ?>; height: 100%; width: <?= $pct ?>%; border-radius: 0.25rem; transition: width 0.3s ease;"></div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                                        <?php for ($s = 1; $s <= $totalSteps; $s++): ?>
                                        <div style="width: 6px; height: 6px; border-radius: 50%; background: <?= $s <= $currentStep ? $barColor : 'rgba(0,0,0,0.15)' ?>;"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
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
    error_log("Oregon Tires customer/my-bookings-ui error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
