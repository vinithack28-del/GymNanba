# GymOS — Gym Owner Portal
# Module 13: Notifications

**URL:** `/notifications` (inbox), `/notifications/settings` (configuration)
**Access:** Inbox — all staff (own relevant notifications); Settings — gym owner only
**Purpose:** (a) Notification inbox for gym owner and staff. (b) Configure automated messages sent to members.

---

## 1. Inbox Layout

```
┌──────────────────────────────────────────────────────┐
│  Notifications  (12 unread)         [Mark all read]  │
├──────────────────────────────────────────────────────┤
│  [All] [Unread] [Renewals] [Payments] [System]       │
├──────────────────────────────────────────────────────┤
│  Notification list (sorted newest first)             │
└──────────────────────────────────────────────────────┘
```

Each notification item:
- Type icon (colour-coded by category)
- Title (bold)
- Body text (2 lines, truncated)
- Timestamp ("3 hours ago" format)
- Unread dot (blue, left edge)
- Dismiss (×) button

Click notification → navigate to related record.

---

## 2. Internal Notification Types

| Type | Icon colour | Trigger |
|---|---|---|
| **Renewal due** | Amber | Member expires in 7 / 3 / 1 day |
| **Renewal overdue** | Red | Member expired 3 days ago, not yet renewed |
| **Payment received** | Green | Any payment above configurable threshold |
| **New member** | Blue | New member registered |
| **Low stock** | Amber | POS product stock ≤ threshold |
| **Class cancelled** | Red | A scheduled class was cancelled |
| **Staff new login** | Grey | Staff logged in from unrecognised device/location |
| **System alert** | Blue | Broadcast from super admin |

---

## 3. Member Notification Settings (`/notifications/settings`)

### Section 1: Member Notifications

Automated messages sent to members. For each event:

| Event | Default channels | Default timing | Template editable? |
|---|---|---|---|
| Expiry reminder | WhatsApp + SMS | 7 days, 3 days, 1 day before | Yes |
| Membership expired | WhatsApp + SMS | Day of + 3 days after | Yes |
| Renewal confirmation | WhatsApp + SMS + Email | On renewal | Yes |
| Payment receipt | WhatsApp + SMS + Email | On payment | Yes |
| Birthday wish | WhatsApp + SMS | On birthday | Yes |
| Class booking confirmation | WhatsApp + SMS | On booking | Yes |
| Class cancellation | WhatsApp + SMS | On cancel | Yes |
| Walk-in receipt | WhatsApp + SMS | On walk-in payment | Yes |

### Section 2: Internal Alerts

Which events create inbox notifications for the owner. Each has:
- Enable/disable toggle
- Configurable threshold where applicable (e.g. "Alert me when renewals due > 10")
- Daily digest email option (9:00 AM summary of renewals, new members, revenue)

---

## 4. Message Templates

Each template has:

| Field | Notes |
|---|---|
| Channel | WhatsApp / SMS / Email |
| Subject | Email only |
| Body | Textarea with variable picker |
| Status | Draft (not used for sends) / Published (active) |

### Available Variables

`{member_name}` `{gym_name}` `{expiry_date}` `{plan_name}` `{renewal_amount}` `{payment_amount}` `{receipt_number}` `{class_name}` `{class_time}` `{trainer_name}` `{balance_due}`

### Constraints

| Channel | Body limit |
|---|---|
| SMS | 160 chars per message. Counter shows: "142/160 (1 message)" or "180/160 (2 messages)" |
| WhatsApp | 1,024 chars |
| Email | Unlimited |

**Preview panel:** Renders template with sample data. Shows exactly what the member will receive.

**Publish validation:** Cannot publish a template with missing required variables.

---

## 5. WhatsApp Integration Note

If WhatsApp Business API is connected (via Settings > Integrations):
- Template approval status shown per template: Approved ✓ / Pending ⏳ / Rejected ✗
- Only approved templates can be sent via WhatsApp
- Rejected templates show rejection reason from Meta
- Link shown: "Configure WhatsApp → Settings > Integrations"

---

## 6. Notification Log (`/notifications/log`)

Full history of all automated notifications sent to members.

**Table:** Sent at | Recipient name | Event | Channel | Status | Message preview (first 50 chars)

**Filters:** Date range, event type, channel, status (Sent / Failed / Pending)

**Status meanings:**
- **Sent:** Message delivered to carrier/WhatsApp
- **Failed:** Delivery failed (error reason shown on row expand)
- **Pending:** Queued, not yet attempted

---

## 7. API Endpoints

```
GET /api/v1/notifications
  Query: read (all|unread), type, page, limit
  Response: { notifications: [...], unread_count }

POST /api/v1/notifications/:id/read
  Response 200: { read: true }

POST /api/v1/notifications/read-all
  Response 200: { marked_read: N }

GET /api/v1/notifications/settings
  Response: { member_notifications: [...], internal_alerts: [...] }

PUT /api/v1/notifications/settings
  Body: { member_notifications: [...], internal_alerts: [...] }
  Response 200: { saved: true }

GET /api/v1/notifications/templates
  Response: { templates: [...] }

PUT /api/v1/notifications/templates/:id
  Body: { subject?, body, status (draft|published) }
  Response 200: { template }
  Error 422: MISSING_REQUIRED_VARIABLES

GET /api/v1/notifications/log
  Query: from, to, event, channel, status, page, limit
  Response: { log: [...], total }
```

---

## 8. Database Schema

```sql
CREATE TABLE notifications (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id   UUID NOT NULL REFERENCES tenants(id),
  staff_id    UUID REFERENCES staff(id),  -- NULL = shown to all staff
  type        VARCHAR(50) NOT NULL,
  title       VARCHAR(200) NOT NULL,
  body        TEXT NOT NULL,
  link        TEXT,  -- relative URL to navigate on click
  is_read     BOOLEAN NOT NULL DEFAULT false,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE notification_templates (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id   UUID NOT NULL REFERENCES tenants(id),
  event       VARCHAR(50) NOT NULL,
  channel     VARCHAR(20) NOT NULL,  -- whatsapp | sms | email
  subject     TEXT,                  -- email only
  body        TEXT NOT NULL,
  status      VARCHAR(20) NOT NULL DEFAULT 'draft',  -- draft | published
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT uq_template UNIQUE (tenant_id, event, channel)
);

CREATE TABLE notification_log (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id   UUID NOT NULL REFERENCES tenants(id),
  member_id   UUID REFERENCES members(id),
  event       VARCHAR(50) NOT NULL,
  channel     VARCHAR(20) NOT NULL,
  message     TEXT NOT NULL,
  status      VARCHAR(20) NOT NULL DEFAULT 'pending',
  error_msg   TEXT,
  sent_at     TIMESTAMPTZ,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_notif_tenant    ON notifications(tenant_id, created_at DESC);
CREATE INDEX idx_notif_log_member ON notification_log(member_id, created_at DESC);
```

---

## 9. Validation Rules

| Rule | Detail |
|---|---|
| SMS body | Max 160 chars per message segment |
| WhatsApp body | Max 1,024 chars |
| Email body | No limit |
| Required variables | All `{variable}` placeholders must be in the approved variable list |
| Publish | All required variables for the event must be present in body |

---

## 10. Access Control

| Role | View inbox | Settings | Templates | Log |
|---|---|---|---|---|
| Gym owner | Yes (all) | Yes | Yes | Yes |
| Branch manager | Yes (own) | No | No | No |
| Receptionist | Yes (own) | No | No | No |
| Accountant | Yes (own) | No | No | No |
| Trainer | Yes (own) | No | No | No |
| POS staff | Yes (own) | No | No | No |
