<?php
/**
 * GET /api/member/my-schedule.php
 *
 * Returns employee's weekly schedule as HTML for dashboard tab.
 * Requires employee role (linked via oretir_employees.member_id or email).
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

    // Find employee record
    $employeeId = $_SESSION['employee_id'] ?? null;
    if (!$employeeId) {
        // Fallback: look up by email
        $email = $_SESSION['member_email'] ?? '';
        $stmt = $pdo->prepare('SELECT id FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $emp = $stmt->fetch();
        $employeeId = $emp ? (int) $emp['id'] : null;
    }

    if (!$employeeId) {
        echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('no_schedule', $lang)) . '</div>';
        exit;
    }

    // Fetch weekly schedule
    $stmt = $pdo->prepare('SELECT day_of_week, start_time, end_time, is_available FROM oretir_schedules WHERE employee_id = ? ORDER BY day_of_week');
    $stmt->execute([$employeeId]);
    $schedule = $stmt->fetchAll();

    // Fetch upcoming overrides (next 14 days)
    $stmt = $pdo->prepare('SELECT override_date, is_closed, start_time, end_time, reason FROM oretir_schedule_overrides WHERE employee_id = ? AND override_date >= CURDATE() AND override_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY) ORDER BY override_date');
    $stmt->execute([$employeeId]);
    $overrides = $stmt->fetchAll();

    $dayNames = [
        0 => memberT('day_sunday', $lang),
        1 => memberT('day_monday', $lang),
        2 => memberT('day_tuesday', $lang),
        3 => memberT('day_wednesday', $lang),
        4 => memberT('day_thursday', $lang),
        5 => memberT('day_friday', $lang),
        6 => memberT('day_saturday', $lang),
    ];

    $today = (int) date('w'); // 0=Sun

    header('Content-Type: text/html; charset=utf-8');

    echo '<div class="member-tab-content">';
    echo '<h3 class="member-tab-title">' . htmlspecialchars(memberT('my_schedule', $lang)) . '</h3>';
    echo '<p class="member-tab-subtitle" style="color:var(--member-text-muted);margin-bottom:1rem;">' . htmlspecialchars(memberT('schedule_subtitle', $lang)) . '</p>';

    if (empty($schedule)) {
        echo '<div class="member-alert member-alert--warning">' . htmlspecialchars(memberT('no_schedule', $lang)) . '</div>';
    } else {
        echo '<div style="display:grid;gap:0.5rem;max-width:100%;">';
        foreach ($schedule as $row) {
            $dow = (int) $row['day_of_week'];
            $isToday = $dow === $today;
            $bg = $isToday ? 'var(--member-accent)' : 'var(--member-surface)';
            $color = $isToday ? 'var(--member-accent-text)' : 'var(--member-text)';

            echo '<div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;border-radius:var(--member-radius);background:' . $bg . ';color:' . $color . ';border:1px solid var(--member-border);">';
            echo '<span style="font-weight:600;">' . htmlspecialchars($dayNames[$dow] ?? '') . '</span>';

            if ($row['is_available']) {
                $start = date('g:i A', strtotime($row['start_time']));
                $end   = date('g:i A', strtotime($row['end_time']));
                echo '<span>' . htmlspecialchars($start . ' - ' . $end) . '</span>';
            } else {
                echo '<span style="opacity:0.6;">' . htmlspecialchars(memberT('off', $lang)) . '</span>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    // Show upcoming overrides
    if (!empty($overrides)) {
        echo '<h4 style="margin-top:1.5rem;margin-bottom:0.75rem;">' . htmlspecialchars(memberT('today_override', $lang)) . '</h4>';
        echo '<div style="display:grid;gap:0.5rem;max-width:100%;">';
        foreach ($overrides as $ov) {
            $dateStr = date('M j, Y', strtotime($ov['override_date']));
            $bgOv = 'var(--member-surface)';
            echo '<div style="padding:0.75rem 1rem;border-radius:var(--member-radius);background:' . $bgOv . ';border:1px solid var(--member-border);">';
            echo '<div style="display:flex;justify-content:space-between;align-items:center;">';
            echo '<span style="font-weight:600;">' . htmlspecialchars($dateStr) . '</span>';
            if ($ov['is_closed']) {
                echo '<span style="color:var(--member-error);font-weight:600;">' . htmlspecialchars(memberT('off', $lang)) . '</span>';
            } else {
                $start = date('g:i A', strtotime($ov['start_time']));
                $end   = date('g:i A', strtotime($ov['end_time']));
                echo '<span>' . htmlspecialchars($start . ' - ' . $end) . '</span>';
            }
            echo '</div>';
            if (!empty($ov['reason'])) {
                echo '<div style="color:var(--member-text-muted);font-size:0.875rem;margin-top:0.25rem;">' . htmlspecialchars($ov['reason']) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    echo '</div>';

} catch (\Throwable $e) {
    error_log('my-schedule.php error: ' . $e->getMessage());
    http_response_code(500);
    echo '<div class="member-alert member-alert--error">' . htmlspecialchars(memberT('error_loading', $lang)) . '</div>';
}
