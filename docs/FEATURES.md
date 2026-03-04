# Oregon Tires Auto Care — Feature Inventory

> **Last updated:** 2026-03-03
> **Stack:** PHP + MySQL + Tailwind CSS v4 + PHPMailer | cPanel/Apache
> **Live:** https://oregon.tires
> **Admin:** https://oregon.tires/admin/

---

## Summary

| Category | Features | Priority |
|----------|----------|----------|
| Core Shop Operations | 4 systems | P0 |
| Customer Management | 3 systems | P0 |
| Customer Communication | 4 systems | P1 |
| Revenue & Subscriptions | 2 systems | P1 |
| Content & Marketing | 6 systems | P2 |
| Public Website | 4 systems | P2 |
| Infrastructure | 7 systems | P3 |
| **Total** | **~30 systems, 70+ API endpoints, 29 pages** | |

---

## Group 1: Core Shop Operations (P0 — Revenue-Critical)

### Appointment Booking

**Pages:** `/book-appointment/`, `/cancel/{token}`, `/reschedule/{token}`
**API:** `POST /api/book.php`, `GET /api/available-times.php?date=YYYY-MM-DD`, `/api/appointment-cancel.php`, `/api/appointment-reschedule.php`, `/api/appointment-status.php`

- 15-minute slot granularity with multi-bay capacity
- Employee schedule-aware availability (blocks booked slots per technician)
- VIN decode built into booking form (auto-populates vehicle details)
- SMS opt-in checkbox during booking
- Auto-creates customer + vehicle records on first booking
- Service type selection (tire, brake, oil change, alignment, inspection, other)
- Bilingual confirmation emails
- Self-service cancel/reschedule via unique token links
- Calendar event generation (`/api/calendar-event.php`)
- Appointment statuses: New → Confirmed → Completed / Cancelled

### Repair Order (RO) Lifecycle

**Admin:** `Repair Orders` tab, `admin/js/repair-orders.js`, `admin/js/kanban.js`
**API:** `/api/admin/repair-orders.php`

- **11 statuses:** intake → diagnosis → estimate_pending → pending_approval → approved → in_progress → waiting_parts → ready → completed → invoiced (+ cancelled)
- Create from existing appointment (one-click conversion) or as walk-in
- Reference numbers: `RO-XXXXXXXX` (auto-generated)
- Status timeline visualization with timestamps
- Promised date tracking
- Mileage in/out recording
- **Kanban board:** Drag-and-drop status changes across columns, time-in-status display, column counts
- **Table view:** Sortable, filterable, searchable list with status badges
- Toggle between table and kanban views

### Digital Vehicle Inspection (DVI)

**Pages:** `/inspection/{token}` (customer view)
**API:** `/api/admin/inspections.php`, `/api/admin/inspection-photos.php`, `/api/inspection-view.php?token=`

- **35 template items** auto-populated across **12 categories:**
  - Tires (5): LF, RF, LR, RR, Spare
  - Brakes (4): Front Pads, Rear Pads, Front Rotors, Rear Rotors
  - Suspension (4): Front Struts/Shocks, Rear Struts/Shocks, Tie Rods, Ball Joints
  - Fluids (5): Engine Oil, Coolant, Brake Fluid, Transmission Fluid, Power Steering Fluid
  - Lights (4): Headlights, Tail Lights, Brake Lights, Turn Signals
  - Engine (3): Air Filter, Cabin Air Filter, Spark Plugs
  - Exhaust (2): Exhaust System, Catalytic Converter
  - Hoses (2): Coolant Hoses, Heater Hoses
  - Belts (2): Serpentine Belt, Timing Belt/Chain
  - Battery (2): Battery, Battery Terminals
  - Wipers (2): Front Wipers, Rear Wiper
  - Other (custom items)
