# Oregon Tires Auto Care — Admin User Guide

**Oregon Tires Auto Care**
8536 SE 82nd Ave, Portland, OR 97266
Phone: (503) 367-9714
Hours: Monday–Saturday 7:00 AM – 7:00 PM | Sunday Closed

Super Admin Contact:
Tyrone "Mental Stamina" Norris
(774) 277-9202
tyronenorris@gmail.com

Spanish Contact:
Margarita Escalante
(541) 936-1884
growwithmagi@gmail.com

---

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Managing Appointments](#2-managing-appointments)
3. [Repair Orders](#3-repair-orders)
4. [Customers](#4-customers)
5. [Vehicles & VIN Decode](#5-vehicles--vin-decode)
6. [Digital Vehicle Inspections (DVI)](#6-digital-vehicle-inspections-dvi)
7. [Estimates](#7-estimates)
8. [Customer Communication](#8-customer-communication)
9. [Blog](#9-blog)
10. [Promotions](#10-promotions)
11. [FAQ](#11-faq)
12. [Reviews / Testimonials](#12-reviews--testimonials)
13. [Gallery & Service Images](#13-gallery--service-images)
14. [Newsletter Subscribers](#14-newsletter-subscribers)
15. [Analytics](#15-analytics)
16. [Site Content & Settings](#16-site-content--settings)
17. [Account Settings](#17-account-settings)
18. [Customer-Facing Pages](#18-customer-facing-pages)
19. [Invoices & Receipts](#19-invoices--receipts)
20. [Loyalty & Referrals](#20-loyalty--referrals)
21. [Labor Tracking](#21-labor-tracking)
22. [Waitlist / Walk-In Queue](#22-waitlist--walk-in-queue)
23. [Tire Quote Requests](#23-tire-quote-requests)
24. [Push Notifications](#24-push-notifications)
25. [Troubleshooting](#25-troubleshooting)

---

## 1. Getting Started

### How to Access the Admin Dashboard

1. Open your web browser (Chrome, Firefox, Safari, or Edge).
2. Go to: `oregon.tires/admin/`
3. You will see a login screen.

### How to Log In

1. Enter your **email address**.
2. Enter your **password**.
3. Click **Log In**.
4. First-time users: the super admin email is **tyronenorris@gmail.com**. If you don't know the password, use the password reset option or contact Tyrone.

**Note:** After 5 failed login attempts, the account locks for 15 minutes. This is a security feature.

### Dashboard Overview

After logging in, you land on the **Overview** tab showing a 14-day activity snapshot:

- New appointments received
- New contact messages
- Recent repair order activity
- Quick stats (pending ROs, upcoming appointments)

### Navigation

Use the **tab bar** across the top to switch sections. There are 15 tabs:

| Tab | What It Does |
|-----|-------------|
| **Overview** | 14-day activity snapshot |
| **Appointments** | View, assign, manage customer bookings |
| **Customers** | Customer records, search, history |
| **Repair Orders** | RO lifecycle, kanban board, inspections, estimates |
| **Messages** | Contact form submissions + email logs |
| **Employees** | Staff management, admin access |
| **Gallery** | Gallery images + service image slots |
| **Blog** | Create/edit bilingual blog posts |
| **Promotions** | Time-limited promotional offers |
| **FAQ** | Manage bilingual Q&A |
| **Reviews** | Customer testimonials |
| **Analytics** | Appointment stats, popular times |
| **Docs** | This manual, feature list, improvements |
| **Site Content** | Edit business info, email templates |
| **Subscribers** | Email subscriber list |

The dashboard supports **English and Spanish**. Use the language toggle in the header to switch.

---

## 2. Managing Appointments

### Calendar View

1. Click the **Appointments** tab.
2. The calendar shows the current month.
3. Navigate months with the **left/right arrows**.
4. **Green dot** = date has appointments. **Yellow ring** = today.
5. Click any date to see that day's appointments.

### List View

Switch to list view for searching and filtering:

1. **Search** by customer name, phone, or details.
2. **Filter by Status**: New, Confirmed, Completed, Cancelled.
3. **Filter by Month** to narrow results.
4. **Sort** by clicking column headers (Date, Name, Status).
5. **Per-page count**: Choose 10, 25, or 50 at the bottom.

### Assigning an Employee

1. Find the appointment.
2. Click the **employee dropdown**.
3. Select a technician.
4. Status automatically changes to **Confirmed**.
5. The assigned employee receives an email notification.

### Changing Appointment Status

Appointments flow: **New → Confirmed → Completed / Cancelled**

1. Click the **status dropdown** on the appointment.
2. Select the new status.
3. **Completed** triggers a completion email to the customer.

### Adding Notes

1. Click the **notes icon** next to the appointment.
2. Type your note (e.g., "Customer also wants alignment check").
3. Click **Save**. Notes are admin-only — customers don't see them.

### Quick Assign All (Bulk)

1. Click **"Quick Assign All"** above the list.
2. All unassigned appointments get distributed evenly (round-robin) among active employees.
3. All assigned appointments set to **Confirmed** with email notifications sent.

### Convert Appointment to Repair Order

1. Open an appointment.
2. Click **"Create Repair Order"** (or similar button).
3. A new RO is created with the customer and vehicle info pre-filled.
4. You'll be taken to the Repair Orders tab.

---

## 3. Repair Orders

The Repair Orders tab manages the full service workflow from vehicle intake to invoice.

### RO Status Flow

```
intake → diagnosis → estimate_pending → pending_approval → approved → in_progress → waiting_parts → ready → completed → invoiced
                                                                                                              (also: cancelled at any point)
```

| Status | Meaning |
|--------|---------|
| **Intake** | Vehicle just arrived, initial RO created |
| **Diagnosis** | Technician inspecting the vehicle |
| **Estimate Pending** | Inspection done, estimate being prepared |
| **Pending Approval** | Estimate sent to customer, waiting for response |
| **Approved** | Customer approved the estimate |
| **In Progress** | Work is being done |
| **Waiting Parts** | Paused — waiting for parts delivery |
| **Ready** | Work complete, vehicle ready for pickup |
| **Completed** | Customer picked up, service finished |
| **Invoiced** | Payment processed |
| **Cancelled** | RO cancelled (can happen at any stage) |

### Creating a Repair Order

**From an appointment:**
1. Go to Appointments tab → find the appointment.
2. Click **"Create Repair Order"**.
3. Customer and vehicle info auto-populated.

**Walk-in (no appointment):**
1. Go to Repair Orders tab.
2. Click **"New Repair Order"**.
3. Search for existing customer or create new.
4. Select or add a vehicle.
5. Fill in mileage, notes, promised date.

Each RO gets a unique reference number: **RO-XXXXXXXX**.

### Table View vs. Kanban Board

**Table View:** Default list with columns for RO number, customer, vehicle, status, dates. Sortable and filterable.

**Kanban Board:**
1. Click the **Kanban toggle** button (board icon).
2. Each column represents a status.
3. **Drag and drop** an RO card between columns to change its status.
4. Cards show RO number, customer name, vehicle, and time in current status.
5. Column headers show count of ROs in each status.

### RO Detail View

Click any RO to open the detail view:

- **Status timeline** showing when each status was reached
- Customer and vehicle info
- Mileage in/out
- Promised date
- Admin notes
- **Inspections section** — create/view DVIs for this RO
- **Estimates section** — create/view estimates for this RO
- Quick status change buttons

---

## 4. Customers

### Viewing Customers

1. Click the **Customers** tab.
2. Browse the customer list or use the **search bar** (searches name, email, phone).

### How Customers Get Created

- **Automatically** when someone books an appointment (deduplicated by email).
- **Manually** by clicking **"Add Customer"** in the admin.

### Customer Details

Each customer record includes:
- Name, email, phone
- **Language preference** (English or Spanish) — used for automated emails
- **Linked vehicles** — view all vehicles associated with this customer
- **Appointment history** — all past and upcoming appointments
- **Repair order history** — all ROs for this customer

### Editing a Customer

1. Find the customer.
2. Click **Edit**.
3. Update name, email, phone, or language preference.
4. Click **Save**.

---

## 5. Vehicles & VIN Decode

### Adding a Vehicle to a Customer

1. Open a customer's detail view.
2. Click **"Add Vehicle"**.
3. Enter the **VIN** (Vehicle Identification Number) — 17 characters.
4. Click **"Decode VIN"** to auto-populate vehicle details.

### What VIN Decode Fills In

The system queries the NHTSA vPIC database and auto-fills:
- Year, Make, Model, Trim
- Engine type
- Body style

Results are permanently cached — the same VIN never needs to be decoded twice.

### Tire Fitment Lookup

After a vehicle is decoded:
1. Click **"Lookup Tire Fitment"**.
2. The system queries tire size data for that year/make/model.
3. Auto-fills front and rear tire sizes.
4. Results cached for 90 days.

### Other Vehicle Fields

- **License plate** — manual entry
- **Color** — manual entry
- **Tire pressure** — recommended PSI
- **Notes** — any special notes about the vehicle

A customer can have **multiple vehicles** linked to their account.

---

## 6. Digital Vehicle Inspections (DVI)

DVIs are the core of your diagnostic workflow. They let you document vehicle condition with photos and send a professional report to the customer.

### Creating an Inspection

1. Open a Repair Order detail view.
2. In the **Inspections** section, click **"New Inspection"**.
3. The system auto-creates **35 inspection items** across **12 categories**:

| Category | Items |
|----------|-------|
| Tires | Left Front, Right Front, Left Rear, Right Rear, Spare |
| Brakes | Front Pads, Rear Pads, Front Rotors, Rear Rotors |
| Suspension | Front Struts/Shocks, Rear Struts/Shocks, Tie Rods, Ball Joints |
| Fluids | Engine Oil, Coolant, Brake Fluid, Transmission Fluid, Power Steering Fluid |
| Lights | Headlights, Tail Lights, Brake Lights, Turn Signals |
| Engine | Air Filter, Cabin Air Filter, Spark Plugs |
| Exhaust | Exhaust System, Catalytic Converter |
| Hoses | Coolant Hoses, Heater Hoses |
| Belts | Serpentine Belt, Timing Belt/Chain |
| Battery | Battery, Battery Terminals |
| Wipers | Front Wipers, Rear Wiper |
| Other | (add custom items as needed) |

### Rating Items (Traffic Light System)

For each item, set a condition:
- **Green** — Good condition, no action needed
- **Yellow** — Needs attention soon (recommended service)
- **Red** — Critical issue (safety concern, needs immediate repair)

You can also add **notes** to any item explaining what you found.

### Adding Photos

1. Click the **camera icon** next to any inspection item.
2. Take or upload a photo.
3. Photos are stored under the RO number and linked to the specific item.
4. You can add multiple photos per item.
5. Delete photos by clicking the **X** on the photo thumbnail.

### Completing an Inspection

1. After rating all relevant items, click **"Complete Inspection"**.
2. Status changes from draft/in_progress to **completed**.

### Sending to Customer

1. On a completed inspection, click **"Send to Customer"**.
2. The customer receives an **email** (and SMS if configured) with a link to their inspection report.
3. The link uses a secure token — no login required.
4. Status changes to **sent**.

### What the Customer Sees

The customer's inspection page (`/inspection/{token}`) shows:
- An **overall health score ring** (color-coded: green, yellow, or red)
- All items grouped by category with traffic light indicators
- Photos for each item (click to enlarge, swipe or use arrow keys to navigate)
- A **"Book Service"** button if no estimate has been created yet
- **Print button** for a paper copy
- Page is fully **bilingual** (English/Spanish) based on customer language preference

---

## 7. Estimates

Estimates are how you present repair costs to customers for approval.

### Creating an Estimate

**Auto-generate from inspection (recommended):**
1. Open a completed inspection.
2. Click **"Generate Estimate"**.
3. The system creates estimate line items from all **red and yellow** inspection items.
4. Review and adjust pricing.

**Manual creation:**
1. Open a Repair Order detail view.
2. In the **Estimates** section, click **"New Estimate"**.
3. Add line items manually.

Each estimate gets a unique reference number: **ES-XXXXXXXX**.

### Line Item Types

| Type | Use For |
|------|---------|
| **Labor** | Service hours, labor charges |
| **Parts** | Replacement parts |
| **Tire** | Tire-specific items (tires, mounting, balancing) |
| **Fee** | Shop fees, diagnostic fees, disposal fees |
| **Discount** | Price reductions, loyalty discounts |
| **Sublet** | Work subcontracted to another shop |

For each item, enter: description, quantity, unit price. The system calculates totals automatically.

### Setting Valid-Until Date

1. Set the **valid until** date on the estimate.
2. The system sends an automatic reminder email **2 days before expiration** (via cron job).
3. Expired estimates are marked accordingly.

### Sending to Customer

1. Click **"Send Estimate"**.
2. Customer receives an email (and SMS if configured) with a link.
3. The link uses a secure approval token.
4. RO status automatically changes to **pending_approval**.

### Estimate Statuses

| Status | Meaning |
|--------|---------|
| **Draft** | Being prepared |
| **Sent** | Sent to customer |
| **Viewed** | Customer opened the link |
| **Approved** | Customer approved everything |
| **Partial** | Customer approved some items, declined others |
| **Declined** | Customer declined the entire estimate |
| **Expired** | Past the valid-until date |
| **Superseded** | A newer version of this estimate exists |

### What the Customer Sees

The approval page (`/approve/{token}`) shows:
- Customer greeting with vehicle info
- All line items with per-item **approve/decline toggles**
- Running total updates as items are toggled
- Priority grouping: Safety items, Recommended items, Preventive items
- Link back to the inspection report
- **Print button** for a paper copy
- Page is fully **bilingual**

When the customer submits their decision:
- A confirmation email is sent to the customer
- The admin is notified
- RO status updates automatically (to **approved** or stays at **pending_approval** for partial/declined)

---

## 8. Customer Communication

### Email Templates

The system uses **bilingual email templates** stored in the database. To edit them:

1. Go to the **Site Content** tab.
2. Find the email template section.
3. Templates use `{{variable}}` placeholders that get replaced with real data.
4. Click **"Template Variables"** to see all available placeholders.

### Automated Emails

The system sends emails automatically for these events:

| Event | Email Sent To |
|-------|--------------|
| New appointment booked | Customer (confirmation) + Admin (notification) |
| Employee assigned | Assigned employee |
| Appointment completed | Customer (completion email) |
| Inspection sent | Customer (inspection link) |
| Estimate sent | Customer (approval link) |
| Estimate approved/declined | Customer (confirmation) + Admin (notification) |
| Vehicle ready | Customer (pickup notification) |

### Automated Cron Emails

These run automatically on schedule:

| Time | Email | Purpose |
|------|-------|---------|
| Daily 6:00 PM | Appointment reminders | Reminds customers of next-day appointments |
| Daily 10:00 AM | Review requests | Asks customers to leave a review (after completed service) |
| Daily 10:00 AM | Estimate expiry reminders | Warns customers their estimate expires in 2 days |

### SMS Messages

If Twilio is configured, SMS messages are sent alongside emails for:
- Inspection report ready
- Estimate ready for approval
- Vehicle ready for pickup
- Estimate approval confirmation

### Email Logs

1. Go to **Messages** tab → **Email Logs** sub-tab.
2. View a record of every email sent by the system.
3. Useful for verifying delivery or troubleshooting.

---

## 9. Blog

### Creating a Blog Post

1. Go to the **Blog** tab.
2. Click **"New Post"**.
3. Fill in:
   - **Title** (English and Spanish)
   - **Content** (English and Spanish)
   - **Category** — select or create
   - **Featured image** — upload an image
   - **Status** — Draft (not visible) or Published
4. Click **Save**.

### Editing a Post

1. Find the post in the list.
2. Click **Edit**.
3. Make changes and click **Save**.

### Blog URLs

Published posts appear at `/blog` (listing) and `/blog/{slug}` (individual post). Slugs are auto-generated from the English title.

---

## 10. Promotions

### Creating a Promotion

1. Go to the **Promotions** tab.
2. Click **"New Promotion"**.
3. Fill in:
   - **Title** (English and Spanish)
   - **Description** (English and Spanish)
   - **Image** — upload a promotional image
   - **Start date** and **End date**
   - **Active** toggle
4. Click **Save**.

Promotions automatically appear on the public site during the active date range and disappear after the end date.

---

## 11. FAQ

### Adding a FAQ Entry

1. Go to the **FAQ** tab.
2. Click **"Add FAQ"**.
3. Enter:
   - **Question** (English and Spanish)
   - **Answer** (English and Spanish)
   - **Category** — group related questions
   - **Sort order** — controls display position
4. Click **Save**.

FAQs appear on the public `/faq/` page in an accordion layout.

---

## 12. Reviews / Testimonials

### Managing Reviews

1. Go to the **Reviews** tab.
2. You can:
   - **Add** a testimonial manually (customer name, star rating 1-5, review text in EN/ES, service type)
   - **Edit** existing reviews
   - **Feature** a review (toggle) — featured reviews get highlighted
   - **Delete** reviews

The homepage shows **3 random reviews** each page load to keep the section fresh.

---

## 13. Gallery & Service Images

### Gallery Images

1. Go to the **Gallery** tab.
2. Click **"Add Image"**.
3. Select a file, enter a **title**, choose **language** (English or Spanish), add optional **description**.
4. Click **Upload**.

Gallery images appear on the public site, filtered by the visitor's selected language.

### Service Images (8 Slots)

1. Go to the **Gallery** tab → **Service Images** sub-tab.
2. The 8 slots control key sections of the website:

| Slot | Controls |
|------|----------|
| Hero Background | Homepage banner |
| Expert Technicians | Feature card |
| Fast Cars | Feature card |
| Quality Parts | Feature card |
| Bilingual Support | Feature card |
| Tire Shop | Service section |
| Auto Repair | Service section |
| Specialized Tools | Service section |

3. Click **"Choose File"** next to a slot, select an image, click **Upload**.
4. Use the **position sliders** (horizontal, vertical, zoom) to frame the image.
5. Click **"Save Position & Crop"**.

A **"Live"** badge means the image is currently showing on the public site. Unset slots use built-in fallback images.

---

## 14. Newsletter Subscribers

1. Go to the **Subscribers** tab.
2. View all email subscribers who signed up via the website.
3. Each entry shows: email, signup date, status.
4. Export the list if needed.

Visitors subscribe via the email signup form on the public website.

---

## 15. Analytics

1. Go to the **Analytics** tab.
2. The enhanced analytics dashboard (`admin-analytics.js`) provides:

### Appointment Statistics
- Total appointments, breakdown by status
- This week's appointment count
- Trends over time

### Revenue Tracking
- Revenue by service type
- Monthly revenue trends
- Average ticket value

### Customer Statistics
- Total unique customers
- Returning customer count
- Customer acquisition metrics

### Employee Performance
- Appointments handled per employee
- Appointments completed per employee
- Average time per appointment
- Labor utilization rates

### Popular Times
- Most-requested time slots — helps with staffing decisions
- Day-of-week and time-of-day heatmaps

---

## 16. Site Content & Settings

1. Go to the **Site Content** tab.
2. Edit:
   - **Business information** — name, address, phone, hours
   - **Email templates** — customize automated email content (bilingual)
   - **Pricing** — service pricing displayed on the website
   - **Content blocks** — various text sections shown on the public site

All changes take effect immediately on the live website.

---

## 17. Account Settings

1. Click your **email address** in the top-right corner.
2. From here you can:
   - **Change notification email** — where admin alerts get sent
   - **Update display name** — shown in the dashboard
   - **Change login email**
   - **Change password** — must be 8+ characters with uppercase, lowercase, and a number

---

## 18. Customer-Facing Pages

These are the pages your customers interact with. Understanding them helps you answer customer questions.

### Inspection Report (`/inspection/{token}`)

When you send an inspection, the customer receives a link to a page showing:
- Overall health score with color-coded ring
- Every inspected item with green/yellow/red rating
- Photos (tap to enlarge, swipe to navigate)
- "Book Service" button if no estimate exists yet
- Print button
- Available in English and Spanish

### Estimate Approval (`/approve/{token}`)

When you send an estimate, the customer gets a link to:
- Their vehicle info and greeting
- Each line item with approve/decline toggle
- Running cost total
- Priority grouping (safety/recommended/preventive)
- Link to their inspection report
- Print button
- Available in English and Spanish

### Member Portal (`/members`)

Customers can create an account to:
- View upcoming and past appointments
- See their vehicles
- View and respond to estimates
- Check their care plan status
- Send messages
- Sign in with Google

### Care Plans (`/care-plan`)

Customers can view and enroll in monthly care plans:
- Basic ($19/mo), Standard ($29/mo), Premium ($49/mo)
- Each tier lists included services and discounts
- Enrollment flows through checkout

### Appointment Self-Service

Customers receive token links in their emails to:
- **Cancel** (`/cancel/{token}`) — one-click cancellation
- **Reschedule** (`/reschedule/{token}`) — pick a new date/time

No login required — the token authenticates them.

### Booking Page (`/book-appointment/`)

The public booking form includes:
- Service type selection
- Date and time picker (only shows available slots)
- Customer info fields
- VIN field with decode button (auto-fills vehicle details)
- SMS opt-in checkbox
- Bilingual support

---

## 19. Invoices & Receipts

### Generating an Invoice

1. Open a completed Repair Order.
2. Click **"Generate Invoice"**.
3. The system creates an invoice with line items carried from the estimate/RO.
4. Each invoice gets a unique number.
5. Review and adjust if needed, then click **Save**.

### Sending to Customer

1. On a completed invoice, click **"Send to Customer"**.
2. The customer receives an email with a link to view the invoice.
3. The link uses a secure token — no login required.
4. The customer invoice page is bilingual (EN/ES) with a print button.

### Managing Invoices

1. Go to **Repair Orders** tab → open an RO → **Invoices** section.
2. View invoice history, resend links, or generate new invoices.
3. Admin CRUD available via the invoices management interface.

---

## 20. Loyalty & Referrals

### Loyalty Points

1. Points are automatically earned on completed ROs (points-per-dollar).
2. View customer point balances in the **Customers** tab.
3. Manage the rewards catalog via the loyalty management interface:
   - Create rewards with point costs
   - Set active/inactive status
   - Track redemptions
4. Care plan members earn tier bonus multipliers.

### Referral Program

1. Each customer gets a unique referral code.
2. New customers can enter a referral code during booking.
3. The referring customer earns bonus loyalty points when the referral completes service.
4. Track referral status: pending → completed → rewarded.
5. Public referral lookup available at `/referral-lookup`.

---

## 21. Labor Tracking

### Logging Labor Hours

1. Open a Repair Order.
2. In the **Labor** section, click **"Add Labor Entry"**.
3. Select the technician, labor type (diagnosis, repair, inspection), and enter hours.
4. Save the entry. Multiple entries per RO are supported.

### Viewing Reports

1. The **Analytics** tab includes labor utilization data.
2. Track hours logged vs. estimated per RO.
3. Employee performance metrics show efficiency rates.
4. The `labor-tracker.js` interface provides real-time tracking.

---

## 22. Waitlist / Walk-In Queue

### Adding a Walk-In

1. When a walk-in customer arrives, add them to the waitlist.
2. Enter customer name, phone, service needed, and vehicle info.
3. The system assigns a queue position and estimated wait time.

### Managing the Queue

1. View all waiting customers in queue order.
2. Drag and drop to reorder if needed.
3. When ready, mark a customer as "called" — they receive an SMS notification (if Twilio configured).
4. Mark as "completed" or "no-show" when done.

### Customer Self-Service

Customers can check their waitlist position via `/waitlist` (public API).

---

## 23. Tire Quote Requests

### Receiving Quotes

1. Customers submit tire quote requests from the public website.
2. Requests appear in the admin panel with vehicle info and desired tire specs.
3. Each request shows: customer name, vehicle, tire size, quantity, preferred brand.

### Responding to Quotes

1. Open a tire quote request.
2. Enter the quoted price, tire details, and availability.
3. Change status from "pending" to "quoted".
4. The customer receives a notification with the quote details.
5. Track status: pending → quoted → accepted → expired.

---

## 24. Push Notifications

### How Push Notifications Work

1. Customers can opt in to push notifications via the website (browser prompt).
2. Notifications are sent automatically for:
   - Booking confirmations
   - Appointment reminders (next day)
   - RO status updates
   - Vehicle ready for pickup
3. Notifications are bilingual (EN/ES) based on customer preference.

### Broadcasting Promotions

1. Go to the admin panel.
2. Use the **Push Broadcast** feature to send a promotional notification to all opted-in subscribers.
3. Enter the message (English and Spanish).
4. Broadcasts are limited to 5 per day to prevent spam.

### Managing Subscriptions

- The push notification queue processor runs every 5 minutes via cron.
- Customers can manage their notification preferences (4 toggles: bookings, reminders, status updates, promotions).
- Failed notifications are retried automatically.

---

## 25. Troubleshooting

### "I can't log in."

1. Make sure you're going to `oregon.tires/admin/`
2. Check email and password (watch for extra spaces).
3. If locked out after 5 attempts, wait 15 minutes.
4. Use password reset, or contact Tyrone at (774) 277-9202.

### "Images aren't showing on the website."

1. Try a hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac).
2. Check that the image was uploaded in the correct language (English or Spanish).
3. Service image slots without uploads use fallback images automatically.

### "Customer didn't receive an email."

1. Go to **Messages** → **Email Logs** to verify the email was sent.
2. Ask the customer to check spam/junk folder.
3. Verify the customer's email address is correct in their record.
4. If emails consistently fail, contact a developer to check SMTP settings.

### "Contact form isn't working."

1. Ask the customer to check their internet connection.
2. Check browser console (F12 → Console tab) for errors.
3. Verify the API is responding: visit `oregon.tires/api/health.php`.

### "Language toggle isn't switching."

1. JavaScript must be enabled in the browser.
2. Try a different browser.
3. Hard refresh the page.

### "I see a 403 or 500 error."

1. For 403: ensure the page file exists on the server.
2. For 500: check `.htaccess` for syntax errors, or contact a developer.
3. Try clearing browser cache.

### "Repair order status won't change."

1. Check that you're clicking the correct status button.
2. Some transitions require prerequisites (e.g., estimate must exist before pending_approval).
3. Try refreshing the page.

### "VIN decode isn't working."

1. Ensure the VIN is exactly 17 characters.
2. Very old vehicles (pre-1981) may not have decodable VINs.
3. The public endpoint is rate-limited (10/hr). Admin VIN decode has no limit.

### "Kanban board cards aren't dragging."

1. Click and hold the card for a moment before dragging.
2. Try refreshing the page.
3. Ensure you're not trying to drag to an invalid status (cards can only move to adjacent statuses).

### "Customer can't see their inspection/estimate."

1. Verify the inspection/estimate was marked as "sent" (not just completed).
2. Ask the customer to check the link in their email (it uses a unique token).
3. Check Email Logs to confirm the email was delivered.

### "Estimate auto-generation is empty."

1. The inspection must have at least one **red** or **yellow** item.
2. Make sure the inspection is in **completed** status.
3. All-green inspections don't generate estimate items (nothing needs repair).

### "Cron emails aren't sending."

1. Cron jobs run on the server at fixed times (6 PM for reminders, 10 AM for reviews/estimate reminders).
2. If no emails are going out, the cron jobs may not be configured on the server.
3. Contact a developer to verify crontab entries.

### "Blog post not showing on the website."

1. Make sure the post status is **Published** (not Draft).
2. Check that both English and Spanish content are filled in.
3. Try refreshing the blog page.

### "Promotion not appearing."

1. Check that today's date is between the start and end dates.
2. Make sure the **Active** toggle is on.
3. Try refreshing the page.

### "Google sign-in not working for customers."

1. Google OAuth must be configured on the server (developer task).
2. If the button shows but fails, check browser console for errors.
3. This requires valid Google OAuth credentials in the server configuration.

---

## Quick Reference: Admin Tabs

| Tab | What It Does |
|-----|-------------|
| **Overview** | 14-day activity snapshot |
| **Appointments** | Calendar + list view, assign, status, notes, bulk assign |
| **Customers** | Search, create, edit, view history and vehicles |
| **Repair Orders** | RO lifecycle, kanban board, inspections, estimates |
| **Messages** | Contact submissions + email logs |
| **Employees** | Staff CRUD, admin access control |
| **Gallery** | Gallery images + 8 service image slots |
| **Blog** | Bilingual blog post management |
| **Promotions** | Time-limited offers |
| **FAQ** | Bilingual Q&A management |
| **Reviews** | Customer testimonials |
| **Analytics** | Appointment, customer, employee stats |
| **Docs** | Manual, features, improvements |
| **Site Content** | Business info, email templates, pricing |
| **Subscribers** | Email subscriber list |

## Quick Reference: RO Status Flow

```
intake → diagnosis → estimate_pending → pending_approval → approved → in_progress → waiting_parts → ready → completed → invoiced
```

At any point, an RO can also be **cancelled**.

## Quick Reference: Inspection Traffic Light

| Color | Meaning | Action |
|-------|---------|--------|
| Green | Good condition | No action needed |
| Yellow | Needs attention | Recommended service |
| Red | Critical | Safety concern — needs immediate repair |

## Quick Reference: Estimate Line Item Types

| Type | Use For |
|------|---------|
| Labor | Service hours |
| Parts | Replacement parts |
| Tire | Tires, mounting, balancing |
| Fee | Shop/diagnostic/disposal fees |
| Discount | Price reductions |
| Sublet | Subcontracted work |

## Quick Reference: Keyboard Shortcuts

| Key | Where | Action |
|-----|-------|--------|
| Left Arrow | Inspection photo overlay | Previous photo |
| Right Arrow | Inspection photo overlay | Next photo |
| Escape | Inspection photo overlay | Close overlay |
| Swipe Left/Right | Inspection photo overlay (mobile) | Navigate photos |

---

*This manual was written for the Oregon Tires Auto Care admin team. For technical support, contact Tyrone Norris at tyronenorris@gmail.com or (774) 277-9202. For Spanish support, contact Margarita Escalante at growwithmagi@gmail.com or (541) 936-1884.*
