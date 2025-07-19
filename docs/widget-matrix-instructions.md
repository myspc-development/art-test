# Admin Widget Matrix Instructions

This guide explains how to build the **Widget Role Matrix** used to control which dashboard widgets are visible for each user role.

Open the matrix from **ArtPulse → Widget Matrix** (`artpulse-widget-matrix`). To edit widget placement and ordering, use **ArtPulse → Widget Editor** (`artpulse-widget-editor`); see the [Admin Dashboard Widgets Editor Guide](./Admin_Dashboard_Widgets_Editor_Guide.md).

---

## 1. Refactor and Prepare the Codebase
- Centralize widget role configuration via a helper such as `getWidgetRoles()`.
- Ensure each widget registry entry includes a `roles[]` array.
- Store assignments in the `artpulse_widget_roles` option.

## 2. REST API
Add `/dashboard-config` endpoints returning a `widget_roles` property:

```php
register_rest_route('artpulse/v1', '/dashboard-config', [
  'methods'  => 'GET',
  'callback' => [DashboardConfigController::class, 'get_config'],
  'permission_callback' => fn() => current_user_can('read'),
]);

register_rest_route('artpulse/v1', '/dashboard-config', [
  'methods'  => 'POST',
  'callback' => [DashboardConfigController::class, 'save_config'],
  'permission_callback' => fn() => current_user_can('manage_options'),
]);
```

Example response:

```json
{
  "widget_roles": {
    "upcoming-events": ["member", "artist"],
    "portfolio": ["artist"]
  },
  "locked": []
}
```

## 3. React Admin UI
- Create `AdminWidgetMatrix.tsx` that fetches and saves the matrix via REST.
- Render a checkbox table of widgets versus roles.
- Show a toast when changes are saved.

## 4. Dashboard Filtering
- Filter widgets by the saved `widget_roles` when building dashboards.
- If no config exists, fall back to the defaults in the registry.

## 5. QA & Testing
- Verify settings persist via REST and update the dashboard correctly.
- Ensure endpoints require the appropriate capabilities.
