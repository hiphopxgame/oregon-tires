<?php
/**
 * GET /api/member/my-customers.php
 *
 * Admin-only customer directory tab for the member dashboard.
 * Shows aggregated customer data from appointments, vehicles, ROs, estimates.
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

    // Admin check: member email must match oretir_admins
    $member = MemberAuth::getCurrentMember();
    $adminCheck = $pdo->prepare('SELECT id FROM oretir_admins WHERE email = ? AND role IN (?, ?) LIMIT 1');
    $adminCheck->execute([$member['email'] ?? '', 'admin', 'superadmin']);
    if (!$adminCheck->fetch()) {
        http_response_code(403);
        echo '<div class="member-alert member-alert--error">Access denied.</div>';
        exit;
    }

    // Aggregated customer query
    $stmt = $pdo->query(
        "SELECT c.id, c.first_name, c.last_name, c.email, c.phone, c.language, c.created_at,
                (SELECT COUNT(*) FROM oretir_appointments a WHERE a.customer_id = c.id AND a.status = 'completed') AS visit_count,
                (SELECT MAX(a2.preferred_date) FROM oretir_appointments a2 WHERE a2.customer_id = c.id) AS last_visit,
                (SELECT GROUP_CONCAT(DISTINCT a3.service SEPARATOR ', ')
                 FROM oretir_appointments a3 WHERE a3.customer_id = c.id LIMIT 1) AS services_list,
                (SELECT COUNT(*) FROM oretir_vehicles v WHERE v.customer_id = c.id) AS vehicle_count,
                (SELECT CONCAT(v2.year, ' ', v2.make, ' ', v2.model)
                 FROM oretir_vehicles v2 WHERE v2.customer_id = c.id ORDER BY v2.created_at DESC LIMIT 1) AS primary_vehicle,
                (SELECT COUNT(*) FROM oretir_repair_orders ro
                 WHERE ro.customer_id = c.id AND ro.status NOT IN ('completed', 'invoiced', 'cancelled')) AS active_ro_count
         FROM oretir_customers c
         ORDER BY c.created_at DESC"
    );
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $returningCount = 0;
    foreach ($customers as $cust) {
        if ((int) $cust['visit_count'] > 1) {
            $returningCount++;
        }
    }

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1><?= htmlspecialchars(memberT('customer_directory', $lang)) ?></h1>
                <p><?= htmlspecialchars(memberT('customer_subtitle', $lang)) ?></p>
            </div>

            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center;">
                <input type="text" id="customer-search" placeholder="<?= htmlspecialchars(memberT('search_customers', $lang)) ?>"
                    style="flex: 1; min-width: 200px; padding: 0.5rem 0.75rem; border: 1px solid var(--member-border); border-radius: var(--member-radius); background: var(--member-surface); color: var(--member-text); font-size: 0.875rem;">
                <span style="padding: 0.25rem 0.75rem; background: var(--member-accent); color: var(--member-accent-text); border-radius: 0.25rem; font-size: 0.75rem; white-space: nowrap;">
                    <?= count($customers) ?> <?= htmlspecialchars(mb_strtolower(memberT('customer', $lang))) ?><?= count($customers) !== 1 ? 's' : '' ?>
                </span>
                <?php if ($returningCount > 0): ?>
                <span style="padding: 0.25rem 0.75rem; background: #15803d; color: #fff; border-radius: 0.25rem; font-size: 0.75rem; white-space: nowrap;">
                    <?= $returningCount ?> <?= htmlspecialchars(memberT('returning', $lang)) ?>
                </span>
                <?php endif; ?>
            </div>

            <?php if (empty($customers)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    <?= htmlspecialchars(memberT('no_customers', $lang)) ?>
                </p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--member-border); text-align: left;">
                                <th style="padding: 0.5rem 0.75rem;"><?= htmlspecialchars(memberT('customer', $lang)) ?></th>
                                <th style="padding: 0.5rem 0.75rem;"><?= htmlspecialchars(memberT('contact', $lang)) ?></th>
                                <th style="padding: 0.5rem 0.75rem;"><?= htmlspecialchars(memberT('vehicle', $lang)) ?></th>
                                <th style="padding: 0.5rem 0.75rem; text-align: center;"><?= htmlspecialchars(memberT('visits', $lang)) ?></th>
                                <th style="padding: 0.5rem 0.75rem;"><?= htmlspecialchars(memberT('last_visit', $lang)) ?></th>
                                <th style="padding: 0.5rem 0.75rem; text-align: center;"><?= htmlspecialchars(memberT('vehicles', $lang)) ?></th>
                                <th style="padding: 0.5rem 0.75rem; text-align: center;"><?= htmlspecialchars(memberT('active_ros', $lang)) ?></th>
                            </tr>
                        </thead>
                        <tbody id="customer-tbody">
                            <?php foreach ($customers as $cust):
                                $name = trim(($cust['first_name'] ?? '') . ' ' . ($cust['last_name'] ?? ''));
                                $searchData = mb_strtolower($name . ' ' . ($cust['email'] ?? '') . ' ' . ($cust['phone'] ?? ''));
                            ?>
                            <tr class="customer-row" data-search="<?= htmlspecialchars($searchData) ?>"
                                style="border-bottom: 1px solid var(--member-border); cursor: pointer; transition: background 0.15s;"
                                onmouseover="this.style.background='var(--member-surface-hover)'"
                                onmouseout="this.style.background='transparent'"
                                onclick="toggleCustomerDetail(this)">
                                <td style="padding: 0.5rem 0.75rem;">
                                    <div style="font-weight: 600;"><?= htmlspecialchars($name ?: '—') ?></div>
                                    <?php if ((int) $cust['visit_count'] > 1): ?>
                                        <span style="font-size: 0.7rem; padding: 0.1rem 0.4rem; background: #15803d; color: #fff; border-radius: 0.2rem;"><?= htmlspecialchars(memberT('returning', $lang)) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.5rem 0.75rem;">
                                    <div style="font-size: 0.8rem;"><?= htmlspecialchars($cust['email'] ?? '—') ?></div>
                                    <?php if (!empty($cust['phone'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--member-text-muted);"><?= htmlspecialchars($cust['phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.5rem 0.75rem; font-size: 0.8rem;">
                                    <?= htmlspecialchars($cust['primary_vehicle'] ?? '—') ?>
                                </td>
                                <td style="padding: 0.5rem 0.75rem; text-align: center;">
                                    <?= (int) $cust['visit_count'] ?>
                                </td>
                                <td style="padding: 0.5rem 0.75rem; font-size: 0.8rem;">
                                    <?= $cust['last_visit'] ? htmlspecialchars(date('M d, Y', strtotime($cust['last_visit']))) : htmlspecialchars(memberT('never', $lang)) ?>
                                </td>
                                <td style="padding: 0.5rem 0.75rem; text-align: center;">
                                    <?= (int) $cust['vehicle_count'] ?>
                                </td>
                                <td style="padding: 0.5rem 0.75rem; text-align: center;">
                                    <?php if ((int) $cust['active_ro_count'] > 0): ?>
                                        <span style="padding: 0.1rem 0.5rem; background: #d97706; color: #fff; border-radius: 0.2rem; font-size: 0.75rem;">
                                            <?= (int) $cust['active_ro_count'] ?>
                                        </span>
                                    <?php else: ?>
                                        0
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr class="customer-detail" style="display: none;">
                                <td colspan="7" style="padding: 0.75rem 1rem; background: var(--member-surface-hover); border-bottom: 1px solid var(--member-border);">
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; font-size: 0.8rem;">
                                        <div>
                                            <strong><?= htmlspecialchars(memberT('email', $lang)) ?>:</strong><br>
                                            <?= htmlspecialchars($cust['email'] ?? '—') ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars(memberT('phone', $lang)) ?>:</strong><br>
                                            <?= htmlspecialchars($cust['phone'] ?? '—') ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars(memberT('language_pref', $lang)) ?>:</strong><br>
                                            <?= ($cust['language'] ?? 'en') === 'es' ? 'Español' : 'English' ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars(memberT('first_visit', $lang)) ?>:</strong><br>
                                            <?= htmlspecialchars(date('M d, Y', strtotime($cust['created_at']))) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars(memberT('services', $lang)) ?>:</strong><br>
                                            <?= htmlspecialchars($cust['services_list'] ?? '—') ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars(memberT('vehicles', $lang)) ?>:</strong><br>
                                            <?= (int) $cust['vehicle_count'] ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <script>
                function toggleCustomerDetail(row) {
                    var detail = row.nextElementSibling;
                    if (detail && detail.classList.contains('customer-detail')) {
                        detail.style.display = detail.style.display === 'none' ? 'table-row' : 'none';
                    }
                }

                document.getElementById('customer-search').addEventListener('input', function() {
                    var q = this.value.toLowerCase();
                    var rows = document.querySelectorAll('.customer-row');
                    rows.forEach(function(row) {
                        var data = row.getAttribute('data-search') || '';
                        var match = !q || data.indexOf(q) !== -1;
                        row.style.display = match ? '' : 'none';
                        var detail = row.nextElementSibling;
                        if (detail && detail.classList.contains('customer-detail')) {
                            if (!match) detail.style.display = 'none';
                        }
                    });
                });
                </script>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (\Throwable $e) {
    error_log("Oregon Tires member/my-customers error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
