# Oregon Tires Auto Care — Feature Inventory

> **Last updated:** 2026-03-18
> **Stack:** PHP + MySQL + Tailwind CSS v4 + PHPMailer | cPanel/Apache
> **Live:** https://oregon.tires
> **Admin:** https://oregon.tires/admin/

---

## Summary

| Category | Features | Priority |
|----------|----------|----------|
| Core Shop Operations | 8 systems | P0 |
| Customer Management | 4 systems | P0 |
| Customer Communication | 6 systems | P1 |
| Revenue & Subscriptions | 4 systems | P1 |
| Content & Marketing | 6 systems | P2 |
| Public Website | 4 systems | P2 |
| Infrastructure | 8 systems | P3 |
| Roadmap (2026 Q2–Q3) | 12 items | P1–P3 |
| **Total** | **~52 systems, 115+ API endpoints, 36 pages** | |

---

## Group 1: Core Shop Operations (P0 — Revenue-Critical)

### Appointment Booking

**Pages:** `/book-appointment/`, `/cancel/{token}`, `/reschedule/{token}`
**API:** `POST /api/book.php`, `GET /api/available-times.php?date=YYYY-MM-DD`, `/api/appointment-cancel.php`, `/api/appointment-reschedule.php`, `/api/appointment-status.php`

- 15-minute slot granularity with multi-bay capacity
- Employee schedule-aware availability (blocks booked slots per technician)
- VIN decode built into booking form (auto-populates vehicle details)
- License plate lookup integration (plate → vehicle → tire sizes via `api/plate-lookup.php`)
- "Find My Tire Size" helper for tire-related services (dynamic display from plate/VIN decode)
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

### Digital Invoices

**API:** `/api/admin/invoices.php` (admin CRUD), `/api/invoice-view.php` (customer token-based view)
**Admin JS:** `admin/js/invoices.js`

- Generate invoices from completed repair orders
- Token-based customer view (bilingual, no login required)
- Invoice number auto-generation
- Line items carried from estimate/RO
- Print-friendly layout
- Table: `oretir_invoices` (migration 042)

### Labor Tracking

**API:** `/api/admin/labor.php`
**Admin JS:** `admin/js/labor-tracker.js`

- Log technician hours per repair order
- Track labor type (diagnosis, repair, inspection)
- Efficiency reporting (hours logged vs estimated)
- Employee performance metrics
- Table: `oretir_labor_entries` (migration 045)

### Waitlist / Walk-In Queue

**API:** `/api/admin/waitlist.php` (admin), `/api/waitlist.php` (public)
**Admin JS:** `admin/js/waitlist.js`

- Walk-in customer queue management
- Estimated wait time tracking
- SMS notification when ready (via Twilio)
- Queue position display
- Admin drag-and-drop queue reordering
- Table: `oretir_waitlist` (migration 047)

### Tire Quote Requests

**API:** `/api/admin/tire-quotes.php` (admin), `/api/tire-quote.php` (public)
**Admin JS:** `admin/js/tire-quotes.js`

- Customer-facing tire quote request form
- Captures vehicle info + desired tire specs
- Admin quote response workflow
- Quote status tracking (pending, quoted, accepted, expired)
- Table: `oretir_tire_quotes` (migration 048)

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
**API:** `/api/member/*` (21 endpoints)

- Customer login/register via Member Kit integration
- Google OAuth sign-in option (`/api/member/google.php`, `/api/member/google-callback.php`)
- Password reset flow (`/api/member/forgot-password.php`, `/api/member/reset-password.php`)
- Bilingual auth pages (EN/ES) with local template overrides
- Unified auth flow: admin login redirects to `/members` for single entry point
- **Dashboard tabs:**
  - **Appointments:** View upcoming and past bookings (`/api/member/my-bookings.php`)
  - **Vehicles:** View/manage linked vehicles (`/api/member/my-vehicles.php`)
  - **Estimates:** View sent estimates, approve/decline (`/api/member/my-estimates.php`)
  - **Messages:** Contact history (`/api/member/my-messages.php`)
  - **Conversations:** Member conversation threads (`/api/member/conversations.php`)
  - **Care Plan:** Current plan status and benefits (`/api/member/my-care-plan.php`)
- **Employee role-based tabs** (visible only to employee/admin roles):
  - **My Schedule:** Employee work schedule (`/api/member/my-schedule.php`)
  - **My Assigned Work:** Employee's assigned ROs (`/api/member/my-assigned-work.php`)
  - **My Customers:** Employee's customer list (`/api/member/my-customers.php`)
