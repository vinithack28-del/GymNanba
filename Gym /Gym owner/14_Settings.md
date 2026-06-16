# GymOS — Gym Owner Portal
# Module 14: Settings

**URL:** `/settings`
**Sub-pages:** `/settings/profile`, `/settings/account`, `/settings/integrations`, `/settings/language`
**Access:** Gym owner only. All staff roles have zero access to the settings pages.
**Purpose:** Configure all gym-level settings and the owner's personal account.

---

## 1. Gym Profile (`/settings/profile`)

### Fields

| Field | Validation | Required |
|---|---|---|
| **Gym name** | 2–80 chars | Yes |
| **Business type** | Gym / Yoga studio / CrossFit / Martial arts / Dance / Sports club / Other | Yes |
| **Logo** | JPG/PNG/SVG, max 2 MB, min 200×200px | No |
| **Cover photo** | JPG/PNG, max 5 MB, min 1200×400px | No |
| **Address line 1** | 5–100 chars | Yes |
| **Address line 2** | Max 100 chars | No |
| **City** | 2–50 chars | Yes |
| **State** | Dropdown of Indian states | Yes |
| **PIN code** | 6-digit numeric | Yes |
| **Phone** | E.164 format | Yes |
| **Email** | RFC 5322 | Yes |
| **Website** | Valid URL | No |
| **GSTIN** | `^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[0-9A-Z]$` | No |
| **PAN** | `^[A-Z]{5}[0-9]{4}[A-Z]$` | No |
| **Business registration no.** | Max 30 chars | No |
| **Instagram URL** | Valid Instagram URL | No |
| **Facebook URL** | Valid Facebook URL | No |
| **About / description** | Max 1,000 chars; shown in member app | No |

### Operating Hours

Per-day configuration table:

| Day | Closed toggle | Open time | Close time |
|---|---|---|---|
| Monday | ☐ | 06:00 | 22:00 |
| Tuesday | ☐ | 06:00 | 22:00 |
| … | | | |
| Sunday | ☑ | — | — |

- Close time must be after open time
- Closed toggle: greys out open/close fields for that day
- Used by: class timetable, booking availability, member app display
- Branch-specific hours can be configured separately in each branch's edit form (overrides gym-wide)

---

## 2. My Account (`/settings/account`)

### Editable Fields
- Owner full name
- Phone (E.164 format)
- Profile photo (JPG/PNG, max 2 MB)

### Email
- Read-only. "Request change" link → sends email to super admin requesting email change.

### Password Change

| Field | Rule |
|---|---|
| Current password | Required to change |
| New password | Min 12 chars, 1 uppercase, 1 lowercase, 1 number, 1 symbol |
| Confirm new password | Must match new password exactly |

On save: all active sessions terminated immediately, confirmation email sent.

### Two-Factor Authentication (TOTP)

**Enable flow:**
1. QR code displayed (scan with Google Authenticator / Authy)
2. Enter 6-digit verification code
3. Recovery codes shown **once** (8 codes, each usable once)
4. 2FA active

**Disable flow:**
1. Enter current password
2. Enter current TOTP code
3. 2FA removed

**Regenerate recovery codes:**
- Enter password + TOTP code to confirm
- All existing recovery codes invalidated
- New 8 codes displayed once

### Active Sessions

Table: Device type | IP address | Approximate location | Last active

Actions:
- Terminate individual session
- "Sign out all other devices" (terminates all except current)

---

## 3. Integrations (`/settings/integrations`)

Cards in 2-column grid. Each card: name, description, status (Connected / Not connected), Connect/Disconnect button, configuration fields.

### WhatsApp Business API

| Field | Notes |
|---|---|
| Business Phone Number ID | From Meta Business Manager |
| API Access Token | Permanent token from Meta |
| Verify Token | For webhook verification |
| Webhook URL | Shown (read-only, copy button) — register this in Meta |

Test button: sends a test message to the owner's phone number.

### Razorpay

| Field | Notes |
|---|---|
| Key ID | From Razorpay dashboard |
| Key Secret | Masked after save |
| Webhook Secret | For verifying webhook payloads |
| Test mode toggle | ON = sandbox / OFF = live |

Webhook URL shown for registration in Razorpay dashboard.

### Biometric Device

| Field | Notes |
|---|---|
| Device type | ZKTeco / eSSL / Mantra / Other |
| IP address | Device IP on local network |
| Port | Default: 4370 |
| Device serial | Optional |
| Sync schedule | Real-time / Every 5 min / Every 15 min |

Test connection button: pings device and shows response time.

### Google Calendar

Connect via OAuth button (opens Google consent screen). Scopes: read/write calendar events.

Settings after connect:
- Sync toggle: classes → Google Calendar events (on/off)
- Calendar selector: dropdown of owner's Google calendars

### Tally / Accounting

| Field | Notes |
|---|---|
| Export format | Tally XML / CSV |
| Auto-sync toggle | Daily at 2:00 AM IST |
| Manual export | "Export now" button → triggers background job |

---

## 4. Language Settings (`/settings/language`)

Dropdown of platform-enabled languages:

| Locale code | Language |
|---|---|
| `en-IN` | English (India) — default |
| `hi-IN` | Hindi — हिन्दी |
| `ta-IN` | Tamil — தமிழ் |
| `te-IN` | Telugu — తెలుగు |

- Selection applies **immediately** to this owner's portal session
- Does **not** change language for staff or members (they have separate preferences)
- Stored in `owner_preferences.preferred_language`

---

