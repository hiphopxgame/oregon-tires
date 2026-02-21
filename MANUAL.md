# Oregon Tires Auto Care -- Website Instruction Manual

**Oregon Tires Auto Care**
8536 SE 82nd Ave, Portland, OR 97266
Phone: (503) 367-9714
Hours: Monday--Saturday 7:00 AM -- 7:00 PM | Sunday Closed

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
3. [Managing Contact Messages](#3-managing-contact-messages)
4. [Managing Employees](#4-managing-employees)
5. [Managing Gallery Images](#5-managing-gallery-images)
6. [Managing Service Images (Hero and Feature Cards)](#6-managing-service-images-hero--feature-cards)
7. [Viewing Analytics](#7-viewing-analytics)
8. [Account Settings](#8-account-settings)
9. [Public Website Features (What Your Customers See)](#9-public-website-features-what-your-customers-see)
10. [Documentation Tab](#10-documentation-tab)
11. [Troubleshooting](#11-troubleshooting)

---

## 1. Getting Started

### How to Access the Admin Dashboard

1. Open your web browser (Chrome, Firefox, Safari, or Edge all work).
2. In the address bar, type your website address followed by `/admin/`. For example: `oregon.tires/admin/`
3. You will see a login screen.

### How to Log In

1. Enter your **email address** in the email field.
2. Enter your **password** in the password field.
3. Click the **Log In** button.
4. If this is your first time, the super admin account email is **tyronenorris@gmail.com**. If you do not know the password, use the password reset option or contact whoever set up the account.

### What You See After Logging In

After a successful login, you land on the **Overview** tab. This screen shows you a snapshot of your business activity over the last 14 days, including:

- Number of new appointments received
- Number of new contact messages
- Recent activity highlights

Use the **navigation tabs** along the top of the dashboard to move between sections: Appointments, Messages, Employees, Gallery, Analytics, Docs, and Settings.

---

## 2. Managing Appointments

The Appointments tab is where you view, organize, and manage all service appointments that customers have booked through the website. There are two ways to view appointments: **Calendar View** and **List View**.

### Calendar View

1. Click the **Appointments** tab in the dashboard.
2. The calendar shows the current month by default.
3. Use the **left arrow** and **right arrow** buttons to navigate to previous or future months.
4. **Color meanings on the calendar:**
   - **Green dot** on a date = That date has one or more appointments scheduled.
   - **Yellow ring** around a date = That date is today.
5. Click on any date to see all appointments scheduled for that day.

### List View

The List View shows all appointments in a table format, which is useful for searching and filtering.

1. Switch to the **List View** using the view toggle (if not already selected).
2. **Search**: Use the search bar at the top to find appointments by customer name, phone number, or other details.
3. **Filter by Status**: Use the status dropdown to show only appointments with a specific status (New, Confirmed, Completed, or Cancelled).
4. **Filter by Month**: Use the month filter to narrow results to a specific month.
5. **Sort Columns**: Click on any column header (like Date, Name, or Status) to sort the list by that column.
6. **Change Per-Page Count**: At the bottom of the list, choose how many appointments to show per page (for example, 10, 25, or 50).

### Assigning an Employee to an Appointment

1. Find the appointment in the list or calendar view.
2. Look for the **employee dropdown** next to the appointment.
3. Click the dropdown and select the employee you want to assign.
4. The appointment status will **automatically change to Confirmed** when you assign an employee.
5. The system will **automatically send an email** to the assigned employee (if they have an email on file) notifying them about the appointment.

### Changing Appointment Status

Each appointment moves through a workflow:

**New** --> **Confirmed** --> **Completed** or **Cancelled**

1. Find the appointment you want to update.
2. Click the **status dropdown** on that appointment.
3. Select the new status:
   - **Confirmed** -- The appointment has been acknowledged and assigned.
   - **Completed** -- The service has been finished.
   - **Cancelled** -- The appointment was cancelled.
4. The change is saved automatically.
5. When an appointment is marked as **Completed**, the system automatically sends a completion email to the customer.

### Adding Admin Notes to an Appointment

1. Find the appointment you want to annotate.
2. Click the **notes icon** (it looks like a small notepad or pencil icon next to the appointment).
3. A text box will appear where you can type your note. For example: "Customer requested alignment check as well" or "Called to confirm -- arriving at 3 PM."
4. Click **Save** to store the note.
5. Notes are only visible to admins and employees -- customers do not see them.

### Quick Assign All (Bulk Assignment)

If you have several unassigned appointments and want to distribute them quickly:

1. Click the **"Quick Assign All"** button (located above the appointments list).
2. The system will automatically distribute all unassigned appointments evenly among your active employees using a round-robin method (Employee A gets one, Employee B gets the next, Employee C gets the next, and so on).
3. All assigned appointments will be set to **Confirmed** status.
4. Email notifications will be sent to each assigned employee.

---

## 3. Managing Contact Messages

### Viewing Messages

1. Click the **Messages** tab in the dashboard.
2. You will see a list of all messages submitted through the website's contact form.
3. Each message shows:
   - **Customer name**
   - **Contact information** (email and/or phone)
   - **Message preview** (first few lines of their message)
   - **Date received**
   - **Status** (New, Priority, or Completed)

### Reading a Full Message

1. Find the message you want to read.
2. Click the **"View"** button next to it.
3. The full message will open so you can read all the details.

### Changing Message Status

1. Find the message you want to update.
2. Use the **status dropdown** or status buttons to change it:
   - **New** -- The message has not been handled yet (this is the default).
   - **Priority** -- Mark this for urgent messages that need immediate attention.
   - **Completed** -- Mark this when you have responded to or handled the message.

### Email Logs Sub-Tab

1. Within the Messages section, look for the **"Email Logs"** sub-tab.
2. Click it to view a record of every email the system has sent, including:
   - Appointment confirmation emails
   - Employee assignment notification emails
   - Appointment completion emails
3. This is useful for verifying that emails were sent or for troubleshooting if a customer says they did not receive a notification.

---

## 4. Managing Employees

### Adding a New Employee

1. Click the **Employees** tab in the dashboard.
2. Click the **"Add Employee"** button.
3. Fill in the form:
   - **Name** (required) -- The employee's full name.
   - **Email** (optional but recommended) -- If you provide an email, the system will automatically create a login account for the employee.
   - **Phone** (optional) -- The employee's phone number.
   - **Role** -- Choose either **Employee** or **Manager**.
4. Click **Save**.

### Editing an Employee

1. Go to the **Employees** tab.
2. Find the employee card you want to update.
3. Click the **"Edit"** button on their card.
4. Update any fields you need to change (name, email, phone, or role).
5. Click **Save** to apply the changes.

### Deactivating an Employee

When an employee leaves or is no longer working, you should deactivate them rather than delete them. This preserves their appointment history.

1. Go to the **Employees** tab.
2. Find the employee card.
3. Click the **"Deactivate"** button.
4. The employee will no longer appear in assignment dropdowns when you are assigning appointments.
5. All of their past appointment history is preserved.

### Reactivating an Employee

1. Go to the **Employees** tab.
2. Look for the inactive employee (they may be in a separate section or visually marked as inactive).
3. Click the **"Activate"** button on their card.
4. They will reappear in the assignment dropdowns and be available for new appointments.

### Granting Admin Dashboard Access to an Employee

1. Go to the **Employees** tab.
2. Find the employee you want to promote.
3. Click the **"Make Admin"** button on their card.
4. That employee can now log in to the admin dashboard at `oregon.tires/admin/` using their email and password.

### Adding a New Admin (Who Is Not an Existing Employee)

1. Go to the **Employees** tab.
2. Find the **"Add Admin"** form.
3. Enter the person's **email address**.
4. Click **Add**. The system will create an account if one does not already exist and grant admin dashboard access.

### Revoking Admin Access

1. Go to the **Employees** tab.
2. Find the admin whose access you want to remove.
3. Click the **"Revoke Admin"** button.
4. That person will no longer be able to access the admin dashboard. They will still exist as an employee if they were one.

---

## 5. Managing Gallery Images

Gallery images appear on the public website in the Gallery section that customers can browse.

### Adding a New Image

1. Click the **Gallery** tab in the dashboard.
2. Click the **"Add Image"** button.
3. **Select a file** from your computer (JPG, PNG, or similar image formats work best).
4. Enter a **Title** (required) -- This is the name that appears with the image.
5. Choose a **Language**:
   - **English** -- The image will appear when the site is in English.
   - **Spanish** -- The image will appear when the site is in Spanish.
6. Add an optional **Description** if you want to include extra details about the image.
7. Click **Upload**.
8. The image is uploaded to the server and will appear on the website automatically.

### Deleting an Image

1. Go to the **Gallery** tab.
2. Find the image you want to remove.
3. Click the **red delete button** on the image.
4. Confirm the deletion when prompted.
5. **Warning**: This permanently removes the image. It cannot be undone.

---

## 6. Managing Service Images (Hero & Feature Cards)

Service images control what visitors see on the main sections of the public website. These are different from gallery images -- they are the large background and feature images that define the look and feel of the site.

### Accessing Service Images

1. Click the **Gallery** tab in the dashboard.
2. Switch to the **"Service Images"** sub-tab.

### The 8 Service Image Slots

There are 8 image slots, each controlling a specific part of the website:

| Slot Name | What It Controls |
|---|---|
| Hero Background | The large banner image at the top of the homepage |
| Expert Technicians | The feature card for expert technicians |
| Fast Cars | The feature card for fast service |
| Quality Parts | The feature card for quality parts |
| Bilingual Support | The feature card for bilingual (English/Spanish) support |
| Tire Shop | The tire services section image |
| Auto Repair | The auto repair section image |
| Specialized Tools | The specialized tools section image |

### Uploading a New Service Image

1. Find the service image slot you want to update (for example, "Hero Background").
2. Click the **"Choose File"** button next to that slot name.
3. Select an image from your computer.
4. Click **Upload**.
5. The new image will replace the old one immediately.

### Adjusting Image Position and Zoom

After uploading, you may want to adjust how the image is framed so the most important part of the photo is visible:

1. Find the service image you just uploaded.
2. Use the **Horizontal slider** to move the image left or right.
3. Use the **Vertical slider** to move the image up or down.
4. Use the **Zoom slider** to zoom in or out.
5. You will see a live preview of your adjustments.
6. When you are satisfied, click **"Save Position & Crop"** to save your adjustments.

### Understanding the "Live" Badge

- A **"Live"** badge next to a service image means that image is currently showing on the public website.
- If no custom image has been uploaded for a slot, the website automatically uses a built-in fallback image. You do not need to upload images for the site to work -- fallbacks are already in place.

---

## 7. Viewing Analytics

The Analytics tab gives you a bird's-eye view of your business performance.

### How to View Analytics

1. Click the **Analytics** tab in the dashboard.
2. Review the following sections:

### Appointment Statistics

- **Total Appointments**: The overall number of appointments in the system.
- **Breakdown by Status**: See how many appointments are New, Confirmed, Completed, or Cancelled.
- **This Week's Count**: How many appointments are scheduled for the current week.

### Customer Statistics

- **Total Unique Customers**: How many different customers have booked with you.
- **Returning Customers**: How many customers have come back for additional appointments.

### Employee Performance

- **Appointments Handled**: How many appointments each employee has been assigned.
- **Appointments Completed**: How many each employee has finished.
- **Average Time Per Appointment**: The typical time from assignment to completion for each employee.

### Popular Times

- Shows which appointment time slots are requested most often. This helps you plan staffing and availability.

---

## 8. Account Settings

### Accessing Your Settings

1. Look for your **email address** displayed in the top-right corner of the dashboard.
2. Click on it to open the settings panel.

### Changing Your Notification Email

1. In the settings panel, find the **Notification Email** field.
2. Enter the email address where you want to receive admin notifications (such as new appointment alerts).
3. Click **Save**.

### Updating Your Display Name

1. Find the **Display Name** field in settings.
2. Type your preferred name (this is what appears in the dashboard).
3. Click **Save**.

### Changing Your Login Email

1. Find the **Email** field in settings.
2. Enter your new email address.
3. You may need to confirm the change via a confirmation step.
4. Click **Save**.

### Changing Your Password

1. Find the **Password** section in settings.
2. Enter your new password. It must be at least **8 characters** long, including an uppercase letter, a lowercase letter, and a number.
3. Confirm the new password by typing it again (if prompted).
4. Click **Save**.

---

## 9. Public Website Features (What Your Customers See)

This section describes the features that your customers interact with on the public-facing website. Understanding these helps you know what your customers experience.

### Language Toggle (English / Spanish)

- Visitors will see a **globe icon** in the top area of the website.
- Clicking the globe icon switches the entire website between **English** and **Spanish**.
- All text, labels, and headings change to the selected language.
- The site remembers the visitor's language choice as they browse.

### Contact Form

- The website has a **contact form** where visitors can send you a message.
- When a visitor submits the form, their message is sent to the server API and immediately appears in your **Messages** tab in the admin dashboard.
- The form collects the visitor's name, contact details, and their message.

### Schedule Service

- The **"Schedule Service"** button or link on the website directs visitors to the contact form section so they can reach out to book an appointment.

### Customer Reviews

- The website displays **3 randomly selected customer reviews** each time a visitor loads the page.
- This keeps the reviews section fresh and varied for repeat visitors.

### Gallery

- The **Gallery** section on the public website displays the images you have uploaded through the Gallery tab in your admin dashboard.
- Images are shown in the language the visitor has selected (English or Spanish), based on the language you assigned when uploading.

### Google Maps

- The website includes an embedded **Google Maps** widget showing your shop location at 8536 SE 82nd Ave, Portland, OR 97266.
- This helps customers find directions to your shop.

---

## 10. Documentation Tab

The Docs tab provides built-in documentation directly inside the admin dashboard.

### Viewing Documentation

1. Click the **Docs** tab in the dashboard.
2. You will see sub-tabs for different documents:
   - **Manual** -- This instruction manual.
   - **Features** -- A complete list of all site features.
   - **Roadmap** -- The strategic growth plan and future features.
3. Each document is available in both English and Spanish. The language shown matches your dashboard language setting.

### Bilingual Documentation

- Documentation sections automatically display in your selected language.
- Use the language toggle in the dashboard header to switch between English and Spanish.

---

## 11. Troubleshooting

### "I can't log in to the admin dashboard."

1. Double-check that you are going to the correct address: `oregon.tires/admin/`
2. Make sure you are typing your email and password correctly (check for extra spaces or capitalization).
3. Try resetting your password if the option is available.
4. If you are still locked out, contact the super admin, Tyrone Norris, at **tyronenorris@gmail.com** or **(774) 277-9202** for help with your account.

### "Images I uploaded are not showing on the website."

1. When you upload images through the admin dashboard, they are stored on the server. The website loads them from there.
2. If no images have been uploaded for a particular section, the website uses **fallback images** from the `/images/` folder on the server.
3. Make sure the fallback images exist on the server if you have not uploaded replacements through the dashboard.
4. Try refreshing the page (press Ctrl+F5 on Windows or Cmd+Shift+R on Mac to do a hard refresh that clears the cache).

### "The contact form is not working."

1. The contact form submits data to the server via the website.
2. Ask the customer to check their internet connection.
3. If the problem persists, open the browser's developer tools (press F12) and check the **Console** tab for error messages. Share these with a developer if needed.

### "The language toggle is not switching between English and Spanish."

1. The language toggle requires **JavaScript** to be enabled in the visitor's browser.
2. Most browsers have JavaScript enabled by default. If it has been disabled, the visitor needs to re-enable it in their browser settings.
3. Try a different browser to confirm whether the issue is browser-specific.

### "I see a '403 Forbidden' error when visiting the site."

1. Make sure the file `index.html` exists in the root folder on the server.
2. Make sure the `.htaccess` file is present and properly configured on the server.
3. If you recently uploaded files, double-check that they were placed in the correct directory on the server.
4. Contact your hosting provider if the issue persists.

### "The site looks broken or unstyled on a device."

1. The website uses **Tailwind CSS** that is compiled and included with the site, so styling works even if external resources are unavailable. If the site still appears unstyled, try refreshing the page.
2. Ask the visitor to refresh the page once their connection is stable.
3. The site is designed to be **responsive** (it adjusts to phones, tablets, and desktops). If something looks off on a specific device, take a screenshot and share it with a developer.

### "An employee says they did not receive an email notification."

1. Go to the **Messages** tab and click the **Email Logs** sub-tab to verify whether the system sent the email.
2. Ask the employee to check their **spam or junk folder**.
3. Make sure the employee has a valid email address entered in their profile under the Employees tab.
4. If emails are consistently not being delivered, there may be an issue with the SMTP (email server) configuration. Contact a developer to check the server settings.

---

## Quick Reference: Admin Dashboard Tabs

| Tab | What It Does |
|---|---|
| **Overview** | Shows 14-day activity snapshot after login |
| **Appointments** | View, assign, and manage customer appointments |
| **Messages** | Read and manage contact form submissions and email logs |
| **Employees** | Add, edit, activate/deactivate employees and manage admin access |
| **Gallery** | Upload and manage gallery images and service images |
| **Analytics** | View appointment, customer, and employee performance stats |
| **Docs** | View instruction manual, features, and roadmap |
| **Settings** | Update your profile, email, and password |

---

## Quick Reference: Appointment Status Flow

```
New  -->  Confirmed  -->  Completed
                    -->  Cancelled
```

- **New**: A customer just booked. No employee assigned yet.
- **Confirmed**: An employee has been assigned. The customer has been notified.
- **Completed**: The service is done. The customer receives a completion email.
- **Cancelled**: The appointment was cancelled and will not be serviced.

---

*This manual was written for the Oregon Tires Auto Care admin team. For technical support, contact Tyrone Norris at tyronenorris@gmail.com or (774) 277-9202. For Spanish support, contact Margarita Escalante at growwithmagi@gmail.com or (541) 936-1884.*
