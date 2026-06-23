# GymOS — Assess Group
# Module 05: Posture Assessment

**URL:** `/assess/posture`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** View and manage posture assessment history for clients. Shows key posture metrics, trend summary cards, filters, and PDF export. This is primarily a view/manage history screen — posture assessments are typically entered via a dedicated assessment device or external workflow and stored here.

---

## 1. Navigation Flow

```
Assess → Posture Assessment
  → Summary cards (top)
  → Filters
  → Browse assessments (card list / table)
    → Click record → View details
      → Download PDF Report
      → Delete assessment
```

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Posture Assessment                                          │
├──────────────────────────────────────────────────────────────┤
│  [Total assessments: 142]  [This month: 18]  [Last month: 21]│
├──────────────────────────────────────────────────────────────┤
│  🔍 Search by client name / phone    Date filter  Per page ▾ │
├──────────────────────────────────────────────────────────────┤
│  Assessment cards / list                                     │
│                                                              │
│  [Client: Arjun]  [Date: Jun 10]  [Status: Reviewed]  …     │
│  [Client: Priya]  [Date: Jun 8]   [Status: Pending]   …     │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Summary Cards (Top Row)

| Card | Calculation |
|---|---|
| **Total assessments** | All posture assessments across all clients at this branch |
| **This month** | Assessments recorded in current calendar month |
| **Last month** | Assessments recorded in previous calendar month |

---

## 4. Filters

| Filter | Options |
|---|---|
| **Search** | Client name or phone (10 digits, no +91) |
| **Date filter** | Date range (from – to) on assessment date |
| **Items per page** | 10 / 25 / 50 / 100 |

---

## 5. Assessment Card / List View

**Desktop:** Table rows. **Mobile:** Cards.

Each record shows:
- Client name + avatar
- Assessment date
- Key posture metric summary (e.g. head position, shoulder alignment — abbreviated)
- Status badge (Reviewed / Pending review)
- Actions menu (⋯): View details, Download PDF, Delete

---

## 6. View Details

Click a record or card to open assessment details panel.

| Section | Fields shown |
|---|---|
| **Client info** | Name, age, gender |
| **Assessment date** | dd MMM yyyy |
| **Posture metrics** | Full list of measured posture data points with values and feedback labels |
| **Feedback notes** | Trainer/system notes on findings |
| **Download PDF Report** | Button in details panel |

**Posture metric examples:**
- Head alignment (forward / neutral / extended)
- Shoulder height symmetry (level / right high / left high)
- Spine curvature (normal / increased lordosis / increased kyphosis / scoliosis indicator)
- Hip tilt (neutral / anterior tilt / posterior tilt)
- Knee alignment (neutral / valgus / varus)
- Foot position (neutral / pronated / supinated)

Each metric shows: measured value + interpretation label.

---

## 7. Download PDF Report

Available from:
- The ⋯ actions menu on any card/row → **Download**
- The **Download PDF Report** button inside assessment details

**PDF contents:**
- Client name + date of assessment
- All posture metric results with interpretation labels
- Gym logo + trainer name
- Recommendations (if entered)

---

## 8. Delete Assessment

- Click **Delete** from ⋯ actions menu
- Confirm dialog: type client name in **plain text** (e.g. `Sandhiya`) to permanently delete
- On confirm: assessment record is permanently removed from history

---

## 9. API Endpoints

```
GET /api/v1/assess/posture
  Query: client_id, from, to, page, limit
  Response: { assessments: [...], total,
              summary: { total, this_month, last_month } }

GET /api/v1/assess/posture/:assessmentId
  Response: {
    client, date, status,
    metrics: [{ name, value, interpretation }],
    notes
  }

POST /api/v1/assess/posture
  Body: {
    client_id, assessment_date,
    metrics: [{ name, value, interpretation? }],
    notes?
  }
  Response 201: { assessment_id }

PUT /api/v1/assess/posture/:assessmentId
  Body: partial update
  Response 200: { assessment }

DELETE /api/v1/assess/posture/:assessmentId
  Response 200: { deleted: true }

GET /api/v1/assess/posture/:assessmentId/pdf
  Response: application/pdf download
```

---

## 10. Validation Rules

| Field | Rule |
|---|---|
| Client | Required |
| Assessment date | Required; not in future |
| At least one metric | Required before saving |
| Phone search | 10 digits, no +91 |
| Delete confirm | Plain text client name — no HTML tags |

---

## 11. Business Rules

- Multiple assessments per client — all stored as history
- Posture data is primarily read-only after entry (view, download, delete — no inline editing of individual metric values once saved; use Edit to correct)
- PDF filename: `posture_assessment_{client_name}_{date}.pdf`
- Status "Reviewed" is set when the trainer opens and views the details panel
