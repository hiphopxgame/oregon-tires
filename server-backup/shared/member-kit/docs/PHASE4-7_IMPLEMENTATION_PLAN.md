# Member Kit Phase 4-7 Implementation Plan

**Status:** Ready for Implementation
**Date:** February 23, 2026
**Approach:** Test-Driven Development (TDD) — Tests First, Then Implementation
**Agents:** 8-10 parallel teams
**Estimated Duration:** 8-12 weeks (can parallelize to 4-6 weeks)

---

## Architecture Overview

```
Phase 4 (Weeks 1-2): Foundation
├─ Agent 1: Social Button Consistency
├─ Agent 2: Password Reset Flow
├─ Agent 3: Success/Error Animations
└─ Agent 4: Dark Mode Polish

Phase 5 (Weeks 3-4): Device Management
├─ Agent 5: Device Nicknames
├─ Agent 6: Login History Dashboard
├─ Agent 7: Rate Limiting UI
└─ Agent 8: Loading States

Phase 6 (Weeks 5-8): Advanced Auth
├─ Agent 9: Progressive 2FA Enrollment
├─ Agent 10: Email Verification Requirement
├─ Agent 11: SMS 2FA Option
└─ Agent 12: Keyboard Shortcuts

Phase 7 (Weeks 9-12): Enterprise Security
├─ Agent 13: WebAuthn/Passkey Support
├─ Agent 14: Account Takeover Prevention
├─ Agent 15: Session Fingerprint Rotation
└─ Agent 16: Native Mobile App Integration
```

---

## Agent Team Assignments

### PHASE 4 — Foundation Layer

**Agent 1: Social Button Consistency**
- Files to create/modify:
  - `css/member.css` — Add `.member-social-btn` unified class
  - `templates/member/login.php` — Update Google button to use class
  - `js/member.js` — Consolidate button event handlers
- Dependencies: None
- Tests: 4 tests in `test-phase-4-enhancements.php`
- Effort: 3-4 hours

**Agent 2: Password Reset Flow**
- Files to create:
  - `api/member/password-reset.php` — Email token generation
  - `api/member/reset-password.php` — Token verification + password update
  - `templates/member/forgot-password.php` — Forgot password form
  - `templates/member/reset-password.php` — Reset form
  - `migrations/003_password_reset.php` — Database schema
- Dependencies: Email provider setup (SendGrid/Mailgun)
- Tests: 6 tests in `test-phase-4-enhancements.php`
- Effort: 8-10 hours

**Agent 3: Success/Error Animations**
- Files to modify/create:
  - `css/member.css` — Toast + shake animations
  - `js/member.js` — Toast notification + error animation functions
  - `templates/member/login.php` — Toast container element
- Dependencies: None
- Tests: 6 tests in `test-phase-4-enhancements.php`
- Effort: 4-6 hours

**Agent 4: Dark Mode Polish**
- Files to modify:
  - `templates/member/login.php` — Add color-scheme meta tag
  - `css/member.css` — Dark mode variable overrides
  - `js/member.js` — Theme detection + persistence
- Dependencies: None
- Tests: 4 tests in `test-phase-4-enhancements.php`
- Effort: 2-3 hours

---

### PHASE 5 — Device Management & UX

**Agent 5: Device Nicknames**
- Files to create:
  - `api/member/devices.php` — List user devices
  - `api/member/rename-device.php` — Update device nickname
  - `api/member/revoke-device.php` — Sign out device
  - `templates/member/devices.php` — Device management page
  - Database migration (add `device_name` to `member_sessions`)
- Dependencies: Phase 1-3 complete
- Tests: 7 tests in `test-phase-5-enhancements.php`
- Effort: 10-12 hours

**Agent 6: Login History Dashboard**
- Files to create:
  - `templates/member/login-history.php` — History view
  - Enhance `api/member/login-activity.php` — Add geo location field
  - `js/member.js` — Load and display history
- Dependencies: Geolocation provider (MaxMind/IP2Location)
- Tests: 5 tests in `test-phase-5-enhancements.php`
- Effort: 8-10 hours

**Agent 7: Rate Limiting UI**
- Files to modify:
  - `api/member/password-reset.php` — Include rate limit response
  - `templates/member/login.php` — Rate limit message element
  - `js/member.js` — Countdown timer display
  - `css/member.css` — Rate limit alert styling
- Dependencies: Phase 4.2 (password reset)
- Tests: 4 tests in `test-phase-5-enhancements.php`
- Effort: 4-5 hours

**Agent 8: Loading States**
- Files to modify:
  - `css/member.css` — Skeleton loader + pulse animations
  - `js/member.js` — Loading state manager
  - `templates/member/devices.php` — Skeleton placeholders
