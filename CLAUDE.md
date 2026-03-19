# Oregon Tires Auto Care — Project Instructions

See parent `/Users/hiphop/CLAUDE.md` for network-wide conventions (naming, .htaccess, directory structure, kit patterns).

## Quick Reference
- **Stack**: Static HTML + Tailwind CSS v4 + PHP API + MySQL
- **Live**: https://oregon.tires
- **Domain**: `oregon.tires` — premium .tires TLD, selected via domain consultation, ownership transfers to client with payment
- **Google Place ID**: `ChIJLSxZDQyflVQRWXEi9LpJGxs`
- **Site type**: `client` (independent mode, `MEMBER_MODE=independent`)
- **Deploy**: `./deploy.sh` (builds CSS, stages changed files, SCPs to server)
- **Server**: `ssh hiphopworld` → `/home/hiphopwo/public_html/---oregon.tires/`
- **DB**: `hiphopwo_oregon_tires`, prefix `oretir_`
- **Bilingual**: EN/ES — inline JS `t` object with `data-t` attributes, `currentLang` variable

## Client Service Offerings
- **Client Price**: $5,000 — full software ownership + 3 months marketing & management + domain transfer
- **Domain**: `oregon.tires` — transfers to client with payment
- **Managed Hosting**: $50/mo — server management, SSL, backups, uptime monitoring, security patches, Cloudflare CDN
- **Marketing & Management**: Starting at $500/mo — SEO, content updates, blog, social media, Google Business management, analytics, platform enhancements
- **Platform Value**: ~$52,400 (freelancer rate @ $50/hr) — 113 features across 17 categories

## Kit Usage
- **member-kit** — customer/employee accounts, Google OAuth, password reset (`MEMBER_KIT_PATH`)
- **form-kit** — contact form submissions (`FORM_KIT_PATH`)
- **commerce-kit** — checkout, payments, care plan billing (`COMMERCE_KIT_PATH`)
- **engine-kit** — error tracking, network integration (`ENGINE_KIT_PATH`)

## Key Paths
- Local: `public_html/` prefix
- Server: flat at `---oregon.tires/` level (strip `public_html/` when SCPing)
- CLI scripts: `cli/` (bootstrap path on server: `__DIR__ . '/../includes/bootstrap.php'`)
- SQL migrations: `sql/` (outside public_html, 52 migration files)
- Uploads: `uploads/inspections/{ro_number}/` (inspection photos)

## Authentication (member-kit)
- **Roles**: admin > employee > member (stored in member-kit `members` table)
- **Google OAuth**: `api/auth/google.php` → `api/auth/google-callback.php` (also mirrored at `api/member/google.php` / `google-callback.php`)
- **Password reset**: `api/member/forgot-password.php` → `api/member/reset-password.php`
- **Admin auth**: `api/admin/login.php` / `logout.php` / `session.php` / `forgot-password.php` / `setup-password.php`
- **Admin lockout**: setup tokens via `cli/resend-setup-emails.php`, password set via token
- **Unified auth flow**: Admin login redirects to `/members` page — single auth entry point
- **Bilingual auth pages**: EN/ES local template overrides for login/register/reset forms
- **Admin session recovery**: Fallback session vars prevent redirect loops on session edge cases
- **Smart account**: `includes/smart-account.php` — auto-links booking customers to member accounts
- **Member translations**: `includes/member-translations.php` — bilingual auth UI strings

## Database Tables (prefix: `oretir_`)

### Core
- `oretir_appointments` — bookings (customer_id, vehicle_id FKs, reminder_sent, cancel_token, utm fields, sms_opt_in)
- `oretir_contact_messages` — contact form submissions (status tracking)
- `oretir_admin_users` — admin accounts
- `oretir_employees` — technicians/staff
- `oretir_site_settings` — editable site content + email templates (including Google Analytics ID)
- `oretir_email_logs` — email audit trail
- `oretir_rate_limits` — API rate limiting
- `oretir_gallery_images` — gallery (bilingual captions)
- `oretir_service_images` — service card images
- `oretir_subscribers` — email newsletter subscribers (with welcome email flag)
- `oretir_blog_posts` — blog articles

