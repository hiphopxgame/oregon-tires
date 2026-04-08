<?php
declare(strict_types=1);

/**
 * MemberAuth — Core authentication for HW Member Kit
 *
 * Handles login, registration, session management, CSRF tokens,
 * rate limiting, email verification, and password reset.
 *
 * Supports three modes via MEMBER_MODE env:
 *   "independent" — site has its own `members` table
 *   "hw"          — site uses hiphop.world's shared `users` table (no direct registration)
 *   "network"     — site uses shared `users` table + allows direct registration + site-scoped roles
 */
class MemberAuth extends KitBase
{
    private static ?PDO $pdo = null;
    private static array $config = [];
    private static bool $initialized = false;
    /** @var callable|null Post-login callback: fn(array $member): void */
    private static $onLoginCallback = null;

    // ── KitBase abstract method implementations ──────────────────────────

    protected static function &staticPdo(): ?\PDO        { return self::$pdo; }
    protected static function &staticConfig(): array     { return self::$config; }
    protected static function &staticInitialized(): bool { return self::$initialized; }
    protected static function kitName(): string          { return 'MemberAuth'; }

    protected static function defaultConfig(): array
    {
        return [
            'mode'          => $_ENV['MEMBER_MODE'] ?? 'independent',
            'table_prefix'  => $_ENV['MEMBER_TABLE_PREFIX'] ?? '',
            'site_key'      => '',
            'members_table' => $_ENV['MEMBERS_TABLE'] ?? '',
            'session_key'   => $_ENV['SESSION_KEY'] ?? 'member_id',
            'login_url'     => $_ENV['LOGIN_URL'] ?? '',
            'site_url'      => $_ENV['SITE_URL'] ?? '',
            'site_name'     => $_ENV['SITE_NAME'] ?? 'Site',
            'session_name'  => $_ENV['SESSION_NAME'] ?? 'member_session',
            'session_lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 86400),
            'max_login_attempts' => 5,
            'lockout_minutes'    => 15,
            'token_expiry_minutes' => 30,
            'password_min_length'  => 8,
            'sso_brand_name' => $_ENV['SSO_BRAND_NAME'] ?? '',
            'sso_brand_logo' => $_ENV['SSO_BRAND_LOGO'] ?? '',
        ];
    }

    // ── Initialization ──────────────────────────────────────────────────

    public static function init(PDO $pdo, array $config = []): void
    {
        if (self::$initialized) {
            return;
        }
        parent::baseInit($pdo, $config);
    }

    /**
     * Get SSO button branding (name + logo URL).
     *
     * Priority: explicit config > site_name fallback > 1vsM default.
     * HW-mode sites default to "HipHop.World" branding.
     *
     * @return array{name: string, logo: string}
     */
    public static function getSSOBranding(): array
    {
        $name = self::$config['sso_brand_name'] ?? '';
        $logo = self::$config['sso_brand_logo'] ?? '';

        if ($name === '') {
            if (self::isHwMode()) {
                $name = 'HipHop.World';
            } else {
                $siteName = self::$config['site_name'] ?? '';
                $name = $siteName !== '' ? ($siteName . ' Account') : '1vsM Account';
            }
        }

        // Fallback logo: HW branding for HW-mode, 1vsM for others
        if ($logo === '') {
            $logo = self::isHwMode()
                ? 'https://hiphop.world/assets/logos/HipHop.World.svg'
                : 'https://1vsm.com/assets/logos/1vsM-logo.png';
        }

        return ['name' => $name, 'logo' => $logo];
    }

    /**
     * Resolve a member template path.
     *
     * Checks site-local override first, then falls back to the shared
     * member-kit templates directory. This allows any site to override
     * specific templates while sharing the rest.
     *
     * @param string $template Relative path like 'member/login.php'
     * @param string $siteTemplatesDir The site's local templates directory
     * @return string Absolute path to the template file
     */
    public static function resolveTemplate(string $template, string $siteTemplatesDir): string
    {
        // Site-specific override takes priority
        $localPath = rtrim($siteTemplatesDir, '/') . '/' . $template;
        if (file_exists($localPath)) {
            return $localPath;
        }

        // Fall back to shared member-kit templates
        if (defined('MEMBER_KIT_TEMPLATES')) {
            $sharedPath = MEMBER_KIT_TEMPLATES . '/' . $template;
            if (file_exists($sharedPath)) {
                return $sharedPath;
            }
        }

        // Return local path even if missing (will trigger a standard PHP error)
        return $localPath;
    }

