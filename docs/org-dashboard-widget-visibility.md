---
title: Organization Dashboard Widgets
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Organization Dashboard Widgets

The dashboard displays a common set of widgets for all organization sub‑roles. `DashboardController::get_widgets_for_role()` returns the same widget list for `org_manager`, `org_editor` and `org_viewer`.

| Role | Default Widgets |
|------|-----------------|
| org_manager | site_stats, webhooks, rsvp_stats, org_analytics, donor_activity, notifications |
| org_editor  | site_stats, webhooks, rsvp_stats, org_analytics*, donor_activity, notifications |
| org_viewer  | site_stats, webhooks, rsvp_stats, donor_activity, notifications |

`*` The `org_editor` role inherits the analytics capability but the widget is removed during `wp_dashboard_setup` by `ap_dashboard_widget_visibility_filter()`. Viewers lack the capability entirely so the widget never registers.

When customizing layouts ensure each role has at least one widget to avoid an empty dashboard. If additional widgets are needed for viewers, consider lightweight notices or activity feeds.
