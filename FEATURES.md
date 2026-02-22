# Oregon Tires Auto Care -- Feature List

> **Last updated:** 2026-02-22
> **Stack:** Static HTML/CSS/JS | PHP API + MySQL + PHPMailer | cPanel/Apache Hosting
> **URL structure:** `index.html` (public site), `admin/index.html` (dashboard), `book-appointment/index.html` (redirect stub), `contact.php`, `cancel.php`, `reschedule.php`, `checkout.php`, `blog.html`, `blog-post.html`, `privacy.html`, `feedback.html`

---

## Table of Contents

1. [Public Website](#1-public-website)
   - [Navigation and Layout](#11-navigation-and-layout)
   - [Hero Section](#12-hero-section)
   - [Services Section](#13-services-section)
   - [About Section](#14-about-section)
   - [Reviews Section](#15-reviews-section)
   - [Gallery Section](#16-gallery-section)
   - [Contact Section](#17-contact-section)
   - [Bilingual System](#18-bilingual-system-englishspanish)
   - [SEO and Structured Data](#19-seo-and-structured-data)
   - [External Integrations](#110-external-integrations)
   - [Asset Management](#111-asset-management)
2. [Admin Dashboard](#2-admin-dashboard)
   - [Authentication](#21-authentication)
   - [Overview Tab](#22-overview-tab)
   - [Appointments Tab](#23-appointments-tab)
   - [Messages Tab](#24-messages-tab)
   - [Employees Tab](#25-employees-tab)
   - [Gallery Tab](#26-gallery-tab)
   - [Analytics Tab](#27-analytics-tab)
   - [Data Refresh](#28-data-refresh)
   - [Account Settings](#29-account-settings)
   - [Utility Functions](#210-utility-functions)
   - [Documentation Tab](#211-documentation-tab)
3. [Book Appointment Page](#3-book-appointment-page)
4. [Customer Self-Service](#4-customer-self-service)
5. [NEW Features (Pending Client Approval)](#5-new-features-pending-client-approval)
   - [Blog / SEO Content Hub](#51-blog--seo-content-hub)
   - [Post-Service Feedback System](#52-post-service-feedback-system)
   - [Employee Schedule Management](#53-employee-schedule-management)
   - [SMS Appointment Reminders](#54-sms-appointment-reminders-twilio)
   - [WhatsApp Integration](#55-whatsapp-integration-twilio)
   - [Privacy Policy Page](#56-privacy-policy-page)
   - [Admin Dashboard Enhancements](#57-admin-dashboard-enhancements)
   - [Homepage Enhancements](#58-homepage-enhancements)
   - [Appointment Workflow Enhancements](#59-appointment-workflow-enhancements)
   - [Schedule-Aware Availability](#510-schedule-aware-availability)
6. [Database Schema](#6-database-schema)
7. [Infrastructure](#7-infrastructure)
8. [Shared Kit Integrations](#8-shared-kit-integrations)

---

## 1. Public Website

**File:** `public_html/index.html` (single-file SPA)

### 1.1 Navigation and Layout

| Feature | Details |
|---------|---------|
| Top info bar | Phone, email, address, business hours, social links, language toggle |
| Sticky header | Logo + desktop nav, pinned to top on scroll (`sticky top-0 z-50`) |
| Desktop nav links | Home, Services, About, Reviews, Gallery, Contact, Schedule Service (CTA button) |
| Mobile hamburger menu | Toggle-based `hidden` menu with identical link set |
| Smooth scroll | `html { scroll-behavior: smooth; }` with `scroll-mt-24` offsets on each section |
| Responsive design | Tailwind CSS breakpoints: `md:` (tablet), `lg:` (desktop), mobile-first |
| Fade-in animations | `.fade-in` keyframe animation on content elements (opacity + translateY) |

### 1.2 Hero Section

- Dynamic background image loaded from PHP API `/api/service-images.php` (key: `hero-background`)
- Local fallback image (`assets/hero-bg.png`) if API URL fails or is unavailable
- Semi-transparent black overlay (`bg-black/50`) for text readability
- Bilingual headline and subtitle (swapped via `data-t` translation keys)
- Two CTA buttons:
  - **"Contact Us"** -- smooth-scrolls to `#contact`
  - **"Schedule Service"** -- navigates to `/book-appointment`

### 1.3 Services Section

**7 Feature Cards:**

| Card | ID | Fallback Image |
|------|----|----------------|
| Expert Technicians | `svc-img-expert-technicians` | `images/expert-technicians.jpg` |
| Fast Service | `svc-img-fast-cars` | `images/fast-cars.jpg` |
| Quality Parts | `svc-img-quality-car-parts` | `images/quality-parts.jpg` |
| Bilingual Support | `svc-img-bilingual-support` | `images/bilingual-service.jpg` |
| Tire Services | `svc-img-tire-shop` | `images/tire-services.jpg` |
| Auto Maintenance | `svc-img-auto-repair` | `images/auto-maintenance.jpg` |
| Specialized Services | `svc-img-specialized-tools` | `images/specialized-services.jpg` |

- Each card loads its background image dynamically from the PHP API with position and scale controls
- `tryLoadImage()` validates every URL before applying -- broken URLs fall back to local images
- Cards display a title and description, both fully translatable

**Three Service Category Lists:**

| Category | Services Listed |
|----------|-----------------|
| Tire Services | New or Used Tires, Mount and Balance Tires, Tire Repair |
| Auto Maintenance | Oil Change, Brake Services, Tuneup |
| Specialized Services | Alignment, Mechanical Inspection, Mobile Service (At Your Home), Roadside Assistance (Any Location) |

**Emergency Service Callout:**
- Red-bordered alert box in the Specialized Services card
- Direct phone number call-to-action: (503) 367-9714

**Service Promise Section:**
- Three pillars displayed in a branded banner:
  - Quality Guarantee
  - Fair Pricing
  - Expert Service
- Descriptive text about commitment to quality, transparency, and satisfaction

### 1.4 About Section

- **Catchphrase:** "We take care of your car, you enjoy the road."
- **Vision statement:** "To be the most trusted tire shop in Portland, Oregon."
- **Mission statement:** Dedication to high-quality tires, reliable mechanical services, and personalized attention
- **"Why Choose Us" list:**
  - Bilingual staff (English and Spanish)
  - Honest, transparent pricing
  - Quality workmanship guaranteed
  - Fast and reliable service
- **History block:** "Serving Portland Since 2008" with 15+ years narrative

### 1.5 Reviews Section

- **12 hardcoded reviews** (mix of English and Spanish text)
- **Random selection:** 3 reviews displayed per page load via `Array.sort(() => 0.5 - Math.random()).slice(0, 3)`
- **Star rating display:** Unicode filled stars and empty stars (e.g., five filled stars for a 5-star review)
- **"Verified" badge** on each review card
- **Aggregated rating display:**
  - 4.8 out of 5 stars
  - "Based on 150+ Google Reviews"
  - Five filled stars rendered in yellow
- **External link:** "View All Reviews on Google" button linking to Google search results

### 1.6 Gallery Section

- Dynamic image grid loaded from PHP API (`GET /api/gallery.php`)
- Filters by `is_active = true`, ordered by `display_order`
- **Hover zoom animation:** `group-hover:scale-105` with CSS transition on images
- Optional title and description displayed below each image (if populated)
- Loading placeholder text: "Loading gallery..."
- Fallback message: "No gallery images yet." when the table is empty
- **Gallery reloads on language switch** to refresh any language-specific content

### 1.7 Contact Section

**Contact Form (5 fields):**

| Field | Type | Required |
|-------|------|----------|
| First Name | text | Yes |
| Last Name | text | Yes |
| Phone | tel | Yes |
| Email | email | Yes |
| Message | textarea (4 rows) | Yes |

- Form submits to PHP API endpoint `POST /api/contact.php`
- Stores the current language with the message (`english` or `spanish`)
- Admin notification email sent via PHPMailer using bilingual DB-driven templates
- Reply-To header set to the customer's email address
- **Success feedback:** Green text -- "Message sent successfully!" / Spanish equivalent
- **Error feedback:** Red text -- "Error sending message. Please try again." / Spanish equivalent

**Contact Info Display:**

| Item | Value |
|------|-------|
| Phone | (503) 367-9714 (clickable `tel:` link) |
| Email | oregontirespdx@gmail.com (clickable `mailto:` link in top bar and footer) |
| Address | 8536 SE 82nd Ave, Portland, OR 97266 |
| Hours | Mon-Sat 7AM-7PM, Sunday: Closed |

**Google Maps Embed:**
- Full-width iframe pointing to 8536 SE 82nd Ave, Portland, OR 97266
- Lazy-loaded with `loading="lazy"` attribute
- Rounded corners matching site design

### 1.8 Bilingual System (English/Spanish)

| Aspect | Implementation |
|--------|----------------|
| Toggle button | Top bar and footer -- switches between EN/ES |
| Translation keys | 74+ keys covering all UI text |
| Attribute system | `data-t` attribute on all translatable elements |
| Toggle function | `toggleLanguage()` swaps all `data-t` elements simultaneously |
| Language state | `currentLang` variable (`'en'` or `'es'`) |
| Gallery reload | Gallery images reload on language switch |
| Form messages | Success/error messages display in the active language |
| Contact storage | Language is stored with each submitted contact message |
| Email templates | DB-driven bilingual templates (`value_en` / `value_es` columns in `oretir_site_settings`) |

### 1.9 SEO and Structured Data

**Meta Tags:**
- `<title>` -- "Oregon Tires Auto Care - Professional Tire & Auto Services in Portland"
- `<meta name="description">` -- full business description with phone number
- `<meta name="keywords">` -- tires, auto care, brake service, oil change, Portland Oregon, bilingual
- `<meta name="author">` -- Oregon Tires Auto Care
- `<meta name="viewport">` -- responsive viewport

**Open Graph Tags:**
- `og:title` -- Oregon Tires Auto Care
- `og:description` -- serving Portland since 2008
- `og:type` -- website
- `og:locale` -- en_US

**Schema.org JSON-LD:**
- Type: `AutomotiveBusiness`
- Name, description, telephone, full postal address
- Opening hours: `Mo-Sa 07:00-19:00`
- Price range: `$$`

**Semantic HTML:** Proper use of `<header>`, `<section>`, `<footer>`, `<nav>`, heading hierarchy

### 1.10 External Integrations

| Service | Usage |
|---------|-------|
| PHP API (custom REST endpoints) | Database queries, file uploads, email, authentication |
| PHPMailer | SMTP email delivery with bilingual DB-driven templates |
| Tailwind CSS v4 (compiled) | All styling via utility classes, built from `src/input.css` |
| Google Maps Embed API | Location iframe in contact section |
| Google Analytics | Tracking ID stored in `oretir_site_settings` |
| Instagram | Social link to `instagram.com/oregontires` |
| Facebook | Social link to business page |

### 1.11 Asset Management

**`normalizeAssetPath()` function:**
- Converts Lovable platform `/lovable-uploads/` UUID paths to clean `/assets/` paths
- Used for backward compatibility with migrated assets

**ASSET_PATH_MAP (3 core assets):**

| UUID | Clean Filename |
|------|----------------|
| `1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png` | `logo.png` |
| `afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png` | `hero-bg.png` |
| `b0182aa8-dde3-4175-8f09-21b6122f47f4.png` | `favicon.png` |

**`tryLoadImage()` function:**
- Creates a temporary `Image` object to validate URLs before applying as CSS backgrounds
- Returns a Promise that resolves on successful load, rejects on error
- On rejection, the fallback local image path is used instead

**Path strategy:** All paths are relative (no leading `/`) for cPanel subdirectory compatibility

**Service Worker (`sw.js`):**
- `CACHE_VERSION` constant -- bumped on each deploy to invalidate stale caches
- Caches static assets for offline/fast repeat loads

---

## 2. Admin Dashboard

**File:** `public_html/admin/index.html` (~3,870 lines -- single-file SPA with embedded documentation)
**Access:** `noindex, nofollow` meta tag prevents search engine indexing

### 2.1 Authentication

| Feature | Details |
|---------|---------|
| Login method | Email/password via PHP session-based auth (`POST /api/admin/login.php`) |
| Admin verification | Checks `oretir_admins` table for matching email, verifies BCrypt password hash, confirms `is_active = 1` |
| Role-based access | `role` column in `oretir_admins`: `admin` or `superadmin` (superadmin sees Improvements tab in docs) |
| Account lockout | After 5 failed attempts, account locked for 15 minutes (`locked_until` column) |
| Session restore | On page load, calls `GET /api/admin/session.php` to check for valid PHP session |
| Sign out | `POST /api/admin/logout.php` destroys PHP session and returns to login screen |
| Login feedback | Error messages displayed below form; button shows "Signing in..." during auth |
| Password setup | New admins receive email with setup link via `/admin/setup-password.html` |
| Forgot password | `POST /api/admin/forgot-password.php` sends reset link with time-limited token |

### 2.2 Overview Tab

**14-Day Appointment Statistics (4 stat cards):**

| Metric | Color | Description |
|--------|-------|-------------|
| Total Appointments | Brand green | Count of all appointments in the next 14 days |
| Pending | Blue | Appointments with status `new` |
| Confirmed | Green | Appointments with status `confirmed` |
| Completed | Gray | Appointments with status `completed` |

**Upcoming Appointments Panel:**
- Groups appointments by date for the next 14 days
- Each date shows day label, appointment count badge, and up to 3 appointment previews
- Today's date highlighted with yellow ring and star icon
- Dates with appointments have green left border; empty dates show "Open"
- Shows customer name, time, service, and status badge per appointment

**Employee Schedule Panel:**
- Lists all active employees with their 14-day appointment counts
- Shows estimated hours (appointments x 1.5 hours)
- Displays next upcoming appointment date for each employee

### 2.3 Appointments Tab

**Two sub-views toggled via sub-tabs:**

#### Calendar View

| Feature | Details |
|---------|---------|
| Monthly calendar grid | 7-column grid (Sun-Sat) with day numbers |
| Month navigation | Previous/Next month buttons |
| Day navigation | Previous/Next day buttons for the detail panel |
| Color coding | Green background + border = has appointments; Yellow ring = today; Brand color fill = selected |
| Appointment count | Per-day count displayed inside calendar cells |
| Legend | Visual guide for color meanings |
| Day detail panel | Full appointment list for the selected day (spans 2 columns) |
| Per-appointment controls | Employee assignment dropdown, status dropdown, notes button |
| Quick Assign All | Round-robin distribution of unassigned appointments across active employees for a given day |
| Unassigned warning | Orange banner showing count of unassigned appointments with quick-assign button |

#### List View

| Feature | Details |
|---------|---------|
| Search | Text search across name, email, phone, service, status |
| Status filter | Dropdown: All, New, Confirmed, Completed, Cancelled |
| Month filter | Dropdown populated with last 24 months |
| Per-page control | 10, 25, 50, 100, or All results |
| Sortable columns | Customer, Service, Date & Time, Status (click to toggle asc/desc) |
| Pagination | Numbered page buttons with Previous/Next navigation |
| Per-row actions | Status dropdown, employee assignment dropdown, notes button |
| Reference number | Each appointment has a unique `OT-XXXXXXXX` reference number for tracking |

**Appointment Actions:**
- **Assign employee:** Dropdown of active employees; auto-confirms status on assignment
- **Change status:** New, Confirmed, Completed, Cancelled
- **Confirmation guard:** Cannot confirm an appointment without an assigned employee
- **Admin notes:** Modal with textarea for per-appointment notes, saved to `admin_notes` column
- **Email notifications:** Triggered via PHPMailer (`includes/mail.php`) using `sendBrandedTemplateEmail()` on:
  - Employee assignment (`appointment_assigned`)
  - Completion (`appointment_completed`)
  - Booking confirmation (sent to customer at time of booking)

### 2.4 Messages Tab

**Two sub-tabs:**

#### Contact Messages Sub-Tab

| Feature | Details |
|---------|---------|
| Message table | Columns: Customer, Contact (email + phone), Message (truncated), Date, Status |
| Status management | Dropdown per message: New, Priority, Completed |
| Full message modal | Shows name, email, phone, date, full message text, and status selector |
| Read full link | Appears when message exceeds 80 characters |
| Refresh button | Manual reload of messages from PHP API |

#### Website Changes Sub-Tab

| Feature | Details |
|---------|---------|
| Email history list | Card-based layout showing all sent emails |
| Type badges | Color-coded: Confirmation (blue), Assignment (yellow), Completion (green) |
| Recipient type badge | Shows whether email was sent to customer or employee |
| Email detail modal | Shows subject, To, Type, Sent date, and full HTML body |
| Refresh button | Manual reload of email logs |

### 2.5 Employees Tab

**Employee Management:**

| Feature | Details |
|---------|---------|
| Add employee form | Fields: Name (required), Email, Phone, Role (Employee/Manager) |
| Account creation | If email provided, PHP API creates admin account and sends setup credentials via PHPMailer |
| Edit employee | Inline edit form with Name, Email, Phone, Role fields |
| Toggle active/inactive | Activate or deactivate employees (soft delete) |
| Upcoming appointments | Badge showing count and next appointment date per active employee |
| Role display | Badge showing Employee or Manager role |

**Admin Privilege Management:**

| Feature | Details |
|---------|---------|
| Add admin form | Email input; creates `oretir_admins` record with setup token |
| Grant admin access | PHP endpoint `POST /api/admin/admins.php` creates account and sends setup email |
| Revoke admin access | Sets `is_active = 0` in `oretir_admins` with confirmation dialog |
| Admin-only user display | Separate section showing dashboard admins not in employee list |
| Last login info | Shows last login date or account creation date for each admin |
| Admin badge | Green shield badge on employees who also have admin access |

### 2.6 Gallery Tab

**Two sub-tabs:**

#### Gallery Images Sub-Tab

| Feature | Details |
|---------|---------|
| Upload form | File picker (image only), Title (required), Language (English/Spanish), Description |
| Upload process | File uploaded to server `uploads/` directory via PHP endpoint `POST /api/admin/gallery.php`; URL stored in `oretir_gallery_images` |
| Image list | Card layout with thumbnail, title, language, display order, active status, description |
| Delete | Removes file from server storage and database record with confirmation dialog |

#### Service Images Sub-Tab

**Manages 8 service hero images:**

| Position | Service Key | Display Title |
|----------|-------------|---------------|
| 1 | `hero-background` | Hero Background Image |
| 2 | `expert-technicians` | Expert Technicians |
| 3 | `fast-cars` | Quick Service |
| 4 | `quality-car-parts` | Quality Parts |
| 5 | `bilingual-support` | Bilingual Support |
| 6 | `tire-shop` | Tire Services |
| 7 | `auto-repair` | Auto Maintenance |
| 8 | `specialized-tools` | Specialized Services |

| Feature | Details |
|---------|---------|
| Image preview | Live preview showing current image with position and zoom applied |
| Horizontal position slider | Range 0-100%, controls `background-position-x` |
| Vertical position slider | Range 0-100%, controls `background-position-y` |
| Zoom slider | Range 100-300%, controls `background-size` |
| Live preview | Slider changes update the preview image in real time |
| "Live" badge | Green badge on images currently active on the website (`is_current = true`) |
| Upload new image | File picker per service position; marks old image as not current, inserts new as current |
| Save position | Saves position_x, position_y, and scale to database |

### 2.7 Analytics Tab

**Appointment Statistics:**

| Metric | Description |
|--------|-------------|
| Total Appointments | All-time count |
| This Week | Appointments created in the last 7 days |
| By Status | Breakdown into New, Confirmed, Completed, Cancelled (4 color-coded cards) |

**Customer Analysis:**

| Metric | Description |
|--------|-------------|
| Total Customers | Unique email addresses across all appointments |
| Recurring Customers | Customers with more than one appointment |

**Messages Overview:**

| Metric | Description |
|--------|-------------|
| Total Messages | All contact messages received |
| Unread Messages | Messages with status `new` |

**Time Tracking:**
- Average appointment duration in minutes (from completed appointments with `actual_duration_minutes`)
- Shows sample size (number of completed appointments used in calculation)

**Employee Performance Table:**

| Column | Description |
|--------|-------------|
| Employee | Name |
| Total | Total assigned appointments |
| Completed | Completed appointments count |
| Total Time | Sum of all `actual_duration_minutes` |
| Avg Time | Average minutes per completed appointment |

**Popular Appointment Times:**
- Top 10 most frequently requested times
- Horizontal bar chart with percentage fill
- Shows formatted time (12-hour AM/PM) and count

### 2.8 Data Refresh

| Feature | Details |
|---------|---------|
| Manual refresh | Refresh buttons on each tab reload data from PHP API endpoints |
| Periodic polling | Dashboard periodically fetches updated data from API |
| Legacy note | Supabase Realtime was removed during migration; data updates are now pull-based |

### 2.9 Account Settings

Accessible via clicking the admin email/role display in the header. Opens a modal with setting panels:

| Setting | Details |
|---------|---------|
| Admin Notification Email | CC address for all appointment confirmations, employee emails, and assignment notifications; stored in `oretir_site_settings` table |
| Update Display Name | Updates `display_name` in `oretir_admins` via `PUT /api/admin/account.php` |
| Update Email | Updates email in `oretir_admins` via `PUT /api/admin/account.php` |
| Update Password | Minimum 6 characters, confirmation field required to match; updates BCrypt hash via `PUT /api/admin/account.php` |

### 2.10 Utility Functions

| Function | Purpose |
|----------|---------|
| `showToast(msg, isError)` | Bottom-right toast notification, auto-hides after 3 seconds; green for success, red for error |
| `statusBadge(status)` | Returns color-coded HTML badge (blue=new/pending, green=confirmed, gray=completed, red=cancelled, yellow=priority, indigo=read/replied) |
| `fmtDate(d)` | Formats date string to "Mon DD, YYYY" |
| `fmtTime(t)` | Converts 24-hour time to 12-hour AM/PM format |
| `fmtDateTime(d)` | Full locale date+time string |
| `esc(s)` | HTML escaping via `document.createElement('div').textContent` (XSS prevention) |
| `closeModal(id)` | Adds `hidden` class to modal by ID |
| Modal overlay click | All modals close when clicking the semi-transparent overlay |

### 2.11 Documentation Tab

**Self-documenting admin panel** with four sub-tabs toggled via `switchDocsView()`:

| Sub-Tab | Content |
|---------|---------|
| Manual | Full instruction manual for the admin team (Getting Started, Appointments, Messages, Employees, Gallery, Service Images, Analytics, Account Settings, Public Website Features, Troubleshooting) |
| Features | Complete feature list documenting every function of the public site, admin dashboard, database schema, and infrastructure |
| Improvements | Prioritized improvement roadmap with Critical, High Priority, Medium Priority, and Low Priority items plus a 4-week action plan (superadmin only) |
| Roadmap | Future development roadmap and planned features |

- Sub-tab toggle: `switchDocsView(view)` shows/hides content divs and updates button styles
- Manual sub-tab visible by default; Features, Improvements, and Roadmap start hidden
- Scrollable content areas with `max-h-[75vh] overflow-y-auto`
- All documentation rendered as styled HTML with Tailwind CSS classes

---

## 3. Book Appointment Page

**File:** `public_html/book-appointment/index.html` (11 lines)

- Immediate redirect to main site contact form (`/#contact`)
- `<meta http-equiv="refresh" content="0; url=/#contact">` for instant redirect
- Fallback `<a href="/#contact">` link if meta refresh fails
- No JavaScript redirect (meta-only approach)

---

## 4. Customer Self-Service

### 4.1 Appointment Cancellation

**File:** `public_html/cancel.php`

| Feature | Details |
|---------|---------|
| Token-based access | Unique `cancel_token` (64 hex chars) generated at booking time |
| Token expiration | Tokens expire after a configurable period (`cancel_token_expires` column) |
| Confirmation page | Displays appointment details and asks customer to confirm cancellation |
| Status update | Sets appointment status to `cancelled` on confirmation |
| Bilingual | Page renders in the language stored with the appointment |

### 4.2 Appointment Rescheduling

**File:** `public_html/reschedule.php`

| Feature | Details |
|---------|---------|
| Token-based access | Uses the same `cancel_token` as cancellation |
| Date/time picker | Customer selects new preferred date and time |
| Available times check | Calls `/api/available-times.php` to show only open slots |
| Status preservation | Appointment remains in current status after rescheduling |
| Bilingual | Page renders in the language stored with the appointment |

### 4.3 Appointment Reminders

**Cron job:** `cli/send-reminders.php` (runs daily at 6 PM via server cron)

| Feature | Details |
|---------|---------|
| Target | Appointments scheduled for the next day with `reminder_sent = 0` |
| Email content | Bilingual reminder with appointment details, cancel/reschedule links |
| Sent tracking | Sets `reminder_sent = 1` after successful send to prevent duplicates |
| Template | Uses `sendBrandedTemplateEmail()` from `includes/mail.php` |

---

## 5. NEW Features (Pending Client Approval)

> **Status:** These features were built proactively and are **not yet approved by the client**. They are deployed and functional but may be disabled or removed based on client feedback. Each feature is self-contained and can be independently toggled.
>
> **Added:** 2026-02-22

### 5.1 Blog / SEO Content Hub

**Status:** `NEW` | **Files:** `blog.html`, `blog-post.html`, `api/blog.php`, `api/admin/blog.php`, `cli/migrate-blog.php`, `cli/seed-blog.php`

| Feature | Details |
|---------|---------|
| Blog listing page | `blog.html` -- paginated grid of published blog posts with thumbnails |
| Blog post page | `blog-post.html` -- individual post view with share/copy link |
| Bilingual content | Each post has `title_en`, `title_es`, `body_en`, `body_es`, `excerpt_en`, `excerpt_es` |
| Public API | `GET /api/blog.php` -- list published posts (paginated); `GET /api/blog.php?slug=xxx` -- single post |
| Admin CRUD | `GET/POST/PUT/DELETE /api/admin/blog.php` -- full blog management with auto-slug generation |
| Categories | `oretir_blog_categories` and `oretir_blog_post_categories` many-to-many junction |
| Seed content | 4 bilingual posts: tire signs, wheel alignment, Portland weather tires, brake maintenance |
| Dark mode | Full dark mode support matching site design |
| SEO | Added to `sitemap.xml`, meta tags per post |

### 5.2 Post-Service Feedback System

**Status:** `NEW` | **Files:** `feedback.html`, `api/feedback.php`, `api/admin/feedback.php`, `cli/send-feedback-requests.php`, `cli/migrate-feedback.php`

| Feature | Details |
|---------|---------|
| Feedback page | `feedback.html` -- star rating (1-5) + optional comment, token-based access |
| Google Review redirect | After submitting feedback, prominent CTA to leave a Google Review (Place ID: `ChIJLSxZDQyflVQRWXEi9LpJGxs`) |
| Automated emails | CLI script sends feedback requests 24h after completed appointments |
| Token-based security | 64-char hex tokens with 7-day expiry, one feedback per appointment |
| Admin dashboard | `GET /api/admin/feedback.php` -- feedback list with aggregate stats (avg rating, breakdown by star) |
| Bilingual | Star labels in EN/ES ("Poor"/"Malo" through "Excellent"/"Excelente") |
| Rate limited | 5 requests/hour per IP |
| Suggested cron | `0 10 * * *` -- `cli/send-feedback-requests.php` |

### 5.3 Employee Schedule Management

**Status:** `NEW` | **Files:** `api/admin/schedules.php`, `api/admin/schedule-overrides.php`, `cli/migrate-schedules.php`

| Feature | Details |
|---------|---------|
| Weekly schedules | `oretir_schedules` -- recurring schedule per employee per day of week (Mon-Sun) |
| Schedule overrides | `oretir_schedule_overrides` -- date-specific overrides for holidays, PTO, special hours |
| Shop-wide closures | Override with `employee_id = NULL` closes the entire shop for a date |
| Admin API (schedules) | `GET/POST/DELETE /api/admin/schedules.php` -- CRUD with UPSERT support |
| Admin API (overrides) | `GET/POST/DELETE /api/admin/schedule-overrides.php` -- date-range filtering |
| Validation | Employee existence check, day range 0-6, time format validation, end > start |

### 5.4 SMS Appointment Reminders (Twilio)

**Status:** `NEW` -- scaffold only, requires Twilio credentials | **Files:** `includes/sms.php`, `cli/migrate-sms.php`, updated `cli/send-reminders.php`

| Feature | Details |
|---------|---------|
| SMS helper | `sendSMS($to, $message)` via Twilio REST API (cURL, no SDK) |
| Phone normalization | `normalizeSMSPhone()` converts US numbers to E.164 format |
| Audit trail | `logSMS()` logs all SMS attempts to `oretir_sms_logs` |
| Dual-channel reminders | `send-reminders.php` now sends email AND SMS (if configured) |
| Bilingual SMS | EN: "Oregon Tires: Reminder - your [Service] appointment is tomorrow at [Time]." / ES equivalent |
| Graceful degradation | If Twilio env vars missing, SMS silently skips (email-only fallback) |
| New env vars needed | `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER` |

### 5.5 WhatsApp Integration (Twilio)

**Status:** `NEW` -- scaffold only, requires Twilio WhatsApp credentials | **Files:** `includes/whatsapp.php`

| Feature | Details |
|---------|---------|
| WhatsApp helper | `sendWhatsApp($to, $templateName, $params)` via Twilio WhatsApp API |
| Template support | Content Template SID (HX...), friendly template names with `{{1}}` placeholders, or freeform body |
| Phone normalization | Reuses `normalizeSMSPhone()` from SMS helper |
| Graceful degradation | Returns `['success' => false, 'error' => 'WhatsApp not configured']` if env vars missing |
| New env var needed | `WHATSAPP_FROM_NUMBER` |

### 5.6 Privacy Policy Page

**Status:** `NEW` | **Files:** `privacy.html`

| Feature | Details |
|---------|---------|
| Bilingual privacy policy | Full EN/ES privacy policy matching site design |
| Sections covered | Information collected, how used, data sharing, cookies, data security, children's privacy, changes, contact |
| Compliance | Aligned with general US privacy practices |
| Dark mode | Full dark mode support |
| Added to sitemap | `sitemap.xml` entry with hreflang tags |
| Footer link | Added to homepage footer |

### 5.7 Admin Dashboard Enhancements

**Status:** `NEW` | **Files:** `js/admin.js`, `admin/index.html`

| Feature | Details |
|---------|---------|
| Session timeout warning | Amber banner appears 10 minutes before 8-hour session expires with "Extend Session" button |
| Email template preview | Modal with iframe rendering of email templates using sample data, accessible from email templates section |
| Revenue stats | Estimated revenue from completed appointments based on service-to-price mapping |
| Completion rate | Percentage of all-time appointments that reached "completed" status |
| Cancellation rate | Percentage of all-time appointments that were cancelled |
| Calendar capacity bars | Color-coded bars (green/yellow/red) on each calendar day showing appointment count vs MAX_CAPACITY (12) |
| Login autocomplete | `autocomplete="email"` and `autocomplete="current-password"` on login form inputs |

### 5.8 Homepage Enhancements

**Status:** `NEW` | **Files:** `index.html`, `js/main.js`

| Feature | Details |
|---------|---------|
| Service card CTAs | "Book Tire Service", "Book Maintenance", "Book Service" buttons on each of the 3 service category cards |
| GA4 booking tracking | Each CTA fires `gtag('event', 'begin_checkout', {item_category: ...})` for funnel analysis |
| Pricing section CTA | "Schedule Service" button below the pricing grid |
| Blog footer link | Link to `/blog.html` in footer |
| Privacy footer link | Link to `/privacy.html` in footer |
| Contact form honeypot | Hidden `website` field that bots fill out; submissions with this field are silently discarded |
| Success banner auto-dismiss | Contact form success message auto-hides after 6 seconds |
| Error banner auto-dismiss | Contact form error message auto-hides after 8 seconds |
| CTA translations | `bookTireService`, `bookAutoMaintenance`, `bookSpecialized` keys in EN/ES |

### 5.9 Appointment Workflow Enhancements

**Status:** `NEW` | **Files:** `api/appointment-cancel.php`, `api/appointment-reschedule.php`, `api/admin/appointments.php`

| Feature | Details |
|---------|---------|
| Cancel reason tracking | `cancel_reason VARCHAR(500)` column stores why customers cancel |
| Owner cancellation notification | Shop owner receives branded email with appointment details + cancel reason when customer cancels |
| Owner reschedule notification | Shop owner receives email showing old vs new date/time when customer reschedules, notes status reset to "new" |
| Confirmation email on status change | When admin changes status to "confirmed", system sends booking confirmation email to customer with calendar links |
| Token regeneration | Confirmation email generates a new cancel/reschedule token if expired |

### 5.10 Schedule-Aware Availability

**Status:** `NEW` | **Files:** `api/available-times.php`

| Feature | Details |
|---------|---------|
| Shop closure detection | Checks `oretir_schedule_overrides` for shop-wide closures; returns all slots unavailable with `"closed": true` |
| Shop special hours | Shop-wide non-closure overrides narrow the available time window |
| Employee schedule integration | Loads employee schedules for the day of week; counts available employees per hour slot |
| Per-employee overrides | PTO, sick days, or special hours per employee on specific dates |
| Capacity per slot | Each slot response includes `capacity` (number of available employees) |
| Legacy fallback | If no schedule data exists (tables empty), falls back to original behavior (all slots open, capacity 2) |
| Graceful degradation | Schedule table errors caught silently; endpoint never breaks |

---

## 6. Database Schema

**Backend:** MySQL (cPanel shared hosting)
**Database:** `hiphopwo_oregon_tires`
**Prefix:** `oretir_` for all Oregon Tires tables
**Charset:** `utf8mb4` with `utf8mb4_unicode_ci` collation

### Tables Overview

| # | Table | Purpose | Key Columns |
|---|-------|---------|-------------|
| 1 | `oretir_admins` | Admin accounts with role-based access | id, email, password_hash, display_name, role (admin/superadmin), language, notification_email, is_active, login_attempts, locked_until, password_reset_token, password_reset_expires, setup_completed_at, last_login_at |
| 2 | `oretir_employees` | Employee records | id, name, email, phone, role (Employee/Manager), is_active |
| 3 | `oretir_appointments` | Appointment records | id, reference_number, service, preferred_date, preferred_time, vehicle_year, vehicle_make, vehicle_model, first_name, last_name, phone, email, notes, status, cancel_reason, language, reminder_sent, feedback_sent, feedback_sent_at, sms_reminder_sent, phone_verified, assigned_employee_id (FK), admin_notes, cancel_token, cancel_token_expires |
| 4 | `oretir_contact_messages` | Contact form submissions | id, first_name, last_name, email, phone, message, status (new/priority/completed), language |
| 5 | `oretir_gallery_images` | Gallery image records | id, image_url, title, description, is_active, display_order |
| 6 | `oretir_service_images` | Service section hero images | id, service_key (unique per current), image_url, position_x, position_y, scale, is_current |
| 7 | `oretir_site_settings` | Key-value settings and email templates | id, setting_key (unique), value_en, value_es |
| 8 | `oretir_email_logs` | Email and change audit trail | id, log_type, description, admin_email, created_at |
| 9 | `oretir_rate_limits` | API rate limiting | id, ip_address, action, created_at |
| 10 | `oretir_feedback` | **NEW** Post-service feedback | id, appointment_id (FK, unique), rating (1-5), comment, language, feedback_token (unique), token_expires, created_at |
| 11 | `oretir_schedules` | **NEW** Employee weekly schedules | id, employee_id (FK), day_of_week (0-6), start_time, end_time, is_available, created_at, updated_at |
| 12 | `oretir_schedule_overrides` | **NEW** Date-specific schedule overrides | id, employee_id (FK, nullable), override_date, is_closed, start_time, end_time, reason, created_at |
| 13 | `oretir_sms_logs` | **NEW** SMS audit trail | id, appointment_id (FK), phone, message_type (ENUM), twilio_sid, status (ENUM), created_at |
| 14 | `oretir_blog_posts` | **NEW** Blog posts | id, title_en, title_es, slug (unique), excerpt_en, excerpt_es, body_en, body_es, featured_image, status, author_id, published_at, created_at, updated_at |
| 15 | `oretir_blog_categories` | **NEW** Blog categories | id, name_en, name_es, slug (unique) |
| 16 | `oretir_blog_post_categories` | **NEW** Blog post-category junction | post_id, category_id (composite PK) |

### Email Templates (stored in `oretir_site_settings`)

| Template Key Prefix | Purpose |
|---------------------|---------|
| `email_tpl_welcome_*` | Admin account setup invitation (subject, greeting, body, button, footer) |
| `email_tpl_reset_*` | Password reset email (subject, greeting, body, button, footer) |
| `email_tpl_contact_*` | Contact form notification to admin (subject, greeting, body, button, footer) |
| `email_tpl_booking_*` | Booking confirmation to customer |
| `email_tpl_reminder_*` | Appointment reminder to customer |

All templates are bilingual (`value_en` / `value_es`) and support `{{variable}}` placeholders.

### Indexes

| Table | Index | Columns |
|-------|-------|---------|
| `oretir_appointments` | `idx_status` | status |
| `oretir_appointments` | `idx_date` | preferred_date |
| `oretir_appointments` | `idx_employee` | assigned_employee_id |
| `oretir_appointments` | `idx_reference` | reference_number (unique) |
| `oretir_appointments` | `idx_cancel_token` | cancel_token (unique) |
| `oretir_appointments` | `idx_appointments_reminder` | preferred_date, status, reminder_sent |
| `oretir_gallery_images` | `idx_active_order` | is_active, display_order |
| `oretir_service_images` | `idx_current` | service_key, is_current |
| `oretir_rate_limits` | `idx_ip_action` | ip_address, action, created_at |
| `oretir_email_logs` | `idx_type` | log_type |
| `oretir_email_logs` | `idx_created` | created_at |

---

## 7. Infrastructure

### Architecture

| Layer | Technology |
|-------|------------|
| Frontend | Static HTML, CSS (Tailwind CSS v4, compiled), vanilla JavaScript |
| Backend | PHP API (custom REST endpoints) + MySQL |
| Email | PHPMailer via SMTP with bilingual DB-driven templates |
| Hosting | cPanel with Apache (`.htaccess` configuration) |
| Build step | Tailwind CSS compiled via `npx @tailwindcss/cli` (in `deploy.sh`) |

### PHP Backend Components

| Component | File | Purpose |
|-----------|------|---------|
| Bootstrap | `includes/bootstrap.php` | Loads `.env` via phpdotenv, establishes PDO connection |
| Database | `includes/db.php` | PDO MySQL connection helper |
| Auth | `includes/auth.php` | Session-based authentication, admin verification |
| Mail | `includes/mail.php` | PHPMailer wrapper, `sendBrandedTemplateEmail()` for bilingual emails |
| Rate Limiting | `includes/rate-limit.php` | DB-backed rate limiting per IP/action |
| Validation | `includes/validate.php` | Input sanitization and validation helpers |
| Response | `includes/response.php` | JSON response helpers with proper HTTP status codes |

### API Endpoints

**Public Endpoints:**

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/book.php` | Create appointment (with optional payment via Commerce Kit) |
| POST | `/api/contact.php` | Submit contact form |
| GET | `/api/available-times.php?date=YYYY-MM-DD` | Slot availability |
| GET | `/api/settings.php` | Site settings (phone, hours, etc.) |
| GET | `/api/gallery.php` | Gallery images |
| GET | `/api/service-images.php` | Service card images |
| GET | `/api/calendar-event.php` | iCal download for appointment |
| POST | `/api/appointment-cancel.php` | Cancel appointment via token (now with cancel_reason + owner notification) |
| POST | `/api/appointment-reschedule.php` | Reschedule appointment via token (now with owner notification) |
| GET/POST | `/api/feedback.php` | **NEW** Public feedback submission (token-based) |
| GET | `/api/blog.php` | **NEW** Public blog listing and individual post |

**Admin Endpoints (require session auth):**

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/admin/login.php` | Email/password login |
| GET | `/api/admin/session.php` | Check active session |
| POST | `/api/admin/logout.php` | Destroy session |
| GET/PUT | `/api/admin/appointments.php` | List and update appointments |
| GET/PUT | `/api/admin/messages.php` | List and update contact messages |
| GET/POST/PUT | `/api/admin/employees.php` | CRUD employees |
| GET/POST/DELETE | `/api/admin/gallery.php` | CRUD gallery images (with file upload) |
| GET/POST/PUT | `/api/admin/service-images.php` | CRUD service images (with file upload) |
| GET/POST/PUT | `/api/admin/admins.php` | Manage admin accounts |
| GET/PUT | `/api/admin/account.php` | Current admin's account settings |
| GET | `/api/admin/email-logs.php` | Email audit trail |
| GET | `/api/admin/analytics.php` | Dashboard analytics data |
| GET | `/api/admin/export.php` | Data export (CSV) |
| GET/PUT | `/api/admin/site-settings.php` | Manage site settings and email templates |
| GET | `/api/admin/email-template-vars.php` | Available template variables |
| POST | `/api/admin/forgot-password.php` | Send password reset email |
| POST | `/api/admin/setup-password.php` | Set password via setup token |
| POST | `/api/admin/verify-token.php` | Verify setup/reset token |
| GET/POST/PUT/DELETE | `/api/admin/blog.php` | **NEW** Blog post CRUD |
| GET | `/api/admin/feedback.php` | **NEW** Feedback listing with stats |
| GET/POST/DELETE | `/api/admin/schedules.php` | **NEW** Employee schedule CRUD |
| GET/POST/DELETE | `/api/admin/schedule-overrides.php` | **NEW** Schedule override CRUD |
| GET | `/api/admin/calendar-health.php` | Google Calendar sync health check |
| POST | `/api/admin/calendar-retry-sync.php` | Retry failed calendar syncs |
| POST | `/api/admin/calendar-test-sync.php` | Test calendar sync connection |

### CLI Scripts (Server Cron)

| Schedule | Script | Purpose |
|----------|--------|---------|
| `0 18 * * *` | `cli/send-reminders.php` | Send appointment reminders for next day (email + SMS) |
| `0 10 * * *` | `cli/send-feedback-requests.php` | **NEW** Send feedback request emails 24h after completed appointments |

**Other CLI scripts (run manually):**

| Script | Purpose |
|--------|---------|
| `cli/create-admins-feb2026.php` | Bulk admin account creation |
| `cli/resend-setup-emails.php` | Resend setup invitation emails |
| `cli/seed-email-templates.php` | Seed/update bilingual email templates |
| `cli/test-smtp-debug.php` | SMTP connectivity test |
| `cli/migrate-blog.php` | **NEW** Create blog tables |
| `cli/migrate-feedback.php` | **NEW** Create feedback table + appointment columns |
| `cli/migrate-schedules.php` | **NEW** Create schedule tables |
| `cli/migrate-sms.php` | **NEW** Create SMS log table + appointment columns |
| `cli/migrate-cancel-reason.php` | **NEW** Add cancel_reason column |
| `cli/seed-blog.php` | **NEW** Seed 4 bilingual blog posts |
| `cli/send-feedback-requests.php` | **NEW** Cron: feedback request emails |

### Hosting and Deployment

| Aspect | Details |
|--------|---------|
| Server | cPanel/Apache shared hosting (`ssh hiphopworld`) |
| Remote path | `/home/hiphopwo/public_html/---oregon.tires/` |
| Source directory | `public_html/` (local development) |
| Staging directory | `_uploads/` (temporary staging for modified files only) |
| Deployment | `./deploy.sh` -- builds Tailwind CSS, stages changed files, SCPs to server |
| Deploy modes | `deploy` (default), `diff` (dry run), `status` (remote state), `build` (CSS only) |
| CSS build | `npx @tailwindcss/cli -i src/input.css -o public_html/assets/styles.css --minify` |
| Path strategy | Relative paths throughout (no leading `/` on asset references) for cPanel compatibility |
| Service worker | `sw.js` with `CACHE_VERSION` -- bump on each deploy |

### Testing

| Framework | Type | Config File |
|-----------|------|-------------|
| Vitest | Unit tests | `vitest.config.js` |
| Playwright | End-to-end tests | `playwright.config.js`, `playwright.deploy-clean.config.js` |

### File Structure

```
public_html/
  index.html              # Public website (single-file SPA)
  contact.php             # Contact page (Form Kit integration)
  cancel.php              # Appointment cancellation (token-based)
  reschedule.php          # Appointment rescheduling (token-based)
  checkout.php            # Payment page (Commerce Kit integration)
  blog.html               # NEW: Blog listing page
  blog-post.html          # NEW: Individual blog post
  feedback.html           # NEW: Post-service feedback (star rating)
  privacy.html            # NEW: Privacy policy (bilingual)
  sw.js                   # Service worker
  manifest.json           # PWA manifest
  robots.txt              # Search engine directives
  sitemap.xml             # XML sitemap
  404.html                # Custom 404 page
  .htaccess               # Apache rewrite rules
  admin/
    index.html            # Admin dashboard (~3,870 lines, includes embedded docs)
    setup-password.html   # Admin password setup page
  book-appointment/
    index.html            # Redirect stub (11 lines)
  about/
    index.html            # About page
  services/
    index.html            # Services page
  faq/
    index.html            # FAQ page
  api/
    book.php              # Booking endpoint
    contact.php           # Contact form endpoint
    available-times.php   # Slot availability
    settings.php          # Site settings
    gallery.php           # Gallery images
    service-images.php    # Service card images
    calendar-event.php    # iCal download
    appointment-cancel.php  # Updated: cancel_reason + owner notification
    appointment-reschedule.php  # Updated: owner notification
    blog.php              # NEW: Public blog API
    feedback.php          # NEW: Public feedback API
    admin/                # 25+ admin CRUD endpoints (session-protected)
    commerce/             # 7 Commerce Kit wrapper endpoints
    form/                 # 5 Form Kit wrapper endpoints
  includes/
    bootstrap.php         # .env loader + PDO init
    db.php                # Database connection
    auth.php              # Session auth helpers
    mail.php              # PHPMailer wrapper + branded templates
    rate-limit.php        # Rate limiting
    validate.php          # Input validation
    response.php          # JSON response helpers
    sms.php               # NEW: Twilio SMS helper
    whatsapp.php          # NEW: Twilio WhatsApp helper
  assets/
    styles.css            # Compiled Tailwind CSS v4
    logo.png              # Site logo
    hero-bg.png           # Hero background fallback
    favicon.png           # Browser favicon
  images/
    expert-technicians.jpg
    fast-cars.jpg
    quality-parts.jpg
    bilingual-service.jpg
    tire-services.jpg
    auto-maintenance.jpg
    specialized-services.jpg
  uploads/                # User-uploaded files (gallery, service images)
  cli/
    send-reminders.php    # Cron: appointment reminders (email + SMS)
    send-feedback-requests.php  # NEW: Cron: feedback emails
    migrate-blog.php      # NEW: Blog tables migration
    migrate-feedback.php  # NEW: Feedback table migration
    migrate-schedules.php # NEW: Schedule tables migration
    migrate-sms.php       # NEW: SMS log table migration
    migrate-cancel-reason.php  # NEW: Cancel reason column
    seed-blog.php         # NEW: Seed blog posts
    seed-email-templates.php
    create-admins-feb2026.php
    resend-setup-emails.php
    test-smtp-debug.php
  vendor/                 # Composer dependencies (PHPMailer, phpdotenv)
sql/
  schema.sql              # Full MySQL schema
  seed-images.sql         # Default service image seeds
  migrate-*.sql           # Database migrations
src/
  input.css               # Tailwind CSS v4 source
deploy.sh                 # Build + deploy script
```

### Security Measures

| Measure | Details |
|---------|---------|
| PHP prepared statements | All database queries use PDO prepared statements (parameterized) |
| BCrypt password hashing | `password_hash()` with `PASSWORD_BCRYPT` cost 12 |
| Session management | `session_regenerate_id(true)` on login; `httponly`, `secure`, `samesite` cookie flags |
| Rate limiting | DB-backed rate limiting per IP/action (`oretir_rate_limits` table) |
| Input validation | Server-side validation via `includes/validate.php` for all form submissions |
| XSS prevention | `esc()` function uses `textContent` assignment; no `innerHTML` for user content |
| `noindex, nofollow` | Admin page excluded from search engines |
| `.htaccess` protection | `includes/` directory blocked from direct HTTP access |
| Token-based actions | Cancel/reschedule tokens use `bin2hex(random_bytes(32))` with expiration |
| Account lockout | 5 failed login attempts triggers 15-minute lockout |
| Error handling | All API endpoints catch `\Throwable` to prevent stack trace leakage |
| SMTP credentials | Stored in `.env` (never committed to git), loaded via phpdotenv |

---

## 8. Shared Kit Integrations

### 7.1 Form Kit

**Source:** Shared Form Kit (`/home/hiphopwo/shared/form-kit/` on server)

| Feature | Details |
|---------|---------|
| Contact page | `contact.php` uses Form Kit renderer for a bilingual dark-theme contact form |
| API wrappers | 5 thin wrappers in `/api/form/`: `submit.php`, `submissions.php`, `mark-read.php`, `stats.php`, `config.php` |
| Admin inbox | Contact submissions viewable via Form Kit admin interface |
| Mail delivery | 3-tier: site's mail helper (auto-detects signature) -> PHPMailer -> `mail()` |
| Rate limiting | Form Kit's built-in DB-backed rate limiter |

### 7.2 Commerce Kit

**Source:** Shared Commerce Kit (`/home/hiphopwo/shared/commerce-kit/` on server)

| Feature | Details |
|---------|---------|
| Checkout page | `checkout.php` provides optional payment flow for appointments |
| API wrappers | 7 thin wrappers in `/api/commerce/`: `checkout.php`, `checkout-return.php`, `orders.php`, `stats.php`, `webhook.php`, `paypal-webhook.php`, `crypto-confirm.php` |
| Providers | PayPal (live), Crypto (active), Stripe (needs credentials), Manual |
| Order tracking | `commerce_orders`, `commerce_line_items`, `commerce_transactions` tables |
| Webhook support | PayPal IPN and crypto confirmation webhooks |

---

*Document generated from source code analysis of the Oregon Tires Auto Care codebase.*
