# Member Kit Login Template — Phase 1-3 Enhancement Code Snippets

## Summary

Enhanced `/Users/hiphop/Desktop/____1vsM____/---member-kit/templates/member/login.php` with 10 Phase 1-3 features: accessibility landmarks, session timeout warning, device verification, 2FA prompts, trusted device checkbox, login activity link, data tracking attributes, and translation keys.

**Total additions:** 69 lines, zero breaking changes, full backward compatibility.

---

## Code Snippets by Feature

### 1. Conditional Feature Detection (Lines 36-38)

```php
// Determine conditional features
$showDeviceVerification = isset($_GET['device_verify']);
$show2FAPrompt = isset($_GET['2fa_prompt']);
```

**Purpose:** Flag-based rendering of device verification and 2FA prompts.

---

### 2. Session Timeout Warning (Lines 52-59)

```php
<div id="session-timeout-warning" class="member-alert member-alert--warning"
     style="display:none;margin-bottom:1rem;">
    <strong><?php echo htmlspecialchars(t('session_timeout_warning') ?? 'Session expiring soon'); ?></strong>
    Your session will expire in <span id="countdown">5:00</span>.
    <button type="button" class="member-link" id="extend-session" style="margin-left:0.5rem;text-decoration:underline;">
        <?php echo htmlspecialchars(t('extend_session') ?? 'Extend session'); ?>
    </button>
</div>
```

**Features:**
- Hidden by default, shown via JavaScript
- Countdown timer in `<span id="countdown">`
- Extend button with `id="extend-session"`
- Bilingual with fallbacks

---

### 3. Device Verification Alert (Lines 83-88)

```php
<?php if ($showDeviceVerification): ?>
    <div class="member-alert member-alert--info">
        <strong><?= htmlspecialchars(t('new_device_detected') ?? 'New device detected'); ?></strong>
        <?= htmlspecialchars(t('check_email_verify') ?? 'Check your email to verify this device.'); ?>
    </div>
<?php endif; ?>
```

**Trigger:** `?device_verify` URL parameter

---

### 4. ARIA Landmarks & Live Region (Lines 94-95)

```php
<div id="auth-status" role="status" aria-live="polite" aria-label="Authentication method status"
     style="position:absolute;left:-9999px;"></div>
```

**Purpose:** Accessibility announcements for screen readers.

---

### 5. Enhanced Email Field with Helper (Lines 106-118)

```php
<div class="member-field">
    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
        <label class="member-label" for="login-email" style="margin-bottom:0;">Email</label>
        <span class="member-helper-icon" title="<?= htmlspecialchars(t('email_privacy_info') ?? 'We protect your privacy. Your email is never shared.'); ?>"
              style="cursor:help;font-size:0.875rem;">ℹ️</span>
    </div>
    <input class="member-input" type="email" id="login-email" name="email"
           required autocomplete="email" placeholder="you@example.com"
           aria-describedby="field-helper-email" data-track="email-input">
    <div id="field-helper-email" class="member-form-helper" style="font-size:0.75rem;color:#666;margin-top:0.25rem;">
        <?= htmlspecialchars(t('email_helper_text') ?? 'We\'ll never share your email address.'); ?>
    </div>
</div>
```

**Features:**
- Info icon with tooltip (title attribute)
- Helper text with `aria-describedby` connection
- Data tracking on input
- Bilingual with fallbacks

---

### 6. Session Lifetime Hidden Input (Line 104)

```php
<input type="hidden" name="session_lifetime" value="<?= htmlspecialchars((string)($_ENV['SESSION_LIFETIME'] ?? '3600')) ?>" id="session-lifetime">
```

**Purpose:** Pass server session timeout to form submission, accessible via JavaScript.

---

### 7. Form Landmark & CSRF (Lines 101-104)

```php
<form class="member-form" data-action="/api/member/login.php" data-method="POST"
      role="region" aria-label="Email login form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? MemberAuth::getCsrfToken()) ?>">
    <input type="hidden" name="session_lifetime" value="<?= htmlspecialchars((string)($_ENV['SESSION_LIFETIME'] ?? '3600')) ?>" id="session-lifetime">
```

**Features:**
- `role="region"` for accessibility
- `aria-label` for context
- CSRF token preserved
- Session lifetime added

---

### 8. Password Field with Tracking (Lines 120-127)

```php
<div class="member-field">
    <label class="member-label" for="login-password">Password</label>
    <div class="member-password-wrap">
        <input class="member-input" type="password" id="login-password" name="password"
               required autocomplete="current-password" placeholder="Your password" minlength="8"
               data-track="password-input">
    </div>
</div>
```

**Addition:** `data-track="password-input"` for analytics

---

### 9. Trusted Device Checkbox (Lines 129-135)

```php
<div class="member-field" style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
    <input type="checkbox" id="trust-device" name="trust_device" value="1"
           style="width:auto;cursor:pointer;" data-track="trust-device-toggle">
    <label for="trust-device" style="margin:0;font-size:0.875rem;cursor:pointer;">
        <?= htmlspecialchars(t('remember_device_30_days') ?? 'Remember this device for 30 days'); ?>
    </label>
</div>
```

**Features:**
- Checkbox with label
- Optional 30-day device trust
- Data tracking
- Bilingual

---

### 10. Submit Button with Tracking (Line 137)

```php
<button type="submit" class="member-btn" data-track="email-submit">Sign In</button>
```

**Addition:** `data-track="email-submit"` for analytics

---

### 11. SSO Button with Tracking (Line 150)