## 5. Billing & Subscription (View Only Tab)

| Field | Shown |
|---|---|
| Current plan name | e.g. "Growth" |
| Plan features | Bullet list of included features |
| Branches used | "2 of 3 branches" |
| Members used | "1,240 members (plan: unlimited)" |
| Renewal date | dd MMM yyyy |
| Monthly amount | Rs. 2,999.00 |
| Invoice history | Table with download links |

> No self-serve plan change. Message: "To change your plan or billing details, contact GymOS support at support@gymos.in."

---

## 6. Data & Privacy

### Export All Data
- Button: "Export my gym data"
- Generates: ZIP of CSVs (Members, Payments, Attendance, Expenses, Staff)
- Background job for large gyms
- Email sent with download link (valid 48 hours)

### Account Deletion Request
- Confirm dialog: explains data will be deleted after 90 days, active subscriptions will end
- Sends request to super admin
- Owner receives confirmation email
- Cannot be self-processed — requires super admin action

---

## 7. API Endpoints

```
GET /api/v1/settings/profile
  Response: { gym profile object }

PUT /api/v1/settings/profile
  Body: partial update of any profile fields
  Response 200: { profile }

GET /api/v1/settings/account
  Response: { name, email, phone, avatar_url, totp_enabled,
              sessions: [{ device, ip, location, last_active }] }

PUT /api/v1/settings/account
  Body: { name?, phone?, avatar_url? }
  Response 200: { account }

PUT /api/v1/settings/account/password
  Body: { current_password, new_password }
  Response 200: { success: true }
  Error 401: WRONG_CURRENT_PASSWORD

POST /api/v1/settings/account/sessions/:sessionId/terminate
  Response 200: { terminated: true }

POST /api/v1/settings/account/sessions/terminate-all-others
  Response 200: { terminated: N }

GET /api/v1/settings/integrations
  Response: { integrations: [{ key, name, status, config_fields }] }

PUT /api/v1/settings/integrations/:key
  Body: integration-specific config fields
  Response 200: { connected: true }

POST /api/v1/settings/integrations/:key/disconnect
  Response 200: { disconnected: true }

POST /api/v1/settings/integrations/:key/test
  Response 200: { test_passed: true }
  or  { test_passed: false, error: "Connection refused" }

GET /api/v1/settings/language
  Response: { preferred_language, available_languages: [...] }

PUT /api/v1/settings/language
  Body: { locale_code }
  Response 200: { preferred_language }

GET /api/v1/settings/subscription
  Response: { plan_name, features, branches_used, branches_limit,
              renewal_date, amount_paise, invoices: [...] }

POST /api/v1/settings/data-export
  Response 202: { job_id, message: "Export started. Email when ready." }
```

---

## 8. Database Schema

```sql
CREATE TABLE gym_settings (
  id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id        UUID NOT NULL UNIQUE REFERENCES tenants(id),
  name             VARCHAR(80) NOT NULL,
  business_type    VARCHAR(50),
  logo_url         TEXT,
  cover_photo_url  TEXT,
  address1         VARCHAR(100),
  address2         VARCHAR(100),
  city             VARCHAR(50),
  state            VARCHAR(50),
  pin              CHAR(6),
  phone            VARCHAR(20),
  email            VARCHAR(255),
  website          TEXT,
  gstin            VARCHAR(15),
  pan              VARCHAR(10),
  reg_number       VARCHAR(30),
  social_links     JSONB,
    -- { instagram: '...', facebook: '...' }
  about            TEXT,
  operating_hours  JSONB,
    -- { mon: { open: '06:00', close: '22:00', closed: false }, ... }
  updated_at       TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE integrations (
  id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id    UUID NOT NULL REFERENCES tenants(id),
  key          VARCHAR(50) NOT NULL,
    -- whatsapp | razorpay | biometric | google_calendar | tally
  status       VARCHAR(20) NOT NULL DEFAULT 'disconnected',
  config       JSONB,  -- non-secret config; secrets stored in vault
  connected_at TIMESTAMPTZ,
  updated_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT uq_integration UNIQUE (tenant_id, key)
);

CREATE TABLE owner_preferences (
  id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id           UUID NOT NULL UNIQUE REFERENCES tenants(id),
  owner_id            UUID NOT NULL REFERENCES tenant_owners(id),
  preferred_language  VARCHAR(10) NOT NULL DEFAULT 'en-IN'
                      REFERENCES platform_languages(locale_code),
  notification_prefs  JSONB,
  updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
```

---

## 9. Validation Rules

| Field | Rule |
|---|---|
| GSTIN | `^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[0-9A-Z]$` |
| PAN | `^[A-Z]{5}[0-9]{4}[A-Z]$` |
| Phone | E.164 format |
| Logo | JPG/PNG/SVG only; max 2 MB |
| Cover photo | JPG/PNG only; max 5 MB |
| Operating hours | Close time must be after open time for non-closed days |
| Password | Min 12 chars, 1 uppercase, 1 lowercase, 1 number, 1 symbol |
| Locale code | Must match a code in `platform_languages` table |

---

## 10. Security Notes

- Integration secrets (API keys, tokens) are stored in a secret vault (e.g. AWS Secrets Manager), not in the `config` JSONB column
- `config` JSONB contains only non-sensitive config (e.g. phone number ID, sync schedule)
- Secret fields are masked in API responses (show first 4 + `****`)
- Changing password invalidates all active sessions immediately
- 2FA recovery codes are hashed before storage; shown in plaintext only at generation time
