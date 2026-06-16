# GymOS — Gym Owner Portal
# Module 04: Attendance

**URL:** `/attendance`
**Sub-pages:** `/attendance/checkins`, `/attendance/walkins`
**Access:** Gym owner (full), Branch manager (own branch), Receptionist (own branch — add only)
**Purpose:** Track member check-ins by date and method, and log walk-in visitors (non-members).

---

## 1. Check-in Log Layout

```
┌────────────────────────────────────────────────────────┐
│  Date: [Jun 12, 2026 ▾]    Branch: [All ▾]             │
│  Total today: 87  │  Unique: 72  │  Peak: 7–8 AM (24)  │
├────────────────────────────────────────────────────────┤
│  🔍 Search member...   Method ▾   [+ Manual check-in]  │
├────────────────────────────────────────────────────────┤
│  Check-in table (real-time, newest first)              │
└────────────────────────────────────────────────────────┘
```

---

## 2. Check-in Table Columns

| Column | Notes |
|---|---|
| **Time** | HH:MM format |
| **Member name + ID** | Clickable to member profile |
| **Plan** | Current plan name |
| **Branch** | Branch where checked in |
| **Method** | Badge: QR / Biometric / Manual |
| **Check-out** | Time if recorded. "—" if still open. "Auto" if midnight auto-checkout. |
| **Duration** | Calculated if checked out. "Active" if still in. |
| **Actions** | Manual check-out (if open); Delete (owner only) |

Default sort: most recent first. Real-time updates via WebSocket or 30-second polling.

---

## 3. Check-in Methods

| Method | How it works | DB `source` value |
|---|---|---|
| **QR code** | Member scans personal QR at entrance kiosk or receptionist device | `qr` |
| **Biometric** | Fingerprint on integrated device (if configured in Settings > Integrations) | `biometric` |
| **Manual** | Receptionist searches member and checks them in | `manual` |

All check-ins record: `member_id`, `branch_id`, `method`, `checked_in_at`, `checked_in_by` (staff_id, null for QR/biometric).

---

## 4. Manual Check-in Flow

1. Staff clicks **+ Manual check-in**
2. Search member by name / phone / member ID
3. Member card shown: name, photo, plan, expiry date, current status
4. If membership expired and within grace period: allow with orange warning banner
5. If past grace period: block check-in with red error message
6. Optional reason field: Late QR scan / Device issue / Guest pass / Other
7. Confirm → check-in logged with `method=manual`, `checked_in_by=staff_id`

---

## 5. Check-out Recording

- Check-out is **optional** by default (configurable in Settings > Gym profile)
- If mandatory: member must scan out; access control system enforces at gate
- Duration = `checked_out_at − checked_in_at`
- **Auto-checkout:** Scheduled job at 23:59 IST sets `checked_out_at=23:59`, `is_auto_checkout=true` for all open check-ins from that day

---

## 6. Walk-ins Page (`/attendance/walkins`)

Walk-ins are non-members visiting for a day pass, trial, or inquiry.

**Table columns:** Time | Name | Phone | Purpose | Fee collected | Method | Staff | Notes

**Add walk-in form:**

| Field | Required | Validation |
|---|---|---|
| Name | Yes | 2–100 chars |
| Phone | Yes | E.164 format |
| Purpose | Yes | Day pass / Free trial / Inquiry / Guest of member |
| Fee collected | No | INR; 0 if free |
| Payment method | If fee > 0 | Cash / UPI / Card |
| Reference | No | For UPI/card |
| Guest of (member ID) | No | Link to existing member |
| Notes | No | Max 300 chars |

Walk-in fee creates a `payments` record not linked to any subscription.

---

## 7. Attendance Stats Panel

Available as a collapsible panel above the table:

| Stat | Chart type |
|---|---|
| Check-ins by hour (selected date) | Bar chart (00:00–23:00) |
| Check-ins by day (selected week) | Line chart |
| Peak hours | Top 3 as badges e.g. "7–8 AM: 24 check-ins" |
| Today vs same day last week | % change KPI |
| Rolling 30-day average | KPI card |

