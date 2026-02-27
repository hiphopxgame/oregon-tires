<?php
declare(strict_types=1);

/**
 * Oregon Tires — Google OAuth unlink wrapper
 * Bootstraps the site, then delegates to the shared member-kit endpoint.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/member-kit-init.php';
startSecureSession();
$pdo = getDB();
initMemberKit($pdo);

require_once MEMBER_KIT_PATH . '/api/member/google-unlink.php';
