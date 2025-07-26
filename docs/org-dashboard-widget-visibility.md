---
title: Organization Dashboard Widgets
category: developer
role: developer
last_updated: 2025-08-02
status: complete
---
# Organization Dashboard Widgets

The dashboard displays a common set of widgets for the `organization` role. Legacy sub‑roles were removed so all organization accounts share the same widget list.

| Role | Default Widgets |
|------|-----------------|
| organization | site_stats, webhooks, rsvp_stats, artpulse_analytics_widget*, ap_donor_activity, notifications |
| administrator | *(no default widgets)* |

`*` Organization users with the `view_analytics` capability see the analytics widget. If the capability is missing the widget is removed during `wp_dashboard_setup` by `WidgetVisibilityManager::filter_visible_widgets()`.

Plugins can hook into `ap_dashboard_widget_visibility_rules` to modify widget visibility or register new widgets. Each rule key is the widget id and supports `capability` and `exclude_roles` options.

```php
add_filter('ap_dashboard_widget_visibility_rules', function (array $rules) {
    $rules['myplugin_stats_widget'] = [
        'capability'    => 'view_my_stats',
        'exclude_roles' => ['member'],
    ];
    return $rules;
});
```

Ensure every role still receives at least one widget or the dashboard will display a "no dashboard content available" message. The fallback notice is triggered by `WidgetVisibilityManager::render_empty_state_notice()` and can link to documentation using the `ap_dashboard_empty_help_url` filter.

Widget visibility is managed by `ArtPulse\Dashboard\WidgetVisibilityManager`. Developers may subclass this manager or replace it entirely to customize behavior. Overriding `get_visibility_rules()` allows complete control over which widgets appear for each role.

## Extensibility

The manager exposes several filters:

- `ap_dashboard_widget_visibility_rules` – register additional widgets or custom visibility logic.
- `ap_dashboard_empty_help_url` – supply help links when no widgets remain after filtering.

Methods like `filter_visible_widgets()` also accept a user object allowing unit tests and plugins to evaluate visibility for arbitrary accounts. If the argument is not a valid `WP_User`, widgets remain unfiltered.

When customizing layouts ensure each role has at least one widget to avoid an empty dashboard. If additional widgets are needed for viewers, consider lightweight notices or activity feeds.
