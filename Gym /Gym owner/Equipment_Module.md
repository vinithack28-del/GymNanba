# GymOS — Equipment Module
# Complete Specification

**Module type:** Add-on
**URL:** `/equipment`
**Access:** Gym owner, Branch manager
**Purpose:** Track gym equipment, its status, and maintenance history. Add, edit, delete equipment items and log service records for each piece of equipment.

---

## Table of Contents

1. [Equipment Overview (Main Page)](#1-equipment-overview-main-page)
2. [Add Equipment](#2-add-equipment)
3. [View Equipment Details](#3-view-equipment-details)
4. [Edit Equipment](#4-edit-equipment)
5. [Delete Equipment](#5-delete-equipment)
6. [Add Service Record](#6-add-service-record)
7. [API Endpoints](#7-api-endpoints)
8. [Database Schema](#8-database-schema)
9. [Validation Rules](#9-validation-rules)
10. [Access Control](#10-access-control)

---

## 1. Equipment Overview (Main Page)

**URL:** `/equipment`
**How to reach it:** Open **Equipment** in the sidebar.

### 1.1 Page Layout

```
┌──────────────────────────────────────────────────────────────────┐
│  Equipment                          [Add Equipment]              │
├──────────────────────────────────────────────────────────────────┤
│  [Total: 48]  [Operational: 41]  [Maintenance: 4]  [Broken: 3]  │
├──────────────────────────────────────────────────────────────────┤
│  🔍 Search...   Type ▾   Status ▾                                │
├──────────────────────────────────────────────────────────────────┤
│  Equipment table (desktop) / Card list (mobile)                  │
└──────────────────────────────────────────────────────────────────┘
```

### 1.2 Overview Cards (Top Row)

At the top of the screen four summary cards are shown:

| Card | What it shows |
|---|---|
| **Total Equipment** | Total number of equipment items registered |
| **Operational** | Items with status = Operational (working) |
| **Maintenance** | Items with status = Maintenance (under maintenance) |
| **Broken** | Items with status = Broken (need repair) |

Cards update immediately when any equipment is added, edited, or deleted.

### 1.3 Filters and Search

| Filter | Options |
|---|---|
| **Search** | Equipment name, brand, or model (real-time) |
| **Type** | All / Cardio / Strength / Free Weights / Functional / Other |
| **Status** | All / Operational / Maintenance / Broken |

### 1.4 Equipment Table (Desktop)

| Column | Notes |
|---|---|
| **Equipment Name** | Name of the item |
| **Type** | Cardio / Strength / Free Weights / Functional / Other |
| **Brand** | Brand name if entered |
| **Status** | Colour badge: Green=Operational, Amber=Maintenance, Red=Broken |
| **Location** | Where it is placed in the gym |
| **Purchase Date** | dd MMM yyyy if entered |
| **Warranty Expiry** | dd MMM yyyy; red text if expired |
| **Actions** | View (row click), Delete (trash icon) |

Default sort: equipment name ascending.

### 1.5 Equipment Card List (Mobile)

Each card shows: Equipment name, Type badge, Status badge, Brand, Location, and a trash icon for delete.

Tap card → opens details side panel from the bottom.

---

## 2. Add Equipment

**Registers new gym equipment in the system so it can be tracked and have maintenance logged.**

### 2.1 Navigation Flow

```
Equipment (sidebar)
  → Click Add Equipment (desktop) / Add (mobile)
    → Form opens
      → Fill in fields
        → Click Add Equipment to save
          or Cancel to discard
```

### 2.2 Form Fields

| Field | Required | Notes |
|---|---|---|
| **Equipment Name** | Yes | Name of the equipment |
| **Type** | Yes | Cardio / Strength / Free Weights / Functional / Other |
| **Brand** | No | Brand name |
| **Model** | No | Model name |
| **Purchase Date** | No | Date of purchase |
| **Warranty Expiry** | No | Warranty end date |
| **Purchase Price (₹)** | No | Cost of the equipment |
| **Status** | No | Operational (default) / Maintenance / Broken |
| **Location** | No | Where the equipment is placed in the gym |
| **Notes** | No | Extra details |

### 2.3 Validation

- **Equipment Name** and **Type** are required
- If either is missing on submit: inline error shown — **"Please fill in all required fields"**
- All other fields are optional

### 2.4 What Happens After Saving

- Equipment is added to the list immediately
- Form closes and resets (ready for next entry)
- New item appears in the overview cards and equipment table
- Status overview card counts update

---

## 3. View Equipment Details

**Opens a side panel with full details for an equipment item and its complete service history.**

### 3.1 Navigation Flow

```
Equipment table / card list
  → Desktop: Click the equipment row
  → Mobile: Tap the equipment card
    → Details panel opens
      → Desktop: slides in from the right
      → Mobile: slides up from the bottom
```

### 3.2 Details Panel Contents

#### Equipment Details section

| Field | Notes |
|---|---|
| Brand | Shown if entered |
| Model | Shown if entered |
| Type | Cardio / Strength / Free Weights / Functional / Other |
| Status | Badge: Operational / Maintenance / Broken |
| Purchase Date | dd MMM yyyy |
| Warranty Expiry | dd MMM yyyy; red if expired |
| Price | ₹ format |
| Location | Where it is placed |
| Notes | Extra details if entered |

#### Service History section

| Element | Detail |
|---|---|
| **Add service record** | Form to log a new maintenance, repair, inspection, calibration, cleaning, or replacement event |
| **Past services list** | All service records for this item: date, type, cost (₹), provider, notes |

### 3.3 Actions in the Details Panel

| Action | Behaviour |
|---|---|
| **Edit** | Switches the panel to edit mode — all fields become editable |
| **Add Service Record** | Opens the service record form within the panel |
| **Close** | Closes the panel (also: click outside panel, or use the × close control) |

> **Note:** On desktop the panel opens from the right; on mobile it opens from the bottom.

---

## 4. Edit Equipment

**Updates an existing equipment record. Any field can be changed — name, brand, model, type, status, location, purchase date, price, warranty, and notes.**

### 4.1 Navigation Flow

```
Equipment → Open equipment (click row / tap card)
  → In the details panel, click Edit
    → Fields become editable
      → Make changes
        → Click Save Changes to save
          or Cancel to discard
```

### 4.2 Edit Form Fields

| Field | Required | Notes |
|---|---|---|
| **Name** | Yes | Equipment name |
| **Brand** | No | Brand name |
| **Model** | No | Model name |
| **Type** | Yes | Equipment type |
| **Status** | Yes | Operational / Maintenance / Broken |
| **Location** | No | Where the equipment is placed |
| **Purchase Date** | No | Date of purchase |
| **Purchase Price (₹)** | No | Cost of the equipment |
| **Warranty Expiry** | No | Warranty end date |
| **Notes** | No | Extra details |

### 4.3 Edit Actions

| Button | Behaviour |
|---|---|
| **Save Changes** | Saves all updates and switches the panel back to view mode |
| **Cancel** | Discards all changes and switches back to view mode |

> **Note:** After saving, the equipment list and details panel immediately reflect the updated values. Service history is unchanged.

---

## 5. Delete Equipment

**Permanently removes an equipment record and all its service history. This action cannot be undone.**

### 5.1 Navigation Flow

```
Equipment table (desktop) → Click the trash icon on the row
Equipment card list (mobile) → Tap the trash icon on the card
  → Confirmation dialog opens
    → Click Delete to confirm permanent removal
      or Cancel to keep the item
```

### 5.2 Confirmation Dialog

| Element | Content |
|---|---|
| **Title** | Are you sure? |
| **Message** | "This action cannot be undone. This will permanently delete the equipment "[equipment name]" and all its service history." |
| **Buttons** | Delete (destructive, red) / Cancel |

> **Note:** After confirming, the equipment record and all its service records are permanently removed. Use delete only for items that are no longer in use or were added by mistake.

### 5.3 What Happens After Delete

- Equipment row/card removed from the list immediately
- Overview card counts (Total, Operational, Maintenance, Broken) update
- All service records linked to this equipment are also permanently deleted

---

## 6. Add Service Record

**Adds a maintenance or service entry for a selected equipment item. Use it to log repairs, inspections, calibrations, cleaning events, or replacements.**

### 6.1 Navigation Flow

```
Equipment → Open the equipment (click row / tap card)
  → Details panel opens
    → Scroll to Service History section
      → In Add Service Record, fill in the form
        → Click Add Service Record to save
```

> **Note:** Service records are tied to the selected equipment. Add them only when that equipment's details panel is open.

### 6.2 Service Record Form Fields

| Field | Required | Notes |
|---|---|---|
| **Service Date** | Yes | Date of the service; defaults to today |
| **Service Type** | Yes | Maintenance / Repair / Inspection / Calibration / Cleaning / Replacement |
| **Cost (₹)** | Yes | Cost of the service |
| **Service Provider** | No | Person or company who performed the work |
| **Notes** | No | Extra details about the service |

### 6.3 Validation

- **Service Date**, **Service Type**, and **Cost** are all required
- If any required field is missing on submit: **"Please fill in required service fields"** shown inline

### 6.4 What Happens After Saving

- Record added to the Service History list for this equipment immediately
- Form resets (ready for another entry without closing the panel)
- New record appears in the Service History section showing: date, type, cost (₹), provider, and notes

### 6.5 Service History List

Each past service record shows:

| Field | Notes |
|---|---|
| **Service Date** | dd MMM yyyy |
| **Service Type** | Maintenance / Repair / Inspection / Calibration / Cleaning / Replacement |
| **Cost** | ₹ format |
| **Service Provider** | Name if entered |
| **Notes** | Extra details if entered |

Records sorted: most recent service first.

---

## 7. API Endpoints

```
-- Equipment CRUD

GET /api/v1/equipment
  Query: search, type, status, page, limit
  Response: {
    equipment: [...],
    total,
    summary: { total, operational, maintenance, broken }
  }

POST /api/v1/equipment
  Body: {
    name,                    -- required
    type,                    -- required: cardio|strength|free_weights|functional|other
    brand?,
    model?,
    purchase_date?,
    warranty_expiry?,
    purchase_price_paise?,
    status?,                 -- default: operational
    location?,
    notes?
  }
  Response 201: { equipment_id }

GET /api/v1/equipment/:id
  Response: { equipment details + service_history: [...] }

PUT /api/v1/equipment/:id
  Body: partial update of any fields
  Response 200: { equipment }

DELETE /api/v1/equipment/:id
  Response 200: { deleted: true }
  -- Also deletes all service_records for this equipment


-- Service Records

GET /api/v1/equipment/:id/service-records
  Response: { records: [...] }

POST /api/v1/equipment/:id/service-records
  Body: {
    service_date,            -- required; defaults to today
    service_type,            -- required: maintenance|repair|inspection|
                             --           calibration|cleaning|replacement
    cost_paise,              -- required
    service_provider?,
    notes?
  }
  Response 201: { record_id }

DELETE /api/v1/equipment/:id/service-records/:recordId
  Response 200: { deleted: true }
```

---

## 8. Database Schema

```sql
CREATE TABLE equipment (
  id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id           UUID NOT NULL REFERENCES tenants(id),
  branch_id           UUID REFERENCES branches(id),
  name                VARCHAR(150) NOT NULL,
  type                VARCHAR(30) NOT NULL,
    -- cardio | strength | free_weights | functional | other
  brand               VARCHAR(100),
  model               VARCHAR(100),
  purchase_date       DATE,
  warranty_expiry     DATE,
  purchase_price_paise INTEGER,
  status              VARCHAR(20) NOT NULL DEFAULT 'operational',
    -- operational | maintenance | broken
  location            VARCHAR(200),
  notes               TEXT,
  created_by          UUID REFERENCES staff(id),
  created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT chk_equipment_status
    CHECK (status IN ('operational', 'maintenance', 'broken')),
  CONSTRAINT chk_equipment_type
    CHECK (type IN ('cardio', 'strength', 'free_weights', 'functional', 'other'))
);

CREATE TABLE equipment_service_records (
  id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  equipment_id      UUID NOT NULL REFERENCES equipment(id) ON DELETE CASCADE,
  tenant_id         UUID NOT NULL REFERENCES tenants(id),
  service_date      DATE NOT NULL,
  service_type      VARCHAR(30) NOT NULL,
    -- maintenance | repair | inspection | calibration | cleaning | replacement
  cost_paise        INTEGER NOT NULL CHECK (cost_paise >= 0),
  service_provider  VARCHAR(200),
  notes             TEXT,
  created_by        UUID REFERENCES staff(id),
  created_at        TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT chk_service_type
    CHECK (service_type IN (
      'maintenance', 'repair', 'inspection',
      'calibration', 'cleaning', 'replacement'
    ))
);

CREATE INDEX idx_equipment_tenant   ON equipment(tenant_id, status);
CREATE INDEX idx_equipment_branch   ON equipment(branch_id);
CREATE INDEX idx_service_equipment  ON equipment_service_records(equipment_id, service_date DESC);
```

---

## 9. Validation Rules

### Equipment form

| Field | Rule |
|---|---|
| Equipment Name | Required; 2–150 chars |
| Type | Required; must be one of the allowed values |
| Status | Operational (default) / Maintenance / Broken |
| Purchase Price | ≥ 0 if provided; stored in paise |
| Warranty Expiry | Must be a valid date; can be past (to indicate expired warranty) |
| Purchase Date | Must be a valid date; not in future |

### Service record form

| Field | Rule |
|---|---|
| Service Date | Required; defaults to today; not in future |
| Service Type | Required; must be one of the allowed values |
| Cost | Required; ≥ 0 (₹) |

### Error messages

| Trigger | Message shown |
|---|---|
| Equipment Name or Type missing on Add | "Please fill in all required fields" |
| Service Date, Type, or Cost missing | "Please fill in required service fields" |
| Delete equipment | Confirmation dialog with equipment name — "Are you sure?" |

---

## 10. Access Control

| Role | View list | Add | Edit | Delete | Add service record |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | Yes |
| Branch manager | Yes | Yes | Yes | Yes | Yes |
| Receptionist | Yes | No | No | No | No |
| Trainer | Yes | No | No | No | No |
| Accountant | Yes | No | No | No | No |
| POS staff | No | No | No | No | No |

> Equipment is an **Add-on** module — it is available only to gyms on plans that include the Equipment add-on.
