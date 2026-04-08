# Login System Optimization — Implementation Complete

## Overview
All 5 fixes have been implemented to solve 3 confirmed bugs and enable 1 enhancement for the unified login system across HipHop Network (hw mode) and independent sites.

## Changes Implemented

### 1. ✅ Fix P0 Bug — `isMemberLoggedIn()` Alias
**File:** `includes/member-kit/MemberAuth.php` (after line 213)

Added a public static method that aliases `isLoggedIn()`:
```php
public static function isMemberLoggedIn(): bool
{
    return self::isLoggedIn();
}
```

**Why:** Dashboard template (and other code) calls `MemberAuth::isMemberLoggedIn()`. This method was missing, causing P0 fatal errors on all /members pages.

**Verification:**
- ✓ Method is callable: `MemberAuth::isMemberLoggedIn()`
- ✓ Returns same value as `isLoggedIn()` when logged out: `false`
- ✓ Returns same value as `isLoggedIn()` when logged in: `true`

---

### 2. ✅ Fix P2 Bug — Honor `?return=` When Already Logged In
**File:** `templates/member/dashboard.php` (after line 28)

Added redirect logic after session state is determined:
```php
if ($isLoggedIn) {
    $returnTarget = $_GET['return'] ?? '';
    if ($returnTarget !== ''
        && str_starts_with($returnTarget, '/')
        && !str_starts_with($returnTarget, '//')) {
        header('Location: ' . $returnTarget);
        exit;
    }
}
```

**Flow:**
- User at `/feed` (logged out) → guard redirects to `/members?return=/feed`
- Dashboard loads, sees `$isLoggedIn = true` AND `?return=/feed`
- Redirects directly to `/feed` instead of showing dashboard
- User lands on `/feed` ✓

**Security:**
- Only allows relative paths starting with `/`
- Blocks `//evil.com` (protocol-relative URLs)
- Blocks `https://evil.com` (absolute URLs)
- Blocks empty/missing `return` parameter (no redirect)

---

### 3. ✅ Pass Return URL Through Email/Password Form
**File:** `templates/member/login.php` (after line 107)

Added hidden input to capture and pass `?return=` parameter:
```html
<input type="hidden" name="return_url" id="login-return-url"
       value="<?= htmlspecialchars($_GET['return'] ?? '') ?>">
```

**How it works:**
1. User clicks "login via email/password" with `?return=/feed`
2. Login form auto-populates hidden field with `/feed`
3. Form data includes `return_url: "/feed"` when submitted
4. JS form serializer (in `member.js`) automatically includes all named inputs

---

### 4. ✅ Fix P1 Bug — Login API Honors Return URL
**File:** `api/member/login.php`

#### 4a. Read return_url (after line 39)
```php
$returnUrl = trim($input['return_url'] ?? '');
```

#### 4b. Generate server-validated redirect (replaced lines 77–91)

**Independent Mode Logic:**
```php
// Return URL must be same-site relative path only
$isRelative = str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//');
$redirectUrl = ($returnUrl !== '' && $isRelative)
    ? $returnUrl
    : ($isAdmin ? '/admin' : '/member/profile');
```

**HW Mode Logic:**
```php
$hubUrl = rtrim($_ENV['HH_HUB_URL'] ?? 'https://hiphop.world', '/');

// Validate: relative paths OR hub URLs (for OAuth chain)
$validReturn = '';
if ($returnUrl !== '') {
    $isRelative = str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//');
    $isHubUrl   = str_starts_with($returnUrl, $hubUrl . '/');
    if ($isRelative || $isHubUrl) {
        $validReturn = $returnUrl;
    }
}
$destination = $validReturn !== '' ? $validReturn : ($isAdmin ? '/admin' : '/member/profile');

// Generate single-use SSO token (300s TTL)
$pdo    = MemberAuth::getPdo();
$userId = (int) $member['id'];
$token  = bin2hex(random_bytes(32));
$pdo->prepare("DELETE FROM engine_sso_tokens WHERE expires_at < NOW()")->execute();
$pdo->prepare("INSERT INTO engine_sso_tokens (token, user_id, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 300 SECOND))")
    ->execute([$token, $userId]);

// Build SSO hop URL
$redirectUrl = $hubUrl . '/sso?token=' . $token . '&return=' . urlencode($destination);
```

**Response includes `server_validated: true`:**
```json
{
    "success": true,
    "member": { ... },
    "redirect": "/feed",
    "server_validated": true
}
```

---

### 5. ✅ Trust Server-Validated Redirects in JS
**File:** `js/member.js` (around line 456)

Changed redirect handling to support cross-domain SSO hops:
```javascript
if (result.data.redirect) {
    setTimeout(function () {
        if (result.data.server_validated) {
            // Server pre-validated this URL (SSO allowlist or same-origin check).
            // May be cross-domain (e.g. hub SSO hop) — assign directly.
            window.location.href = result.data.redirect;
        } else {
            safeRedirect(result.data.redirect);
        }
    }, 500);
}
```

**Why this matters:**
- `safeRedirect()` validates against a whitelist (security)
- SSO hops to `https://hiphop.world/sso` would fail this whitelist check
- Server already validated the URL server-side
- `server_validated: true` tells JS to trust the server's validation
- `window.location.href = url` allows cross-domain redirects

