# Member Kit — Kit Instructions

## Purpose
Shared authentication, SSO, OAuth2, session management, and member profile system for all 1vsM network sites. Supports independent members tables, shared HipHop.World users, or network mode with site-scoped roles. Includes 2FA, WebAuthn, magic links, email verification, password reset, device tracking, anomaly detection, and cross-site connection tracking.

## Structure
```
loader.php                              — Entry point (defines MEMBER_KIT_PATH/URL, stub t(), loads classes)
includes/
  KitBase.php                           — Shared abstract base (fallback)
  member-kit/
    MemberAuth.php                      — Core auth: login, register, sessions, CSRF, rate limiting, roles
    MemberProfile.php                   — Profile CRUD, avatar upload, preferences, activity log
    MemberSSO.php                       — OAuth2 consumer with PKCE (Authorization Code + S256)
    MemberMail.php                      — Email verification + password reset emails (PHPMailer)
    MemberSync.php                      — Cross-site activity reporting to HW hub
config/database.php                     — Database configuration
css/member.css                          — Member UI styles
js/member.js                            — Client-side auth logic
api/member/                             — 34 API endpoints (see below)
endpoints/
  sso-callback.php                      — SSO callback handler
  api/site-roles.php                    — Site role management
templates/
  member/                               — Login, register, dashboard, profile, settings, devices, etc.
  member/modals/                        — 2FA suggestion, keyboard help
  member/tabs/                          — Role management tabs
  site-auth-boilerplate/                — Starter files for new sites (auth.php, login.php, members.php)
migrations/                             — 14 migration files (001-008 + schema + extras)
```

## Integration
```php
require_once $_ENV['MEMBER_KIT_PATH'] . '/loader.php';
MemberAuth::init($pdo, [
    'mode'          => 'independent',   // or 'hw' or 'network'
    'site_key'      => '1vsm',
    'table_prefix'  => 'ovsm_',
    'members_table' => 'members',       // or 'users' for HW mode
    'session_key'   => 'member_id',
    'login_url'     => '/members',
    'site_url'      => 'https://1vsm.com',
    'site_name'     => '1vsM',
]);
MemberAuth::startSession();
```

### Required .env Variables
- `MEMBER_KIT_PATH` — filesystem path
- `MEMBER_KIT_URL` — web-accessible URL for CSS/JS assets
- `MEMBER_MODE` — `independent`, `hw`, or `network`
- `MEMBER_TABLE_PREFIX` — table name prefix
- `MEMBERS_TABLE` — override table name
- `SESSION_KEY`, `SESSION_NAME`, `SESSION_LIFETIME` — session config
- `SSO_CLIENT_ID`, `SSO_CLIENT_SECRET`, `SSO_REDIRECT_URI` — for OAuth SSO
- `SSO_BRAND_NAME`, `SSO_BRAND_LOGO` — SSO button branding
- `SYNC_API_KEY` — for cross-site activity sync
- SMTP vars for email (via MemberMail)

## API Endpoints (34 total)
**Auth**: `login.php`, `register.php`, `logout.php`, `status.php`
**Email**: `verify-email.php`, `resend-verification.php`
**Password**: `forgot-password.php`, `password-reset.php`, `reset-password.php`, `password.php`
**Profile**: `profile.php`, `preferences.php`
**SSO**: `sso.php`, `sso-callback.php`, `sso-unlink.php`, `google.php`
**2FA**: `2fa-setup.php`, `2fa-prompt.php`
**Devices**: `devices.php`, `rename-device.php`, `revoke-device.php`, `rotate-fingerprint.php`
**Magic Link**: `magic-link.php`, `magic-link-verify.php`
**WebAuthn**: `webauthn-register-begin.php`, `webauthn-register-complete.php`
**Security**: `anomaly-check.php`, `report-suspicious-activity.php`, `login-activity.php`
**Mobile**: `mobile-auth.php`, `mobile-notify.php`, `mobile-register-device.php`
**Session**: `session-extend.php`

## Key Classes/Functions