- **Traffic light rating:** Green (good) / Yellow (needs attention) / Red (critical)
- Photo capture per item (upload via admin, stored in `uploads/inspections/{ro_number}/`)
- **Inspection statuses:** draft → in_progress → completed → sent
- **Customer view** (token-based, bilingual):
  - Overall health score ring (color-coded)
  - Items grouped by category with traffic light indicators
  - Photo overlay with prev/next navigation, keyboard (arrow keys) + swipe support
  - Booking CTA when no estimate exists
  - Print-friendly layout

### Estimates & Approval

**Pages:** `/approve/{token}` (customer approval)
**API:** `/api/admin/estimates.php`, `/api/estimate-approve.php?token=`

- Auto-generate from inspection red/yellow items (one-click)
- Reference numbers: `ES-XXXXXXXX` (auto-generated)
- **6 line item types:** labor, parts, tire, fee, discount, sublet
- Per-item approve/decline by customer (partial approval supported)
- **8 estimate statuses:** draft → sent → viewed → approved / partial / declined / expired / superseded
- Valid-until date with expiry tracking (cron sends reminders 2 days before)
- Priority cost summary: Safety / Recommended / Preventive categories
- **Customer approval page** (token-based, bilingual):
  - Customer greeting with vehicle info
  - Per-item toggle (approve/decline) with running total
  - Link back to inspection report
  - Dynamic button labels based on selection state
  - Print-friendly layout
- Admin receives notification on customer approval/decline
- Approval confirmation email sent to customer

---

## Group 2: Customer Management (P0)

### Customer Records

**API:** `/api/admin/customers.php`

- Auto-created from appointments (persistent, deduplicated by email)
- Searchable customer list with name, email, phone
- Language preference (English/Spanish) stored per customer
- Linked vehicles with full history
- Appointment history per customer
- Manual create/edit/delete

### Vehicle Management

**API:** `/api/admin/vehicles.php`, `/api/admin/vin-decode.php`, `/api/admin/tire-fitment.php`

- VIN decode via NHTSA vPIC API with permanent DB cache (`oretir_vin_cache`)
- Auto-populates: year, make, model, trim, engine, body style
- Tire fitment lookup with 90-day DB cache (`oretir_tire_fitment_cache`)
- Fields: VIN, year/make/model/trim/engine, tire sizes (front/rear), tire pressure, color, license plate
- Multiple vehicles per customer
- Public VIN decode rate-limited (10/hr per IP); admin unlimited

### Member Portal

**Page:** `/members`
**API:** `/api/member/*` (17 endpoints)

- Customer login/register via Member Kit integration
- Google OAuth sign-in option (`/api/member/google.php`, `/api/member/google-callback.php`)
- Password reset flow (`/api/member/forgot-password.php`, `/api/member/reset-password.php`)
- **Dashboard tabs:**
  - **Appointments:** View upcoming and past bookings (`/api/member/my-bookings.php`)
  - **Vehicles:** View/manage linked vehicles (`/api/member/my-vehicles.php`)
  - **Estimates:** View sent estimates, approve/decline (`/api/member/my-estimates.php`)
  - **Messages:** Contact history (`/api/member/my-messages.php`)
  - **Care Plan:** Current plan status and benefits (`/api/member/my-care-plan.php`)
- Profile management (`/api/member/profile.php`, `/api/member/password.php`)
- Google account linking/unlinking

---

## Group 3: Customer Communication (P1)

### Email System

**Core:** `includes/mail.php`
**API:** `/api/admin/email-logs.php`, `/api/admin/email-template-vars.php`

- PHPMailer + SMTP with DB-driven bilingual templates
- **Email functions:**
  - `sendBrandedTemplateEmail()` — generic branded template sender
  - `sendInspectionEmail()` — DVI report link to customer
  - `sendEstimateEmail()` — estimate link with approval token
  - `sendApprovalConfirmationEmail()` — confirms customer's estimate decision
  - `sendReadyEmail()` — vehicle ready for pickup notification
  - `notifyOwner()` — internal notification to shop owner
- **Template features:**
  - DB-stored templates editable via Settings tab
  - `{{variable}}` placeholder replacement
  - Bilingual sections (EN/ES side by side)
  - Template variable reference in admin
