# Widget Role Matrix Implementation Guide

This document outlines the steps needed to build an admin interface for configuring which dashboard widgets are visible for each user role. Follow this checklist when implementing the feature.

## 1. Refactor and Prepare the Codebase
- **Centralize Widget Role Configuration**
  - Move any hardcoded role-based logic into a shared utility, for example `getWidgetRoles()`.
  - Ensure each widget registry entry includes a `roles[]` array.
- **Create `widgetRoles` Option**
  - Store widget role assignments in WordPress using `get_option('artpulse_widget_roles', [])` as the fallback.

## 2. REST API Endpoint
Add endpoints in `includes/api/dashboard-config.php`:
```php
register_rest_route('artpulse/v1', '/dashboard-config', [
  'methods'  => 'GET',
  'callback' => 'artpulse_get_dashboard_config',
  'permission_callback' => function () {
    return current_user_can('manage_options');
  }
]);

function artpulse_get_dashboard_config() {
  return [
    'widget_roles' => get_option('artpulse_widget_roles', []),
  ];
}
```
Implement a `POST` handler that updates the option via `update_option`.

## 3. React Admin UI
Create `AdminWidgetMatrix.tsx`:
- Fetch `widget_roles` from `GET /dashboard-config`.
- Render a matrix of roles (columns) against widgets (rows).
- Each cell contains a checkbox:
  ```jsx
  <input
    type="checkbox"
    checked={matrix[widgetId]?.includes(role)}
    onChange={() => toggleRole(widgetId, role)}
  />
  ```
- Save changes to the REST API with `POST /dashboard-config`.

## 4. UI Details
- Display widget titles instead of IDs.
- Optionally add tooltips or preview icons.
- Include a **Save** button that shows a success toast.
- Disable the UI unless the user has `manage_options` permission.

## 5. Frontend Filtering
- Update the dashboard loader to read roles from `getDashboardConfig().widget_roles`.
- If no configuration exists, use the default roles defined in the registry or hardcoded fallback.

## 6. Verification & Testing
- Roles update correctly in the admin UI.
- Settings persist via REST + `update_option()`.
- Users only see widgets allowed for their role.
- Unknown widgets or roles are ignored.
- REST endpoints require admin permissions.
- The fallback logic works when no configuration is stored.