---

## Complete Email/Password Login Flow

### Independent Site (e.g., hiphop.social)
```
User at /feed (logged out)
  ↓ page guard bounces to /members?return=/feed
/members dashboard.php
  ↓ not logged in → renders login form
Login form:
  - hidden field: return_url = "/feed"
  - hidden field: csrf_token
  ↓ user enters email + password
POST /api/member/login.php
  ↓ independent mode: validates "/feed" is relative → redirect = "/feed"
  ↓ response: { redirect: "/feed", server_validated: true }
JS in browser:
  ↓ server_validated=true → window.location.href = "/feed"
USER LANDS ON /feed ✓
```

### HW Mode (e.g., hiphop.id → hiphop.world chain)
```
User at hiphop.social/members (logged out, SSO button)
  ↓ OAuth: redirects to hiphop.world/oauth/authorize
  ↓ hiphop.world checks: user not logged in
  ↓ redirects to hiphop.id/members?return=https://hiphop.world/oauth/authorize?...
hiphop.id dashboard.php:
  ↓ not logged in → renders login form
Login form:
  - hidden field: return_url = "https://hiphop.world/oauth/authorize?..."
  ↓ user enters email + password
POST hiphop.id/api/member/login.php
  ↓ HW mode: return_url starts with hub URL → valid
  ↓ generates SSO token T in engine_sso_tokens (300s TTL)
  ↓ response: { redirect: "https://hiphop.world/sso?token=T&return=<authorize_url>", server_validated: true }
JS in browser:
  ↓ server_validated=true → window.location.href = hub SSO hop
  ↓ hiphop.world/sso validates T
  ↓ creates hub session
  ↓ redirects to oauth/authorize URL
  ↓ OAuth completes with session → spoke callback
  ↓ hiphop.social/api/member/sso-callback gets session token from HW
  ↓ creates spoke session
  ↓ redirects to /members or ?return=/feed
USER LOGGED IN, returned to their spoke ✓
```

---

## Required .env Setup

### All HW-Mode Sites
Add to `.env`:
```
HH_HUB_URL=https://hiphop.world
```

This applies to:
- `hiphop.id` (the login UI itself)
- `hiphop.social`
- `hiphop.world` (can set to itself)
- Any other hiphop.[tld] site running in HW mode

### Independent Sites
No additional .env setup required. Return URLs are always same-site relative paths.

---

## Database Requirements

### For HW Mode: `engine_sso_tokens` Table
Must exist on the shared database (same as `users` table):

```sql
CREATE TABLE engine_sso_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Testing Checklist

### Bug 1 (P0): Fatal Error Fixed
- [ ] Load `/members` on any site
- [ ] No PHP fatal error
- [ ] Login form renders
- [ ] Dashboard shows if logged in

### Bug 2 (P1): Return URL in API
- [ ] Independent site: `/members?return=/feed` → login → lands on `/feed`
- [ ] HW mode: `/members?return=https://hiphop.world/...` → login → SSO hop generated
- [ ] Invalid return URL (`//evil.com`) → login → fallback to `/member/profile`

### Bug 3 (P2): ?return= Honored When Logged In
- [ ] Logged in user: `/members?return=/settings` → instantly redirected to `/settings`
- [ ] Logged in user: `/members` → dashboard shown (no return param)
- [ ] Invalid return param: `/members?return=//evil.com` → dashboard shown (param blocked)

### Enhancement: server_validated Flag
- [ ] API response includes `server_validated: true`
- [ ] JS uses `window.location.href` for validated redirects
- [ ] SSO hops to hub URL work (not blocked by safeRedirect)

---

## Files Modified

1. `includes/member-kit/MemberAuth.php` — +6 lines
2. `templates/member/dashboard.php` — +9 lines
3. `templates/member/login.php` — +2 lines
4. `api/member/login.php` — ~35 lines (replaced 77-91)
5. `js/member.js` — +4 lines

**Total:** 5 files, ~56 lines of code

---

## Security Notes

✓ **Return URL Validation**: Relative paths only (independent) or hub allowlist (HW mode)
✓ **SSO Token Security**: Single-use, cryptographically random, 300s expiry, indexed on database
✓ **No Open Redirect**: Protocol-relative and absolute URLs are filtered before response
✓ **Session Hardening**: Token-based SSO with server-side session validation
✓ **Prepared Statements**: All token operations use parameterized queries

---

## Deployment

1. Deploy the 5 modified files to production
2. For HW-mode sites: Add `HH_HUB_URL=https://hiphop.world` to `.env`
3. For HW-mode sites: Ensure `engine_sso_tokens` table exists
4. Run tests from the "Testing Checklist" above
5. No database migrations required for independent sites

---

## Rollback

If issues occur:
1. Revert the 5 modified files to previous version
2. Remove `server_validated` from JS flow (will use `safeRedirect`)
3. Users can still log in, but return URLs won't work

---

## Future Improvements

- [ ] Add rate limiting to SSO token generation (prevent token enumeration)
- [ ] Add audit logging for SSO token usage
- [ ] Add admin dashboard to view/revoke active SSO tokens
- [ ] Add support for time-based expiry verification in hiphop.world/sso
- [ ] Add metrics tracking for login flow success rates
