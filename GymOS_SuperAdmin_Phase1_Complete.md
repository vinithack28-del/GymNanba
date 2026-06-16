# GymOS SaaS — Super Admin Portal
## Phase 1 Complete Technical Specification

**Authentication · Tenant Management · Subscriptions · Dashboard · Audit Log · Emails · API Spec**

> Version 2.0 Complete Edition | June 2026 | Confidential — Internal Use Only

---

## Table of Contents

1. [Phase 1 Overview — scope, timeline, go-live criteria](#1-phase-1-overview)
2. [Side Navigation Menu — UI structure and flows](#2-side-navigation-menu--phase-1)
3. [Module Deep Dives — authentication, tenants, subscriptions, dashboard](#3-module-deep-dives)
4. [Audit Log — design, schema, implementation](#4-audit-log--design--implementation)
5. [Email Templates — 8 transactional email designs with copy](#5-transactional-email-templates--phase-1)
6. [Core Database Schema — complete SQL schema for Phase 1](#6-complete-database-schema--phase-1)
7. [REST API Specification — all endpoints, request/response contracts](#7-rest-api-specification--phase-1)
8. [Data Validation Rules — field constraints, business logic](#8-data-validation-rules)
9. [Error Handling & Edge Cases — failure modes and recovery](#9-error-handling--edge-cases)
10. [Security Threat Model & Mitigations](#10-security-threat-model--mitigations)
11. [Testing Strategy — unit, integration, e2e, load, security](#11-testing-strategy)
12. [Deployment, Monitoring & Rollback](#12-deployment-monitoring--rollback)
13. [Final Go-Live Checklist — all sign-off items](#13-final-go-live-checklist)

---

## 1. Phase 1 Overview

Phase 1 is the foundation of the entire GymOS platform. Nothing else can be built or operated until these four modules are production-ready. The goal is to enable the platform team to onboard the very first gym tenant securely, assign them a plan, and monitor basic platform health — all from a single, locked-down admin portal.

| Field | Details |
|---|---|
| **Scope** | Authentication & Security · Tenant Management · Subscription & Plan Control · Basic Dashboard · Audit Logging · Transactional Emails |
| **Timeline** | Month 1 – Month 2 (8 weeks) |
| **Milestone** | First paying gym tenant successfully onboarded end-to-end |
| **Effort estimate** | 280 engineering hours (1 backend engineer + 1 frontend engineer, parallel work) |
| **Who uses it** | Platform owner (you) and optionally 1–2 co-founders only |

### 1.1 Success Criteria — Phase 1 Go-Live

- Super admin account created with email + password + mandatory 2FA enforced on every login
- At least 1 pricing plan configured and tested end-to-end
- First test gym tenant onboarded using the wizard (all 4 steps completed)
- Subdomain routing working — `test.gymos.in` routes to correct tenant's schema
- All 8 Phase 1 transactional emails tested on real email clients (Gmail, Outlook, mobile)
- Emails landing in inbox (not spam) — verified using mail-tester.com or similar
- Audit log table created and enforced immutable — verified no UPDATE/DELETE permissions at DB level
- All critical audit events logged: tenant create/suspend/archive, plan assign, admin login, impersonate start/end
- Impersonate feature tested — super admin logs in as gym owner, actions logged, session terminates after 60 min
- Basic dashboard rendering with correct KPI calculations for test tenant
- Gym owner receives welcome email and can log in to their portal for the first time
- Admin can suspend/activate tenant and gym owner receives notification emails
- All error scenarios tested and handled gracefully (e.g. duplicate subdomain, invalid email, network timeouts)
- Load testing on staging: 10+ concurrent admin sessions, no degradation < 500ms response time
- Security audit completed: no SQL injection, XSS, CSRF vulnerabilities. All inputs validated and sanitized.
- Backup job configured — full platform DB backup every 6 hours, tested restore on recovery VM

---

## 2. Side Navigation Menu — Phase 1

The super admin portal uses a persistent left sidebar (210px width on desktop, collapsible hamburger on mobile). In Phase 1, only the following menu items exist. Items from Phase 2–4 are not shown yet — they are added progressively as each phase ships.

| Icon | Menu Item | What it shows |
|---|---|---|
| [#] | **Dashboard** | KPIs, active tenants, MRR, renewals due |
| [T] | **Tenants** | All gyms list — search, filter, status |
| [+] | **Add new tenant** | Wizard to onboard a new gym |
| [P] | **Plans & pricing** | Create and manage subscription plans |
| [S] | **Subscriptions** | Tenant-plan assignments, trial status |
| [I] | **Invoices & payments** | Manual payments, invoice download |
| [A] | **Audit log** | Every admin action with actor + timestamp |
| [G] | **Settings** | 2FA recovery, password, IP whitelist, email config, language management |
| ... | *Phase 2+* | Health Monitor, Support Tickets, Broadcasts, Advanced Analytics, Automations |

> **Design rule:** Do not show Phase 2+ menu items in Phase 1, even as disabled/greyed links. Early admin users should not be distracted by unbuilt features. Add each menu item only when the underlying feature is production-ready.

---

## 3. Module Deep Dives

---

### MODULE 1 · PHASE 1 — Authentication & Security
> Protect the highest-privilege account on the platform

## 3.1 Authentication & Security

### 3.1.1 Login Page Logic

The super admin login is a standalone URL separate from gym-owner logins. Recommended URL: `admin.gymos.in/login` — this must never appear in any public documentation. The login page has no branding, logo, or visual design — intentionally low-profile to avoid search engine indexing.

| Step | Logic |
|---|---|
| **1. Load login page** | Display email + password form. No 'sign up' link. No social login. Plain HTML, minimal CSS. Page title: 'Admin' (generic). No favicon (prevent favicon tracking). |
| **2. Email validation** | Frontend: basic email regex check. Backend: strict RFC 5322 validation. Max length 255 chars. Case-insensitive comparison (store as lowercase in DB, compare lowercase at login). |
| **3. Password validation** | Frontend: min 12 chars shown. Backend: bcrypt cost 12. Timing-safe string comparison to prevent timing attacks. Do NOT reveal whether email exists or password is wrong — generic error: 'Invalid credentials'. |
| **4. Rate limiting** | Per-IP: max 5 login attempts per 15 minutes (configurable). Redis-backed counter with sliding window. Increment on both email/password validation. Return 429 Too Many Requests after threshold. |
| **5. Lock account** | After 5 failed attempts within 15 min from same IP: set `failed_attempts=5` and `locked_until=now+30min` in admin_accounts table. All further login attempts to this email are rejected with 'Account locked' (same message for security). |
| **6. 2FA prompt** | On correct credentials: do NOT issue session yet. Redirect to `/auth/2fa` with a temporary unsigned token (JWT, 5 min expiry). Show TOTP code entry (6 digits). If SMS 2FA enabled, show 'Send code via SMS' option. |
| **7. TOTP verification** | Validate TOTP code against secret stored (AES-256 encrypted) in DB. Code window: +/-1 step (30-sec tolerance). Use library like speakeasy.js. On success: clear failed_attempts, create session. On fail: increment temp TOTP attempts counter (max 3 per 2FA session). |
| **8. Session creation** | Create session record: admin_id, ip_address, user_agent, created_at, last_active_at. Issue JWT access token (15 min TTL, contains: admin_id, ip, aud=admin). Also issue refresh token (7-day TTL, stored in httpOnly cookie, secure + sameSite=Strict). |
| **9. IP check** | After session created, cross-check request IP against ip_whitelist JSONB array in admin_accounts. If whitelist is non-empty and request IP is NOT in list: invalidate session immediately, return 403, send IP-blocked alert email. |
| **10. Redirect** | On success: redirect to `/admin/dashboard` and set `X-Tenant-ID=global` header. Record login event in admin_audit_log with action=LOGIN, ip_address, user_agent. |

### 3.1.2 2FA Setup Flow (forced on first login)

On first login after account creation, admin is forced through 2FA setup before accessing the dashboard. This is non-bypassable.

- Show QR code to scan with Google Authenticator, Authy, or similar TOTP app
- Display account key (base32) as backup if QR code fails to scan
- Admin scans QR and enters the 6-digit verification code shown in their app
- Backend verifies code against TOTP secret. Max 2 verification attempts (prevent bruteforce).
- If verified: encrypt TOTP secret with AES-256, store in `admin_accounts.totp_secret`. Set `totp_enabled=true`.
- If failed after 2 attempts: reset page, regenerate new secret, ask to try again (no progressive lockout).
- On success, generate 8 recovery codes (random 8-char alphanumeric). Each bcrypt-hashed and stored in JSONB array.
- Show recovery codes once (plaintext). Admin must download/print. Warn: *'These are shown once — save them in a secure place.'*
- Recovery codes are stored hashed but can be validated 1:1 by bcrypt comparison.
- Once saved, recovery codes cannot be viewed again (front-end confirms 'I have saved these codes' checkbox).
- 2FA is now permanently enforced — no UI option to disable it (only regenerate or use recovery codes).
- Recovery codes are single-use. Once used, that code is deleted from the array (update JSONB).

### 3.1.3 Session Management

URL: `/admin/settings/sessions` — The admin sees all active sessions and can terminate them remotely.

| Column | Data shown |
|---|---|
| **Device** | Browser + OS parsed from user agent (e.g. 'Chrome 125 on macOS Sonoma') |
| **IP address** | IPv4/IPv6 of the session origin. Shown with last octet masked for privacy: 10.0.0.XXX |
| **Location** | City/country resolved via MaxMind GeoLite2 IP geolocation |
| **Started** | ISO 8601 timestamp (e.g. 2026-06-03T10:30:00Z). Displayed in IST. |
| **Last active** | Timestamp of last API call using this session. Highlight red if > 7 days ago (stale). |
| **Action** | 'Terminate' button — POST to `/api/sessions/{sessionId}/terminate`, invalidates refresh token immediately |

> **Current session indicator:** The row representing the current logged-in session is highlighted in blue and shows '(Current session)' badge. Cannot terminate own current session (button disabled, tooltip explains why).
>
> **Bulk termination:** Top-right button 'Terminate all other sessions' — POST to `/api/sessions/terminate-others`. Invalidates all refresh tokens except current session. Logs audit event.
>
> **Session timeout:** Access token expires after 15 minutes of inactivity. Automatic refresh (using refresh token) happens transparently on next API call. If refresh token expired: admin is redirected to login page with reason 'Session expired'.

### 3.1.4 Password Reset (self-service)

URL: `/auth/forgot-password` — Admin enters their email, backend sends a reset link valid for 1 hour.

- Check if email exists in admin_accounts (if not, still show 'Check your email' — don't reveal whether account exists)
- Generate reset token: JWT signed with `admin.email` as aud, `exp = now + 1 hour`, `jti = uuid` (one-time use)
- Store `jti` in cache (Redis) with `ttl=3600`. Mark as 'unused'.
- Send password reset email with link: `https://admin.gymos.in/auth/reset-password?token={{reset_token}}`
- Admin clicks link, frontend validates JWT. If valid, show password reset form (new password + confirm).
- On submit: verify `jti` hasn't been used (check cache). If used, show 'Link expired or already used'. If valid: hash new password, update `admin_accounts.password_hash`, mark `jti` as used in Redis.
- Send confirmation email *'Your password was reset at [time]. If not you, reply to this email immediately.'*
- Clear all active sessions — force re-login with new password.

### 3.1.5 Account Recovery Using Recovery Codes

If admin loses access to their TOTP device and cannot complete 2FA:

- At 2FA prompt, show button 'Can't access your authenticator? Use a recovery code'
- Admin enters one of their 8 recovery codes (8 chars, case-insensitive)
- Backend finds matching bcrypt hash in `admin_accounts.recovery_codes` JSONB array
- Validate, mark code as used (delete from array), issue session normally
- Show warning: *'You used a recovery code. Generate new recovery codes at Settings > Account > Recovery codes to maintain security.'*
- Direct them to `/admin/settings/account/recovery-codes` where they can regenerate all 8 codes (old codes immediately invalidated)

### 3.1.6 IP Whitelist Configuration

URL: `/admin/settings/security/ip-whitelist` — Admin can restrict login to specific IP addresses.

- Whitelist is stored as JSONB array of CIDR strings: `['192.168.1.0/24', '10.0.0.5/32', ...]`
- Empty whitelist = all IPs allowed. Non-empty whitelist = enforce allowlist.
- Field accepts both single IPs (auto-converted to /32) and CIDR ranges.
- On each login, after 2FA succeeds: compare request IP against all CIDR ranges. If not matched, reject 403.
- Changes to whitelist take effect immediately. All active sessions of non-matching IPs are terminated.
- Admin must be very careful not to lock themselves out. UI shows warning: *'Your current IP is 203.0.113.45. Make sure this IP is in the whitelist.'*

### 3.1.7 Security Hardening — Headers & Cookies

| Security measure | Implementation |
|---|---|
| **HTTPS only** | All `/admin/*` endpoints redirect HTTP to HTTPS. `Strict-Transport-Security: max-age=31536000; includeSubDomains`. |
| **Cookies** | `refresh_token` stored in `httpOnly=true`, `Secure=true`, `SameSite=Strict` cookie. Not accessible to JavaScript (prevents XSS token theft). |
| **CSRF protection** | All POST/PUT/DELETE requests require `X-CSRF-Token` header (double-submit cookie pattern). Token regenerated on login, stored in non-httpOnly cookie. |
| **CSP header** | `Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;` Block inline scripts, external domain loads. |
| **X-Frame-Options** | `X-Frame-Options: DENY` (prevent clickjacking) |
| **X-Content-Type-Options** | `X-Content-Type-Options: nosniff` (prevent MIME type sniffing) |
| **X-XSS-Protection** | `X-XSS-Protection: 1; mode=block` (legacy browser support) |
| **Referrer-Policy** | `Referrer-Policy: no-referrer` (don't leak URLs in referer headers) |

---

### MODULE 2 · PHASE 1 — Tenant Management
> Create, view, configure, and control every gym on the platform

## 3.2 Tenant Management

### 3.2.1 Tenants List Page

URL: `/admin/tenants` — Default landing after Dashboard. Shows all tenants in a searchable, sortable data table with multiple filters.

| Feature | Specification |
|---|---|
| **Search** | Real-time search by gym name, owner name, email, or subdomain. Debounced (300ms). Results update instantly without page reload. Highlights matching text in results. |
| **Filters (left sidebar)** | Status (Active / Trial / Suspended / Archived), Plan (checkbox multi-select), Business type (Gym / Yoga / Turf), City (autocomplete dropdown), Created date (date range picker) |
| **Table columns** | Gym name (clickable), Owner name, Subdomain, Plan name, Status badge (color-coded), Members count (read-only), Created date, Last login (gym owner) |
| **Row actions** | Click row to open profile OR use dropdown menu: View, Impersonate, Edit, Suspend/Activate, Archive, Download invoice |
| **Bulk actions** | Top checkbox to select all. Dropdown: Suspend selected, Send broadcast, Export CSV with selected tenants |
| **Pagination** | 25 rows per page default. Dropdown to select 10 / 25 / 50 / 100. Page indicator shows 'Showing 1-25 of 1,234 tenants'. Server-side pagination using LIMIT/OFFSET. |
| **Sorting** | Click column header to sort ascending/descending. Indicators show sort order. Sort persists when filtering. |
| **Status badge colors** | Active=green, Trial=blue, Suspended=red, Archived=grey |

### 3.2.2 Add New Tenant — 4-Step Wizard

URL: `/admin/tenants/new` — Multi-step form that saves as draft after each step. Cannot skip steps. Progress indicator shows current step (1/4).

| Step | Screen | Form fields & validation |
|---|---|---|
| **Step 1** | Business details | Gym name (required, 2–80 chars, alphanumeric + spaces), Business type (radio: Gym/Yoga/Turf), City (autocomplete), State (dropdown), Address (text area, 10–200 chars), GST number (optional, regex validated), Phone (E.164 format) |
| **Step 2** | Owner account | Owner full name (2–100 chars), Owner email (unique check real-time, RFC 5322), Temp password (auto-generated 16-char shown once, copy button), Owner phone (optional). If email already exists: show error 'Email already registered as a tenant owner. Use a different email.' |
| **Step 3** | Subdomain & plan | Subdomain (3–20 chars, lowercase alphanumeric+hyphen, real-time availability check, forbidden: admin/api/www/app/mail/support/help/login/dashboard/gymos), Plan selection (radio, only Active plans shown), Trial toggle (yes/no with date picker), Trial end date (if yes, must be >= today+7 days) |
| **Step 4** | Review & confirm | Readonly summary of all data from steps 1-3. Submit button says 'Create tenant'. On click: POST to `/api/admin/tenants` with all data. Show spinner 'Creating tenant...' Do not allow double-click. |

### 3.2.3 Tenant Profile Page

URL: `/admin/tenants/{tenantId}` — Each gym has a full profile page with tabs and actions.

- **Tab 1: Overview** — Tenant name, type, city, phone, address, GST, current plan, status, created date, owner name/email
- **Tab 2: Subscriptions** — Table of all subscription records (plan, start date, end date, trial dates). Current subscription highlighted. Button to assign new plan.
- **Tab 3: Activity log** — Tenant-specific audit log. Shows all actions by gym owner and staff. Filterable by date, actor role.
- **Tab 4: Members** — Read-only count of total members, active members, inactive. Link to member list in gym owner's portal (if impersonating).
- **Tab 5: Notes** — Internal notes by super admin team. WYSIWYG editor. Not visible to gym owner. Timestamps of who wrote each note.

**Profile page actions**

- **Impersonate** — Opens a new browser tab logged in as the gym owner. Session time-limited to 60 min. Banner at top: *'You are impersonating [gym owner] — your actions will be logged.'*
- **Suspend** — Immediately blocks gym owner and all staff from logging in. Modal to select suspension reason (Dropdown: Non-payment, Policy violation, Requested by owner, Other). Optional text note. On submit: set `status=suspended`, write audit log, send suspension email to gym owner.
- **Activate** — Reverses suspension (if suspended). Requires confirmation. Sends activation email to gym owner. Sets `status=active`.
- **Archive** — Soft-delete. Requires typing gym name to confirm. Explains *'Archived data retained for 90 days then auto-purged.'* Sets `status=archived`, `archived_at=now`. Audit log entry written.
- **Edit** — Opens edit form for gym details (name, city, address, phone, GST). On save: validates all fields, updates DB, writes audit log entry.

### 3.2.4 Business Logic Rules & Constraints

> **Subdomain uniqueness:** Enforced at DB level (UNIQUE constraint). Checked in real-time during wizard Step 3.
>
> **Schema isolation:** Each tenant gets a dedicated PostgreSQL schema named `tenant_{uuid}`. Schemas created at tenant creation time using `CREATE SCHEMA`. No cross-tenant data access at DB level.
>
> **Impersonation limit:** Max 1 concurrent impersonation per admin. Enforce in code: `SELECT COUNT(*) FROM admin_sessions WHERE impersonating_tenant_id IS NOT NULL AND admin_id = ? AND created_at > now() - interval '1 hour'`.
>
> **Deletion policy:** Phase 1 does not support hard-delete. Archive = soft-delete. Data retained for 90 days. Scheduled job (daily at 2 AM UTC) hard-deletes archived tenants older than 90 days: `DROP SCHEMA tenant_xxx CASCADE`.
>
> **Status transitions:** Active → Suspended (reversible). Active → Archived (irreversible without manual SQL). Trial → Active (auto, when trial ends and payment succeeds).
>
> **Tenant owner email uniqueness:** Enforced across the entire platform. A gym owner's email cannot be reused as owner of another tenant.

---

### MODULE 3 · PHASE 1 — Subscription & Plan Management
> Define pricing, features, and billing for each tenant

## 3.3 Subscription & Plan Management

### 3.3.1 Plan Configuration

URL: `/admin/plans` — Admin creates and edits pricing plans. Plans are platform-wide templates in Phase 1.

| Field | Details & validation |
|---|---|
| **Plan name** | Starter / Growth / Enterprise etc. (50 chars max, alphanumeric + spaces + hyphen) |
| **Billing cycle** | Dropdown: Monthly / Quarterly / Annual. Different plans can have different cycles. |
| **Price (INR)** | Input field. Value stored in paise (integer, not float). Display as rupees with 2 decimals. Example: 2999.00 INR = 299900 paise. Min 0, Max 999999.99 INR. |
| **Max members** | Integer. 0 = unlimited. Non-zero = hard cap enforced at gym owner portal level. Example: Starter max_members = 50 means gym can add at most 50 members. |
| **Max branches** | Integer. Starter = 1, Growth = 3, Enterprise = unlimited (0). Enforced by gym owner portal. |
| **Max staff accounts** | Integer. Total staff (receptionist + trainer + accountant + POS) combined. Starter = 5. |
| **Feature flags** | Checkboxes for modules: Inventory/POS, Advanced analytics, White-label, API access, Biometric integration, WhatsApp integration, GST compliance mode. |
| **Trial eligible** | Toggle: Yes (this plan can be offered as trial) / No (paid-only) |
| **Description** | Text area (500 chars max). Shown to gym owners when viewing plans. Marketing-friendly copy. |
| **Status** | Dropdown: Active (assignable to new tenants), Archived (existing tenants keep it, new cannot select) |

> **Plan pricing change:** Changes to price apply only to NEW subscriptions. Existing subscriptions retain the old price until renewal.
>
> **Billing cycle logic:** Monthly = 30 days, Quarterly = 90 days, Annual = 365 days (ignore leap years). Used for calculating renewal dates and prorated amounts.
>
> **Feature flags:** Stored in JSONB. Example: `{'pos': true, 'analytics': false, 'whatsapp': true}`. Gym portal checks these flags before showing features to gym owner.

### 3.3.2 Assigning Plan to Tenant

- Admin navigates to tenant profile > Subscriptions tab > Click 'Assign plan' button
- Modal opens: Dropdown to select plan (only Active plans shown), Start date (date picker, default today), Trial (yes/no radio)
- If trial=yes: Trial end date picker appears (must be >= today+7 days, <= today+30 days)
- On submit: Backend creates subscription record: `tenant_id`, `plan_id`, `status='trial'` or `'active'`, `start_date`, `end_date` (null if ongoing), `trial_end_date`, `created_by=admin_id`, `created_at=now`
- Gym owner receives 'Plan assigned' email with plan name, features, and billing date
- If trial: email mentions *'Trial ends on [date]. You will be reminded 3 days before.'*

### 3.3.3 Plan Upgrade / Downgrade Logic

| Scenario | System behaviour |
|---|---|
| **Upgrade mid-cycle** | Example: Starter (Rs.1000/mo) to Growth (Rs.2500/mo) on day 15 of 30. Calculate prorated credit: `days_remaining/days_in_cycle * old_price = (15/30)*1000 = Rs.500`. Apply as immediate credit on next invoice. New subscription end_date = old_end_date (don't extend). Next invoice shows new plan price minus credit. |
| **Downgrade mid-cycle** | Scheduled for next renewal date (not immediate). Email gym owner: *'Plan downgrade scheduled for [renewal date].'* 7 days before renewal, send reminder. On renewal: downgrade takes effect. If current usage exceeds new plan limit (e.g. 4 branches but downgrading to 1-branch plan), admin must confirm override. |
| **Price change on active plan** | Only affects new subscriptions on that plan. All existing subscriptions retain old price until renewal. Example: Growth plan price changes from Rs.2500 to Rs.3000. Existing tenants pay Rs.2500 until renewal. |
| **Cancellation** | Admin clicks 'Cancel subscription' in modal. Confirm dialog. Set `subscription status=cancelled`, `cancelled_at=now`, `cancellation_reason` (dropdown). `End_date` can be set to today (immediate) or at current_end_date (at billing cycle end). Email gym owner. |
| **Reactivation after cancel** | Cancelled subscriptions cannot be reactivated. Admin must assign a new plan to the tenant. |

### 3.3.4 Invoices & Manual Payments

URL: `/admin/invoices` — Record manual payments and generate GST-compliant invoices.

- Form: Select tenant (dropdown/search), Amount (INR), Payment method (Cash / Bank transfer / UPI / Cheque), Transaction reference (optional), Date paid (date picker, default today)
- On submit: Backend creates entry in `tenant_payments` table (`tenant_id`, `admin_id`, `amount_paise`, `payment_method`, `transaction_ref`, `created_at`)
- Generate PDF invoice (IN format): invoice number (auto-increment per tenant), GSTIN (GymOS GSTIN), HSN code (998314 — IT services), amount, 18% GST breakdown, total, payment method, date
- Invoice emailed to gym owner automatically with PDF attachment
- Invoice list shows all invoices per tenant, sortable by date, downloadable as PDF

---

### MODULE 4 · PHASE 1 — Basic Platform Dashboard
> Executive overview of platform health and revenue

## 3.4 Basic Platform Dashboard

### 3.4.1 KPI Card Layout (top row)

| KPI card | Calculation & display |
|---|---|
| **Total tenants** | `SELECT COUNT(*) FROM tenants WHERE status != 'archived'`. Show number + trend vs last month. Click navigates to Tenants list. |
| **Active tenants** | COUNT of subscriptions with `status='active'` AND `trial_end_date IS NULL` AND `tenant status='active'`. Paying customers, not trials. |
| **Trials active** | COUNT of subscriptions WHERE `status='trial'` AND `trial_end_date >= TODAY`. Subtitle: 'Converting to paid next week' count if > 0. |
| **MRR** | SUM of monthly recurring revenue. Sum of `(plan_price / billing_cycle_months)` for all active non-trial subscriptions. Display in INR. |
| **Renewals this week** | COUNT of subscriptions WHERE `end_date BETWEEN TODAY AND TODAY+7`. Highlighted amber if > 0. |
| **Trials expiring in 7 days** | COUNT of subscriptions WHERE `trial_end_date BETWEEN TODAY AND TODAY+7`. Click shows list of at-risk tenants. |

### 3.4.2 Chart: MRR Trend (12-month)

Line chart showing MRR for last 12 months (month-over-month). X-axis = month, Y-axis = MRR in INR. Tooltip on hover shows exact amount. Trendline shows growth rate.

### 3.4.3 Recent Activity Feed (right sidebar)

Last 20 audit log entries across all tenants. Each entry shows: `[timestamp] [admin_name] [action_type] [target]`. Color-coded: green=CREATE, amber=UPDATE, red=DELETE/SUSPEND, blue=LOGIN. Clickable to navigate to relevant record.

### 3.4.4 Renewals Due Table (below MRR chart)

Sorted by renewal date (soonest first). Columns: Gym name, Current plan, Renewal date, MRR amount, Button 'Send reminder'.

---

### MODULE 5 · PHASE 1 — Multi-language Support
> Serve gym owners and staff in their preferred language across all portals

## 3.5 Multi-language Support

GymOS serves gym owners and staff across India and potentially other regions. Multi-language support allows the gym owner portal (and optionally the super admin portal) to be displayed in the user's preferred language. The super admin can configure which languages are available per tenant.

### 3.5.1 Supported Languages — Phase 1 Launch Set

| Language | Locale code | Script / RTL | Phase 1 status |
|---|---|---|---|
| **English (India)** | `en-IN` | Latin / No | ✅ Default — included at launch |
| **Hindi** | `hi-IN` | Devanagari / No | ✅ Included at launch |
| **Tamil** | `ta-IN` | Tamil / No | ✅ Included at launch |
| **Telugu** | `te-IN` | Telugu / No | ✅ Included at launch |
| **Kannada** | `kn-IN` | Kannada / No | ⏳ Phase 1.1 — post-launch sprint |
| **Marathi** | `mr-IN` | Devanagari / No | ⏳ Phase 1.1 — post-launch sprint |

### 3.5.2 Technical Architecture — i18n Implementation

Use a standard i18n library throughout: **react-i18next** (frontend) and **i18next** (Node.js backend for email templates). All user-facing strings are externalised into JSON translation files — no hardcoded text anywhere in components.

**File structure**

```
locales/
  en-IN/
    common.json
    dashboard.json
    tenants.json
    plans.json
    emails.json
  hi-IN/
    common.json
    dashboard.json
    ...  (mirrors en-IN structure)
  ta-IN/  ...
  te-IN/  ...
```

**Key implementation rules**

- `en-IN` is the canonical source. All other languages are translations of `en-IN` keys. Missing keys in a non-English locale fall back to `en-IN` automatically.
- **Locale detection order:** (1) user's saved preference in DB, (2) browser `Accept-Language` header, (3) default `en-IN`.
- **Number formatting:** use `Intl.NumberFormat` with the active locale. `Rs. 1,23,456` for `en-IN` (Indian comma grouping); `₹1,23,456` for `hi-IN`.
- **Date formatting:** use `Intl.DateTimeFormat`. Dates are stored in UTC; displayed in IST (`Asia/Kolkata`) across all locales.
- **Font loading:** Noto Sans for Latin scripts; Noto Sans Devanagari for Hindi/Marathi; Noto Sans Tamil for Tamil; Noto Sans Telugu for Telugu. Load via Google Fonts CDN with `display=swap`.
- **Email templates** are also translated. The email sent to a gym owner uses the language saved on their account (`preferred_language` column in `tenant_owners` table).

### 3.5.3 Super Admin Controls — Language Management

URL: `/admin/settings/languages` — The super admin manages which languages are active on the platform and can view translation completeness per locale.

| Feature | Specification |
|---|---|
| **Enable / disable language** | Toggle per language. Disabling a language removes it from all language-picker dropdowns immediately. Users previously set to that language fall back to `en-IN`. |
| **Translation completeness** | Admin UI shows a progress bar per language (e.g. "Hindi — 94% translated, 12 strings missing"). Missing strings are listed for the developer. A language cannot be enabled on production if completeness < 90%. |
| **Default language per tenant** | Super admin can set a default language per tenant during onboarding (Step 1 of wizard). Gym owner and staff can override their own language in their profile settings, choosing from the platform-enabled languages. |
| **Add new language** | Developer creates the `locales/{locale}/` folder with translated JSON files, deploys, and the language appears in this admin panel as "inactive" until enabled. No code change required to add a new language. |

### 3.5.4 Database Schema Additions

```sql
-- Platform-level language registry
CREATE TABLE platform_languages (
    locale_code       VARCHAR(10)  PRIMARY KEY,           -- e.g. 'hi-IN'
    display_name      VARCHAR(50)  NOT NULL,              -- e.g. 'हिन्दी'
    is_active         BOOLEAN      NOT NULL DEFAULT false,
    completeness_pct  SMALLINT     DEFAULT 0,             -- updated by CI pipeline
    is_rtl            BOOLEAN      NOT NULL DEFAULT false
);

-- Add preferred language to tenant owner
ALTER TABLE tenant_owners
    ADD COLUMN preferred_language VARCHAR(10)
    REFERENCES platform_languages(locale_code)
    DEFAULT 'en-IN';

-- Add default language to tenants table
ALTER TABLE tenants
    ADD COLUMN default_language VARCHAR(10)
    REFERENCES platform_languages(locale_code)
    DEFAULT 'en-IN';
```

### 3.5.5 API Endpoints — Language Management

| Method | Endpoint | Description | Request / Response |
|---|---|---|---|
| `GET` | `/settings/languages` | List all languages with completeness stats | Response: `[{locale_code, display_name, is_active, completeness_pct, is_rtl}]` |
| `PATCH` | `/settings/languages/{locale_code}` | Enable or disable a language | Request: `{is_active: true}` · Response: `{locale_code, is_active}` |
| `GET` | `/settings/languages/{locale_code}/missing-keys` | List untranslated keys | Response: `[{namespace, key, en_value}]` |
| `PUT` | `/tenants/{tenantId}/language` | Set tenant default language | Request: `{locale_code}` · Response: `{tenant_id, default_language}` |
| `PUT` | `/profile/language` | Gym owner sets own preferred language | Request: `{locale_code}` · Response: `{preferred_language}` · Auth: tenant-owner session |

---

## 4. Audit Log — Design & Implementation

The audit log is immutable and must be built into Phase 1. Every action performed by a super admin is recorded. This is your compliance record, dispute resolution mechanism, and debugging tool.

### 4.1 Database Table: admin_audit_log

| Column | Type & description |
|---|---|
| **id** | UUID PK default `gen_random_uuid()` |
| **actor_admin_id** | UUID FK to `admin_accounts.id` — who performed the action |
| **actor_name** | VARCHAR(100) — denormalized name (do not rely on FK alone for display) |
| **actor_ip** | VARCHAR(45) — IPv4/IPv6 of the admin at time of action |
| **action_type** | ENUM(CREATE, UPDATE, DELETE, SUSPEND, ACTIVATE, ARCHIVE, LOGIN, LOGOUT, IMPERSONATE_START, IMPERSONATE_END, PLAN_ASSIGN, PAYMENT_RECORD, SETTINGS_CHANGE) |
| **target_type** | ENUM(TENANT, PLAN, SUBSCRIPTION, INVOICE, ADMIN_ACCOUNT, SETTINGS) |
| **target_id** | UUID — ID of the affected record (nullable if target is settings) |
| **target_name** | VARCHAR(255) — denormalized target name for display (gym name, owner email, plan name) |
| **old_value** | JSONB — full record before change. Null for CREATE. |
| **new_value** | JSONB — full record after change. Null for DELETE. |
| **difference** | JSONB — only the fields that changed. Easier to read than old_value vs new_value. |
| **user_agent** | TEXT — browser/client string |
| **created_at** | TIMESTAMPTZ DEFAULT NOW() — UTC. Displayed in IST on UI. |

### 4.2 Immutability Enforcement

> **Database:** The app service role has NO UPDATE or DELETE grants on audit log. Only INSERT is allowed.
> ```sql
> GRANT INSERT ON admin_audit_log TO app_role;
> REVOKE UPDATE, DELETE ON admin_audit_log FROM app_role;
> ```
>
> **Correction:** If an audit entry needs correction (very rare), DO NOT edit it. Write a new entry with `action_type='AUDIT_CORRECTION'`, referencing the original entry ID in `new_value`.
>
> **Retention:** Minimum 5 years. Archive to S3 after 12 months (daily snapshots). Keep 7 years of backups (compliance with Indian law). Scheduled job: monthly export to `s3://gymos-backups/audit-logs/YYYY-MM.jsonl.gz`.

### 4.3 Audit Events to Log in Phase 1

- **TENANT_CREATE** — admin creates new tenant. `old_value=null`, `new_value=full tenant record`.
- **TENANT_SUSPEND** — admin suspends tenant. `difference={'status': {'old': 'active', 'new': 'suspended'}, 'suspension_reason': 'Non-payment'}`.
- **TENANT_ACTIVATE** — admin reactivates suspended tenant.
- **TENANT_ARCHIVE** — admin archives tenant.
- **PLAN_ASSIGN** — admin assigns plan to tenant. `new_value={'tenant_id': ..., 'plan_id': ..., 'start_date': ..., 'trial_end_date': ...}`.
- **SUBSCRIPTION_CANCEL** — admin cancels subscription.
- **PAYMENT_RECORD** — admin records manual payment. `new_value={'tenant_id': ..., 'amount': ..., 'method': ...}`.
- **ADMIN_LOGIN** — admin logs in successfully. `new_value={'login_timestamp': ..., 'ip': ..., 'device': ...}`.
- **IMPERSONATE_START** — admin starts impersonating gym owner.
- **IMPERSONATE_END** — admin ends impersonation (timeout or manual).

---

## 5. Transactional Email Templates — Phase 1

All emails sent from `noreply@gymos.in`. Plain HTML with inline CSS. Tested with mail-tester.com (target: 9.5+ score). DKIM and SPF configured for sending domain.

> **Variables:** `{{gym_name}}`, `{{owner_name}}`, `{{owner_email}}`, `{{subdomain}}`, `{{temp_password}}`, `{{plan_name}}`, `{{plan_price}}`, `{{trial_end_date}}`, `{{payment_amount}}`, `{{invoice_number}}`, `{{admin_name}}`, `{{suspension_reason}}`, `{{city}}`, `{{device}}`, `{{ip_address}}`
>
> **Date/time:** All dates displayed in IST (Asia/Kolkata, UTC+5:30). Format: 'June 3, 2026, 10:30 AM IST'.
>
> **Amounts:** All INR amounts formatted with comma separators. Example: Rs. 2,999.00 (not Rs. 2999).
>
> **Every email:** Must include footer with unsubscribe link, address (1234 Tech Park, Chennai, Tamil Nadu 600001), support email, GymOS logo.

### 5.1 New Tenant Welcome Email

Triggered: Immediately after Step 4 of Add Tenant wizard submitted successfully. Recipient: Gym owner email. Delivery: Within 10 seconds.

**Subject:** `Welcome to GymOS — Your gym portal is ready`

```
Hi {{owner_name}},

Your gym management portal for {{gym_name}} is now ready to use.

YOUR LOGIN CREDENTIALS
Portal: https://{{subdomain}}.gymos.in
Email: {{owner_email}}
Temporary password: {{temp_password}}
You will be asked to change your password on first login.

YOUR SUBSCRIPTION
Plan: {{plan_name}}
Billing: {{plan_price}} per month
Trial period: {{trial_end_date}}  [shown only if trial]

GETTING STARTED
1. Log in and change your password
2. Complete your gym profile
3. Add your first staff member
4. Register your first member

Need help? Reply to this email or email support@gymos.in.

Welcome aboard!
{{admin_name}}
GymOS Platform
```

### 5.2 Admin Login Alert

Triggered: Every successful login by super admin (after 2FA verified). Sent to admin's own email.

**Subject:** `New login to your GymOS Admin account`

```
Hi {{admin_name}},

A new login to your admin account was detected.

LOGIN DETAILS
Time: {{login_timestamp}} IST
IP: {{ip_address}}
Location: {{city}}, {{country}}
Device: {{device}}

If this was you, no action needed.

If NOT, immediately:
1. Go to Settings > Active sessions and terminate all other sessions
2. Change your password
3. Contact your platform engineer — account may be compromised

GymOS Security
```

### 5.3 Tenant Suspended Notification

Triggered: When super admin suspends a gym tenant. Sent to the gym owner.

**Subject:** `Your GymOS portal has been suspended`

```
Hi {{owner_name}},

Your GymOS portal for {{gym_name}} has been suspended effective immediately.

SUSPENSION DETAILS
Date: {{suspension_date}}
Reason: {{suspension_reason}}

During suspension:
- You cannot log in
- Members cannot check in
- Your data is safe and retained

To resolve, contact support@gymos.in or reply to this email.
Response time: within 4 business hours.

GymOS Support
support@gymos.in
```

### 5.4 Tenant Restored Notification

Triggered: When super admin reactivates a previously suspended tenant.

**Subject:** `Your GymOS portal is now active`

```
Hi {{owner_name}},

Your GymOS portal for {{gym_name}} has been restored and is now active.

ACCESS RESTORED
Portal: https://{{subdomain}}.gymos.in
Restored at: {{restore_timestamp}} IST

Your data is intact. You and your staff can log in normally.

Questions? Contact support@gymos.in.

GymOS Support
```

### 5.5 Plan Assigned Notification

Triggered: When a plan is assigned to a tenant for the first time or changed.

**Subject:** `Your subscription is confirmed — {{plan_name}} plan`

```
Hi {{owner_name}},

Your {{gym_name}} subscription is confirmed.

SUBSCRIPTION DETAILS
Plan: {{plan_name}}
Amount: Rs. {{plan_price}} per month
Start date: {{start_date}}
Next renewal: {{renewal_date}}
Trial ends: {{trial_end_date}}  [if applicable]

WHAT'S INCLUDED
{{plan_features_list}}

Invoice #{{invoice_number}} is attached.
Manage your subscription in your portal: Settings > Subscription.

GymOS Billing
billing@gymos.in
```

### 5.6 Trial Expiry Reminder (3 days before)

Triggered: Automated job daily at 9:00 AM IST. Query: `SELECT * FROM subscriptions WHERE trial_end_date = CURRENT_DATE + 3 AND status='trial'`.

**Subject:** `Your GymOS free trial ends in 3 days`

```
Hi {{owner_name}},

Your free trial of GymOS for {{gym_name}} ends on {{trial_end_date}} (3 days away).

After this date, your portal will be paused unless you activate a paid subscription.

ACTIVATE NOW
Plan: {{plan_name}}
Cost: Rs. {{plan_price}} per month
Log in to activate: https://{{subdomain}}.gymos.in/settings/subscription

Or reply to this email — we'll set it up for you.
Need a different plan? Check options on our website.

GymOS Team
support@gymos.in
```

### 5.7 Payment Confirmation / Invoice Email

Triggered: When a manual payment is recorded by the super admin.

**Subject:** `Payment received — Invoice #{{invoice_number}}`

```
Hi {{owner_name}},

We've received your payment. Thank you!

PAYMENT SUMMARY
Invoice: #{{invoice_number}}
Date: {{payment_date}}
Amount (before tax): Rs. {{amount_before_tax}}
GST (18%): Rs. {{gst_amount}}
Total: Rs. {{total_amount}}
Method: {{payment_method}}
Reference: {{transaction_ref}}
Active until: {{subscription_end_date}}

GST invoice PDF is attached.
GymOS GSTIN: {{gymos_gstin}}
HSN/SAC: 998314 (IT Services)

For billing questions: billing@gymos.in

GymOS Billing
```

### 5.8 Password Reset Link Email

Triggered: When admin requests password reset via forgot-password flow.

**Subject:** `Reset your GymOS admin password`

```
Hi {{admin_name}},

We received a request to reset the password for your GymOS admin account.

Click the link below to reset your password. Link expires in 1 hour.

Reset password: {{password_reset_link}}

If you didn't request this, ignore this email. Your password has not been changed.

GymOS Security
```

---

## 6. Complete Database Schema — Phase 1

All Phase 1 tables live in the platform schema (public schema in PostgreSQL). Each gym tenant gets an isolated schema (`tenant_{uuid}`) created at onboarding time. The platform schema is never accessible to gym-owner-level application code.

### 6.1 Table: admin_accounts

| Column | Type / Notes |
|---|---|
| **id** | UUID PK default `gen_random_uuid()` |
| **name** | VARCHAR(100) NOT NULL |
| **email** | VARCHAR(255) UNIQUE NOT NULL (stored lowercase) |
| **password_hash** | VARCHAR(60) — bcrypt cost 12 |
| **totp_secret** | BYTEA — AES-256 encrypted TOTP secret |
| **totp_enabled** | BOOLEAN DEFAULT false |
| **recovery_codes** | JSONB ARRAY — array of bcrypt-hashed recovery codes |
| **ip_whitelist** | JSONB ARRAY — CIDR strings, empty array = all IPs allowed |
| **failed_attempts** | INTEGER DEFAULT 0 — incremented on login fail |
| **locked_until** | TIMESTAMPTZ — null if unlocked |
| **role** | VARCHAR(50) DEFAULT 'super_admin' — future: 'support', 'finance' |
| **status** | VARCHAR(50) DEFAULT 'active' — 'active', 'suspended' |
| **created_at** | TIMESTAMPTZ DEFAULT NOW() |
| **updated_at** | TIMESTAMPTZ DEFAULT NOW() |
| **last_login_at** | TIMESTAMPTZ — null initially |

### 6.2 Table: tenants

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **name** | VARCHAR(120) NOT NULL |
| **business_type** | VARCHAR(20) — 'gym', 'yoga', 'turf' |
| **subdomain** | VARCHAR(30) UNIQUE NOT NULL — lowercase `[a-z0-9-]+` |
| **city** | VARCHAR(80) |
| **state** | VARCHAR(80) |
| **address** | TEXT |
| **phone** | VARCHAR(20) — E.164 format |
| **gst_number** | VARCHAR(15) — regex validated, nullable |
| **status** | VARCHAR(20) — 'trial', 'active', 'suspended', 'archived' |
| **schema_name** | VARCHAR(80) UNIQUE — 'tenant_' + uuid |
| **default_language** | VARCHAR(10) FK to `platform_languages(locale_code)` DEFAULT 'en-IN' |
| **created_by** | UUID FK to `admin_accounts.id` |
| **created_at** | TIMESTAMPTZ DEFAULT NOW() |
| **archived_at** | TIMESTAMPTZ — null unless archived |
| **notes** | TEXT — admin-only notes |

### 6.3 Table: tenant_owners

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **tenant_id** | UUID FK to `tenants.id` — one owner per tenant (Phase 1) |
| **name** | VARCHAR(100) |
| **email** | VARCHAR(255) UNIQUE NOT NULL |
| **password_hash** | VARCHAR(60) — bcrypt |
| **phone** | VARCHAR(20) |
| **preferred_language** | VARCHAR(10) FK to `platform_languages(locale_code)` DEFAULT 'en-IN' |
| **must_change_password** | BOOLEAN DEFAULT true — forced on first login |
| **status** | VARCHAR(50) DEFAULT 'active' |
| **created_at** | TIMESTAMPTZ |
| **last_login_at** | TIMESTAMPTZ |

### 6.4 Table: plans

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **name** | VARCHAR(80) NOT NULL — 'Starter', 'Growth', 'Enterprise' |
| **billing_cycle** | VARCHAR(20) — 'monthly', 'quarterly', 'annual' |
| **price_paise** | INTEGER NOT NULL — store in paise (100 paise = 1 INR) |
| **max_members** | INTEGER DEFAULT 0 — 0=unlimited |
| **max_branches** | INTEGER DEFAULT 1 |
| **max_staff** | INTEGER DEFAULT 5 |
| **feature_flags** | JSONB — `{'pos': true, 'analytics': false, ...}` |
| **trial_eligible** | BOOLEAN DEFAULT true |
| **description** | TEXT — plan description for gym owners |
| **status** | VARCHAR(50) DEFAULT 'active' — 'active', 'archived' |
| **created_at** | TIMESTAMPTZ |
| **updated_at** | TIMESTAMPTZ |

### 6.5 Table: subscriptions

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **tenant_id** | UUID FK to `tenants.id` |
| **plan_id** | UUID FK to `plans.id` |
| **status** | VARCHAR(50) — 'trial', 'active', 'expired', 'cancelled' |
| **start_date** | DATE NOT NULL |
| **end_date** | DATE — null if ongoing |
| **trial_end_date** | DATE — null if no trial |
| **assigned_by** | UUID FK to `admin_accounts.id` |
| **created_at** | TIMESTAMPTZ |
| **cancelled_at** | TIMESTAMPTZ |
| **cancellation_reason** | TEXT |

### 6.6 Table: admin_audit_log (immutable)

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **actor_admin_id** | UUID FK to `admin_accounts.id` |
| **actor_name** | VARCHAR(100) |
| **actor_ip** | VARCHAR(45) — IPv4 or IPv6 |
| **action_type** | VARCHAR(50) — ENUM of actions (see Section 4.3) |
| **target_type** | VARCHAR(50) — 'tenant', 'plan', 'subscription', 'payment', 'admin_account' |
| **target_id** | UUID — nullable for some actions |
| **target_name** | VARCHAR(255) — denormalized for display |
| **old_value** | JSONB — previous state |
| **new_value** | JSONB — new state |
| **difference** | JSONB — only changed fields |
| **user_agent** | TEXT |
| **created_at** | TIMESTAMPTZ DEFAULT NOW() |
| **CONSTRAINT** | NO UPDATE, DELETE permissions at app level — INSERT only |

### 6.7 Table: tenant_payments

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **tenant_id** | UUID — denormalized or FK |
| **subscription_id** | UUID FK to `subscriptions.id` |
| **amount_paise** | INTEGER — payment amount in paise |
| **payment_method** | VARCHAR(50) — 'cash', 'bank_transfer', 'upi', 'cheque', 'razorpay' |
| **transaction_ref** | VARCHAR(100) — reference number |
| **invoice_number** | VARCHAR(50) — generated invoice number |
| **status** | VARCHAR(50) — 'pending', 'success', 'failed' |
| **created_by** | UUID FK to `admin_accounts.id` |
| **created_at** | TIMESTAMPTZ |

### 6.8 Table: admin_sessions

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **admin_id** | UUID FK to `admin_accounts.id` |
| **ip_address** | VARCHAR(45) |
| **user_agent** | TEXT |
| **device_name** | VARCHAR(200) — parsed from user_agent |
| **location_city** | VARCHAR(100) — from MaxMind IP geo |
| **location_country** | VARCHAR(100) |
| **created_at** | TIMESTAMPTZ |
| **last_active_at** | TIMESTAMPTZ |
| **expires_at** | TIMESTAMPTZ — refresh token expiry |
| **impersonating_tenant_id** | UUID — null unless impersonating |
| **impersonation_started_at** | TIMESTAMPTZ |

### 6.9 Table: admin_password_resets

| Column | Type / Notes |
|---|---|
| **id** | UUID PK |
| **admin_id** | UUID FK to `admin_accounts.id` |
| **token_hash** | VARCHAR(255) — bcrypt hash of reset token |
| **jti** | VARCHAR(36) — JWT jti (one-time use) |
| **used** | BOOLEAN DEFAULT false — mark used after reset |
| **created_at** | TIMESTAMPTZ |
| **expires_at** | TIMESTAMPTZ — 1 hour TTL |

### 6.10 Table: platform_languages

| Column | Type / Notes |
|---|---|
| **locale_code** | VARCHAR(10) PRIMARY KEY — e.g. 'hi-IN' |
| **display_name** | VARCHAR(50) NOT NULL — e.g. 'हिन्दी' |
| **is_active** | BOOLEAN NOT NULL DEFAULT false |
| **completeness_pct** | SMALLINT DEFAULT 0 — updated by CI pipeline |
| **is_rtl** | BOOLEAN NOT NULL DEFAULT false |

---

## 7. REST API Specification — Phase 1

All endpoints prefixed with `/api/admin/v1`. All requests include `X-CSRF-Token` header (except GET). All responses use JSON envelope: `{success: true/false, data: {...}, error: {...}}`.

### 7.1 Authentication Endpoints

```
POST /auth/login
  Request:  {email, password}
  Response: {access_token, requires_2fa: false} OR {requires_2fa: true, temp_token}

POST /auth/2fa/verify
  Request:  {temp_token, totp_code}
  Response: {access_token, refresh_token}

POST /auth/logout
  Response: {success: true}

POST /auth/refresh
  Response: {access_token}

POST /auth/password-reset
  Request:  {email}
  Response: {success: true, message: 'Check your email'}

POST /auth/password-reset/:token
  Request:  {new_password}
  Response: {success: true}
```

### 7.2 Tenant Management Endpoints

```
GET /tenants
  Query:    {page, limit, search, status, plan_id, city, sort_by, sort_dir}
  Response: {tenants: [...], total, page, limit}

POST /tenants
  Request:  {name, business_type, subdomain, city, address, gst_number, phone,
             owner_name, owner_email, plan_id, start_date, trial_end_date, trial: bool}
  Response: {tenant_id, schema_name, owner_id, subscription_id}

GET /tenants/:id
  Response: {tenant, owner, current_subscription, subscription_history, member_count, notes}

PUT /tenants/:id
  Request:  {name, city, address, phone, gst_number, notes}
  Response: {tenant}

POST /tenants/:id/suspend
  Request:  {reason, note}
  Response: {success: true}

POST /tenants/:id/activate
  Response: {success: true}

POST /tenants/:id/archive
  Request:  {confirmation_gym_name}
  Response: {success: true}

POST /tenants/:id/impersonate
  Response: {impersonate_token, session_id, expires_at}
```

### 7.3 Plans Endpoints

```
GET /plans
  Response: {plans: [{id, name, billing_cycle, price_paise, status, feature_flags, ...}]}

POST /plans
  Request:  {name, billing_cycle, price_paise, max_members, max_branches,
             max_staff, feature_flags, trial_eligible, description, status}
  Response: {plan_id}

PUT /plans/:id
  Response: {plan}
```

### 7.4 Subscriptions Endpoints

```
POST /subscriptions
  Request:  {tenant_id, plan_id, start_date, trial: bool, trial_end_date}
  Response: {subscription_id}

GET /subscriptions/:id
  Response: {subscription, plan, tenant, next_renewal_date}

PUT /subscriptions/:id
  Request:  {new_plan_id, effective_date}
  Response: {subscription, proration_credit}

POST /subscriptions/:id/cancel
  Request:  {cancellation_reason, end_date}
  Response: {success: true}
```

### 7.5 Payments & Invoices Endpoints

```
POST /payments
  Request:  {tenant_id, subscription_id, amount_paise, payment_method,
             transaction_ref, payment_date}
  Response: {payment_id, invoice_number, invoice_url}

GET /invoices
  Query:    {tenant_id, limit, page}
  Response: {invoices: [...], total}

GET /invoices/:id/pdf
  Response: PDF file (Content-Type: application/pdf)
```

### 7.6 Audit Log Endpoints

```
GET /audit-log
  Query:    {action_type, target_type, tenant_id, limit, page, from_date, to_date}
  Response: {entries: [...], total}
```

### 7.7 Session Management Endpoints

```
GET /sessions
  Response: {sessions: [{id, ip, device, location, created_at, last_active, is_current}]}

POST /sessions/:id/terminate
  Response: {success: true}

POST /sessions/terminate-others
  Response: {terminated_count}
```

### 7.8 Settings Endpoints

```
GET  /settings/admin/profile
  Response: {name, email, totp_enabled}

PUT  /settings/admin/profile
  Request:  {name}
  Response: {admin}

POST /settings/admin/password
  Request:  {old_password, new_password, new_password_confirm}

POST /settings/security/2fa/setup
  Response: {qr_code_data_uri, manual_entry_key, recovery_codes}

POST /settings/security/2fa/regenerate-codes
  Response: {recovery_codes}

PUT  /settings/security/ip-whitelist
  Request:  {ips: ['192.168.1.0/24', '10.0.0.5']}

GET  /settings/languages
  Response: [{locale_code, display_name, is_active, completeness_pct, is_rtl}]

PATCH /settings/languages/{locale_code}
  Request:  {is_active: true}
  Response: {locale_code, is_active}

GET  /settings/languages/{locale_code}/missing-keys
  Response: [{namespace, key, en_value}]
```

### 7.9 Dashboard Endpoint

```
GET /dashboard
  Response: {total_tenants, active_tenants, trials_active, mrr,
             renewals_this_week, trials_expiring_7d, mrr_chart_data,
             recent_activity, renewals_due_table}
```

### 7.10 Language Endpoints

```
PUT /tenants/{tenantId}/language
  Request:  {locale_code}
  Response: {tenant_id, default_language}

PUT /profile/language
  Request:  {locale_code}
  Response: {preferred_language}
  Auth: tenant-owner session
```

---

## 8. Data Validation Rules

### 8.1 Email Validation

- Format: RFC 5322 strict. Reject: spaces, consecutive dots, missing @, etc.
- Max length: 254 characters (RFC standard)
- Domain: Reject throwaway email domains (mailinator.com, 10minutemail.com, etc.) — maintain blocklist
- Case-insensitive: Store as lowercase. Compare case-insensitive.
- Uniqueness: Check `tenant_owners.email` UNIQUE constraint. Error if exists.

### 8.2 Password Validation

- Min 12 characters (non-negotiable)
- Must contain: 1 uppercase, 1 lowercase, 1 number, 1 symbol (`!@#$%^&*`)
- Max 128 characters
- Reject common passwords (from rockyou wordlist top 10k)
- Backend validation is the enforcer; frontend validation is for UX only

### 8.3 Subdomain Validation

- Length: 3–20 characters
- Format: `[a-z0-9]+` with hyphens allowed (not at start/end)
- Forced lowercase
- Reserved words blocked: admin, api, www, app, mail, support, help, login, dashboard, gymos, auth, noreply
- Uniqueness: UNIQUE constraint at DB. Real-time check via API during wizard.

### 8.4 Phone Validation

- Format: E.164 only. Examples: `+918765432109`, `+14155552368`
- Validate using `libphonenumber-js` library
- Store without formatting (`+918765432109` exactly)

### 8.5 GST Number Validation

- Format: 15 alphanumeric characters. Regex: `^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}[Z]{1}[0-9]{1}$`
- Example: `07AADCR5055K1Z5`
- Optional field, but if provided must be valid

### 8.6 Amount (Price) Validation

- Stored in paise (integer). Convert: INR × 100 = paise
- Min: 0 paise. Max: 999999.99 INR (999999900 paise)
- Display: always with 2 decimals (`Rs.2,999.00`)

### 8.7 Date Range Validation

- Trial end date: must be >= today + 7 days (min trial duration)
- Trial end date: must be <= today + 30 days (max trial duration)
- Subscription start date: can be today or any future date
- End date must be >= start date (enforced at DB level)

### 8.8 TOTP Code Validation

- Format: 6-digit numeric code entered by user
- Window: +/-1 time step (30 seconds each), so codes valid for up to 90 sec
- Max attempts: 3 per 2FA session. Lock after 3 fails.

### 8.9 Recovery Code Validation

- Format: 8-character alphanumeric codes (case-insensitive input, uppercase stored)
- Single-use: once used, deleted from `recovery_codes` array
- Stored hashed: bcrypt comparison required

---

## 9. Error Handling & Edge Cases

### 9.1 HTTP Status Codes

| Status | Meaning & when to use |
|---|---|
| **200 OK** | Request succeeded. Return data in response body. |
| **201 Created** | Tenant/plan/payment created. Include resource ID in response. |
| **400 Bad Request** | Invalid input (email format, missing field). Return `{error: {code, message, field_errors}}`. |
| **401 Unauthorized** | Not authenticated (no token, expired token). Redirect to `/auth/login`. |
| **403 Forbidden** | Authenticated but lacks permission. IP blocked, account locked, or insufficient role. |
| **404 Not Found** | Resource not found (wrong tenant ID, plan not found). |
| **409 Conflict** | Resource already exists (duplicate email, subdomain taken). |
| **429 Too Many Requests** | Rate limited. Return `Retry-After` header in response. |
| **500 Internal Server Error** | Unhandled exception. Log to Sentry. Return generic 'Something went wrong' to client. |
| **503 Service Unavailable** | Maintenance or database down. Return message 'Service temporarily unavailable'. |

### 9.2 Standard Error Response Format

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed. See field_errors.",
    "field_errors": {
      "email": "Invalid email format",
      "subdomain": "Subdomain already taken"
    }
  }
}
```

### 9.3 Edge Case Handling

- **Duplicate subdomain during creation:** Real-time check. Show 'Subdomain unavailable' before form submit.
- **Duplicate email:** Show 'Email already registered'. Suggest password reset if they forgot their credentials.
- **Network timeout on email send:** Log to Sentry. Tenant created successfully (don't fail). Queue email for retry (5 retries, exponential backoff).
- **DB connection loss:** Return 503. Alert ops team via PagerDuty. Force user to try again.
- **Concurrent tenant creation (same name):** Last write wins. Alert user: 'Tenant already exists under this name'.
- **Plan assigned to tenant, then plan archived:** Tenant keeps the plan. Only new tenants prevented from selecting archived plans.
- **Impersonation session timeout (after 60 min):** Session auto-terminates. Admin gets 'Session expired' error on next action. Audit log entry written.

---

## 10. Security Threat Model & Mitigations

### 10.1 Threat: Brute Force Admin Login

| | |
|---|---|
| **Threat** | Attacker attempts thousands of password guesses |
| **Mitigation** | Rate limit: 5 attempts per 15 min per IP. Lock account 30 min after 5 fails. Send admin alert email on lockout. IP geolocation email on suspicious login. |

### 10.2 Threat: Credential Stuffing

| | |
|---|---|
| **Threat** | Admin password breached elsewhere (e.g. LinkedIn). Attacker tries it on GymOS. |
| **Mitigation** | 2FA mandatory — even with correct password, account protected. TOTP code only admin knows. |

### 10.3 Threat: Session Hijacking / Cookie Theft

| | |
|---|---|
| **Threat** | Attacker steals refresh token cookie, uses it without 2FA. |
| **Mitigation** | Cookies: `httpOnly=true`, `Secure=true`, `SameSite=Strict`. IP binding: token issued at IP X can only be used from IP X. Server validates IP on every API call. |

### 10.4 Threat: XSS (Cross-Site Scripting)

| | |
|---|---|
| **Threat** | Attacker injects `<script>` into form input. When displayed, script runs and steals access token. |
| **Mitigation** | All user input sanitized with DOMPurify on frontend. Backend escapes all output when rendering HTML. CSP header blocks inline scripts. JSON API responses not rendered as raw HTML. |

### 10.5 Threat: SQL Injection

| | |
|---|---|
| **Threat** | Attacker injects SQL via form fields: `test@example.com' OR 1=1 --` |
| **Mitigation** | Parameterized queries only. ORM (Prisma) prevents SQL injection. No string concatenation for SQL. Raw SQL rare and code-reviewed. |

### 10.6 Threat: CSRF (Cross-Site Request Forgery)

| | |
|---|---|
| **Threat** | Attacker tricks admin into visiting malicious site that POSTs to `/api/admin/tenants/delete`. Browser auto-includes cookies. |
| **Mitigation** | `X-CSRF-Token` header required for all POST/PUT/DELETE. Token stored in non-httpOnly cookie, sent in header. Attacker cannot read header token from their domain. |

### 10.7 Threat: Impersonation Abuse

| | |
|---|---|
| **Threat** | Super admin impersonates gym owner, makes malicious changes, claims it was the gym owner. |
| **Mitigation** | Impersonation limited to 60 minutes. Audit log ALWAYS shows 'Impersonated by [admin_name]' on all actions. Gym owner receives alert email if unusual activity found. |

### 10.8 Threat: Account Takeover via Email

| | |
|---|---|
| **Threat** | Attacker gains access to admin's email, resets password via forgot-password link. |
| **Mitigation** | Password reset links single-use (`jti` marked used). After reset, all sessions invalidated. Email notification sent: *'Your password was reset at [time]. If not you, contact us immediately.'* |

### 10.9 Threat: Recovery Codes Stolen

| | |
|---|---|
| **Threat** | Attacker finds recovery codes written on admin's desk. |
| **Mitigation** | Recovery codes single-use. Once used, automatically deleted. After use, admin is prompted to regenerate all 8 codes. |

### 10.10 Threat: Audit Log Manipulation

| | |
|---|---|
| **Threat** | Attacker with DB access modifies audit log to hide their actions. |
| **Mitigation** | Audit log table has NO UPDATE/DELETE permissions at app level. Backups immutable on S3. Even platform DB superuser cannot delete individual entries without leaving a trail. |

> **Security testing required before go-live:**
> 1. OWASP ZAP scan on `/admin/*` endpoints
> 2. Penetration test by external firm (budget: Rs. 3–5 lakh)
> 3. Code review of auth + crypto logic by security-experienced developer
> 4. Dependency scan: `npm audit` for known vulnerabilities
> 5. Secrets scanning: no API keys or passwords in repo (use `.env` files and AWS Secrets Manager)

---

## 11. Testing Strategy

### 11.1 Unit Tests (backend)

- Email validation: valid emails pass, invalid emails fail, throwaway domains rejected
- Password hashing: bcrypt works, timing-safe comparison used
- TOTP verification: correct codes pass, wrong codes fail, time window respected
- Audit log write: ensure all fields populated, immutability tested
- Plan price calculation: paise conversion correct (2999 INR = 299900 paise)
- Date validation: trial end date rules enforced
- Subdomain validation: reserved words blocked, uniqueness checked
- IP CIDR validation: ranges parsed correctly

### 11.2 Integration Tests

- Full tenant creation flow: all 4 wizard steps, tenant schema created, owner account created, welcome email sent
- Login flow: email → password → 2FA → session issued
- Payment recording: payment created → invoice PDF generated → email sent → audit log written
- Plan assignment: subscription record created → feature flags set → gym owner notified
- Account suspension: tenant status updated → all sessions invalidated → email sent → audit log entry
- Impersonation termination: 60 min timeout, session deleted, audit entry logged

### 11.3 End-to-End (E2E) Tests

- **Scenario 1:** Admin onboards first gym tenant from scratch (all 4 wizard steps)
- **Scenario 2:** Admin assigns plan, tenant receives welcome + plan email
- **Scenario 3:** Admin suspends tenant, tenant cannot log in, receives suspension email
- **Scenario 4:** Admin impersonates tenant, makes edits, impersonation logged
- **Scenario 5:** Email templates render correctly on Gmail, Outlook, mobile

### 11.4 Load Testing

- 10 concurrent admin sessions, all accessing tenants list (pagination, search, filters)
- Expected: < 500ms response time, < 50% CPU, no memory leaks
- Tools: Apache JMeter or k6
- Sustained load: 1 min baseline, 2 min ramp up, 5 min sustained, 2 min ramp down

### 11.5 Security Testing

- OWASP ZAP scan: automated vulnerability detection on all `/admin/*` endpoints
- Penetration test: manual testing by security expert
- SQL injection: attempt various payloads in all text fields
- XSS: inject `<script>` tags in gym name, verify sanitization
- CSRF: attempt cross-domain POST without `X-CSRF-Token`
- Broken auth: attempt to access `/admin/*` endpoints without token

### 11.6 Email Testing

- All 8 email templates rendered in Gmail, Outlook, Apple Mail, mobile browsers
- Links clickable and navigate to correct URLs
- PDF invoices open and render correctly
- Spam score: test with mail-tester.com, target ≥ 9.5/10

### 11.7 Database Testing

- Audit log immutability: verify UPDATE/DELETE denied by DB
- Foreign key constraints: cascade deletes work as designed
- Unique constraints: duplicates rejected (subdomain, email)
- Index performance: query plans show index usage, no full table scans
- Backup/restore: daily backup succeeds, restore on separate DB instance works

### 11.8 Test Coverage Targets

- Backend code: ≥ 80% unit + integration test coverage
- Critical paths (auth, tenant creation, payment): 100% coverage
- Frontend code: ≥ 60% unit test coverage for validation, error handling
- E2E: 5 core user journeys fully tested

---

## 12. Deployment, Monitoring & Rollback

### 12.1 Deployment Pipeline

1. Developer pushes code to GitHub main branch
2. CI/CD (GitHub Actions) runs: lint, unit tests, integration tests, security scan
3. On pass: build Docker image, push to ECR
4. Deploy to staging: image pulled, migrations run, smoke tests run
5. Staging sign-off by QA / product team (manual step)
6. Merge to production: image pulled to prod ECS cluster, zero-downtime deployment (blue-green)
7. Health checks: `/health` endpoint returns 200 and `db_connected=true`
8. Post-deploy: run smoke tests again

### 12.2 Database Migrations

- All migrations versioned: `V001__initial_schema.sql`, `V002__add_admin_audit_log.sql`, etc.
- Tool: Flyway (auto-runs on app startup)
- Manual review before applying to production
- Always have a rollb