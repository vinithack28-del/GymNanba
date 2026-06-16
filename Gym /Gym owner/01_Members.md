# GymOS — Gym Owner Portal
# Module 01: Members

**URL:** `/members`
**Access:** Gym owner (all branches), Branch manager (own branch only)
**Purpose:** Central member directory. Single flat page — no sub-menu. Clicking Members in the sidebar opens the member list directly with inline search, filters, and an Add member button.

---

## 1. Page Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│  [Total: 1,240]  [Active: 1,102]  [Inactive: 84]  [Expired: 54]    │  ← stats bar
├─────────────────────────────────────────────────────────────────────┤
│  🔍 Search...  │ Status ▾ │ Plan ▾ │ Branch ▾ │ Gender ▾ │ Date ▾  │
│  [Clear all filters]                 [+ Add member]   [Export CSV]  │
├─────────────────────────────────────────────────────────────────────┤
│  Member table (paginated, 25 rows default)                          │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 2. Stats Bar

| Card | Calculation | Click behaviour |
|---|---|---|
| **Total members** | `COUNT(*) WHERE status != 'deleted'` | Clears all filters |
| **Active** | `COUNT(*) WHERE status='active' AND subscription_end_date >= TODAY` | Filters to Active |
| **Inactive** | `COUNT(*) WHERE status='inactive'` | Filters to Inactive |
| **Expired** | `COUNT(*) WHERE subscription_end_date < TODAY AND status='active'` | Filters to Expired |

Cards refresh every 60 seconds or on any member action (add, edit, status change).

---

## 3. Search

- Real-time search, 300 ms debounce
- Fields searched: full name, phone, email, member ID (partial match, case-insensitive)
- Matching text highlighted in yellow in results
- Combines with active filters using AND logic
- Empty search = show all (subject to other active filters)

---

## 4. Filters

| Filter | Options | Behaviour |
|---|---|---|
| **Status** | All / Active / Inactive / Expired / Frozen | Single select |
| **Plan** | All active plans in this gym | Single select |
| **Branch** | All / specific (shown only if 2+ branches) | Single select |
| **Gender** | All / Male / Female / Other | Single select |
| **Join date** | Date range picker (from → to) | Filters on `created_at` |
| **Expiry date** | Date range picker (from → to) | Filters on `subscription_end_date` |

- All filters combine with AND logic
- Active filters shown as dismissible chips below the filter row
- **Clear all** removes all active filters and search
- Filter state persists during the session (navigating away and back restores filters)

---

## 5. Member Table Columns

| Column | Width | Sortable | Notes |
|---|---|---|---|
| Checkbox | 36px | No | Bulk actions |
| **Member ID** | 90px | Yes | Auto-generated e.g. MEM-00147 |
| **Name + avatar** | 180px | Yes (by name) | Initials avatar if no photo |
| **Phone** | 120px | No | Clickable to dial on mobile |
| **Plan** | 120px | Yes | Plan name |
| **Branch** | 110px | Yes | Hidden on single-branch gyms |
| **Join date** | 100px | Yes | dd MMM yyyy |
| **Expiry date** | 100px | Yes | Red text if past |
| **Status** | 90px | Yes | Colour badge (see §6) |
| **Balance due** | 100px | Yes | Rs. 0.00 if clean; red if > 0 |
| **Actions** | 80px | No | Dropdown menu on hover |

Default sort: join date descending (newest first). Row click opens member profile. Sticky header on scroll.

---

## 6. Status Badge Definitions

| Status | Colour | Definition |
|---|---|---|
| **Active** | Green `#1D9E75` | Membership current (end date ≥ today) and account active |
| **Inactive** | Grey `#888780` | Manually marked inactive — not expired |
| **Expired** | Red `#E24B4A` | End date has passed, no renewal yet |
| **Frozen** | Blue `#378ADD` | Plan temporarily paused; end date extended by freeze days |

---

## 7. Row Actions

| Action | Who can do it | Behaviour |
|---|---|---|
| **View profile** | All roles | Opens `/members/:id` |
| **Edit** | Owner, Branch manager | Opens edit drawer pre-filled |
| **Collect fee** | Owner, Receptionist, Accountant | Shortcut to Payments pre-filled with this member |
| **Freeze** | Owner, Branch manager | Modal: reason, start date, end date, auto-resume toggle |
| **Unfreeze** | Owner, Branch manager | Shown only if frozen. Recalculates end date. |
| **Mark inactive** | Owner | Confirm dialog. Sets `status=inactive`. |
| **Delete** | Owner only | Type member name to confirm. Blocked if balance > 0. Soft delete. |

---

## 8. Add Member Form

Opens as a right-side drawer (480px wide).

| Field | Type | Validation | Required |
|---|---|---|---|
| **Full name** | Text | 2–100 chars, letters + spaces | Yes |
| **Phone** | Tel | E.164, unique within gym | Yes |
| **Email** | Email | RFC 5322, unique if provided | No |
| **Gender** | Radio | Male / Female / Other | No |
| **Date of birth** | Date picker | Past only; person ≥ 5 years old | No |
| **Address** | Textarea | Max 300 chars | No |
| **ID proof type** | Dropdown | Aadhaar / PAN / Passport / Voter ID / DL | No |
| **ID proof number** | Text | Format validated per type | No |
| **ID proof upload** | File | JPG/PNG/PDF, max 5 MB | No |
| **Photo** | File/Camera | JPG/PNG, max 2 MB, 1:1 crop | No |
| **Membership plan** | Dropdown | Active plans only | Yes |
| **Start date** | Date picker | Default today; max +30 days future | Yes |
| **Payment collected** | Number | INR ≥ 0 | No |
| **Payment method** | Dropdown | Cash / UPI / Card / Bank | Required if amount > 0 |
| **Notes** | Textarea | Max 500 chars | No |

