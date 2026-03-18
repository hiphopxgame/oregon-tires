# Oregon Tires Auto Care — Improvements List

> Prioritized improvements based on full code reviews. Each item includes severity, confidence level, and suggested fix.

---

## Completed (34 of 43 original items)

| # | Item | Completed |
|---|------|-----------|
| 1 | Rate limiting / brute-force protection on admin login | 2026-02-19 |
| 2 | Force HTTPS redirect | 2026-02-15 |
| 3 | SEO meta tags (OG, Twitter, canonical, hreflang, JSON-LD) | 2026-02-19 |
| 4 | robots.txt and sitemap.xml | 2026-02-19 |
| 5 | Image lazy loading | 2026-02-19 |
| 6 | Fix render-blocking CDN scripts | 2026-02-15 |
| 7 | ARIA labels and focus management | 2026-02-19 |
| 8 | Color contrast WCAG AA | 2026-02-19 |
| 9 | Form validation feedback + loading state | 2026-02-19 |
| 10 | Extract inline JS to separate files | 2026-02-22 |
| 11 | Complete Spanish translations | 2026-02-15 |
| 13 | Analytics and error tracking | 2026-03-01 |
| 14 | Structured data (Schema.org) | 2026-02-19 |
| 17 | Block sensitive files via .htaccess | 2026-02-15 |
| 18 | Standardize database table naming | 2026-02-22 |
| 19 | Click-to-call phone numbers | 2026-02-19 |
| 20 | Skip-to-content link | 2026-02-19 |
| 21 | Service worker + PWA (enhanced 2026-03-17 with push, offline, install) | 2026-02-15 |
| 22 | Dark mode support | 2026-02-19 |
| 23 | Web Vitals monitoring | 2026-02-19 |
| 27 | Loyalty points system | 2026-03-17 |
| 30 | Appointment text/push reminders | 2026-03-17 |
| 35 | Digital invoices | 2026-03-17 |
| 36 | Automated service reminders | 2026-03-17 |
| 37 | Labor tracking | 2026-03-17 |
| 38 | Customer referral program | 2026-03-17 |
| 39 | Waitlist / walk-in queue | 2026-03-17 |
| 40 | Tire quote requests | 2026-03-17 |
| 41 | Enhanced admin analytics | 2026-03-17 |
| 42 | Google Business sync | 2026-03-17 |
| 43 | PWA push notifications + offline booking | 2026-03-17 |
| 44 | Email inbox integration (IMAP fetch + threading) | 2026-03-18 |
| 45 | Admin nav dropdown fix (CSS gap + JS click-to-toggle) | 2026-03-18 |

---

## Remaining (Original Items)

### 12. Add Bulk Operations to Admin Panel
**Confidence:** 85% | **Priority:** Medium

Admins must update each appointment individually. No bulk select, bulk assign, bulk status change, or CSV export.

**Fix:** Add checkbox column to appointments table, bulk action toolbar with assign/status/export buttons.

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

### 24. Prevent Password Logging on Auth Errors
**Confidence:** 80% | **Priority:** Low

Login error handling could inadvertently log credentials. Use generic error messages and avoid `console.error(err)` on auth failures.

---

## Remaining (Added 2026-03-03)

### 25. WhatsApp Integration
**Confidence:** 90% | **Priority:** High — See Roadmap R3

### 26. Google Calendar Sync
**Confidence:** 85% | **Priority:** Medium — See Roadmap R4

### 28. Online Payment for Estimates
**Confidence:** 90% | **Priority:** High — See Roadmap R1

### 29. Inventory / Parts Tracking
**Confidence:** 80% | **Priority:** Medium — See Roadmap R2

### 31. Customer Photo Upload in Portal
**Confidence:** 75% | **Priority:** Low

Customers sometimes want to show the shop what issue they're experiencing before visiting.

**Fix:** Add photo upload field to booking form and member portal messages. Store in `uploads/customer/` with customer_id prefix. Display in appointment detail view.

---

### 32. Multi-Location Support
**Confidence:** 70% | **Priority:** Deferred (future)

Currently single-location. If the shop expands, the system would need multi-location awareness.

**Fix:** Add `location_id` to appointments, ROs, employees. Location selector in admin. Separate schedules per location.

---

### 33. Customer Vehicle History Timeline
**Confidence:** 85% | **Priority:** Medium — See Roadmap R5

### 34. Automated Follow-Up Sequences
**Confidence:** 85% | **Priority:** Medium — See Roadmap R6

---

## New Improvements (2026-03-18)

### 46. Technician Mobile View
**Confidence:** 90% | **Priority:** Medium — See Roadmap R7

Technicians use the full admin panel on phones, which is clunky for field work. A simplified mobile-first tech view would improve efficiency for inspections, photo capture, and status updates.

---

### 47. QR Code Walk-In Check-In
**Confidence:** 85% | **Priority:** Medium — See Roadmap R8

Walk-in customers wait to be manually checked in. A QR code at the entrance lets them self-register to the waitlist from their phone.

---

### 48. Seasonal Tire Storage Tracking
**Confidence:** 85% | **Priority:** Medium — See Roadmap R9

Portland customers swap tires seasonally but there's no system to track stored tire sets, locations, or swap reminders.

---

### 49. Customer Satisfaction Surveys (CSAT/NPS)
**Confidence:** 90% | **Priority:** Medium — See Roadmap R10

No structured feedback mechanism beyond Google reviews. Post-service surveys with scoring would provide actionable data and catch unhappy customers before they leave bad public reviews.

---

### 50. Appointment Deposit / No-Show Reduction
**Confidence:** 80% | **Priority:** Low — See Roadmap R11

No-shows waste bay capacity. Deposit requirements for high-value services would reduce no-shows and protect revenue.

---

### 51. Video Inspections
**Confidence:** 75% | **Priority:** Low — See Roadmap R12

Photos sometimes don't convey the full issue (e.g., suspension noise, engine vibration). Short video clips alongside DVI photos would improve customer understanding and trust.

---

## Completion Summary

**35 of 51 items completed** as of 2026-03-18.

| Status | Count | Items |
|--------|-------|-------|
| Completed | 35 | #1–11, #13, #14, #17–23, #27, #30, #35–45 |
| Remaining (original) | 4 | #12, #15, #16, #24 |
| Remaining (2026-03-03) | 3 | #25, #28, #29 (→ Roadmap R1–R3) |
| Remaining (2026-03-03, standalone) | 2 | #31 (customer photos), #32 (multi-location) |
| Remaining (2026-03-03, → Roadmap) | 2 | #33 (→ R5), #34 (→ R6) |
| New (2026-03-18, → Roadmap) | 6 | #46–51 (→ R7–R12) |

### Priority Matrix (Remaining)

| Priority | Items | Description |
|----------|-------|-------------|
| **High** | #25, #28, #29 | WhatsApp, online estimate payment, inventory |
| **Medium** | #12, #15, #16, #26, #33, #34, #46, #47, #48, #49 | Bulk ops, lazy maps, skeletons, calendar sync, vehicle timeline, follow-ups, tech mobile, QR check-in, tire storage, CSAT |
| **Low** | #24, #31, #50, #51 | Password logging, customer photos, deposits, video inspections |
| **Deferred** | #32 | Multi-location (future expansion) |

---

*Generated from code reviews. Last updated 2026-03-18.*
