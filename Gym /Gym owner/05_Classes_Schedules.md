# GymOS — Gym Owner Portal
# Module 05: Classes & Schedules

**URL:** `/classes`
**Sub-pages:** `/classes/timetable`, `/classes/book`, `/classes/trainers`
**Access:** Gym owner (full), Branch manager (full), Receptionist (view + book), Trainer (own classes only)
**Purpose:** Manage group fitness classes, weekly schedules, member bookings, and trainer assignments.

---

## 1. Timetable Layout (`/classes/timetable`)

Default view: **weekly calendar** (Mon–Sun, 7-day). Toggle to **List view**.

```
  Week Jun 9–15  [< Prev]  [Next >]   Branch: All ▾   [+ Create class]

         Mon 9    Tue 10   Wed 11   Thu 12   Fri 13   Sat 14   Sun 15
06:00   [Yoga 6AM]
07:00   [HIIT 7AM]          [HIIT]
08:00            [Zumba]             [Zumba]
...
```

**Class block shows:** name, start–end time, trainer name, enrolled/capacity (e.g. 12/20), status colour.

**Status colour coding:**

| Status | Colour |
|---|---|
| Scheduled (upcoming, open) | Green |
| Full (at capacity, waitlist open) | Blue |
| Cancelled | Amber |
| Completed | Grey |

---

## 2. Create Class Form

| Field | Validation | Required |
|---|---|---|
| **Class name** | 2–80 chars | Yes |
| **Class type** | Yoga / HIIT / Zumba / Strength / Pilates / Crossfit / Aerobics / Custom | Yes |
| **Branch** | Active branches | Yes |
| **Room / area** | Max 80 chars e.g. "Studio A" | No |
| **Trainer** | Active trainers at selected branch | No |
| **Start time** | Time picker | Yes |
| **End time** | Must be after start time | Yes |
| **Repeat** | Does not repeat / Daily / Weekly on selected days | Yes |
| **Days of week** | Mon–Sun multi-select (if weekly repeat) | Yes if weekly |
| **Start date** | Cannot be in the past | Yes |
| **End date** | After start date (if recurring) | Yes if recurring |
| **Max capacity** | 1–500 | Yes |
| **Allow waitlist** | Toggle Yes / No | Yes |
| **Visible to members** | Toggle — if off, not bookable from member app | Yes |
| **Description** | Max 500 chars, shown to members | No |

---

## 3. Edit and Cancel Rules

**Edit class:**
- For recurring: "Edit this class only" or "Edit this and all future classes"
- Cannot reduce capacity below current booking count
- Changing start time when bookings exist: show warning, require confirmation

**Cancel class:**
- Select reason: Trainer unavailable / Facility maintenance / Holiday / Other
- All booked members notified via WhatsApp/SMS (per notification settings)
- Bookings cancelled automatically — no fee deduction

---

## 4. Book a Class (`/classes/book`)

**Flow 1 — Book for a member:**
1. Search member by name / phone / member ID
2. Show member card (name, plan, status)
3. Show upcoming bookable classes
4. Select class → confirm → booking created

**Flow 2 — Browse and book:**
1. Browse timetable
2. Click a class → see class details + enrolled member list
3. Search and select member → confirm booking

**Waitlist logic:**
- When class is full: new bookings go to waitlist position
- When a booking is cancelled: first waitlisted member is auto-enrolled and notified via WhatsApp/SMS

---

## 5. Trainers Page (`/classes/trainers`)

**Table columns:** Photo | Name | Specialisation | Phone | Email | Classes this week | Status | Actions (View schedule, Edit, Deactivate)

- **Add trainer:** Links to Staff > Add staff. Trainer role automatically appears here.
- **View trainer schedule:** Weekly calendar view filtered to one trainer showing all assigned classes.

---

## 6. Class Attendance Marking

