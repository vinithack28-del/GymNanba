# GymOS — Assess Group
# Module 06: Balance Assessment

**URL:** `/assess/balance`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Evaluate a client's dynamic balance using the Y-Balance Test. Stores results, shows risk interpretation, supports AI insight generation, and maintains a full test history per client.

---

## 1. Navigation Flow

```
Assess → Balance Assessment
  → Select Client
    → Balance Test Results section loads
      → New Test
        → Enter test values → Save
          → Auto-calculation: asymmetry, composite scores, overall status
      → View previous tests
      → Open details
        → Generate AI insight (if not already generated)
      → Delete a test
```

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Balance Assessment                                          │
├──────────────────────────────────────────────────────────────┤
│  Select Client: 🔍 Search by name / phone / email            │
├──────────────────────────────────────────────────────────────┤
│  Balance Test Results                        [New Test]      │
│                                                              │
│  Date       Status          Composite R  Composite L  Actions│
│  Jun 10     Balanced        98.5%        97.2%         …     │
│  May 15     Asymmetrical    91.3%        84.1%         …     │
│  Apr 2      Moderate Risk   78.6%        69.4%         …     │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Client Selection

- Search by name, phone (10 digits, no +91), email
- After selection: all balance test results for that client load in the table
- Empty state: "No balance tests yet. Click New Test to add the first test."

---

## 4. Y-Balance Test — Inputs

The Y-Balance Test measures dynamic balance by recording reach distances in three directions.

### New Test Form Fields

| Field | Type | Validation | Required |
|---|---|---|---|
| **Limb length (cm)** | Number | > 0 | Yes |
| **Right — Anterior reach (cm)** | Number | > 0 | Yes |
| **Right — Posteromedial reach (cm)** | Number | > 0 | Yes |
| **Right — Posterolateral reach (cm)** | Number | > 0 | Yes |
| **Left — Anterior reach (cm)** | Number | > 0 | Yes |
| **Left — Posteromedial reach (cm)** | Number | > 0 | Yes |
| **Left — Posterolateral reach (cm)** | Number | > 0 | Yes |
| **Measurement date** | Date picker | Default today; not future | Yes |
| **Next measurement date** | Date picker | After measurement date | No |
| **Notes** | Textarea | Max 300 chars | No |

---

## 5. Auto-Calculations on Save

All calculations are performed server-side on save.

### Composite Score (per side)

```
Composite Score = (Anterior + Posteromedial + Posterolateral) / (3 × Limb Length) × 100
```

Calculated for Right and Left separately.

### Asymmetry

```
Asymmetry % = |Right Composite - Left Composite|
```

| Asymmetry | Interpretation |
|---|---|
| < 4% | Balanced |
| 4 – 6% | Asymmetrical |
| > 6% | Significant asymmetry |

### Overall Balance Status

| Condition | Status |
|---|---|
| Both composites ≥ 89% AND asymmetry < 4% | **Balanced** |
| Composite 80–88% OR asymmetry 4–6% | **Asymmetrical** |
| Composite 70–79% OR asymmetry > 6% | **Moderate Risk** |
| Any composite < 70% | **High Risk** |

Status shown as a colour badge: Green (Balanced), Amber (Asymmetrical), Orange (Moderate Risk), Red (High Risk).

---

## 6. Balance Test Results Table

| Column | Notes |
|---|---|
| **Date** | Measurement date |
| **Overall status** | Colour badge: Balanced / Asymmetrical / Moderate Risk / High Risk |
| **Composite R** | Right composite score (%) |
| **Composite L** | Left composite score (%) |
| **Asymmetry** | % difference |
| **Actions** | View details, Delete |

---

## 7. View Details Flow

Click a test row or **View details** from the actions menu.

Details panel shows:

| Section | Fields |
|---|---|
| **Test inputs** | Limb length, all 6 reach distances |
| **Calculated scores** | Right composite %, Left composite %, Asymmetry % |
| **Overall interpretation** | Status badge + written explanation |
| **Next measurement date** | If set |
| **AI insight** | Generated insight (if available) or "Generate AI insight" button |
| **Notes** | Trainer notes if entered |

---

## 8. AI Insight Flow

Available within the test details panel.

1. Open a test's details
2. **Generate AI insight** button shown only if insight has not been saved yet
3. Click → app calls AI API with test data (composite scores, asymmetry, status)
4. Wait for generation to complete (spinner shown)
5. AI insight text is saved to the record and displayed in the details panel
6. On subsequent opens: saved insight shown — no button (already generated)

**AI insight content covers:**
- Interpretation of the balance scores in plain language
- Risk explanation relevant to the client's sport or fitness goal (if available)
- Recommended exercises or corrective focus areas based on scores

---

## 9. Delete Flow

- From row/card actions: **Delete**, or within details panel: **Delete Assessment**
- Confirm dialog: type client name in **plain text** (e.g. `Sandhiya`) to permanently delete
- Assessment removed from history

---

## 10. API Endpoints

```
GET /api/v1/assess/balance/:clientId
  Response: { tests: [...], total }

POST /api/v1/assess/balance/:clientId
  Body: {
    limb_length_cm,
    right: { anterior, posteromedial, posterolateral },
    left:  { anterior, posteromedial, posterolateral },
    measurement_date,
    next_measurement_date?,
    notes?
  }
  Response 201: {
    test_id,
    composite_right_pct,
    composite_left_pct,
    asymmetry_pct,
    overall_status   -- balanced | asymmetrical | moderate_risk | high_risk
  }

GET /api/v1/assess/balance/:clientId/:testId
  Response: { full test record with calculations and ai_insight if available }

POST /api/v1/assess/balance/:clientId/:testId/ai-insight
  Response 200: { insight_text }
  -- Generates and saves; subsequent GETs return saved insight

DELETE /api/v1/assess/balance/:clientId/:testId
  Response 200: { deleted: true }
```

---

## 11. Validation Rules

| Field | Rule |
|---|---|
| Client | Required |
| Limb length | > 0 cm |
| All 6 reach distances | > 0 cm each |
| Measurement date | Required; not in future |
| Next measurement date | After measurement date |
| Phone search | 10 digits, no +91 |
| Delete confirm | Plain text — no HTML tags |

---

## 12. Business Rules

- AI insight is generated once per test — cannot be regenerated (to avoid duplicate costs)
- If AI insight generation fails, "Retry" button shown
- Composite scores and status are always recalculated server-side — not trusting client-submitted values
- All 6 reach distances are required — test cannot be saved with any missing