- Email audit trail: every sent email logged to `oretir_email_logs`
- SMTP debug level configurable via `.env` (`SMTP_DEBUG=0|1|2`)

### SMS System

**Core:** `includes/sms.php`

- Twilio integration with graceful fallback (no-op if unconfigured)
- **SMS functions:**
  - `sendInspectionSms()` — inspection report link
  - `sendEstimateSms()` — estimate link
  - `sendReadySms()` — vehicle ready notification
  - `sendApprovalConfirmationSms()` — approval confirmation

### Automated Cron Jobs

**Location:** `cli/`

| Schedule | Script | Purpose |
|----------|--------|---------|
| Daily 6:00 PM | `send-reminders.php` | Appointment reminders for next day |
| Daily 10:00 AM | `send-review-requests.php` | Review request emails (completed appointments) |
| Daily 10:00 AM | `send-estimate-reminders.php` | Estimate expiry reminders (2 days before valid_until) |
| On demand | `send-welcome-emails.php` | Onboarding/welcome emails |

### Appointment Self-Service

- **Cancel:** `/cancel/{token}` — one-click cancellation with confirmation
- **Reschedule:** `/reschedule/{token}` — pick new date/time
- Both pages bilingual (EN/ES)
- Token-based: no login required

---

## Group 4: Revenue & Subscriptions (P1)

### Care Plan Tiers

**Page:** `/care-plan`
**API:** `/api/care-plan-enroll.php`, `/api/care-plan-status.php`, `/api/care-plan-webhook.php`

- **3 tiers:**
  - Basic ($19/mo) — oil changes, basic discounts
  - Standard ($29/mo) — enhanced discounts, priority scheduling
  - Premium ($49/mo) — comprehensive coverage, maximum discounts
- Enrollment via checkout flow
- Webhook handling for recurring subscription events
- Member portal shows current plan status and benefits

### Checkout & Payments

**Page:** `/checkout`
**API:** `/api/commerce/*` (6 endpoints)

- PayPal integration (`/api/commerce/checkout.php`, `/api/commerce/paypal-webhook.php`)
- Credit card processing
- Cryptocurrency option (`/api/commerce/crypto-confirm.php`)
- Order management (`/api/commerce/orders.php`)
- Post-checkout return flow (`/api/commerce/checkout-return.php`)
- Commerce analytics (`/api/commerce/stats.php`)
- Webhook handling for payment events (`/api/commerce/webhook.php`)

---

## Group 5: Content & Marketing (P2)

### Blog

**Pages:** `/blog`, `/blog/{slug}`
**API:** `/api/admin/blog.php`, `/api/blog.php`
**Admin JS:** `admin/js/blog.js`

- Bilingual posts (EN/ES)
- Categories and tags
- Featured images
- Draft/published status
- Clean URL slugs (`/blog/my-post-title`)
- Admin CRUD with rich text editing

### Promotions

**API:** `/api/admin/promotions.php`, `/api/promotions.php`
**Admin JS:** `admin/js/promotions.js`

- Time-limited offers with start/end dates
- Bilingual title and description
- Image upload per promotion
- Active/inactive toggle
- Display on public site automatically during active period

### FAQ

**Pages:** `/faq/`
**API:** `/api/admin/faq.php`, `/api/faq.php`
**Admin JS:** `admin/js/faq.js`

- Bilingual Q&A entries
- Category grouping
- Sort order control
- Public FAQ page with accordion display

### Testimonials / Reviews

**API:** `/api/admin/testimonials.php`, `/api/testimonials.php`
**Admin JS:** `admin/js/testimonials.js`

- Customer reviews with star ratings (1-5)
- Bilingual content
- Featured toggle (highlighted on homepage)
- Customer name and service type
- 3 random reviews displayed on homepage per load

### Newsletter Subscribers

**API:** `/api/admin/subscribers.php`, `/api/subscribe.php`
**Admin JS:** `admin/js/subscribers.js`

- Email subscription form on public site
- Subscriber list with date and status
- Export capability

### Gallery & Service Images