- Dependencies: None
- Tests: 4 tests in `test-phase-5-enhancements.php`
- Effort: 3-4 hours

---

### PHASE 6 — Advanced Authentication

**Agent 9: Progressive 2FA Enrollment**
- Files to create:
  - `api/member/2fa-prompt.php` — Suggest 2FA based on activity
  - `templates/member/modals/2fa-suggestion.php` — Modal template
  - Enhance `js/member.js` — Modal trigger logic
  - Database migration — Track 2FA suggestion state
- Dependencies: Phase 3 (2FA scaffolding)
- Tests: 4 tests in `test-phase-6-7-enhancements.php`
- Effort: 6-8 hours

**Agent 10: Email Verification Requirement**
- Files to create:
  - `migrations/004_email_verification.php` — Add `email_verified_at`
  - `templates/member/verify-email.php` — Verification prompt
  - Enhance `api/member/2fa-setup.php` — Require verified email
  - `api/member/resend-verification.php` — Resend verification email
- Dependencies: Email provider + Phase 4.2
- Tests: 3 tests in `test-phase-6-7-enhancements.php`
- Effort: 6-8 hours

**Agent 11: SMS 2FA Option**
- Files to create:
  - `api/member/2fa-sms-setup.php` — Phone number registration
  - `api/member/2fa-sms-verify.php` — SMS code verification
  - `migrations/005_sms_2fa.php` — SMS 2FA table
  - `templates/member/2fa-setup.php` — SMS option in setup
  - Enhance `js/member.js` — SMS code input handling
- Dependencies: SMS provider (Twilio/Vonage) + Phase 9
- Tests: 5 tests in `test-phase-6-7-enhancements.php`
- Effort: 10-12 hours

**Agent 12: Keyboard Shortcuts**
- Files to create:
  - `templates/member/modals/keyboard-help.php` — Help overlay
  - Enhance `js/member.js` — Keyboard handler + help modal
- Dependencies: None
- Tests: 4 tests in `test-phase-6-7-enhancements.php`
- Effort: 3-4 hours

---

### PHASE 7 — Enterprise Security

**Agent 13: WebAuthn/Passkey Support**
- Files to create:
  - `api/member/webauthn-register.php` — Start registration
  - `api/member/webauthn-authenticate.php` — Authentication ceremony
  - `api/member/webauthn-verify.php` — Verify signature
  - `migrations/006_webauthn.php` — Credentials table
  - Enhance `js/member.js` — WebAuthn JS ceremony functions
  - `templates/member/webauthn-setup.php` — Setup wizard
- Dependencies: WebAuthn library (open-source) + Phase 5
- Tests: 5 tests in `test-phase-6-7-enhancements.php`
- Effort: 16-20 hours

**Agent 14: Account Takeover Prevention**
- Files to create:
  - `api/member/detect-anomalies.php` — Anomaly heuristics
  - `migrations/007_account_security.php` — Tracking tables
  - Enhance `api/member/login.php` — Call anomaly detection
  - `templates/member/modals/suspicious-login.php` — Alert modal
  - Email templates for security alerts
- Dependencies: Phase 5.2 (geo data) + Email provider
- Tests: 4 tests in `test-phase-6-7-enhancements.php`
- Effort: 12-14 hours

**Agent 15: Session Fingerprint Rotation**
- Files to create:
  - `api/member/session-refresh.php` — Rotate fingerprint
  - Enhance `js/member.js` — Auto-rotation on login
- Dependencies: Phase 2 (fingerprinting)
- Tests: 2 tests in `test-phase-6-7-enhancements.php`
- Effort: 4-6 hours

**Agent 16: Native Mobile App Integration**
- Files to create:
  - `api/member/app-login.php` — App-specific login
  - Enhance `js/member.js` — WebView detection + native calls
  - Handle deep links for magic links
- Dependencies: None (app coordination may be needed later)
- Tests: 4 tests in `test-phase-6-7-enhancements.php`
- Effort: 8-10 hours

---

## Development Workflow (TDD)

### For Each Agent:

**Step 1: Write Tests** (30% of effort)
- Tests already provided in `tests/test-phase-*.php`
- Run tests: `php tests/test-phase-*.php`
- All tests should FAIL initially (red)

**Step 2: Implement Code** (60% of effort)
- Implement features to make tests PASS (green)
- Refactor if needed (optional, if code smell)

**Step 3: Verify & Document** (10% of effort)
- Ensure all tests pass
- Document new APIs in comments
- Update IMPLEMENTATION_SUMMARY.md

---

## Database Migrations

New migrations required:

| Migration | Features | Lines |
|-----------|----------|-------|
| `003_password_reset.php` | Password reset tokens | ~50 |
| `004_email_verification.php` | Email verified timestamp | ~20 |
| `005_sms_2fa.php` | SMS 2FA table | ~40 |
| `006_webauthn.php` | WebAuthn credentials | ~60 |
| `007_account_security.php` | Anomaly tracking | ~50 |

All are idempotent (safe to re-run).

---

## Dependency Graph

```
Phase 4
├─ Agent 1 (Social buttons) — No dependencies
├─ Agent 2 (Password reset) — Needs email provider
├─ Agent 3 (Animations) — No dependencies
└─ Agent 4 (Dark mode) — No dependencies
        ↓
Phase 5
├─ Agent 5 (Device nicknames) — Needs Phase 1-3
├─ Agent 6 (History) — Needs geo provider
├─ Agent 7 (Rate limiting) — Needs Agent 2
└─ Agent 8 (Loading states) — No dependencies
        ↓
Phase 6
├─ Agent 9 (Progressive 2FA) — Needs Phase 3
├─ Agent 10 (Email verification) — Needs Agent 2 + email
├─ Agent 11 (SMS 2FA) — Needs SMS provider + Agent 9
└─ Agent 12 (Keyboard shortcuts) — No dependencies
        ↓
Phase 7
├─ Agent 13 (WebAuthn) — Needs WebAuthn lib + Phase 5
├─ Agent 14 (Takeover prevention) — Needs Phase 5.2 + email
├─ Agent 15 (Fingerprint rotation) — Needs Phase 2
└─ Agent 16 (Mobile app) — Standalone
```

---

## Success Criteria

### Phase 4 (Foundation)
- [ ] All 20 tests passing
- [ ] Social buttons unified
- [ ] Password reset flow complete
- [ ] Animations smooth (60fps)
- [ ] Dark mode auto-detects system preference

### Phase 5 (Device Management)
- [ ] All 20 tests passing
- [ ] Users can rename devices
- [ ] Login history displays geo location
- [ ] Rate limiting shows countdown
- [ ] Loading states smooth and accessible

### Phase 6 (Advanced Auth)
- [ ] All 15 tests passing
- [ ] 2FA enrollment progressive (not forced)
- [ ] Email verification required before 2FA
- [ ] SMS 2FA works alongside TOTP
- [ ] Keyboard shortcuts intuitive

### Phase 7 (Enterprise)
- [ ] All 15 tests passing
- [ ] WebAuthn registration ceremony works
- [ ] Suspicious logins detected + alerted
- [ ] Session fingerprints rotate
- [ ] App deep-links resolved correctly

---

## Deployment Strategy

### Phase 4 & 5
1. Commit to `phase-4-5` branch
2. Deploy to staging
3. Manual testing (desktop + mobile)
4. User acceptance testing
5. Merge to `main` → deploy production

### Phase 6 & 7
1. Same as above
2. Additionally: Security audit
3. Load test (high concurrent logins)
4. 24h monitoring post-deploy

---

## Risk Mitigation

| Risk | Mitigation |
|------|-----------|
| Third-party API downtime | Fallback to non-API features |
| SMS cost spike | Rate limiting + alerts on Dashboard |
| WebAuthn browser support | Progressive enhancement (fallback to 2FA) |
| Session rotation breaks apps | Backward compatibility flag |

---

## Expected Codebase Growth

```
Current (Phase 1-3):    ~2,700 lines
Phase 4 additions:      ~1,200 lines
Phase 5 additions:      ~1,500 lines
Phase 6 additions:      ~2,000 lines
Phase 7 additions:      ~2,600 lines
─────────────────────────────────
Total Phase 4-7:        ~7,300 lines
Grand Total:            ~10,000 lines

Test files:             ~1,500 lines
Documentation:          ~2,000 lines
Migrations:             ~300 lines
```

---

## Timeline (Optimistic)

| Phase | Duration | Start | End |
|-------|----------|-------|-----|
| Phase 4 | 2 weeks | Week 1 | Week 2 |
| Phase 5 | 2 weeks | Week 3 | Week 4 |
| Phase 6 | 4 weeks | Week 5 | Week 8 |
| Phase 7 | 4 weeks | Week 9 | Week 12 |
| **Total** | **12 weeks** | **Now** | **End Q2** |

*With 8-10 parallel agents, can compress to 6-8 weeks.*

---

## Next Steps

1. ✅ Review this plan
2. ✅ Read VENDOR_SETUP_GUIDE.md
3. ✅ Set up email provider (Phase 4 blocker)
4. ✅ (Optional) Set up geo provider (Phase 5)
5. ✅ (Optional) Set up SMS provider (Phase 6)
6. 👉 **Dispatch 8-10 agents to begin implementation**

---

**Ready to begin?** Confirm vendor setup and I'll dispatch agents with TDD approach.