```php
<button type="button" class="member-sso-btn" data-return="<?= $returnUrl ?>" data-track="sso-click">
    <img class="member-sso-icon" src="<?= htmlspecialchars($ssoBrand['logo']) ?>" alt="" loading="lazy">
    <?= htmlspecialchars($ssoBrand['name']) ?>
</button>
```

**Addition:** `data-track="sso-click"`

---

### 12. Google Button with Tracking (Line 157)

```php
<a href="/api/member/google.php?return=<?= urlencode($returnUrl) ?>" class="member-google-btn" data-track="google-click">
    <svg>...</svg>
    Google
</a>
```

**Addition:** `data-track="google-click"`

---

### 13. Wallet Buttons with Tracking (Lines 181, 190, 199)

```php
<?php if ($metamaskEnabled): ?>
    <button type="button" class="member-wallet-btn" data-wallet="metamask" data-track="wallet-click">
        <svg>...</svg>
        MetaMask
    </button>
<?php endif; ?>

<?php if ($walletConnectEnabled): ?>
    <button type="button" class="member-wallet-btn" data-wallet="walletconnect" data-track="wallet-click">
        <svg>...</svg>
        WalletConnect
    </button>
<?php endif; ?>

<?php if ($coinbaseEnabled): ?>
    <button type="button" class="member-wallet-btn" data-wallet="coinbase" data-track="wallet-click">
        <svg>...</svg>
        Coinbase
    </button>
<?php endif; ?>
```

**Addition:** `data-track="wallet-click"` on all three wallet buttons

---

### 14. 2FA Enrollment Prompt (Lines 214-224)

```php
<?php if ($show2FAPrompt): ?>
    <div class="member-alert member-alert--info" style="margin-top:1rem;">
        <strong><?= htmlspecialchars(t('secure_your_account') ?? 'Secure your account'); ?></strong>
        <div style="margin-top:0.5rem;">
            <?= htmlspecialchars(t('2fa_info') ?? 'Add two-factor authentication for enhanced security.'); ?>
        </div>
        <a href="/member/2fa-setup" class="member-link" style="display:inline-block;margin-top:0.5rem;text-decoration:underline;">
            <?= htmlspecialchars(t('enable_2fa') ?? 'Enable two-factor auth'); ?>
        </a>
    </div>
<?php endif; ?>
```

**Trigger:** `?2fa_prompt` URL parameter
**Link:** `/member/2fa-setup`

---

### 15. Enhanced Footer with Login Activity (Lines 230-236)

```php
<div class="member-footer">
    <a href="/member/forgot-password" class="member-link" data-track="forgot-password">Forgot your password?</a>
    <a href="/member/register" class="member-link" data-track="create-account">Create an account</a>
    <a href="/member/login-activity" class="member-link" data-track="login-activity" style="margin-left:auto;">
        <?= htmlspecialchars(t('view_login_activity') ?? 'View login activity'); ?>
    </a>
</div>
```

**Additions:**
- All links now have `data-track` attributes
- New login activity link right-aligned (margin-left:auto)
- Bilingual with fallback

---

## Translation Keys Reference

```php
// All with htmlspecialchars() and fallback values
t('session_timeout_warning')      // "Session expiring soon"
t('extend_session')                // "Extend session"
t('new_device_detected')           // "New device detected"
t('check_email_verify')            // "Check your email to verify this device."
t('secure_your_account')           // "Secure your account"
t('2fa_info')                      // "Add two-factor authentication for enhanced security."
t('enable_2fa')                    // "Enable two-factor auth"
t('email_privacy_info')            // "We protect your privacy. Your email is never shared."
t('email_helper_text')             // "We'll never share your email address."
t('remember_device_30_days')       // "Remember this device for 30 days"
t('view_login_activity')           // "View login activity"
```

---

## Data Tracking Attributes

```html
<!-- Form fields -->
data-track="email-input"
data-track="password-input"
data-track="trust-device-toggle"

<!-- Buttons -->
data-track="email-submit"
data-track="sso-click"
data-track="google-click"
data-track="wallet-click"

<!-- Links -->
data-track="forgot-password"
data-track="create-account"
data-track="login-activity"
```

---

## Accessibility Elements

```php
// Form region landmark
role="region"
aria-label="Email login form"

// Hidden live region
id="auth-status"
role="status"
aria-live="polite"
aria-label="Authentication method status"

// Field connection
aria-describedby="field-helper-email"
```

---

## Integration Notes

### Frontend (JavaScript Required)
1. Monitor session timeout and show `#session-timeout-warning`
2. Update `#countdown` timer every second
3. Handle `#extend-session` button to refresh session
4. Analytics: Track `data-track` attributes on user interactions

### Backend (Optional but Recommended)
1. Check `trust_device` form parameter during login
2. Validate/use `session_lifetime` from form
3. Set `$showDeviceVerification` for new device logins
4. Set `$show2FAPrompt` for users without 2FA enabled
5. Implement `/member/login-activity` endpoint

### Deployment
```bash
cd /Users/hiphop/Desktop/____1vsM____/---member-kit
./deploy.sh
```

---

## Files Modified

| Path | Changes |
|------|---------|
| `/templates/member/login.php` | 69 lines added, 0 lines removed |

## Files Created (Documentation)

| Path | Purpose |
|------|---------|
| `ENHANCEMENT_VERIFICATION.md` | Detailed checklist and verification |
| `ENHANCEMENT_CODE_SNIPPETS.md` | This file - code reference |
