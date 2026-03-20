<?php
/**
 * GET /api/member/my-invoices.php
 *
 * Returns invoices for the logged-in member's customer record.
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

    // Fetch member email for fallback matching
    $memberStmt = $pdo->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $memberStmt->execute([$memberId]);
    $memberEmail = $memberStmt->fetchColumn();

    // Get invoices for this customer (member_id OR email fallback)
    $stmt = $pdo->prepare(
        'SELECT inv.id, inv.invoice_number, inv.total, inv.status, inv.payment_method,
                inv.paid_at, inv.due_date, inv.customer_view_token, inv.created_at,
                r.ro_number,
                v.year as vehicle_year, v.make as vehicle_make, v.model as vehicle_model
         FROM oretir_invoices inv
         JOIN oretir_repair_orders r ON r.id = inv.repair_order_id
         JOIN oretir_customers c ON c.id = inv.customer_id
         LEFT JOIN oretir_vehicles v ON v.id = r.vehicle_id
         WHERE (c.member_id = ? OR c.email = ?)
         ORDER BY inv.created_at DESC'
    );
    $stmt->execute([$memberId, $memberEmail]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status labels for bilingual display
    $statusLabels = [
        'en' => ['draft' => 'Draft', 'sent' => 'Sent', 'viewed' => 'Viewed', 'paid' => 'Paid', 'overdue' => 'Overdue', 'void' => 'Void'],
        'es' => ['draft' => 'Borrador', 'sent' => 'Enviada', 'viewed' => 'Vista', 'paid' => 'Pagada', 'overdue' => 'Vencida', 'void' => 'Anulada'],
    ];
    $statusColors = [
        'draft' => 'var(--member-text-muted)',
        'sent' => '#3b82f6',
        'viewed' => '#eab308',
        'paid' => '#22c55e',
        'overdue' => '#ef4444',
        'void' => 'var(--member-text-muted)',
    ];

    ?>
    <div class="member-page">
        <div class="member-card member-card--wide">
            <div class="member-header">
                <h1><?= htmlspecialchars(memberT('invoices', $lang, 'Invoices')) ?></h1>
                <p><?= htmlspecialchars(memberT('invoices_subtitle', $lang, $lang === 'es' ? 'Sus facturas y recibos' : 'Your invoices and receipts')) ?></p>
            </div>

            <?php if (empty($invoices)): ?>
                <p class="member-text-muted" style="text-align: center; padding: 2rem 0;">
                    <?= htmlspecialchars($lang === 'es' ? 'No tiene facturas todavia.' : 'No invoices yet.') ?>
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($invoices as $inv): ?>
                        <?php
                            $vehicle = trim(($inv['vehicle_year'] ?? '') . ' ' . ($inv['vehicle_make'] ?? '') . ' ' . ($inv['vehicle_model'] ?? ''));
                            $statusLabel = $statusLabels[$lang][$inv['status']] ?? ucfirst($inv['status']);
                            $statusColor = $statusColors[$inv['status']] ?? 'var(--member-text-muted)';
                        ?>
                        <div style="padding: 1rem; background: var(--member-surface-hover); border-radius: var(--member-radius); border-left: 3px solid var(--member-accent);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem; font-size: 0.95rem;">
                                        <?= htmlspecialchars($lang === 'es' ? 'Factura' : 'Invoice') ?> <?= htmlspecialchars($inv['invoice_number']) ?>
                                    </h3>
                                    <p style="margin: 0; color: var(--member-text-muted); font-size: 0.875rem;">
                                        RO: <?= htmlspecialchars($inv['ro_number']) ?>
                                        <?php if ($vehicle): ?>
                                            &mdash; <?= htmlspecialchars($vehicle) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span style="padding: 0.25rem 0.75rem; background: <?= $statusColor ?>22; color: <?= $statusColor ?>; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                    <?= htmlspecialchars($statusLabel) ?>
                                </span>
                            </div>
                            <div style="margin-top: 0.5rem;">
                                <p style="margin: 0; font-size: 0.875rem;">
                                    <?= htmlspecialchars($lang === 'es' ? 'Total' : 'Total') ?>: <strong>$<?= number_format((float) ($inv['total'] ?? 0), 2) ?></strong>
                                </p>
                                <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--member-text-muted);">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($inv['created_at']))) ?>
                                </p>
                                <?php if (!empty($inv['customer_view_token'])): ?>
                                <p style="margin: 0.5rem 0 0;">
                                    <a href="/invoice/<?= htmlspecialchars($inv['customer_view_token']) ?>" style="color: var(--member-accent); text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                                        <?= htmlspecialchars($lang === 'es' ? 'Ver Factura' : 'View Invoice') ?> &rarr;
                                    </a>
                                </p>
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
    error_log("Oregon Tires member/my-invoices error: " . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