**On submit:**
1. Create `members` record
2. Create `subscriptions` record linked to plan
3. If payment > 0: create `payments` record + generate receipt
4. Send welcome message to member (if notifications enabled)

---

## 9. Export CSV

- Respects all active search and filter criteria
- Filename: `members_{gym_name}_{YYYY-MM-DD}.csv`
- Columns: Member ID, Full name, Phone, Email, Gender, DOB, Address, Plan, Branch, Join date, Expiry date, Status, Total paid, Balance due, Last payment date
- Max 10,000 rows per export. Warning shown if more.

---

## 10. Pagination

- Default 25 rows. Options: 10 / 25 / 50 / 100
- Server-side pagination using LIMIT/OFFSET
- Shows: "Showing 1–25 of 1,240 members"
- Page state preserved on back navigation

---

## 11. Empty States

**No members yet:**
> Large icon + "No members yet. Add your first member to get started." + `+ Add member` button

**No search/filter results:**
> Large icon + "No members match your search. Try adjusting your filters." + `Clear all filters` button

---

## 12. API Endpoints

```
GET /api/v1/members
  Query params:
    search        string    — name, phone, email, member ID
    status        string    — active | inactive | expired | frozen
    plan_id       uuid
    branch_id     uuid
    gender        string    — male | female | other
    join_from     date      — ISO 8601
    join_to       date
    expiry_from   date
    expiry_to     date
    sort_by       string    — name | join_date | expiry_date | status | balance
    sort_dir      string    — asc | desc (default: desc)
    page          integer   — default 1
    limit         integer   — default 25, max 100
  Response 200:
    { members: [...], total: 1240, page: 1, limit: 25, total_pages: 50 }

POST /api/v1/members
  Body: { name, phone, email?, gender?, dob?, address?, id_proof_type?,
          id_proof_number?, plan_id, start_date, payment_amount?,
          payment_method?, branch_id, notes? }
  Response 201: { member_id, member_code, subscription_id, payment_id? }

GET /api/v1/members/:id
  Response 200: { member, subscription, payment_history, attendance_summary }

PUT /api/v1/members/:id
  Body: partial update of any fields
  Response 200: { member }

DELETE /api/v1/members/:id
  Response 200: { deleted: true }
  Error 409: MEMBER_HAS_OUTSTANDING_BALANCE

GET /api/v1/members/export
  Query: same as GET (no pagination)
  Response: text/csv file download

GET /api/v1/members/stats
  Response: { total, active, inactive, expired, frozen }
```

---

## 13. Database Schema

```sql
CREATE TABLE members (
  id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id         UUID NOT NULL REFERENCES tenants(id),
  branch_id         UUID REFERENCES branches(id),
  member_code       VARCHAR(20) UNIQUE NOT NULL,  -- e.g. MEM-00147
  name              VARCHAR(100) NOT NULL,
  phone             VARCHAR(20) NOT NULL,
  email             VARCHAR(255),
  gender            VARCHAR(10),                  -- male | female | other
  dob               DATE,
  address           TEXT,
  id_proof_type     VARCHAR(20),
  id_proof_number   VARCHAR(50),
  id_proof_url      TEXT,
  photo_url         TEXT,
  status            VARCHAR(20) NOT NULL DEFAULT 'active',
  balance_paise     INTEGER NOT NULL DEFAULT 0,
  notes             TEXT,
  created_by        UUID REFERENCES staff(id),
  created_at        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at        TIMESTAMPTZ,

  CONSTRAINT uq_member_phone UNIQUE (tenant_id, phone),
  CONSTRAINT uq_member_email UNIQUE (tenant_id, email),
  CONSTRAINT chk_status CHECK (status IN ('active','inactive','expired','frozen'))
);

CREATE INDEX idx_members_tenant    ON members(tenant_id, status);
CREATE INDEX idx_members_phone     ON members(tenant_id, phone);
CREATE INDEX idx_members_branch    ON members(branch_id);
CREATE INDEX idx_members_name_trgm ON members USING gin(name gin_trgm_ops);
```

---

## 14. Validation Rules

| Field | Rule |
|---|---|
| Name | 2–100 chars, letters + spaces + hyphens |
| Phone | E.164 format, unique per tenant |
| Email | RFC 5322, unique per tenant if provided |
| DOB | Must be in past; person must be ≥ 5 years old |
| Aadhaar | 12 digits exactly |
| PAN | 10 chars, format AAAAA9999A |
| Photo | Max 2 MB, JPG or PNG |
| ID proof | Max 5 MB, JPG/PNG/PDF |
| Start date | Not more than 30 days in the future |

---

## 15. Error Handling

| Error | Code | Message shown |
|---|---|---|
| Duplicate phone | `DUPLICATE_PHONE` | "A member with this phone number already exists." |
| Duplicate email | `DUPLICATE_EMAIL` | "This email is already registered to another member." |
| Invalid plan | `INVALID_PLAN` | "Selected plan is not available. Please choose another." |
| Delete with dues | `HAS_OUTSTANDING_BALANCE` | "Cannot delete — member has outstanding balance. Collect payment first." |
| Photo too large | `FILE_TOO_LARGE` | "Photo must be under 2 MB." |
| Network error | `NETWORK_ERROR` | "Connection lost. Changes not saved. Please try again." |

---

## 16. Access Control

| Role | View | Add | Edit | Delete | Scope |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | All branches |
| Branch manager | Yes | Yes | Yes | No | Own branch only |
| Receptionist | Yes | Yes | No | No | Own branch only |
| Accountant | Yes (name + balance only) | No | No | No | All branches |
| Trainer | Own clients only | No | No | No | Own branch only |
