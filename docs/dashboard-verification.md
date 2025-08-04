# Dashboard & Capability Verification

### Admin capabilities
- Log in as an administrator.
- Run `current_user_can('publish_pages')` in a debugging console or with `wp shell`; it should return `true`.

### Member dashboard widgets
- Log in as a user with the `member` role.
- Visit the page containing the `[ap_user_dashboard]` shortcode.
- The dashboard should display:
  - The **Favorites Overview** widget listing favorited events.
  - The **Near Me Events** widget showing `[ap_event_listing]` results.

### Resetting layouts for testing
- To restore the default layout for a user run:
  ```bash
  wp ap reset-user-dashboard <USER_ID>
  ```
  This deletes `ap_dashboard_layout` and `ap_widget_visibility` for the user so only the default widgets load.

### WP-CLI repairs
To ensure required capabilities exist on older installs run:
```bash
wp cap add member view_artpulse_dashboard
wp user list --role=member --field=ID | xargs -I % wp user add-cap % view_artpulse_dashboard
wp cap add administrator publish_pages
```
