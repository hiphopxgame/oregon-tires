<?php
/**
 * GET /api/member/my-bookings-ui.php
 *
 * Returns appointment list as HTML for dashboard tab.
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
        echo '<div class="member-alert member-alert--error">Please sign in to view appointments.</div>';
        exit;
    }

    $memberId = (int) $_SESSION['member_id'];
    $status = sanitize((string) ($_GET['status'] ?? ''), 20);

    $sql = 'SELECT id, reference_number, service, preferred_date, preferred_time,
                   vehicle_year, vehicle_make, vehicle_model, status, language,
                   created_at
            FROM oretir_appointments
            WHERE member_id = ?';
    $params = [$memberId];

    if ($status && in_array($status, ['new', 'confirmed', 'completed', 'cancelled'], true)) {
        $sql .= ' AND status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY preferred_date DESC, preferred_time DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1>My Appointments</h1>
                <p>View and manage your service appointments</p>
            </div>

            <?php if (empty($bookings)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    No appointments scheduled yet.
                    <a href="/book-appointment/" style="color: var(--member-accent); text-decoration: none;">
                        Book one now →
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
                                        Ref: <?= htmlspecialchars($booking['reference_number']) ?>
                                    </p>
                                </div>
                                <span style="padding: 0.25rem 0.75rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.25rem; font-size: 0.75rem;">
                                    <?= htmlspecialchars(ucfirst($booking['status'])) ?>
                                </span>
                            </div>
                            <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                                <p style="margin: 0;">
                                    Service: <strong><?= htmlspecialchars($booking['service']) ?></strong>
                                </p>
                                <p style="margin: 0.25rem 0 0;">
                                    Date & Time: <?= htmlspecialchars(date('M d, Y g:i A', strtotime($booking['preferred_date'] . ' ' . $booking['preferred_time']))) ?>
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
    echo '<div class="member-alert member-alert--error">Error loading appointments. Please try again.</div>';
}
