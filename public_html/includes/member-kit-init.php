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
    if (!$path || !file_exists($path . '/loader.php')) return;
    require_once $path . '/loader.php';

    MemberAuth::init($pdo, [
        'mode'           => 'independent',
        'members_table'  => 'members',
        'session_key'    => 'member_id',
        'login_url'      => '/customer/login',
        'site_name'      => 'Oregon Tires Auto Care',
        'session_name'   => 'oregon_session',
    ]);

    MemberAuth::onLogin(function (array $member): void {
        $_SESSION['member_email'] = $member['email'] ?? '';
        $_SESSION['is_customer']  = true;

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
