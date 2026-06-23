# GymOS — Assess Group
# Module 04: Body Metrics

**URL:** `/assess/body-metrics`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Record and track client body measurements over time — weight, height, waist/hip/neck, BMI, body fat percentage, and next measurement date. Supports view, edit, download PDF, and print per record.

---

## 1. Navigation Flow

```
Assess → Body Metrics
  → Page loads with top actions + filter/search
    → Add body metrics (or Add on mobile)
      → Form panel / drawer opens
        → Fill fields → Click Add to save
```

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Body Metrics                                                │
│  [Add body metrics]  [Progress tracking →]                   │
├──────────────────────────────────────────────────────────────┤
│  🔍 Search by client    From [date]  To [date]               │
│  Next measurement date [date]   Items per page [25 ▾]        │
├──────────────────────────────────────────────────────────────┤
│  Body metrics table / card list                              │
│                                                              │
│  Client    Date     Weight  Height  BMI    Body fat  Actions │
│  Arjun     Jun 12   78 kg   175 cm  25.5   18%       …       │
│  Priya     Jun 10   62 kg   162 cm  23.6   22%       …       │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Top Actions

| Button | Behaviour |
|---|---|
| **Add body metrics** | Opens the Add/Edit form panel or drawer |
| **Progress tracking** | Navigates to the Progress Tracking screen (`/assess/body-metrics/progress`) |

---

## 4. Filters and Search

| Filter | Options |
|---|---|
| **Search by client** | Name, phone (10 digits, no +91), email |
| **From date** | Measurement date range start |
| **To date** | Measurement date range end |
| **Next measurement date** | Filter records due for measurement on or before this date |
| **Items per page** | 10 / 25 / 50 / 100 |

---

## 5. Body Metrics Table

| Column | Notes |
|---|---|
| **Client** | Name + avatar |
| **Measurement date** | dd MMM yyyy |
| **Weight** | kg (2 decimal places) |
| **Height** | cm |
| **BMI** | Auto-calculated; displayed with category label |
| **Body fat %** | If entered |
| **Next measurement date** | If set |
| **Actions** | ⋯ dropdown: Edit, Download PDF, Print, Delete |

---

## 6. BMI Calculation and Categories

`BMI = weight(kg) / (height(m))²`

| BMI range | Category |
|---|---|
| < 18.5 | Underweight |
| 18.5 – 24.9 | Normal weight |
| 25.0 – 29.9 | Overweight |
| ≥ 30.0 | Obese |

BMI is calculated client-side on form input and stored on save.

---

## 7. Add Body Metrics — Form Fields

Opens as a right-side drawer (desktop) or bottom sheet (mobile).

| Field | Type | Validation | Required |
|---|---|---|---|
| **Client** | Search dropdown | Premium clients only | Yes |
| **Measurement date** | Date picker | Default today; not future | Yes |
| **Weight (kg)** | Number | 1.0 – 500.0 | Yes |
| **Height (cm)** | Number | 50 – 300 | Yes |
| **Waist (cm)** | Number | ≥ 0 | No |
| **Hip (cm)** | Number | ≥ 0 | No |
| **Neck (cm)** | Number | ≥ 0 | No |
| **Body fat (%)** | Number | 0 – 100 | No |
| **Next measurement date** | Date picker | Must be after measurement date | No |
| **Notes** | Textarea | Max 300 chars | No |

**Auto-fill fields (not editable):**
- **Age** — auto-filled from client's date of birth
- **Gender** — auto-filled from client profile

**On Add/Save:**
1. BMI auto-calculated from weight and height
2. Record saved under this client's body metrics history
3. Row appears in the table immediately

---

## 8. Edit Body Metrics

- Click **Edit** from the ⋯ actions menu on any row
- Same form opens pre-filled with that record's values
- All fields editable
- Click **Save** to update

---

## 9. Download PDF Flow

- Click **Download** from the ⋯ actions menu
- App generates a thermal/PDF-style body metrics report for that specific record
- PDF downloads automatically

**PDF contents:**
- Gym logo + client name + date
- All measured values (weight, height, BMI, waist, hip, neck, body fat)
- BMI category + interpretation note
- Next measurement date (if set)
- Trainer name + branch

---

## 10. Print

- Click **Print** from the ⋯ actions menu
- Opens browser print dialog with the same body metrics report layout
- Print-optimised CSS layout (no background colours, clean table)

---

## 11. Delete

- Click **Delete** from ⋯ actions menu
- Confirm dialog: type client's name in **plain text** to confirm permanent deletion
  - Correct: `Sandhiya`
  - Wrong (bug to fix): `<strong>Sandhiya</strong>` — HTML tags must not appear in the dialog
- On confirm: record permanently removed

---

## 12. Progress Tracking Screen

Accessed via **Progress tracking** button at the top.

- Select client
- Line chart: weight over time
- Line chart: BMI over time
- Line chart: body fat % over time (if data exists)
- Date range filter
- Toggle between chart and table view

---

## 13. API Endpoints

```
GET /api/v1/assess/body-metrics
  Query: client_id, from, to, next_measurement_date, page, limit
  Response: { records: [...], total }

POST /api/v1/assess/body-metrics
  Body: {
    client_id, measurement_date, weight_kg, height_cm,
    waist_cm?, hip_cm?, neck_cm?, body_fat_pct?,
    next_measurement_date?, notes?
  }
  -- bmi auto-calculated server-side
  Response 201: { record_id, bmi }

GET /api/v1/assess/body-metrics/:recordId
  Response: { full record }

PUT /api/v1/assess/body-metrics/:recordId
  Body: partial update
  Response 200: { record }

DELETE /api/v1/assess/body-metrics/:recordId
  Response 200: { deleted: true }

GET /api/v1/assess/body-metrics/:clientId/progress
  Query: from, to
  Response: { weight_trend: [...], bmi_trend: [...], body_fat_trend: [...] }

GET /api/v1/assess/body-metrics/:recordId/pdf
  Response: application/pdf download
```

---

## 14. Validation Rules

| Field | Rule |
|---|---|
| Client | Required |
| Measurement date | Required; not in the future |
| Weight | 1.0 – 500.0 kg |
| Height | 50 – 300 cm |
| Next measurement date | Must be after measurement date if provided |
| Body fat | 0 – 100% |
| Phone (search) | 10 digits, no +91 |
| Delete confirm | Plain text name — no HTML tags |

---

## 15. Business Rules

- Multiple records per client — full history kept
- BMI is always calculated from weight + height at time of entry
- Age and gender auto-fill from client profile — not editable in this form
- If age or gender is missing from client profile, body fat interpretation may not be available
- Progress Tracking screen shows all historical records for the selected client
