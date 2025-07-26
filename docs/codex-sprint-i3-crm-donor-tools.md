---
title: Codex: Sprint I3 – CRM & Donor Tools for Orgs
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Codex: Sprint I3 – CRM & Donor Tools for Orgs

## Overview
This sprint introduces lightweight, integrated CRM and donor tooling for ArtPulse organizations — helping galleries, nonprofits, and artist groups track relationships, improve outreach, and demonstrate impact. The features are particularly valuable for grant-funded or partner-tier accounts that need to show engagement data.

## Goals
| Tool | Purpose |
|------|---------|
| **Contact manager** | Save and tag users (attendees, patrons, curators) |
| **Engagement logs** | Track who RSVPed, followed, donated |
| **Smart email list export** | Outreach-ready, tag-filtered |
| **Donor analytics dashboard** | Amounts, trends, top supporters |
| **Grant export reports** | Save CSV or PDF with support data |

## Data Structures
### A. `ap_crm_contacts` Table
```sql
CREATE TABLE ap_crm_contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT,
  user_id BIGINT NULL,
  email VARCHAR(255),
  name VARCHAR(255),
  tags TEXT, -- JSON array
  first_seen DATETIME,
  last_active DATETIME
);
```

### B. `ap_donations` Table
```sql
CREATE TABLE ap_donations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT,
  user_id BIGINT NULL,
  amount DECIMAL(6,2),
  method VARCHAR(20), -- stripe, offline
  donated_at DATETIME
);
```

## 1. Contact Capture & Auto-Tagging
Contacts are automatically created whenever an email address can be
associated with an organization. Tags are added based on activity:

- **RSVP** → `rsvp`
- **Follow an artist/org** → `follower`
- **Donation** → `donor`
- **Event feedback** → `engaged`

Developers may also call the helper directly:

```php
ap_crm_add_tag($org_id, $email, 'follower');
```

## 2. CRM Dashboard UI (Removed)
The legacy admin page `/wp-admin?page=ap-org-crm&org_id=XX` has been removed.
Contact data can still be exported using custom queries or other tooling.

## 3. CSV Export Tool
Button **Export Contacts (CSV)** outputs:
- Name and email
- Tags
- Last active date
- RSVP count and donation totals
Optional **Send to Mailchimp** button via webhook or integration.

## 4. Donor Dashboard
Tab `/crm/donors` lists each donor:
- Donor, amount, last gift and method

Aggregate stats show totals for the year, repeat donor rate and average gift size.

## 5. Grant Export Tools
REST endpoint `GET /artpulse/v1/orgs/{id}/grant-report?format=csv` returns:
- Top 10 events by RSVPs
- Top donors
- Attendance over time
- Contact growth
Data can be emailed or downloaded.

## 6. Access Control
Only users with the role `admin` or `crm_manager` for the matching org may view or export CRM data. Roles are stored in `ap_org_roles`.

## QA Checklist
- RSVP adds contact to org CRM
- Donation logs to donor table
- Export returns valid CSV
- Tags editable in CRM UI
- Donor summary shows real totals
- Access limited to scoped org admin

## Developer Sprint Checklist
- [x] CRM table & model live
- [x] Auto-tagging from activity
- [x] CRM dashboard UI + filters
- [x] CSV export works
- [x] Donor table + dashboard
- [x] Grant export route added

## Codex Docs to Update
- CRM System
- Contact Model, Auto-Tag Rules
- Donor Tools
- Donation Logging, Analytics View
- Org Dashboard
- CRM Tab, Donor Tab, Exports
- Partner Handbook – “Using CRM for grant reports”

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
