---
title: Dashboard Widgets by Role QA Checklist
category: qa
last_updated: 2025-07-28
status: complete
---

This checklist verifies that each user role sees the correct widgets in both the live dashboard (`[ap_user_dashboard]` shortcode) and the Dashboard Builder.


## Default Widgets by Role
The plugin loads a baseline set of widgets for each new account. Administrators can change this list from the Dashboard Builder.

- **Member:** `widget_news`, `membership`, `upgrade`, `account-tools`, `recommended_for_you`, `my_rsvps`, `favorites`, `local-events`, `my-follows`, `notifications`, `messages`, `dashboard_feedback`, `cat_fact`
- **Artist:** `artist_feed_publisher`, `artist_audience_insights`, `artist_spotlight`, `artist_revenue_summary`, `my_events`, `messages`, `notifications`, `dashboard_feedback`, `cat_fact`

- **Organization:** `org_event_overview`, `artpulse_analytics_widget`, `rsvp_stats`, `my-events`, `org_ticket_insights`, `org_team_roster`, `audience_crm`, `org_broadcast_box`, `org_approval_center`, `webhooks`, `support-history`
| Widget ID | Required Capability | Member | Artist | Organization |
|-----------|--------------------|:------:|:------:|:------------:|
| membership | read | ✅ | ✅ | ✅ |
| upgrade | read | ✅ | ✅ | ✅ |
| local-events | read | ✅ | ✅ | ✅ |
| favorites | read | ✅ | ✅ | ✅ |
| my-follows | read | ✅ | ✅ | ✅ |
| creator-tips | read | ✅ | ✅ | ✅ |
| rsvps | read | ✅ | ✅ | ✅ |
| rsvp_stats | read | ✅ | ❌ | ✅ |
| my-events | read | ✅ | ✅ | ✅ |
| events | read | ✅ | ✅ | ✅ |
| messages | read | ✅ | ✅ | ✅ |
| account-tools | read | ✅ | ✅ | ✅ |
| support-history | read | ✅ | ✅ | ✅ |
| notifications | read | ✅ | ✅ | ✅ |
| role-spotlight | read | ✅ | ✅ | ✅ |
| widget_for_you_all | read | ✅ | ✅ | ✅ |
| widget_followed_artists | read | ✅ | ✅ | ❌ |
| upcoming_events_by_location | read | ✅ | ❌ | ❌ |
| followed_artists_activity | read | ✅ | ❌ | ❌ |
| artist_inbox_preview | read | ✅ | ✅ | ❌ |
| my_rsvps | read | ✅ | ❌ | ❌ |
| my_shared_events_activity | read | ✅ | ❌ | ❌ |
| recommended_for_you | read | ✅ | ❌ | ❌ |
| cat_fact | read | ✅ | ✅ | ✅ |
| dashboard_feedback | read | ✅ | ✅ | ✅ |
| instagram_widget | read | ✅ | ✅ | ❌ |
| widget_spotlight_events | read | ✅ | ❌ | ✅ |
| widget_spotlight_calls | read | ✅ | ✅ | ✅ |
| widget_spotlight_features | read | ✅ | ✅ | ✅ |
| artist_revenue_summary | read | ❌ | ✅ | ❌ |
| artist_spotlight | read | ❌ | ✅ | ❌ |
| widget_news | read | ✅ | ❌ | ❌ |
| artist_feed_publisher | read | ❌ | ✅ | ❌ |
| artist_audience_insights | read | ❌ | ✅ | ❌ |
| organization_dashboard | read | ❌ | ❌ | ✅ |
| organization_analytics | read | ❌ | ❌ | ✅ |
| org_messages | read | ❌ | ❌ | ✅ |
| lead_capture | read | ❌ | ❌ | ✅ |
| site_stats | read | ❌ | ❌ | ✅ |
| widget_spotlights | read | ❌ | ✅ | ❌ |

### Verification Steps
1. Log in as each role and open the Dashboard Builder.
2. Confirm the widgets above appear under **Available Widgets**.
3. Save a layout containing all widgets for that role.
4. View the live dashboard (`[ap_user_dashboard]`) and ensure each widget renders.
5. If a widget shows the *"You don’t have access to view this widget."* message, verify the user lacks the listed capability.
6. If a widget is missing or greyed out:
   - Check `DashboardWidgetRegistry::get_definitions(true)` output.
   - Review the **Widget Visibility** admin page for role/capability overrides.
   - Ensure the widget file exists and is included in `available-widgets.json`.
7. Repeat the process after clearing user meta with the **Reset Layout** button to ensure role defaults load correctly.

Successful completion of these checks confirms widgets match capabilities for every role across both the builder and front‑end view.
