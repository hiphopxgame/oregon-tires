# Oregon Tires Auto Care - Improvements List

> Prioritized improvements based on a full code review of the public site, admin dashboard, .htaccess, and database schema. Each item includes severity, confidence level, and a suggested fix.

---

## Critical (Fix Immediately)

### 1. Add Rate Limiting / Brute-Force Protection on Admin Login
**Severity:** Critical | **Confidence:** 90%

The `/admin/` login form has no rate limiting, CAPTCHA, or account lockout. Attackers can brute-force passwords indefinitely.

**Fix:**
- Add hCaptcha or Cloudflare Turnstile to the login form
- Track failed login attempts in a Supabase table and lock after 5 failures in 15 minutes
- Use a generic error message ("Invalid credentials") to prevent email enumeration
- Remove the hardcoded superadmin email from client-side code

---

### 2. Force HTTPS Redirect
**Severity:** Critical | **Confidence:** 85%

No HTTPS redirect configured. Admin login credentials could be sent in plaintext over HTTP.

**Fix:** Add to top of `.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

---

## High Priority (Fix This Week)

### 3. Add Missing SEO Meta Tags
**Confidence:** 100%

Missing: `og:url`, `og:image`, Twitter Card tags, canonical URL, robots meta tag. Social media shares will have no preview image.

**Fix:** Add to `<head>`:
```html
<link rel="canonical" href="https://oregontires.com/">
<meta property="og:url" content="https://oregontires.com/">
<meta property="og:image" content="https://oregontires.com/assets/og-image.jpg">
<meta property="og:site_name" content="Oregon Tires Auto Care">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Oregon Tires Auto Care">
<meta name="twitter:image" content="https://oregontires.com/assets/og-image.jpg">
<meta name="robots" content="index, follow">
```

**Action Required:** Create a 1200x630px branded OG image.

---

### 4. Create robots.txt and sitemap.xml
**Confidence:** 100%

Neither file exists. Search engines can't efficiently crawl the site and crawl budget is wasted on admin pages.

**Fix:** Create `public_html/robots.txt`:
```
User-agent: *
Allow: /
Disallow: /admin/
Sitemap: https://oregontires.com/sitemap.xml
```

Create `public_html/sitemap.xml` with URLs for `/`, `/#services`, `/#about`, `/#contact`.

---

### 5. Add Image Lazy Loading
**Confidence:** 95%

Gallery images load immediately on page load regardless of scroll position. No `loading="lazy"`, no responsive `srcset`, no modern image formats (WebP).

**Fix:**
- Add `loading="lazy" decoding="async"` to all gallery `<img>` tags
- Add `loading="eager" fetchpriority="high"` to the logo (above the fold)
- Convert images to WebP for ~80% size reduction

---

### 6. Fix Render-Blocking CDN Scripts
**Confidence:** 90%

Tailwind CSS and Supabase SDK loaded in `<head>` without `async` or `defer`, blocking first paint by 500-800ms.

**Fix (quick):** Add `defer` to both script tags.

**Fix (production):** Build Tailwind CSS at compile time (`npx tailwindcss -o styles.css --minify`) and load as a `<link>` stylesheet. This eliminates the CDN dependency entirely.

---

### 7. Fix Accessibility: ARIA Labels and Focus Management
**Confidence:** 85%

- Mobile menu button has no `aria-label` or `aria-expanded`
- Language toggle has no `aria-label`
- Admin modals have no `role="dialog"`, no focus trap, no Escape key close
- No skip-to-content link for keyboard users

**Fix:** Add `aria-label`, `aria-expanded`, `aria-controls` to interactive elements. Add focus trap to modals. Add skip-to-content link after `<body>`.

---

### 8. Fix Color Contrast Failures
**Confidence:** 90%

- Yellow-400 button with black text = 1.9:1 ratio (WCAG requires 4.5:1)
- Yellow-200 hover text on dark green = 2.8:1 ratio (fails)

**Fix:** Use `text-gray-900 font-bold` on yellow buttons. Use `yellow-300` instead of `yellow-200` for hover states.

---

### 9. Add Form Validation Feedback and Loading State
**Confidence:** 95%

Contact form has no real-time validation, no loading spinner during submission, and the submit button doesn't disable (allowing double-submit).

**Fix:**
- Disable submit button and show "Sending..." during async submission
- Add `pattern` attributes for email and phone validation
- Re-enable button in `finally` block

---

## Medium Priority (Fix This Month)

