# GymOS — Gym Owner Portal
# Module 02: Memberships / Plans

**URL:** `/memberships`
**Access:** Gym owner (full), Branch manager (view only), Staff (no access)
**Purpose:** Gym owner defines and manages the plan catalogue for their gym (Monthly Rs.999, Quarterly Rs.2,499 etc.). These are distinct from GymOS platform plans set by the super admin.

---

## 1. Page Layout

```
┌──────────────────────────────────────────────────────┐
│  Memberships / Plans                [+ Create plan]  │
├──────────────────────────────────────────────────────┤
│  [All] [Active] [Inactive] [Archived]    🔍 Search   │
├──────────────────────────────────────────────────────┤
│  [Plan card]  [Plan card]  [Plan card]  [Plan card]  │
│  [Plan card]  ...                                    │
└──────────────────────────────────────────────────────┘
```

Plans displayed as responsive cards (3 columns desktop, 2 tablet, 1 mobile).

---

## 2. Plan Card

Each card shows:
- Plan name (bold, 16px)
- Duration badge (e.g. "30 days" or "3 months")
- Price (large, Rs. format) with "+18% GST" label if applicable
- Active member count ("142 members")
- Status badge: Active (green) / Inactive (grey) / Archived (strikethrough)
- Actions row: **Edit** | **Duplicate** | **Archive**

---

## 3. Create / Edit Plan Form

Opens as right-side drawer (520px wide).

| Field | Type | Validation | Required |
|---|---|---|---|
| **Plan name** | Text | 2–80 chars, unique within gym (case-insensitive) | Yes |
| **Description** | Textarea | Max 500 chars, shown to members | No |
| **Duration type** | Dropdown | Days / Months | Yes |
| **Duration value** | Number | 1–730 if Days; 1–24 if Months | Yes |
| **Price (INR)** | Number | 0–999,999 stored in paise | Yes |
| **GST applicable** | Toggle | Yes / No | Yes |
| **GST rate** | Dropdown | 0 / 5 / 12 / 18 / 28% | Yes if GST on |
| **Max members cap** | Number | 0 = unlimited; > 0 = hard limit | No |
| **Grace period (days)** | Number | 0–30 days after expiry before access blocked | No |
| **Inclusions** | Tag input | Free text tags e.g. "Pool access", "Steam room" | No |
| **Valid at branches** | Multi-select | All branches or specific branches | Yes |
| **Allow freeze** | Toggle | Yes / No | Yes |
| **Max freeze days/year** | Number | 1–90 | Yes if freeze on |
| **Status** | Toggle | Active / Inactive | Yes |

---

## 4. Plan Types and Expiry Calculation

| Plan type | Duration | End date logic |
|---|---|---|
| Day pass | 1 day | Start date + 1 day |
| Weekly | 7 days | Start date + 7 days |
| Monthly | 1 month | Same date next month (e.g. Jun 15 → Jul 15) |
| Quarterly | 3 months | Same date + 3 months |
| Half-yearly | 6 months | Same date + 6 months |
| Annual | 12 months | Same date next year |
| Custom (days) | N days | Start date + N days |
| Custom (months) | N months | Same date + N months |

> If end date falls on a non-existent date (e.g. Jan 31 + 1 month), use last day of the month (Feb 28/29).

---

## 5. Pricing & GST

- All prices stored in **paise** (integer). Rs. 999 = 99,900 paise.
- Display always as `Rs. X,XXX.00`
- GST calculated on top of base price: `total = price + (price × gst_rate / 100)`
- Invoice breakdown: Base amount | CGST + SGST (intra-state) or IGST (inter-state) | Total
- CGST = SGST = gst_rate / 2 for intra-state transactions

---

## 6. Assigning a Plan to a Member

- Done at member creation (Add member form) or from member profile (Renew / Change plan)
- Creates a `subscriptions` record: `member_id`, `plan_id`, `start_date`, `end_date` (calculated), `grace_end_date`, `status`
- Only one active subscription per member at a time
- Previous subscription set to `status=ended` when a new one is created

---

## 7. Freeze / Pause Logic

When a member's plan is frozen:
1. Set `subscriptions.freeze_start = today`
2. Set `members.status = frozen`
3. Member cannot check in while frozen
4. On unfreeze: `freeze_days = unfreeze_date − freeze_start`
5. New end date: `original_end_date + freeze_days`
6. Freeze days deducted from annual allowance