### Shop Management
- `oretir_customers` — persistent customer records (email unique)
- `oretir_vehicles` — vehicles linked to customers (VIN, year/make/model, tire sizes, member_id)
- `oretir_vin_cache` — permanent NHTSA vPIC decode cache
- `oretir_tire_fitment_cache` — tire fitment lookup cache (90-day TTL)
- `oretir_plate_cache` — license plate → vehicle lookup cache (035)
- `oretir_repair_orders` — RO lifecycle
- `oretir_inspections` — digital vehicle inspections (linked to RO)
- `oretir_inspection_items` — DVI line items with traffic light ratings (green/yellow/red)
- `oretir_inspection_photos` — photos per inspection item
- `oretir_estimates` — estimates with approval tokens (decline_reason field)
- `oretir_estimate_items` — estimate line items (labor/parts/tire/fee/discount/sublet)

### Features (migrations 017–036)
- `oretir_promotions` — promotional offers (image, placement targeting)
- `oretir_care_plans` — service care plan definitions
- `oretir_care_plan_subscriptions` — customer care plan enrollments
- `oretir_faq` — FAQ entries (seeded bilingual)
- `oretir_testimonials` — customer testimonials
- `oretir_calendar_sync` — Google Calendar sync tracking
- `oretir_google_reviews` — cached Google Business reviews
- `oretir_employee_schedules` — employee work schedules
- `oretir_employee_skills` — employee skill/certification tracking
- `oretir_task_summary` — daily task summaries for employee dashboard
- `oretir_conversations` — messaging threads (admin↔customer)
- `oretir_messages` — individual messages within conversations
- `oretir_loyalty_points` — customer loyalty point ledger

### Shop Operations (migrations 037–048)
- `oretir_invoices` — digital invoices from completed ROs (token-based customer view)
- `oretir_service_reminders` — automated service due date tracking per vehicle
- `oretir_loyalty_rewards` — redeemable loyalty rewards catalog
- `oretir_labor_entries` — technician labor hours per RO
- `oretir_referrals` — customer referral tracking (codes, status, bonus points)
- `oretir_waitlist` — walk-in queue management
- `oretir_tire_quotes` — tire quote requests and responses

### PWA & Push Notifications (migrations 049–052)
- `oretir_push_subscriptions` — Web Push subscription storage (endpoint, keys, customer/member FK, language, notification preferences)
- `oretir_notification_queue` — bilingual notification queue with targeting (subscription/customer/member/broadcast), retry logic, scheduling
- `oretir_offline_sync_log` — offline form submission deduplication via unique sync_id

### Email Integration (migration 053)
- `oretir_email_message_ids` — email Message-ID tracking for dedup + threading (inbound/outbound, In-Reply-To, conversation FK)
- `oretir_conversation_messages.source` — message source: web, email, system
- `oretir_conversation_messages.attachments_json` — JSON array of attachment metadata
- `oretir_conversations.source` — conversation source: web, email, contact_form
- `oretir_conversations.email_thread_id` — original email Message-ID for thread root

## RO Lifecycle
`intake → diagnosis → estimate_pending → pending_approval → approved → in_progress → waiting_parts → ready → completed → invoiced` (also: `cancelled`)

## Shop Management Features
- **VIN decode**: NHTSA vPIC API with permanent DB cache (`includes/vin-decode.php`)
- **Plate lookup**: License plate → vehicle via `api/plate-lookup.php` with DB cache
- **Tire fitment**: API with 90-day DB cache (`includes/tire-fitment.php`)
- **DVI**: Traffic light system (green/yellow/red), photo capture, customer view via token
- **Estimates**: Per-item approve/decline, token-based bilingual approval page
- **Kanban board**: Drag-and-drop RO status management (`admin/js/kanban.js`)
- **Reference numbers**: `RO-XXXXXXXX` (repair orders), `ES-XXXXXXXX` (estimates)
- **Care plans**: Subscription-based service plans with PayPal billing
- **Google Reviews**: Fetched via Places API, cached in DB (`includes/google-reviews.php`)

