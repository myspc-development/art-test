---
title: Admin Analytics Codex
category: admin
role: developer
last_updated: 2025-07-20
status: complete
---

# ArtPulse Codex: Admin & Analytics

This guide outlines optional modules for advanced reporting, custom RSVP fields, segmentation and automated reminders. It targets developers extending the plugin.

## 1. Advanced Reporting

A `ReportingManager` class provides CSV exports and REST endpoints. Available types include attendance, engagement, demographics, feedback and time‑series trends.

Example endpoint:

```
GET /wp-json/artpulse/v1/admin/export?type=attendance&event_id=123
```

The manager restricts access by capability and streams CSV data using `fputcsv()`.

## 2. Custom Fields & Surveys

Use `CustomFieldsManager` to define extra RSVP fields. Fields are stored in the `ap_custom_fields` table and displayed automatically on the RSVP form. `SurveyManager` handles post‑event surveys and responses.

Endpoints:

```
GET/POST /wp-json/artpulse/v1/event/{id}/rsvp/custom-fields
GET/POST /wp-json/artpulse/v1/event/{id}/survey
```

## 3. Segmentation

`SegmentationManager` exposes a query interface to filter users by interests, location and activity. Segments can be exported as CSV through the `/admin/users` endpoint.

## 4. Automated Reminders

`ReminderManager` schedules pre‑event and post‑event notifications via WP‑Cron. Admins can configure timing and messages per event. Hooks are available before and after a reminder is sent.

## Extensibility

Developers can customize export types with the `artpulse_admin_export_types` filter and adjust reminder behavior using `artpulse_before_event_reminder_sent` and `artpulse_after_event_reminder_sent` actions.


## 5. Integration with Analytics Dashboard
The analytics modules tie directly into the metrics collected for the [Analytics Pilot](../../analytics-pilot.md). Data is stored in custom tables so the plugin can generate granular charts without overloading WordPress core tables. Use the `AnalyticsDashboard` class to push new datasets into the charts.

## 6. Extending Reports
If your deployment requires different KPIs, register a report class implementing `ReportInterface`. The system auto-discovers these classes via the `artpulse_report_classes` filter. Developers can look at [Analytics Reporting Codex](./analytics-reporting-codex.md) for the complete data schema and JSON examples.

## 7. Permissions
Dashboards are only available to users with the `ap_premium_member` capability. See [Multi-Org Roles and Permissions](../../multi-org-roles-permissions.md) for the mapping between capabilities and partner tiers.

By following these conventions you can customize metrics and automate reminders while ensuring admin screens remain performant.

💬 Found something outdated? Submit Feedback
