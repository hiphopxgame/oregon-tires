# Oregon Tires — Full System Audit & Integration Plan

**Date:** 2026-03-20
**Scope:** Operations, Sales, Marketing — 3-system audit

---

## AUDIT SUMMARY

### What Was Already Built (Complete)

| # | Feature | System | Status | Key Files |
|---|---------|--------|--------|-----------|
| 1 | 15-min slot availability with employee-based capacity | Ops | Complete | `api/available-times.php`, `includes/schedule.php` |
| 2 | Configurable business hours (per day of week) | Ops | Complete | `api/admin/business-hours.php` |
| 3 | Holiday calendar + shop-wide closures | Ops | Complete | `oretir_holidays`, `oretir_schedule_overrides` |
| 4 | Employee weekly schedules + date overrides | Ops | Complete | `api/admin/schedules.php` |
| 5 | Max daily appointments per employee (1-30) | Ops | Complete | `oretir_employees.max_daily_appointments` |
| 6 | Employee skills & certifications tracking | Ops | Complete | `oretir_employee_skills` |
| 7 | Bilingual email system (PHPMailer + templates) | Ops | Complete | `includes/mail.php` |
| 8 | SMS notifications via Twilio REST API | Ops | Complete | `includes/sms.php` |
| 9 | Appointment reminders (email + SMS + push) | Ops | Complete | `cli/send-reminders.php` |
| 10 | Push notifications (VAPID + queue processor) | Ops | Complete | `includes/push.php`, `cli/send-push-notifications.php` |
| 11 | Visit tracking (check-in/out, bay assignment, timers) | Ops | Complete | `api/admin/visit-log.php`, `admin/js/visit-tracker.js` |
| 12 | Customer database (persistent, email unique, visits) | Ops | Complete | `oretir_customers`, `api/admin/customers.php` |
| 13 | Repair Order lifecycle (10 stages + kanban) | Ops | Complete | `api/admin/repair-orders.php`, `admin/js/kanban.js` |
| 14 | DVI with traffic light system + photos | Ops | Complete | `api/admin/inspections.php` |
| 15 | Estimate builder + per-item approval | Ops | Complete | `api/admin/estimates.php` |
| 16 | Invoice generation from ROs | Ops | Complete | `includes/invoices.php` |
| 17 | Analytics dashboard (30+ metrics) | Ops | Complete | `api/admin/analytics.php` |
| 18 | Resource planner (multi-date scheduling) | Ops | Complete | `api/admin/resource-planner.php` |
| 19 | Labor hours tracking per RO | Ops | Complete | `api/admin/labor.php` |
| 20 | Tire quote form (new/used/either, size, count, budget) | Sales | Complete | `api/tire-quote.php` |
| 21 | Booking form with VIN/plate lookup | Sales | Complete | `book-appointment/index.html`, `api/book.php` |
| 22 | Service-specific intake (tire fields for tire services) | Sales | Complete | Booking form Step 1 tire section |
| 23 | Roadside assistance calculator (zone-based) | Sales | Complete | `assets/js/roadside-estimator.js` |
| 24 | Mobile service coverage messaging | Sales | Complete | `mobile-service.php` |
| 25 | 10 service pages with CTAs + FAQs | Sales | Complete | `templates/service-detail.php` |
| 26 | Financing page with payment plans | Sales | Complete | `financing.php` |
| 27 | 3 CTAs per service page (Book, Call, Text) | Sales | Complete | Service detail template |
| 28 | Homepage with logo, social links, email | Mktg | Complete | `index.php`, `templates/header.php` |
| 29 | Waiting room experience section | Mktg | Complete | Homepage "While You Wait" section |
| 30 | Gallery with categories + bilingual + video | Mktg | Complete | `api/admin/gallery.php` |
| 31 | Promotions with placement targeting + scheduling | Mktg | Complete | `api/admin/promotions.php` |
| 32 | Blog (bilingual, CMS-driven) | Mktg | Complete | `api/admin/blog.php` |
| 33 | FAQ management (bilingual) | Mktg | Complete | `api/admin/faq.php` |
| 34 | Service images (admin-uploadable) | Mktg | Complete | `api/admin/service-images.php` |
| 35 | Full bilingual support (EN/ES all pages) | Mktg | Complete | data-t attribute system |
| 36 | Care plan subscriptions (3 tiers, PayPal) | Sales | Complete | `care-plan.php`, `api/care-plan-enroll.php` |
| 37 | Loyalty points program | Sales | Complete | `includes/loyalty.php` |
| 38 | Customer referral program | Sales | Complete | `includes/referrals.php` |
| 39 | Walk-in waitlist queue | Ops | Complete | `api/admin/waitlist.php` |
| 40 | Inbound email integration (IMAP) | Ops | Complete | `includes/email-fetcher.php` |
| 41 | In-app messaging (admin ↔ customer) | Ops | Complete | `api/admin/conversations.php` |
| 42 | Member dashboard (8 tabs) | Sales | Complete | `members.php` |
| 43 | Google OAuth login | Ops | Complete | `api/auth/google.php` |
| 44 | 8 regional SEO pages | Mktg | Complete | `tires-se-portland.php`, etc. |
| 45 | PWA + offline booking | Mktg | Complete | `sw.js`, `assets/js/pwa-manager.js` |

