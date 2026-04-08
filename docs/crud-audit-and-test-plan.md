# Oregon Tires — CRUD Audit & Test Plan

## Bug: Deleted Employees Still in Schedule

**Root Cause:** Employee deletion (`api/admin/employees.php`) performs NO cascade cleanup. It only deletes from `oretir_employees` — orphaned rows remain in:
- `oretir_schedules` — NOT cleaned up
- `oretir_schedule_overrides` — NOT cleaned up
- `oretir_employee_skills` — NOT cleaned up
- Appointment `assigned_employee_id` — NOT nullified

**Schedule API Issue:** `api/admin/schedules.php` line 26-31 queries ALL employees including inactive. The daily view (line 79) correctly filters by `is_active = 1`, but the weekly view does not.

**Fix Required:**
1. Add cascade cleanup to employee DELETE handler (both single and bulk)
2. Add `WHERE e.is_active = 1` filter to weekly schedule query
3. Clean up existing orphaned schedule/skill rows for deleted employees

---

## CRUD Gaps Audit

### Entities with FULL CRUD (Create, Read, Update, Delete)
- employees.php
- customers.php
- conversations.php
- vehicles.php
- blog.php
- faq.php
- promotions.php
- testimonials.php
- gallery.php
- services.php
- loyalty-rewards.php

### Entities MISSING operations

| Entity | Missing | Notes |
|--------|---------|-------|
| **admins.php** | UPDATE | Can create & deactivate, but no PUT to update name/role/email |
| **appointments.php** | CREATE, DELETE | Only read & update. Bookings come from public form only |
| **repair-orders.php** | DELETE | Has create (from appointment/walk-in), read, update |
| **estimates.php** | DELETE | Has create, read, update |
| **invoices.php** | DELETE | Has create (from RO), read, update |
| **subscribers.php** | CREATE, UPDATE | Read-only + soft unsubscribe |
| **referrals.php** | CREATE | Read, update (mark complete), delete |
| **tire-quotes.php** | CREATE, DELETE | Read & update only |
| **waitlist.php** | CREATE | Read, update, delete |
| **service-reminders.php** | CREATE | Read, update, delete |

### Priority Fixes
1. **appointments.php** — Add CREATE (admin can book on behalf of customer) and DELETE
2. **admins.php** — Add UPDATE (edit name, role, email, language)
3. **repair-orders.php** — Add DELETE (superadmin only, cascade inspections/estimates/invoices/labor)
4. **estimates.php** — Add DELETE (superadmin only)
5. **invoices.php** — Add DELETE (superadmin only)
6. **subscribers.php** — Add CREATE (admin can manually add subscriber) and UPDATE
7. **tire-quotes.php** — Add CREATE and DELETE
8. **waitlist.php** — Add CREATE (admin can add walk-in to queue)
9. **service-reminders.php** — Add CREATE (admin can set reminder for customer)
10. **referrals.php** — Add CREATE (admin can create referral code)

---

## Employee Deletion — Required Cascade Cleanup

```php
// Before deleting from oretir_employees:
$db->prepare('DELETE FROM oretir_schedules WHERE employee_id = ?')->execute([$id]);
$db->prepare('DELETE FROM oretir_schedule_overrides WHERE employee_id = ?')->execute([$id]);
$db->prepare('DELETE FROM oretir_employee_skills WHERE employee_id = ?')->execute([$id]);
$db->prepare('DELETE FROM oretir_labor_entries WHERE employee_id = ?')->execute([$id]);
$db->prepare('UPDATE oretir_appointments SET assigned_employee_id = NULL WHERE assigned_employee_id = ?')->execute([$id]);
$db->prepare('UPDATE oretir_repair_orders SET assigned_employee_id = NULL WHERE assigned_employee_id = ?')->execute([$id]);
```

---

## Test Infrastructure

### Frameworks Available
- **Vitest** (v3.0.0) — JS unit tests in `tests/unit/*.test.js` (~40 files)
- **Playwright** (v1.50.0) — E2E browser tests in `tests/e2e/*.spec.js`
- **PHP TestHelper** — Custom assertion framework in `public_html/tests/TestHelper.php`

### Run Commands
```bash
npm test              # Vitest unit tests
npm run test:watch    # Vitest watch mode
npm run test:e2e      # Playwright E2E
php public_html/tests/test-site.php   # PHP API tests
```

### PHP TestHelper Pattern
```php
require_once __DIR__ . '/TestHelper.php';
TestHelper::initSession();
$t = new TestHelper('Test Suite Name');

$t->test('description', function () {
    TestHelper::assertEqual(expected, actual);
    TestHelper::assertTrue(condition);
    TestHelper::assertContains(needle, haystack);
});

$t->done();
```

---

## TDD Test Plan

### Phase 1: Write Tests First (RED)

**File: `tests/test-crud-operations.php`**

Test cases for each entity:
1. Create → verify row exists in DB
2. Read → verify response contains expected fields
3. Update → verify changed fields persist
4. Delete (single) → verify row removed + cascade cleanup
5. Delete (bulk) → verify multiple rows removed
6. Delete protected accounts → verify rejection
7. Self-delete prevention → verify rejection
8. Schedule cleanup on employee delete → verify no orphans

### Phase 2: Implement Missing Operations (GREEN)

Priority order:
1. Fix employee deletion cascade (schedule bug)
2. Fix schedule API to filter inactive employees
3. Add missing DELETE to: appointments, repair-orders, estimates, invoices, tire-quotes
4. Add missing CREATE to: appointments (admin booking), subscribers, waitlist, service-reminders, referrals, tire-quotes
5. Add missing UPDATE to: admins

### Phase 3: Refactor & Verify (REFACTOR)

1. Run full test suite
2. Verify bilingual support on all new UI elements
3. Deploy to BlueHost
4. Manual smoke test all tabs

---

## UI Forms Needed

### Existing Create Forms
- ✅ Admin creation (Settings → Admin Users → Create Admin)
- ✅ Employee creation (Team → Employees → Add Employee)
- ✅ Customer creation (Shop → Customers → Add Customer)
- ✅ Blog post creation (Marketing → Blog → New Post)
- ✅ FAQ creation (Marketing → FAQ → Add FAQ)
- ✅ Promotion creation (Marketing → Promotions → New Promotion)
- ✅ Testimonial creation (Marketing → Reviews → Add Testimonial)
- ✅ Gallery upload (Marketing → Gallery → Upload)
- ✅ Service creation (Shop → Services → Add Service)
- ✅ Loyalty reward creation (Marketing → Loyalty → Add Reward)
- ✅ Vehicle creation (within customer detail)
- ✅ RO creation (from appointment or walk-in)
- ✅ Invoice creation (from completed RO)
- ✅ Estimate creation (from RO)

### Missing Create Forms
- ❌ Appointment creation (admin booking on behalf of customer)
- ❌ Subscriber creation (manual add)
- ❌ Waitlist creation (manual add walk-in)
- ❌ Service reminder creation (set reminder for customer/vehicle)
- ❌ Referral code creation (generate for customer)
- ❌ Tire quote creation (start quote for customer)

### Missing Update Forms
- ❌ Admin update (edit existing admin name/role/language)
