# Network-Wide Site Connection Tracking — Implementation Summary

**Status**: ✅ Code implementation complete | Pending server deployment

**Purpose**: Enable network-wide tracking of member activity across 1vsM sites by:
1. Adding `registered_site_key` to the `members` table (tracks where each member first registered)
2. Creating `member_site_connections` table (logs every site a member touches + timestamps)
3. Updating 1vsm.com to use the shared `members` table in `hiphopwo_rld_system` instead of isolated `ovsm_members`

---

## Changes Implemented

### 1. MemberAuth.php — New Method + Hooks

**File**: `---member-kit/includes/member-kit/MemberAuth.php`

#### Added `recordSiteConnection(int $memberId): void` (private method)

```php
/**
 * Record a member's connection to a site.
 *
 * Inserts or upserts into member_site_connections table. Silent no-op if:
 * - site_key is empty
 * - member_site_connections table is absent (graceful degradation)
 * - PDO is null
 */
private static function recordSiteConnection(int $memberId): void
```

**Behavior**:
- Upserts a row into `member_site_connections` (member_id, site_key)
- On first insert: `first_seen_at = NOW()`, `connection_count = 1`
- On update: increments `connection_count`, updates `last_seen_at = NOW()`
- Silent no-op if `site_key` is empty string or table is absent (graceful degradation)

#### Updated `register()` method (lines ~555-565)

Adds two lines after `lastInsertId()`:
1. Sets `registered_site_key` on the member row if `site_key` is configured
2. Calls `recordSiteConnection($memberId)` to record the first site visit

#### Updated `startAuthenticatedSession()` method (lines ~907-920)

Adds one line to call `recordSiteConnection((int) $member['id'])` after setting session vars.
This records every login across all sites.

---

### 2. Migration: 007_site_connections.php

**File**: `---member-kit/migrations/007_site_connections.php` (new)

Creates/alters:
- `members.registered_site_key` — VARCHAR(64), nullable, indexed
- `member_site_connections` — new table with:
  - `member_id` (FK → members.id)
  - `site_key` (VARCHAR 64)
  - `first_seen_at` — timestamp when they first visited
  - `last_seen_at` — most recent activity
  - `connection_count` — total logins from that site
  - UNIQUE(member_id, site_key) — one row per member+site pair

---

### 3. 1vsm.com Configuration

**File**: `---1vsm.com/includes/auth.php`

Changed `MemberAuth::init()` config:
```php
// Before:
'members_table'  => 'ovsm_members',
'table_prefix'   => 'ovsm_',

// After:
'members_table'  => 'members',        // Standard shared table
'table_prefix'   => '',               // No prefix
'site_key'       => '1vsm',           // Enables connection tracking
```

Also updated `getCurrentMember()` helper to query `members` table instead of `ovsm_members`.

**Note**: `.env` change (`DB_NAME=hiphopwo_rld_system`) is server-side only — never SCP'd.

---

## Data Model

```sql
-- In hiphopwo_rld_system database

members
  id                    INT UNSIGNED PRIMARY KEY
  registered_site_key   VARCHAR(64) NULL          ← "1vsm", "oregon_tires", etc.
  email, password_hash, display_name, ...         ← existing columns

member_site_connections
  id                INT UNSIGNED PRIMARY KEY
  member_id         INT UNSIGNED (FK)
  site_key          VARCHAR(64)                   ← "1vsm", "oregon_tires", etc.
  first_seen_at     TIMESTAMP                     ← member's intro to this site
  last_seen_at      TIMESTAMP                     ← most recent login
  connection_count  INT UNSIGNED                  ← total logins from this site
```

### Example Data

Member registers on 1vsm.com, later logs in via oregon.tires:
```
members: id=42, registered_site_key='1vsm'

member_site_connections:
  - (42, '1vsm',           first=2026-02-24 10:15, last=2026-02-24 12:30, count=5)
  - (42, 'oregon_tires',   first=2026-02-24 13:00, last=2026-02-24 14:45, count=2)
```

---

## Test Coverage

