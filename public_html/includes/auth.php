<?php
/**
 * Oregon Tires — Admin Authentication Helpers
 */

declare(strict_types=1);

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES    = 15;
const BCRYPT_COST        = 12;

/**
 * Attempt admin login. Returns admin row on success, error string on failure.
 */
function adminLogin(string $email, string $password): array|string
{
    $db = getDB();

    $stmt = $db->prepare('SELECT * FROM oretir_admins WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return 'Invalid email or password.';
    }

    // Check lockout
    if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
        $remaining = ceil((strtotime($admin['locked_until']) - time()) / 60);
        return "Account locked. Try again in {$remaining} minute(s).";
    }

    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        $attempts = $admin['login_attempts'] + 1;

        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_MINUTES * 60);
            $db->prepare('UPDATE oretir_admins SET login_attempts = ?, locked_until = ? WHERE id = ?')
               ->execute([$attempts, $lockUntil, $admin['id']]);
        } else {
            $db->prepare('UPDATE oretir_admins SET login_attempts = ? WHERE id = ?')
               ->execute([$attempts, $admin['id']]);
        }

        return 'Invalid email or password.';
    }

    // Success — reset attempts, record login time
    $db->prepare('UPDATE oretir_admins SET login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?')
       ->execute([$admin['id']]);

    // Start secure session
    startSecureSession();
    session_regenerate_id(true);

    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_email']    = $admin['email'];
    $_SESSION['admin_role']     = $admin['role'];
    $_SESSION['admin_name']     = $admin['display_name'];
    $_SESSION['admin_language'] = $admin['language'] ?? 'both';
    $_SESSION['login_time']     = time();
    $_SESSION['csrf_token']     = bin2hex(random_bytes(32));

    return $admin;
}

/**
 * Check if current session is an authenticated admin.
 */
function requireAdmin(): array
{
    startSecureSession();

    if (empty($_SESSION['admin_id'])) {
        jsonError('Authentication required.', 401);
    }

    // Session timeout (8 hours)
    if (time() - ($_SESSION['login_time'] ?? 0) > 28800) {
        session_destroy();
        jsonError('Session expired. Please log in again.', 401);
    }

    return [
        'id'       => $_SESSION['admin_id'],
        'email'    => $_SESSION['admin_email'],
        'role'     => $_SESSION['admin_role'],
        'name'     => $_SESSION['admin_name'],
        'language' => $_SESSION['admin_language'] ?? 'both',
    ];
}

/**
 * Verify CSRF token from request header.
 */
function verifyCsrf(): void
{
    startSecureSession();

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        jsonError('Invalid CSRF token.', 403);
    }
}

/**
 * Admin logout.
 */
function adminLogout(): void
{
    startSecureSession();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
}

/**
 * Hash a password with bcrypt cost 12.
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
}
