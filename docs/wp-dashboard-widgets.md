---
title: WordPress Dashboard Widgets
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# WordPress Dashboard Widgets

This plugin adds simple widgets to the native WordPress dashboard. Widgets are registered via `wp_add_dashboard_widget()` so users can drag and drop them just like the default WordPress boxes. Each user's arrangement is stored automatically in `metaboxorder_dashboard` user meta.

## Widgets
- **Site Overview** – quick summary of recent activity. Title uses `dashicons-admin-home`.
- **Upcoming Events** – shows upcoming events (currently static text) with `dashicons-calendar-alt`.
- **Trending Tags** – lists tags trending over the past month with a `dashicons-tag` icon.

The **Trending Tags** widget is placed in the sidebar column by default, while the other two appear in the main column. You can drag the widgets between columns or reorder them. WordPress persists the layout per user without any additional code.

## Layout CSS
The stylesheet `assets/css/wp-dashboard-layout.css` provides a flexible two‑column layout. Columns collapse to full width on smaller screens. The CSS is enqueued only on the dashboard screen.

## Extending
Developers can add new widgets using the same `wp_add_dashboard_widget()` function. For a richer experience you could create a React interface using a grid library and save the layout via `update_user_meta()`.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
