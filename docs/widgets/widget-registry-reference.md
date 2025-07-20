---
title: Widget Registry Reference
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
---

# Widget Registry Reference

All dashboard widgets are registered via `DashboardWidgetRegistry::register()`.
React-driven widgets are also listed in `assets/js/widgets/index.js` so the dashboard
container can dynamically load their components. Each widget entry contains:

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

JS registry format:

```js
export default [
  {
    id: 'my_widget',
    title: 'My Widget',
    component: MyWidgetComponent,
    roles: ['member']
  }
];
```

## Requirements
- IDs must be unique across all widgets and roles.
- Widgets can specify default visibility with `visible => true|false`.
- Categories allow the **Add Widget** modal to filter by topic.
- Register widgets on the `artpulse_register_dashboard_widget` action so they are available before layouts load. Core widgets reside in `src/Sample/RoleBasedWidgets.php`.

See the [Dashboard Widget Design Codex](./dashboard-widget-design-codex.md) for styling guidelines.

To add or remove a widget from the React dashboard, edit `assets/js/widgets/index.js`
and update the exported array. Roles may be adjusted by modifying the `roles`
property of each entry.