## API Endpoints

### Public
- `POST /api/book.php` — create appointment (auto-creates customer + vehicle)
- `POST /api/contact.php` — contact form
- `POST /api/subscribe.php` — newsletter signup
- `POST /api/feedback.php` — customer feedback
- `GET /api/available-times.php?date=` — slot availability
- `GET /api/settings.php` — site settings
- `GET /api/gallery.php` — gallery images
- `GET /api/service-images.php` — service card images
- `GET /api/vin-decode.php?vin=` — VIN decode (rate limited 10/hr)
- `GET /api/tire-fitment.php?year=&make=&model=` — tire fitment (rate limited)
- `GET /api/plate-lookup.php?plate=&state=` — license plate lookup (rate limited)
- `GET /api/inspection-view.php?token=` — customer DVI report (token-based)
- `GET/POST /api/estimate-approve.php?token=` — estimate view + approval (token-based)
- `GET /api/blog.php` — blog posts list / single
- `GET /api/faq.php` — FAQ entries
- `GET /api/promotions.php` — active promotions
- `GET /api/testimonials.php` — customer testimonials
- `GET /api/sitemap.php` — dynamic XML sitemap
- `GET /api/calendar-event.php` — .ics calendar event download
- `GET /api/appointment-status.php?ref=` — appointment status check
- `POST /api/appointment-cancel.php` — cancel via token
- `POST /api/appointment-reschedule.php` — reschedule via token
- `POST /api/care-plan-enroll.php` — care plan signup
- `GET /api/care-plan-status.php` — care plan status
- `POST /api/care-plan-webhook.php` — PayPal subscription webhook
- `GET /api/health.php` — health check
- `GET /api/invoice-view.php?token=` — customer invoice view (token-based)
- `GET /api/referral-lookup.php?code=` — referral code lookup
- `POST /api/tire-quote.php` — submit tire quote request
- `GET/POST /api/waitlist.php` — join/check walk-in waitlist
- `GET /api/push-vapid-key.php` — VAPID public key for push subscription
- `POST/PUT/DELETE /api/push-subscribe.php` — push subscription CRUD + preferences
- `POST /api/offline-sync.php` — offline form replay with sync_id dedup

### Auth (Google OAuth)
- `GET /api/auth/google.php` — initiate Google OAuth
- `GET /api/auth/google-callback.php` — OAuth callback

### Member (session auth)
- `POST /api/member/register.php` — member registration
- `POST /api/member/login.php` — member login
- `POST /api/member/logout.php` — member logout
- `POST /api/member/forgot-password.php` — request password reset
- `POST /api/member/password-reset.php` — password reset handler
- `POST /api/member/reset-password.php` — complete password reset
- `POST /api/member/password.php` — change password (logged in)
- `GET/PUT /api/member/profile.php` — member profile
- `GET /api/member/my-bookings.php` — member's appointments
- `GET /api/member/my-bookings-ui.php` — bookings with UI data
- `GET /api/member/my-vehicles.php` — member's vehicles
- `GET /api/member/my-estimates.php` — member's estimates
- `GET /api/member/my-care-plan.php` — member's care plan
- `GET /api/member/my-invoices.php` — member's invoices (HTML tab)
- `GET /api/member/my-loyalty.php` — loyalty points dashboard (HTML tab)
- `GET /api/member/my-referral.php` — referral code + stats (JSON)
- `GET /api/member/my-referral-ui.php` — referral dashboard (HTML tab)
- `GET /api/member/my-messages.php` — member's messages
- `GET /api/member/conversations.php` — member's conversations
- `GET /api/member/my-schedule.php` — employee schedule
- `GET /api/member/my-assigned-work.php` — employee assigned ROs
- `GET /api/member/my-customers.php` — employee's customers
- `GET /api/member/google.php` / `google-callback.php` — Google OAuth (member path)
- `POST /api/member/google-unlink.php` — unlink Google account