- Profile management (`/api/member/profile.php`, `/api/member/password.php`)
- Google account linking/unlinking

### Visit Tracking

**API:** `/api/admin/visit-log.php`
**Admin JS:** `admin/js/visit-tracker.js`

- Log customer walk-in visits
- Track visit purpose and outcome
- Visit history per customer
- Admin dashboard widget for daily visit count

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
  - `sendEmailReply()` — reply to inbound customer emails
  - `notifyOwner()` — internal notification to shop owner
- **Template features:**
  - DB-stored templates editable via Settings tab
  - `{{variable}}` placeholder replacement
  - Bilingual sections (EN/ES side by side)
  - Template variable reference in admin
- Email audit trail: every sent email logged to `oretir_email_logs`
- SMTP debug level configurable via `.env` (`SMTP_DEBUG=0|1|2`)

### Email Inbox Integration

**Core:** `includes/email-fetcher.php`
**API:** `/api/admin/conversations.php`, `/api/admin/email-check.php`

- IMAP inbound email fetching (webklex/php-imap)
- Emails threaded into admin conversations via Message-ID / In-Reply-To / References headers
- Automatic conversation matching by customer email
- New conversations auto-created for unknown threads
- Attachment metadata stored as JSON
- Message source tracking (web, email, system)
- Admin can reply to email threads from the Messages tab
- Cron: `cli/fetch-inbound-emails.php` runs every 2 minutes
- Tables: `oretir_email_message_ids` (migration 053), enhanced `oretir_conversations` + `oretir_conversation_messages`

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
| Daily 6:00 PM | `send-reminders.php` | Appointment reminders for next day (email + SMS + push) |
| Daily 10:00 AM | `send-review-requests.php` | Review request emails (completed appointments) |
| Daily 10:00 AM | `send-estimate-reminders.php` | Estimate expiry reminders (2 days before valid_until) |
| Daily 6:00 AM | `fetch-google-reviews.php` | Refresh Google Reviews cache |
| Every 5 min | `send-push-notifications.php` | Push notification queue processor |
| Every 2 min | `fetch-inbound-emails.php` | IMAP inbound email fetch |
| Mon 9:00 AM | `send-service-reminders.php` | Automated service due date reminders |
| Mon 7:00 AM | `sync-google-business.php` | Google Business Profile sync |
| On demand | `send-welcome-emails.php` | Onboarding/welcome emails |

### Automated Service Reminders

**API:** `/api/admin/service-reminders.php`
**Admin JS:** `admin/js/service-reminders.js`
**CLI:** `cli/send-service-reminders.php` (weekly cron, Mon 9AM)

- Track service due dates per vehicle (oil change, tire rotation, brake check, etc.)
- Automated email reminders when service is due
- Admin can create/edit/delete reminders per customer+vehicle
- Bilingual reminder emails via branded template system
- Table: `oretir_service_reminders` (migration 043)

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

### Loyalty & Rewards

**API:** `/api/admin/loyalty.php`, `/api/admin/loyalty-rewards.php`
**Admin JS:** `admin/js/loyalty.js`

- Points-per-dollar on completed ROs
- Redeemable rewards catalog (admin-managed)
- Points balance visible in member portal
- Tier bonuses for care plan members
- Points ledger with transaction history
- Tables: `oretir_loyalty_points` (enhanced via migration 044), `oretir_loyalty_rewards` (migration 044)

### Customer Referrals

**API:** `/api/referral-lookup.php` (public), `/api/admin/referrals.php`
**Admin JS:** `admin/js/referrals.js`

- Referral code generation per customer
- Track referral source on new bookings
- Referral bonus points for referring customer
- Referral status tracking (pending, completed, rewarded)
- Table: `oretir_referrals` (migration 046)

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

### Service Detail Pages (10 pages)

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
| Mobile Service | `/mobile-service` |
| Fleet Services | `/fleet-services` |
| Roadside Assistance | `/roadside-assistance` |

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
- **7 service feature cards** — each card links to its service detail page:

| Card | Links To |
|------|----------|
| Expert Technicians | `/engine-diagnostics` |
| Fast Service | `/tire-repair` |
| Quality Parts | `/brake-service` |
| Bilingual Support | `/book-appointment` |
| Tire Services | `/tire-installation` |
| Auto Maintenance | `/oil-change` |
| Specialized Services | `/wheel-alignment` |

