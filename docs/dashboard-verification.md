# Dashboard & Capability Verification

## Admin capabilities
1. Log in as an administrator.
2. Run `current_user_can('publish_pages')` in a debugging console or with `wp shell`; it should return `true`.

## Member dashboard rendering
1. Log in as a user with the `member` role.
2. Visit the page containing the `[ap_user_dashboard]` shortcode.
3. Confirm the dashboard displays:
   - The **Favorites Overview** widget listing favorited events.
   - The **Near Me Events** widget showing `[ap_event_listing]` results.

## Using the inspector
1. Enable `WP_DEBUG` in `wp-config.php`.
2. As an administrator, visit `/wp-admin/index.php`.
3. A notice lists the current role, capability check, and loaded widget IDs. The same data is written to the `debug.log` file.

## Resetting layouts for testing
To restore the default layout for a user run:

```bash
wp ap reset-layout <USER_ID>
```

This deletes `ap_dashboard_layout` and `ap_widget_visibility` so only the default widgets load.

## WP-CLI repairs
To ensure required capabilities exist on older installs run:

```bash
wp cap add member view_artpulse_dashboard
wp user list --role=member --field=ID | xargs -I % wp user add-cap % view_artpulse_dashboard
wp cap add administrator publish_pages
```