### Commerce (kit wrappers)
- `POST /api/commerce/checkout.php` — PayPal checkout
- `GET /api/commerce/checkout-return.php` — post-checkout return
- `POST /api/commerce/paypal-webhook.php` — PayPal IPN
- `GET /api/commerce/orders.php` — order history
- `GET /api/commerce/stats.php` — commerce stats
- `POST /api/commerce/webhook.php` — general webhook
- `POST /api/commerce/crypto-confirm.php` — crypto payment confirm

### Form (kit wrappers)
- `POST /api/form/submit.php` — form submission
- `GET /api/form/submissions.php` — list submissions
- `GET /api/form/config.php` — form config
- `GET /api/form/stats.php` — form stats
- `POST /api/form/mark-read.php` — mark submission read

### Admin (session auth + CSRF — 48 endpoints)
- `/api/admin/login.php`, `logout.php`, `session.php` — auth
- `/api/admin/forgot-password.php`, `setup-password.php`, `verify-token.php` — password management
- `/api/admin/account.php` — admin account settings
- `/api/admin/admins.php` — admin user CRUD
- `/api/admin/appointments.php` — appointment CRUD
- `/api/admin/customers.php` — customer CRUD + search
- `/api/admin/vehicles.php` — vehicle CRUD per customer
- `/api/admin/repair-orders.php` — RO lifecycle + appointment-to-RO conversion
- `/api/admin/inspections.php` — inspection CRUD + complete + send
- `/api/admin/inspection-photos.php` — photo upload/delete
- `/api/admin/estimates.php` — estimate CRUD + auto-generate from inspection + send
- `/api/admin/employees.php` — employee CRUD
- `/api/admin/schedules.php` — employee schedules
- `/api/admin/vin-decode.php` — admin VIN decode (no rate limit)
- `/api/admin/tire-fitment.php` — admin tire fitment (no rate limit)
- `/api/admin/blog.php` — blog post CRUD
- `/api/admin/faq.php` — FAQ CRUD
- `/api/admin/promotions.php` — promotions CRUD
- `/api/admin/testimonials.php` — testimonials CRUD
- `/api/admin/conversations.php` — messaging management
- `/api/admin/messages.php` — contact message CRUD
- `/api/admin/subscribers.php` — subscriber management
- `/api/admin/gallery.php` — gallery image CRUD
- `/api/admin/service-images.php` — service image CRUD
- `/api/admin/site-settings.php` — site settings CRUD
- `/api/admin/email-logs.php` — email log viewer
- `/api/admin/email-template-vars.php` — template variable reference
- `/api/admin/analytics.php` — dashboard analytics
- `/api/admin/export.php` — data export
- `/api/admin/invoices.php` — invoice CRUD + generate from RO
- `/api/admin/labor.php` — labor hours tracking per RO
- `/api/admin/loyalty.php` — loyalty points management
- `/api/admin/loyalty-rewards.php` — loyalty rewards catalog
- `/api/admin/referrals.php` — referral management (list, mark complete, award points)
- `/api/admin/service-reminders.php` — service reminder management
- `/api/admin/waitlist.php` — walk-in queue management
- `/api/admin/tire-quotes.php` — tire quote request management
- `/api/admin/visit-log.php` — visit tracking log
- `/api/admin/google-business-sync.php` — Google Business Profile sync
- `/api/admin/business-hours.php` — business hours configuration
- `POST /api/admin/push-broadcast.php` — admin push broadcast to opted-in subscribers (5/day limit)
- `GET /api/admin/email-check.php` — manual IMAP fetch trigger, returns count of new emails
- `GET /api/admin/resource-planner.php?dates=` — multi-date resource planning (employees, skill gaps, hourly breakdown, recommendations)

## Public Pages (36 pages)

