# GymOS — Assess Group
# Module 07: Vitals Check

**URL:** `/assess/vitals`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Record and track a client's resting heart rate and blood pressure over time, including the next check date. Maintains a full vitals history per client.

---

## 1. Navigation Flow

```
Assess → Vitals Check
  → Select Client
    → Vitals history loads (table)
      → New record
        → Form dialog opens
          → Enter HR + BP + next check date → Save
      → Refresh icon → Reload vitals list
```

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Vitals Check                                  [🔄 Refresh]  │
├──────────────────────────────────────────────────────────────┤
│  Select Client: 🔍 Search by name / phone / email            │
├──────────────────────────────────────────────────────────────┤
│  [Vitals history loads after client selection]               │
│                                                              │
│  Vitals history                              [New record]    │
│                                                              │
│  Meas. date  HR (bpm)  BP (systolic/diastolic)  Next check  │
│  Jun 12      72        118 / 76                  Jul 12      │
│  May 15      78        122 / 80                  Jun 15      │
│  Apr 3       76        120 / 78                  May 3       │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Client Selection

- Search by name, phone (10 digits, no +91), email
- Only premium/eligible clients shown
- After selection: vitals history table loads for that client
- Empty state: "No vitals records yet. Click New record to add one."

---

## 4. Vitals History Table

| Column | Notes |
|---|---|
| **Measurement date** | dd MMM yyyy |
| **HR (bpm)** | Resting heart rate |
| **BP (systolic / diastolic)** | e.g. 118 / 76 mmHg |
| **Next check date** | Scheduled follow-up date |
| **Last updated** | Timestamp of last edit |
| **Actions** | ⋯: Edit, Delete |

Default sort: most recent measurement first.

---

## 5. New Record — Form Dialog

Clicking **New record** opens a dialog/modal.

| Field | Type | Validation | Required |
|---|---|---|---|
| **Measurement date** | Date picker | Default today; not future | Yes |
| **Resting heart rate (bpm)** | Number | 20 – 250 | Yes |
| **Blood pressure — Systolic (mmHg)** | Number | 50 – 300 | Yes |
| **Blood pressure — Diastolic (mmHg)** | Number | 20 – 200 | Yes |
| **Next check date** | Date picker | Must be after measurement date | No |
| **Notes** | Textarea | Max 300 chars | No |

**On Save:**
- Record added to vitals history
- Table refreshes — new row appears at the top
- Dialog closes

---

## 6. Edit Vitals Record

- Click **Edit** from ⋯ actions on any row
- Same form dialog opens pre-filled
- All fields editable
- Click **Save** to update

---

## 7. Delete Vitals Record

- Click **Delete** from ⋯ actions
- Confirm dialog: type client name in **plain text** (e.g. `Sandhiya`) — not as HTML tags
- On confirm: record permanently removed

---

## 8. Refresh

- **Refresh icon button** (top right) reloads the vitals list for the currently selected client
- Useful after another trainer has added a record in the same session

---

## 9. Heart Rate Interpretation Reference

(Displayed as a tooltip or helper text — not stored)

| Category | Resting HR (bpm) |
|---|---|
| Athlete | 40 – 60 |
| Normal (adult) | 60 – 100 |
| Slightly elevated | 100 – 110 |
| Elevated | > 110 |

---

## 10. Blood Pressure Interpretation Reference

(Displayed as a tooltip or helper text — not stored)

| Category | Systolic (mmHg) | Diastolic (mmHg) |
|---|---|---|
| Normal | < 120 | < 80 |
| Elevated | 120 – 129 | < 80 |
| High Stage 1 | 130 – 139 | 80 – 89 |
| High Stage 2 | ≥ 140 | ≥ 90 |
| Hypertensive crisis | > 180 | > 120 |

---

## 11. API Endpoints

```
GET /api/v1/assess/vitals/:clientId
  Response: { records: [...], total }

POST /api/v1/assess/vitals/:clientId
  Body: {
    measurement_date,
    hr_bpm,
    bp_systolic,
    bp_diastolic,
    next_check_date?,
    notes?
  }
  Response 201: { record_id }

PUT /api/v1/assess/vitals/:clientId/:recordId
  Body: partial update
  Response 200: { record }

DELETE /api/v1/assess/vitals/:clientId/:recordId
  Response 200: { deleted: true }
```

---

## 12. Validation Rules

| Field | Rule |
|---|---|
| Client | Required — cannot add record without selecting |
| Measurement date | Required; not in future |
| HR | 20 – 250 bpm |
| BP Systolic | 50 – 300 mmHg |
| BP Diastolic | 20 – 200 mmHg; must be less than systolic |
| Next check date | Must be after measurement date |
| Phone search | 10 digits, no +91 |
| Delete confirm | Plain text name — HTML tags must not appear |

---

## 13. Business Rules

- Multiple records per client — full history kept
- Records are sorted by measurement date descending by default
- No automatic alerts when vitals fall outside normal range (display only — trainer judges)
- Next check date is informational — no automated reminder is sent from this screen (notification settings handle reminders separately)
