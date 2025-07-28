---
title: Dashboard Config API
category: api
role: developer
last_updated: 2025-07-28
status: complete
---

# Dashboard Config API

These endpoints manage widget role assignments and locked widgets.

## `GET /artpulse/v1/dashboard-config`
Returns the current configuration.
- **Permission:** `read`
- **Response:**
  ```json
  { "widget_roles": {"widget_id": ["role"]}, "locked": ["widget_id"] }
  ```

## `POST /artpulse/v1/dashboard-config`
Updates configuration values.
- **Permission:** `manage_options`
- **Headers:** `X-WP-Nonce`
- **Body:**
  ```json
  { "widget_roles": {"widget_id": ["role"]}, "locked": ["widget_id"] }
  ```
- **Response:** `{ "saved": true }`

