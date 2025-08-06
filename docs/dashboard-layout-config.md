---
title: Dashboard Layout Config
category: developer
role: developer
last_updated: 2025-07-23
status: complete
---
# Dashboard Layout Config

The Dashboard Builder stores layouts as JSON arrays. Each entry contains the widget `id`, `visible` flag and optional grid sizing.

```json
[
  { "id": "membership", "visible": true, "w": 4, "h": 2 },
  { "id": "favorites", "visible": false, "w": 4, "h": 2 }
]
```

Layouts are saved per user in `ap_dashboard_layout` and per role in the `ap_dashboard_widget_config` option. Use `DashboardWidgetManager::saveUserLayout()` and `saveRoleLayout()` to persist updates.

If no user-specific layout exists, the builder falls back to the role defaults defined in `DashboardController::$role_widgets`. Preset templates registered via `DashboardController::get_default_presets()` can be loaded on demand through the dashboard UI.

Default presets are now filtered against the current role before rendering. Widgets that are unregistered, restricted to other roles or require capabilities the role lacks are automatically removed. When every widget is filtered out an empty-state message is shown with a prompt to load a preset.

Preview parameters (`?ap_preview_role` and `?ap_preview_user`) continue to influence which role and layout are loaded, allowing administrators to verify the experience for different accounts.

The builder fetches layouts via `GET /wp-json/artpulse/v1/dashboard-widgets?role={role}` and saves changes with `POST /dashboard-widgets/save`.

A **Reset Layout** action deletes the user meta so role defaults apply on next login.
Additional fields such as `minW` and `minH` can be supplied by widgets that require a fixed size. The builder honors these constraints when positioning tiles.

To retrieve the stored layout for the current user in PHP use:

```php
$layout = DashboardWidgetManager::getUserLayout( get_current_user_id() );
```

Administrators editing defaults should call `DashboardWidgetManager::saveRoleLayout( $role, $layout );` after validating the layout structure. See the **Widget Manager Data Layer Guide** for persistence details.

### Exporting and Importing Layouts
Use the **Import/Export** panel in the Dashboard Builder to transfer layouts between sites. The exported JSON matches the structure above and can be restored by uploading the file.

