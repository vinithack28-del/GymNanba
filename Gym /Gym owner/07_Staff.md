# GymOS — Gym Owner Portal
# Module 07: Staff

**URL:** `/staff`
**Sub-pages:** `/staff` (list), `/staff/roles`, `/staff/attendance`
**Access:** Gym owner (full), Branch manager (view own branch, limited edit)
**Purpose:** Manage all staff across all branches — their accounts, roles, permissions, and attendance.

---

## 1. Staff List Layout

```
┌───────────────────────────────────────────────────────────────┐
│  Staff (42)   Role ▾  Branch ▾  Status ▾   🔍 Search  [+ Add] │
├───────────────────────────────────────────────────────────────┤
│  Staff table                                                  │
└───────────────────────────────────────────────────────────────┘
```

**Table columns:** Name + avatar | Role badge | Branch | Phone | Email | Join date | Status | Last login | Actions (View, Edit, Deactivate, Delete)

- Last login shown as "2 hours ago". Red if > 30 days.
- Row click opens staff profile page

---

## 2. Add Staff Form

| Field | Validation | Required |
|---|---|---|
| **Full name** | 2–100 chars | Yes |
| **Phone** | E.164, unique within gym | Yes |
| **Email** | RFC 5322, unique within gym (used as login) | Yes |
| **Role** | Receptionist / Trainer / Accountant / POS / Branch manager | Yes |
| **Branch** | Active branches | Yes |
| **Salary (INR/month)** | Optional, used for expense tracking | No |
| **Join date** | Cannot be future | Yes |
| **ID proof type** | Aadhaar / PAN / Passport | No |
| **ID proof upload** | JPG/PNG/PDF, max 5 MB | No |
| **Profile photo** | JPG/PNG, max 2 MB | No |

**On submit:**
- Create `staff` record
- Create login credentials (email + auto-generated 12-char temporary password)
- Send welcome email with login URL and temporary password
- Staff must change password on first login

---

## 3. Role Access Matrix

| Module / Action | Receptionist | Trainer | Accountant | POS Staff | Branch Manager |
|---|---|---|---|---|---|
| Dashboard | View | Limited | View | Limited | Own branch |
| Members — view | Yes | Own clients | Yes | No | Yes (branch) |
| Members — add | Yes | No | No | No | Yes |
| Members — edit | No | No | No | No | Yes |
| Members — delete | No | No | No | No | No |
| Memberships/Plans | View | No | View | No | View |
| Renewals due | Yes | No | Yes | No | Yes |
| Attendance — check-in | Yes | No | No | No | Yes |
| Attendance — view log | Yes | No | Yes | No | Yes |
| Classes — view timetable | Yes | Yes | No | No | Yes |
| Classes — create/edit | No | Own classes | No | No | Yes |
| Classes — book for member | Yes | Yes | No | No | Yes |
| Branches | No | No | No | No | View (own) |
| Staff | No | No | No | No | View (own branch) |
| Payments — collect | Yes | No | Yes | No | Yes |
| Payments — history | Yes | No | Yes | No | Yes |
| Payments — void | No | No | Yes | No | No |
| Invoices | No | No | Yes | No | Yes |
| POS — billing | No | No | No | Yes | Yes |
| POS — products/stock | No | No | No | View | Yes |
| Expenses | No | No | Yes | No | Yes (branch) |
| Reports | No | Own data only | Revenue only | No | Branch only |
| Notifications — inbox | Yes | Yes | Yes | Yes | Yes |
| Notifications — settings | No | No | No | No | No |
| Settings | No | No | No | No | No |

---

## 4. Roles & Permissions Page (`/staff/roles`)

- Visual matrix: roles (rows) × modules/actions (columns)
- Toggle each cell on/off
- Gym owner can customise permissions per role
- Changes apply immediately to all staff with that role
- "Reset to defaults" button per role
- All permission changes logged in `owner_audit_log`

---

## 5. Staff Profile Page (`/staff/:id`)