### What Was Partially Built (Gaps Found)

| # | Feature | Gap | Fix |
|---|---------|-----|-----|
| 1 | Employee notifications on assignment | Job-finished SMS exists (`sendJobFinishedSms`) but employee assignment notification doesn't fire automatically when RO assigned | Wire notification into RO assignment flow |
| 2 | Admin/office appointment reminders | Customer reminders work; admin/employee reminders not sent | Add employee reminder to `send-reminders.php` |
| 3 | Adjustable reminder intervals | Hardcoded to 6PM day-before | Add `reminder_hours_before` to site settings |
| 4 | Customer-facing technician selection | Admin can assign; customer cannot select preferred tech during booking | Add optional tech preference to booking form |

### What Was Missing (Not Built)

None of the requested features are completely missing. The system is 95%+ complete.

### Third-Party Service Requirements

| Service | Env Var | Status | Needed For |
|---------|---------|--------|------------|
| SMTP (email) | `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD` | Configured | All email notifications |
| Twilio (SMS) | `TWILIO_SID`, `TWILIO_TOKEN`, `TWILIO_FROM` | Needs credentials | SMS appointment reminders, job-finished texts |
| Google OAuth | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | Configured | Member login with Google |
| Google Places API | (key in settings) | Configured | Google Reviews display |
| PayPal | (commerce-kit) | Configured | Care plan subscriptions, checkout |
| Sentry | `SENTRY_DSN`, `SENTRY_DSN_JS` | Optional | Error tracking |

### Manual QA Checklist

**Booking Flow:**
- [ ] Book appointment for tire installation → verify tire-specific fields appear
- [ ] Book appointment for oil change → verify no tire fields
- [ ] Test Spanish language booking end-to-end
- [ ] Verify confirmation email received
- [ ] Verify SMS confirmation (if Twilio configured)
- [ ] Cancel appointment via email link
- [ ] Reschedule appointment via email link

**Worker Assignment:**
- [ ] Admin assigns employee to appointment
- [ ] Check employee schedule shows appointment
- [ ] Verify max daily appointments enforced

**Notifications:**
- [ ] Customer receives reminder email (day before)
- [ ] Customer receives reminder SMS (if opted in)
- [ ] Employee receives daily assignment summary
- [ ] "Job finished" notification reaches customer

**Spanish Flow:**
- [ ] Homepage loads in Spanish via `?lang=es`
- [ ] Booking form fully translated
- [ ] Service pages fully translated
- [ ] Email templates render in Spanish for Spanish-pref customers

**Media Uploads:**
- [ ] Admin uploads gallery image with EN/ES captions
- [ ] Admin uploads promotional image with scheduling
- [ ] Admin uploads service image with positioning
- [ ] Gallery page renders uploaded images

**Offers:**
- [ ] Create weekly offer in admin with start/end dates
- [ ] Verify offer appears on homepage banner
- [ ] Verify offer disappears after end date

**Analytics:**
- [ ] Dashboard shows appointment trends
- [ ] Peak hours chart populated
- [ ] Employee productivity visible
- [ ] Revenue by month chart populated