### Core
- `index.php` — homepage
- `contact.php` — contact form
- `faq.php` — FAQ
- `why-us.php` — about/value prop
- `reviews.php` — Google Reviews display
- `guarantee.php` — service guarantee
- `members.php` — member login/register/dashboard
- `blog.php` / `blog-post.php` — blog listing / single post
- `promotions.php` — current promotions
- `care-plan.php` — care plan info + enrollment
- `checkout.php` — payment checkout

### Service Pages
- `tire-installation.php`, `tire-repair.php`, `wheel-alignment.php`
- `brake-service.php`, `oil-change.php`, `engine-diagnostics.php`
- `suspension-repair.php`, `mobile-service.php`
- `fleet-services.php`, `roadside-assistance.php`

### Regional SEO Pages
- `service-areas.php` — service areas overview
- `tires-se-portland.php`, `tires-foster-powell.php`, `tires-woodstock.php`
- `tires-lents.php`, `tires-mt-scott.php`, `tires-happy-valley.php`
- `tires-clackamas.php`, `tires-milwaukie.php`

### Booking / Appointment
- `book-appointment/` — booking form (VIN decode + plate lookup + SMS opt-in)
- `cancel.php` — appointment cancellation
- `reschedule.php` — appointment rescheduling

### Customer Portals (token-based)
- `inspection.php` — bilingual DVI report with photos + print
- `approve.php` — bilingual estimate approval with per-item approve/decline + print

### Utility
- `send-setup-emails.php` — admin setup email trigger

## Includes (21 files)
- `bootstrap.php` — .env loader, DB connection, session, error tracking init
- `db.php` — PDO connection helper
- `auth.php` — session auth, role checks, CSRF
- `mail.php` — PHPMailer: sendInspectionEmail, sendEstimateEmail, sendApprovalConfirmationEmail, sendReadyEmail, sendBrandedTemplateEmail, sendEmailReply
- `email-fetcher.php` — IMAP inbound email fetcher (webklex/php-imap): EmailFetcher class, threading via Message-ID/In-Reply-To/References
- `response.php` — JSON response helper (X-API-Version header)
- `validate.php` — input validation helpers
- `rate-limit.php` — API rate limiting
- `schedule.php` — appointment scheduling logic
- `vin-decode.php` — NHTSA vPIC API + DB cache + findOrCreateCustomer/Vehicle + generateRoNumber/EstimateNumber
- `tire-fitment.php` — tire fitment API + 90-day DB cache
- `google-reviews.php` — Google Places API review fetcher + DB cache
- `smart-account.php` — auto-link booking customers to member accounts
- `seo-config.php` — per-page SEO metadata config
- `seo-head.php` — SEO meta tag renderer (canonical, OG, JSON-LD)
- `image-helpers.php` — `responsiveImage()` for AVIF/WebP/fallback `<picture>` tags
- `sms.php` — Twilio scaffold (sendInspectionSms, sendEstimateSms, sendReadySms)
- `member-kit-init.php` — member-kit loader
- `member-translations.php` — bilingual auth UI strings
- `engine-kit-init.php` — engine-kit error tracking loader
- `push.php` — Web Push utility: VAPID key management, subscription CRUD, notification queuing, queue processor (minishlink/web-push)

## CLI Scripts
- `send-reminders.php` — appointment reminders for next day (cron)
- `send-review-requests.php` — review request emails (cron)
- `send-welcome-emails.php` — welcome emails for new subscribers
- `fetch-google-reviews.php` — pull latest Google Reviews (cron)
- `indexnow-submit.php` — submit URLs to Bing IndexNow
- `seed-email-templates.php` — seed/update email templates
- `resend-setup-emails.php` — resend admin setup tokens
- `create-admins-feb2026.php`, `create-joslyn-admin.php` — one-time admin creation
- `list-admins.php` — list admin accounts
- `test-email-account.php`, `test-smtp-debug.php` — SMTP diagnostics
- `send-service-reminders.php` — automated service due date reminders (weekly cron Mon 9AM)
- `sync-google-business.php` — Google Business Profile sync (weekly cron Mon 7AM)
- `generate-vapid-keys.php` — one-time VAPID key pair generation for Web Push
- `send-push-notifications.php` — push notification queue processor (cron, every 5 min)
- `fetch-inbound-emails.php` — IMAP inbound email fetch into conversations (cron, every 2 min)

