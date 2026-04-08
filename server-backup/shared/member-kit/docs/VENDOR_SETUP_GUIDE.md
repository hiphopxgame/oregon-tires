# Third-Party Vendor Setup Guide

**For Member Kit Phase 4-7 Enhancements**

---

## Overview

The following third-party services are required for optional features. Setup is organized by vendor and feature. You can implement these incrementally—not all are required for Phase 4-5.

---

## 1. SMS Provider (Phase 6.3 — SMS 2FA)

### Option A: Twilio (Recommended)

**Why:** Industry standard, reliable, good docs, competitive pricing

**Setup Steps:**

1. **Create Account**
   - Visit https://www.twilio.com
   - Sign up for free trial account ($5 free credits)
   - Verify phone number (your number)

2. **Get Credentials**
   - Go to Console Dashboard
   - Find **Account SID** (copy)
   - Find **Auth Token** (copy)
   - Request production numbers in Settings → Phone Numbers

3. **Add to .env**
   ```
   TWILIO_ACCOUNT_SID=your_account_sid_here
   TWILIO_AUTH_TOKEN=your_auth_token_here
   TWILIO_PHONE_NUMBER=+1234567890
   SMS_PROVIDER=twilio
   ```

4. **Install PHP SDK**
   ```bash
   cd ---member-kit
   composer require twilio/sdk
   ```

5. **Test Setup**
   ```bash
   php tests/vendor-twilio-test.php
   ```

**Cost:** $0.0075 per SMS (+ fees) — ~$0.01 per code
**Rate Limit:** 1000 SMS/day on free tier
**Status:** ✅ Production Ready

---

### Option B: Vonage (Alternative)

**Why:** Similar to Twilio, good alternative

**Setup Steps:**

1. **Create Account**
   - Visit https://dashboard.nexmo.com
   - Sign up (free tier: 2€ credit)

2. **Get Credentials**
   - Dashboard → API Settings
   - Find **API Key** (copy)
   - Find **API Secret** (copy)
   - Create dedicated Sender ID

3. **Add to .env**
   ```
   VONAGE_API_KEY=your_api_key
   VONAGE_API_SECRET=your_api_secret
   VONAGE_FROM_ID=YourAppName
   SMS_PROVIDER=vonage
   ```

4. **Install PHP SDK**
   ```bash
   composer require vonage/client-core vonage/client-sms
   ```

**Cost:** Similar to Twilio (~$0.07 per message average)
**Status:** ✅ Production Ready

---

### Option C: AWS SNS (If Using AWS)

**Why:** Integrated if already on AWS, cheaper at scale

**Setup Steps:**

1. **Enable SNS in AWS Console**
   - Go to AWS → SNS → Text messaging

2. **Get Credentials**
   - Create IAM user with SNS permissions
   - Get **Access Key ID** and **Secret Access Key**

3. **Add to .env**
   ```
   AWS_ACCESS_KEY_ID=your_key
   AWS_SECRET_ACCESS_KEY=your_secret
   AWS_REGION=us-east-1
   SMS_PROVIDER=aws_sns
   ```

4. **Request Production Limit**
   - SMS by default limited to $1/day
   - Request limit increase in SNS Settings

**Cost:** $0.00645 per SMS in US
**Status:** ✅ Production Ready

---

## 2. Geolocation Service (Phase 5.2 — Login History with Location)

### Option A: MaxMind GeoIP2 (Recommended)

**Why:** Most accurate IP geolocation, trusted by industry

**Setup Steps:**

1. **Create Account**
   - Visit https://www.maxmind.com
   - Sign up for account

2. **Download GeoIP2 Database**
   - Go to Account → My License Key
   - Create license key (copy)
   - Download GeoLite2 City database (.mmdb file)

3. **Add to .env**
   ```
   MAXMIND_LICENSE_KEY=your_license_key
   MAXMIND_DB_PATH=/path/to/GeoLite2-City.mmdb
   GEO_PROVIDER=maxmind
   ```

4. **Install PHP Library**
   ```bash
   composer require geoip2/geoip2
   ```

5. **Place Database**
   ```bash
   mkdir -p ---member-kit/data
   cp GeoLite2-City.mmdb ---member-kit/data/
   ```

**Cost:** Free (GeoLite2) or $120/year (GeoIP2 Precision)
**Status:** ✅ Production Ready

---

### Option B: IP2Location (Alternative)

**Why:** Good accuracy, affordable, easy API

**Setup Steps:**

1. **Create Account**
   - Visit https://www.ip2location.com
   - Sign up for free (limited queries/month)

2. **Get API Key**
   - Dashboard → API Key (copy)

3. **Add to .env**
   ```
   IP2LOCATION_API_KEY=your_api_key
   GEO_PROVIDER=ip2location
   ```

4. **Install PHP SDK**
   ```bash
   composer require ip2location/ip2location-php
   ```

**Cost:** Free tier (500 lookups/month) or $49/month paid
**Status:** ✅ Production Ready

---

### Option C: Cloudflare (If Using CDN)

**Why:** Free if already using Cloudflare, built-in to requests

**Setup Steps:**

1. **Enable Country/IP Headers**
   - Cloudflare Dashboard → Rules → Transform Rules
   - Headers get added automatically to each request

2. **Read from Request Headers**
   - `CF-IPCountry` header contains 2-letter country code
   - No API key needed

3. **Add to code**
   ```php
   $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'Unknown';
   ```

**Cost:** Free if Cloudflare already enabled
**Status:** ✅ Simple (geo-location only, no city-level)

---