After class end time, trainer or admin can mark attendance:
1. Class appears in "Mark attendance" list
2. All enrolled members shown with toggle: Present / Absent
3. Late cancellation: marked separately for reporting
4. Submit closes the attendance record — cannot be reopened

---

## 7. Capacity and Waitlist

| Event | System action |
|---|---|
| Booking when class is full | Member added to waitlist; booking status = `waitlisted` |
| Booking cancelled | First waitlisted member auto-enrolled; status changed to `booked`; notification sent |
| Class capacity increased | Waitlisted members auto-enrolled up to new limit |

---

## 8. API Endpoints

```
GET /api/v1/classes
  Query: from, to, branch_id, trainer_id, type, status
  Response: { classes: [...] }

POST /api/v1/classes
  Body: { name, type, branch_id, room?, trainer_id?, start_time, end_time,
          repeat, days_of_week?, start_date, end_date?, max_capacity,
          allow_waitlist, visible_to_members, description? }
  Response 201: { class_id, occurrence_ids: [...] }

PUT /api/v1/classes/:id
  Body: { scope: 'this' | 'future', ...fields }
  Response 200: { updated_count }

POST /api/v1/classes/:id/cancel
  Body: { reason, scope: 'this' | 'future' }
  Response 200: { notified_members: N }

GET /api/v1/classes/:id/bookings
  Response: { booked: [...], waitlisted: [...] }

POST /api/v1/classes/:id/book
  Body: { member_id }
  Response 201: { booking_id, status: 'booked' | 'waitlisted' }

DELETE /api/v1/classes/:id/book/:bookingId
  Response 200: { cancelled: true, waitlist_promoted?: member_id }

POST /api/v1/classes/:id/attendance
  Body: { attendances: [{ member_id, status: 'present' | 'absent' | 'late_cancel' }] }
  Response 200: { saved: true }
```

---

## 9. Database Schema

```sql
CREATE TABLE classes (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id       UUID NOT NULL REFERENCES tenants(id),
  branch_id       UUID NOT NULL REFERENCES branches(id),
  name            VARCHAR(80) NOT NULL,
  type            VARCHAR(30) NOT NULL,
  room            VARCHAR(80),
  trainer_id      UUID REFERENCES staff(id),
  start_time      TIME NOT NULL,
  end_time        TIME NOT NULL,
  max_capacity    INTEGER NOT NULL,
  allow_waitlist  BOOLEAN NOT NULL DEFAULT true,
  visible         BOOLEAN NOT NULL DEFAULT true,
  description     TEXT,
  parent_id       UUID REFERENCES classes(id),  -- recurring series
  class_date      DATE NOT NULL,
  status          VARCHAR(20) NOT NULL DEFAULT 'scheduled',
  cancel_reason   TEXT,
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE class_bookings (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  class_id    UUID NOT NULL REFERENCES classes(id),
  member_id   UUID NOT NULL REFERENCES members(id),
  tenant_id   UUID NOT NULL REFERENCES tenants(id),
  status      VARCHAR(20) NOT NULL DEFAULT 'booked',
                -- booked | waitlisted | cancelled | attended | absent | late_cancel
  waitlist_pos INTEGER,  -- position in waitlist (1 = first)
  booked_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  booked_by   UUID REFERENCES staff(id),
  CONSTRAINT uq_class_member UNIQUE (class_id, member_id)
);
```

---

## 10. Business Rules

- Trainer cannot be double-booked at the same time in the same branch
- Class capacity is a hard cap — system prevents overbooking (waitlist is the overflow)
- Cancelled class: no automatic fee deduction; refund must be processed manually if applicable
- Cannot delete a class with past attendance records — cancel instead

---

## 11. Notifications

| Event | Notification sent to |
|---|---|
| Class cancelled | All booked members (WhatsApp/SMS) |
| 1 hour before class | Booked members (if configured) |
| Waitlist promoted to booked | Promoted member |
| Attendance marked | No notification |
