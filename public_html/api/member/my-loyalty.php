<?php
/**
 * GET /api/member/my-loyalty.php
 *
 * Returns loyalty balance, recent history, and available rewards
 * as HTML for the member dashboard tab.
 * Bilingual EN/ES support via member-translations.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
require_once __DIR__ . '/../../includes/member-translations.php';
require_once __DIR__ . '/../../includes/loyalty.php';

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
        'SELECT id, loyalty_balance, visit_count FROM oretir_customers
         WHERE (member_id = ? OR email = ?)
         ORDER BY member_id IS NOT NULL DESC
         LIMIT 1'
    );
    $custStmt->execute([$memberId, $memberEmail ?: '']);
    $customer = $custStmt->fetch(PDO::FETCH_ASSOC);

    $balance = $customer ? (int) $customer['loyalty_balance'] : 0;
    $visitCount = $customer ? (int) $customer['visit_count'] : 0;
    $customerId = $customer ? (int) $customer['id'] : 0;

    // Get recent history (last 20 entries)
    $history = $customerId > 0 ? getLoyaltyHistory($pdo, $customerId, 20, 0) : [];

    // Get available rewards
    $rewards = getAvailableRewards($pdo);

    // Translation strings
    $t = [
        'title'          => $lang === 'es' ? 'Mis Puntos de Lealtad' : 'My Loyalty Points',
        'subtitle'       => $lang === 'es' ? 'Gane puntos con cada visita y canjéelos por recompensas' : 'Earn points with every visit and redeem them for rewards',
        'balance'        => $lang === 'es' ? 'Saldo de Puntos' : 'Point Balance',
        'points'         => $lang === 'es' ? 'puntos' : 'points',
        'visits'         => $lang === 'es' ? 'Visitas' : 'Visits',
        'history'        => $lang === 'es' ? 'Historial Reciente' : 'Recent History',
        'no_history'     => $lang === 'es' ? 'Aún no hay actividad de puntos. ¡Gane puntos en su próxima visita!' : 'No point activity yet. Earn points on your next visit!',
        'rewards'        => $lang === 'es' ? 'Recompensas Disponibles' : 'Available Rewards',
        'no_rewards'     => $lang === 'es' ? 'No hay recompensas disponibles en este momento.' : 'No rewards available at this time.',
        'pts'            => $lang === 'es' ? 'pts' : 'pts',
        'earned'         => $lang === 'es' ? 'Ganados' : 'Earned',
        'redeemed'       => $lang === 'es' ? 'Canjeados' : 'Redeemed',
        'adjusted'       => $lang === 'es' ? 'Ajuste' : 'Adjusted',
        'no_account'     => $lang === 'es' ? 'Aún no tiene un perfil de cliente. Reserve una cita para comenzar a ganar puntos.' : 'No customer profile yet. Book an appointment to start earning points.',
        'book_now'       => $lang === 'es' ? 'Reservar Cita' : 'Book Appointment',
        'cost'           => $lang === 'es' ? 'costo' : 'cost',
    ];

    // Type labels for history entries
    $typeLabels = [
        'earn_visit'    => $lang === 'es' ? 'Visita de Servicio' : 'Service Visit',
        'earn_referral' => $lang === 'es' ? 'Referencia' : 'Referral',
        'earn_review'   => $lang === 'es' ? 'Reseña' : 'Review',
        'earn_bonus'    => $lang === 'es' ? 'Bonificación' : 'Bonus',
        'redeem'        => $lang === 'es' ? 'Canje' : 'Redemption',
        'expire'        => $lang === 'es' ? 'Expiración' : 'Expiration',
        'adjust'        => $lang === 'es' ? 'Ajuste' : 'Adjustment',
    ];

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1><?= htmlspecialchars($t['title']) ?></h1>
                <p><?= htmlspecialchars($t['subtitle']) ?></p>
            </div>

            <?php if (!$customer): ?>
                <div style="text-align: center; padding: 2rem 0;">
                    <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--member-text);"><?= htmlspecialchars($t['no_account']) ?></p>
                    <a href="/book-appointment/" style="display: inline-block; padding: 0.75rem 2rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.5rem; text-decoration: none; font-weight: 600; margin-top: 1rem;">
                        <?= htmlspecialchars($t['book_now']) ?>
                    </a>
                </div>
            <?php else: ?>
                <!-- Balance + Visits Summary -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="padding: 1.25rem; background: var(--member-surface-hover); border-radius: var(--member-radius); text-align: center; border-left: 4px solid var(--member-accent);">
                        <p style="margin: 0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--member-text-muted);">
                            <?= htmlspecialchars($t['balance']) ?>
                        </p>
                        <p style="margin: 0.25rem 0 0; font-size: 1.75rem; font-weight: 700; color: var(--member-accent);">
                            <?= number_format($balance) ?>
                        </p>
                        <p style="margin: 0; font-size: 0.75rem; color: var(--member-text-muted);">
                            <?= htmlspecialchars($t['points']) ?>
                        </p>
                    </div>
                    <div style="padding: 1.25rem; background: var(--member-surface-hover); border-radius: var(--member-radius); text-align: center;">
                        <p style="margin: 0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--member-text-muted);">
                            <?= htmlspecialchars($t['visits']) ?>
                        </p>
                        <p style="margin: 0.25rem 0 0; font-size: 1.75rem; font-weight: 700; color: var(--member-text);">
                            <?= number_format($visitCount) ?>
                        </p>
                    </div>
                </div>

                <!-- Available Rewards -->
                <?php if (!empty($rewards)): ?>
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 0.75rem; font-size: 0.95rem; color: var(--member-text);">
                        <?= htmlspecialchars($t['rewards']) ?>
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <?php foreach ($rewards as $reward):
                            $name = $lang === 'es' && $reward['name_es'] !== '' ? $reward['name_es'] : $reward['name_en'];
                            $desc = $lang === 'es' && $reward['description_es'] !== '' ? $reward['description_es'] : $reward['description_en'];
                            $canRedeem = $balance >= (int) $reward['points_cost'];
                        ?>
                        <div style="padding: 0.75rem 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; opacity: <?= $canRedeem ? '1' : '0.6' ?>;">
                            <div style="flex: 1; min-width: 0;">
                                <p style="margin: 0; font-size: 0.875rem; font-weight: 600; color: var(--member-text);">
                                    <?= htmlspecialchars($name) ?>
                                </p>
                                <?php if ($desc !== ''): ?>
                                <p style="margin: 0.125rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= htmlspecialchars($desc) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right; white-space: nowrap;">
                                <span style="font-size: 0.875rem; font-weight: 700; color: <?= $canRedeem ? 'var(--member-accent)' : 'var(--member-text-muted)' ?>;">
                                    <?= number_format((int) $reward['points_cost']) ?> <?= htmlspecialchars($t['pts']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent History -->
                <div>
                    <h3 style="margin: 0 0 0.75rem; font-size: 0.95rem; color: var(--member-text);">
                        <?= htmlspecialchars($t['history']) ?>
                    </h3>
                    <?php if (empty($history)): ?>
                        <p class="member-text-muted" style="text-align: center; padding: 1rem 0; font-size: 0.875rem;">
                            <?= htmlspecialchars($t['no_history']) ?>
                        </p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                            <?php foreach ($history as $entry):
                                $isPositive = (int) $entry['points'] > 0;
                                $typeLabel = $typeLabels[$entry['type']] ?? ucfirst(str_replace('_', ' ', $entry['type']));
                            ?>
                            <div style="padding: 0.625rem 0.75rem; background: var(--member-surface-hover); border-radius: var(--member-radius); display: flex; justify-content: space-between; align-items: center; border-left: 3px solid <?= $isPositive ? '#15803d' : '#dc2626' ?>;">
                                <div>
                                    <p style="margin: 0; font-size: 0.8rem; font-weight: 600; color: var(--member-text);">
                                        <?= htmlspecialchars($typeLabel) ?>
                                    </p>
                                    <?php if ($entry['description'] !== ''): ?>
                                    <p style="margin: 0.125rem 0 0; font-size: 0.7rem; color: var(--member-text-muted);">
                                        <?= htmlspecialchars($entry['description']) ?>
                                    </p>
                                    <?php endif; ?>
                                    <p style="margin: 0.125rem 0 0; font-size: 0.65rem; color: var(--member-text-muted);">
                                        <?= htmlspecialchars(date('M d, Y g:i A', strtotime($entry['created_at']))) ?>
                                    </p>
                                </div>
                                <span style="font-size: 0.875rem; font-weight: 700; color: <?= $isPositive ? '#15803d' : '#dc2626' ?>; white-space: nowrap;">
                                    <?= $isPositive ? '+' : '' ?><?= number_format((int) $entry['points']) ?>
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
    error_log("Oregon Tires member/my-loyalty error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
