# GymOS — Gym Owner Portal
# Module 03: Renewals Due

**URL:** `/renewals`
**Access:** Gym owner, Branch manager (own branch), Receptionist (own branch)
**Purpose:** Proactive renewal management. Shows all members whose membership is expiring soon or has already expired. Enables one-click renewals and reminders.

---

## 1. Page Layout

```
┌──────────────────────────────────────────────────────────────────┐
│  [Expired: 54]  [Today: 8]  [7 days: 37]  [30 days: 112]        │
├──────────────────────────────────────────────────────────────────┤
│  [All] [Expired] [Today] [3 days] [7 days] [30 days] [Custom]   │
├──────────────────────────────────────────────────────────────────┤
│  Plan ▾  Branch ▾  Payment status ▾      [Bulk actions ▾] [CSV]  │
├──────────────────────────────────────────────────────────────────┤
│  Renewals table                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 2. Stats Cards

| Card | Calculation |
|---|---|
| **Expired** | `subscription_end_date < TODAY` and no new active subscription |
| **Today** | `end_date = TODAY` |
| **7 days** | `end_date BETWEEN TOMORROW AND TODAY+7` |
| **30 days** | `end_date BETWEEN TOMORROW AND TODAY+30` |

Clicking a card activates the corresponding filter tab.

**Sidebar badge:** `COUNT(expired) + COUNT(today) + COUNT(7 days)`. Refreshed every 5 minutes via polling, or immediately on any renewal action.

---

## 3. Filter Tabs

| Tab | Members shown |
|---|---|
| All | All due for renewal (expired + expiring in 30 days) |
| Expired | `end_date < TODAY` |
| Today | `end_date = TODAY` |
| 3 days | `end_date BETWEEN TOMORROW AND TODAY+3` |
| 7 days | `end_date BETWEEN TOMORROW AND TODAY+7` |
| 30 days | `end_date BETWEEN TOMORROW AND TODAY+30` |
| Custom | Date range picker on `end_date` |

---

## 4. Additional Filters

| Filter | Options |
|---|---|
| **Plan** | Dropdown of all plans. Filters by current active plan. |
| **Branch** | All / specific branch |
| **Payment status** | All / Paid (renewed) / Unpaid / Partial |

---

## 5. Renewals Table Columns

| Column | Notes |
|---|---|
| Checkbox | For bulk actions |
| **Name + avatar** | Clickable to member profile |
| **Member ID** | e.g. MEM-00147 |
| **Phone** | Tap to WhatsApp on mobile |
| **Current plan** | Plan name + duration |
| **Expiry date** | Red text if past. "Today" badge if today. Green if future. |
| **Status** | "12 days overdue" (red) or "5 days left" (amber) |
| **Last payment** | Amount + date |
| **Balance due** | Outstanding amount in Rs. |
| **Actions** | Renew now, Send reminder, View profile |

Default sort: most overdue first (expired before expiring, then by days overdue descending).

---

## 6. Renew Now Modal

| Field | Default | Validation |
|---|---|---|
| **Plan** | Current plan | Dropdown of active plans |
| **Start date** | Day after expiry (or today if expired) | Cannot be > 7 days past |
| **Duration** | From selected plan | Display only |
| **New end date** | Auto-calculated | Display only |
| **Price** | Plan price | Editable for discounts (requires manager/owner permission) |
| **Payment collected** | Plan price | Editable; 0 = balance recorded as due |
| **Payment method** | — | Cash / UPI / Card / Bank / Cheque |
| **Reference** | — | Required for non-cash methods |
| **Notes** | — | Max 300 chars |

**On submit:**
1. Set current subscription `status = ended`
2. Create new subscription record
3. If payment > 0: create payment record + generate receipt
4. Update `members.status = active`
5. Send renewal confirmation to member (if notifications enabled)

---

## 7. Send Reminder

- Pre-filled message template shown (editable in Notifications > Settings)
- Variables populated: `{member_name}`, `{gym_name}`, `{expiry_date}`, `{plan_name}`, `{renewal_amount}`
- Channel: WhatsApp (if connected), SMS, or both
- Character count shown for SMS (160 char limit per message)
- One-click send
- Logged in `renewal_reminders` table: member_id, channel, message, status, sent_by, sent_at
- "Sent X ago" replaces reminder button after sending

---

## 8. Bulk Actions

Select 2+ members via checkboxes. Bulk action dropdown:

| Action | Behaviour |
|---|---|
| **Send bulk reminder** | Same template sent to all selected. Progress indicator ("Sent 12/45"). |
| **Export selected** | CSV of selected members only |
| **Mark as contacted** | Logs a "contacted" note per member without sending a message |

---

## 9. Automated Reminders

Configurable in Notifications > Settings. Runs daily at **9:00 AM IST** via scheduled job.

| Trigger | Default channel | Configurable? |
|---|---|---|
| 7 days before expiry | WhatsApp + SMS | Yes — toggle + template |
| 3 days before expiry | WhatsApp + SMS | Yes |
| Day of expiry | WhatsApp + SMS | Yes |
| 3 days after expiry | WhatsApp + SMS | Yes |

---

## 10. API Endpoints

```
GET /api/v1/renewals
  Query: tab (expired|today|3days|7days|30days|custom), from, to,
         plan_id, branch_id, payment_status, page, limit
  Response: { members: [...], total,
              stats: { expired, today, seven_days, thirty_days } }

GET /api/v1/renewals/stats
  Response: { expired, today, seven_days, thirty_days, sidebar_badge_count }

POST /api/v1/renewals/:memberId/renew
  Body: { plan_id, start_date, price_paise, payment_amount_paise,
          payment_method, reference?, notes? }
  Response 201: { subscription_id, payment_id?, receipt_url? }

POST /api/v1/renewals/:memberId/remind
  Body: { channel (whatsapp|sms|both), message }
  Response 200: { sent: true, reminder_id }
```

---

## 11. Database Schema

```sql
CREATE TABLE renewal_reminders (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  member_id   UUID NOT NULL REFERENCES members(id),
  tenant_id   UUID NOT NULL REFERENCES tenants(id),
  channel     VARCHAR(20) NOT NULL,    -- whatsapp | sms | email
  message     TEXT NOT NULL,
  status      VARCHAR(20) DEFAULT 'sent',  -- sent | failed | pending
  sent_by     UUID REFERENCES staff(id),   -- NULL if automated
  sent_at     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  is_auto     BOOLEAN NOT NULL DEFAULT false
);

CREATE INDEX idx_reminders_member ON renewal_reminders(member_id, sent_at DESC);
```

---

## 12. Notifications

- Member gets renewal confirmation receipt on successful renewal
- Owner/manager gets inbox notification for each renewal
- Daily digest (configurable): summary of renewals due + completed renewals today
