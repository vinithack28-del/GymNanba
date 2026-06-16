# GymOS — Gym Owner Portal
# Module 12: Reports

**URL:** `/reports`
**Sub-pages:** `/reports/revenue`, `/reports/members`, `/reports/attendance`, `/reports/staff`
**Access:** Owner (all reports, all branches); Branch manager (attendance + staff, own branch); Accountant (revenue only)
**Data freshness:** Real-time for data < 3 months. Pre-aggregated nightly for older data.

---

## 1. Reports Landing Page

Grid of 4 report cards:

| Card | Report | Description |
|---|---|---|
| Revenue | `/reports/revenue` | Income, collections, dues |
| Members | `/reports/members` | Registrations, churn, demographics |
| Attendance | `/reports/attendance` | Check-ins, peak hours, class usage |
| Staff | `/reports/staff` | Attendance, hours, performance |

Each card shows last generated timestamp and "Open report" button.

---

## 2. Common Filters (All Reports)

| Filter | Presets |
|---|---|
| **Date range** | Today / This week / This month / Last month / Q1 / Q2 / Q3 / Q4 / Last 3 months / Last 6 months / Last year / Custom |
| **Branch** | All / specific branch (shown only if 2+ branches) |
| **Plan** | All / specific plan (Revenue and Members reports only) |

---

## 3. Revenue Report (`/reports/revenue`)

### KPI Cards

| KPI | Calculation |
|---|---|
| Total revenue | Sum of all payments in period |
| % change vs previous | Comparison to equivalent previous period |
| Payments count | Number of transactions |
| Average transaction | Total ÷ count |
| Total pending dues | Sum of members.balance_paise across all members |

### Charts

| Section | Chart type |
|---|---|
| Revenue trend | Line chart (daily/weekly/monthly selectable toggle) |
| By membership plan | Bar chart |
| By payment method | Donut chart (Cash / UPI / Card / Bank / Cheque) |
| By branch | Bar chart (multi-branch only) |

### Tables

- **Day-by-day breakdown:** Date | Payments count | Revenue | GST
- **Top 10 paying members in period:** Name | Plan | Total paid

### Export
- **CSV:** Raw payment rows matching filters
- **PDF:** Formatted report with charts embedded as images, gym header, date range

---

## 4. Members Report (`/reports/members`)

### KPI Cards

| KPI | Calculation |
|---|---|
| New members | COUNT registered in period |
| Churn rate | % members expired and not renewed in period |
| Retention rate | 100% − churn rate |
| Net growth | New members − churned members |

### Charts

| Section | Chart type |
|---|---|
| New registrations trend | Line chart, daily |
| Plan distribution | Donut chart — members by plan |
| Gender split | Donut chart |
| Age group distribution | Bar chart: <18, 18–25, 26–35, 36–45, 46–60, 60+ |

### Tables
- Members by branch: Branch | Count | % of total
- Month-on-month comparison: Month | New | Churned | Net | Total

---

## 5. Attendance Report (`/reports/attendance`)

### KPI Cards

Total check-ins | Unique members | Avg check-ins per member per month | Walk-ins count

### Charts

| Section | Chart type |
|---|---|
| Check-ins trend | Line chart, daily |
| Peak hours heatmap | 7 rows (Mon–Sun) × 24 cols (hours 0–23). Cell colour intensity = check-in count. Hover shows exact count. |
| Method breakdown | Donut (QR / Biometric / Manual) |
| Walk-ins vs member check-ins | Stacked bar chart, daily |

### Tables
- **Class attendance summary:** Class name | Booked | Attended | % attendance

---

## 6. Staff Report (`/reports/staff`)

### Tables

**Attendance summary:**
Staff name | Role | Days present | Days absent | Total hours worked

**Classes by trainer:**
Trainer name | Classes scheduled | Classes held | Cancellations | % held

**Fees collected per staff:**
Staff name | Role | Payments count | Total collected (INR)

**POS sales by staff:**
Staff name | Bills raised | Total sales amount (INR)

---

## 7. Export Formats

| Format | Contents | Notes |
|---|---|---|
| **CSV** | All raw rows matching active filter. No aggregation. | Suitable for spreadsheet analysis. |
| **PDF** | Formatted report. Charts as PNG images embedded. Gym header + date range subtitle. | Printable. |

**Filename convention:** `gymos_{type}_{gym_name}_{from}_{to}.{ext}`
e.g. `gymos_revenue_fitzoned_2026-06-01_2026-06-30.pdf`

**Large exports** (> 10,000 rows): Queued as background job. Email sent with download link when ready (link valid 48 hours).

---

## 8. Scheduled Reports

Configure in Notifications > Settings:

| Setting | Options |
|---|---|
| Report types | One or multiple: Revenue / Members / Attendance / Staff |
| Frequency | Monthly (1st of month) or Weekly (Monday morning) |
| Recipients | Owner email + additional email addresses (comma separated) |
| Format | PDF of previous month/week |

---

## 9. API Endpoints

```
GET /api/v1/reports/revenue
  Query: from, to, branch_id, plan_id
  Response: { kpis, trend, by_plan, by_method, by_branch,
              daily_breakdown, top_members, pending_dues_total }

GET /api/v1/reports/members
  Query: from, to, branch_id
  Response: { kpis, new_trend, by_plan, by_gender, by_age,
              by_branch, monthly_comparison }

GET /api/v1/reports/attendance
  Query: from, to, branch_id
  Response: { kpis, trend, heatmap, by_method,
              walkins_vs_members, class_summary }

GET /api/v1/reports/staff
  Query: from, to, branch_id, staff_id
  Response: { attendance_summary, classes_by_trainer,
              fees_collected, pos_sales }

GET /api/v1/reports/:type/export
  Query: same as report + format (csv|pdf)
  Response: file download
  or 202 Accepted: { job_id } if > 10,000 rows

POST /api/v1/reports/schedule
  Body: { report_types, frequency, recipients }
  Response 201: { schedule_id }
```

---

## 10. Data Freshness & Caching

| Data age | Strategy |
|---|---|
| < 3 months | Real-time queries on live tables |
| ≥ 3 months | Pre-aggregated `report_daily_summaries` table, refreshed nightly at 2:00 AM IST |

Cache invalidation: any payment, member, or attendance event triggers cache bust for that date/branch combination.

---

## 11. Access Control

| Role | Revenue | Members | Attendance | Staff | Branch scope |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | All |
| Branch manager | No | No | Yes | Yes | Own branch only |
| Accountant | Yes | No | No | No | All |
| Receptionist | No | No | No | No | — |
| Trainer | No | No | No | Own data only | — |
