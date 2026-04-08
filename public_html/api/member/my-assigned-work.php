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
               c.first_name, c.last_name, c.phone,
               v.year, v.make, v.model
        FROM oretir_repair_orders ro
        LEFT JOIN oretir_customers c ON ro.customer_id = c.id
        LEFT JOIN oretir_vehicles v ON ro.vehicle_id = v.id
        WHERE ro.assigned_employee_id = ?
          AND ro.status NOT IN ('completed', 'invoiced', 'cancelled')
        ORDER BY FIELD(ro.status, 'in_progress', 'diagnosis', 'intake', 'on_hold', 'waiting_parts', 'ready', 'approved', 'pending_approval', 'estimate_pending') ASC,
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
                'on_hold'          => '#ef4444',
                'waiting_parts'    => '#f59e0b',
                'ready'            => '#10b981',
            ];
            $color = $statusColors[$ro['status']] ?? '#64748b';
            $statusLabel = ucwords(str_replace('_', ' ', $ro['status']));
            $customer = trim(($ro['first_name'] ?? '') . ' ' . ($ro['last_name'] ?? ''));
            $vehicle  = trim(($ro['year'] ?? '') . ' ' . ($ro['make'] ?? '') . ' ' . ($ro['model'] ?? ''));
            $dateStr  = date('M j', strtotime($ro['created_at']));
            $phoneRaw = preg_replace('/[^0-9+]/', '', (string) ($ro['phone'] ?? ''));
            $roNum    = (string) ($ro['ro_number'] ?? '');

            // Worker pipeline: visible transitions per current status
            $transitions = [
                'intake'        => ['diagnosis' => 'Start Diagnosis', 'in_progress' => 'Start Work'],
                'check_in'      => ['diagnosis' => 'Start Diagnosis', 'in_progress' => 'Start Work'],
                'diagnosis'     => ['estimate_pending' => 'Send to Estimate', 'in_progress' => 'Start Work'],
                'approved'      => ['in_progress' => 'Start Work'],
                'in_progress'   => ['ready' => 'Mark Ready', 'on_hold' => 'On Hold', 'waiting_parts' => 'Waiting Parts'],
                'on_hold'       => ['in_progress' => 'Resume'],
                'waiting_parts' => ['in_progress' => 'Resume'],
            ];
            $nextActions = $transitions[$ro['status']] ?? [];

            echo '<div data-test="ro-card" data-ro-number="' . htmlspecialchars($roNum) . '" data-ro-status="' . htmlspecialchars($ro['status']) . '" style="padding:1rem;border-radius:var(--member-radius);background:var(--member-surface);border:1px solid var(--member-border);">';
            echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;flex-wrap:wrap;gap:0.5rem;">';
            echo '<span style="font-weight:700;font-family:monospace;">' . htmlspecialchars($roNum) . '</span>';
            echo '<span data-ro-status-pill style="display:inline-block;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;color:#fff;background:' . $color . ';">' . htmlspecialchars($statusLabel) . '</span>';
            echo '</div>';
            if ($customer) {
                echo '<div style="color:var(--member-text);">' . htmlspecialchars($customer) . '</div>';
            }
            if ($vehicle) {
                echo '<div style="color:var(--member-text-muted);font-size:0.875rem;">' . htmlspecialchars($vehicle) . '</div>';
            }
            echo '<div style="color:var(--member-text-muted);font-size:0.75rem;margin-top:0.25rem;">' . htmlspecialchars($dateStr) . '</div>';

            // tap-to-call / tap-to-text
            if ($phoneRaw !== '') {
                echo '<div data-test="ro-contact" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;">';
                echo '<a href="tel:' . htmlspecialchars($phoneRaw) . '" aria-label="Call customer" style="flex:1;min-width:120px;text-align:center;padding:0.75rem;background:#10b981;color:#fff;border-radius:var(--member-radius);text-decoration:none;font-weight:600;font-size:0.85rem;min-height:44px;display:inline-flex;align-items:center;justify-content:center;">&#128222; Call</a>';
                echo '<a href="sms:' . htmlspecialchars($phoneRaw) . '" aria-label="Text customer" style="flex:1;min-width:120px;text-align:center;padding:0.75rem;background:#3b82f6;color:#fff;border-radius:var(--member-radius);text-decoration:none;font-weight:600;font-size:0.85rem;min-height:44px;display:inline-flex;align-items:center;justify-content:center;">&#128172; Text</a>';
                echo '</div>';
            }

            // Status pipeline + transition buttons
            if (!empty($nextActions)) {
                echo '<div data-test="status-pipeline" style="margin-top:0.75rem;display:flex;flex-direction:column;gap:0.5rem;">';
                echo '<div style="font-size:0.7rem;font-weight:600;color:var(--member-text-muted);text-transform:uppercase;letter-spacing:0.05em;">Next Steps</div>';
                echo '<div style="display:flex;gap:0.5rem;flex-wrap:wrap;">';
                foreach ($nextActions as $toStatus => $label) {
                    echo '<button type="button" data-ro-action="status" data-to-status="' . htmlspecialchars($toStatus) . '" aria-label="' . htmlspecialchars($label) . '" style="flex:1;min-width:140px;min-height:56px;padding:0.75rem 1rem;background:var(--member-accent,#047857);color:#fff;border:none;border-radius:var(--member-radius);font-weight:600;font-size:0.85rem;cursor:pointer;">' . htmlspecialchars($label) . '</button>';
                }
                echo '</div>';
                echo '</div>';
            }

            // Note form
            echo '<form data-test="ro-note-form" data-ro-action="note" style="margin-top:0.75rem;display:flex;flex-direction:column;gap:0.5rem;">';
            echo '<label style="font-size:0.7rem;font-weight:600;color:var(--member-text-muted);text-transform:uppercase;letter-spacing:0.05em;">Add Tech Note</label>';
            echo '<textarea data-ro-note rows="2" maxlength="2000" placeholder="What did you find?" aria-label="Technician note" style="width:100%;padding:0.5rem;border:1px solid var(--member-border);border-radius:var(--member-radius);background:var(--member-surface);color:var(--member-text);font-size:0.85rem;resize:vertical;box-sizing:border-box;min-height:60px;"></textarea>';
            echo '<button type="submit" aria-label="Add note" style="min-height:44px;padding:0.5rem 1rem;background:#374151;color:#fff;border:none;border-radius:var(--member-radius);font-weight:600;font-size:0.85rem;cursor:pointer;">Add Note</button>';
            echo '</form>';

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
