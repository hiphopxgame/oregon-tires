<?php
/**
 * Oregon Tires — Admin Authentication Helpers
 */

declare(strict_types=1);

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES    = 15;
const BCRYPT_COST        = 12;

// The business owner — ALWAYS admin, cannot lose access under any circumstance.
const OWNER_EMAIL = 'oregontirespdx@gmail.com';

// Protected accounts — cannot be demoted, deactivated, or deleted via the admin panel.
const PROTECTED_SUPERADMINS = [
    OWNER_EMAIL,
];

/**
 * Check if an email is the owner email (case-insensitive).
 */
function isOwnerEmail(string $email): bool
{
    return strtolower(trim($email)) === OWNER_EMAIL;
}

/**
 * Enforce admin rights for ANY active admin account.
 *
 * Called as a safety net whenever admin auth is checked. If the current session
 * email (admin_email or member_email) matches any active row in oretir_admins,
 * ensures full admin session vars are present — even if session_regenerate_id()
 * wiped them. Case-insensitive email matching.
 *
 * For the business owner (OWNER_EMAIL), auto-creates the admin row if missing.
 *
 * @return bool True if admin was detected and session vars enforced.
 */
function enforceAdminSession(PDO $pdo): bool
{
    $email = $_SESSION['admin_email'] ?? $_SESSION['member_email'] ?? '';
    if ($email === '') {
        return false;
    }

    // Already has valid admin session — nothing to do
    if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role']) && !empty($_SESSION['login_time'])) {
        return true;
    }

    // Look up admin by email (case-insensitive via LOWER())
    try {
        $stmt = $pdo->prepare('SELECT id, email, role, display_name, language FROM oretir_admins WHERE LOWER(email) = LOWER(?) AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // Owner-only: auto-create admin row if missing
        if (!$admin && isOwnerEmail($email)) {
            $pdo->prepare(
                'INSERT INTO oretir_admins (email, display_name, role, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, 1, NOW(), NOW())'
            )->execute([OWNER_EMAIL, 'Owner', 'admin']);
            $admin = [
                'id'           => (int) $pdo->lastInsertId(),
                'email'        => OWNER_EMAIL,
                'role'         => 'admin',
                'display_name' => 'Owner',
                'language'     => 'both',
            ];
        }

        if (!$admin) {
            return false;
        }

        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_email']    = $admin['email'];
        $_SESSION['admin_role']     = $admin['role'] ?: 'admin';
        $_SESSION['admin_name']     = $admin['display_name'] ?: $admin['email'];
        $_SESSION['admin_language'] = $admin['language'] ?? 'both';
        $_SESSION['dashboard_role'] = 'admin';

        if (empty($_SESSION['login_time'])) {
            $_SESSION['login_time'] = time();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return true;
    } catch (\Throwable $e) {
        error_log('enforceAdminSession failed: ' . $e->getMessage());
        return false;
    }
}

/** @deprecated Use enforceAdminSession() — kept for backwards compatibility */
function enforceOwnerAdmin(PDO $pdo): bool
{
    return enforceAdminSession($pdo);
}

// ─── Permission Bundles ─────────────────────────────────────────────────────
// Maps each admin API endpoint filename to the required permission bundle.
// 'my_work' is always granted. Admins bypass all checks.
const ENDPOINT_PERMISSIONS = [
    // Shop Operations
    'appointments.php'       => 'shop_ops',
    'repair-orders.php'      => 'shop_ops',
    'inspections.php'        => 'shop_ops',
    'inspection-photos.php'  => 'shop_ops',
    'estimates.php'          => 'shop_ops',
    'invoices.php'           => 'shop_ops',
    'waitlist.php'           => 'shop_ops',
    'tire-quotes.php'        => 'shop_ops',
    'services.php'           => 'shop_ops',
    'visit-log.php'          => 'shop_ops',
    'vehicles.php'           => 'shop_ops',
    'vin-decode.php'         => 'shop_ops',
    'tire-fitment.php'       => 'shop_ops',
    'business-hours.php'     => 'shop_ops',
    'service-reminders.php'  => 'shop_ops',
    // Customers
    'customers.php'          => 'customers',
    'resource-planner.php'   => 'customers',
    // Messaging
    'conversations.php'      => 'messaging',
    'messages.php'           => 'messaging',
    'email-check.php'        => 'messaging',
    // Team
    'employees.php'          => 'team',
    'employee-groups.php'    => 'team',
    'schedules.php'          => 'team',
    'labor.php'              => 'team',
    // Marketing
    'blog.php'               => 'marketing',
    'promotions.php'         => 'marketing',
    'faq.php'                => 'marketing',
    'testimonials.php'       => 'marketing',
    'gallery.php'            => 'marketing',
    'service-images.php'     => 'marketing',
    'subscribers.php'        => 'marketing',
    'loyalty.php'            => 'marketing',
    'loyalty-rewards.php'    => 'marketing',
    'referrals.php'          => 'marketing',
    'push-broadcast.php'     => 'marketing',
    // Settings & Analytics
    'analytics.php'          => 'settings',
    'site-settings.php'      => 'settings',
    'email-logs.php'         => 'settings',
    'email-template-vars.php'=> 'settings',
    'export.php'             => 'settings',
    'admins.php'             => 'settings',
    // account.php uses requireStaff() directly — all staff can manage their own account
];

/**
 * Check if current staff member has the required permission bundle.
 * Admins bypass all checks. Employees must have the bundle in their group.
 */
function requirePermission(string $bundle): array
{
    $staff = requireStaff();

    // Admins bypass permission checks
    if ($staff['type'] === 'admin') {
        return $staff;
    }

    // my_work is always granted for employees
    if ($bundle === 'my_work') {
        return $staff;
    }

    $perms = $_SESSION['employee_permissions'] ?? [];
    if (!in_array($bundle, $perms, true)) {
        jsonError('You do not have permission to access this feature.', 403);
    }

    return $staff;
}

/**
 * Resolve permission bundle for the current admin endpoint file.
 * Uses the ENDPOINT_PERMISSIONS map with the current script filename.
 */
function requireEndpointPermission(): array
{
    $file = basename($_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
    $bundle = ENDPOINT_PERMISSIONS[$file] ?? null;

    // If no mapping exists, require admin (safe default)
    if ($bundle === null) {
        return requireAdmin();
    }

    return requirePermission($bundle);
}

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
    $_SESSION['dashboard_role'] = 'admin';

    // Detect if admin is also an employee (for My Work / My Schedule tabs)
    $empStmt = $db->prepare('SELECT id, name, role, group_id FROM oretir_employees WHERE email = ? AND is_active = 1 LIMIT 1');
    $empStmt->execute([$admin['email']]);
    $emp = $empStmt->fetch();
    if ($emp) {
        $_SESSION['employee_id']   = (int) $emp['id'];
        $_SESSION['employee_name'] = $emp['name'];
        $_SESSION['employee_role'] = $emp['role'];
        if ($emp['group_id']) {
            $grpStmt = $db->prepare('SELECT name_en, name_es, permissions FROM oretir_employee_groups WHERE id = ? LIMIT 1');
            $grpStmt->execute([$emp['group_id']]);
            $grp = $grpStmt->fetch();
            if ($grp) {
                $_SESSION['employee_group_id']      = (int) $emp['group_id'];
                $_SESSION['employee_group_name']     = $grp['name_en'];
                $_SESSION['employee_group_name_es']   = $grp['name_es'];
                $_SESSION['employee_permissions']     = json_decode($grp['permissions'], true) ?: ['my_work'];
            }
        }
    }

    return $admin;
}

/**
 * Check if current session is an authenticated admin.
 */
function requireAdmin(): array
{
    startSecureSession();

    if (empty($_SESSION['admin_id'])) {
        // Safety net: restore admin rights for any active admin in oretir_admins
        enforceAdminSession(getDB());
    }

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
 * Check if current session is an authenticated staff member (admin or employee).
 * Returns a normalized user array for either role.
 */
function requireStaff(): array
{
    startSecureSession();

    // Session timeout (8 hours)
    if (time() - ($_SESSION['login_time'] ?? 0) > 28800) {
        session_destroy();
        jsonError('Session expired. Please log in again.', 401);
    }

    // Safety net: restore admin rights for any active admin in oretir_admins
    if (empty($_SESSION['admin_id'])) {
        enforceAdminSession(getDB());
    }

    // Check admin first
    if (!empty($_SESSION['admin_id'])) {
        return [
            'id'          => $_SESSION['admin_id'],
            'email'       => $_SESSION['admin_email'],
            'role'        => $_SESSION['admin_role'],
            'name'        => $_SESSION['admin_name'],
            'type'        => 'admin',
            'language'    => $_SESSION['admin_language'] ?? 'both',
            'employee_id' => $_SESSION['employee_id'] ?? null,
        ];
    }

    // Check employee
    if (!empty($_SESSION['employee_id'])) {
        return [
            'id'          => $_SESSION['employee_id'],
            'email'       => $_SESSION['employee_email'] ?? '',
            'role'        => $_SESSION['employee_role'] ?? 'Employee',
            'name'        => $_SESSION['employee_name'] ?? '',
            'type'        => 'employee',
            'language'    => $_SESSION['admin_language'] ?? 'both',
            'employee_id' => $_SESSION['employee_id'],
        ];
    }

    jsonError('Authentication required.', 401);
}

/**
 * Require superadmin role. Returns admin array or exits with 403.
 */
function requireSuperAdmin(): array
{
    $admin = requireAdmin();
    if (!in_array($admin['role'], ['superadmin', 'super_admin'], true)) {
        jsonError('Super admin access required.', 403);
    }
    return $admin;
}

/**
 * Check if admin is superadmin (non-fatal).
 */
function isSuperAdmin(): bool
{
    startSecureSession();
    return in_array($_SESSION['admin_role'] ?? '', ['superadmin', 'super_admin'], true);
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

// ─── Customer Auth Helpers ──────────────────────────────────────────────────

/**
 * Check if a customer is logged in via Member Kit.
 */
function isCustomerLoggedIn(): bool
{
    return !empty($_SESSION['member_id']);
}

/**
 * Guard: require customer auth or return 401.
 */
function requireCustomerAuth(): void
{
    if (!isCustomerLoggedIn()) {
        jsonError('Authentication required', 401);
    }
}

/**
 * Get the current user type (admin or customer).
 */
function getCurrentUserType(): ?string
{
    if (!empty($_SESSION['admin_id'])) return 'admin';
    if (!empty($_SESSION['employee_id'])) return 'employee';
    if (!empty($_SESSION['member_id'])) return 'customer';
    return null;
}