**API:** `/api/admin/gallery.php`, `/api/admin/service-images.php`, `/api/gallery.php`, `/api/service-images.php`

- **Gallery:** Bilingual image uploads with title and description, language-filtered display
- **8 Service Image Slots:**

| Slot | Controls |
|------|----------|
| Hero Background | Homepage banner image |
| Expert Technicians | Feature card |
| Fast Cars | Feature card |
| Quality Parts | Feature card |
| Bilingual Support | Feature card |
| Tire Shop | Service section |
| Auto Repair | Service section |
| Specialized Tools | Service section |

- Position/crop controls (horizontal, vertical, zoom sliders)
- Live preview with "Live" badge indicator
- Fallback images for all slots

---

## Group 6: Public Website (P2)

### Service Detail Pages (8 pages)

Each page is bilingual with pricing info, FAQ section, and Schema.org markup.

| Page | URL |
|------|-----|
| Brake Service | `/brake-service` |
| Tire Installation | `/tire-installation` |
| Tire Repair | `/tire-repair` |
| Oil Change | `/oil-change` |
| Engine Diagnostics | `/engine-diagnostics` |
| Wheel Alignment | `/wheel-alignment` |
| Suspension Repair | `/suspension-repair` |
| Fleet Services | `/fleet-services` |

### Location Pages (8 pages)

Local SEO targeting for Portland neighborhoods.

| Page | URL |
|------|-----|
| Clackamas | `/tires-clackamas` |
| Foster-Powell | `/tires-foster-powell` |
| Happy Valley | `/tires-happy-valley` |
| Lents | `/tires-lents` |
| Milwaukie | `/tires-milwaukie` |
| Mt. Scott | `/tires-mt-scott` |
| SE Portland | `/tires-se-portland` |
| Woodstock | `/tires-woodstock` |

### Homepage

- Hero section with dynamic background image from API
- 7 service feature cards with dynamic images
- About section with shop story
- Reviews section (3 random reviews per load)
- Gallery section (language-filtered)
- Contact section with form + Google Maps embed
- Sticky header with smooth scroll navigation

### Site-Wide Features

- **Bilingual system:** EN/ES toggle via globe icon, `data-t` attribute translations, `currentLang` JS variable, language persisted across pages
- **Dark mode:** System preference detection + manual toggle, Tailwind v4 `@variant dark` strategy
- **PWA:** Service worker (`sw.js`) with versioned caching, offline fallback, network-first HTML, cache-first images
- **Responsive:** Mobile-first design with Tailwind breakpoints (320px+)
- **Accessibility:** ARIA labels, focus management, skip-to-content link, WCAG AA contrast
- **SEO:** OG tags, Twitter Cards, canonical URLs, hreflang (en/es), JSON-LD AutomotiveBusiness schema, robots.txt, sitemap.xml

### Additional Pages

| Page | URL | Purpose |
|------|-----|---------|
| About | `/about/` | Company information |
| Why Us | `/why-us` | Value proposition |
| Guarantee | `/guarantee` | Service warranty |
| Contact | `/contact` | Contact form |
| Feedback | `/feedback/` | Post-service feedback form |
| Status | `/status/` | System status page |
| 404 | `/404` | Error page |

---

## Group 7: Infrastructure (P3)

### Security

- CSRF token validation on all admin endpoints
- Bcrypt password hashing (cost 12)
- Session hardening with `session_regenerate_id(true)` on login
- Account lockout after 5 failed attempts (15-minute cooldown)
- Rate limiting on public API endpoints (VIN decode, tire fitment: 10/hr)
- `.htaccess` blocks sensitive files (.env, config.php, composer.*, includes/)
- No innerHTML — DOM manipulation via createElement/textContent
- API error handling: catch `\Throwable` in all endpoints

### Clean URLs

- `.php` extension stripping via `.htaccess` (301 redirect + internal rewrite)
- Token-based paths: `/inspection/{token}`, `/approve/{token}`, `/cancel/{token}`, `/reschedule/{token}`
- Blog slugs: `/blog/{slug}`
- API endpoints excluded from URL rewriting

