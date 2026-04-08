<?php
/**
 * SSO Callback Endpoint -- Shared Network SSO Handler
 *
 * Each network site includes this at /sso.php (or routes to it).
 * Validates a single-use SSO token, creates a session, and redirects.
 *
 * Flow:
 *   1. Read ?token= and ?return= params
 *   2. Validate token against sso_tokens table (single-use, delete after validation)
 *   3. Load user from users table
 *   4. Call MemberAuth::startAuthenticatedSession (sets session + loads site role)
 *   5. Show branded transition screen (200ms), then redirect to return URL
 *
 * Security:
 *   - Return URL validated as relative path only (no open redirects)
 *   - Token deleted immediately after use (single-use)
 *   - Session regenerated on login
 *
 * Usage from site's /sso.php:
 *   <?php
 *   require_once __DIR__ . '/config.php';
 *   require_once __DIR__ . '/includes/auth.php';
 *   initMemberAuth();
 *   require MEMBER_KIT_PATH . '/endpoints/sso-callback.php';
 */

declare(strict_types=1);

// Ensure MemberAuth is initialized
if (!class_exists('MemberAuth') || !MemberAuth::getConfig('mode')) {
    http_response_code(500);
    echo 'SSO endpoint not configured';
    exit;
}

$pdo = MemberAuth::getPdo();
$token = trim($_GET['token'] ?? '');
$returnUrl = $_GET['return'] ?? '/';

// Validate return URL -- must be a relative path (no open redirects)
if (!str_starts_with($returnUrl, '/') || str_starts_with($returnUrl, '//')) {
    $returnUrl = '/';
}

$siteName = MemberAuth::getConfig('site_name') ?: 'Site';

if (empty($token)) {
    renderSSOTransition($siteName, '', 'missing_token');
}

try {
    // Validate token from sso_tokens table
    $stmt = $pdo->prepare(
        "SELECT user_id FROM sso_tokens WHERE token = ? AND expires_at > NOW()"
    );
    $stmt->execute([$token]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        // Check if token exists but expired
        $expCheck = $pdo->prepare("SELECT expires_at FROM sso_tokens WHERE token = ?");
        $expCheck->execute([$token]);
        $expRow = $expCheck->fetch();

        if ($expRow) {
            $pdo->prepare("DELETE FROM sso_tokens WHERE token = ?")->execute([$token]);
            renderSSOTransition($siteName, $returnUrl, 'expired');
        }
        renderSSOTransition($siteName, $returnUrl, 'invalid');
    }

    $userId = (int) $row['user_id'];

    // Delete token immediately -- single-use only
    $pdo->prepare("DELETE FROM sso_tokens WHERE token = ?")->execute([$token]);

    // Log token exchange (best-effort)
    try {
        $domain = $_SERVER['HTTP_HOST'] ?? 'unknown';
        $pdo->prepare(
            "INSERT INTO engine_sso_audit (user_id, domain, event_type, ip_address, user_agent)
             VALUES (?, ?, 'token_exchanged', ?, ?)"
        )->execute([
            $userId,
            $domain,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
        ]);
    } catch (\Throwable) {
        // Audit logging should never break the flow
    }

    // Load user from shared users table
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND disabled_at IS NULL");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

    if (!$user) {
        renderSSOTransition($siteName, $returnUrl, 'not_found');
    }

    // Start authenticated session (sets session vars + loads site role)
    MemberAuth::startAuthenticatedSession($user);

    // Show branded transition screen
    renderSSOTransition($siteName, $returnUrl, 'success');

} catch (\Throwable $e) {
    error_log('[SSO Callback] Error: ' . $e->getMessage());
    renderSSOTransition($siteName, $returnUrl, 'error');
}

/**
 * Render a branded SSO transition screen.
 * On success: brief "Connecting to..." animation then redirect.
 * On error: appropriate error message with retry/login options.
 */
function renderSSOTransition(string $siteName, string $returnUrl, string $status): never
{
    $isSuccess = $status === 'success';

    $messages = [
        'success'       => ['title' => 'Connecting...', 'msg' => "Connecting to {$siteName}..."],
        'missing_token' => ['title' => 'Authentication Required', 'msg' => 'No authentication token provided.'],
        'expired'       => ['title' => 'Session Expired', 'msg' => 'Your sign-in link has expired. Please try again.'],
        'invalid'       => ['title' => 'Invalid Token', 'msg' => 'The authentication token is invalid or already used.'],
        'not_found'     => ['title' => 'Account Not Found', 'msg' => 'Your account could not be found or is disabled.'],
        'error'         => ['title' => 'Connection Error', 'msg' => 'Something went wrong. Please try again.'],
    ];

    $c = $messages[$status] ?? $messages['error'];

    if (!$isSuccess) {
        http_response_code($status === 'missing_token' ? 401 : 403);
    }

    $safeReturn = htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8');
    $safeName = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8');
    $safeMsg = htmlspecialchars($c['msg'], ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle} - {$safeName}</title>
HTML;

    if ($isSuccess) {
        echo '<meta http-equiv="refresh" content="0;url=' . $safeReturn . '">';
    }

    echo <<<HTML
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0f172a;
            color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .sso-card {
            text-align: center;
            max-width: 24rem;
            width: 100%;
        }
        .sso-logo {
            width: 48px;
            height: 48px;
            margin: 0 auto 1.5rem;
        }
        .sso-spinner {
            width: 32px;
            height: 32px;
            margin: 0 auto 1.5rem;
            border: 3px solid rgba(255,255,255,0.1);
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        p {
            color: #94a3b8;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }
        .sso-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            background: #3b82f6;
            color: #fff;
            margin: 0.25rem;
        }
        .sso-btn:hover { background: #2563eb; }
        .sso-btn-ghost {
            background: transparent;
            color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .sso-btn-ghost:hover { border-color: #3b82f6; color: #3b82f6; }
    </style>
</head>
<body>
    <div class="sso-card">
HTML;

    if ($isSuccess) {
        echo <<<HTML
        <div class="sso-spinner"></div>
        <h1>{$safeMsg}</h1>
        <p>You'll be redirected in a moment.</p>
        <noscript><a href="{$safeReturn}" class="sso-btn">Continue</a></noscript>
        <script>setTimeout(function(){window.location.href='{$safeReturn}'},200);</script>
HTML;
    } else {
        echo <<<HTML
        <img class="sso-logo" src="https://hiphop.world/assets/logos/HipHop.World.svg" alt="Logo">
        <h1>{$safeTitle}</h1>
        <p>{$safeMsg}</p>
        <div>
HTML;
        if (in_array($status, ['expired', 'invalid', 'error'])) {
            echo '<a href="' . $safeReturn . '" class="sso-btn">Try Again</a> ';
        }
        echo '<a href="/" class="sso-btn sso-btn-ghost">Go Home</a>';
        echo '</div>';
    }

    echo <<<HTML
    </div>
</body>
</html>
HTML;
    exit;
}
