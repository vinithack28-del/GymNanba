# GymOS — Assess Group
# Module 03: Nutritional Assessment (Diet Record)

**URL:** `/assess/nutrition`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Adds and manages diet plans for selected clients so trainers can track meals, plan dates, and nutritional guidance in one place.

---

## 1. Navigation Flow

```
Assess → Nutritional Assessment
  → Select Client
    → Diet Plans section loads
      → Create Diet Plan (desktop) / Add (mobile)
        → Diet Record dialog opens
          → Enter plan details (plan date + meals)
            → Save
```

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  Nutritional Assessment                                      │
├──────────────────────────────────────────────────────────────┤
│  Select Client: 🔍 Search by name / phone / email            │
├──────────────────────────────────────────────────────────────┤
│  [Diet Plans section loads after client selection]           │
│                                                              │
│  Diet Plans                        [Create Diet Plan]        │
│  ┌─────────────────────────────────────────────────────┐     │
│  │  Plan date  │  Plan name  │  Meals  │  Actions      │     │
│  │  Jun 12     │  Weight loss│  5      │  View Edit Del│     │
│  │  May 28     │  Bulking    │  6      │  View Edit Del│     │
│  └─────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Client Selection

- Search by name, phone (10 digits, no +91), or email
- Only premium/eligible clients shown
- After selection: all diet plans for that client are listed

---

## 4. Diet Plans List

| Column | Notes |
|---|---|
| **Plan date** | Date the plan was created/assigned |
| **Plan name** | e.g. "Weight loss plan", "Bulking phase" |
| **Meals** | Count of meal entries in this plan |
| **Actions** | View, Edit, Delete |

**Empty state:** "No diet plans yet. Click Create Diet Plan to add one."

---

## 5. Create Diet Plan — Diet Record Dialog

Clicking **Create Diet Plan** (desktop) or **Add** (mobile) opens a dialog/drawer.

### Dialog fields

| Field | Type | Validation | Required |
|---|---|---|---|
| **Plan name** | Text | 2–100 chars | Yes |
| **Plan date** | Date picker | Default today | Yes |
| **Goal / notes** | Textarea | Max 500 chars | No |

### Meals section (within the dialog)

One or more meal entries. Each meal row:

| Field | Type | Validation | Required |
|---|---|---|---|
| **Meal name** | Text | e.g. Breakfast, Lunch, Pre-workout | Yes |
| **Time** | Time picker | — | No |
| **Food items** | Textarea / tag input | List of food items | Yes |
| **Calories (kcal)** | Number | ≥ 0 | No |
| **Protein (g)** | Number | ≥ 0 | No |
| **Carbs (g)** | Number | ≥ 0 | No |
| **Fat (g)** | Number | ≥ 0 | No |
| **Notes** | Text | Max 200 chars | No |

**Add meal:** "+ Add meal" button appends another meal row.
**Remove meal:** × button on each meal row (minimum 1 meal required).

**Total row** at bottom of meals: auto-sums Calories, Protein, Carbs, Fat across all meals.

---

## 6. Edit Diet Plan

- Click **Edit** from the actions menu on any diet plan row
- Same Diet Record dialog opens, pre-filled with existing plan data
- All fields editable
- Click **Save** to update

---

## 7. View Diet Plan

- Click **View** from actions menu
- Read-only expanded view of the plan
- Shows all meals in a structured table
- **Edit** button available from within the view

---

## 8. Delete Diet Plan

- Click **Delete** from actions menu
- Confirm dialog: type client name in plain text (e.g. `Sandhiya`) to permanently delete
- On confirm: plan and all its meal entries are permanently removed

---

## 9. Mobile Behaviour

| Desktop | Mobile equivalent |
|---|---|
| Create Diet Plan button | Add button (floating or top bar) |
| Table view for plans | Card list view |
| Full dialog | Bottom sheet drawer |

---

## 10. API Endpoints

```
GET /api/v1/assess/nutrition/:clientId/diet-plans
  Response: { plans: [{ id, name, date, meal_count, created_at }] }

POST /api/v1/assess/nutrition/:clientId/diet-plans
  Body: {
    name,
    plan_date,
    goal_notes?,
    meals: [{
      meal_name, time?, food_items, calories?, protein_g?,
      carbs_g?, fat_g?, notes?
    }]
  }
  Response 201: { plan_id }

GET /api/v1/assess/nutrition/:clientId/diet-plans/:planId
  Response: { plan with full meals array }

PUT /api/v1/assess/nutrition/:clientId/diet-plans/:planId
  Body: same as POST (partial update supported)
  Response 200: { plan }

DELETE /api/v1/assess/nutrition/:clientId/diet-plans/:planId
  Response 200: { deleted: true }
```

---

## 11. Validation Rules

| Field | Rule |
|---|---|
| Client | Required — cannot open plan list without selecting a client |
| Plan name | 2–100 chars |
| Plan date | Required; cannot be more than 1 year in the past |
| Meals | At least 1 meal required per plan |
| Meal name | Required per meal row |
| Food items | Required per meal row |
| Phone search | 10 digits, no +91 |
| Delete confirmation | Type client name as plain text — HTML tags must not appear |

---

## 12. Business Rules

- Multiple diet plans per client — no limit
- Plans are not active/inactive toggled — all plans are stored as history
- Totals (calories, macros) are summed client-side in the UI; stored per-meal in DB
- Trainer can create plans for any client assigned to their gym / branch