### Image Optimization

- WebP + AVIF generation via build pipeline
- `.htaccess` content negotiation (serves best format based on `Accept` header)
- `responsiveImage()` PHP helper generates `<picture>` with AVIF/WebP/fallback sources
- Lazy loading on gallery and below-fold images

### Error Tracking

- 3-tier: Sentry (when configured) → DB (`engine_error_log`) → `error_log()`
- JS error capture via `window.__captureError()`
- Admin API for error log viewing (`/api/admin/error-log.php`)

### API Versioning

- `/api/v1/*` alias via `.htaccess` (maps to `/api/*`)
- `X-API-Version: v1` header on all JSON responses

### Cloudflare CDN

- `RemoteIPHeader CF-Connecting-IP` configured
- `Vary: Accept-Encoding` headers
- Cache rules for static assets
- Pending: DNS migration

### Deploy Pipeline

- `./deploy.sh`: Tailwind CSS build → rsync changed files → OPcache reset → health check → git tag
- `.last-deploy` timestamp tracking
- Health check endpoint (`/api/health.php`)

---

## Database Tables

**Prefix:** `oretir_`

### Core Tables

| Table | Purpose |
|-------|---------|
| `oretir_appointments` | Bookings (customer_id, vehicle_id FKs, reminder_sent) |
| `oretir_contact_messages` | Contact form submissions |
| `oretir_admin_users` | Admin accounts |
| `oretir_employees` | Technicians/staff |
| `oretir_site_settings` | Editable site content + email templates |
| `oretir_email_logs` | Email audit trail |
| `oretir_rate_limits` | API rate limiting |
| `oretir_gallery_images` | Promotions/gallery |
| `oretir_service_images` | Service card images |

### Shop Management Tables

| Table | Purpose |
|-------|---------|
| `oretir_customers` | Persistent customer records (email unique, language pref) |
| `oretir_vehicles` | Vehicles linked to customers (VIN, year/make/model, tires) |
| `oretir_vin_cache` | Permanent NHTSA vPIC decode cache |
| `oretir_tire_fitment_cache` | Tire fitment lookup cache (90-day TTL) |
| `oretir_repair_orders` | RO lifecycle (11 statuses) |
| `oretir_inspections` | Digital vehicle inspections |
| `oretir_inspection_items` | DVI line items with traffic light ratings |
| `oretir_inspection_photos` | Photos per inspection item |
| `oretir_estimates` | Estimates with approval tokens (8 statuses) |
| `oretir_estimate_items` | Estimate line items (6 types) |

---

## API Endpoint Summary

### Public Endpoints (~28)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/book.php` | POST | Create appointment |
| `/api/contact.php` | POST | Submit contact form |
| `/api/available-times.php` | GET | Slot availability by date |
| `/api/settings.php` | GET | Site settings |
| `/api/gallery.php` | GET | Gallery images |
| `/api/service-images.php` | GET | Service card images |
| `/api/vin-decode.php` | GET | VIN decode (rate limited) |
| `/api/tire-fitment.php` | GET | Tire fitment (rate limited) |
| `/api/inspection-view.php` | GET | Customer DVI report (token) |
| `/api/estimate-approve.php` | GET/POST | Estimate view + approval (token) |
| `/api/blog.php` | GET | Blog posts |
| `/api/testimonials.php` | GET | Customer reviews |
| `/api/promotions.php` | GET | Active promotions |
| `/api/faq.php` | GET | FAQ items |
| `/api/feedback.php` | POST | Submit feedback |
| `/api/subscribe.php` | POST | Newsletter signup |
| `/api/appointment-cancel.php` | POST | Cancel appointment (token) |
| `/api/appointment-reschedule.php` | POST | Reschedule appointment (token) |
| `/api/appointment-status.php` | GET | Check appointment status |
| `/api/calendar-event.php` | GET | Calendar event |
| `/api/care-plan-enroll.php` | POST | Care plan enrollment |
| `/api/care-plan-status.php` | GET | Care plan status |
| `/api/care-plan-webhook.php` | POST | Care plan webhook |
| `/api/health.php` | GET | Health check |