## Cron Jobs (on server)
```
0 18 * * *  cli/send-reminders.php              # appointment reminders for next day (email + SMS + push)
0 10 * * *  cli/send-review-requests.php       # review request emails
0 6  * * *  cli/fetch-google-reviews.php       # refresh Google Reviews cache
*/5 * * * * cli/send-push-notifications.php    # push notification queue processor
0 9  * * 1  cli/send-service-reminders.php     # automated service due date reminders (Mon 9AM)
0 7  * * 1  cli/sync-google-business.php       # Google Business Profile sync (Mon 7AM)
*/2 * * * * cli/fetch-inbound-emails.php      # IMAP inbound email fetch (every 2 min)
```

## Admin Panel
- **Tabs**: Dashboard, Appointments, Customers, Repair Orders, Messages, Employees, Blog, FAQ, Promotions, Testimonials, Subscribers, Feedback, Gallery, Settings, Resource Planner, Referrals
- **RO Tab**: Table view + kanban board (drag-and-drop), status timeline, create from appointment or walk-in
- **Member Dashboard** (`/members`): 8 tabs — Appointments, Vehicles, Estimates, Messages, Care Plan, Invoices, Loyalty Points, Refer a Friend
- **Employee Dashboard**: My Schedule, My Assigned Work, My Customers (via member portal)

### Admin JS
- `admin/js/repair-orders.js` — RO tab, inspection, estimate management
- `admin/js/kanban.js` — kanban board (drag-and-drop status changes, time-in-status)
- `admin/js/blog.js` — blog post editor
- `admin/js/faq.js` — FAQ management
- `admin/js/promotions.js` — promotion management
- `admin/js/testimonials.js` — testimonial management
- `admin/js/subscribers.js` — subscriber management
- `admin/js/ot-charts.js` — dashboard charts (includes stackedHorizontalBars)
- `admin/js/resource-planner.js` — resource planner tab (grid, heatmap, skills matrix, recommendations)
- `admin/js/brand-toast.js` — branded toast notifications
- `admin/js/admin-analytics.js` — enhanced analytics dashboard
- `admin/js/labor-tracker.js` — labor hours tracking UI
- `admin/js/visit-tracker.js` — visit tracking UI
- `admin/js/referrals.js` — referral management tab (list, filter, mark complete, award points)
- `admin/js/feature-data.js` — feature configuration data

### Frontend JS
- `assets/js/pwa-manager.js` — PWA install prompt (Android + iOS), push subscription, online/offline indicator
- `assets/js/offline-booking.js` — IndexedDB queue for offline bookings + Background Sync fallback
- `assets/js/exit-intent.js` — exit intent popup
- `assets/js/scroll-reveal.js` — scroll animation
- `assets/js/htmx.min.js` — HTMX for partial page updates

## .env Variables
See `.env.example` for full template. Key additions beyond DB/SMTP:
- `APP_SECRET` — session/token signing secret
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` — Google OAuth
- `SENTRY_DSN`, `SENTRY_DSN_JS` — error tracking (optional)
- `GOOGLE_SITE_VERIFICATION`, `BING_SITE_VERIFICATION` — search console
- `INDEXNOW_KEY` — Bing fast indexing
- `SYNC_API_KEY` — cross-site activity reporting to HHW network
- `VAPID_SUBJECT` — Web Push VAPID subject (e.g. `mailto:info@oregon.tires`); VAPID keys stored in DB

## SEO
- `includes/seo-config.php` — per-page title, description, canonical, OG tags
- `includes/seo-head.php` — renders meta tags, JSON-LD Organization schema
- `api/sitemap.php` — dynamic XML sitemap (services, blog, regions)
- Regional pages target Portland-area neighborhoods for local SEO
- IndexNow integration for fast Bing indexing of new content