- **Service category lists** — individual list items link to their respective service pages (tire installation, tire repair, oil change, brake service, alignment, etc.)
- About section with shop story
- Reviews section (3 random reviews per load)
- Gallery section (language-filtered)
- Contact section with form + Google Maps embed
- Sticky header with smooth scroll navigation
- **Footer** links to all 10 service pages

### Site-Wide Features

- **Bilingual system:** EN/ES toggle via globe icon, `data-t` attribute translations, `currentLang` JS variable, language persisted across pages — includes bilingual auth pages (login/register/reset)
- **Dark mode:** System preference detection + manual toggle, Tailwind v4 `@variant dark` strategy
- **PWA (Enhanced):** Service worker (`sw.js` v21) with versioned caching, bilingual offline fallback page (`offline.html`), network-first HTML, cache-first images, all 8 service pages + booking page precached. **Push notifications** via Web Push API (VAPID) with bilingual payloads — booking confirmations, appointment reminders, RO status updates, admin broadcast promotions. **Offline booking** via IndexedDB queue + Background Sync replay (`assets/js/offline-booking.js`). **Install prompt** for Android (deferred prompt) + iOS (share sheet instructions) via `assets/js/pwa-manager.js`. Online/offline indicator with bilingual toast. Notification preferences (4 toggles) per subscription. Manifest with scope, split icons (any/maskable), shortcuts (Book, My Bookings, Call Us), share_target
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
- Rate limiting on public API endpoints (VIN decode, tire fitment, plate lookup: 10/hr)
- `.htaccess` blocks sensitive files (.env, config.php, composer.*, includes/)
- No innerHTML — DOM manipulation via createElement/textContent
- API error handling: catch `\Throwable` in all endpoints
- Unified auth flow: admin login redirects to `/members` — single auth entry point
- Admin session recovery with fallback session vars (prevents redirect loops)
- Bilingual auth template overrides for login/register/reset pages

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

### Enhanced Analytics

**API:** `/api/admin/analytics.php`
**Admin JS:** `admin/js/admin-analytics.js`

- Revenue tracking and trends
- Service type breakdown
- Customer acquisition metrics
- Employee utilization rates
- Appointment conversion funnel
- Time-of-day and day-of-week heatmaps

### Google Business Sync

**API:** `/api/admin/google-business-sync.php`
**Admin JS:** `admin/js/google-business.js`
**CLI:** `cli/sync-google-business.php` (weekly cron, Mon 7AM)

- Sync business hours, services, and info to Google Business Profile
- Pull latest Google reviews into local cache
- Track sync status and errors
- Manual sync trigger from admin panel

### Deploy Pipeline

- `./deploy.sh`: Tailwind CSS build → rsync changed files → OPcache reset → health check → git tag
- `.last-deploy` timestamp tracking
- Health check endpoint (`/api/health.php`)

---

## Group 8: Roadmap (2026 Q2–Q3)

### R1. Online Estimate Payment (P1 — High)

Customers can approve estimates but cannot pay online. Must pay in-person.

- Add "Approve & Pay" button on estimate approval page
- Integrate with existing commerce-kit checkout flow (PayPal + card)
- Support deposit (50%) or full prepayment
- Send digital receipt via email
- Update RO status to `approved` + `paid` flag on successful payment

### R2. Parts & Inventory Management (P1 — High)

No parts inventory system. Technicians track parts manually.

- New table `oretir_inventory` (part_number, name, qty, cost, supplier, reorder_point, location)
- Link estimate line items (type=parts/tire) to inventory records
- Auto-deduct stock when RO moves to `in_progress`
- Low-stock alerts on admin dashboard (configurable threshold per item)
- Supplier contact list with last-order tracking
- Reorder report: items below reorder point grouped by supplier

### R3. WhatsApp Business Integration (P1 — High)

Many Spanish-speaking customers prefer WhatsApp over SMS/email.

- Integrate WhatsApp Business API (via Twilio or Meta Cloud API)
- Send inspection reports, estimate links, ready notifications, appointment reminders via WhatsApp
- Add WhatsApp opt-in to booking form alongside SMS opt-in
- WhatsApp message templates (bilingual, pre-approved by Meta)
- Fallback chain: WhatsApp → SMS → Email (try preferred channel first)

### R4. Google Calendar Sync (P2 — Medium)

Admin calendar endpoints exist but full sync is not wired up.

