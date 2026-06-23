# GymOS — Assess Group
# Module 08: Fitness Assessment

**URL:** `/assess/fitness`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Track a client's fitness across four key domains — Cardiorespiratory fitness (VO2max), Muscular Strength, Muscular Endurance, and Flexibility — using standardised test protocols. Each domain has its own tab with history table, chart view, and test entry.

---

## 1. Navigation Flow

```
Assess → Fitness Assessment
  → Select Client
    → Four tabs load:
        Cardiorespiratory | Muscular Strength | Muscular Endurance | Flexibility
      → In each tab:
          Table / Chart toggle
          New Test → dialog → fill → Save Test
          Table rows: View, Delete
```

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Fitness Assessment                                          │
├──────────────────────────────────────────────────────────────┤
│  Select Client: 🔍 Search by name / phone / email            │
├──────────────────────────────────────────────────────────────┤
│  [Cardiorespiratory] [Muscular Strength] [Muscular Endurance]│
│  [Flexibility]                                               │
├──────────────────────────────────────────────────────────────┤
│  [Table view ↔ Chart view toggle]           [New Test]       │
│                                                              │
│  [Tab-specific test history table or chart]                  │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Client Selection

- Search by name, phone (10 digits, no +91), email
- Only premium/eligible clients shown
- After selection: all four tabs load with that client's history
- Each tab shows its own empty state if no tests exist yet

---

## 4. Common Tab Behaviour

Each of the four tabs follows this pattern:

| Element | Behaviour |
|---|---|
| **Table view** | Row-based history with test name, value, date, next date, actions |
| **Chart view** | Line chart of test values over time |
| **New Test** | Opens a tab-specific dialog to enter a new test |
| **Delete (table row)** | Confirm then remove from history |

---

# Tab A — Cardiorespiratory Fitness

**Purpose:** Track aerobic capacity via VO2max estimation tests.

## A.1 Cardiorespiratory History Table

| Column | Notes |
|---|---|
| **Test type** | 12 min walk/run / 1.5 mile walk/run / 1 mile walk |
| **VO2max (ml/kg/min)** | Auto-calculated on save |
| **Interpretation** | Text label (Excellent / Good / Average / Below average / Poor) — shown if age + gender available |
| **Measurement date** | |
| **Next test date** | |
| **Actions** | Delete |

## A.2 New Cardiorespiratory Test Dialog

| Field | Type | Validation | Required |
|---|---|---|---|
| **Test type** | Dropdown | 12 min walk/run / 1.5 mile walk/run / 1 mile walk | Yes |
| **Test value** | Number | Field label changes per test type (see A.3) | Yes |
| **HRR (Heart rate reserve)** | Number | Only shown for 12 min walk/run | Conditional |
| **Measurement date** | Date picker | Default today; not future | Yes |
| **Next measurement date** | Date picker | After measurement date | No |

## A.3 Test-Type-Specific Fields

| Test type | Test value field label | HRR field |
|---|---|---|
| 12 min walk/run | Distance covered (metres) | Optional — shown |
| 1.5 mile walk/run | Time taken (minutes:seconds) | Not shown |
| 1 mile walk | Time taken (minutes:seconds) | Not shown |

## A.4 VO2max Calculations

**12-minute walk/run (Cooper Test):**
```
VO2max = (distance_metres − 504.9) / 44.73
```

**1.5-mile run:**
```
VO2max = 3.5 + 483 / time_minutes
```

**1-mile walk (Rockport Walk Test):**
```
VO2max = 132.853 − (0.0769 × weight_lbs) − (0.3877 × age)
         + (6.315 × gender_factor) − (3.2649 × time_minutes)
         − (0.1565 × HR_at_end)
```
*(gender_factor: male=1, female=0)*

VO2max is calculated server-side and stored with the test record.

## A.5 Interpretation Table (12 min walk/run, by age + gender)

Interpretation is generated for 12 min walk/run **only** when client age and gender are available in their profile.

| Category | Description |
|---|---|
| Superior | Top 5% for age/gender group |
| Excellent | Top 15% |
| Good | Above average |
| Average | Average for age/gender |
| Below average | Below average |
| Poor | Lowest category |

If age or gender is missing from client profile: VO2max is still saved but interpretation is not shown.

## A.6 What Happens on Save (Cardiorespiratory)

1. VO2max auto-calculated from inputs
2. Interpretation generated if age + gender available (12 min test only)
3. Record appears in history table and chart immediately

---

# Tab B — Muscular Strength

**Purpose:** Track force output progress over time.

## B.1 Muscular Strength History Table

| Column | Notes |
|---|---|
| **Test name / type** | e.g. Grip strength, 1-Rep Max Bench Press |
| **Test value** | kg or N depending on test |
| **Measurement date** | |
| **Next test date** | |
| **Actions** | Delete |

## B.2 New Muscular Strength Test Dialog

| Field | Type | Validation | Required |
|---|---|---|---|
| **Test name / type** | Text or dropdown | 2–100 chars | Yes |
| **Test value** | Number | > 0 | Yes |
| **Unit** | Dropdown | kg / N / lbs | Yes |
| **Measurement date** | Date picker | Default today; not future | Yes |
| **Next measurement date** | Date picker | After measurement date | No |

**Context passed to dialog (from client profile):**
- Client age
- Client gender
- Latest body weight (from most recent body metrics record)

