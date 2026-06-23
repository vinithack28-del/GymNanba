# GymOS — Assess Group
# Overview & Navigation

**Group name:** Assess
**Who can access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Full client health and fitness assessment suite — from readiness questionnaires and vitals to body composition, posture, balance, fitness testing, nutrition planning, and goal forecasting.

---

## Side Menu Structure

```
┌─────────────────────────────┐
│  Assess                  ∨  │
│    Assessment Report        │
│    Questionnaire (PAR-Q+)   │
│    Nutritional Assessment   │
│    Body Metrics             │
│    Posture Assessment       │
│    Balance Assessment       │
│    Vitals Check             │
│    Fitness Assessment       │
│      └ Cardiorespiratory    │
│      └ Muscular Strength    │
│      └ Muscular Endurance   │
│      └ Flexibility          │
│    Goal Forecasting         │
└─────────────────────────────┘
```

---

## Module Index

| # | Module | URL |
|---|---|---|
| 1 | [Assessment Report](./01_Assessment_Report.md) | `/assess/report` |
| 2 | [Questionnaire (PAR-Q+)](./02_Questionnaire_PARQ.md) | `/assess/questionnaire` |
| 3 | [Nutritional Assessment](./03_Nutritional_Assessment.md) | `/assess/nutrition` |
| 4 | [Body Metrics](./04_Body_Metrics.md) | `/assess/body-metrics` |
| 5 | [Posture Assessment](./05_Posture_Assessment.md) | `/assess/posture` |
| 6 | [Balance Assessment](./06_Balance_Assessment.md) | `/assess/balance` |
| 7 | [Vitals Check](./07_Vitals_Check.md) | `/assess/vitals` |
| 8 | [Fitness Assessment](./08_Fitness_Assessment.md) | `/assess/fitness` |
| 9 | [Goal Forecasting](./09_Goal_Forecasting.md) | `/assess/goal-forecasting` |

---

## Common Patterns Across All Assess Modules

### Client Selection
Every assess screen requires selecting a client first. Only **premium/eligible clients** appear in the selector. Search by name, phone, or email.

### Phone Number Format
All phone fields across every assess screen accept **10 digits only** — no country code prefix (+91 not needed).

### Confirmation Dialogs for Deletion
When permanently deleting a record, the user must type the **client's name** (plain text, no HTML tags) to confirm. Example: type `Sandhiya` — not `<strong>Sandhiya</strong>`.

### Known Issues to Fix
1. Branch Add screen — step-wise activity not working properly (fix in progress)
2. Phone fields — enforce 10-digit validation, strip +91 prefix everywhere
3. Delete confirmation — render name as plain text, not raw HTML tags

---

## Data Relationships

```
Client (member)
  ├── PAR-Q+ questionnaire         (1 active per client)
  ├── Vitals history               (many records)
  ├── Body metrics history         (many records)
  ├── Posture assessments          (many records)
  ├── Balance assessments          (many records)
  ├── Nutritional assessment
  │     └── Diet plans             (many per client)
  ├── Fitness assessments
  │     ├── Cardiorespiratory      (many records)
  │     ├── Muscular strength      (many records)
  │     ├── Muscular endurance     (many records)
  │     └── Flexibility            (many records)
  ├── Assessment report            (generated from above data)
  └── Goal forecasting             (calculated on demand)
```