- Complete Google Calendar API integration (OAuth2 service account)
- Auto-create calendar events for confirmed appointments
- Sync RO status changes to calendar event descriptions
- Technicians see their schedule on their phones via shared Google Calendar
- Two-way sync: calendar edits reflect in admin panel

### R5. Customer Vehicle History Timeline (P2 — Medium)

No unified view of a vehicle's full service history across ROs, inspections, and estimates.

- Timeline view in member portal: all RO/inspection/estimate history per vehicle in chronological order
- Status badges, cost summaries, and links to detail pages
- Mileage progression chart (track odometer readings over time)
- Service interval indicators (show when next oil change / tire rotation is due)
- Print-friendly vehicle history report (useful for resale)

### R6. Automated Follow-Up Sequences (P2 — Medium)

Currently only single-touch emails (reminder, review request). No multi-step follow-up.

- Multi-step email sequences after service completion:
  - Day 0: Thank you + digital receipt
  - Day 3: Review request (Google + internal)
  - Day 30: Return reminder with seasonal promotion
  - Day 90: Service check-in (are you due for maintenance?)
- Configurable per service type (e.g., tire install gets different sequence than oil change)
- Unsubscribe link per sequence
- Admin UI to view/pause/edit sequences

### R7. Technician Mobile View (P2 — Medium)

Technicians use the full admin panel on their phones, which is clunky for field work.

- Simplified mobile-optimized view for techs (separate route: `/tech/`)
- See assigned ROs for today with swipe-to-update-status
- Quick photo capture for inspections (camera → upload → done)
- Clock in/out for labor tracking (start/stop timer per RO)
- View customer vehicle info and notes
- No admin-level access (employee role only)

### R8. QR Code Check-In (P2 — Medium)

Walk-in customers wait in line to check in manually.

- Generate unique QR code poster for shop entrance
- Customer scans QR → lands on check-in page (no app needed)
- Check-in form: name, phone, service needed, vehicle (optional VIN scan)
- Auto-adds to waitlist with estimated wait time
- Push notification when their turn is next
- Repeat customers auto-detected by phone number → pre-fills info

### R9. Seasonal Tire Storage Tracking (P2 — Medium)

Portland customers swap between all-season and winter tires. No way to track stored sets.

- New table `oretir_tire_storage` (customer_id, vehicle_id, tire_set, location, stored_date, condition)
- Track tire brand/model/size/tread depth for each stored set
- Storage location labels (rack/bin number in shop)
- Automated reminder when swap season arrives (Oct for winter, Apr for summer)
- Customer portal view of stored tires
- Admin search by storage location

### R10. Customer Satisfaction Surveys (P2 — Medium)

No structured feedback beyond Google reviews and the generic feedback form.

- Post-service CSAT survey (1-5 stars + optional comment) sent 24 hours after RO completion
- NPS question: "How likely are you to recommend us?" (0-10 scale)
- Admin dashboard: CSAT trend, NPS score, response rate
- Auto-flag low scores (≤2) for manager follow-up
- Bilingual survey page (token-based, no login)
- Link negative respondents to direct contact (phone/email) instead of public review

### R11. Appointment Deposit / No-Show Reduction (P3 — Low)

No-shows waste technician time and bay capacity.

- Optional deposit requirement for high-value services (configurable per service type)
- Deposit amount: flat fee ($25) or percentage (10%)
- Deposit collected at booking via commerce-kit checkout
- Refundable if cancelled 24+ hours before appointment
- No-show tracking per customer (flag repeat offenders)
- SMS + push reminder sequence: 24hr → 2hr → 30min before appointment

### R12. Video Inspections (P3 — Low)

Photos don't always capture the full picture of a vehicle issue.

- Short video capture (15-30 sec) per inspection item alongside photos
- Upload from tech's phone camera during inspection
- Video playback in customer inspection report (inline player)
- Storage in `uploads/inspections/{ro_number}/` alongside photos
- Bandwidth-conscious: compress to 720p, lazy-load on customer view
- Fallback to photo-only for slow connections

---

## Database Tables

**Prefix:** `oretir_` | **53 migration files** | **~46 tables**

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
| `oretir_subscribers` | Email newsletter subscribers |
| `oretir_blog_posts` | Blog articles |

### Shop Management Tables