### MemberAuth (static, extends KitBase)
- `init(PDO, array $config): void` — Initialize auth system
- `startSession(): void` — Start/resume session with CSRF token
- `login(string $email, string $password): array|false` — Authenticate user
- `register(array $data): array` — Create account with email verification
- `logout(): void` — Destroy session
- `isLoggedIn(): bool` / `isMemberLoggedIn(): bool` — Check auth state
- `getCurrentMember(): ?array` — Get logged-in member data
- `requireAuth(): array` — Guard: redirect to login if not authenticated
- `getCsrfToken(): string` / `verifyCsrf(string): bool` — CSRF protection
- `checkRateLimit(string $email): bool` — Login rate limiting
- `verifyEmail(string $token): bool` — Confirm email
- `requestPasswordReset(string $email): bool` — Send reset link
- `resetPassword(string $token, string $newPassword): bool` — Execute reset
- `changePassword(int $memberId, string $current, string $new): bool`
- `onLogin(callable $callback): void` — Register post-login hook
- `startAuthenticatedSession(array $member): void` — Establish session (records site connection)
- `getSiteRole(): string` — Current user's role (super_admin, admin, manager, support, member)
- `isSuperAdmin()`, `isSiteAdmin()`, `isSiteManager()`, `isSiteSupport()` — Role checks
- `requireSiteRole(string $minRole): void` — Guard: minimum role required
- `buildCrossDomainUrl(string $targetUrl): string` — SSO token for cross-domain hops

### MemberProfile (static)
- `get(int $memberId): ?array` — Profile data (never exposes password hash)
- `update(int $memberId, array $data): bool` — Update display_name, username, bio, avatar_url
- `uploadAvatar(int $memberId, array $file): string` — Avatar upload
- `requestEmailChange(int $memberId, string $newEmail): bool`
- `getPreference / setPreference / getAllPreferences` — Per-site preferences
- `logActivity(...)` / `getActivity(int $memberId): array` — Activity audit trail

### MemberSSO (static)
- `isEnabled(): bool` — Check if SSO is configured
- `getAuthorizeUrl(?string $returnUrl): string` — Build OAuth URL with PKCE
- `handleCallback(string $code, string $state): array` — Exchange code for tokens + user
- `unlinkAccount(int $memberId): bool` — Remove SSO link

### MemberMail (static)
- `sendVerification(string $email, string $token, string $siteName, string $siteUrl): bool`
- `sendPasswordReset(string $email, string $token, string $siteName, string $siteUrl): bool`

### MemberSync (static)
- `reportActivity(int $hwUserId, string $siteDomain, string $action, ?array $details): bool`
- `syncProfile(int $hwUserId, array $profile): bool`

## Database Tables
- `members` — Core member records (email unique, password hash, username, status, email_verified_at)
- `member_sessions` — Active sessions (token hash, device_id, fingerprint, trusted, expires_at)
- `member_2fa` — TOTP secrets + backup codes
- `member_login_activity` — Login audit trail (method, IP, success/failure)
- `member_password_resets` — Reset tokens (hashed, single-use, TTL)
- `member_email_verifications` — Verification tokens
- `member_preferences` — Per-site key/value preferences
- `member_activity` — Activity log
- `member_site_connections` — Cross-site tracking (member_id + site_key, visit counts, timestamps)
- `member_anomaly_alerts` — Suspicious activity alerts
- `member_mobile_devices` — Mobile device registrations
- `member_webauthn_credentials` — WebAuthn public keys
- `member_magic_links` — Magic link tokens
- `user_site_roles` — Site-scoped role assignments (network mode)

## OAuth Column Contract
Any code in member-kit or site-level OAuth callbacks that references columns on the `users`/`members` table MUST follow these rules:
1. **Required columns for Google OAuth**: `google_id`, `google_email`, `google_name`, `google_avatar`, `google_connected_at`, `google_updated_at`, `auth_provider`, `full_name` — must exist via migration before code that uses them is deployed
2. **All column-dependent queries in OAuth flows must be wrapped in try-catch** — missing columns degrade gracefully, never crash the login flow
3. **INSERT must have a minimal fallback** — if the full INSERT (with google_* columns) fails, fall back to basic INSERT with only `username`, `email`, `status`, `created_at`
4. **`user_connections` is always optional** — every query against it must be try-caught since not all sites have it
5. **Migration-first rule**: Schema changes go to server BEFORE code that depends on them. Never deploy PHP that references new columns without running the migration first
6. **Shared table warning**: `users` table is shared by hiphop.world and 1vsm.com. Any ALTER TABLE affects both

## Deployment
- Deployed to `/home/hiphopwo/shared/member-kit/`
- deploy.sh available: yes
- Boilerplate for new sites: `templates/site-auth-boilerplate/` (auth.php, login.php, members.php)