**Rules:**
- Minimum freeze duration: 1 day
- Maximum per occurrence: 30 days (configurable per plan)
- Maximum total per year: `plan.max_freeze_days`
- Cannot freeze in the last 7 days of membership (configurable)

---

## 8. Plan Archiving

- Archived plans: existing subscribers keep their plan — no disruption
- New members cannot select archived plans
- Archived plans shown with strikethrough; excluded from Add member / Renew selectors
- Cannot delete a plan with any members (active or historical) — archive instead

---

## 9. Plan Usage Stats

Available from plan card or plan detail view:
- Active members on this plan (current count)
- Total members enrolled historically
- Revenue generated this month from this plan
- Revenue generated this year from this plan
- Churn rate: % of members who did not renew in last 3 months

---

## 10. API Endpoints

```
GET /api/v1/membership-plans
  Query: status (active|inactive|archived|all), search
  Response: { plans: [...], total }

POST /api/v1/membership-plans
  Body: { name, description, duration_type, duration_value, price_paise,
          gst_applicable, gst_rate, max_members, grace_days, inclusions,
          branch_ids, allow_freeze, max_freeze_days, status }
  Response 201: { plan_id }

PUT /api/v1/membership-plans/:id
  Body: partial update of any fields
  Response 200: { plan }

POST /api/v1/membership-plans/:id/archive
  Response 200: { archived: true }
  Error 409: HAS_ACTIVE_MEMBERS — warn, require confirmation to proceed

GET /api/v1/membership-plans/:id/stats
  Response: { active_members, total_historical, revenue_this_month,
              revenue_this_year, churn_rate_pct }
```

---

## 11. Database Schema

```sql
CREATE TABLE membership_plans (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  name            VARCHAR(80) NOT NULL,
  description     TEXT,
  duration_type   VARCHAR(10) NOT NULL,    -- 'days' | 'months'
  duration_value  INTEGER NOT NULL CHECK (duration_value > 0),
  price_paise     INTEGER NOT NULL CHECK (price_paise >= 0),
  gst_applicable  BOOLEAN NOT NULL DEFAULT false,
  gst_rate        NUMERIC(5,2),            -- e.g. 18.00
  max_members     INTEGER NOT NULL DEFAULT 0,
  grace_days      INTEGER NOT NULL DEFAULT 0,
  inclusions      TEXT[],
  allow_freeze    BOOLEAN NOT NULL DEFAULT true,
  max_freeze_days INTEGER DEFAULT 30,
  status          VARCHAR(20) NOT NULL DEFAULT 'active',
  created_by      UUID REFERENCES staff(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT uq_plan_name UNIQUE (tenant_id, name),
  CONSTRAINT chk_plan_status CHECK (status IN ('active','inactive','archived'))
);

CREATE TABLE plan_branches (
  plan_id    UUID REFERENCES membership_plans(id) ON DELETE CASCADE,
  branch_id  UUID REFERENCES branches(id) ON DELETE CASCADE,
  PRIMARY KEY (plan_id, branch_id)
);

CREATE TABLE subscriptions (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  member_id       UUID NOT NULL REFERENCES members(id),
  plan_id         UUID NOT NULL REFERENCES membership_plans(id),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  start_date      DATE NOT NULL,
  end_date        DATE NOT NULL,
  grace_end_date  DATE,
  freeze_start    DATE,
  freeze_days     INTEGER DEFAULT 0,
  status          VARCHAR(20) NOT NULL DEFAULT 'active',
  created_by      UUID REFERENCES staff(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
```

---

## 12. Validation Rules

| Rule | Detail |
|---|---|
| Plan name | Unique per tenant, case-insensitive |
| Price | 0–999,999 INR (day pass can be Rs. 0 for free trial) |
| Duration (days) | 1–730 |
| Duration (months) | 1–24 |
| GST rate | Must be one of: 0, 5, 12, 18, 28 |
| Grace days | 0–30 |
| Max freeze days | 1–90 |

---

## 13. Error Handling

| Error | Message shown |
|---|---|
| Duplicate plan name | "A plan named '{name}' already exists. Please use a different name." |
| Delete with active members | "Cannot delete this plan — 142 members are on it. Archive it instead." |
| Invalid GST rate | "GST rate must be 0, 5, 12, 18, or 28%." |
| Price out of range | "Price must be between Rs. 0 and Rs. 9,99,999." |

---

## 14. Access Control

| Role | Create | Edit | Archive | View stats |
|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes |
| Branch manager | No | No | No | Yes (own branch plans) |
| Receptionist | No | No | No | Plan names only |
| Accountant | No | No | No | Yes |
