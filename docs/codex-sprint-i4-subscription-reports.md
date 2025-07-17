# Codex: Sprint I4 – Subscription Reports & Recurring Exports

## Overview
This sprint delivers automated reporting tools for Partner-tier organizations and high-level partners. Organizations can subscribe to scheduled exports that summarize engagement, donations and grant metrics on a weekly or monthly basis.

## Goals
| Feature | Outcome |
| ------- | ------- |
| **Scheduled reports** | Email PDF/CSV every week or month |
| **Time-filtered exports** | Event and engagement data in a date range |
| **Grant mode export** | Custom "Events + Reach + Donors" summary |
| **Report scheduler UI** | Partner orgs choose delivery frequency and type |

## Data Structure
`ap_org_report_subscriptions` table stores subscriptions.
```sql
CREATE TABLE ap_org_report_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT,
  email VARCHAR(255),
  frequency VARCHAR(10), -- weekly | monthly
  format VARCHAR(10), -- csv | pdf
  report_type VARCHAR(20), -- engagement | donors | grant
  last_sent DATETIME
);
```

## 1. Report Scheduler UI
Admin page `/wp-admin?page=org-reports` visible only to Partner organizations.
- Add or remove email subscribers
- Select report type, format (CSV or PDF) and frequency
- Toggle each subscription active/inactive

## 2. Scheduled Delivery via Cron
Use WordPress cron or an external scheduler to trigger delivery.
```php
wp_schedule_event( strtotime('next Monday 6am'), 'weekly', 'ap_send_scheduled_reports' );
```
The cron task finds active rows matching the frequency and sends the generated report via email. After sending, update `last_sent` for that row.

## 3. Report Types & Format Specs
### A. engagement (CSV)
| Field | Example |
| ----- | ------- |
| Event | "Open Studio Night" |
| Date | "2025-07-05" |
| RSVPs | 138 |
| Follows | 42 |
| Avg Views | 313 |
| Saves | 55 |

### B. donors (CSV)
| Field | Example |
| ----- | ------- |
| Donor Name | Jane Patron |
| Email | jane@example.com |
| Amount | $75.00 |
| Date | 2025-06-28 |
| Notes | Stripe |

### C. grant (PDF)
The PDF includes:
- Summary totals (events and reach)
- Top 5 event highlights
- Donor summary table
- Audience growth chart (RSVP trend)
Use Dompdf or a similar library. Grant PDF generation is handled by the
`GrantReportBuilder` class which outputs a simple summary table along with
top event and donor sections.

### Sample Output
```
Grant Report
============
Summary
- Events: 5
- Reach: 350

Top Events
- Open Studio (120 RSVPs)

Donors
- Alice - $100
```

## 4. One-Off Manual Export
Organizations can also manually download reports via REST:
```http
GET /artpulse/v1/orgs/{org_id}/report?type=grant&from=2025-01-01&to=2025-07-31
```
Returns a CSV or PDF file depending on the request.

## 5. Access Control
Only `org_admin` or `report_manager` roles may manage subscriptions or trigger exports. Recurring setup is limited to Partner-tier organizations.

## QA Checklist
- Create subscription → report sent on schedule
- Grant PDF includes all sections correctly
- Report link download works for logged-in org admin
- Export respects date filters
- Multiple subscribers per org supported

## Developer Sprint Checklist
- [x] `ap_org_report_subscriptions` table created
- [x] Report generator for three types
- [x] Scheduled delivery cron live
 - [x] PDF generator for grant reports
- [x] Admin UI for managing subscriptions
- [x] REST route for manual download

## Codex Docs to Update
- Reporting System
- Subscription Logic, Report Types
- Partner Tools
- Grant Export PDF Spec
- Engagement Metrics Explained
- Admin UX
- Report Scheduler Page
- Report Preview
- API Docs `/orgs/{id}/report`
