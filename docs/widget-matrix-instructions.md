# Widget Role Matrix Implementation Guide

This guide explains how to build the admin interface for configuring which dashboard widgets appear for each user role.

## 1. Refactor and Prepare the Codebase
- Centralize widget role logic using a helper such as `getWidgetRoles()`.
- Ensure each widget registry entry includes a `roles[]` array.
- Store role assignments in WordPress via `get_option('artpulse_widget_roles', [])`.

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

Implement a `POST` handler that updates the option with `update_option('artpulse_widget_roles', $matrix);`.

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

- Display widget titles instead of IDs.
- Optionally add tooltips or preview icons.
- Include a **Save** button that shows a success toast.
- Disable the UI unless the user has `manage_options` capability.
- Persist changes using `POST /dashboard-config`.

## 4. Frontend Filtering
- Update the dashboard loader to read roles from `getDashboardConfig().widget_roles`.
- If no configuration exists, fall back to the roles defined in the registry or hardcoded defaults.

## 5. Verification & Testing
- Admin UI renders and saves correctly.
- Settings persist via REST and `update_option()`.
- Users only see widgets allowed for their role.
- Unknown widgets or roles are ignored.
- REST endpoints require admin permissions.
- Layout doesn't break and locked widgets remain enforced.