    /**
     * Check if running in network mode (shared users table + direct registration + site roles).
     */
    public static function isNetworkMode(): bool
    {
        return (self::$config['mode'] ?? '') === 'network';
    }

    /**
     * Check if using shared users table (hw OR network mode).
     */
    public static function isSharedUsersMode(): bool
    {
        return self::isHwMode() || self::isNetworkMode();
    }

    /**
     * Get the members/users table name based on mode
     */
    public static function getMembersTable(): string
    {
        if (!empty(self::$config['members_table'])) {
            return self::$config['members_table'];
        }
        return self::isSharedUsersMode() ? 'users' : 'members';
    }

    /**
     * Get the member ID column name used in preference/activity tables
     */
    public static function getMemberIdColumn(): string
    {
        return self::isSharedUsersMode() ? 'user_id' : 'member_id';
    }

    /**
     * Get prefixed table name for site-specific tables (preferences, activity)
     */
    public static function prefixedTable(string $table): string
    {
        $prefix = self::$config['table_prefix'] ?? '';
        if ($prefix && !str_ends_with($prefix, '_')) {
            $prefix .= '_';
        }
        return $prefix . $table;
    }

    /**
     * Get the table prefix string (with trailing underscore if configured)
     */
    public static function getTablePrefix(): string
    {
        $prefix = self::$config['table_prefix'] ?? '';
        if ($prefix !== '' && !str_ends_with($prefix, '_')) {
            $prefix .= '_';
        }
        return $prefix;
    }

