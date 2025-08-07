---
title: Organization Dashboard Overview
category: user
role: organization
last_updated: 2025-08-30
status: complete
---
# Organization Dashboard Overview

Organization administrators access their tools through the `[ap_user_dashboard]` shortcode. By default, `DashboardController` loads these widgets:

1. `org_event_overview` – Snapshot of upcoming events.
2. `artpulse_analytics_widget` – Traffic and engagement metrics.
3. `rsvp_stats` – RSVP totals across events.
4. `my-events` – Quick links to manage events.
5. `org_ticket_insights` – Ticket sales analysis.
6. `org_team_roster` – Manage team members and roles.
7. `audience_crm` – Audience relationship management.
8. `org_broadcast_box` – Send announcements to followers.
9. `org_approval_center` – Review pending submissions.
10. `webhooks` – Configure outbound webhooks.
11. `support-history` – Recent support conversations.

Admins may tailor this lineup via the dashboard builder. Customized role defaults are stored in `ap_dashboard_widget_config`, while individual changes persist to the `ap_dashboard_layout` user meta key.
