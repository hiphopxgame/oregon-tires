# Oregon Tires Auto Care — Improvements List

> Prioritized improvements based on full code reviews. Each item includes severity, confidence level, and suggested fix.

---

## Critical (Fix Immediately)

### ~~1. Add Rate Limiting / Brute-Force Protection on Admin Login~~ COMPLETED
**Status:** Done (2026-02-19)

Account lockout after 5 failed attempts for 15 minutes (`locked_until` column). Generic error messages. DB-backed rate limiting. No hardcoded emails in client-side code.

---

### ~~2. Force HTTPS Redirect~~ COMPLETED
**Status:** Done (2026-02-15)

HTTPS redirect configured in `.htaccess` with `RewriteCond %{HTTPS} off` rule.

---

## High Priority (Fix This Week)

### ~~3. Add Missing SEO Meta Tags~~ COMPLETED
**Status:** Done (2026-02-19)

Full OG tags, Twitter Card, canonical URL, robots meta, hreflang tags, JSON-LD schema all added.

---

### ~~4. Create robots.txt and sitemap.xml~~ COMPLETED
**Status:** Done (2026-02-19)

Both created with proper entries. Updated 2026-02-22 to include blog and privacy pages.

---

### ~~5. Add Image Lazy Loading~~ COMPLETED
**Status:** Done (2026-02-19)

Gallery images use `loading="lazy"`. Maps iframe uses `loading="lazy"`. WebP images created for core assets.

---

### ~~6. Fix Render-Blocking CDN Scripts~~ COMPLETED
**Status:** Done (2026-02-15)

Tailwind CSS built at compile time. No CDN dependencies. JS loaded with `defer`.

---

### ~~7. Fix Accessibility: ARIA Labels and Focus Management~~ COMPLETED
**Status:** Done (2026-02-19)

ARIA labels on interactive elements. `role="dialog"` and `aria-modal` on modals. Escape key closes modals. Skip-to-content link added.

---

### ~~8. Fix Color Contrast Failures~~ COMPLETED
**Status:** Done (2026-02-19)

Accessible color palette with WCAG AA compliance. Dark mode properly themed.

---

### ~~9. Add Form Validation Feedback and Loading State~~ COMPLETED
**Status:** Done (2026-02-19)

Submit button disables with "Sending..." during submission. HTML5 validation attributes. Auto-dismiss banners.

---

## Medium Priority (Fix This Month)

### ~~10. Extract Inline JavaScript to Separate Files~~ COMPLETED
**Status:** Done (2026-02-22)

Main site JS extracted to `/js/main.js`. Admin JS extracted to `/js/admin.js`. Loaded with `defer`.

---

### ~~11. Complete Spanish Translations~~ COMPLETED
**Status:** Done (2026-02-15)

All strings have `data-t` attributes. Public site: 90+ keys. Admin panel: full bilingual system with 60+ keys.

---

### 12. Add Bulk Operations to Admin Panel
**Confidence:** 85% | **Priority:** Medium

Admins must update each appointment individually. No bulk select, bulk assign, bulk status change, or CSV export.

**Fix:** Add checkbox column to appointments table, bulk action toolbar with assign/status/export buttons.

---

### ~~13. Add Analytics and Error Tracking~~ COMPLETED
**Status:** Done (2026-03-01)

GA4 tracking events for booking funnel. Web Vitals monitoring (LCP, FID, CLS). Admin analytics dashboard. Error tracking via engine-kit (3-tier: Sentry → DB → error_log). Admin error log API deployed.

---

### ~~14. Enhance Structured Data (Schema.org)~~ COMPLETED
**Status:** Done (2026-02-19)

Full AutomotiveBusiness schema with geo coordinates, aggregate rating, opening hours, social profiles, service offerings, and price range.

---

### 15. Lazy-Load Google Maps
**Confidence:** 80% | **Priority:** Medium

Google Maps iframe loads immediately (~500KB), even for users who never scroll to the contact section.

**Fix:** Use IntersectionObserver to load the iframe only when the contact section enters the viewport. Show a "Click to load map" placeholder until then.

---

### 16. Add Loading Skeletons for Gallery
**Confidence:** 80% | **Priority:** Medium

Gallery shows plain "Loading gallery..." text during API fetch. No visual feedback.

**Fix:** Replace with animated skeleton cards (`animate-pulse bg-gray-200 rounded-xl h-64`) that disappear when images load.

---

### ~~17. Block Access to Sensitive Files via .htaccess~~ COMPLETED
**Status:** Done (2026-02-15)

`.htaccess` blocks access to `.sql`, `.env`, `.git`, `package.json`, `.bak`, `.tmp`, and `includes/` directory.

---

## Low Priority (Nice to Have)

### ~~18. Standardize Database Table Naming~~ COMPLETED
**Status:** Done (2026-02-22)

All tables standardized to `oretir_` prefix during Phase 1 shop management build.

---

### ~~19. Make All Phone Numbers Click-to-Call~~ COMPLETED
**Status:** Done (2026-02-19)

All phone numbers wrapped in `<a href="tel:...">`.

---

### ~~20. Add Skip-to-Content Link~~ COMPLETED
**Status:** Done (2026-02-19)

