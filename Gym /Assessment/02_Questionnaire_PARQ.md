# GymOS — Assess Group
# Module 02: Questionnaire (PAR-Q+)

**URL:** `/assess/questionnaire`
**Access:** Gym owner, Branch manager, Trainer (own clients)
**Purpose:** Opens the PAR-Q+ (Physical Activity Readiness Questionnaire) screen so trainers can add or update a client's readiness questionnaire. Identifies health conditions that may require medical clearance before starting a fitness program.

---

## 1. Navigation Flow

```
Assess (sidebar group)
  → Questionnaire
    → Select client
      → PAR-Q+ questionnaire form loads
        → Fill / update answers
          → Save
```

Alternate path: Assess → find the PAR-Q+ card → click/tap → taken to Questionnaire page.

---

## 2. Page Layout

```
┌──────────────────────────────────────────────────────────────┐
│  PAR-Q+ Questionnaire                                        │
├──────────────────────────────────────────────────────────────┤
│  Select Client: 🔍 Search by name / phone / email            │
├──────────────────────────────────────────────────────────────┤
│  [Questionnaire loads after client selection]                │
│                                                              │
│  Section 1 — General Health                                  │
│  Q1. [question text]        ○ Yes  ○ No                      │
│  Q2. [question text]        ○ Yes  ○ No                      │
│  ...                                                         │
│                                                              │
│  Section 2 — Follow-up (if any Yes in Section 1)            │
│  ...                                                         │
│                                                              │
│  [Save]   [Clear]                                            │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Client Selection

- Search by client name, phone (10 digits, no +91), or email
- Only premium/eligible clients shown
- If client already has a PAR-Q+ on record: form pre-fills with existing answers
- If no prior record: blank form shown

---

## 4. PAR-Q+ Structure

The PAR-Q+ is a two-stage questionnaire.

### Section 1 — General Health Questions (7 questions)

All questions are Yes / No.

| # | Question |
|---|---|
| 1 | Has your doctor ever said that you have a heart condition OR high blood pressure? |
| 2 | Do you feel pain in your chest at rest, during your daily activities of living, OR when you do physical activity? |
| 3 | Do you lose balance because of dizziness OR have you lost consciousness in the last 12 months? |
| 4 | Have you ever been diagnosed with another chronic medical condition (other than heart disease or high blood pressure)? |
| 5 | Are you currently taking prescribed medications for a chronic medical condition? |
| 6 | Do you currently have (or have had within the past 12 months) a bone, joint, or soft-tissue (muscle, ligament, or tendon) problem that could be made worse by becoming more physically active? |
| 7 | Has your doctor ever said that you should only do medically supervised physical activity? |

**If all answers are No:** Client is cleared for physical activity — form can be saved.

**If any answer is Yes:** Section 2 follow-up questions are revealed for the relevant condition.

---

### Section 2 — Follow-up Questions

Shown **only** when relevant Section 1 question is answered Yes. Follow-up questions are grouped by condition type (heart condition, musculoskeletal, chronic condition, etc.). Each follow-up is also Yes / No.

If follow-up answers indicate risk: a medical clearance flag is recorded — trainer is alerted that medical clearance from a physician is recommended before starting.

---

## 5. Form Behaviour

| Behaviour | Detail |
|---|---|
| Conditional reveal | Section 2 questions appear dynamically based on Section 1 Yes answers |
| Pre-fill | If client has a prior PAR-Q+ record, answers load automatically for review/update |
| Validation | All Section 1 questions must be answered before Save is allowed |
| Save | Overwrites the existing record (one active PAR-Q+ per client) |
| Clear | Resets all answers to unanswered state |
| Completion date | Auto-set to today on Save |

---

## 6. After Saving

| Result | System action |
|---|---|
| All No | Status = "Cleared". No flag. Saved with completion date. |
| Any Yes (Section 1 only, no follow-up risk) | Status = "Conditional". Note added. |
| Follow-up indicates medical risk | Status = "Medical clearance required". Flag shown on client profile and Assessment Report. |

---

## 7. Viewing Existing Record

If a record already exists, the page shows:
- Completion date
- Status (Cleared / Conditional / Medical clearance required)
- All answers in read-only format
- **Edit** button to update answers

---

## 8. API Endpoints

```
GET /api/v1/assess/parq/:clientId
  Response: {
    client_id,
    status,           -- cleared | conditional | medical_clearance_required | not_started
    completed_date,
    section1: [{ question_id, question_text, answer: true|false }],
    section2: [{ question_id, question_text, answer: true|false, condition_group }],
    flags: [{ type, description }]
  }

POST /api/v1/assess/parq/:clientId
  Body: {
    section1: [{ question_id, answer: true|false }],
    section2: [{ question_id, answer: true|false }]
  }
  Response 200: { status, completed_date, flags }
```

---

## 9. Validation Rules

| Rule | Detail |
|---|---|
| Client required | Cannot save without selecting a client |
| All Section 1 questions | Must be answered (Yes or No) before Save |
| Section 2 questions | Required only if triggered by a Yes in Section 1 |
| Phone search | 10 digits, no +91 prefix |

---

## 10. Business Rules

- One active PAR-Q+ per client — saving overwrites the previous record
- Historical PAR-Q+ records are not stored (only current record kept)
- Medical clearance flag propagates to Assessment Report and client profile header
- Trainer cannot remove a medical clearance flag manually — it clears only when the questionnaire is updated and follow-up answers no longer indicate risk
- Delete confirmation for any linked assessment record: type client name in plain text (e.g. `Sandhiya`) — not as HTML tags
