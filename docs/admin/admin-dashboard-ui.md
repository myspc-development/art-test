---
title: Admin Dashboard UI
category: admin
role: admin
last_updated: 2025-07-20
status: complete
---

# Admin Dashboard UI

This guide details how administrators configure dashboard layouts for each user role. It builds on the Dashboard Builder and covers saving behavior, widget management and REST updates.

🔗 Available widgets and role visibility can now be managed in the [Dashboard Builder](../widgets/widget-matrix-reference.md).

## Role Based Configuration

Dashboard layouts are stored per role in the `artpulse_dashboard_layouts` option. Each entry is an array of widget IDs in display order—not block markup. The builder fetches the configuration for the selected role via `GET /wp-json/artpulse/v1/layout/{role}`. If no layout exists the default preset is used.

Admins choose a role from the **Role** dropdown before arranging widgets. Switching roles reloads the layout and available widgets for that role.

```js
// Example request
fetch('/wp-json/artpulse/v1/layout/artist')
  .then(r => r.json())
  .then(layout => renderLayout(layout));
```

### Artist Dashboard Example

An Artist layout might include widgets for managing artworks, sales reports and audience insights. Enable monetization blocks like **Artwork Manager** and **Payout Summary** so artists can track revenue.

### Organization Dashboard Example

Organizations typically arrange widgets such as **Ticketing Overview**, **Event Analytics** and **Staff Notices**. Use the role dropdown to load the `organization` layout and configure widgets for event management.

## Widgets and Layout Logic

Widgets appear in the **Add Widget** panel. Drag widgets into the main grid to add them. Layouts use a simple 12 column system and widgets define their default width. The editor prevents overlaps and snaps widgets into rows.

- **Lock Widget** toggles whether a widget can be removed by end users.
- **Reset Layout** reverts to the plugin default for the current role.
- **Save Layout** persists changes via a `POST` request.

## Saving and Resetting

Clicking **Save** sends the updated layout to `POST /wp-json/artpulse/v1/layout/{role}`. On success, a notice confirms the update. **Reset** deletes the stored layout so the next load falls back to defaults.

```js
await fetch('/wp-json/artpulse/v1/layout/member', {
  method: 'POST',
  body: JSON.stringify(layout),
  headers: { 'Content-Type': 'application/json' }
});
```

## Preview Mode & Styling

Dashboard Preview now loads the `[ap_user_dashboard]` shortcode in an iframe so administrators can switch roles and see the actual layout. Access the preview from **ArtPulse → Dashboard**. A style panel lets you configure background color, border and padding presets. Saved values are stored under the `style` key in `ap_dashboard_widget_config`.

Use the **Preview as…** dropdown to impersonate a role. Changes to layout or style can be saved directly from the preview window.

## Related Links

- [Admin Settings UI](./admin-settings-ui.md)
- [Dashboard Editor Developer Guide](../dashboard-editor-developer-guide.md)
- [Dashboard Builder Reference](../widgets/widget-matrix-reference.md)
- [Sprint Plan – Dashboard Integration](../internal/planning/Sprint_Plan.md)
- [Admin Feature Documentation Review](./admin-feature-review.md)

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
