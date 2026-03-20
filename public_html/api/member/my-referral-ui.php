<?php
/**
 * GET /api/member/my-referral-ui.php
 *
 * Returns referral dashboard as HTML for the member dashboard tab.
 * Shows referral code, share instructions, stats, and referral history.
 * Bilingual EN/ES support via member-translations.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
require_once __DIR__ . '/../../includes/member-translations.php';
require_once __DIR__ . '/../../includes/referrals.php';

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

    // Fetch member email for fallback customer matching
    $memberStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $memberStmt->execute([$memberId]);
    $memberEmail = $memberStmt->fetchColumn();

    // Find the customer record (by member_id or email fallback)
    $custStmt = $pdo->prepare(
        'SELECT id FROM oretir_customers
         WHERE (member_id = ? OR email = ?)
         ORDER BY member_id IS NOT NULL DESC
         LIMIT 1'
    );
    $custStmt->execute([$memberId, $memberEmail ?: '']);
    $customer = $custStmt->fetch(PDO::FETCH_ASSOC);
    $customerId = $customer ? (int) $customer['id'] : 0;

    // Get or create referral code
    $referralCode = $customerId > 0 ? getOrCreateReferralCode($pdo, $customerId) : null;

    // Count successful referrals
    $successfulReferrals = 0;
    $totalPointsEarned = 0;
    $referralHistory = [];

    if ($customerId > 0) {
        $countStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM oretir_referrals
             WHERE referrer_customer_id = ? AND status = 'rewarded'"
        );
        $countStmt->execute([$customerId]);
        $successfulReferrals = (int) $countStmt->fetchColumn();

        // Total points earned from referrals
        $pointsStmt = $pdo->prepare(
            "SELECT COALESCE(SUM(points), 0) FROM oretir_loyalty_points
             WHERE customer_id = ? AND type = 'earn_referral' AND points > 0"
        );
        $pointsStmt->execute([$customerId]);
        $totalPointsEarned = (int) $pointsStmt->fetchColumn();

        // Referral history (all referrals made by this customer)
        $histStmt = $pdo->prepare(
            "SELECT r.status, r.referrer_points, r.referred_points, r.created_at,
                    CASE WHEN rd.id IS NOT NULL
                         THEN CONCAT(rd.first_name, ' ', LEFT(rd.last_name, 1), '.')
                         ELSE r.referred_email END AS referred_name
             FROM oretir_referrals r
             LEFT JOIN oretir_customers rd ON rd.id = r.referred_customer_id
             WHERE r.referrer_customer_id = ?
             ORDER BY r.created_at DESC
             LIMIT 20"
        );
        $histStmt->execute([$customerId]);
        $referralHistory = $histStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $siteUrl = rtrim($_ENV['APP_URL'] ?? 'https://oregon.tires', '/');

    // Translation strings
    $t = [
        'title'         => $lang === 'es' ? 'Referir un Amigo' : 'Refer a Friend',
        'subtitle'      => $lang === 'es' ? 'Comparta su codigo y ambos ganan puntos de lealtad' : 'Share your code and you both earn loyalty points',
        'your_code'     => $lang === 'es' ? 'Su Codigo de Referencia' : 'Your Referral Code',
        'copy'          => $lang === 'es' ? 'Copiar' : 'Copy',
        'copied'        => $lang === 'es' ? 'Copiado' : 'Copied!',
        'share_link'    => $lang === 'es' ? 'O comparta este enlace:' : 'Or share this link:',
        'how_it_works'  => $lang === 'es' ? 'Como Funciona' : 'How It Works',
        'step1'         => $lang === 'es' ? 'Comparta su codigo con amigos y familiares' : 'Share your code with friends and family',
        'step2'         => $lang === 'es' ? 'Ellos mencionan su codigo al reservar una cita' : 'They mention your code when booking an appointment',
        'step3'         => $lang === 'es' ? 'Ambos reciben puntos de lealtad al completar el servicio' : 'You both earn loyalty points when their service is complete',
        'you_earn'      => $lang === 'es' ? 'Usted gana' : 'You earn',
        'they_earn'     => $lang === 'es' ? 'Ellos ganan' : 'They earn',
        'points'        => $lang === 'es' ? 'puntos' : 'points',
        'stats'         => $lang === 'es' ? 'Sus Resultados' : 'Your Results',
        'referrals_completed' => $lang === 'es' ? 'Referencias Completadas' : 'Referrals Completed',
        'points_earned' => $lang === 'es' ? 'Puntos Ganados' : 'Points Earned',
        'history'       => $lang === 'es' ? 'Historial de Referencias' : 'Referral History',
        'no_history'    => $lang === 'es' ? 'Aun no tiene referencias. Comparta su codigo para comenzar.' : 'No referrals yet. Share your code to get started!',
        'no_account'    => $lang === 'es' ? 'Reserve una cita para obtener su codigo de referencia.' : 'Book an appointment to get your referral code.',
        'book_now'      => $lang === 'es' ? 'Reservar Cita' : 'Book Appointment',
        'pending'       => $lang === 'es' ? 'Pendiente' : 'Pending',
        'booked'        => $lang === 'es' ? 'Reservado' : 'Booked',
        'completed'     => $lang === 'es' ? 'Completado' : 'Completed',
        'rewarded'      => $lang === 'es' ? 'Recompensado' : 'Rewarded',
        'expired'       => $lang === 'es' ? 'Expirado' : 'Expired',
    ];

    $statusLabels = [
        'pending'   => $t['pending'],
        'booked'    => $t['booked'],
        'completed' => $t['completed'],
        'rewarded'  => $t['rewarded'],
        'expired'   => $t['expired'],
    ];

    $statusColors = [
        'pending'   => '#eab308',
        'booked'    => '#3b82f6',
        'completed' => '#3b82f6',
        'rewarded'  => '#22c55e',
        'expired'   => '#9ca3af',
    ];

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1><?= htmlspecialchars($t['title']) ?></h1>
                <p><?= htmlspecialchars($t['subtitle']) ?></p>
            </div>

            <?php if (!$customerId): ?>
                <div style="text-align: center; padding: 2rem 0;">
                    <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--member-text);"><?= htmlspecialchars($t['no_account']) ?></p>
                    <a href="/book-appointment/" style="display: inline-block; padding: 0.75rem 2rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.5rem; text-decoration: none; font-weight: 600; margin-top: 1rem;">
                        <?= htmlspecialchars($t['book_now']) ?>
                    </a>
                </div>
            <?php else: ?>
                <!-- Referral Code Card -->
                <?php if ($referralCode): ?>
                <div style="padding: 1.5rem; background: var(--member-surface-hover); border-radius: var(--member-radius); text-align: center; margin-bottom: 1.5rem; border: 2px dashed var(--member-accent);">
                    <p style="margin: 0 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--member-text-muted);">
                        <?= htmlspecialchars($t['your_code']) ?>
                    </p>
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <span id="referral-code" style="font-size: 2rem; font-weight: 800; letter-spacing: 0.15em; font-family: monospace; color: var(--member-accent);">
                            <?= htmlspecialchars($referralCode) ?>
                        </span>
                        <button type="button" id="copy-code-btn" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($referralCode) ?>').then(function(){var b=document.getElementById('copy-code-btn');b.textContent='<?= htmlspecialchars($t['copied']) ?>';setTimeout(function(){b.textContent='<?= htmlspecialchars($t['copy']) ?>';},2000);})" style="padding: 0.375rem 0.75rem; background: var(--member-accent); color: var(--member-accent-text); border: none; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                            <?= htmlspecialchars($t['copy']) ?>
                        </button>
                    </div>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--member-text-muted);">
                        <?= htmlspecialchars($t['share_link']) ?>
                    </p>
                    <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-accent); word-break: break-all;">
                        <?= htmlspecialchars($siteUrl . '/book-appointment/?ref=' . $referralCode) ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- How It Works -->
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 0.75rem; font-size: 0.95rem; color: var(--member-text);">
                        <?= htmlspecialchars($t['how_it_works']) ?>
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <?php
                        $steps = [$t['step1'], $t['step2'], $t['step3']];
                        foreach ($steps as $i => $step): ?>
                        <div style="padding: 0.625rem 0.75rem; background: var(--member-surface-hover); border-radius: var(--member-radius); display: flex; align-items: center; gap: 0.75rem;">
                            <span style="flex-shrink: 0; width: 1.5rem; height: 1.5rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                                <?= $i + 1 ?>
                            </span>
                            <span style="font-size: 0.85rem; color: var(--member-text);"><?= htmlspecialchars($step) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display: flex; gap: 1rem; margin-top: 0.75rem; justify-content: center;">
                        <span style="font-size: 0.8rem; padding: 0.375rem 0.75rem; background: #15803d22; color: #15803d; border-radius: 0.375rem; font-weight: 600;">
                            <?= htmlspecialchars($t['you_earn']) ?>: 100 <?= htmlspecialchars($t['points']) ?>
                        </span>
                        <span style="font-size: 0.8rem; padding: 0.375rem 0.75rem; background: #3b82f622; color: #3b82f6; border-radius: 0.375rem; font-weight: 600;">
                            <?= htmlspecialchars($t['they_earn']) ?>: 50 <?= htmlspecialchars($t['points']) ?>
                        </span>
                    </div>
                </div>

                <!-- Stats -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="padding: 1.25rem; background: var(--member-surface-hover); border-radius: var(--member-radius); text-align: center; border-left: 4px solid var(--member-accent);">
                        <p style="margin: 0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--member-text-muted);">
                            <?= htmlspecialchars($t['referrals_completed']) ?>
                        </p>
                        <p style="margin: 0.25rem 0 0; font-size: 1.75rem; font-weight: 700; color: var(--member-accent);">
                            <?= number_format($successfulReferrals) ?>
                        </p>
                    </div>
                    <div style="padding: 1.25rem; background: var(--member-surface-hover); border-radius: var(--member-radius); text-align: center;">
                        <p style="margin: 0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--member-text-muted);">
                            <?= htmlspecialchars($t['points_earned']) ?>
                        </p>
                        <p style="margin: 0.25rem 0 0; font-size: 1.75rem; font-weight: 700; color: var(--member-text);">
                            <?= number_format($totalPointsEarned) ?>
                        </p>
                    </div>
                </div>

                <!-- Referral History -->
                <div>
                    <h3 style="margin: 0 0 0.75rem; font-size: 0.95rem; color: var(--member-text);">
                        <?= htmlspecialchars($t['history']) ?>
                    </h3>
                    <?php if (empty($referralHistory)): ?>
                        <p class="member-text-muted" style="text-align: center; padding: 1rem 0; font-size: 0.875rem;">
                            <?= htmlspecialchars($t['no_history']) ?>
                        </p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <?php foreach ($referralHistory as $ref):
                                $statusLabel = $statusLabels[$ref['status']] ?? ucfirst($ref['status']);
                                $statusColor = $statusColors[$ref['status']] ?? '#9ca3af';
                            ?>
                            <div style="padding: 0.625rem 0.75rem; background: var(--member-surface-hover); border-radius: var(--member-radius); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="margin: 0; font-size: 0.8rem; font-weight: 600; color: var(--member-text);">
                                        <?= htmlspecialchars($ref['referred_name'] ?: '—') ?>
                                    </p>
                                    <p style="margin: 0.125rem 0 0; font-size: 0.65rem; color: var(--member-text-muted);">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($ref['created_at']))) ?>
                                    </p>
                                </div>
                                <span style="padding: 0.2rem 0.5rem; background: <?= $statusColor ?>22; color: <?= $statusColor ?>; border-radius: 0.25rem; font-size: 0.7rem; font-weight: 600;">
                                    <?= htmlspecialchars($statusLabel) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (\Throwable $e) {
    error_log("Oregon Tires member/my-referral-ui error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