**File**: `---member-kit/tests/test-site-connections.php`

7 comprehensive test assertions:
1. ✓ `recordSiteConnection()` inserts with `connection_count=1`
2. ✓ Second call increments count and updates `last_seen_at`
3. ✓ No-op when `site_key` is empty string
4. ✓ Graceful fail when `member_site_connections` table absent
5. ✓ `register()` writes `registered_site_key` when configured
6. ✓ `register()` leaves `registered_site_key` NULL when not configured
7. ✓ `startAuthenticatedSession()` records connection after login

**Note**: Tests require database configuration (`.env` with DB_NAME, DB_USER). Run on server only.

---

## Deployment Order (Server)

### Prerequisites
- Shared database: `hiphopwo_rld_system` (must have existing `members` table from member-kit)
- SSH access: `ssh hiphopworld` → `/home/hiphopwo/public_html/`

### Steps

1. **Deploy updated member-kit to server**
   ```bash
   scp ---member-kit/includes/member-kit/MemberAuth.php hiphopworld:/home/hiphopwo/shared/member-kit/includes/member-kit/
   scp ---member-kit/migrations/007_site_connections.php hiphopworld:/home/hiphopwo/shared/member-kit/migrations/
   ```

2. **Run migration on server**
   ```bash
   ssh hiphopworld
   cd /home/hiphopwo/shared/member-kit
   php migrations/007_site_connections.php
   ```
   Expected output:
   ```
   Running migration 007: Add site connection tracking
     ✓ Added registered_site_key column to members
     ✓ Added index on registered_site_key
     ✓ Created member_site_connections table
   ```

3. **Update 1vsm.com config (server-side .env)**
   ```bash
   ssh hiphopworld
   cd /home/hiphopwo/public_html/---1vsm.com
   # Edit .env:
   #   OLD: DB_NAME=hiphopwo_vsmany
   #   NEW: DB_NAME=hiphopwo_rld_system
   nano .env
   ```

4. **Deploy 1vsm.com auth.php**
   ```bash
   scp ---1vsm.com/includes/auth.php hiphopworld:/home/hiphopwo/public_html/---1vsm.com/includes/
   ```

5. **Verify deployment**
   ```bash
   # Visit https://1vsm.com/members
   # Test login/signup
   # Check database:
   ssh hiphopworld
   mysql hiphopwo_rld_system
   > SELECT * FROM members LIMIT 1;
   > SELECT * FROM member_site_connections LIMIT 5;
   ```

---

## Opt-In for Other Sites

Any site can enable network tracking by:
1. Adding `site_key` to their `MemberAuth::init()` config
2. Pointing to `hiphopwo_rld_system` database (shared)

Example:
```php
MemberAuth::init($pdo, [
    'members_table' => 'members',
    'table_prefix'  => '',
    'site_key'      => 'oregon_tires',  // ← Enable tracking
    // ... other config
]);
```

Once enabled, all registrations and logins will populate:
- `members.registered_site_key` (origin site)
- `member_site_connections` (activity log)

---

## Backwards Compatibility

✅ **Fully backwards compatible**:
- Old sites using isolated tables with site-specific `table_prefix` continue to work
- `recorded_site_key` defaults to NULL for existing members (pre-migration)
- Connection tracking is silent no-op if table absent (graceful degradation)
- Empty `site_key` means no tracking (opt-in behavior)

---

## Security & Privacy

✅ **No sensitive data stored**:
- Only member_id, site_key, and timestamps
- No passwords, IPs, or user behavior logged
- Foreign key ensures orphaned records auto-delete if member deleted
- UNIQUE constraint prevents duplicate site rows per member

---

## Next Steps

After deployment:
1. ✅ Test login/signup at https://1vsm.com/members
2. ✅ Verify tables in production database
3. ✅ Run test suite: `php tests/test-site-connections.php`
4. Optional: Enable tracking on other sites (oregon.tires, gremgoyles, etc.)
5. Optional: Build network dashboard queries:
   - "Members who touched multiple sites"
   - "Site activity by date range"
   - "Member funnels (first site → other sites)"