| Table | Purpose |
|-------|---------|
| `oretir_customers` | Persistent customer records (email unique, language pref) |
| `oretir_vehicles` | Vehicles linked to customers (VIN, year/make/model, tires) |
| `oretir_vin_cache` | Permanent NHTSA vPIC decode cache |
| `oretir_tire_fitment_cache` | Tire fitment lookup cache (90-day TTL) |
| `oretir_plate_cache` | License plate → vehicle lookup cache |
| `oretir_repair_orders` | RO lifecycle (11 statuses) |
| `oretir_inspections` | Digital vehicle inspections |
| `oretir_inspection_items` | DVI line items with traffic light ratings |
| `oretir_inspection_photos` | Photos per inspection item |
| `oretir_estimates` | Estimates with approval tokens (8 statuses) |
| `oretir_estimate_items` | Estimate line items (6 types) |
| `oretir_invoices` | Digital invoices from completed ROs |
| `oretir_labor_entries` | Technician labor hours per RO |
| `oretir_waitlist` | Walk-in queue management |
| `oretir_tire_quotes` | Tire quote requests and responses |

### Features Tables (migrations 017–048)

| Table | Purpose |
|-------|---------|
| `oretir_promotions` | Promotional offers (image, placement targeting) |
| `oretir_care_plans` | Service care plan definitions |
| `oretir_care_plan_subscriptions` | Customer care plan enrollments |
| `oretir_faq` | FAQ entries (bilingual seed) |
| `oretir_testimonials` | Customer testimonials |
| `oretir_calendar_sync` | Google Calendar sync tracking |
| `oretir_google_reviews` | Cached Google Business reviews |
| `oretir_employee_schedules` | Employee work schedules |
| `oretir_employee_skills` | Employee skill/certification tracking |
| `oretir_task_summary` | Daily task summaries for employee dashboard |
| `oretir_conversations` | Messaging threads (admin↔customer, source: web/email/contact_form) |
| `oretir_messages` | Individual messages (source: web/email/system, attachments_json) |
| `oretir_loyalty_points` | Customer loyalty point ledger |
| `oretir_loyalty_rewards` | Redeemable loyalty rewards catalog |
| `oretir_service_reminders` | Automated service due date tracking |
| `oretir_referrals` | Customer referral tracking |

### PWA & Communication (migrations 049–053)

| Table | Purpose |
|-------|---------|
| `oretir_push_subscriptions` | Web Push subscription storage |
| `oretir_notification_queue` | Bilingual notification queue with targeting + retry |
| `oretir_offline_sync_log` | Offline form submission deduplication |
| `oretir_email_message_ids` | Email Message-ID tracking for dedup + threading |

---

## API Endpoint Summary

### Public Endpoints (~34)

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
| `/api/plate-lookup.php` | GET | License plate lookup (rate limited) |
| `/api/inspection-view.php` | GET | Customer DVI report (token) |
| `/api/estimate-approve.php` | GET/POST | Estimate view + approval (token) |
| `/api/invoice-view.php` | GET | Customer invoice view (token) |
| `/api/blog.php` | GET | Blog posts |
| `/api/testimonials.php` | GET | Customer reviews |
| `/api/promotions.php` | GET | Active promotions |
| `/api/faq.php` | GET | FAQ items |
| `/api/feedback.php` | POST | Submit feedback |
| `/api/subscribe.php` | POST | Newsletter signup |
| `/api/referral-lookup.php` | GET | Referral code lookup |
| `/api/tire-quote.php` | POST | Submit tire quote request |
| `/api/waitlist.php` | GET/POST | Join/check waitlist |
| `/api/appointment-cancel.php` | POST | Cancel appointment (token) |
| `/api/appointment-reschedule.php` | POST | Reschedule appointment (token) |
| `/api/appointment-status.php` | GET | Check appointment status |
| `/api/calendar-event.php` | GET | Calendar event |
| `/api/care-plan-enroll.php` | POST | Care plan enrollment |
| `/api/care-plan-status.php` | GET | Care plan status |
| `/api/care-plan-webhook.php` | POST | Care plan webhook |
| `/api/push-vapid-key.php` | GET | VAPID public key for push |
| `/api/push-subscribe.php` | POST/PUT/DELETE | Push subscription CRUD |
| `/api/offline-sync.php` | POST | Offline form replay |
| `/api/health.php` | GET | Health check |

### Admin Endpoints (~48, session auth + CSRF)

