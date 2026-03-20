<?php
/**
 * sso-handler.php — SSO callback handler for Oregon Tires
 *
 * Validates SSO token, creates session, redirects to destination.
 *
 * Usage (from sso.php):
 *   require_once __DIR__ . '/includes/sso-handler.php';
 *   handleSSOCallback('https://oregon.tires');
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/member-kit-init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Handle SSO callback: validate token, create session, redirect.
 *
 * @param string $defaultReturnUrl Where to redirect if no valid ?return= param
 */
function handleSSOCallback(string $defaultReturnUrl): void {
    if (!MEMBER_KIT_PATH || !class_exists('MemberAuth')) {
        http_response_code(500);
        die('SSO is not configured');
    }

    // Delegate to member-kit's SSO callback endpoint if it exists
    $ssoCallbackPath = MEMBER_KIT_PATH . '/endpoints/sso-callback.php';
    if (file_exists($ssoCallbackPath)) {
        $pdo = getDB();
        require $ssoCallbackPath;
        return;
    }

    // Fallback: basic token validation
    $token = $_GET['token'] ?? '';
    $returnUrl = $_GET['return'] ?? $defaultReturnUrl;
    // Prevent open redirect — only allow relative paths or same-domain URLs
    if ($returnUrl && !preg_match('#^/[^/]#', $returnUrl) && !str_starts_with($returnUrl, 'https://oregon.tires')) {
        $returnUrl = $defaultReturnUrl;
    }

    if (empty($token)) {
        header('Location: /login');
        exit;
    }

    // Validate via DB
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM sso_tokens WHERE token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || strtotime($row['expires_at']) < time()) {
            header('Location: /login?error=token_expired');
            exit;
        }

        // Delete used token (single-use)
        $pdo->prepare("DELETE FROM sso_tokens WHERE token = ?")->execute([$token]);

        // Load user and start session
        $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ? AND disabled_at IS NULL");
        $stmt->execute([$row['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: /login?error=user_not_found');
            exit;
        }

        // Start authenticated session via MemberAuth
        if (method_exists('MemberAuth', 'startAuthenticatedSession')) {
            MemberAuth::startAuthenticatedSession($user);
        } else {
            $_SESSION['member_id'] = $user['id'];
        }

        session_write_close();
    } catch (\Throwable $e) {
        error_log('SSO callback error: ' . $e->getMessage());
        header('Location: /login?error=sso_failed');
        exit;
    }

    // Redirect with HTML bounce (for cookie reliability)
    $dest = $returnUrl ?: $defaultReturnUrl;
    $destSafe = htmlspecialchars($dest, ENT_QUOTES, 'UTF-8');
    $destJs = json_encode($dest);

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">'
        . '<meta http-equiv="refresh" content="0;url=' . $destSafe . '">'
        . '<title>Signing in…</title></head>'
        . '<body style="background:#f8faf8;color:#222;display:flex;align-items:center;'
        . 'justify-content:center;min-height:100vh;font-family:system-ui,sans-serif">'
        . '<p>Signing you in…</p>'
        . '<script>window.location.replace(' . $destJs . ');</script>'
        . '</body></html>';
    exit;
}
