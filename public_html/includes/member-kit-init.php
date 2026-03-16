<?php
/**
 * Oregon Tires — Member Kit Initialization
 * Initializes customer auth via shared Member Kit.
 */

declare(strict_types=1);

function initMemberKit(PDO $pdo): void
{
    static $initialized = false;
    if ($initialized) return;

    $path = $_ENV['MEMBER_KIT_PATH'] ?? null;
    if (!$path || !file_exists($path . '/loader.php')) {
        // Define constant even when path is invalid so downstream code
        // can check it rather than fatal on an undefined constant.
        if (!defined('MEMBER_KIT_PATH')) {
            define('MEMBER_KIT_PATH', $path ?? '');
        }
        return;
    }

    if (!defined('MEMBER_KIT_PATH')) {
        define('MEMBER_KIT_PATH', $path);
    }

    require_once $path . '/loader.php';

    MemberAuth::init($pdo, [
        'mode'           => 'independent',
        'members_table'  => 'members',
        'session_key'    => 'member_id',
        'login_url'      => '/members',
        'site_url'       => $_ENV['APP_URL'] ?? 'https://oregon.tires',
        'site_name'      => 'Oregon Tires Auto Care',
        'session_name'   => 'oregon_session',
        'site_key'       => 'oregon_tires',
    ]);

    // Ensure CSRF token exists (session may already be started by startSecureSession())
    if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    MemberAuth::onLogin(function (array $member) use ($pdo): void {
        $_SESSION['member_email'] = $member['email'] ?? '';
        $_SESSION['is_customer']  = true;

        $email    = $member['email'] ?? '';
        $memberId = (int) $member['id'];

        // ── Detect role: admin > employee > member ──────────────────────
        $detectedRole = 'member';

        // 1. Check local oretir_admins table
        if ($email !== '') {
            try {
                $stmt = $pdo->prepare('SELECT id, role, display_name, language FROM oretir_admins WHERE email = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$email]);
                $localAdmin = $stmt->fetch();
                if ($localAdmin) {
                    $detectedRole = 'admin';
                    $_SESSION['admin_id']       = $localAdmin['id'];
                    $_SESSION['admin_email']    = $email;
                    $_SESSION['admin_role']     = $localAdmin['role'] ?? 'admin';
                    $_SESSION['admin_name']     = $localAdmin['display_name'] ?? $member['display_name'] ?? $email;
                    $_SESSION['admin_language'] = $localAdmin['language'] ?? 'both';
                    $_SESSION['login_time']     = time();
                }
            } catch (\Throwable $e) {
                error_log('Local admin check failed: ' . $e->getMessage());
            }
        }

        // 2. Cross-DB: HW super admin overrides local admin
        if ($email !== '') {
            try {
                $hwDb = $_ENV['HW_DB_NAME'] ?? 'hiphopwo_rld_system';
                $stmt = $pdo->prepare("SELECT 1 FROM {$hwDb}.users WHERE email = ? AND is_admin = 1 AND disabled_at IS NULL LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $detectedRole = 'admin';
                    $_SESSION['is_super_admin'] = true;
                    $_SESSION['admin_id']       = $memberId;
                    $_SESSION['admin_email']    = $email;
                    $_SESSION['admin_role']     = 'super_admin';
                    $_SESSION['admin_name']     = $member['display_name'] ?? $member['username'] ?? $email;
                    $_SESSION['admin_language'] = 'both';
                    $_SESSION['login_time']     = time();
                }
            } catch (\Throwable $e) {
                error_log('HW super admin check failed: ' . $e->getMessage());
            }
        }

        // 3. Check oretir_employees (only if not already admin)
        if ($detectedRole !== 'admin') {
            try {
                $stmt = $pdo->prepare('SELECT id, name, role FROM oretir_employees WHERE member_id = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$memberId]);
                $employee = $stmt->fetch();
                if (!$employee && $email !== '') {
                    // Fallback: match by email if member_id not linked yet
                    $stmt = $pdo->prepare('SELECT id, name, role FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
                    $stmt->execute([$email]);
                    $employee = $stmt->fetch();
                    // Auto-link employee to member account
                    if ($employee) {
                        $pdo->prepare('UPDATE oretir_employees SET member_id = ? WHERE id = ?')
                            ->execute([$memberId, $employee['id']]);
                    }
                }
                if ($employee) {
                    $detectedRole = 'employee';
                    $_SESSION['employee_id']   = (int) $employee['id'];
                    $_SESSION['employee_name'] = $employee['name'];
                    $_SESSION['employee_role'] = $employee['role']; // Employee or Manager
                }
            } catch (\Throwable $e) {
                error_log('Employee check failed: ' . $e->getMessage());
            }
        }

        // Persist role to members table + session
        $_SESSION['dashboard_role'] = $detectedRole;
        try {
            $pdo->prepare('UPDATE members SET role = ?, is_admin = ? WHERE id = ?')
                ->execute([$detectedRole, $detectedRole === 'admin' ? 1 : 0, $memberId]);
        } catch (\Throwable $e) {
            // role column may not exist yet (pre-migration)
            if ($detectedRole === 'admin') {
                $pdo->prepare('UPDATE members SET is_admin = 1 WHERE id = ?')
                    ->execute([$memberId]);
            }
        }

        // Cross-site activity reporting (fire-and-forget)
        if (!empty($member['hw_user_id']) && class_exists('MemberSync')) {
            MemberSync::reportActivity(
                (int) $member['hw_user_id'],
                'oregon.tires',
                'login',
                null
            );
        }
    });

    $initialized = true;
}
