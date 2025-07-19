# Widget Registry Reference

All dashboard widgets are registered via `DashboardWidgetRegistry::register()`.
Each widget must provide the following fields:

```php
DashboardWidgetRegistry::register(
    'id',            // unique slug
    'label',         // human-readable name
    'category',      // grouping used in the editor filter
    'roles',         // array of roles that can use the widget
    'description',   // short help text
    'callback'       // function that outputs the widget
);
```

## Requirements
- IDs must be unique across all widgets and roles.
- Widgets can specify default visibility with `visible => true|false`.
- Categories allow the **Add Widget** modal to filter by topic.
- Register widgets on the `artpulse_register_dashboard_widget` action so they are available before layouts load. Core widgets reside in `src/Sample/RoleBasedWidgets.php`.

See the [Dashboard Widget Design Codex](./dashboard-widget-design-codex.md) for styling guidelines.