## 3. Email Service (Phase 4.2 — Password Reset Emails)

### Option A: SendGrid (Recommended)

**Why:** Excellent deliverability, free tier generous, great APIs

**Setup Steps:**

1. **Create Account**
   - Visit https://sendgrid.com
   - Sign up (free: 100 emails/day)

2. **Create API Key**
   - Dashboard → Settings → API Keys
   - Create new key (Full Access)
   - Copy API key

3. **Add to .env**
   ```
   SENDGRID_API_KEY=your_api_key
   EMAIL_PROVIDER=sendgrid
   SENDGRID_FROM_EMAIL=noreply@yoursite.com
   ```

4. **Install PHP SDK**
   ```bash
   composer require sendgrid/sendgrid-php
   ```

5. **Verify Sender**
   - Settings → Sender Authentication
   - Add sender domain

**Cost:** Free tier (100/day) → $30/month (50k/month)
**Status:** ✅ Production Ready

---

### Option B: Mailgun (Alternative)

**Why:** Developer-friendly, good docs, competitive pricing

**Setup Steps:**

1. **Create Account**
   - Visit https://mailgun.com
   - Sign up

2. **Get Credentials**
   - Manage → Domains → Add domain
   - Get **API Key** and **Domain Name**

3. **Add to .env**
   ```
   MAILGUN_API_KEY=your_api_key
   MAILGUN_DOMAIN=mail.yoursite.com
   EMAIL_PROVIDER=mailgun
   ```

4. **Install PHP SDK**
   ```bash
   composer require mailgun/mailgun-php
   ```

**Cost:** Free tier (up to 100 emails/day)
**Status:** ✅ Production Ready

---

## 4. Authenticator Apps Library (Phase 6.3 — SMS + Phase 7.1 WebAuthn)

### TOTP (Time-based One-Time Password) Library

**Required for:** 2FA setup (generates QR code for Google Authenticator, Authy, Microsoft Authenticator)

**Setup:**

```bash
composer require spomky-labs/otphp
```

**No API key needed** — works entirely offline

**Cost:** Free (open-source)
**Status:** ✅ Production Ready

---

## 5. WebAuthn Library (Phase 7.1 — Passkey Support)

### Recommended Library

**Setup:**

```bash
composer require web-auth/webauthn-lib
```

**Includes:**
- Credential registration ceremony
- Authentication ceremony
- Device attestation verification

**No API key needed** — works entirely offline

**Cost:** Free (open-source)
**Status:** ✅ Production Ready

---

## 6. Analytics (Phase 3 + Phase 4+ — Event Tracking)

### Google Analytics 4 (Recommended)

**Already integrated** from Phase 1-3 implementation

**Setup (if not already done):**

1. **Create GA4 Property**
   - Visit https://analytics.google.com
   - Create property for your site

2. **Get Measurement ID**
   - Admin → Data Streams → Web
   - Copy **Measurement ID** (format: G-XXXXXXXXXX)

3. **Add to .env**
   ```
   GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
   ```

**Cost:** Free tier (10M hits/month)
**Status:** ✅ Already Integrated

---

## Implementation Checklist

### Phase 4 (Now) — Required

- [ ] Email Provider (SendGrid or Mailgun)
  - Create account
  - Generate API key
  - Verify sender domain
  - Test email delivery

### Phase 5 (Next 2 weeks) — Recommended

- [ ] Geolocation (MaxMind or IP2Location)
  - Create account
  - Download/configure database
  - Test geo lookup

### Phase 6 (4 weeks) — Optional but Valuable

- [ ] SMS Provider (Twilio, Vonage, or AWS SNS)
  - Create account
  - Generate credentials
  - Request production limits
  - Test SMS sending

- [ ] TOTP Library
  - `composer require spomky-labs/otphp`
  - Test QR code generation

### Phase 7 (8+ weeks) — Future

- [ ] WebAuthn Library
  - `composer require web-auth/webauthn-lib`
  - Test credential registration

---

## Environment Variables Summary

```bash
# Email (Phase 4)
SENDGRID_API_KEY=
SENDGRID_FROM_EMAIL=noreply@yoursite.com
EMAIL_PROVIDER=sendgrid

# Geolocation (Phase 5)
MAXMIND_LICENSE_KEY=
MAXMIND_DB_PATH=/path/to/GeoLite2-City.mmdb
GEO_PROVIDER=maxmind

# SMS (Phase 6)
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_PHONE_NUMBER=
SMS_PROVIDER=twilio

# Google Analytics (Phase 3+)
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

---

## Testing Setup

After each vendor setup:

```bash
# Test email
php tests/vendor-email-test.php

# Test geo
php tests/vendor-geo-test.php

# Test SMS
php tests/vendor-sms-test.php

# Test analytics
php tests/vendor-analytics-test.php
```

---

## Cost Breakdown (Monthly at Scale)

```
Email (SendGrid):     $30/month (50k/month)
SMS (Twilio):         $50/month (6,667 codes/month)
Geo (MaxMind):        $0/month (GeoLite2 free)
Authenticator:        $0/month (open-source)
WebAuthn:             $0/month (open-source)
─────────────────────────────────
Total:                $80/month
```

*Note: These are estimates at scale. Free tiers often sufficient for small deployments.*

---

## Support

If you encounter issues with any vendor:

1. Check vendor documentation
2. Verify API credentials in .env
3. Run vendor-specific test script
4. Check error logs: `tail -f error_log`
5. Enable vendor debug mode if available

---

**Next Step:** Once you've set up email provider, inform me and Phase 4 agents can begin implementation.
