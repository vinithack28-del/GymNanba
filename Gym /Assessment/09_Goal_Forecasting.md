# GymOS — Assess Group
# Module 09: Goal Forecasting

**URL:** `/assess/goal-forecasting`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Estimate how long a client may take to reach a weight goal, compare forecasted progress with actual body metrics measurements, and export the forecast as a shareable PDF report.

---

## 1. Navigation Flow

```
Assess → Goal Forecasting
  → Select Client
    → Enter Goal Parameters
      → Click Calculate Forecast
        → Review Forecast Results + chart + energy breakdown
          → Click Download PDF (optional)
```

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Goal Forecasting                                            │
├──────────────────────────────────────────────────────────────┤
│  Select Client: 🔍 Search by name / phone / email            │
├──────────────────────────────────────────────────────────────┤
│  Goal Parameters                                             │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Current weight (kg)  [_____]                        │    │
│  │  Goal type            [Weight Loss ▾]                │    │
│  │  Target weight (kg)   [_____]                        │    │
│  │  Weekly rate          [Recommended ▾]                │    │
│  │                               [Calculate Forecast]   │    │
│  └──────────────────────────────────────────────────────┘    │
├──────────────────────────────────────────────────────────────┤
│  Forecast Results                      [Download PDF]        │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Weight difference  │  Weekly rate  │  Duration      │    │
│  │  Estimated target date                               │    │
│  └──────────────────────────────────────────────────────┘    │
│  Forecast vs Actual Progress chart                           │
│  Energy Breakdown section                                    │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Client Selection

- Search by name, phone (10 digits, no +91), email
- After selection: current weight auto-fills from the most recent body metrics record (if available)
- Goal type, target weight, and weekly rate must be entered manually

---

## 4. Goal Parameters Form

| Field | Type | Options / Validation | Required |
|---|---|---|---|
| **Current weight (kg)** | Number | > 0; auto-filled from latest body metrics if available; editable | Yes |
| **Goal type** | Dropdown | Weight Loss / Weight Gain / Maintain | Yes |
| **Target weight (kg)** | Number | > 0; must differ from current weight (unless Maintain) | Yes |
| **Weekly rate** | Dropdown | Slow / Recommended / Extreme | Yes |

### Weekly Rate Definitions

| Rate | Weight Loss | Weight Gain |
|---|---|---|
| Slow | 0.25 kg/week | 0.25 kg/week |
| Recommended | 0.5 kg/week | 0.5 kg/week |
| Extreme | 1.0 kg/week | 1.0 kg/week |

> Maintain: target = current weight; forecast shows energy balance at maintenance.

### Validation Before Calculate

- Current weight > 0
- Target weight > 0
- For Weight Loss: target < current weight
- For Weight Gain: target > current weight
- If inputs invalid: error messages shown inline; Calculate button disabled

---

## 5. Calculate Forecast

Click **Calculate Forecast** to compute results. No data is saved — calculation is on-demand.

---

## 6. Forecast Results Card

| Field | Content |
|---|---|
| **Weight difference** | target − current (e.g. "−8.0 kg") |
| **Weekly rate** | Selected rate label + kg/week value |
| **Duration** | Weeks and months (e.g. "16 weeks / 4 months") |
| **Estimated target date** | Today + duration (e.g. "Oct 8, 2026") |

### Duration Calculation

```
Duration (weeks) = |target_weight − current_weight| / weekly_rate_kg
```

Rounded up to the nearest whole week.

---

## 7. Forecast vs Actual Progress Chart

Line chart showing two series:

| Series | Description |
|---|---|
| **Forecasted** | Straight line from current weight to target weight over the calculated duration |
| **Actual** | Client's actual body weight measurements from body metrics history (plotted against the same date axis) |

- X-axis: dates (from forecast start to estimated target date)
- Y-axis: weight (kg)
- Hover tooltip: date + weight value for each series
- If no body metrics history exists: only the forecast line is shown

---

## 8. Energy Breakdown Section

Calculated from current weight, client age, gender, and activity level (if available in client profile).

| Field | Content |
|---|---|
| **BMR (Basal Metabolic Rate)** | kcal/day at rest |
| **TDEE (Total Daily Energy Expenditure)** | kcal/day at estimated activity level |
| **Daily calorie target** | TDEE ± adjustment for goal (deficit for loss, surplus for gain) |
| **Weekly calorie adjustment** | Daily target × 7 vs TDEE × 7 |

**Calorie formulas used:**

*BMR (Mifflin-St Jeor):*
- Male: `BMR = 10 × weight_kg + 6.25 × height_cm − 5 × age + 5`
- Female: `BMR = 10 × weight_kg + 6.25 × height_cm − 5 × age − 161`

*TDEE = BMR × activity_multiplier*

| Activity level | Multiplier |
|---|---|
| Sedentary | 1.2 |
| Lightly active | 1.375 |
| Moderately active | 1.55 |
| Very active | 1.725 |
| Extra active | 1.9 |

If height or age is missing from client profile: BMR/TDEE is not shown; a note is displayed: "Add height and age to client profile to see energy breakdown."

---

## 9. Download PDF Flow

1. Select client and calculate forecast
2. Click **Download PDF** in Forecast Results section
3. App generates and downloads the forecast report

**PDF contents:**
- Gym logo + client name + report date
- Goal parameters summary (current weight, goal type, target, rate)
- Forecast results (difference, duration, target date)
- Forecast chart as embedded image
- Energy breakdown table (if calculable)
- Trainer name + branch

> Download PDF button is **only enabled** after forecast is calculated.

---

## 10. API Endpoints

```
POST /api/v1/assess/goal-forecasting/calculate
  Body: {
    client_id,
    current_weight_kg,
    goal_type,        -- weight_loss | weight_gain | maintain
    target_weight_kg,
    weekly_rate       -- slow | recommended | extreme
  }
  Response 200: {
    weight_diff_kg,
    weekly_rate_kg,
    duration_weeks,
    duration_months,
    estimated_target_date,
    forecast_series: [{ date, forecasted_weight_kg }],
    actual_series:   [{ date, actual_weight_kg }],  -- from body metrics history
    energy: {
      bmr?, tdee?, daily_target_kcal?, weekly_adjustment_kcal?
    }
  }

GET /api/v1/assess/goal-forecasting/pdf
  Query: client_id + same params as calculate
  Response: application/pdf download
```

---

## 11. Validation Rules

| Field | Rule |
|---|---|
| Client | Required |
| Current weight | > 0 kg |
| Target weight | > 0 kg |
| Weight Loss goal | target < current weight |
| Weight Gain goal | target > current weight |
| Maintain goal | any target accepted |
| Phone search | 10 digits, no +91 |

---

## 12. Business Rules

- Forecast is calculated on demand — not persisted in DB
- PDF is generated from the last calculated forecast in the current session
- If session changes or page reloads: parameters must be re-entered and forecast recalculated before PDF download
- Forecast chart "actual" series plots all body metrics weight entries for the client — from forecast start date onward
- Great for setting realistic timelines and reviewing progress during follow-up sessions
