# GymOS — Gym Owner Portal
# Module 08: Payments

**URL:** `/payments`
**Sub-pages:** `/payments/collect`, `/payments/history`, `/payments/dues`
**Access:** Owner + Accountant (full); Receptionist (collect only, no void); Branch manager (own branch)
**Purpose:** Record member fee collections, view payment history, and track outstanding dues.

---

## 1. Collect Fee (`/payments/collect`)

### Flow
1. Search member by name / phone / member ID
2. Member card appears: name, photo, plan, expiry date, balance due (highlighted in red)
3. Fill payment form and submit

### Payment Form Fields

| Field | Default | Validation |
|---|---|---|
| **Amount (INR)** | Balance due amount | > 0; max 999,999 |
| **Payment method** | Cash | Cash / UPI / Card / Bank transfer / Cheque |
| **Reference number** | — | Required for UPI, Card, Bank, Cheque |
| **Date** | Today | Not future; not > 180 days past |
| **Notes** | — | Max 300 chars |

**On submit:** create payment record → update member balance → generate receipt → show receipt preview.

### Receipt Options
- Print (browser print dialog)
- Download PDF
- Send via WhatsApp
- Send via SMS

---

## 2. Payment Receipt Contents

| Field | Content |
|---|---|
| Receipt number | Sequential per gym: `REC-00847` |
| Gym name + logo | From gym profile |
| Member name + ID | |
| Plan name | |
| Payment date | As entered |
| Amount received | Rs. X,XXX.00 |
| Payment method | With reference number if applicable |
| GST breakdown | Base + CGST + SGST = Total (if plan has GST) |
| Balance after payment | Running balance — Rs. 0.00 if fully settled |
| Collected by | Staff name |
| Footer | Gym address, phone, GSTIN |

---

## 3. Payment History (`/payments/history`)

**Filters:** Date range (Today / This week / This month / Last month / Custom), Branch, Method, Staff, Status (Active / Voided)

**Search:** Member name or receipt number

**Table columns:** Date | Member name | Amount | GST | Total | Method | Plan | Branch | Received by | Receipt # | Status | Actions (View receipt, Void)

**Total row:** Sum of visible rows (Amount + Total). Updates with every filter change.

---

## 4. Pending Dues (`/payments/dues`)

Members with `balance_paise > 0`. Default sort: days overdue descending.

**Table columns:** Member name | Plan | Due amount | Due since | Days overdue | Last payment date | Last amount | Actions (Collect now, Send reminder)

---

## 5. Partial Payments

- Member pays less than full plan price at registration or renewal
- Remaining balance stored in `members.balance_paise`
- Balance shown on member card and in Pending dues page
- Next payment form auto-fills with remaining balance
- Running balance tracked: `balance_paise = balance_paise − payment_amount_paise`

---

## 6. Payment Voiding

| Rule | Detail |
|---|---|
| Who can void | Gym owner and accountant only |
| Reason required | Dropdown: Data entry error / Duplicate payment / Refund / Other + text |
| Effect | `payments.status=voided`; `members.balance_paise` restored |
| Time limit | Cannot void > 90 days old without super admin override |
| Audit | All voids logged in `owner_audit_log` with who, when, reason |

---

## 7. GST on Payments

- Applicable when the linked membership plan has `gst_applicable=true`
- GST amount: `plan_price_paise × gst_rate / 100`
- Shown separately on receipt: Base amount | GST (CGST + SGST) | Total
- Included in invoice

---

## 8. Daily Collection Summary

Available from Payment History as "Today's tally" button:
- Total collected by payment method (Cash: Rs. X | UPI: Rs. X | Card: Rs. X)
- Total by staff member
- Total by branch (multi-branch only)
- GST collected
- Printable one-page summary

---

## 9. API Endpoints

```
POST /api/v1/payments
  Body: { member_id, amount_paise, method, reference?, date, notes? }
  Response 201: { payment_id, receipt_id, receipt_url, balance_remaining_paise }

GET /api/v1/payments
  Query: from, to, branch_id, staff_id, method, status, member_id, page, limit
  Response: { payments: [...], total, sum_paise }

GET /api/v1/payments/dues
  Query: branch_id, sort_by, page, limit
  Response: { dues: [...], total, total_due_paise }

GET /api/v1/payments/:id/receipt
  Response: application/pdf file download

POST /api/v1/payments/:id/void
  Body: { reason }
  Response 200: { voided: true, balance_restored_paise }
  Error 403: VOID_TOO_OLD (> 90 days)
```

---

## 10. Database Schema

```sql
CREATE TABLE payments (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  member_id       UUID NOT NULL REFERENCES members(id),
  branch_id       UUID NOT NULL REFERENCES branches(id),
  plan_id         UUID REFERENCES membership_plans(id),
  subscription_id UUID REFERENCES subscriptions(id),
  receipt_number  VARCHAR(20) NOT NULL UNIQUE,
  amount_paise    INTEGER NOT NULL CHECK (amount_paise > 0),
  gst_paise       INTEGER NOT NULL DEFAULT 0,
  total_paise     INTEGER NOT NULL,  -- amount + gst
  method          VARCHAR(20) NOT NULL,  -- cash|upi|card|bank|cheque
  reference       VARCHAR(100),
  payment_date    DATE NOT NULL,
  notes           TEXT,
  status          VARCHAR(20) NOT NULL DEFAULT 'active',  -- active | voided
  voided_at       TIMESTAMPTZ,
  void_reason     TEXT,
  voided_by       UUID REFERENCES staff(id),
  collected_by    UUID REFERENCES staff(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_payments_member ON payments(member_id, payment_date DESC);
CREATE INDEX idx_payments_branch ON payments(branch_id, payment_date DESC);
CREATE INDEX idx_payments_date   ON payments(tenant_id, payment_date);
```

---

## 11. Validation Rules

| Field | Rule |
|---|---|
| Amount | > 0; max 999,999 INR |
| Method | One of: cash, upi, card, bank, cheque |
| Reference | Required if method is upi, card, bank, or cheque |
| Date | Not future; not more than 180 days in the past |

---

## 12. Access Control

| Role | Collect fee | View history | View dues | Void payment |
|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes |
| Accountant | Yes | Yes | Yes | Yes |
| Receptionist | Yes | Yes | Yes | No |
| Branch manager | Yes (own branch) | Yes (own branch) | Yes (own branch) | No |
| Trainer / POS | No | No | No | No |