    // ── Session Management ──────────────────────────────────────────────

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Ensure CSRF token exists even when session was started externally
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return;
        }

        $isSecure = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (($_SERVER['SERVER_PORT'] ?? 0) == 443)
        );

        $sessionName = self::$config['session_name'] ?? 'member_session';
        $lifetime = self::$config['session_lifetime'] ?? 86400;

        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        // CSRF token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Periodic session regeneration (every 24h)
        $now = time();
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = $now;
        } elseif ($now - $_SESSION['_created'] > 86400) {
            session_regenerate_id(true);
            $_SESSION['_created'] = $now;
        }

        // Activity timeout
        if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity'] > $lifetime)) {
            session_unset();
            session_destroy();
            session_name($sessionName);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly'  => true,
                'samesite' => 'Lax',
            ]);
            session_start();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['_created'] = $now;
        }
        $_SESSION['_last_activity'] = $now;

        // Global logout check (network/hw mode only)
        if (self::isSharedUsersMode() && self::isLoggedIn() && self::$pdo) {
            $sessionKey = self::$config['session_key'] ?? 'member_id';
            $userId = (int) ($_SESSION[$sessionKey] ?? 0);
            if ($userId > 0 && self::checkGlobalLogout($userId)) {
                self::logout();
            }
        }
    }

    /**
     * Check if a user was globally logged out after their current session started.
     * Returns true if session should be destroyed.
     */
    private static function checkGlobalLogout(int $userId): bool
    {
        $sessionStart = $_SESSION['sso_session_start'] ?? ($_SESSION['_created'] ?? 0);
        if ($sessionStart === 0) {
            return false; // No session start timestamp — allow (will be set on next regen)
        }

        try {
            $stmt = self::$pdo->prepare("SELECT global_logout_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row || empty($row['global_logout_at'])) {
                return false;
            }

            $logoutTime = strtotime($row['global_logout_at']);
            return $logoutTime > $sessionStart;
        } catch (\Throwable) {
            return false; // DB error — don't break the flow
        }
    }

    public static function isLoggedIn(): bool
    {
        $key = self::$config['session_key'] ?? 'member_id';
        return isset($_SESSION[$key]) && $_SESSION[$key] > 0;
    }

    /**
     * Alias for isLoggedIn() — supports both naming conventions
     */
    public static function isMemberLoggedIn(): bool
    {
        return self::isLoggedIn();
    }

    public static function getCurrentMember(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        $key = self::$config['session_key'] ?? 'member_id';
        $table = self::getMembersTable();
        $stmt = self::getPdo()->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION[$key]]);
        $member = $stmt->fetch();

        return $member !== false ? $member : null;
    }

    public static function requireAuth(): array
    {
        if (!self::isLoggedIn()) {
            $isApi = (
                str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')
                || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            );

            if ($isApi) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                exit;
            }

            $returnUrl = $_SERVER['REQUEST_URI'] ?? '/';
            $loginBase = self::$config['login_url'] ?: (self::$config['site_url'] . '/member/login');
            $loginUrl = $loginBase . '?return=' . urlencode($returnUrl);
            header('Location: ' . $loginUrl);
            exit;
        }

        $member = self::getCurrentMember();
        if ($member === null) {
            self::logout();
            $loginBase = self::$config['login_url'] ?: (self::$config['site_url'] . '/member/login');
            header('Location: ' . $loginBase);
            exit;
        }

        return $member;
    }

    public static function getLoginUrl(): string
    {
        return self::$config['login_url'] ?: (self::$config['site_url'] . '/member/login');
    }

    public static function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function verifyCsrf(string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($sessionToken) || empty($token)) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Check rate limit using database (with session fallback for backward compatibility)
     * @return array{allowed: bool, remaining: int, resetAt: int}
     */
    public static function checkDbRateLimit(
        string $action,
        string $identifier,
        int $max = 5,
        int $windowSecs = 3600
    ): array {
        try {
            if (!self::$pdo) {
                throw new \Exception('No database connection');
            }

            $prefix = self::getTablePrefix();
            $sinceTime = date('Y-m-d H:i:s', time() - $windowSecs);

            // Count requests within window
            $stmt = self::$pdo->prepare(
                "SELECT COUNT(*) as count, MAX(created_at) as latest
                 FROM {$prefix}rate_limit_actions
                 WHERE action = ? AND identifier = ? AND created_at > ?"
            );
            $stmt->execute([$action, $identifier, $sinceTime]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $count = (int)($result['count'] ?? 0);
            $allowed = $count < $max;
            $remaining = max(0, $max - $count);
            $resetAt = $result['latest'] ? strtotime($result['latest']) + $windowSecs : time() + $windowSecs;

            return [
                'allowed' => $allowed,
                'remaining' => $remaining,
                'resetAt' => $resetAt
            ];
        } catch (\Throwable $e) {
            error_log('Rate limit check error: ' . $e->getMessage());
            // Fall back to session-based rate limiting if DB fails
            return self::checkSessionRateLimit($action, $identifier, $max, $windowSecs);
        }
    }

    /**
     * Record rate limit action in database
     */
    public static function recordDbRateLimit(string $action, string $identifier): bool {
        try {
            if (!self::$pdo) {
                return false;
            }

            $prefix = self::getTablePrefix();
            $stmt = self::$pdo->prepare(
                "INSERT INTO {$prefix}rate_limit_actions (action, identifier, created_at)
                 VALUES (?, ?, NOW())"
            );
            return $stmt->execute([$action, $identifier]);
        } catch (\Throwable $e) {
            error_log('Rate limit record error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Session-based rate limiting (fallback/legacy support)
     */
    private static function checkSessionRateLimit(
        string $action,
        string $identifier,
        int $max = 5,
        int $windowSecs = 3600
    ): array {
        $key = "ratelimit_{$action}_{$identifier}";
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['timestamps' => [], 'resetAt' => $now + $windowSecs];
        }

        // Clean old timestamps
        $_SESSION[$key]['timestamps'] = array_filter(
            $_SESSION[$key]['timestamps'],
            fn($ts) => $ts > ($now - $windowSecs)
        );

        $count = count($_SESSION[$key]['timestamps']);
        $allowed = $count < $max;
        $remaining = max(0, $max - $count);
        $resetAt = $_SESSION[$key]['resetAt'] ?? ($now + $windowSecs);

        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'resetAt' => $resetAt
        ];
    }

    // ── Login ───────────────────────────────────────────────────────────

    /**
     * Authenticate a user with email and password.
     *
     * @return array|false Member array on success, false on failure
     */
    public static function login(string $email, string $password): array|false
    {
        $email = strtolower(trim($email));
        if ($email === '' || $password === '') {
            return false;
        }

        // Rate limiting (DB-backed with fallback to session)
        $rateLimitResult = self::checkDbRateLimit('login', $email, 5, 3600);
        if (!$rateLimitResult['allowed']) {
            return false;
        }

        $table = self::getMembersTable();
        $stmt = self::getPdo()->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $member = $stmt->fetch();

        if ($member === false) {
            self::recordFailedAttempt($email);
            return false;
        }

        // Check if account is locked
        if (!empty($member['locked_until']) && strtotime($member['locked_until']) > time()) {
            return false;
        }

        // Check account status
        if (self::isSharedUsersMode()) {
            // HW/network mode: check disabled_at on shared users table
            if (!empty($member['disabled_at'])) {
                return false;
            }
        } else {
            // Independent mode: check status field
            if (($member['status'] ?? '') === 'suspended') {
                return false;
            }
        }

        // Verify password
        $hashField = 'password_hash';
        $hash = $member[$hashField] ?? '';
        if ($hash === '' || !password_verify($password, $hash)) {
            self::recordFailedAttempt($email);
            // Record failed attempt to DB as well
            self::recordDbRateLimit('login', $email);

            // Increment login_attempts on the member record (independent mode only)
            if (!self::isSharedUsersMode()) {
                $attempts = ((int) ($member['login_attempts'] ?? 0)) + 1;
                $lockUntil = null;
                if ($attempts >= self::$config['max_login_attempts']) {
                    $lockUntil = date('Y-m-d H:i:s', time() + (self::$config['lockout_minutes'] * 60));
                }
                $stmt = self::getPdo()->prepare(
                    "UPDATE {$table} SET login_attempts = ?, locked_until = ? WHERE id = ?"
                );
                $stmt->execute([$attempts, $lockUntil, $member['id']]);
            }

            return false;
        }

        // Success — clear attempts and update login timestamp
        self::clearFailedAttempts($email);

        if (!self::isSharedUsersMode()) {
            $stmt = self::getPdo()->prepare(
                "UPDATE {$table} SET login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$member['id']]);
        }

        // Start authenticated session (also fires onLogin hook)
        self::startAuthenticatedSession($member);

        return $member;
    }

    // ── Registration ────────────────────────────────────────────────────

    /**
     * Register a new member (independent mode only).
     *
     * @param array $data Keys: email, password, username, display_name
     * @return array The newly created member
     * @throws \RuntimeException on validation failure
     */
    public static function register(array $data): array
    {
        if (self::isHwMode()) {
            throw new \RuntimeException('Direct registration not available in HW mode. Use SSO.');
        }
        // Network mode allows direct registration into the shared users table

        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $username = trim($data['username'] ?? '');
        $displayName = trim($data['display_name'] ?? '');

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Invalid email address');
        }

        // Validate password
        if (strlen($password) < self::$config['password_min_length']) {
            throw new \RuntimeException('Password must be at least ' . self::$config['password_min_length'] . ' characters');
        }

        // Validate username (optional but unique if provided)
        $table = self::getMembersTable();
        if ($username !== '') {
            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
                throw new \RuntimeException('Username must be 3-50 characters: letters, numbers, underscore only');
            }

            $stmt = self::getPdo()->prepare("SELECT id FROM {$table} WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new \RuntimeException('Username already taken');
            }
        }

        // Check email uniqueness
        $stmt = self::getPdo()->prepare("SELECT id FROM {$table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new \RuntimeException('An account with this email already exists');
        }

        // Create member
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        if (self::isNetworkMode()) {
            // Network mode: insert into shared users table (different column set)
            $stmt = self::getPdo()->prepare(
                "INSERT INTO {$table} (email, username, password_hash, display_name, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())"
            );
            $stmt->execute([
                $email,
                $username ?: null,
                $passwordHash,
                $displayName ?: null,
            ]);
        } else {
            // Independent mode: uses status enum
            $stmt = self::getPdo()->prepare(
                "INSERT INTO {$table} (email, username, password_hash, display_name, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 'unverified', NOW(), NOW())"
            );
            $stmt->execute([
                $email,
                $username ?: null,
                $passwordHash,
                $displayName ?: null,
            ]);
        }

        $memberId = (int) self::getPdo()->lastInsertId();

        // Stamp registered_site_key if site_key is configured
        $siteKey = self::$config['site_key'] ?? '';
        if ($siteKey) {
            self::getPdo()->prepare(
                "UPDATE {$table} SET registered_site_key = ? WHERE id = ?"
            )->execute([$siteKey, $memberId]);
        }

        // Record first site connection
        self::recordSiteConnection($memberId);

        // Generate email verification token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + (self::$config['token_expiry_minutes'] * 60));

        $evTable = self::prefixedTable('email_verifications');
        $stmt = self::getPdo()->prepare(
            "INSERT INTO {$evTable} (member_id, token_hash, expires_at, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$memberId, $tokenHash, $expiresAt]);

        // Send verification email
        MemberMail::sendVerification($email, $token, self::$config['site_name'], self::$config['site_url']);

        // Log activity
        MemberProfile::logActivity($memberId, 'register', null, null, [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);

        // Fetch and return new member
        $stmt = self::getPdo()->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$memberId]);
        return $stmt->fetch();
    }

    // ── Logout ──────────────────────────────────────────────────────────

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly'  => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        session_destroy();
    }

    // ── Email Verification ──────────────────────────────────────────────

    public static function verifyEmail(string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $table = self::getMembersTable();
        $evTable = self::prefixedTable('email_verifications');

        $stmt = self::getPdo()->prepare(
            "SELECT ev.*, m.email FROM {$evTable} ev
             JOIN {$table} m ON m.id = ev.member_id
             WHERE ev.token_hash = ? AND ev.expires_at > NOW() AND ev.verified_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$tokenHash]);
        $record = $stmt->fetch();

        if (!$record) {
            return false;
        }

        // Mark token as used
        $stmt = self::getPdo()->prepare(
            "UPDATE {$evTable} SET verified_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$record['id']]);

        // If there's a new_email, update the member's email
        if (!empty($record['new_email'])) {
            $stmt = self::getPdo()->prepare(
                "UPDATE {$table} SET email = ?, email_verified_at = NOW(), updated_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$record['new_email'], $record['member_id']]);
        } else {
            // Initial verification — activate account
            $stmt = self::getPdo()->prepare(
                "UPDATE {$table} SET status = 'active', email_verified_at = NOW(), updated_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$record['member_id']]);
        }

        MemberProfile::logActivity((int) $record['member_id'], 'email_verified');

        return true;
    }

    public static function resendVerification(int $memberId): bool
    {
        // Rate limit: max 3 resends per 5 minutes per IP
        if (!self::checkActionRateLimit('resend_verification', 3, 300)) {
            return false;
        }

        $table = self::getMembersTable();
        $stmt = self::getPdo()->prepare("SELECT email, status FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        if (!$member || ($member['status'] ?? '') !== 'unverified') {
            return false;
        }

        // Invalidate old tokens
        $evTable = self::prefixedTable('email_verifications');
        $stmt = self::getPdo()->prepare(
            "UPDATE {$evTable} SET verified_at = NOW() WHERE member_id = ? AND verified_at IS NULL"
        );
        $stmt->execute([$memberId]);

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + (self::$config['token_expiry_minutes'] * 60));

        $stmt = self::getPdo()->prepare(
            "INSERT INTO {$evTable} (member_id, token_hash, expires_at, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$memberId, $tokenHash, $expiresAt]);

        MemberMail::sendVerification($member['email'], $token, self::$config['site_name'], self::$config['site_url']);

        return true;
    }

    // ── Password Reset ──────────────────────────────────────────────────

    public static function requestPasswordReset(string $email): bool
    {
        // Rate limit: max 3 requests per hour using DB-backed rate limiting
        $rateLimitResult = self::checkDbRateLimit('password_reset', $email, 3, 3600);
        if (!$rateLimitResult['allowed']) {
            // Return true to not leak rate-limit status
            return true;
        }

        $email = strtolower(trim($email));
        $table = self::getMembersTable();

        $stmt = self::getPdo()->prepare("SELECT id, email FROM {$table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $member = $stmt->fetch();

        if (!$member) {
            // Return true anyway to not leak email existence
            return true;
        }

        $memberId = (int) $member['id'];

        // Invalidate existing reset tokens
        $prTable = self::prefixedTable('password_resets');
        $stmt = self::getPdo()->prepare(
            "UPDATE {$prTable} SET used_at = NOW() WHERE member_id = ? AND used_at IS NULL"
        );
        $stmt->execute([$memberId]);

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + (self::$config['token_expiry_minutes'] * 60));

        $stmt = self::getPdo()->prepare(
            "INSERT INTO {$prTable} (member_id, token_hash, expires_at, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$memberId, $tokenHash, $expiresAt]);

        // Record the password reset request to DB
        self::recordDbRateLimit('password_reset', $email);

        MemberMail::sendPasswordReset($member['email'], $token, self::$config['site_name'], self::$config['site_url']);

        MemberProfile::logActivity($memberId, 'password_reset_requested');

        return true;
    }

    public static function resetPassword(string $token, string $newPassword): bool
    {
        if (strlen($newPassword) < self::$config['password_min_length']) {
            throw new \RuntimeException('Password must be at least ' . self::$config['password_min_length'] . ' characters');
        }

        $tokenHash = hash('sha256', $token);
        $prTable = self::prefixedTable('password_resets');

        $stmt = self::getPdo()->prepare(
            "SELECT * FROM {$prTable}
             WHERE token_hash = ? AND expires_at > NOW() AND used_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$tokenHash]);
        $record = $stmt->fetch();

        if (!$record) {
            return false;
        }

        // Mark token as used
        $stmt = self::getPdo()->prepare("UPDATE {$prTable} SET used_at = NOW() WHERE id = ?");
        $stmt->execute([$record['id']]);

        // Update password
        $table = self::getMembersTable();
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = self::getPdo()->prepare("UPDATE {$table} SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$passwordHash, $record['member_id']]);

        // Clear any lockout (independent mode only — shared users table doesn't have these columns)
        if (!self::isSharedUsersMode()) {
            $table = self::getMembersTable();
            $stmt = self::getPdo()->prepare(
                "UPDATE {$table} SET login_attempts = 0, locked_until = NULL WHERE id = ?"
            );
            $stmt->execute([$record['member_id']]);
        }

        MemberProfile::logActivity((int) $record['member_id'], 'password_reset_completed');

        return true;
    }

    public static function changePassword(int $memberId, string $current, string $new): bool
    {
        if (strlen($new) < self::$config['password_min_length']) {
            throw new \RuntimeException('Password must be at least ' . self::$config['password_min_length'] . ' characters');
        }

        $table = self::getMembersTable();
        $stmt = self::getPdo()->prepare("SELECT password_hash FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        if (!$member || !password_verify($current, $member['password_hash'])) {
            return false;
        }

        $passwordHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = self::getPdo()->prepare("UPDATE {$table} SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$passwordHash, $memberId]);

        MemberProfile::logActivity($memberId, 'password_changed');

        return true;
    }

    // ── Site Connection Tracking ───────────────────────────────────────

    /**
     * Record a member's connection to a site.
     *
     * Inserts or upserts into member_site_connections table. Silent no-op if:
     * - site_key is empty
     * - member_site_connections table is absent (graceful degradation)
     * - PDO is null
     *
     * @param int $memberId The member ID
     * @return void
     */
    private static function recordSiteConnection(int $memberId): void
    {
        $siteKey = self::$config['site_key'] ?? '';
        if (!$siteKey || !self::$pdo) {
            return;
        }

        try {
            self::$pdo->prepare("
                INSERT INTO member_site_connections
                    (member_id, site_key, first_seen_at, last_seen_at, connection_count)
                VALUES (?, ?, NOW(), NOW(), 1)
                ON DUPLICATE KEY UPDATE
                    last_seen_at = NOW(),
                    connection_count = connection_count + 1
            ")->execute([$memberId, $siteKey]);
        } catch (\Throwable) {
            // Table absent on older installs — fail silently
        }
    }

    // ── Rate Limiting ───────────────────────────────────────────────────

    /**
     * Check if login attempts are within rate limit.
     * Uses a simple session-based counter + IP tracking.
     */
    public static function checkRateLimit(string $email): bool
    {
        $key = 'login_attempts_' . md5($email . ($_SERVER['REMOTE_ADDR'] ?? ''));

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first_at' => time()];
        }

        $record = $_SESSION[$key];
        $windowSeconds = self::$config['lockout_minutes'] * 60;

        // Reset if window expired
        if (time() - $record['first_at'] > $windowSeconds) {
            $_SESSION[$key] = ['count' => 0, 'first_at' => time()];
            return true;
        }

        return $record['count'] < self::$config['max_login_attempts'];
    }

    public static function recordFailedAttempt(string $email): void
    {
        $key = 'login_attempts_' . md5($email . ($_SERVER['REMOTE_ADDR'] ?? ''));

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first_at' => time()];
        }

        $_SESSION[$key]['count']++;
    }

    public static function clearFailedAttempts(string $email): void
    {
        $key = 'login_attempts_' . md5($email . ($_SERVER['REMOTE_ADDR'] ?? ''));
        unset($_SESSION[$key]);
    }

    /**
     * Generic session-based rate limiter.
     *
     * @param string $action  Unique key for the action (e.g. 'password_reset')
     * @param int    $maxAttempts  Max attempts within the window
     * @param int    $windowSeconds  Time window in seconds
     * @return bool True if within limit, false if rate-limited
     */
    public static function checkActionRateLimit(string $action, int $maxAttempts = 3, int $windowSeconds = 300): bool
    {
        $key = 'rate_' . $action . '_' . md5($_SERVER['REMOTE_ADDR'] ?? '');

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'first_at' => time()];
            return true;
        }

        $record = $_SESSION[$key];

        // Reset if window expired
        if (time() - $record['first_at'] > $windowSeconds) {
            $_SESSION[$key] = ['count' => 1, 'first_at' => time()];
            return true;
        }

        if ($record['count'] >= $maxAttempts) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }

    // ── Login Hooks ────────────────────────────────────────────────────

    /**
     * Register a callback that runs after any successful login or SSO auth.
     *
     * Use this to set site-specific session keys (e.g. is_admin).
     * The callback receives the full member/user array.
     *
     * @param callable $callback fn(array $member): void
     */
    public static function onLogin(callable $callback): void
    {
        self::$onLoginCallback = $callback;
    }

    // ── SSO Session Helper ──────────────────────────────────────────────

    /**
     * Start an authenticated session for a member (used by SSO callback and login).
     */
    public static function startAuthenticatedSession(array $member): void
    {
        $sessionKey = self::$config['session_key'] ?? 'member_id';
        session_regenerate_id(true);
        $_SESSION[$sessionKey] = (int) $member['id'];
        $_SESSION['member_email'] = $member['email'] ?? '';
        $_SESSION['member_username'] = $member['username'] ?? '';
        $_SESSION['sso_session_start'] = time();

        // Set super admin flag for all modes (safe: if column missing, value is null → false)
        $_SESSION['is_super_admin'] = !empty($member['is_admin']);

        // Load site-scoped role (network/hw mode)
        if (self::isSharedUsersMode()) {
            self::loadSiteRole((int) $member['id'], self::$config['site_key'] ?? '');
        }

        // Record site connection (every login)
        self::recordSiteConnection((int) $member['id']);

        // Run site-specific post-login hook
        if (self::$onLoginCallback !== null) {
            (self::$onLoginCallback)($member);
        }
    }

    /**
     * Load the user's site-scoped role from user_site_roles table.
     * Sets $_SESSION['site_role'] to the role or 'standard' if no row found.
     */
    private static function loadSiteRole(int $userId, string $siteKey): void
    {
        $_SESSION['site_role'] = 'standard';

        if (!$siteKey || !self::$pdo) {
            return;
        }

        try {
            $stmt = self::$pdo->prepare(
                "SELECT role FROM user_site_roles WHERE user_id = ? AND site_key = ? LIMIT 1"
            );
            $stmt->execute([$userId, $siteKey]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $_SESSION['site_role'] = $row['role'];
            }
        } catch (\Throwable) {
            // Table may not exist yet -- graceful degradation
        }
    }

    // -- Site Role Checks -------------------------------------------------

    /**
     * Get the effective role for the current user on this site.
     * Returns 'super_admin' if is_admin=1, otherwise the site_role from session.
     */
    public static function getSiteRole(): string
    {
        if (!empty($_SESSION['is_super_admin'])) {
            return 'super_admin';
        }
        return $_SESSION['site_role'] ?? 'standard';
    }

    /**
     * Check if current user is the super admin (users.is_admin = 1).
     */
    public static function isSuperAdmin(): bool
    {
        return !empty($_SESSION['is_super_admin'])
            || ($_SESSION['site_role'] ?? '') === 'super_admin';
    }

    /**
     * Check if current user is at least a site admin (super_admin or admin).
     */
    public static function isSiteAdmin(): bool
    {
        return self::isSuperAdmin() || ($_SESSION['site_role'] ?? '') === 'admin';
    }

    /**
     * Check if current user is at least a site manager (super_admin, admin, or manager).
     */
    public static function isSiteManager(): bool
    {
        return self::isSiteAdmin() || ($_SESSION['site_role'] ?? '') === 'manager';
    }

    /**
     * Check if current user is at least site support (super_admin, admin, manager, or support).
     */
    public static function isSiteSupport(): bool
    {
        return self::isSiteManager() || ($_SESSION['site_role'] ?? '') === 'support';
    }

    /**
     * Require a minimum site role. Returns 403 JSON if insufficient.
     *
     * @param string $minRole One of: 'support', 'manager', 'admin', 'super_admin'
     */
    public static function requireSiteRole(string $minRole): void
    {
        $check = match ($minRole) {
            'support'     => self::isSiteSupport(),
            'manager'     => self::isSiteManager(),
            'admin'       => self::isSiteAdmin(),
            'super_admin' => self::isSuperAdmin(),
            default       => true, // 'standard' -- any logged-in user
        };

        if (!$check) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
            exit;
        }
    }

    // -- Cross-Domain SSO -------------------------------------------------

    /**
     * Build a cross-domain URL with SSO token for seamless navigation.
     *
     * If not logged in or same domain, return URL as-is.
     * Otherwise, generate an SSO token and build the callback URL.
     *
     * @param string $targetUrl Full URL to the destination
     * @return string URL (possibly with SSO token appended)
     */
    public static function buildCrossDomainUrl(string $targetUrl): string
    {
        // Not logged in -- return as-is
        if (!self::isLoggedIn() || !self::$pdo) {
            return $targetUrl;
        }

        // Parse target
        $parsed = parse_url($targetUrl);
        if (empty($parsed['host'])) {
            return $targetUrl; // Relative path -- no SSO needed
        }

        // Same domain -- no SSO needed
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($parsed['host'] === $currentHost) {
            return $targetUrl;
        }

        // Generate SSO token
        $sessionKey = self::$config['session_key'] ?? 'member_id';
        $userId = (int) ($_SESSION[$sessionKey] ?? 0);
        if ($userId <= 0) {
            return $targetUrl;
        }

        try {
            // Clean expired tokens
            self::$pdo->prepare("DELETE FROM sso_tokens WHERE expires_at < NOW()")->execute();

            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minute TTL

            $stmt = self::$pdo->prepare(
                "INSERT INTO sso_tokens (token, user_id, expires_at) VALUES (?, ?, ?)"
            );
            $stmt->execute([$token, $userId, $expiresAt]);

            // Build SSO callback URL on target domain
            $scheme = $parsed['scheme'] ?? 'https';
            $targetDomain = $scheme . '://' . $parsed['host'];

            return $targetDomain . '/sso?token=' . urlencode($token)
                . '&return=' . urlencode($targetUrl);
        } catch (\Throwable) {
            return $targetUrl; // DB error -- return original URL
        }
    }

    // ── Reset (testing) ─────────────────────────────────────────────────

    /**
     * Reset initialization state (for testing purposes).
     */
    public static function reset(): void
    {
        parent::baseReset();
        self::$onLoginCallback = null;
    }
}