| Endpoint | Purpose |
|----------|---------|
| `/api/admin/appointments.php` | Appointment CRUD |
| `/api/admin/repair-orders.php` | RO lifecycle + conversion |
| `/api/admin/inspections.php` | Inspection CRUD + complete + send |
| `/api/admin/inspection-photos.php` | Photo upload/delete |
| `/api/admin/estimates.php` | Estimate CRUD + auto-generate + send |
| `/api/admin/invoices.php` | Invoice CRUD + generate from RO |
| `/api/admin/customers.php` | Customer CRUD + search |
| `/api/admin/vehicles.php` | Vehicle CRUD per customer |
| `/api/admin/vin-decode.php` | VIN decode (no rate limit) |
| `/api/admin/tire-fitment.php` | Tire fitment (no rate limit) |
| `/api/admin/labor.php` | Labor hours tracking per RO |
| `/api/admin/loyalty.php` | Loyalty points management |
| `/api/admin/loyalty-rewards.php` | Loyalty rewards catalog |
| `/api/admin/service-reminders.php` | Service reminder management |
| `/api/admin/referrals.php` | Referral tracking |
| `/api/admin/waitlist.php` | Walk-in queue management |
| `/api/admin/tire-quotes.php` | Tire quote request management |
| `/api/admin/visit-log.php` | Visit tracking log |
| `/api/admin/google-business-sync.php` | Google Business Profile sync |
| `/api/admin/business-hours.php` | Business hours configuration |
| `/api/admin/blog.php` | Blog post management |
| `/api/admin/promotions.php` | Promotion management |
| `/api/admin/faq.php` | FAQ management |
| `/api/admin/testimonials.php` | Review management |
| `/api/admin/subscribers.php` | Subscriber list |
| `/api/admin/employees.php` | Employee CRUD |
| `/api/admin/schedules.php` | Employee schedules |
| `/api/admin/conversations.php` | Messaging management (web + email threads) |
| `/api/admin/email-check.php` | Manual IMAP fetch trigger |
| `/api/admin/gallery.php` | Gallery image management |
| `/api/admin/service-images.php` | Service image slots |
| `/api/admin/messages.php` | Contact message management |
| `/api/admin/email-logs.php` | Email audit trail |
| `/api/admin/email-template-vars.php` | Template variable reference |
| `/api/admin/analytics.php` | Enhanced analytics dashboard |
| `/api/admin/export.php` | Data export |
| `/api/admin/site-settings.php` | Site configuration |
| `/api/admin/account.php` | Admin account management |
| `/api/admin/admins.php` | Admin user management |
| `/api/admin/push-broadcast.php` | Push notification broadcast |
| `/api/admin/login.php` | Admin login |
| `/api/admin/logout.php` | Admin logout |
| `/api/admin/session.php` | Session validation |
| `/api/admin/forgot-password.php` | Password reset |
| `/api/admin/setup-password.php` | Initial password setup |
| `/api/admin/verify-token.php` | Token verification |
| `/api/admin/calendar-health.php` | Google Calendar sync status |
| `/api/admin/calendar-retry-sync.php` | Retry calendar sync |
| `/api/admin/calendar-test-sync.php` | Test calendar sync |

### Member Endpoints (21, member auth)

| Endpoint | Purpose |
|----------|---------|
| `/api/member/login.php` | Member login |
| `/api/member/register.php` | Member registration |
| `/api/member/logout.php` | Member logout |
| `/api/member/profile.php` | Profile management |
| `/api/member/password.php` | Password change |
| `/api/member/forgot-password.php` | Password reset request |
| `/api/member/password-reset.php` | Password reset handler |
| `/api/member/reset-password.php` | Complete password reset |
| `/api/member/google.php` | Google OAuth initiate |
| `/api/member/google-callback.php` | Google OAuth callback |
| `/api/member/google-unlink.php` | Unlink Google account |
| `/api/member/my-bookings.php` | Customer appointments |
| `/api/member/my-bookings-ui.php` | Bookings UI data |
| `/api/member/my-vehicles.php` | Customer vehicles |
| `/api/member/my-estimates.php` | Customer estimates |
| `/api/member/my-messages.php` | Customer messages |
| `/api/member/conversations.php` | Member conversation threads |
| `/api/member/my-care-plan.php` | Care plan status |
| `/api/member/my-schedule.php` | Employee schedule |
| `/api/member/my-assigned-work.php` | Employee assigned ROs |
| `/api/member/my-customers.php` | Employee's customers |

### Commerce Endpoints (7)

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

*Generated from codebase review. Last updated 2026-03-18.*
