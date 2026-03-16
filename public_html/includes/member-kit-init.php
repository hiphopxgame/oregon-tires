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

    // Stub translation function expected by member-kit templates
    if (!function_exists('t')) {
        function t(string $key): ?string { return null; }
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

        // Cross-DB: check if this user is a HW super admin
        $email = $member['email'] ?? '';
        if ($email !== '') {
            try {
                $hwDb = $_ENV['HW_DB_NAME'] ?? 'hiphopwo_rld_system';
                $stmt = $pdo->prepare("SELECT 1 FROM {$hwDb}.users WHERE email = ? AND is_admin = 1 AND disabled_at IS NULL LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    // Sync is_admin flag to local members table
                    $pdo->prepare('UPDATE members SET is_admin = 1 WHERE id = ?')
                        ->execute([$member['id']]);

                    // Set member-kit super admin session
                    $_SESSION['is_super_admin'] = true;

                    // Bridge: set Oregon Tires admin session keys so /admin/ works
                    $_SESSION['admin_id']       = (int) $member['id'];
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
