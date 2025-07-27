---
title: Dashboard Layout Config
category: developer
role: developer
last_updated: 2025-07-23
status: draft
---
# Dashboard Layout Config

The Dashboard Builder stores layouts as JSON arrays. Each entry contains the widget `id`, `visible` flag and optional grid sizing.

```json
[
  { "id": "membership", "visible": true, "w": 4, "h": 2 },
  { "id": "favorites", "visible": false, "w": 4, "h": 2 }
]
```

Layouts are saved per user in `ap_dashboard_layout` and per role in the `artpulse_dashboard_layouts` option. Use `DashboardWidgetManager::saveUserLayout()` and `saveRoleLayout()` to persist updates.

If no user-specific layout exists, the builder falls back to the role defaults defined in `DashboardController::$role_widgets`. Preset templates registered via `DashboardController::get_default_presets()` can be loaded on demand through the dashboard UI.

The builder fetches layouts via `GET /wp-json/artpulse/v1/dashboard-widgets?role={role}` and saves changes with `POST /dashboard-widgets/save`.

A **Reset Layout** action deletes the user meta so role defaults apply on next login.