### Admin Endpoints (~34, session auth + CSRF)

| Endpoint | Purpose |
|----------|---------|
| `/api/admin/appointments.php` | Appointment CRUD |
| `/api/admin/repair-orders.php` | RO lifecycle + conversion |
| `/api/admin/inspections.php` | Inspection CRUD + complete + send |
| `/api/admin/inspection-photos.php` | Photo upload/delete |
| `/api/admin/estimates.php` | Estimate CRUD + auto-generate + send |
| `/api/admin/customers.php` | Customer CRUD + search |
| `/api/admin/vehicles.php` | Vehicle CRUD per customer |
| `/api/admin/vin-decode.php` | VIN decode (no rate limit) |
| `/api/admin/tire-fitment.php` | Tire fitment (no rate limit) |
| `/api/admin/blog.php` | Blog post management |
| `/api/admin/promotions.php` | Promotion management |
| `/api/admin/faq.php` | FAQ management |
| `/api/admin/testimonials.php` | Review management |
| `/api/admin/subscribers.php` | Subscriber list |
| `/api/admin/employees.php` | Employee CRUD |
| `/api/admin/gallery.php` | Gallery image management |
| `/api/admin/service-images.php` | Service image slots |
| `/api/admin/messages.php` | Contact message management |
| `/api/admin/email-logs.php` | Email audit trail |
| `/api/admin/email-template-vars.php` | Template variable reference |
| `/api/admin/analytics.php` | Analytics data |
| `/api/admin/export.php` | Data export |
| `/api/admin/site-settings.php` | Site configuration |
| `/api/admin/account.php` | Admin account management |
| `/api/admin/admins.php` | Admin user management |
| `/api/admin/login.php` | Admin login |
| `/api/admin/logout.php` | Admin logout |
| `/api/admin/session.php` | Session validation |
| `/api/admin/forgot-password.php` | Password reset |
| `/api/admin/setup-password.php` | Initial password setup |
| `/api/admin/verify-token.php` | Token verification |
| `/api/admin/calendar-health.php` | Google Calendar sync status |
| `/api/admin/calendar-retry-sync.php` | Retry calendar sync |
| `/api/admin/calendar-test-sync.php` | Test calendar sync |

### Member Endpoints (~17, member auth)

| Endpoint | Purpose |
|----------|---------|
| `/api/member/login.php` | Member login |
| `/api/member/register.php` | Member registration |
| `/api/member/logout.php` | Member logout |
| `/api/member/profile.php` | Profile management |
| `/api/member/password.php` | Password change |
| `/api/member/forgot-password.php` | Password reset request |
| `/api/member/reset-password.php` | Password reset |
| `/api/member/google.php` | Google OAuth initiate |
| `/api/member/google-callback.php` | Google OAuth callback |
| `/api/member/google-unlink.php` | Unlink Google account |
| `/api/member/my-bookings.php` | Customer appointments |
| `/api/member/my-bookings-ui.php` | Bookings UI data |
| `/api/member/my-vehicles.php` | Customer vehicles |
| `/api/member/my-estimates.php` | Customer estimates |
| `/api/member/my-messages.php` | Customer messages |
| `/api/member/my-care-plan.php` | Care plan status |
| `/api/member/my-customers.php` | Customer data |

### Commerce Endpoints (6)

| Endpoint | Purpose |
|----------|---------|
| `/api/commerce/checkout.php` | Payment checkout |
| `/api/commerce/checkout-return.php` | Post-checkout redirect |
| `/api/commerce/crypto-confirm.php` | Crypto payment confirmation |
| `/api/commerce/orders.php` | Order management |
| `/api/commerce/paypal-webhook.php` | PayPal webhook |
| `/api/commerce/webhook.php` | Commerce webhook |
| `/api/commerce/stats.php` | Commerce analytics |

---

*Generated from codebase review. Last updated 2026-03-03.*
