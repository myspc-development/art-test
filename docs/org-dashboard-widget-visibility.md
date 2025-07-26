---
title: Organization Dashboard Widgets
category: developer
role: developer
last_updated: 2025-07-26
status: complete
---
# Organization Dashboard Widgets

The dashboard displays a common set of widgets for all organization sub‑roles. `DashboardController::get_widgets_for_role()` returns the same widget list for `org_manager`, `org_editor` and `org_viewer`.

| Role | Default Widgets |
|------|-----------------|
| organization | site_stats, webhooks, rsvp_stats, org_analytics, donor_activity, notifications |
| org_manager | site_stats, webhooks, rsvp_stats, org_analytics, donor_activity, notifications |
| org_editor  | site_stats, webhooks, rsvp_stats, org_analytics*, donor_activity, notifications |
| org_viewer  | site_stats, webhooks, rsvp_stats, donor_activity, notifications |
| administrator | *(no default widgets)* |

`*` The `org_editor` role inherits the analytics capability but the widget is removed during `wp_dashboard_setup` by `ap_dashboard_widget_visibility_filter()`. Viewers lack the capability entirely so the widget never registers.

Future sub‑roles can hook into `ap_dashboard_widget_visibility_rules` to modify widget visibility. Ensure each role still receives at least one widget to avoid an empty dashboard.

When customizing layouts ensure each role has at least one widget to avoid an empty dashboard. If additional widgets are needed for viewers, consider lightweight notices or activity feeds.
