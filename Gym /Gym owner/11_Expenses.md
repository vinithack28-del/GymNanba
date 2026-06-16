# GymOS — Gym Owner Portal
# Module 11: Expenses

**URL:** `/expenses`
**Access:** Owner (full), Accountant (full), Branch manager (own branch, no delete), Receptionist (no access)
**Purpose:** Record and track all gym operational expenses. Data feeds into P&L reports.

---

## 1. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  [This month: Rs.2,84,500]  [Rent: 35%]  [Salary: 42%]      │  ← mini summary
├──────────────────────────────────────────────────────────────┤
│  Date ▾  Category ▾  Branch ▾  Method ▾   🔍  [+ Add]  [CSV]│
├──────────────────────────────────────────────────────────────┤
│  Expense table (sorted by date desc)                         │
└──────────────────────────────────────────────────────────────┘
```

---

## 2. Table Columns

Date | Category | Description | Amount (INR) | GST paid | Method | Paid to | Branch | Recorded by | Receipt | Status | Actions (Edit, Delete)

---

## 3. Add Expense Form

| Field | Validation | Required |
|---|---|---|
| **Date** | Default today; not > 1 year past | Yes |
| **Category** | See §4 | Yes |
| **Sub-category** | Dropdown, dynamic per category | No |
| **Description** | 5–200 chars | Yes |
| **Amount (INR)** | > 0; max 999,999 | Yes |
| **GST paid** | 0 to amount (for input credit tracking) | No |
| **Payment method** | Cash / UPI / Bank / Cheque / Card | Yes |
| **Paid to (vendor)** | Max 100 chars | No |
| **Reference / bill no.** | Max 100 chars | No |
| **Branch** | Active branches | Yes |
| **Receipt upload** | JPG/PNG/PDF, max 5 MB | Required if amount > Rs. 1,000 (configurable) |
| **Notes** | Max 500 chars | No |

---

## 4. Expense Categories & Sub-categories

| Category | Sub-categories | P&L mapping |
|---|---|---|
| **Rent** | Main hall / Studio / Storage / Office | Fixed costs |
| **Utilities** | Electricity / Water / Internet / Phone | Fixed costs |
| **Salaries** | Full-time / Part-time / Contract / Bonus | Labour |
| **Equipment** | Purchase / Repair / Maintenance / Replacement | Operations |
| **Marketing** | Social media / Flyers / Events / Promotions | Growth |
| **Supplies** | Cleaning / Consumables / Stationery / Toiletries | Operations |
| **Insurance** | Liability / Equipment / Health | Fixed costs |
| **Software** | GymOS subscription / Other | Operations |
| **Miscellaneous** | (free text) | Other |

---

## 5. Recurring Expenses

Mark any expense as recurring at time of creation:

| Setting | Options |
|---|---|
| Frequency | Daily / Weekly / Monthly / Annual |
| End date | Optional — auto-stops after this date |

**Behaviour:**
- Creates a `recurring_expense_schedules` record
- Scheduled job creates expense entries automatically on due date
- Owner gets email reminder 1 day before auto-creation: "Recurring expense Rs. 45,000 (Rent) due tomorrow. Edit or cancel at /expenses."
- Pause or cancel recurring from the expense record at any time

---

## 6. Salary Expenses

When category = **Salaries**, additional fields appear:

| Field | Notes |
|---|---|
| Staff member | Dropdown of all active staff |
| Month | Month picker (e.g. Jun 2026) |
| Basic salary | INR |
| Allowances | INR |
| Deductions | INR |
| Net pay | Auto-calculated: Basic + Allowances − Deductions |

- Salary slip PDF generated (employer copy format)
- Salary history visible on Staff profile page under Documents tab

---

## 7. Receipt Attachment

- File types: JPG, PNG, PDF
- Max size: 5 MB
- Preview shown inline in expense record
- Required for expenses above configurable threshold (default Rs. 1,000)
- Stored in object storage; `receipt_url` saved in DB

---

## 8. Approval Workflow

For amounts above a configurable threshold (default Rs. 10,000):

1. Staff submits expense → status = `pending`
2. Gym owner receives inbox notification
3. Owner views expense details → Approve or Reject (reason required for rejection)
4. Rejected expenses shown with rejection reason; can be edited and resubmitted

---

## 9. Monthly Summary Panel

Collapsible panel at top of page showing current month:
- Total expenses
- % breakdown by category (mini bar chart)
- Top 5 individual expenses
- Comparison vs last month (% change with arrow)

---

## 10. API Endpoints

```
GET /api/v1/expenses
  Query: from, to, category, branch_id, method, status, page, limit
  Response: { expenses: [...], total, total_amount_paise, by_category }

POST /api/v1/expenses
  Body: { date, category, sub_category?, description, amount_paise,
          gst_paise?, method, vendor?, reference?, branch_id,
          notes?, is_recurring?, recurrence_freq?, recurrence_end?,
          staff_id?, salary_month? }
  Response 201: { expense_id }

PUT /api/v1/expenses/:id
  Body: partial update
  Response 200: { expense }

DELETE /api/v1/expenses/:id
  Response 200: { deleted: true }

GET /api/v1/expenses/summary
  Query: from, to, branch_id
  Response: { total_paise, by_category: [...], top_5: [...],
              vs_last_month_pct }

POST /api/v1/expenses/:id/approve
  Body: { approved: true|false, reason? }
  Response 200: { status: 'approved' | 'rejected' }
```

---

## 11. Database Schema

```sql
CREATE TABLE expenses (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  branch_id       UUID NOT NULL REFERENCES branches(id),
  date            DATE NOT NULL,
  category        VARCHAR(50) NOT NULL,
  sub_category    VARCHAR(50),
  description     VARCHAR(200) NOT NULL,
  amount_paise    INTEGER NOT NULL CHECK (amount_paise > 0),
  gst_paise       INTEGER NOT NULL DEFAULT 0,
  method          VARCHAR(20) NOT NULL,
  vendor          VARCHAR(100),
  reference       VARCHAR(100),
  receipt_url     TEXT,
  notes           TEXT,
  status          VARCHAR(20) NOT NULL DEFAULT 'approved',
                  -- pending | approved | rejected
  is_recurring    BOOLEAN NOT NULL DEFAULT false,
  recurrence_freq VARCHAR(20),  -- daily | weekly | monthly | annual
  recurrence_end  DATE,
  staff_id        UUID REFERENCES staff(id),  -- salary expenses only
  salary_month    VARCHAR(7),   -- e.g. '2026-06'
  created_by      UUID REFERENCES staff(id),
  approved_by     UUID REFERENCES staff(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_expenses_tenant ON expenses(tenant_id, date DESC);
CREATE INDEX idx_expenses_branch ON expenses(branch_id, date DESC);
```

---

## 12. Validation Rules

| Field | Rule |
|---|---|
| Amount | > 0; max 999,999 INR |
| Date | Not more than 1 year in the past |
| Receipt | Required above threshold (default Rs. 1,000, configurable) |
| GST paid | 0 to amount |

---

## 13. Access Control

| Role | Add | Edit | Delete | Approve | Export |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | Yes |
| Accountant | Yes | Yes | Yes | No | Yes |
| Branch manager | Yes (own branch) | Yes (own branch) | No | No | Yes (own branch) |
| Receptionist | No | No | No | No | No |