### 10. Extract Inline JavaScript to Separate Files
**Confidence:** 85%

`index.html` has 264 lines of inline JS. `admin/index.html` has 900+ lines. This prevents browser caching, minification, and requires `unsafe-inline` in CSP.

**Fix:** Extract to `/js/main.js`, `/js/admin.js`, and shared `/js/supabase-client.js`. Load with `<script type="module" src="...">`.

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

### 13. Add Analytics and Error Tracking
**Confidence:** 90%

No Google Analytics, no error tracking (Sentry), no visibility into user behavior, form conversion rates, or JavaScript errors in production.

**Fix:** Add Google Analytics 4 with custom events for form submissions, language switches, and service views. Add Sentry for JS error tracking.

---

### 14. Enhance Structured Data (Schema.org)
**Confidence:** 85%

Missing: `url`, `image`, `geo` coordinates, `aggregateRating`, `sameAs` (social profiles), service catalog.

**Fix:** Add complete AutomotiveBusiness schema with geo coordinates (45.46123, -122.57895), aggregate rating (4.8/5, 150+ reviews), social profiles, and service offerings.

---

### 15. Lazy-Load Google Maps
**Confidence:** 80%

Google Maps iframe loads immediately (~500KB), even for users who never scroll to the contact section.

**Fix:** Use IntersectionObserver to load the iframe only when the contact section enters the viewport. Show a "Click to load map" placeholder until then.

---

### 16. Add Loading Skeletons for Gallery
**Confidence:** 80%

Gallery shows plain "Loading gallery..." text during Supabase fetch. No visual feedback.

**Fix:** Replace with animated skeleton cards (`animate-pulse bg-gray-200 rounded-xl h-64`) that disappear when images load.

---

### 17. Block Access to Sensitive Files via .htaccess
**Confidence:** 85%

No protection for `.sql`, `.env`, `.git`, `package.json`, or backup files if accidentally uploaded.

**Fix:** Add to `.htaccess`:
```apache
<FilesMatch "(\.sql|\.env|\.git|package\.json|\.bak|\.tmp)$">
    Require all denied
</FilesMatch>
```

---

## Low Priority (Nice to Have)

### 18. Standardize Database Table Naming
**Confidence:** 85%

Inconsistent prefixes: `oretir_profiles`, `oregon_tires_contact_messages`, `customer_vehicles` (no prefix).

**Fix:** Standardize all tables to `oretir_` prefix.

---

### 19. Make All Phone Numbers Click-to-Call
**Confidence:** 95%

Phone number in top bar is a `<span>`, not an `<a href="tel:">`. Mobile users can't tap to call.

**Fix:** Wrap all phone numbers in `<a href="tel:5033679714">`.

---

### 20. Add Skip-to-Content Link
**Confidence:** 80%

Keyboard users must tab through the entire navigation to reach main content.

**Fix:** Add a visually hidden link after `<body>` that becomes visible on focus: `<a href="#main-content" class="sr-only focus:not-sr-only">Skip to main content</a>`.

---

### 21. Add Service Worker for Offline Support
**Confidence:** 80%

Site completely breaks offline. Users can't view business hours or phone number from a cached page.

**Fix:** Create `/sw.js` that caches the homepage, logo, and hero image for offline access.

---

### 22. Consider Dark Mode Support
Detect system preference with `prefers-color-scheme: dark` and apply Tailwind dark mode classes.

---

### 23. Add Web Vitals Monitoring
Track Core Web Vitals (LCP, FID, CLS) to measure real-user performance and identify regressions.

---

### 24. Prevent Password Logging on Auth Errors
**Confidence:** 80%

Login error handling could inadvertently log credentials. Use generic error messages and avoid `console.error(err)` on auth failures.

---

## Priority Action Plan

| Week | Focus | Items |
|------|-------|-------|
| **1** | Security + SEO | HTTPS redirect, rate limiting, robots.txt, sitemap.xml, OG image, meta tags, block sensitive files |
| **2** | UX + Accessibility | ARIA labels, focus management, form validation, color contrast, click-to-call, skip-to-content |
| **3** | Performance | Image lazy loading, render-blocking scripts, lazy-load Maps, gallery skeletons |
| **4** | Code Quality + Admin | Extract inline JS, bulk operations, analytics, structured data, service worker |

---

*Generated from a full code review of 5 files: index.html, admin/index.html, book-appointment/index.html, .htaccess, and database schema.*
