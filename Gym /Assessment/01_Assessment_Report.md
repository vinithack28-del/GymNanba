# GymOS — Assess Group
# Module 01: Assessment Report

**URL:** `/assess/report`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** A complete, client-wise health and fitness summary in one place. Combines all assessment sections so trainers can quickly review overall status, progress, and export a shareable PDF report.

---

## 1. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Assessment Report                    [Download PDF]         │
├──────────────────────────────────────────────────────────────┤
│  Select Client: 🔍 Search by name / phone / email            │
├──────────────────────────────────────────────────────────────┤
│  [Report loads here after client selection]                  │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐     │
│  │  Assessment Score Summary                           │     │
│  ├──────────────┬──────────────┬──────────────────────┤     │
│  │  PAR-Q+      │  Vitals      │  Body Composition    │     │
│  ├──────────────┼──────────────┼──────────────────────┤     │
│  │  Posture     │  Cardiorespiratory  │  Muscular Str  │     │
│  ├──────────────┼──────────────┼──────────────────────┤     │
│  │  Muscular Endurance  │  Flexibility  │  Metabolism  │     │
│  └─────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────┘
```

---

## 2. Client Selection

- Search by client name, phone (10 digits, no +91), or email
- Only **premium/eligible clients** appear in the dropdown
- Report loads automatically after client is selected
- If no data is available for the client: screen shows "No assessment data available for this client"

---

## 3. Assessment Score Summary Card

Top-level card showing an overall health readiness score (aggregate across all available assessments).

| Field | Content |
|---|---|
| Overall score | Calculated from completed assessment sections |
| Sections completed | e.g. "7 of 9 sections" |
| Last updated | Date of most recent assessment across any section |
| Risk flag | If any section shows a risk flag (e.g. PAR-Q+ positive, balance risk), shown here |

---

## 4. Report Sections

Each section is a collapsible card showing the **most recent data** from that assessment module.

### 4.1 PAR-Q+ Section
- Questionnaire completion status (Complete / Incomplete / Not started)
- Answer summary: positive responses highlighted
- Date completed
- Any follow-up flag if answered Yes to any question

### 4.2 Vitals Section
| Field | Shown |
|---|---|
| Most recent resting heart rate | HR (bpm) |
| Most recent blood pressure | Systolic / Diastolic |
| Date of last vitals check | |
| Next check date | |
| Trend indicator | vs. previous reading (↑ ↓ →) |

### 4.3 Body Composition Section
| Field | Shown |
|---|---|
| Latest weight | kg |
| Latest height | cm |
| BMI | Auto-calculated |
| BMI category | Underweight / Normal / Overweight / Obese |
| Body fat % | If entered |
| Waist / Hip / Neck | If entered |
| Measurement date | |
| Next measurement date | |

### 4.4 Posture Section
- Most recent posture assessment summary
- Key metrics with feedback labels
- Assessment date
- Download button for posture PDF report

### 4.5 Cardiorespiratory Fitness Section
| Field | Shown |
|---|---|
| Latest VO2max | ml/kg/min |
| Test type used | 12 min walk/run / 1.5 mile / 1 mile walk |
| Interpretation | If age + gender available |
| Test date | |
| Next test date | |

### 4.6 Muscular Strength Section
- Latest test result with test name/type
- Test value and measurement date
- Trend vs previous test

### 4.7 Muscular Endurance Section
- Latest test result (reps)
- Test name/type and measurement date
- Interpretation if saved

### 4.8 Flexibility Section
- Latest sit-and-reach distance (cm)
- Test type and measurement date
- Interpretation if saved

### 4.9 Metabolism Section
- Basic metabolic rate (BMR) if calculable from body metrics
- TDEE estimate if activity level available

---

## 5. Download PDF Flow

1. Select a client with available report data
2. Click **Download PDF** (top-right)
3. App generates a multi-section PDF report
4. PDF downloads automatically

**PDF contents:**
- Cover page: gym logo, client name, report date
- One section per assessment area (same as screen sections)
- Charts/trend graphs where applicable
- Trainer name / branch at footer

> If no data exists in a section, that section is shown as "No data available" in the PDF — it is not skipped entirely.

---

## 6. API Endpoints

```
GET /api/v1/assess/report/:clientId
  Response: {
    client: { name, dob, gender, phone },
    score_summary: { overall_score, sections_completed, last_updated, risk_flags },
    parq: { status, date, flags },
    vitals: { hr_bpm, bp_systolic, bp_diastolic, date, next_date },
    body_metrics: { weight_kg, height_cm, bmi, bmi_category,
                    body_fat_pct, waist, hip, neck, date, next_date },
    posture: { summary, date },
    cardiorespiratory: { vo2max, test_type, interpretation, date, next_date },
    muscular_strength: { value, test_name, date },
    muscular_endurance: { reps, test_name, date },
    flexibility: { distance_cm, test_name, date },
    metabolism: { bmr, tdee }
  }

GET /api/v1/assess/report/:clientId/pdf
  Response: application/pdf download
```

---

## 7. Business Rules

- Report only shows **most recent record** from each section — not historical trends (use individual module screens for trend views)
- Client must be premium/eligible — standard clients do not appear in the selector
- Download PDF is only enabled when at least one assessment section has data
- Metabolism section is calculated, not stored — derived from body metrics at render time
