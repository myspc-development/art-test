---
title: Community Moderation Codex: Reports, Bans, and Safety Tools
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Community Moderation Codex: Reports, Bans, and Safety Tools

This guide outlines how ArtPulse implements community safety features. Moderators, organizations and admins can investigate reports, issue bans and review audit logs through a dedicated dashboard.

## 1. Reporting System
Users may report posts, comments, events or entire profiles. A `ReportButtonComponent` appears on all content with a dropdown for common reasons:

- Harassment
- Spam
- Inappropriate content
- Misinformation
- Other (allows custom notes)

Each report stores the reporter, target reference, reason and optional notes in the `ap_reports` table.

## 2. Moderation Dashboard
The `ModerationQueueWidget` lists pending reports, active bans and flagged content. Moderators can sort or filter by type, status, severity and reported user. From this view they may resolve items or escalate them to administrators.

## 3. Ban & Suspension System
Moderators can issue temporary or permanent bans. Options include 7, 14 or 30‑day suspensions, permanent removal or content‑only restrictions such as disabling comments. Bans may be scoped globally or limited to a specific event or organization. Each action is logged to maintain a history per user and may trigger automatically after a threshold of verified reports.

## 4. Escalation Queue
Severe reports can be marked for administrative review. All moderation actions are written to `ap_moderation_log` with the moderator ID, notes and timestamp. Manual overrides, reversals and notifications are recorded for audit purposes.

## 5. Database Tables
```sql
CREATE TABLE wp_ap_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT NOT NULL,
  target_type VARCHAR(40) NOT NULL,
  target_id BIGINT NOT NULL,
  reason VARCHAR(40) NOT NULL,
  notes TEXT NULL,
  status VARCHAR(20) DEFAULT 'open',
  severity VARCHAR(20) NULL,
  created_at DATETIME NOT NULL
);

CREATE TABLE wp_ap_bans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  scope VARCHAR(40) NOT NULL,
  duration INT NULL,
  reason VARCHAR(200) NOT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME NULL,
  status VARCHAR(20) DEFAULT 'active'
);

CREATE TABLE wp_ap_moderation_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  moderator_id BIGINT NOT NULL,
  action_type VARCHAR(40) NOT NULL,
  target_type VARCHAR(40) NOT NULL,
  target_id BIGINT NOT NULL,
  notes TEXT NULL,
  timestamp DATETIME NOT NULL
);
```

## 6. REST API Endpoints
| Method | Endpoint | Description |
| ------ | -------- | ----------- |
| POST | `/report` | Submit a report |
| GET | `/moderation/reports` | Fetch open reports |
| POST | `/moderation/ban` | Ban or suspend a user |
| POST | `/moderation/action-log` | Record manual moderation |
| GET | `/moderation/audit-log` | View moderation history |
| POST | `/moderation/escalate` | Flag item for admin review |

## 7. Front-End Components
- **ReportButtonComponent** – visible on all reportable items
- **ModerationQueueWidget** – inbox for moderators
- **BanUserModal** – form to issue bans or suspensions
- **AuditLogView** – searchable action log
- **EscalationSidebar** – admin queue viewer

## 8. Permissions Matrix
| Role | Can Report | Can Moderate | Can Ban | Can Escalate |
| ---- | ---------- | ------------ | ------- | ------------ |
| Member | ✅ | ❌ | ❌ | ❌ |
| Artist | ✅ | Limited (own content) | ❌ | ❌ |
| Organization | ✅ | ✅ (own events) | ✅ (within org) | ✅ |
| Moderator | ✅ | ✅ | ✅ | ✅ |
| Admin | ✅ | ✅ | ✅ (global) | ✅ (review) |

## 9. Optional Enhancements
- Automatically hide content after multiple verified reports
- Send email notifications to flagged users and moderators
- AI‑assisted flagging for offensive material
- Moderator performance dashboard

> 💬 *Found something outdated? [Submit Feedback](../../feedback.md)*
