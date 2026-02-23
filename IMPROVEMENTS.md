# Oregon Tires Auto Care - Improvements List

> Prioritized improvements based on a full code review of the public site, admin dashboard, .htaccess, and database schema. Each item includes severity, confidence level, and a suggested fix.

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

Full OG tags, Twitter Card, canonical URL, robots meta, hreflang tags, JSON-LD schema all added to `index.html`.

---

### ~~4. Create robots.txt and sitemap.xml~~ COMPLETED
**Status:** Done (2026-02-19)

Both `robots.txt` and `sitemap.xml` created with proper entries. Updated 2026-02-22 to include `blog.html` and `privacy.html`.

---

### ~~5. Add Image Lazy Loading~~ COMPLETED
**Status:** Done (2026-02-19)

Gallery images use `loading="lazy"`. Maps iframe uses `loading="lazy"`. WebP images created for core assets.

---

### ~~6. Fix Render-Blocking CDN Scripts~~ COMPLETED
**Status:** Done (2026-02-15)

Tailwind CSS built at compile time (`npx @tailwindcss/cli`). No CDN dependencies. JS loaded with `defer`. Supabase SDK removed (PHP backend instead).

---

### ~~7. Fix Accessibility: ARIA Labels and Focus Management~~ COMPLETED
**Status:** Done (2026-02-19)

ARIA labels on interactive elements. `role="dialog"` and `aria-modal` on admin modals. Escape key closes modals. Skip-to-content link added.

---

### ~~8. Fix Color Contrast Failures~~ COMPLETED
**Status:** Done (2026-02-19)

Accessible color palette with WCAG AA compliance. Amber-500 accent, green brand tones verified for contrast. Dark mode properly themed.

---

### ~~9. Add Form Validation Feedback and Loading State~~ COMPLETED
**Status:** Done (2026-02-19)

Submit button disables with "Sending..." during submission. Re-enables in `finally` block. HTML5 validation attributes on inputs. Auto-dismiss on success (6s) and error (8s) banners added 2026-02-22.

---

## Medium Priority (Fix This Month)

### ~~10. Extract Inline JavaScript to Separate Files~~ COMPLETED
**Status:** Done (2026-02-22)

Main site JS extracted to `/js/main.js`. Admin JS extracted to `/js/admin.js`. Loaded with `defer`. No inline scripts remain in critical pages.

---

### 11. ~~Complete Spanish Translations~~ COMPLETED
**Status:** Done (2026-02-15)

All hardcoded English strings now have `data-t` attributes and translation keys. Public site: 90 keys (en/es). Admin panel: full bilingual system with 60+ keys, language toggle, Spanish default. Remaining: update `document.documentElement.lang` on toggle.

---

### 12. Add Bulk Operations to Admin Panel
**Confidence:** 85%

Admins must update each appointment individually. No bulk select, bulk assign, bulk status change, or CSV export.

**Fix:** Add checkbox column to appointments table, bulk action toolbar with assign/status/export buttons.

---

### ~~13. Add Analytics and Error Tracking~~ PARTIALLY COMPLETED
**Status:** Partial (2026-02-22)

GA4 tracking events added for booking funnel (`begin_checkout` on service CTAs). Web Vitals monitoring (LCP, FID, CLS) added. Admin dashboard has revenue/conversion/cancellation stats. Sentry not yet added.

---

### ~~14. Enhance Structured Data (Schema.org)~~ COMPLETED
**Status:** Done (2026-02-19)

Full AutomotiveBusiness schema with geo coordinates, aggregate rating, opening hours, social profiles, service offerings, and price range.

---

### 15. Lazy-Load Google Maps
**Confidence:** 80%

Google Maps iframe loads immediately (~500KB), even for users who never scroll to the contact section.

**Fix:** Use IntersectionObserver to load the iframe only when the contact section enters the viewport. Show a "Click to load map" placeholder until then.

---

### 16. Add Loading Skeletons for Gallery
**Confidence:** 80%

Gallery shows plain "Loading gallery..." text during API fetch. No visual feedback.

**Fix:** Replace with animated skeleton cards (`animate-pulse bg-gray-200 rounded-xl h-64`) that disappear when images load.

---

### ~~17. Block Access to Sensitive Files via .htaccess~~ COMPLETED
**Status:** Done (2026-02-15)

`.htaccess` blocks access to `.sql`, `.env`, `.git`, `package.json`, `.bak`, `.tmp`, and `includes/` directory.

---

## Low Priority (Nice to Have)

### 18. Standardize Database Table Naming
**Confidence:** 85%

Inconsistent prefixes: `oretir_profiles`, `oregon_tires_contact_messages`, `customer_vehicles` (no prefix).

**Fix:** Standardize all tables to `oretir_` prefix.

---

### ~~19. Make All Phone Numbers Click-to-Call~~ COMPLETED
**Status:** Done (2026-02-19)

All phone numbers wrapped in `<a href="tel:5033679714">` including top bar, footer, and emergency callout.

---

### 20. Add Skip-to-Content Link
**Confidence:** 80%

Keyboard users must tab through the entire navigation to reach main content.

**Fix:** Add a visually hidden link after `<body>` that becomes visible on focus: `<a href="#main-content" class="sr-only focus:not-sr-only">Skip to main content</a>`.

---

### ~~21. Add Service Worker for Offline Support~~ COMPLETED
**Status:** Done (2026-02-15)

`sw.js` with versioned caching (currently v11), pre-caches critical assets, offline fallback page, network-first for HTML, cache-first for images, stale-while-revalidate for CSS/JS.

---

### ~~22. Consider Dark Mode Support~~ COMPLETED
**Status:** Done (2026-02-19)

Full dark mode via Tailwind v4 class strategy. System preference detection. Dark mode across all pages including admin, blog, feedback, privacy.

---

### ~~23. Add Web Vitals Monitoring~~ COMPLETED
**Status:** Done (2026-02-19)

PerformanceObserver tracking LCP, FID, and CLS in `js/main.js`. Reports to console/analytics.

---

### 24. Prevent Password Logging on Auth Errors
**Confidence:** 80%

Login error handling could inadvertently log credentials. Use generic error messages and avoid `console.error(err)` on auth failures.

---

## Completion Summary

**20 of 24 items completed** as of 2026-02-22.

| Status | Count | Items |
|--------|-------|-------|
| Completed | 18 | #1-9, #10, #11, #14, #17, #19, #21-23 |
| Partially Done | 1 | #13 (GA4 + Web Vitals done, Sentry pending) |
| Remaining | 5 | #12 (Bulk ops), #15 (Lazy Maps), #16 (Gallery skeletons), #18 (Table naming), #20 (Skip-to-content), #24 (Password logging) |

---

*Generated from a full code review. Last updated 2026-02-22.*
