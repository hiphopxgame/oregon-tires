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
    $sql = 'SELECT DISTINCT v.id, v.year, v.make, v.model, v.vin, v.trim_level, v.engine, v.transmission,
                   v.drive_type, v.body_class, v.fuel_type, v.doors, v.tire_size_front, v.tire_size_rear,
                   v.tire_size, v.license_plate, v.color, v.mileage, v.created_at
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
                    <?php foreach ($vehicles as $vehicle):
                        $specLabels = $lang === 'es'
                            ? ['engine' => 'Motor', 'transmission' => 'Transmision', 'drive_type' => 'Traccion', 'body_class' => 'Carroceria', 'fuel_type' => 'Combustible', 'trim_level' => 'Version', 'doors' => 'Puertas']
                            : ['engine' => 'Engine', 'transmission' => 'Transmission', 'drive_type' => 'Drive', 'body_class' => 'Body', 'fuel_type' => 'Fuel', 'trim_level' => 'Trim', 'doors' => 'Doors'];
                        $specs = array_filter(array_intersect_key($vehicle, $specLabels));
                    ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 3px solid var(--member-accent);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem; font-size: 0.95rem;">
                                        <?= htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']) ?>
                                        <?php if (!empty($vehicle['trim_level'])): ?>
                                            <span style="font-weight: normal; color: var(--member-text-muted); font-size: 0.8rem;"><?= htmlspecialchars($vehicle['trim_level']) ?></span>
                                        <?php endif; ?>
                                    </h3>
                                    <?php if (!empty($vehicle['license_plate'])): ?>
                                        <p style="margin: 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                            <?= htmlspecialchars(memberT('license', $lang)) ?>: <?= htmlspecialchars($vehicle['license_plate']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($vehicle['color'])): ?>
                                    <span style="font-size: 0.75rem; color: var(--member-text-muted);"><?= htmlspecialchars($vehicle['color']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($specs)): ?>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.125rem 1rem; font-size: 0.75rem; color: var(--member-text-muted); margin-bottom: 0.5rem;">
                                    <?php foreach ($specs as $key => $val): if ($key === 'trim_level') continue; ?>
                                        <div><strong style="color: var(--member-text);"><?= $specLabels[$key] ?>:</strong> <?= htmlspecialchars($val) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($vehicle['vin'])): ?>
                                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    VIN: <span style="font-family: monospace; letter-spacing: 0.05em;"><?= htmlspecialchars($vehicle['vin']) ?></span>
                                </p>
                            <?php endif; ?>
                            <?php
                            $tireDisplay = $vehicle['tire_size_front'] ?: ($vehicle['tire_size'] ?? '');
                            if (!empty($tireDisplay)): ?>
                                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= htmlspecialchars(memberT('tire_size', $lang)) ?>: <?= htmlspecialchars($tireDisplay) ?>
                                    <?php if (!empty($vehicle['tire_size_rear']) && $vehicle['tire_size_rear'] !== $tireDisplay): ?>
                                        / <?= htmlspecialchars($vehicle['tire_size_rear']) ?> (<?= $lang === 'es' ? 'traseras' : 'rear' ?>)
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($vehicle['mileage'])): ?>
                                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= $lang === 'es' ? 'Kilometraje' : 'Mileage' ?>: <?= number_format((int) $vehicle['mileage']) ?>
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
