<?php
/**
 * GET /api/member/my-assigned-work.php
 *
 * Returns repair orders assigned to the logged-in employee as HTML.
 * Shows active ROs (not completed/invoiced/cancelled).
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

    $employeeId = $_SESSION['employee_id'] ?? null;
    if (!$employeeId) {
        $email = $_SESSION['member_email'] ?? '';
        $stmt = $pdo->prepare('SELECT id FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $emp = $stmt->fetch();
        $employeeId = $emp ? (int) $emp['id'] : null;
    }

    if (!$employeeId) {
        echo '<div class="member-alert member-alert--warning">' . htmlspecialchars(memberT('no_assigned_work', $lang)) . '</div>';
        exit;
    }

    // Fetch active ROs assigned to this employee
    $stmt = $pdo->prepare("
        SELECT ro.ro_number, ro.status, ro.created_at,
               c.first_name, c.last_name,
               v.year, v.make, v.model
        FROM oretir_repair_orders ro
        LEFT JOIN oretir_customers c ON ro.customer_id = c.id
        LEFT JOIN oretir_vehicles v ON ro.vehicle_id = v.id
        WHERE ro.assigned_employee_id = ?
          AND ro.status NOT IN ('completed', 'invoiced', 'cancelled')
        ORDER BY FIELD(ro.status, 'in_progress', 'diagnosis', 'intake', 'waiting_parts', 'ready', 'approved', 'pending_approval', 'estimate_pending') ASC,
                 ro.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$employeeId]);
    $orders = $stmt->fetchAll();

    header('Content-Type: text/html; charset=utf-8');

    echo '<div class="member-tab-content">';
    echo '<h3 class="member-tab-title">' . htmlspecialchars(memberT('assigned_work', $lang)) . '</h3>';
    echo '<p class="member-tab-subtitle" style="color:var(--member-text-muted);margin-bottom:1rem;">' . htmlspecialchars(memberT('assigned_subtitle', $lang)) . '</p>';

    if (empty($orders)) {
        echo '<div class="member-alert member-alert--info">' . htmlspecialchars(memberT('no_assigned_work', $lang)) . '</div>';
    } else {
        echo '<div style="display:grid;gap:0.75rem;">';
        foreach ($orders as $ro) {
            $statusColors = [
                'intake'           => '#6366f1',
                'diagnosis'        => '#8b5cf6',
                'estimate_pending' => '#f59e0b',
                'pending_approval' => '#f97316',
                'approved'         => '#22c55e',
                'in_progress'      => '#3b82f6',
                'waiting_parts'    => '#ef4444',
                'ready'            => '#10b981',
            ];
            $color = $statusColors[$ro['status']] ?? '#64748b';
            $statusLabel = ucwords(str_replace('_', ' ', $ro['status']));
            $customer = trim(($ro['first_name'] ?? '') . ' ' . ($ro['last_name'] ?? ''));
            $vehicle  = trim(($ro['year'] ?? '') . ' ' . ($ro['make'] ?? '') . ' ' . ($ro['model'] ?? ''));
            $dateStr  = date('M j', strtotime($ro['created_at']));

            echo '<div style="padding:1rem;border-radius:var(--member-radius);background:var(--member-surface);border:1px solid var(--member-border);">';
            echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">';
            echo '<span style="font-weight:700;font-family:monospace;">' . htmlspecialchars($ro['ro_number'] ?? '') . '</span>';
            echo '<span style="display:inline-block;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;color:#fff;background:' . $color . ';">' . htmlspecialchars($statusLabel) . '</span>';
            echo '</div>';
            if ($customer) {
                echo '<div style="color:var(--member-text);">' . htmlspecialchars($customer) . '</div>';
            }
            if ($vehicle) {
                echo '<div style="color:var(--member-text-muted);font-size:0.875rem;">' . htmlspecialchars($vehicle) . '</div>';
            }
            echo '<div style="color:var(--member-text-muted);font-size:0.75rem;margin-top:0.25rem;">' . htmlspecialchars($dateStr) . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    echo '</div>';

} catch (\Throwable $e) {
    error_log('my-assigned-work.php error: ' . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
