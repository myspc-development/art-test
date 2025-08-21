---
title: REST API Reference
category: api
role: developer
last_updated: 2025-07-20
status: complete
---

# REST API Reference

## Dashboard Settings Endpoints

### GET /wp-json/artpulse/v1/dashboard-config
Returns:
{
  widget_roles: { widgetId: [roles] },
  role_widgets: { role: [widgetIds] },
  locked: [widgetIds]
}

### POST /wp-json/artpulse/v1/dashboard-config
Update the allowed widgets, per-role layout, and locked state.

Request body:
{
  widget_roles: { widgetId: [roles] },
  layout: { role: [widgetIds] }, // alias: role_widgets
  locked: [widgetIds]
}

> **Note**: The legacy `dashboard-config.php` route and the earlier
> definitions inside `artpulse-management.php` have been removed. Routing now uses `src/Rest/DashboardConfigController.php` with core logic in `src/Core/DashboardController.php`.

## Widget Manager Endpoints

### GET /ap/widgets/available?role=member
Return widget metadata for a role.

### GET /ap_dashboard_layout?user_id=123
Fetch a specific user's layout and visibility.

### POST /ap_dashboard_layout
Persist widget order and visibility.

### POST /ap/layout/reset
Delete user layout and fall back to defaults.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