---

## 8. Filters and Search

| Filter | Options |
|---|---|
| **Date** | Single day picker (default today) or date range |
| **Branch** | All / specific branch |
| **Method** | All / QR / Biometric / Manual |
| **Search** | Member name or member ID (real-time, 300ms debounce) |

---

## 9. Export CSV

- Columns: Date, Time, Member name, Member ID, Plan, Branch, Method, Check-out time, Duration, Logged by
- Active filters apply to export
- Filename: `attendance_{branch}_{date_range}.csv`

---

## 10. API Endpoints

```
GET /api/v1/attendance/checkins
  Query: date, from, to, branch_id, member_id, method, page, limit
  Response: { checkins: [...], total,
              stats: { total_today, unique_members, peak_hour } }

POST /api/v1/attendance/checkins
  Body: { member_id, branch_id, method, reason? }
  Response 201: { checkin_id }
  Error 409: ALREADY_CHECKED_IN
  Error 403: MEMBERSHIP_EXPIRED_BEYOND_GRACE

POST /api/v1/attendance/checkins/:id/checkout
  Body: { checked_out_at? }  -- defaults to NOW()
  Response 200: { duration_minutes }

GET /api/v1/attendance/walkins
  Query: date, from, to, branch_id, page, limit
  Response: { walkins: [...], total }

POST /api/v1/attendance/walkins
  Body: { name, phone, purpose, fee_paise?, payment_method?,
          reference?, notes?, guest_of_id? }
  Response 201: { walkin_id }

GET /api/v1/attendance/stats
  Query: date, branch_id
  Response: { by_hour: [...], peak_hours: [...],
              total, unique_members, avg_daily_30d }
```

---

## 11. Database Schema

```sql
CREATE TABLE attendance_logs (
  id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  member_id         UUID NOT NULL REFERENCES members(id),
  tenant_id         UUID NOT NULL REFERENCES tenants(id),
  branch_id         UUID NOT NULL REFERENCES branches(id),
  method            VARCHAR(20) NOT NULL,  -- qr | biometric | manual
  checked_in_at     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  checked_out_at    TIMESTAMPTZ,
  is_auto_checkout  BOOLEAN NOT NULL DEFAULT false,
  reason            TEXT,
  checked_in_by     UUID REFERENCES staff(id),  -- NULL for qr/biometric
  created_at        TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE walk_ins (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  branch_id       UUID NOT NULL REFERENCES branches(id),
  name            VARCHAR(100) NOT NULL,
  phone           VARCHAR(20) NOT NULL,
  purpose         VARCHAR(50) NOT NULL,
  fee_paise       INTEGER NOT NULL DEFAULT 0,
  payment_method  VARCHAR(20),
  reference       VARCHAR(100),
  notes           TEXT,
  guest_of_id     UUID REFERENCES members(id),
  logged_by       UUID REFERENCES staff(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_attendance_member ON attendance_logs(member_id, checked_in_at DESC);
CREATE INDEX idx_attendance_branch ON attendance_logs(branch_id, checked_in_at DESC);
CREATE INDEX idx_attendance_date   ON attendance_logs(tenant_id, (checked_in_at::date));
```

---

## 12. Business Rules

- Cannot check in: `status=inactive` or `status=frozen`
- Expired member: block after grace period; warn during grace period with orange banner
- Double check-in: if open check-in exists today, show confirmation dialog before creating another
- Manual check-in requires reason field if member has expired membership
- Walk-in fee creates a `payments` record linked to `walk_in_id` (no subscription)

---

## 13. Access Control

| Role | Add check-in | View log | Delete entry | Walk-ins | Scope |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | All branches |
| Branch manager | Yes | Yes | No | Yes | Own branch |
| Receptionist | Yes | Yes | No | Yes | Own branch |
| Accountant | No | Yes | No | No | All branches |
| Trainer | No | No | No | No | — |
