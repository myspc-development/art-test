---
title: REST API Reference
category: api
role: developer
last_updated: 2025-07-20
status: draft
---

# REST API Reference

## Dashboard Settings Endpoints

### GET /wp-json/artpulse/v1/dashboard-config
Returns:
{
  roles: { widgetId: [roles] },
  locked: [widgetIds]
}

### POST /wp-json/artpulse/v1/dashboard-config
Update the allowed widgets and locked state.

## Widget Manager Endpoints

### GET /ap/widgets/available?role=member
Return widget metadata for a role.

### GET /ap/layout?user_id=123
Fetch a specific user's layout and visibility.

### POST /ap/layout/save
Persist widget order and visibility.

### POST /ap/layout/reset
Delete user layout and fall back to defaults.

💬 Found something outdated? Submit Feedback
