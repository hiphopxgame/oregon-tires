<?php
/**
 * GET /api/member/my-vehicles.php
 *
 * Returns customer vehicles for dashboard tab.
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
        echo '<div class="member-alert member-alert--error">Please sign in to view vehicles.</div>';
        exit;
    }

    $memberId = (int) $_SESSION['member_id'];

    // Query vehicles linked to this customer
    $stmt = $pdo->prepare(
        'SELECT id, year, make, model, vin, tire_size, license_plate, created_at
         FROM oretir_vehicles
         WHERE member_id = ?
         ORDER BY created_at DESC'
    );
    $stmt->execute([$memberId]);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1>My Vehicles</h1>
                <p>Vehicles associated with your account</p>
            </div>

            <?php if (empty($vehicles)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    No vehicles on file yet. They'll be added when you book an appointment.
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
                                            License: <?= htmlspecialchars($vehicle['license_plate']) ?>
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
                                    Tire Size: <?= htmlspecialchars($vehicle['tire_size']) ?>
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
    echo '<div class="member-alert member-alert--error">Error loading vehicles. Please try again.</div>';
}
