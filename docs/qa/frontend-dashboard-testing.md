---
title: Frontend Dashboard Testing
category: qa
last_updated: 2025-08-03
status: draft
---

# Frontend Dashboard Testing

## Login Steps
1. Open the WordPress login page (`/wp-login.php`).
2. Sign in with test accounts for **Member**, **Artist**, and **Organization** roles.
3. After authentication, navigate to the page containing the `[ap_user_dashboard]` shortcode to load the dashboard.

## Expected Widgets by Role
Refer to [dashboard-widgets-by-role.md](dashboard-widgets-by-role.md) for the full matrix. The default widget sets are:

- **Member:** `widget_news`, `membership`, `upgrade`, `account-tools`, `recommended_for_you`, `my_rsvps`, `favorites`, `local-events`, `my-follows`, `notifications`, `messages`, `dashboard_feedback`, `cat_fact`.
- **Artist:** `artist_feed_publisher`, `artist_audience_insights`, `artist_spotlight`, `artist_revenue_summary`, `my_events`, `messages`, `notifications`, `dashboard_feedback`, `cat_fact`.
- **Organization:** `org_event_overview`, `artpulse_analytics_widget`, `rsvp_stats`, `my-events`, `org_ticket_insights`, `org_team_roster`, `audience_crm`, `org_broadcast_box`, `org_approval_center`, `webhooks`, `support-history`.

Verify that each role displays only the widgets listed above and that access warnings appear for missing capabilities.

## Duplication Check
Run the duplication check script to ensure widget IDs remain unique and the manifest is updated:

```bash
python scripts/widget-preview-guard-check.py
```

Review the generated `widget-preview-report.md` for any duplicate IDs or unmapped files.

> 💬 Found an issue? Submit feedback via [testing-strategy.md](testing-strategy.md) or open a ticket.