These are used for interpretation/calculation behaviour within the test dialog.

## B.3 What Happens on Save (Muscular Strength)

1. Record stored under `muscular_strength` for this client
2. Appears in table and chart immediately
3. Interpretation shown if calculable from age, gender, body weight context

---

# Tab C — Muscular Endurance

**Purpose:** Track fatigue resistance (repeated muscle effort over time).

## C.1 Muscular Endurance History Table

| Column | Notes |
|---|---|
| **Test name / type** | e.g. Push-up test, Curl-up test |
| **Reps** | Count achieved |
| **Interpretation** | If saved with the record |
| **Measurement date** | |
| **Next test date** | |
| **Actions** | Delete |

## C.2 New Muscular Endurance Test Dialog

| Field | Type | Validation | Required |
|---|---|---|---|
| **Test name / type** | Text or dropdown | 2–100 chars | Yes |
| **Reps (test value)** | Integer | ≥ 0 | Yes |
| **Interpretation** | Text | Auto-generated or manually entered | No |
| **Measurement date** | Date picker | Default today; not future | Yes |
| **Next measurement date** | Date picker | After measurement date | No |

## C.3 What Happens on Save (Muscular Endurance)

1. Record stored under `muscular_endurance` for this client
2. Interpretation text (if provided) is saved with the test
3. Appears in table and chart immediately

---

# Tab D — Flexibility

**Purpose:** Track range-of-motion (mobility) improvements over time using the sit-and-reach test.

## D.1 Flexibility History Table

| Column | Notes |
|---|---|
| **Test name / type** | e.g. Sit-and-reach |
| **Distance (cm)** | Reach distance |
| **Interpretation** | If saved |
| **Measurement date** | |
| **Next test date** | |
| **Actions** | Delete |

## D.2 New Flexibility Test Dialog

| Field | Type | Validation | Required |
|---|---|---|---|
| **Test name / type** | Text or dropdown | 2–100 chars | Yes |
| **Distance / value (cm)** | Number | Accepts negative values (for below baseline) | Yes |
| **Interpretation** | Text | Auto-generated or manual | No |
| **Measurement date** | Date picker | Default today; not future | Yes |
| **Next measurement date** | Date picker | After measurement date | No |

## D.3 What Happens on Save (Flexibility)

1. Record stored under `sit_and_reach` for this client
2. Interpretation (if entered/generated) saved with assessment
3. Appears in table and chart immediately

---

## 5. Chart View (All Tabs)

- Line chart showing test values over time for selected client
- X-axis: measurement date
- Y-axis: test value (VO2max / kg or N / reps / cm)
- Hover tooltip: date + exact value
- Multiple test types shown as separate lines (colour-coded) if more than one type exists

---

## 6. Delete Flow (All Tabs)

- Click Delete from table row actions
- Confirm dialog: type client name in **plain text** (e.g. `Sandhiya`) — not as HTML tags
- Record permanently removed from that test category history

---

## 7. API Endpoints

```
-- Cardiorespiratory
GET    /api/v1/assess/fitness/:clientId/cardiorespiratory
POST   /api/v1/assess/fitness/:clientId/cardiorespiratory
DELETE /api/v1/assess/fitness/:clientId/cardiorespiratory/:testId

-- Muscular Strength
GET    /api/v1/assess/fitness/:clientId/muscular-strength
POST   /api/v1/assess/fitness/:clientId/muscular-strength
DELETE /api/v1/assess/fitness/:clientId/muscular-strength/:testId

-- Muscular Endurance
GET    /api/v1/assess/fitness/:clientId/muscular-endurance
POST   /api/v1/assess/fitness/:clientId/muscular-endurance
DELETE /api/v1/assess/fitness/:clientId/muscular-endurance/:testId

-- Flexibility
GET    /api/v1/assess/fitness/:clientId/flexibility
POST   /api/v1/assess/fitness/:clientId/flexibility
DELETE /api/v1/assess/fitness/:clientId/flexibility/:testId

-- POST body (cardiorespiratory):
{
  test_type,        -- 12_min | 1_5_mile | 1_mile_walk
  test_value,       -- metres or time string (MM:SS)
  hrr?,             -- for 12 min only
  measurement_date,
  next_measurement_date?
}
Response 201: { test_id, vo2max, interpretation? }

-- POST body (muscular strength):
{ test_name, test_value, unit, measurement_date, next_measurement_date? }

-- POST body (muscular endurance):
{ test_name, reps, interpretation?, measurement_date, next_measurement_date? }

-- POST body (flexibility):
{ test_name, distance_cm, interpretation?, measurement_date, next_measurement_date? }
```

---

## 8. Validation Rules

| Field | Rule |
|---|---|
| Client | Required — all four tabs need client selected |
| Measurement date | Required; not in future |
| All test values | > 0 (flexibility allows negative) |
| Next measurement date | After measurement date |
| Phone search | 10 digits, no +91 |
| Delete confirm | Plain text — no HTML tags |

---

## 9. Business Rules

- Each tab maintains its own independent history per client
- VO2max is always calculated server-side — never trusted from client input
- Interpretation for non-cardiorespiratory tests is optional and stored as free text
- If client's age or gender is missing from profile: interpretation may not be generated for cardiorespiratory; test can still be saved
- Multiple test types can exist within the same tab (e.g. both grip strength and bench press under Muscular Strength)
