---
title: Widget Architecture Spec
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
---

# Widget Architecture Spec

This specification consolidates the Widget Types, Registration Protocol, and Matrix Mapping used by the dashboard.

## Widget Types
Each widget block definition includes:
- `id` – unique identifier
- `title` – display name
- `component` – React component used for rendering
- `roles` – array of roles allowed to see the widget

## Registration Protocol
Widgets register through `DashboardWidgetRegistry::register()` and are listed in `assets/js/widgets/index.js` for dynamic loading.

```php
DashboardWidgetRegistry::register(
    'rsvp-stats',
    'RSVP Statistics',
    'stats',
    ['organization'],
    'ap_render_rsvp_stats'
);
```

Use the `ap_dashboard_editor_register_widget` hook to add new widgets from extensions.

## Matrix Mapping
Administrators manage widget permissions with the **Widget Matrix** screen. The matrix stores a map of widget IDs to arrays of roles:

```json
{
  "upcoming-events": ["member", "artist"],
  "portfolio": ["artist"],
  "rsvp-stats": ["organization"]
}
```

On the frontend, filter available widgets by the current user's role:

```ts
const userWidgets = allWidgets.filter(w => widgetRoles[w.id]?.includes(currentUserRole));
```

For verification steps see [Widget QA Checklist](../qa/widget-qa-checklist.md).
