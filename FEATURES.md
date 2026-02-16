# Oregon Tires Auto Care -- Feature List

> **Last updated:** 2026-02-15
> **Stack:** Static HTML/CSS/JS | Supabase Backend | cPanel/Apache Hosting
> **URL structure:** `index.html` (public site), `admin/index.html` (dashboard), `book-appointment/index.html` (redirect stub)

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
   - [Realtime Updates](#28-realtime-updates)
   - [Account Settings](#29-account-settings)
   - [Utility Functions](#210-utility-functions)
   - [Documentation Tab](#211-documentation-tab)
3. [Book Appointment Page](#3-book-appointment-page)
4. [Database Schema](#4-database-schema)
5. [Infrastructure](#5-infrastructure)

---

## 1. Public Website

**File:** `public_html/index.html` (697 lines -- single-file SPA)

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

- Dynamic background image loaded from Supabase `oretir_service_images` table (key: `hero-background`)
- Local fallback image (`assets/hero-bg.png`) if Supabase URL fails or is unavailable
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

- Each card loads its background image dynamically from Supabase with position and scale controls
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

- Dynamic image grid loaded from Supabase (`oretir_gallery_images` table)
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

- Form submits to Supabase `oretir_contact_messages` table via `sb.from().insert()`
- Stores the current language with the message (`english` or `spanish`)
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
| Translation keys | 74 keys covering all UI text |
| Attribute system | `data-t` attribute on all translatable elements |
| Toggle function | `toggleLanguage()` swaps all `data-t` elements simultaneously |
| Language state | `currentLang` variable (`'en'` or `'es'`) |
| Gallery reload | Gallery images reload on language switch |
| Form messages | Success/error messages display in the active language |
| Contact storage | Language is stored with each submitted contact message |

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
| Supabase JS SDK v2 | Database queries, storage, realtime subscriptions |
| Tailwind CSS CDN | All styling via utility classes |
| Google Maps Embed API | Location iframe in contact section |
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

---

## 2. Admin Dashboard

**File:** `public_html/admin/index.html` (~3,870 lines -- single-file SPA with embedded documentation)
**Access:** `noindex, nofollow` meta tag prevents search engine indexing

### 2.1 Authentication

| Feature | Details |
|---------|---------|
| Login method | Email/password via `sb.auth.signInWithPassword()` |
| Admin verification | Checks `oretir_profiles.is_admin = true` for the user's UUID and `project_id = 'oregon-tires'` |
| Super-admin bypass | Hardcoded email `tyronenorris@gmail.com` always passes admin check |
| Auto-session restore | On page load, calls `sb.auth.getSession()` and auto-shows dashboard if valid admin session exists |
| Sign out | `sb.auth.signOut()` clears session and returns to login screen |
| Login feedback | Error messages displayed below form; button shows "Signing in..." during auth |

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
| Duration display | Completed appointments show actual duration in minutes |

**Appointment Actions:**
- **Assign employee:** Dropdown of active employees; auto-confirms status on assignment
- **Change status:** New, Confirmed, Completed, Cancelled
- **Confirmation guard:** Cannot confirm an appointment without an assigned employee
- **Admin notes:** Modal with textarea for per-appointment notes, saved to `admin_notes` column
- **Email notifications:** Triggered via Supabase edge function `send-appointment-emails` on:
  - Employee assignment (`appointment_assigned`)
  - Completion (`appointment_completed`)

### 2.4 Messages Tab

**Two sub-tabs:**

#### Contact Messages Sub-Tab

| Feature | Details |
|---------|---------|
| Message table | Columns: Customer, Contact (email + phone), Message (truncated), Date, Status |
| Status management | Dropdown per message: New, Priority, Completed |
| Full message modal | Shows name, email, phone, date, full message text, and status selector |
| Read full link | Appears when message exceeds 80 characters |
| Refresh button | Manual reload of messages from Supabase |

#### Website Changes Sub-Tab

| Feature | Details |
|---------|---------|
| Email history list | Card-based layout showing all sent emails |
| Type badges | Color-coded: Confirmation (blue), Assignment (yellow), Completion (green) |
| Recipient type badge | Shows whether email was sent to customer or employee |
| Email detail modal | Shows subject, To, Type, Sent date, Resend Message ID, and full HTML body |
| Refresh button | Manual reload of email logs |

### 2.5 Employees Tab

**Employee Management:**

| Feature | Details |
|---------|---------|
| Add employee form | Fields: Name (required), Email, Phone, Role (Employee/Manager) |
| Account creation | If email provided, invokes `create-employee-account` Supabase edge function to create auth account and send credentials |
| Edit employee | Inline edit form with Name, Email, Phone, Role fields |
| Toggle active/inactive | Activate or deactivate employees (soft delete) |
| Upcoming appointments | Badge showing count and next appointment date per active employee |
| Role display | Badge showing Employee or Manager role |

**Admin Privilege Management:**

| Feature | Details |
|---------|---------|
| Add admin form | Email input, creates account if user does not exist via edge function |
| Grant admin access | Calls `set_admin_by_email` RPC to set `is_admin = true` in `oretir_profiles` |
| Revoke admin access | Sets `is_admin = false` with confirmation dialog |
| Admin-only user display | Separate section showing dashboard admins not in employee list |
| Last login info | Shows last sign-in date or account creation date for each admin |
| Admin badge | Green shield badge on employees who also have admin access |

### 2.6 Gallery Tab

**Two sub-tabs:**

#### Gallery Images Sub-Tab

| Feature | Details |
|---------|---------|
| Upload form | File picker (image only), Title (required), Language (English/Spanish), Description |
| Upload process | File uploaded to Supabase Storage `gallery-images` bucket, public URL stored in `oretir_gallery_images` |
| Image list | Card layout with thumbnail, title, language, display order, active status, description |
| Delete | Removes from both Supabase Storage and database with confirmation dialog |

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

### 2.8 Realtime Updates

| Subscription | Table | Behavior |
|--------------|-------|----------|
| Appointments | `oretir_appointments` | Toast notification on INSERT showing customer name; reloads appointment data on any change (INSERT, UPDATE, DELETE) |
| Contact Messages | `oretir_contact_messages` | Reloads messages on any change |

- Uses Supabase Realtime `postgres_changes` channel
- Unique channel name per session via random string

### 2.9 Account Settings

Accessible via clicking the admin email/role display in the header. Opens a modal with four setting panels:

| Setting | Details |
|---------|---------|
| Admin Notification Email | CC address for all appointment confirmations, employee emails, and assignment notifications; stored in `oretir_settings` table |
| Update Display Name | Updates `full_name` in Supabase Auth user metadata |
| Update Email | Sends confirmation email to both old and new addresses via `sb.auth.updateUser()` |
| Update Password | Minimum 6 characters, confirmation field required to match; updates via `sb.auth.updateUser()` |

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

**Self-documenting admin panel** with three sub-tabs toggled via `switchDocsView()`:

| Sub-Tab | Content |
|---------|---------|
| Manual | Full instruction manual for the admin team (Getting Started, Appointments, Messages, Employees, Gallery, Service Images, Analytics, Account Settings, Public Website Features, Troubleshooting) |
| Features | Complete feature list documenting every function of the public site, admin dashboard, database schema, and infrastructure |
| Improvements | Prioritized improvement roadmap with Critical, High Priority, Medium Priority, and Low Priority items plus a 4-week action plan |

- Sub-tab toggle: `switchDocsView(view)` shows/hides content divs and updates button styles
- Manual sub-tab visible by default; Features and Improvements start hidden
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

## 4. Database Schema

**Backend:** Supabase PostgreSQL
**Prefix:** `oretir_` for Oregon Tires tables (some legacy tables use `oregon_tires_` prefix)

### Tables Overview

| # | Table | Purpose | Key Columns |
|---|-------|---------|-------------|
| 1 | `admin_accounts` | Admin account registry | id, email, full_name, is_active, project_id, user_id |
| 2 | `oretir_profiles` | User profile with admin flag | id (UUID PK), is_admin, project_id |
| 3 | `oretir_appointments` | Appointment records (25+ columns) | id, first/last_name, email, phone, service, preferred_date/time, status, assigned_employee_id, tire_size, license_plate, vin, service_location, customer_address/city/state/zip, vehicle_id, travel_distance/cost, started_at, completed_at, actual_duration_minutes/seconds, admin_notes |
| 4 | `oretir_contact_messages` | Contact form submissions | id, first/last_name, email, phone, message, status, language |
| 5 | `oretir_admin_notifications` | Admin notification records | id, type, title, message, read, appointment_id |
| 6 | `oretir_employees` | Employee records | id, name, email, phone, role, is_active, color, user_id |
| 7 | `oretir_employee_schedules` | Weekly employee availability | id, employee_id (FK), day_of_week, start_time, end_time, is_available |
| 8 | `oretir_custom_hours` | Business custom hours per day | id, day_of_week (unique), open_time, close_time, is_closed, max_simultaneous_bookings |
| 9 | `oretir_gallery_images` | Gallery image records | id, image_url, title, description, display_order, is_active |
| 10 | `oretir_service_images` | Service section hero images | id, service_key (unique), image_url, alt_text, position_x, position_y, scale, is_current |
| 11 | `customer_vehicles` | Customer vehicle records | id, customer_name, customer_email, make, model, year, license_plate, vin |

### Additional Database Objects

| Object | Type | Purpose |
|--------|------|---------|
| `oretir_email_logs` | Table | Stores sent email history (subject, body, recipient, type, resend_message_id) |
| `oretir_settings` | Table | Key-value settings store (e.g., `admin_email` notification address) |
| `get_admin_users` | RPC Function | Returns list of admin users with login metadata |
| `set_admin_by_email` | RPC Function | Grants or revokes admin access by email for a specific project |
| `app_role` | Enum Type | `admin`, `user`, `artist` |

---

## 5. Infrastructure

### Architecture

| Layer | Technology |
|-------|------------|
| Frontend | Static HTML, CSS (Tailwind CDN), vanilla JavaScript |
| Backend | Supabase (PostgreSQL, Auth, Storage, Edge Functions, Realtime) |
| Hosting | cPanel with Apache (`.htaccess` configuration) |
| Build step | None -- files served as-is |

### Supabase Services Used

| Service | Usage |
|---------|-------|
| PostgreSQL | All data storage (11+ tables) |
| Auth | Email/password login, session management, user metadata |
| Storage | `gallery-images` bucket for gallery and service image uploads |
| Edge Functions | `send-appointment-emails` (email notifications), `create-employee-account` (employee onboarding) |
| Realtime | Live subscriptions to `oretir_appointments` and `oretir_contact_messages` changes |
| RPC | `get_admin_users`, `set_admin_by_email` database functions |

### Hosting and Deployment

| Aspect | Details |
|--------|---------|
| Server | cPanel/Apache shared hosting |
| Source directory | `public_html/` (local development) |
| Staging directory | `_uploads/` (temporary staging for modified files only) |
| Deployment method | Manual file upload -- only changed files, mirroring directory structure |
| Path strategy | Relative paths throughout (no leading `/` on asset references) for cPanel compatibility |

### Testing

| Framework | Type | Config File |
|-----------|------|-------------|
| Vitest | Unit tests | `vitest.config.js` |
| Playwright | End-to-end tests | `playwright.config.js`, `playwright.deploy-clean.config.js` |

### File Structure

```
public_html/
  index.html              # Public website (697 lines)
  admin/
    index.html            # Admin dashboard (~3,870 lines, includes embedded docs)
  book-appointment/
    index.html            # Redirect stub (11 lines)
  assets/
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
```

### Security Measures

| Measure | Location |
|---------|----------|
| Admin role verification | Dashboard login checks `oretir_profiles.is_admin` before granting access |
| XSS prevention | `esc()` function uses `textContent` assignment for all user-generated content |
| `noindex, nofollow` | Admin page excluded from search engines |
| Supabase RLS | Row Level Security on database tables (managed in Supabase) |
| Anon key only | Frontend uses Supabase anonymous key; sensitive operations go through edge functions |
| Modal overlay dismissal | Modals close on overlay click to prevent accidental data exposure |

---

*Document generated from source code analysis of the Oregon Tires Auto Care codebase.*