Skip-to-content link added as part of accessibility improvements (#7).

---

### ~~21. Add Service Worker for Offline Support~~ COMPLETED
**Status:** Done (2026-02-15), Enhanced (2026-03-17)

`sw.js` v21 with versioned caching, pre-caches critical assets + booking page, bilingual offline fallback page (`offline.html`). **Enhanced (2026-03-17):** Push notifications (Web Push API + VAPID), offline booking via IndexedDB + Background Sync, install prompt (Android + iOS), notification click routing, online/offline indicator, notification preferences, admin broadcast.

---

### ~~22. Consider Dark Mode Support~~ COMPLETED
**Status:** Done (2026-02-19)

Full dark mode via Tailwind v4 class strategy. System preference detection.

---

### ~~23. Add Web Vitals Monitoring~~ COMPLETED
**Status:** Done (2026-02-19)

PerformanceObserver tracking LCP, FID, and CLS.

---

### 24. Prevent Password Logging on Auth Errors
**Confidence:** 80% | **Priority:** Low

Login error handling could inadvertently log credentials. Use generic error messages and avoid `console.error(err)` on auth failures.

---

## New Improvements (Added 2026-03-03)

### 25. WhatsApp Integration
**Confidence:** 90% | **Priority:** Medium

Many Spanish-speaking customers prefer WhatsApp over SMS/email. Currently no WhatsApp channel.

**Fix:** Integrate WhatsApp Business API (via Twilio or Meta) for inspection reports, estimate links, ready notifications, and appointment reminders. Add WhatsApp opt-in to booking form alongside SMS.

---

### 26. Google Calendar Sync
**Confidence:** 85% | **Priority:** Medium

Admin calendar endpoints exist (`calendar-health.php`, `calendar-test-sync.php`, `calendar-retry-sync.php`) but full Google Calendar sync is not yet wired up.

**Fix:** Complete Google Calendar API integration so appointments auto-sync to a shared Google Calendar. Technicians can see their schedule on their phones.

---

### 27. Loyalty Points System
**Confidence:** 85% | **Priority:** Medium

No reward mechanism for repeat customers beyond care plans.

**Fix:** Add points-per-dollar on completed ROs. Redeemable for discounts on future services. Points balance visible in member portal. Tier bonuses for care plan members.

---

### 28. Online Payment for Estimates
**Confidence:** 90% | **Priority:** High

Customers can approve estimates but cannot pay online. Must pay in-person.

**Fix:** Add "Approve & Pay" option on estimate approval page. Integrate with existing commerce-kit checkout flow. Allow deposit or full payment. Send receipt via email.

---

### 29. Inventory / Parts Tracking
**Confidence:** 80% | **Priority:** Medium

No parts inventory system. Technicians and service writers track parts manually.

**Fix:** Add `oretir_inventory` table (part number, name, quantity, cost, supplier, reorder point). Link parts from estimates to inventory. Low-stock alerts. Supplier order integration.

---

### 30. Appointment Text Message Reminders
**Confidence:** 90% | **Priority:** High

Cron sends email reminders but not SMS reminders. Many customers miss email.

**Fix:** Add SMS reminders to the 6 PM cron job for customers who opted into SMS. Use existing Twilio integration.

---

### 31. Customer Photo Upload in Portal
**Confidence:** 75% | **Priority:** Low

Customers sometimes want to show the shop what issue they're experiencing before visiting.

**Fix:** Add photo upload field to booking form and member portal messages. Store in `uploads/customer/` with customer_id prefix. Display in appointment detail view.

---

### 32. Multi-Location Support
**Confidence:** 70% | **Priority:** Low (future)

Currently single-location. If the shop expands, the system would need multi-location awareness.

**Fix:** Add `location_id` to appointments, ROs, employees. Location selector in admin. Separate schedules per location.

---

### 33. Customer Vehicle History Timeline
**Confidence:** 85% | **Priority:** Medium

No unified view of a vehicle's full service history. ROs, inspections, and estimates are separate views.

**Fix:** Add timeline view in member portal showing all RO/inspection/estimate history per vehicle in chronological order. Include status badges, cost summaries, and links to inspection/estimate detail pages.

---

### 34. Automated Follow-Up Sequences
**Confidence:** 85% | **Priority:** Medium

Currently only single-touch emails (reminder, review request). No multi-step follow-up sequences.

**Fix:** Add multi-step email sequences after service completion: thank you (day 0) → review request (day 3) → return reminder with promotion (day 30). Configurable per service type. Unsubscribe support.

---

## Completion Summary

**23 of 34 items completed** as of 2026-03-17.

| Status | Count | Items |
|--------|-------|-------|
| Completed | 23 | #1–11, #13, #14, #17–23 |
| Remaining (original) | 4 | #12 (Bulk ops), #15 (Lazy maps), #16 (Gallery skeletons), #24 (Password logging) |
| New (2026-03-03) | 8 | #25–32 |
| New (2026-03-17) | 2 | #33–34 |

### Priority Matrix

| Priority | Items | Description |
|----------|-------|-------------|
| **High** | #28, #30 | Online estimate payment, SMS reminders |
| **Medium** | #12, #15, #16, #25, #26, #27, #29, #33, #34 | Bulk ops, lazy maps, skeletons, WhatsApp, calendar sync, loyalty, inventory, vehicle timeline, follow-up sequences |
| **Low** | #24, #31, #32 | Password logging, customer photos, multi-location |

---

*Generated from code reviews. Last updated 2026-03-17.*
