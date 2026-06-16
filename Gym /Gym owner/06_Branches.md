# GymOS — Gym Owner Portal
# Module 06: Branches

**URL:** `/branches`
**Access:** Gym owner (full), Branch manager (view own branch only), Staff (no access)
**Purpose:** Single flat page — no sub-menu. View all branches, add new branches, and manage existing ones. Plan limit is enforced on this page.

---

## 1. Page Layout

```
┌────────────────────────────────────────────────────────┐
│  Branches  (2 of 3 branches used)     [+ Add branch]  │
├────────────────────────────────────────────────────────┤
│  [Branch card]  [Branch card]  [Branch card]           │
└────────────────────────────────────────────────────────┘
```

- Branch count vs plan limit shown in header (e.g. "2 of 3 branches used")
- **+ Add branch** button disabled with tooltip "Plan limit reached. Upgrade to add more." when at limit

---

## 2. Branch Card

Each branch shown as a card (280px wide, responsive grid):

| Field | Notes |
|---|---|
| Branch name | Bold, 16px |
| Address | City + PIN |
| Phone | Clickable |
| Branch manager | Name, or "No manager assigned" |
| Members count | Active members total |
| Staff count | Active staff at this branch |
| Check-ins today | Live count (cached 5 min) |
| Revenue this month | Rs. format |
| Status badge | Active (green) / Inactive (grey) |
| Actions | Edit \| View members \| View staff \| Deactivate |

---

## 3. Add Branch Form

Opens as right-side drawer (520px wide).

| Field | Validation | Required |
|---|---|---|
| **Branch name** | 2–80 chars, unique within gym | Yes |
| **Address line 1** | 5–100 chars | Yes |
| **Address line 2** | Max 100 chars | No |
| **City** | 2–50 chars | Yes |
| **State** | Dropdown of Indian states | Yes |
| **PIN code** | 6-digit numeric | Yes |
| **Phone** | E.164 format | Yes |
| **Email** | RFC 5322 | No |
| **Branch manager** | Dropdown: branch_manager role staff, or "Assign later" | No |
| **Opening time (per day)** | Time per day of week | No (defaults to gym-wide hours) |
| **Closing time (per day)** | Must be after opening time | No |
| **Closed days** | Multi-select: Mon–Sun | No |
| **Amenities** | Checkboxes: Pool / Steam room / Parking / Locker / Cafeteria / AC / WiFi | No |
| **GST registration** | Separate GSTIN if this branch has its own registration | No |
| **Status** | Active (default) / Inactive | Yes |

---

## 4. Edit Branch

Same form as Add branch, pre-filled.

**Change of branch manager:**
- Old manager: loses branch association; access revoked immediately
- History of managers stored in `branch_manager_history`: from_date, to_date
- New manager: gains branch access immediately

---

## 5. Branch Deactivation

Confirm dialog explains consequences.

**On deactivation:**
1. All staff at this branch: login blocked immediately (sessions revoked)
2. Open check-ins: auto-checked out
3. Members at this branch: reassign to another branch (owner selects in dialog) or leave as "Unassigned"
4. New check-ins at this branch: blocked

**Reactivation:**
- Sets `status = active`
- Staff must be re-enabled individually (they remain inactive after reactivation)
- Sends "Branch reactivated" notification to the branch manager (if assigned)

---

## 6. Plan Limit Enforcement

- Limit read from `tenant_subscriptions.plan.max_branches`
- Check: `COUNT(branches WHERE status='active') >= plan.max_branches`
- At limit: "+ Add branch" disabled; banner shown: "You've used all X branches on your plan."
- Banner includes "View plans" link → navigates to Settings > Subscription

---

## 7. Per-Branch Stats (on card)

| Stat | Calculation | Cache TTL |
|---|---|---|
| Members | `COUNT(members WHERE branch_id=X AND deleted_at IS NULL)` | 5 min |
| Active members | `COUNT WHERE status='active' AND end_date >= TODAY` | 5 min |
| Check-ins today | `COUNT(attendance_logs WHERE branch_id=X AND date=TODAY)` | 5 min |
| Revenue this month | `SUM(payments WHERE branch_id=X AND month=current)` | 5 min |

---

## 8. API Endpoints

```
GET /api/v1/branches
  Response: { branches: [...], total, plan_limit, plan_limit_remaining }

POST /api/v1/branches
  Body: { name, address1, address2, city, state, pin, phone, email?,
          manager_id?, operating_hours, amenities, gst_number?, status }
  Response 201: { branch_id }
  Error 403: BRANCH_LIMIT_REACHED

PUT /api/v1/branches/:id
  Body: partial update of any fields
  Response 200: { branch }

POST /api/v1/branches/:id/deactivate
  Body: { member_reassign_branch_id? }
  Response 200: { deactivated: true, members_reassigned: N }

POST /api/v1/branches/:id/reactivate
  Response 200: { reactivated: true }

GET /api/v1/branches/:id/stats
  Response: { members, active_members, checkins_today, revenue_this_month }
```

---

## 9. Database Schema

```sql
CREATE TABLE branches (
  id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id        UUID NOT NULL REFERENCES tenants(id),
  name             VARCHAR(80) NOT NULL,
  address1         VARCHAR(100) NOT NULL,
  address2         VARCHAR(100),
  city             VARCHAR(50) NOT NULL,
  state            VARCHAR(50) NOT NULL,
  pin              CHAR(6) NOT NULL,
  phone            VARCHAR(20) NOT NULL,
  email            VARCHAR(255),
  current_manager  UUID REFERENCES staff(id),
  operating_hours  JSONB,
    -- { mon: { open: '06:00', close: '22:00', closed: false }, ... }
  amenities        TEXT[],
  gst_number       VARCHAR(15),
  status           VARCHAR(20) NOT NULL DEFAULT 'active',
  is_primary       BOOLEAN NOT NULL DEFAULT false,
  created_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT uq_branch_name UNIQUE (tenant_id, name)
);

CREATE TABLE branch_manager_history (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  branch_id   UUID NOT NULL REFERENCES branches(id),
  manager_id  UUID NOT NULL REFERENCES staff(id),
  from_date   DATE NOT NULL,
  to_date     DATE,  -- NULL means current
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
```

---

## 10. Business Rules

- Minimum 1 branch always required — cannot deactivate the last active branch
- Cannot delete the primary branch — deactivate only
- Deactivation ≠ deletion: deactivated branches retain all data
- Only one manager per branch at any time
- GSTIN per branch is optional — defaults to main gym GSTIN for invoices
