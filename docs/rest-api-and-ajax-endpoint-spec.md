# REST API & AJAX Endpoint Specification

This reference lists the endpoints used by the dashboard widget manager.

## REST Routes
- `GET /ap/widgets/available?role=member` – return widget metadata for a role.
- `GET /ap/layout?user_id=123` – fetch a specific user's layout and visibility.
- `POST /ap/layout/save` – persist widget order and visibility.
- `POST /ap/layout/reset` – delete user layout and fall back to defaults.

All routes require a valid nonce and check the current user's permissions. The save and reset endpoints return `{ "saved": true }` on success.

## AJAX Actions
- `ap_save_dashboard_widget_config` – updates `ap_dashboard_widget_config` when admins save defaults.
- `ap_reset_dashboard_layout` – clears a user's layout via AJAX.

Use `wp_create_nonce()` on page load and send the value with each request. Both actions verify `manage_options` capability before writing options.
