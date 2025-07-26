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
| organization | site_stats, webhooks, rsvp_stats, artpulse_analytics_widget, ap_donor_activity, notifications |
| org_manager | site_stats, webhooks, rsvp_stats, artpulse_analytics_widget, ap_donor_activity, notifications |
| org_editor  | site_stats, webhooks, rsvp_stats, artpulse_analytics_widget*, ap_donor_activity, notifications |
| org_viewer  | site_stats, webhooks, rsvp_stats, ap_donor_activity, notifications |
| administrator | *(no default widgets)* |

`*` The `org_editor` role inherits the analytics capability but the widget is removed during `wp_dashboard_setup` by `WidgetVisibilityManager::filter_visible_widgets()`. Viewers lack the capability entirely so the widget never registers.

Plugins can hook into `ap_dashboard_widget_visibility_rules` to modify widget visibility or register new widgets. Each rule key is the widget id and supports `capability` and `exclude_roles` options.

```php
add_filter('ap_dashboard_widget_visibility_rules', function (array $rules) {
    $rules['myplugin_stats_widget'] = [
        'capability'    => 'view_my_stats',
        'exclude_roles' => ['org_viewer'],
    ];
    return $rules;
});
```

Ensure every role still receives at least one widget or the dashboard will display a "no dashboard content available" message. Provide help via the `ap_dashboard_empty_help_url` filter when needed.

Widget visibility is managed by `ArtPulse\Dashboard\WidgetVisibilityManager`. Developers may subclass this manager or replace it entirely to customize behavior. Overriding `get_visibility_rules()` allows complete control over which widgets appear for each role.

## Extensibility

The manager exposes several filters:

- `ap_dashboard_widget_visibility_rules` – register additional widgets or custom visibility logic.
- `ap_dashboard_empty_help_url` – supply help links when no widgets remain after filtering.

Methods like `filter_visible_widgets()` also accept a user object allowing unit tests and plugins to evaluate visibility for arbitrary accounts.

When customizing layouts ensure each role has at least one widget to avoid an empty dashboard. If additional widgets are needed for viewers, consider lightweight notices or activity feeds.
