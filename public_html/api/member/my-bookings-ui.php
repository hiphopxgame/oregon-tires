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
    $status = sanitize((string) ($_GET['status'] ?? ''), 20);

    // Fetch member email for matching guest bookings
    $memberStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $memberStmt->execute([$memberId]);
    $memberEmail = $memberStmt->fetchColumn();

    // Enhanced query: match by member_id OR by email on orphaned appointments
    $sql = 'SELECT DISTINCT a.id, a.reference_number, a.service, a.preferred_date, a.preferred_time,
                   a.vehicle_year, a.vehicle_make, a.vehicle_model, a.status, a.language,
                   a.created_at
            FROM oretir_appointments a
            LEFT JOIN oretir_customers c ON a.customer_id = c.id
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
