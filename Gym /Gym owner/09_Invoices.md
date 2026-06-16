# GymOS — Gym Owner Portal
# Module 09: Invoices

**URL:** `/invoices`
**Access:** Owner + Accountant (full); Receptionist (view only)
**Purpose:** GST-compliant invoice management. Invoices are auto-generated on each payment. Manual invoices can be created for non-standard charges (personal training, locker fees, etc.).

---

## 1. Page Layout

```
┌─────────────────────────────────────────────────────────────┐
│  Date ▾  Status ▾  Branch ▾  Method ▾   🔍 Search member   │
│                              [+ Create invoice]  [Bulk DL]  │
├─────────────────────────────────────────────────────────────┤
│  Invoice table                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Invoice Table Columns

| Column | Notes |
|---|---|
| **Invoice #** | e.g. FIT-2026-000847 |
| **Date** | Invoice date |
| **Member name** | Clickable to member profile |
| **Description** | Plan name or custom description |
| **Amount** | Excluding GST |
| **GST** | GST amount |
| **Total** | Amount + GST |
| **Method** | Cash / UPI / Card etc. |
| **Status** | Paid / Unpaid / Partial / Void |
| **Actions** | Download PDF, View details, Void |

---

## 3. Auto-generated Invoices

Automatically created when `POST /payments` is called. Invoice linked to payment via `payment_id`.

**Invoice number format:** `GYM-YYYY-NNNNNN`

| Component | Example | Logic |
|---|---|---|
| `GYM` | `FIT` | First 3 chars of gym name, uppercase |
| `YYYY` | `2026` | Current year |
| `NNNNNN` | `000847` | 6-digit zero-padded sequence, resets annually at 000001 |

Example: `FIT-2026-000847`

---

## 4. Manual Invoice Creation

For charges not linked to a membership plan payment.

| Field | Required | Notes |
|---|---|---|
| **Member** | Yes | Search by name / phone |
| **Invoice date** | Yes | Default today |
| **Due date** | No | For unpaid invoices |
| **Line items** | Yes (≥ 1) | Description, Qty, Rate (INR), GST rate — amount auto-calculated |
| **Notes** | No | Shown on PDF |

Line items are added/removed dynamically. Total auto-calculates on every change.

---

## 5. GST Invoice Compliance (Indian Requirements)

All invoices must include:

- Gym GSTIN (from gym profile)
- SAC code: **998311** (Fitness centre services)
- Member name and address
- Invoice number and date
- Line items with rate and GST rate per line
- CGST + SGST (intra-state) **or** IGST (inter-state)
- Grand total in words (e.g. "Rupees One Thousand One Hundred and Seventy-Eight Only")
- Place of supply (state)

---

## 6. Invoice PDF Layout

```
┌────────────────────────────────────────────┐
│  [GYM LOGO]   FitZone Gym                  │
│               123 MG Road, Chennai 600001  │
│               GSTIN: 33AADCF1234Z1Z5       │
├────────────────────────────────────────────┤
│  INVOICE                   FIT-2026-000847  │
│  Date: 12 Jun 2026                          │
├────────────────────────────────────────────┤
│  Bill to:                                   │
│  Arjun Sharma  (MEM-00147)                  │
│  +91 98765 43210                            │
├────────────────────────────────────────────┤
│  #  Description       Qty   Rate    Amount  │
│  1  Monthly plan        1   999.00  999.00  │
├────────────────────────────────────────────┤
│             Subtotal:          Rs.  999.00  │
│             CGST (9%):         Rs.   89.91  │
│             SGST (9%):         Rs.   89.91  │
│             TOTAL:             Rs. 1,178.82 │
├────────────────────────────────────────────┤
│  Total in words: Rupees One Thousand...     │
│  SAC: 998311  │  Payment: Cash             │
│  Place of supply: Tamil Nadu                │
└────────────────────────────────────────────┘
```

---

## 7. Invoice Statuses

| Status | Colour | Definition |
|---|---|---|
| **Paid** | Green | Payment fully recorded against this invoice |
| **Unpaid** | Amber | Invoice created; no payment recorded yet |
| **Partial** | Blue | Partial payment received; balance outstanding |
| **Void** | Grey strikethrough | Invoice cancelled — no financial effect |

---

## 8. Filters

| Filter | Options |
|---|---|
| Date range | Today / This month / Last month / Custom |
| Status | All / Paid / Unpaid / Partial / Void |
| Member search | Search by name, phone, or member ID |
| Amount range | From Rs. — To Rs. |
| Branch | All / specific |
| Method | All / Cash / UPI / Card / Bank / Cheque |

---

## 9. Bulk Download

- Select multiple invoices via checkboxes
- "Bulk download" button → server generates ZIP of PDFs
- Progress bar shown for large batches
- ZIP filename: `invoices_{gym_name}_{date}.zip`

---

## 10. API Endpoints

```
GET /api/v1/invoices
  Query: from, to, status, member_id, branch_id, method,
         amount_min, amount_max, page, limit
  Response: { invoices: [...], total }

POST /api/v1/invoices
  Body: { member_id, date, due_date?,
          line_items: [{ description, qty, rate_paise, gst_rate }],
          notes? }
  Response 201: { invoice_id, invoice_number }

GET /api/v1/invoices/:id
  Response: { full invoice object with line items }

GET /api/v1/invoices/:id/pdf
  Response: application/pdf download

POST /api/v1/invoices/bulk-download
  Body: { invoice_ids: [...] }
  Response: application/zip download (or 202 + job_id if large)

POST /api/v1/invoices/:id/void
  Body: { reason }
  Response 200: { voided: true }
```

---

## 11. Database Schema

```sql
CREATE TABLE invoices (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  member_id       UUID NOT NULL REFERENCES members(id),
  payment_id      UUID REFERENCES payments(id),  -- NULL for manual invoices
  invoice_number  VARCHAR(30) NOT NULL UNIQUE,
  invoice_date    DATE NOT NULL,
  due_date        DATE,
  line_items      JSONB NOT NULL,
    -- [{ description, qty, rate_paise, gst_rate, amount_paise }]
  subtotal_paise  INTEGER NOT NULL,
  gst_paise       INTEGER NOT NULL DEFAULT 0,
  total_paise     INTEGER NOT NULL,
  status          VARCHAR(20) NOT NULL DEFAULT 'paid',  -- paid|unpaid|partial|void
  notes           TEXT,
  voided_at       TIMESTAMPTZ,
  void_reason     TEXT,
  created_by      UUID REFERENCES staff(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_invoices_member ON invoices(member_id, invoice_date DESC);
CREATE INDEX idx_invoices_tenant ON invoices(tenant_id, invoice_date DESC);
```

---

## 12. Validation Rules

| Rule | Detail |
|---|---|
| Cannot edit after payment | Void and recreate instead |
| Manual invoice | Must have at least one line item |
| Line item rate | Must be > 0 |
| GST rate | Must be one of: 0, 5, 12, 18, 28 |
| Void | Requires a reason; cannot void a voided invoice |

---

## 13. Access Control

| Role | Create manual | View all | Download PDF | Void |
|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes |
| Accountant | Yes | Yes | Yes | Yes |
| Branch manager | Yes (own branch) | Yes (own branch) | Yes | No |
| Receptionist | No | Yes | Yes | No |
| Trainer / POS | No | No | No | No |