Tabs: **Details** | **Login activity** | **Attendance** | **Documents**

- **Details:** All staff fields, editable by owner
- **Login activity:** Last 10 logins with IP address, device, location, timestamp
- **Attendance:** Monthly summary — days present, days absent, total hours worked
- **Documents:** Uploaded ID proof, employment contract

---

## 6. Staff Deactivation & Deletion

### Deactivate
- Sets `staff.status = inactive`
- Login blocked immediately — all active sessions revoked
- Data retained: all payments, check-ins, classes, walk-ins remain linked to this staff member
- Receptionist at reception cannot mark them as the collector for new payments

### Delete
- Only allowed if staff has zero associated records (no payments, check-ins, classes, walk-ins logged)
- If records exist: soft delete only — sets `deleted_at`, data retained
- Confirmation: type staff member's name to confirm
- Deactivated staff can be deleted if no records exist

---

## 7. Staff Attendance Sub-page (`/staff/attendance`)

| Filter | Options |
|---|---|
| Date range | Default current month |
| Branch | All / specific |
| Staff | All / specific staff member |

**Table columns:** Date | Staff name | Role | Check-in | Check-out | Hours worked | Source (QR/Manual)

- **Manual entry:** Owner/manager can add attendance for any past date; reason field required
- **Export CSV:** All columns, all rows matching filters
- **Monthly summary:** Total days present, total hours, leaves marked

---

## 8. API Endpoints

```
GET /api/v1/staff
  Query: role, branch_id, status, search, page, limit
  Response: { staff: [...], total }

POST /api/v1/staff
  Body: { name, phone, email, role, branch_id, salary_paise?, join_date }
  Response 201: { staff_id }

GET /api/v1/staff/:id
  Response: { full staff profile, login_history, attendance_summary }

PUT /api/v1/staff/:id
  Body: partial update
  Response 200: { staff }

POST /api/v1/staff/:id/deactivate
  Response 200: { deactivated: true }

DELETE /api/v1/staff/:id
  Response 200: { deleted: true }
  Error 409: STAFF_HAS_RECORDS

GET /api/v1/staff/roles
  Response: { roles: [{ role, permissions: { module: { action: bool } } }] }

PUT /api/v1/staff/roles/:role
  Body: { permissions: { module: { action: bool } } }
  Response 200: { updated: true }
```

---

## 9. Database Schema

```sql
CREATE TABLE staff (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  branch_id       UUID REFERENCES branches(id),
  name            VARCHAR(100) NOT NULL,
  phone           VARCHAR(20) NOT NULL,
  email           VARCHAR(255) NOT NULL,
  role            VARCHAR(30) NOT NULL,
  salary_paise    INTEGER,
  join_date       DATE NOT NULL,
  id_proof_url    TEXT,
  photo_url       TEXT,
  status          VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at      TIMESTAMPTZ,

  CONSTRAINT uq_staff_email UNIQUE (tenant_id, email),
  CONSTRAINT uq_staff_phone UNIQUE (tenant_id, phone),
  CONSTRAINT chk_role CHECK (role IN ('receptionist','trainer','accountant','pos','branch_manager'))
);

CREATE TABLE staff_role_permissions (
  tenant_id   UUID NOT NULL REFERENCES tenants(id),
  role        VARCHAR(30) NOT NULL,
  permissions JSONB NOT NULL,
  updated_by  UUID REFERENCES staff(id),
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  PRIMARY KEY (tenant_id, role)
);
```

---

## 10. Validation Rules

| Field | Rule |
|---|---|
| Email | RFC 5322 format; unique per tenant |
| Phone | E.164 format; unique per tenant |
| Role | Must be one of: receptionist, trainer, accountant, pos, branch_manager |
| Join date | Cannot be in the future |
| Salary | If provided, must be > 0 |

---

## 11. Access Control

| Role | Create staff | View staff | Edit staff | Delete staff | Scope |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | All branches |
| Branch manager | No | Yes | No | No | Own branch |
| All other roles | No | No | No | No | — |
