# GymOS — Locker Module
# Complete Specification

**URL:** `/lockers`
**Access:** Gym owner, Branch manager, Receptionist (assign/reassign only)
**Purpose:** Manage gym lockers — track availability, assign lockers to members, reassign when needed, and view the full usage history of each locker.

---

## Table of Contents

1. [Locker List (Main Page)](#1-locker-list-main-page)
2. [Add Locker](#2-add-locker)
3. [Assign Locker to Member](#3-assign-locker-to-member)
4. [Reassign Locker](#4-reassign-locker)
5. [Release Locker](#5-release-locker)
6. [View Locker Details & History](#6-view-locker-details--history)
7. [Edit Locker](#7-edit-locker)
8. [Delete Locker](#8-delete-locker)
9. [API Endpoints](#9-api-endpoints)
10. [Database Schema](#10-database-schema)
11. [Validation Rules](#11-validation-rules)
12. [Access Control](#12-access-control)

---

## 1. Locker List (Main Page)

**URL:** `/lockers`
**How to reach it:** Open **Lockers** in the sidebar.

### 1.1 Page Layout

```
┌──────────────────────────────────────────────────────────────────┐
│  Lockers                              [+ Add locker]             │
├──────────────────────────────────────────────────────────────────┤
│  [Total: 60]  [Available: 24]  [Occupied: 34]  [Inactive: 2]    │
├──────────────────────────────────────────────────────────────────┤
│  🔍 Search locker no. / member   Availability ▾   Status ▾       │
├──────────────────────────────────────────────────────────────────┤
│  Locker table (desktop) / Card list (mobile)                     │
└──────────────────────────────────────────────────────────────────┘
```

### 1.2 Overview Cards (Top Row)

| Card | Calculation |
|---|---|
| **Total** | All lockers registered at this branch |
| **Available** | Lockers with no active assignment (`availability = available`) |
| **Occupied** | Lockers with an active member assignment (`availability = occupied`) |
| **Inactive** | Lockers marked inactive (out of service, under repair, etc.) |

Cards update immediately after any assign, release, or status change.

### 1.3 Filters and Search

| Filter | Options |
|---|---|
| **Search** | Locker number or assigned member name / phone |
| **Availability** | All / Available / Occupied |
| **Status** | All / Active / Inactive |

### 1.4 Locker Table Columns (Desktop)

| Column | Notes |
|---|---|
| **Locker No.** | Locker identifier e.g. L-01, L-02, A-101 |
| **Availability** | Colour badge: Green = Available, Amber = Occupied |
| **Assigned to** | Member name (if occupied) with reassign icon — or an **Assign** button (if available) |
| **Assigned since** | Date the current member was assigned (shown only if occupied) |
| **Status** | Active (green) / Inactive (grey) |
| **Actions** | View history (eye icon), Edit (pencil icon), Delete (trash icon) |

### 1.5 "Assigned to" Column Behaviour

This column behaves differently based on availability:

**If Available (no current member):**
```
[ + Assign ]   ← clickable button, opens Assign member dialog
```

**If Occupied:**
```
  Arjun Sharma
  [⇄ Reassign]  ← icon/link, opens Reassign dialog
```

### 1.6 Locker Card (Mobile)

Each card shows:
- Locker No. (large, bold)
- Availability badge (Available / Occupied)
- Assigned to: member name or "— Unassigned —"
- Status badge (Active / Inactive)
- Tap card → opens Locker Details & History panel
- Assign / Reassign button on the card

---

## 2. Add Locker

**Registers a new locker in the system.**

### 2.1 Navigation Flow

```
Lockers → Click + Add locker
  → Drawer / dialog opens
    → Fill in fields
      → Click Save
```

### 2.2 Add Locker Form Fields

| Field | Required | Notes |
|---|---|---|
| **Locker No.** | Yes | Unique identifier e.g. L-01, A-101, 42. Max 20 chars. |
| **Location / Zone** | No | e.g. Male changing room, Ground floor, Zone A |
| **Status** | Yes | Active (default) / Inactive |
| **Notes** | No | Extra details e.g. "Near entrance", "Lock replaced Jun 2026" |

> Availability is always **Available** when a locker is first added. It becomes Occupied only when a member is assigned.

### 2.3 Validation

- Locker No. is required and must be **unique within the branch**
- If Locker No. is blank: "Please enter a locker number"
- If Locker No. already exists: "Locker number already exists. Please use a different number."

### 2.4 What Happens After Saving

- New locker appears in the table immediately with status Available
- Overview card counts update
- Locker is ready to be assigned to a member

---

## 3. Assign Locker to Member

**Links an available locker to a member.**

### 3.1 How to Trigger

- Click the **+ Assign** button in the "Assigned to" column on any Available locker row
- Or from the Locker Details panel: click **Assign member**

### 3.2 Assign Dialog

```
┌─────────────────────────────────────────────┐
│  Assign Locker — L-07                       │
├─────────────────────────────────────────────┤
│  Member                                     │
│  🔍 Search by name / phone / member ID      │
│                                             │
│  From date    [Today ▾]                     │
│  To date      [Optional ▾]                  │
│  Notes        [________________]            │
│                                             │
│  [Assign]   [Cancel]                        │
└─────────────────────────────────────────────┘
```

### 3.3 Assign Form Fields

| Field | Required | Notes |
|---|---|---|
| **Member** | Yes | Search by name, phone (10 digits), or member ID. Active members only. |
| **From date** | Yes | Default today. Cannot be in the past. |
| **To date** | No | Optional end date e.g. membership end date. If left blank, assignment is open-ended. |
| **Notes** | No | e.g. "Temporary assignment", "Monthly locker plan" |

### 3.4 Validation

- Member is required — cannot assign without selecting one
- A member can only have **one active locker** at a time. If the selected member already has an assigned locker, show: "This member already has locker [X] assigned. Reassign that locker first or choose a different member."
- From date cannot be in the past
- To date must be after From date if provided

### 3.5 What Happens After Assigning

- Locker availability changes to **Occupied**
- "Assigned to" column shows the member name with Reassign option
- A `locker_assignments` record is created with `from_date`, `to_date`, `member_id`
- Overview card counts update (Available ↓1, Occupied ↑1)

---

## 4. Reassign Locker

**Moves an occupied locker from the current member to a new member without manual release.**

### 4.1 How to Trigger

- Click the **⇄ Reassign** icon/link next to the member name in the "Assigned to" column
- Or from the Locker Details panel: click **Reassign**

### 4.2 Reassign Dialog

```
┌─────────────────────────────────────────────┐
│  Reassign Locker — L-07                     │
├─────────────────────────────────────────────┤
│  Currently assigned to: Arjun Sharma        │
│  Since: 1 Mar 2026                          │
├─────────────────────────────────────────────┤
│  Assign to new member                       │
│  🔍 Search by name / phone / member ID      │
│                                             │
│  From date    [Today ▾]                     │
│  To date      [Optional ▾]                  │
│  Notes        [________________]            │
│                                             │
│  [Reassign]   [Cancel]                      │
└─────────────────────────────────────────────┘
```

### 4.3 Reassign Form Fields

| Field | Required | Notes |
|---|---|---|
| **New member** | Yes | Search by name, phone, or member ID. Cannot be the same as current member. |
| **From date** | Yes | Default today |
| **To date** | No | Optional end date |
| **Notes** | No | Reason for reassignment |

### 4.4 What Happens on Reassign

1. Current assignment record: `to_date` set to today (closed out)
2. New assignment record created for the new member
3. Locker remains **Occupied** — no gap in availability
4. History records both assignments with their date ranges
5. Previous member's locker entry in history shows the `to_date`

---

## 5. Release Locker

**Unlinks a locker from its current member — makes it Available again.**

### 5.1 How to Trigger

From the Locker Details panel → click **Release locker** button.

### 5.2 Release Confirmation Dialog

```
┌──────────────────────────────────────────────────┐
│  Release Locker L-07?                            │
│                                                  │
│  Currently assigned to: Arjun Sharma             │
│  This will make the locker available for others. │
│                                                  │
│  [Release]   [Cancel]                            │
└──────────────────────────────────────────────────┘
```

No name-typing required — simple confirmation is sufficient.

### 5.3 What Happens on Release

- Current assignment record: `to_date` set to today
- Locker availability changes to **Available**
- "Assigned to" column shows **+ Assign** button again
- Overview card counts update (Occupied ↓1, Available ↑1)
- History record preserved with the full date range

---

## 6. View Locker Details & History

**Opens a side panel showing all details for a locker plus the complete history of members who have used it.**

### 6.1 How to Trigger

- Desktop: Click the **eye icon** (👁) in the Actions column of any locker row
- Mobile: Tap the locker card

### 6.2 Details Panel Layout

```
┌────────────────────────────────────────────────┐
│  Locker L-07                         [✕ Close] │
│  Zone A — Male changing room                   │
│  ● Occupied                                    │
├────────────────────────────────────────────────┤
│  Currently assigned to                         │
│  Arjun Sharma  (MEM-00147)                     │
│  Since: 1 Mar 2026   To: —                     │
│  [⇄ Reassign]   [Release locker]               │
├────────────────────────────────────────────────┤
│  Locker details                    [Edit]      │
│  Location: Zone A — Male changing room         │
│  Status:   Active                              │
│  Notes:    Lock replaced Jun 2026              │
├────────────────────────────────────────────────┤
│  Usage History                                 │
│                                                │
│  Member            From        To       Days   │
│  Arjun Sharma      1 Mar 2026  —        114    │  ← current
│  Priya Nair        10 Jan 2026 28 Feb   49     │
│  Karthik Raj       5 Nov 2025  9 Jan    65     │
│  Meena S.          12 Aug 2025 4 Nov    84     │
└────────────────────────────────────────────────┘
```

### 6.3 Locker Details Section

| Field | Notes |
|---|---|
| Locker No. | e.g. L-07 |
| Location / Zone | e.g. Zone A — Male changing room |
| Availability | Occupied (amber) / Available (green) |
| Status | Active / Inactive |
| Notes | Extra details |

### 6.4 Currently Assigned section (shown only when Occupied)

| Field | Notes |
|---|---|
| Member name + ID | Clickable link to member profile |
| Assigned since | From date of current assignment |
| To date | End date if set, or "—" if open-ended |
| Duration so far | Number of days since assigned (calculated) |
| Actions | Reassign button, Release locker button |

### 6.5 Usage History Table

Shows **all past and current assignments** for this locker, ordered by most recent first.

| Column | Notes |
|---|---|
| **Member** | Member name. Clickable to member profile. |
| **From** | Assignment start date (dd MMM yyyy) |
| **To** | Assignment end date (dd MMM yyyy), or "—" if current/open-ended |
| **Days** | Number of days held. If current: days elapsed so far (live count). |

**Empty state:** "No usage history yet. Assign a member to start tracking."

### 6.6 Actions in the Panel

| Action | Behaviour |
|---|---|
| **Reassign** | Opens Reassign dialog (see §4) |
| **Release locker** | Opens Release confirmation (see §5) |
| **Edit** | Switches locker details section to edit mode |
| **Assign member** | Shown only when Available — opens Assign dialog (see §3) |
| **Close (✕)** | Closes the side panel |

---

## 7. Edit Locker

**Update a locker's number, location, status, or notes.**

### 7.1 Navigation Flow

```
Locker Details panel → Click Edit
  → Fields become editable
    → Make changes
      → Save Changes   or   Cancel
```

### 7.2 Edit Form Fields

| Field | Required | Notes |
|---|---|---|
| **Locker No.** | Yes | Must remain unique in this branch |
| **Location / Zone** | No | |
| **Status** | Yes | Active / Inactive |
| **Notes** | No | |

> Changing status to **Inactive** on an occupied locker: show warning — "This locker is currently assigned to [member name]. Release the locker before marking it inactive." Block save until released.

### 7.3 What Happens After Saving

- Updated details reflect immediately in the table and details panel
- Assignment history is not affected

---

## 8. Delete Locker

**Permanently removes a locker and all its assignment history.**

### 8.1 How to Trigger

Click the **trash icon** in the Actions column of the locker row.

### 8.2 Confirmation Dialog

| Element | Content |
|---|---|
| **Title** | Are you sure? |
| **Message** | "This will permanently delete locker "[Locker No.]" and its entire usage history. This cannot be undone." |
| **Buttons** | Delete (red) / Cancel |

> **Cannot delete an occupied locker.** If locker is currently assigned, trash icon is disabled with tooltip: "Release this locker before deleting."

### 8.3 What Happens After Delete

- Locker removed from the list immediately
- All `locker_assignments` records for this locker are permanently deleted
- Overview card counts update

---

## 9. API Endpoints

```
-- Locker CRUD

GET /api/v1/lockers
  Query: search, availability, status, page, limit
  -- branch_id injected from auth token
  Response: {
    lockers: [...],
    total,
    summary: { total, available, occupied, inactive }
  }

POST /api/v1/lockers
  Body: { locker_number, location?, status?, notes? }
  Response 201: { locker_id }
  Error 409: LOCKER_NUMBER_EXISTS

GET /api/v1/lockers/:id
  Response: {
    locker: { id, locker_number, location, availability,
              status, notes },
    current_assignment: { member_id, member_name, from_date,
                          to_date, days_so_far } | null,
    history: [{ member_name, member_id, from_date, to_date, days }]
  }

PUT /api/v1/lockers/:id
  Body: partial update
  Response 200: { locker }
  Error 409: LOCKER_NUMBER_EXISTS
  Error 422: CANNOT_DEACTIVATE_OCCUPIED_LOCKER

DELETE /api/v1/lockers/:id
  Response 200: { deleted: true }
  Error 422: CANNOT_DELETE_OCCUPIED_LOCKER


-- Locker Assignments

POST /api/v1/lockers/:id/assign
  Body: { member_id, from_date, to_date?, notes? }
  Response 201: { assignment_id }
  Error 422: MEMBER_ALREADY_HAS_LOCKER { locker_number }
  Error 422: LOCKER_NOT_AVAILABLE

POST /api/v1/lockers/:id/reassign
  Body: { new_member_id, from_date, to_date?, notes? }
  Response 200: {
    closed_assignment_id,
    new_assignment_id
  }
  Error 422: SAME_MEMBER_AS_CURRENT
  Error 422: MEMBER_ALREADY_HAS_LOCKER { locker_number }

POST /api/v1/lockers/:id/release
  Response 200: { released: true, to_date }
  Error 422: LOCKER_NOT_OCCUPIED
```

---

## 10. Database Schema

```sql
CREATE TABLE lockers (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  branch_id       UUID NOT NULL REFERENCES branches(id),
  locker_number   VARCHAR(20) NOT NULL,
  location        VARCHAR(200),
  availability    VARCHAR(20) NOT NULL DEFAULT 'available',
    -- available | occupied
  status          VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active | inactive
  notes           TEXT,
  created_by      UUID REFERENCES staff(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT uq_locker_number UNIQUE (tenant_id, branch_id, locker_number),
  CONSTRAINT chk_locker_availability
    CHECK (availability IN ('available', 'occupied')),
  CONSTRAINT chk_locker_status
    CHECK (status IN ('active', 'inactive'))
);

CREATE TABLE locker_assignments (
  id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  locker_id    UUID NOT NULL REFERENCES lockers(id) ON DELETE CASCADE,
  member_id    UUID NOT NULL REFERENCES members(id),
  tenant_id    UUID NOT NULL REFERENCES tenants(id),
  from_date    DATE NOT NULL,
  to_date      DATE,             -- NULL = currently active / open-ended
  notes        TEXT,
  assigned_by  UUID REFERENCES staff(id),
  released_by  UUID REFERENCES staff(id),
  created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT chk_locker_dates
    CHECK (to_date IS NULL OR to_date >= from_date)
);

-- Ensure only one active (open-ended) assignment per locker at a time
CREATE UNIQUE INDEX uq_active_locker_assignment
  ON locker_assignments (locker_id)
  WHERE to_date IS NULL;

-- Ensure one active locker per member at a time
CREATE UNIQUE INDEX uq_active_member_locker
  ON locker_assignments (member_id)
  WHERE to_date IS NULL;

CREATE INDEX idx_locker_branch     ON lockers(branch_id, availability);
CREATE INDEX idx_assignment_locker ON locker_assignments(locker_id, from_date DESC);
CREATE INDEX idx_assignment_member ON locker_assignments(member_id, from_date DESC);
```

---

## 11. Validation Rules

### Locker form

| Field | Rule |
|---|---|
| Locker No. | Required; 1–20 chars; unique per branch |
| Status | Active or Inactive |
| Deactivate occupied locker | Blocked — must release first |
| Delete occupied locker | Blocked — trash icon disabled |

### Assign / Reassign form

| Field | Rule |
|---|---|
| Member | Required; must be an active member |
| From date | Required; cannot be in the past |
| To date | Must be after from date if provided |
| One locker per member | A member cannot hold two lockers simultaneously |
| Reassign same member | Blocked — cannot reassign to the current member |

### Error messages

| Trigger | Message |
|---|---|
| Locker No. blank | "Please enter a locker number" |
| Duplicate locker No. | "Locker number already exists. Please use a different number." |
| Member already has a locker | "This member already has locker [X] assigned. Reassign that locker first." |
| Delete occupied | Trash icon disabled — tooltip: "Release this locker before deleting." |
| Deactivate occupied | "This locker is currently assigned to [member name]. Release the locker before marking it inactive." |

---

## 12. Access Control

| Role | View list | Add locker | Assign / Reassign / Release | Edit locker | Delete locker |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | Yes |
| Branch manager | Yes | Yes | Yes | Yes | Yes |
| Receptionist | Yes | No | Yes | No | No |
| Trainer | Yes (view only) | No | No | No | No |
| Accountant | Yes (view only) | No | No | No | No |
