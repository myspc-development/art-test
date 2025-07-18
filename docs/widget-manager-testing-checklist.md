# Widget Manager Testing & Access Control Checklist

Use this checklist when verifying the widget manager implementation.

## Permissions
- Only users with `manage_options` can modify role defaults.
- Regular users may only edit their own layout via REST calls.

## Persistence
- Layout and visibility settings persist across logins and sessions.
- Resetting a layout removes user meta so role defaults load again.

## Functional Tests
- Saving a new layout updates `ap_dashboard_layout` and `ap_widget_visibility`.
- Reset actions return `{ "saved": true }` and clear user data.
- Importing invalid JSON is rejected with an error message.

Refer to `test-dashboard-widgets.sh` for automated checks.
