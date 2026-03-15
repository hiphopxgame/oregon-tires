<?php
/**
 * GET /api/member/my-vehicles.php
 *
 * Returns customer vehicles for dashboard tab.
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

    // Fetch member email for matching guest-booked vehicles
    $memberStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $memberStmt->execute([$memberId]);
    $memberEmail = $memberStmt->fetchColumn();

    // Query vehicles: match by member_id OR via customer email (orphaned vehicles)
    $sql = 'SELECT DISTINCT v.id, v.year, v.make, v.model, v.vin, v.tire_size, v.license_plate, v.created_at
            FROM oretir_vehicles v
            LEFT JOIN oretir_customers c ON v.customer_id = c.id
            WHERE (v.member_id = :mid';

    $params = [':mid' => $memberId];

    if ($memberEmail) {
        $sql .= ' OR (c.email = :email AND v.member_id IS NULL)';
        $params[':email'] = $memberEmail;
    }

    $sql .= ') ORDER BY v.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Backfill: claim orphaned vehicles so future queries are fast
    if ($memberEmail) {
        try {
            $backfill = $pdo->prepare(
                'UPDATE oretir_vehicles v
                 JOIN oretir_customers c ON v.customer_id = c.id
                 SET v.member_id = :mid
                 WHERE c.email = :email AND v.member_id IS NULL'
            );
            $backfill->execute([':mid' => $memberId, ':email' => $memberEmail]);
        } catch (\Throwable $e) {
            error_log("Oregon Tires backfill orphaned vehicles error: " . $e->getMessage());
        }
    }

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1><?= htmlspecialchars(memberT('my_vehicles', $lang)) ?></h1>
                <p><?= htmlspecialchars(memberT('vehicles_subtitle', $lang)) ?></p>
            </div>

            <?php if (empty($vehicles)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    <?= htmlspecialchars(memberT('no_vehicles', $lang)) ?>
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 3px solid var(--member-accent);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem; font-size: 0.95rem;">
                                        <?= htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']) ?>
                                    </h3>
                                    <?php if (!empty($vehicle['license_plate'])): ?>
                                        <p style="margin: 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                            <?= htmlspecialchars(memberT('license', $lang)) ?>: <?= htmlspecialchars($vehicle['license_plate']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($vehicle['vin'])): ?>
                                <p style="margin: 0.5rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    VIN: <?= htmlspecialchars($vehicle['vin']) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($vehicle['tire_size'])): ?>
                                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= htmlspecialchars(memberT('tire_size', $lang)) ?>: <?= htmlspecialchars($vehicle['tire_size']) ?>
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
    error_log("Oregon Tires customer/my-vehicles error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
